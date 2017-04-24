<?php
class wechat{
	public function wechat_pay($orderid,$param_title){
		global $_GPC,$_W;
		$param_title = $param_title ? $param_title :'百步汇';
		$uniacid = $_W['uniacid'];
		$openid = $_W['fans']['from_user'];
		if ($orderid=='' || $orderid==0) 
		{
			return -1;//订单不存在
		}
		$order = pdo_fetch('select * from ' . tablename('xxx') . ' where id=:id and weid=:uniacid and from_user=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
		if($order==''){
			return -1;//订单不存在
		}
		if($order['paystatus']==1){
			return -2;//订单已支付
		}
		$log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'j_act', ':tid' => $order['ordersn']));
		if (!empty($log) && ($log['status'] == '0')) 
		{
			pdo_delete('core_paylog', array('plid' => $log['plid']));
			$log = NULL;
		}
		if (empty($log)) 
		{
			$log = array('uniacid' => $uniacid, 'openid' => $openid, 'module' => 'j_act', 'tid' => $order['ordersn'], 'fee' => $order['real_price'], 'status' => 0);
			pdo_insert('core_paylog', $log);
			$plid = pdo_insertid();
		}

		load()->model('payment');
		$wechat = array('success' => false);
		$setting = uni_setting($_W['uniacid'], array('payment'));
		if(is_array($setting['payment'])){
			$options = $setting['payment']['wechat'];
			$options['appid'] = $_W['account']['key'];
			$options['secret'] = $_W['account']['secret'];
		}
		$params = array();
		$params['tid'] = $log['tid'];
		$params['user'] = $openid;
		$params['fee'] = $order['real_price'];
		$params['title'] = $param_title;
		$wechat = $this->wechat_build($params, $options, 0);
		if (!is_error($wechat)) {
			$wechat['success'] = true;
			$wechat['weixin'] = true;
		}
		return $wechat;
	}

	public function wechat_build($params, $wechat, $type = 0){
		global $_W;
		load()->func('communication');
		if (empty($wechat['version']) && !empty($wechat['signkey'])) 
		{
			$wechat['version'] = 1;
		}
		$wOpt = array();
		if ($wechat['version'] == 1) 
		{
			$wOpt['appId'] = $wechat['appid'];
			$wOpt['timeStamp'] = TIMESTAMP . '';
			$wOpt['nonceStr'] = random(32);
			$package = array();
			$package['bank_type'] = 'WX';
			$package['body'] = urlencode($params['title']);
			$package['attach'] = $_W['uniacid'] . ':' . $type;
			$package['partner'] = $wechat['partner'];
			$package['device_info'] = 'j_act';
			$package['out_trade_no'] = $params['tid'];
			$package['total_fee'] = $params['fee'] * 100;
			$package['fee_type'] = '1';
			$package['notify_url'] = $_W['siteroot'] . 'addons/j_act/payment/notify.php';
			$package['spbill_create_ip'] = CLIENT_IP;
			$package['input_charset'] = 'UTF-8';
			ksort($package);
			$string1 = '';
			foreach ($package as $key => $v ) 
			{
				if (empty($v)) 
				{
					continue;
				}
				$string1 .= $key . '=' . $v . '&';
			}
			$string1 .= 'key=' . $wechat['key'];
			$sign = strtoupper(md5($string1));
			$string2 = '';
			foreach ($package as $key => $v ) 
			{
				$v = urlencode($v);
				$string2 .= $key . '=' . $v . '&';
			}
			$string2 .= 'sign=' . $sign;
			$wOpt['package'] = $string2;
			$string = '';
			$keys = array('appId', 'timeStamp', 'nonceStr', 'package', 'appKey');
			sort($keys);
			foreach ($keys as $key ) 
			{
				$v = $wOpt[$key];
				if ($key == 'appKey') 
				{
					$v = $wechat['signkey'];
				}
				$key = strtolower($key);
				$string .= $key . '=' . $v . '&';
			}
			$string = rtrim($string, '&');
			$wOpt['signType'] = 'SHA1';
			$wOpt['paySign'] = sha1($string);
			return $wOpt;
		}
		$package = array();
		$package['appid'] = $wechat['appid'];
		$package['mch_id'] = $wechat['mchid'];
		$package['nonce_str'] = random(32);
		$package['body'] = $params['title'];
		$package['device_info'] = 'j_act';
		$package['attach'] = $_W['uniacid'] . ':' . $type;
		$package['out_trade_no'] = $params['tid'];
		$package['total_fee'] = $params['fee'] * 100;
		$package['spbill_create_ip'] = CLIENT_IP;
		if (!empty($params['goods_tag'])) 
		{
			$package['goods_tag'] = $params['goods_tag'];
		}
		$package['notify_url'] = $_W['siteroot'] . 'addons/j_act/payment/notify.php';
		$package['trade_type'] = 'JSAPI';
		$package['openid'] = $_W['openid'];
		ksort($package, SORT_STRING);
		$string1 = '';
		foreach ($package as $key => $v ) 
		{
			if (empty($v)) 
			{
				continue;
			}
			$string1 .= $key . '=' . $v . '&';
		}
		$string1 .= 'key=' . $wechat['signkey'];
		$package['sign'] = strtoupper(md5($string1));
		$dat = array2xml($package);
		$response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);
		if (is_error($response)) 
		{
			return $response;
		}
		$xml = @simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
		if (strval($xml->return_code) == 'FAIL') 
		{
			return error(-1, strval($xml->return_msg));
		}
		if (strval($xml->result_code) == 'FAIL') 
		{
			return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
		}
		$prepayid = $xml->prepay_id;
		$wOpt['appId'] = $wechat['appid'];
		$wOpt['timeStamp'] = TIMESTAMP . '';
		$wOpt['nonceStr'] = random(32);
		$wOpt['package'] = 'prepay_id=' . $prepayid;
		$wOpt['signType'] = 'MD5';
		ksort($wOpt, SORT_STRING);
		$string = '';
		foreach ($wOpt as $key => $v ) 
		{
			$string .= $key . '=' . $v . '&';
		}
		$string .= 'key=' . $wechat['signkey'];
		$wOpt['paySign'] = strtoupper(md5($string));
		return $wOpt;
	}
}
?>