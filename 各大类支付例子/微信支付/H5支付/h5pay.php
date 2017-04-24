<?php
/**
 * Created by PhpStorm.
 * User: Jeffery
 * Date: 2017/4/24
 * Time: 16:11
 * h5支付
 */

function payOrder()
{
    global $_GPC, $_W;
    $aid = intval($_GPC['aid']);
    include 'payment/wechat.php';
    $package = new wechat;
    $wechat = $package->wechat_pay($aid);
    if($wechat==-1){
        echo json_encode(array('errno'=>-1,'msg'=>'订单不存在'));exit();
    }elseif($wechat==-2){
        echo json_encode(array('errno'=>-2,'msg'=>'订单已经支付过了'));exit();
    }elseif($wechat==-3){
        echo json_encode(array('errno'=>-3,'msg'=>'记录不存在'));exit();
    }else{
        $arr = array('errno'=>1,'wechat'=>$wechat);
        echo json_encode($arr);exit;
    }
    # 调用完之后，到回调地址处理订单 payment/notify.php
}

##### H5
$('#tj').click(function(){
    var aid = '{$info['id']}';
                $.post('{php payOrder()}',{aid:aid},function(data){
        if(1==data.errno){
            WeixinJSBridge.invoke(
                "getBrandWCPayRequest",
                            {
                               "appId":data['wechat']['appId'],
                               "timeStamp":data['wechat']['timeStamp'],
                               "nonceStr":data['wechat']['nonceStr'],
                               "package":data['wechat']['package'],
                               "signType":data['wechat']['signType'],
                               "paySign":data['wechat']['paySign'],
                            },
                            function(res){
                                //WeixinJSBridge.log(res.err_msg);
                                //alert(res.err_code+'---'+res.err_desc+'---'+res.err_msg);return;
                                if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                                    //支付成功
                                    Jacky._alert("支付成功");
                                    setTimeout(function(){
                                        window.location.reload();
                                    },2000);
                                }
                                if(res.err_msg == "get_brand_wcpay_request:cancel"){
                                    //支付过程中用户取消
                                    Jacky._alert("取消支付");
                                }
                                if(res.err_msg == "get_brand_wcpay_request:fail"){
                                    //支付失败
                                    Jacky._alert("支付失败");
                                }
                            }
                        );
                    }else{
            alert(data.msg);
        }
    },'json');
});