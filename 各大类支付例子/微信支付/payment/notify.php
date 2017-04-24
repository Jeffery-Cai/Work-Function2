<?php
error_reporting(0);
define('IN_MOBILE', true);
$input = file_get_contents('php://input');
libxml_disable_entity_loader(true);
if (!empty($input) && empty($_GET['out_trade_no'])) 
{
	$obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
	$data = json_decode(json_encode($obj), true);
	if (empty($data)) 
	{
		exit('fail');
	}
	if (($data['result_code'] != 'SUCCESS') || ($data['return_code'] != 'SUCCESS')) 
	{
		$result = array('return_code' => 'FAIL', 'return_msg' => (empty($data['return_msg']) ? $data['err_code_des'] : $data['return_msg']));
		echo array2xml($result);
		exit();
	}
	$get = $data;
}
else 
{
	$get = $_GET;
}
require '../../../framework/bootstrap.inc.php';
global $_GPC,$_W;
$strs = explode(':', $get['attach']);
$_W['uniacid'] = $_W['weid'] = intval($strs[0]);
$type = intval($strs[1]);
$total_fee = $get['total_fee'] / 100;
$setting = uni_setting($_W['uniacid'], array('payment'));
if (is_array($setting['payment'])) 
{
	$wechat = $setting['payment']['wechat'];
	if (!empty($wechat)) 
	{
		ksort($get);
		$string1 = '';
		foreach ($get as $k => $v ) 
		{
			if (($v != '') && ($k != 'sign')) 
			{
				$string1 .= $k . '=' . $v . '&';
			}
		}
		$wechat['signkey'] = ($wechat['version'] == 1 ? $wechat['key'] : $wechat['signkey']);
		$sign = strtoupper(md5($string1 . 'key=' . $wechat['signkey']));
		$get['openid'] = (isset($get['sub_openid']) ? $get['sub_openid'] : $get['openid']);
		if ($sign == $get['sign']) 
		{
			if (empty($type)) 
			{
				$tid = $get['out_trade_no'];
				$isborrow = 0;
				$borrowopenid = '';
				if (strpos($tid, '_borrow') !== false) 
				{
					$tid = str_replace('_borrow', '', $tid);
					$isborrow = 1;
					$borrowopenid = $get['openid'];
				}
				if (strpos($tid, '_B') !== false) 
				{
					$tid = str_replace('_B', '', $tid);
					$isborrow = 1;
					$borrowopenid = $get['openid'];
				}
				if (strexists($tid, 'GJ')) 
				{
					$tids = explode('GJ', $tid);
					$tid = $tids[0];
				}
				$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid  limit 1';
				$params = array();
				$params[':tid'] = $tid;
				$params[':module'] = 'j_act';
				$log = pdo_fetch($sql, $params);
				if (!empty($log) && ($log['status'] == '0') && ($log['fee'] == $total_fee)) 
				{
					$site = WeUtility::createModuleSite($log['module']);
					if (!is_error($site)) 
					{
						$method = 'payResult';
						if (method_exists($site, $method)) 
						{
							$ret = array();
							$ret['weid'] = $log['weid'];
							$ret['uniacid'] = $log['uniacid'];
							$ret['result'] = 'success';
							$ret['type'] = $log['type'];
							$ret['from'] = 'return';
							$ret['tid'] = $log['tid'];
							$ret['user'] = $log['openid'];
							$ret['fee'] = $log['fee'];
							$ret['tag'] = $log['tag'];
							$result = $site->$method($ret);
							if ($result) 
							{
								$log['tag'] = iunserializer($log['tag']);
								$log['tag']['transaction_id'] = $get['transaction_id'];
								$record = array();
								$record['status'] = '1';
								$record['tag'] = iserializer($log['tag']);
								pdo_update('core_paylog', $record, array('plid' => $log['plid']));


								# 编写支付回调处理
                                #  （ ### 请往后的技术员仔细重点填写（0 === 活动报名支付 - 默认 ，1 === 场馆预订支付） ）
                                if($type == 0)  #活动报名支
                                {
                                    pdo_update('xxx',array('paystatus'=>1,'paytime'=>time(),'status'=>1),array('sn'=>$log['tid'],'uniacid'=>$log['uniacid']));
                                }
                                else if($type == 1) #场馆预订支付
                                {
                                    pdo_update('xxx',array('status'=>1,'paytime'=>time()),array('ordersn'=>$log['tid'],'weid'=>$log['uniacid']));
                                }
							}
						}
					}
				}
				else 
				{
					$result = array('return_code' => 'FAIL ');
					echo array2xml($result);
					exit();
				}
			}
			$result = array('return_code' => 'SUCCESS', 'return_msg' => 'OK');
			echo array2xml($result);
			exit();
		}
	}
}
exit('fail');
?>