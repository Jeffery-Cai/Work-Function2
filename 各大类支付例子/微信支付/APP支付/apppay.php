<?php
/**
 * Created by PhpStorm.
 * User: Jeffery
 * Date: 2017/4/24
 * Time: 16:18
 * app支付
 */
function apppay()
{
    global $_GPC,$_W,$host;
    $uid = $_GPC['uid'];
    $id = $_GPC['id']; # 转为id
    if($uid <= 0){
        return array('status'=>array('code'=>1001,'msg'=>'你没有登录'));
    }
    if($id <= 0){
        return array('status'=>array('code'=>3014,'msg'=>'缺少订单id'));
    }
    $data = array();
    $getCgOrder = getCgOrder($_W,$_GPC,$host);
    $order = $getCgOrder[3];
    //print_r($order);exit;
    $config = pdo_fetch('SELECT * FROM '.tablename('wxconfig').' WHERE weid=:weid ',array(':weid'=>$_W['uniacid']));
    // print_r($config);exit;
    if(!$order || $order['uid'] != $uid){
        return array('status'=>array('code'=>3038,'msg'=>'订单不存在'));
    }
    if($order['status'] == 1){
        return array('status'=>array('code'=>3031,'msg'=>'订单已支付'));
    }
    $data['appid']	= $config['appid']; //应用ID
    $data['mch_id']	= $config['mchid']; //商户号
    $data['nonce_str'] = random(32);	//随机字符串
    $data['body'] = 'APP项目'; //商品描述
    $data['attach']	= $_W['uniacid'] . ':' . 1; //附加数据   ### 请往后的技术员仔细重点填写（0 活动报名支付 - 默认 ，1场馆预订支付）
    $data['total_fee'] = floatval($order['to_pay'])*100; //总金额，单位：分
    $data['out_trade_no'] = $order['sn']; //商户订单号
    $data['spbill_create_ip']	= $_SERVER['REMOTE_ADDR'];//终端IP
    $data['notify_url'] = substr($_W['siteroot'],0,31).'payment/notify.php';
    //$data['ceshi'] = 123;
    $data['trade_type']	= 'APP'; //交易类型
    $data['sign'] = getSign($data,$config['paysignkey']);
    //print_r($data);exit;
    $set = array(
        'key' => trim($config['paysignkey']),
        'appid' => trim($config['appid']),
        'mch_id' => trim($config['mchid']),
        'order_sn' => trim(trim($data['out_trade_no']))
    );
    //print_r($set);exit;
    $xmlData = ToXml($data);
    //print_r($xmlData);exit;
    $postUrl = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    $resXML	= globalCurlPost($postUrl,$xmlData);
    $resArr = FromXml($resXML);
    $set = array_merge($resArr,$set);
    //print_r($set);exit;
    //$data = returnData($set);
    // print_r($data);exit;
    return  returnData($set); # 微信支付需要的参数
    # 调用完之后，到回调地址处理订单 payment/notify.php

}


/**微信签名包
 * @param $arr
 * @param $key
 * @return string
 */
function getSign($arr,$key)
{
    ksort($arr);//排序
    $str	= ToUrlParams($arr);
    $str 	= $str . "&key=".$key;
    $str 	= md5($str);
    return strtoupper($str);
}
/**
 * 输出xml字符
 * @throws WxPayException
 *数组转化成xml数据
 **/
function ToXml($arr)
{
    if(!is_array($arr) || count($arr) <= 0)
    {
        return "";
    }

    $xml = "<xml>";
    foreach ($arr as $key=>$val)
    {
        if (is_numeric($val)){
            $xml.="<".$key.">".$val."</".$key.">";
        }else{
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
    }
    $xml.="</xml>";
    return $xml;
}
/**
 * 将xml转为array
 * @param string $xml
 * @throws WxPayException
 */
function FromXml($xml)
{
    if(!$xml){
        return array();
    }
    //将XML转为array
    //禁止引用外部xml实体
    //libxml_disable_entity_loader(true);
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}
/**
 * 格式化参数格式化成url参数
 */
function ToUrlParams($arr)
{
    $buff = "";
    foreach ($arr as $k => $v)
    {
        if($k != "sign" && $v != "" && !is_array($v)){
            $buff .= $k . "=" . $v . "&";
        }
    }

    $buff = trim($buff, "&");
    return $buff;
}
/**发送微信支付请求
 * @param $url
 * @param string $data
 * @param int $second
 * @return mixed
 */
function globalCurlPost($url,$data='',$second=30){
    $ch = curl_init();
    $header = "Accept-Charset: utf-8";
    curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $temp = curl_exec($ch);
    curl_close($ch);
    return $temp;
}
function getPayApiSign($data){
    $package				= "prepay_id=".trim($data['prepay_id']);
    $timeStamp				= strval(time());
    $nonceStr = random(32);	//随机字符串

    $appid = trim($data['appid']);
    $key = trim($data['key']);
    $mch_id = trim($data['mch_id']);
    $arr = array();
    $arr['timestamp']	= $timeStamp;
    $arr['appid']		= $appid;
    $arr['partnerid']	= $mch_id;
    $arr['noncestr']	= $nonceStr;
    $arr['prepayid']	= trim($data['prepay_id']);
    $arr['package']		= "Sign=WXPay";//$arr['package'] = $package;
    $arr['sign']		= getSign($arr,$key);

    return $arr;
}
////接口返回数据
function returnData($arr){
    //print_r($arr);exit;
    if($arr['return_code'] != "SUCCESS"){

        return array('code'=>3034,'msg'=>"调用支付失败(".$arr['return_msg'].")");

    }

    if($arr['result_code'] == "SUCCESS"){
        //print_r($arr);exit;
        $signPackage = getPayApiSign($arr);		//jsapi 验签
        // print_r($signPackage);exit;

        if($arr['addr'] != "order"){

            //成功调用接口，记录数据
            //$orderSn = $arr['order_sn'];
//            $where['order_sn'] = $arr['order_sn'];
//            M('product_cart2')->where($where)->setField("paysign",$arr['prepay_id']);
//
//            $content = date("Y-m-d H:i:s")."：调用支付成功（".json_encode($signPackage)."）\r\n";
//            R('Api/LogApi/write',array($content,$this->setTextFile));
        }
        return array('status'=>array('code'=>3032,'msg'=>'微支付调起支付参数','data'=>$signPackage));

    }else{
        //调起支付失败
        //错误代码：
        $errArr 	= array(
            "NOAUTH"				=> "商户无此接口权限"	,
            "NOTENOUGH"				=> "余额不足"	,
            "ORDERPAID"				=> "商户订单已支付"	,
            "ORDERCLOSED"			=> "订单已关闭"	,
            "SYSTEMERROR"			=> "系统错误/系统超时"	,
            "APPID_NOT_EXIST"		=> "APPID不存在"	,
            "MCHID_NOT_EXIST"		=> "MCHID不存在"	,
            "APPID_MCHID_NOT_MATCH"	=> "appid和mch_id不匹配"	,
            "LACK_PARAMS"			=> "缺少参数"	,
            "OUT_TRADE_NO_USED"		=> "商户订单号重复"	,
            "SIGNERROR"				=> "签名错误"	,
            "XML_FORMAT_ERROR"		=> "XML格式错误"	,
            "REQUIRE_POST_METHOD"	=> "XML格式错误"	,
            "REQUIRE_POST_METHOD"	=> "请使用post方法"	,
            "POST_DATA_EMPTY"		=> "post数据为空"	,
            "NOT_UTF8"				=> "编码格式错误"
        );
        $errCode	= $arr['err_code'];	//记录错误码
        $errMsg		= "";

        foreach ($errArr as $k=>$v){
            if($k == $errCode){
                $errMsg	= $v;
            }
        }

        return array('code'=>3035,'msg'=>$errMsg."(".$arr['err_code_des'].")");
    }
}