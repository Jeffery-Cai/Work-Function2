<?php
/**
* 导入功能 
* 只针对 上传的csv后缀有效
*/
class DexcelAction extends BaseAction
{
	/****************  住宅+车位+商铺  均为例子参考  **************/
    public $clearBth = 2;   

	// 住宅
	public function dr_zhuza()
	{
        if($this->clearBth == 1)
        {
            M('fee_area')->where('id!=0')->delete();
            M('fee_carilist')->where('id!=0')->delete();
            M('fee_fanghao')->where('id!=0')->delete();
            M('fee_louge')->where('id!=0')->delete();
            M('fee_shoplist')->where('id!=0')->delete();
            M('fee_spu')->where('id!=0')->delete();
            M('fee_zhuzailist')->where('id!=0')->delete();
        }else{
            $data['area_id'] ='区域ID';
            $data['louge_id'] ='楼阁ID';  
            $data['bianhao'] ='单元编号';  
            $data['fanghao_id'] ='房号ID';  
            $data['username'] ='姓名';  
            $data['time_action'] ='时间段';  
	    $data['xxxxx'] ='管理费'; 
            $data['price'] ='合计';
            $this->importCsv('file',$data,'fee_zhuzailist','index.php?g=User&m=Jiaofei&a=zhuza_index&token='.session('token'));   // 参数如 importCsv 下数据，表
        }
	}

	// 车位 
	public function dr_chewei()
	{
		/* 区域   楼阁名称    车位号 时间段 合计  */
        $data['area_id'] ='区域ID';
        $data['louge_id'] ='楼阁ID';  
        $data['bianhao'] ='单元编号';  
        $data['number'] ='车位号';  
        $data['username'] ='姓名';  
        $data['time_action'] ='时间段';  
	$data['xxxxx'] ='管理费';
        $data['price'] ='合计';
		$this->importCsv('file',$data,'fee_carilist','index.php?g=User&m=Jiaofei&a=chewei_index&token='.session('token'));   // 参数如 importCsv 下数据，表
	}

	// 商铺
	public function dr_shangpu()
	{
        /* 区域   楼阁名称    商铺号 时间段 合计  */
		$data['area_id'] ='区域ID';
        $data['louge_id'] ='楼阁ID';  
        $data['bianhao'] ='单元编号';  
        $data['spu_id'] ='商铺号ID';  
        $data['username'] ='姓名';  
        $data['time_action'] ='时间段'; 
	$data['xxxxx'] ='管理费';  
        $data['price'] ='合计';
		$this->importCsv('file',$data,'fee_shoplist','index.php?g=User&m=Jiaofei&a=shangpu_index&token='.session('token'));   // 参数如 importCsv 下数据，表
	}

	/* 导入函数 
	* $input_name -> 上传的表单名称
	* $import_data -> 数组
	* $table -> 插入的数据表名
	* $url -> 跳转的url
    */
	public function importCsv($input_name,$import_data,$table,$url)
	{
		$line_number = 0;
		$arr = array();
	    $goods_list = array();
	    $field_list = array_keys($import_data);
        $data = file($_FILES["{$input_name}"]['tmp_name']);
        $fixx = substr(strrchr($_FILES["{$input_name}"]['name'], '.'), 1);
        if($fixx != 'csv')
        {
        	$this->error('请上传csv格式'); exit;
        }
        foreach ($data as $line) {
        	# code...
    			$line=iconv('GB2312','UTF-8//IGNORE',$line);

    			if($line_number == 0)   // 跳过第一行
    			{
    				$line_number++;
    	           continue;
    			}
                if($line_number == 1)   // 跳过第二行
                {
                    $line_number++;
                    continue;
                }

    			 $arr    = array();
           $buff   = '';
           $quote  = 0;
           $len    = strlen($line);
           for ($i = 0; $i < $len; $i++)
           {
                    $char = $line[$i];

                    if ('\\' == $char)
                    {
                        $i++;
                        $char = $line[$i];

                        switch ($char)
                        {
                            case '"':
                                $buff .= '"';
                                break;
                            case '\'':
                                $buff .= '\'';
                                break;
                            case ',';
                                $buff .= ',';
                                break;
                            default:
                                $buff .= '\\' . $char;
                                break;
                        }
                    }
                    elseif ('"' == $char)
                    {
                        if (0 == $quote)
                        {
                            $quote++;
                        }
                        else
                        {
                            $quote = 0;
                        }
                    }
                    elseif (',' == $char)
                    {
                        if (0 == $quote)
                        {
                            if (!isset($field_list[count($arr)]))
                            {
                                continue;
                            }
                            $field_name = $field_list[count($arr)];
                            $arr[$field_name] = trim($buff);
                            $buff = '';
                            $quote = 0;
                        }
                        else
                        {
                            $buff .= $char;
                        }
                    }
                    else
                    {
                        $buff .= $char;
                    }

                    if ($i == $len - 1)
                    {
                        if (!isset($field_list[count($arr)]))
                        {
                            continue;
                        }
                        $field_name = $field_list[count($arr)];
                        $arr[$field_name] = trim($buff);
                    }
           }
           $goods_list[] = $arr;
        }

        if($goods_list)
        {
            // $arr = array();
			foreach ($goods_list as $k => $v) {

                $v['price'] = str_replace(',','',$v['price']);

                /********  添加数据的逻辑操作 */
                // 区域
                    $areainfo = D('fee_area')->where(array('name'=>$v['area_id']))->getField('name');
                    if(!$areainfo)
                    {
                        D('fee_area')->add(array('name'=>$v['area_id'],'is_show'=>1));
                    }
                    $areaid = D('fee_area')->where(array('name'=>$v['area_id']))->getField('id');
                    $v['area_id'] = intval($areaid);

                    // 楼阁
                    // 加个判断，是否是住宅-》车位-》商铺
                    // 再加个判断，区域不同
                    if($table == 'fee_zhuzailist')
                    {
                        $lougeinfo = D('fee_louge')->where(array('name'=>$v['louge_id'],'type'=>1,'area_id'=>$areaid))->getField('name');
                        if(!$lougeinfo)
                        {
                            D('fee_louge')->add(array('name'=>$v['louge_id'],'is_show'=>1,'area_id'=>$areaid,'type'=>1));
                        }
                        $lougeid = D('fee_louge')->where(array('name'=>$v['louge_id'],'type'=>1,'area_id'=>$areaid))->getField('id');
                    }

                    if($table == 'fee_carilist')
                    {
                        $lougeinfo = D('fee_louge')->where(array('name'=>$v['louge_id'],'type'=>2,'area_id'=>$areaid))->getField('name');
                        if(!$lougeinfo)
                        {
                            D('fee_louge')->add(array('name'=>$v['louge_id'],'is_show'=>1,'area_id'=>$areaid,'type'=>2));
                        }
                        $lougeid = D('fee_louge')->where(array('name'=>$v['louge_id'],'type'=>2,'area_id'=>$areaid))->getField('id');
                    }

                     if($table == 'fee_shoplist')
                    {
                        $lougeinfo = D('fee_louge')->where(array('name'=>$v['louge_id'],'type'=>3,'area_id'=>$areaid))->getField('name');
                        if(!$lougeinfo)
                        {
                            D('fee_louge')->add(array('name'=>$v['louge_id'],'is_show'=>1,'area_id'=>$areaid,'type'=>3));
                        }
                        $lougeid = D('fee_louge')->where(array('name'=>$v['louge_id'],'type'=>3,'area_id'=>$areaid))->getField('id');
                    }

                    $v['louge_id'] = intval($lougeid);
                    
                    // 房号：
                    // 得加判断，区分是哪个区域
                    if($table == 'fee_zhuzailist')
                    {
                        // 房号
                        $fanghaoinfo = D('fee_fanghao')->where(array('name'=>$v['fanghao_id'],'louge_id'=>$lougeid,'area_id'=>$areaid))->getField('name');
                        if(!$fanghaoinfo)
                        {
                            D('fee_fanghao')->add(array('name'=>$v['fanghao_id'],'is_show'=>1,'louge_id'=>$lougeid,'area_id'=>$areaid));
                        }
                        $fanghaoid = D('fee_fanghao')->where(array('name'=>$v['fanghao_id'],'louge_id'=>$lougeid,'area_id'=>$areaid))->getField('id');
                    }
                    $v['fanghao_id'] = intval($fanghaoid);


                    if($table == 'fee_shoplist')
                    {
                       // 商铺号
                        $spuinfo = D('fee_spu')->where(array('name'=>$v['spu_id'],'louge_id'=>$lougeid,'area_id'=>$areaid))->getField('name');
                        if(!$spuinfo)
                        {
                            D('fee_spu')->add(array('name'=>$v['spu_id'],'is_show'=>1,'louge_id'=>$lougeid,'area_id'=>$areaid));
                        }
                        $spuid = D('fee_spu')->where(array('name'=>$v['spu_id'],'louge_id'=>$lougeid,'area_id'=>$areaid))->getField('id');
                        $v['spu_id'] = intval($spuid);
                    }
                $res = D("{$table}")->add($v);
			}
			if($res){                     
					$this->success("导入成功","{$url}");
			}else{
				  $this->error('导入失败');
			}
		}
	}


    /* 住宅导出 */
    public function zhuza_excute()
    {
        $filename='zhuza'.date('Ymd',time());
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename.xls");
        // ID   小区  楼阁  房号  时间段 付款金额    支付状态
        $data='ID'."\t";
        $data.='小区'."\t";
        $data.='楼阁'."\t";
        $data.='房号'."\t";
        $data.='时间段'."\t";
        $data.='付款金额'."\t";
        $data.='支付状态'."\t\n";
        $orders = D('fee_zhuzailist')->order('id desc')->select();
        if(!empty($orders)){
            // 导出数据
            foreach ($orders as $k => $v) {
                    $area_name = D('fee_area')->where(array('id'=>$v['area_id']))->getField('name');  // 小区
                    $louge_name = D('fee_louge')->where(array('id'=>$v['louge_id']))->getField('name');  // 楼阁
                    $fanghao_name = D('fee_fanghao')->where(array('id'=>$v['fanghao_id']))->getField('name');  // 房号

                    $data.=$v['id']."\t";
                    $data.=$area_name."\t";
                    $data.=$louge_name."\t";
                    $data.=$fanghao_name."\t";
                    $data.=$v['time_action']."\t";
                    $data.=$v['price']."\t";
                    if($v['is_pay'] == 1)
                    {
                        $is_pay = '已支付';
                    }else{
                        $is_pay = '未支付';
                    }
                    $data.=$is_pay."\t";
                    $data.="\t\n";
                }
        }
     echo iconv('utf-8','gbk',$data);
    }

    /* 车位导出 */
    public function chewei_excute()
    {
        $filename='chewei'.date('Ymd',time());
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename.xls");
        // ID   小区  楼阁  车位号 时间段 付款金额    支付状态
        $data='ID'."\t";
        $data.='小区'."\t";
        $data.='楼阁'."\t";
        $data.='车位号'."\t";
        $data.='时间段'."\t";
        $data.='付款金额'."\t";
        $data.='支付状态'."\t\n";

        $orders = D('fee_carilist')->order('id desc')->select();
        if(!empty($orders)){
            // 导出数据
            foreach ($orders as $k => $v) {
                    $area_name = D('fee_area')->where(array('id'=>$v['area_id']))->getField('name');  // 小区
                    $louge_name = D('fee_louge')->where(array('id'=>$v['louge_id']))->getField('name');  // 楼阁

                    $data.=$v['id']."\t";
                    $data.=$area_name."\t";
                    $data.=$louge_name."\t";
                    $data.=$v['number']."\t";
                    $data.=$v['time_action']."\t";
                    $data.=$v['price']."\t";
                    if($v['is_pay'] == 1)
                    {
                        $is_pay = '已支付';
                    }else{
                        $is_pay = '未支付';
                    }
                    $data.=$is_pay."\t";
                    $data.="\t\n";
                }
        }
     echo iconv('utf-8','gbk',$data);
    }

    /* 商铺导出 */
    public function shangpu_excute()
    {
        $filename='shangpu'.date('Ymd',time());
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename.xls");
        // ID    小区  楼阁   商铺号 时间段 付款金额    支付状态
        $data='ID'."\t";
        $data.='小区'."\t";
        $data.='楼阁'."\t";
        $data.='商铺号'."\t";
        $data.='时间段'."\t";
        $data.='付款金额'."\t";
        $data.='支付状态'."\t\n";

        $orders = D('fee_shoplist')->order('id desc')->select();
        if(!empty($orders)){
            // 导出数据
            foreach ($orders as $k => $v) {
                    $area_name = D('fee_area')->where(array('id'=>$v['area_id']))->getField('name');  // 小区
                    $louge_name = D('fee_louge')->where(array('id'=>$v['louge_id']))->getField('name');  // 楼阁
                    $spu_name = D('fee_spu')->where(array('id'=>$v['spu_id']))->getField('name');  // 房号

                    $data.=$v['id']."\t";
                    $data.=$area_name."\t";
                    $data.=$louge_name."\t";
                    $data.=$spu_name."\t";
                    $data.=$v['time_action']."\t";
                    $data.=$v['price']."\t";
                    if($v['is_pay'] == 1)
                    {
                        $is_pay = '已支付';
                    }else{
                        $is_pay = '未支付';
                    }
                    $data.=$is_pay."\t";
                    $data.="\t\n";
                }
        }
     echo iconv('utf-8','gbk',$data);
    }

    /* 订单导出 */
    public function order_excute()
    {
        $filename='jiaofei_order'.date('Ymd',time());
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename.xls");
        /*    ID：152
               缴费类型： 住宅缴费
               业主姓名：2
               业主手机号码：13112157790
               小区：华林居
               楼阁：华鸿路第02栋
               房号 ： 402
               时间段： 0
               支付费用：585.3
               下单时间：2016-02-18 12:19:11
               支付时间：
               支付状态
        */
        $data='ID'."\t";
        $data.='缴费类型'."\t";
        $data.='业主姓名'."\t";
        $data.='业主手机号码'."\t";
        $data.='小区'."\t";
        $data.='楼阁'."\t";
        $data.='房号'."\t";
        $data.='商铺号'."\t";
        $data.='车位号'."\t";
        $data.='时间段'."\t";
        $data.='支付费用'."\t";
        $data.='下单时间'."\t";
        $data.='支付时间'."\t";
        $data.='支付状态'."\t\n";
        $orders = D('fee_order')->where('is_pay=1')->order('id desc')->select();
        if(!empty($orders)){
            // 导出数据
            foreach ($orders as $k => $v) {
                    $area_name = D('fee_area')->where(array('id'=>$v['area_id']))->getField('name');  // 小区
                    $louge_name = D('fee_louge')->where(array('id'=>$v['louge_id']))->getField('name');  // 楼阁
                    $fanghao_name = D('fee_fanghao')->where(array('id'=>$v['fanghao_id']))->getField('name');  // 房号
                    $spu_name = D('fee_spu')->where(array('id'=>$v['spu_id']))->getField('name');  // 商铺号
                    $number = D('fee_carilist')->where(array('id'=>$v['fee_id'],'type'=>2))->getField('number');  // 车位号
                    $user_name = D('userinfo')->where(array('id'=>$v['uid']))->getField('nickname');
                    if($v['type'] == 1)
                    {
                        $type_name = '住宅缴费';
                        $db = 'fee_zhuzailist';
                    }else if($v['type'] == 2)
                    {
                        $type_name = '车位缴费';
                        $db = 'fee_carilist';
                    }else if($v['type'] == 3){
                        $type_name = '商铺缴费';
                        $db = 'fee_shoplist';
                    }

                    if($v['is_pay'] == 1)
                    {
                        $is_pay = '已支付';
                    }else{
                        $is_pay = '未支付';
                    }

                    $time_action = D($db)->where(array('id'=>$v['fee_id']))->getField('time_action');


                    $data.=$v['id']."\t";
                    $data.=$type_name."\t";
                    $data.=$v['username']."\t";
                    $data.=$v['usermobile']."\t";
                    $data.=$area_name."\t";
                    $data.=$louge_name."\t";
                    $data.=$fanghao_name."\t";
                    $data.=$spu_name."\t";
                    $data.=$v['cart_number']."\t";
                    $data.=$time_action."\t";
                    $data.=$v['price']."\t";
                    $data.=date("Y-m-d H:i:s",$v['addtime'])."\t";
                    $data.=date("Y-m-d H:i:s",$v['pay_time'])."\t";
                    $data.=$is_pay."\t";
                    $data.="\t\n";
                }
        }
     echo iconv('utf-8','gbk',$data);
    }
}