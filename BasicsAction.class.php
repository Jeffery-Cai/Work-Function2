<?php
/**
* 基础函数库
* @author     : Jeffery
* @createtime : 2016-1-31
* @updatetime : 2016-1-31
*/
class BasicsAction extends UserAction
{
	 /*************************************************************************************
		* 去除重复
		* $data -> 二维数组
		* ------------------------------------
		* $data1 = array(array('id'=>1,'name'='王小明','paytime'=>14123222));
		* $data2 = array(array('id'=>1,'name'='王小明','paytime'=>14123222));
		* $data = array_mereg($data1,$data2);
		* unique($data);
		* -------------------------------------;
		*/
		public function unique($data = array())
		{
			$tmp = array();
			foreach($data as $key => $value){
				//把一维数组键值与键名组合
				foreach($value as $key1 => $value1){
					$value[$key1] = $key1 . '_|_' . $value1;//_|_分隔符复杂点以免冲突
				}
				$tmp[$key] = implode(',|,', $value);//,|,分隔符复杂点以免冲突
			}

			//对降维后的数组去重复处理
			$tmp = array_unique($tmp);

			//重组二维数组
			$newArr = array();
			foreach($tmp as $k => $tmp_v){
				$tmp_v2 = explode(',|,', $tmp_v);
				foreach($tmp_v2 as $k2 => $v2){
					$v2 = explode('_|_', $v2);
					$tmp_v3[$v2[0]] = $v2[1];
				}
				$newArr[$k] = $tmp_v3;
			}
			return $newArr;
		}

	 /************************************************************************************* 
		* 多维数组排序
		* $d -> 多维数组
		* $field -> 数据表字段
		* $sort -> 升序/降序（默认为降序）
		* ------------------------------------
		* $data = array(array('id'=>1,'name'='王小明','paytime'=>14123222));
		* sortarr($data,'paytime',SORT_ASC);
		* -------------------------------------;
		*/
		public function sortarr($d=array(),$field,$sort=SORT_DESC)
		{
			$ages = array();
			foreach ($d as $user) {
				$ages[] = $user["{$field}"];
			}
				array_multisort($ages , $sort, $d);   // 排序
				return $d;
			}


	 /*************************************************************************************
		* 上传图片
		* $input_name -> 上传表单名称
		* $uploaded_dir -> 上传文件的路径（默认为'./Uploads/'）
		* ------------------------------------
		* upload_file("file");
		* -------------------------------------;
		*/
		public function upload_file($input_name, $uploaded_dir='./Uploads/')
		{

		  // 接收上传过来的文件数据
			$file = $_FILES[$input_name];  
		    // 根据错误信息反馈用户
			$error = $file['error'];
			switch($error){
				case UPLOAD_ERR_INI_SIZE:
		            //die('<script>alert("");location.href="'.$_SERVER['PHP_SELF'].'"</script>');
				error("请上传小于1MB的文件");
				break;
				case UPLOAD_ERR_FORM_SIZE:
				break;
				case UPLOAD_ERR_PARTIAL:
		            //die('<script>alert("你的网络有问题");location.href="'.$_SERVER['PHP_SELF'].'"</script>');
				error("你的网络有问题");
				break;
				case UPLOAD_ERR_NO_FILE:
		            //die('<script>alert("请选择上传的文件");location.href="'.$_SERVER['PHP_SELF'].'"</script>');
				error("请选择上传的文件");
				break;
			}
		    // 限制文件类型（MIME）
			$type = $file['type'];
			if( !in_array($type, array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'))){
		       // die('<script>alert("请上传jpg、png、gif格式的图片");location.href="'.$_SERVER['PHP_SELF'].'"</script>');
				error("请上传jpg、png、gif格式的图片");
			}
		    // 限制文件大小
			$size = $file['size'];
			if( $size > 1*1024*1024 ){
		        //die('<script>alert("请上传小于1MB的文件");location.href="'.$_SERVER['PHP_SELF'].'"</script>');
				error("请上传小于1MB的文件");
			}
			
		    // 生成唯一文件名
		    // $filename = date('YmdHis') . rand(1000,9999);
		    // 检查上传目录是否存在
			if( !file_exists( $uploaded_dir ) ){
				mkdir( $uploaded_dir );
			}
		    // 获取原文件的文件类型
			$suffix = '';
			switch($file['type']){
				case 'image/jpeg':
				case 'image/jpg':
				$suffix = 'jpg';
				break;
				case 'image/png':
				$suffix = 'png';
				break;
				case 'image/gif':
				$suffix = 'gif';
				break;
			}
		    // 保存文件
			$result = move_uploaded_file($file['tmp_name'], "{$uploaded_dir}.{$_FILES[$input_name]['name']}");  
		   // $result = move_uploaded_file($file['tmp_name'], "{$uploaded_dir}.{$$filename}.{$suffix}");  
			if( $result ){
		        //die('<script>alert("文件上传成功");location.href="'.$_SERVER['PHP_SELF'].'"</script>');
			}else{
				error("文件上传失败");
		       // die('<script>alert("文件上传失败");location.href="'.$_SERVER['PHP_SELF'].'"</script>');
			}
		}

		/*************************************************************************************
		 * 生成缩略图 （可配合上传图片函数 -> 以上）
		 * $img_path: 原图片地址路径
		 * $filename: 新的名称
		 * $w=100: 缩略图宽度（初始值为100）
		 * $h=80: 缩略图宽度（初始值为80）
		 * ------------------------------------
		 * mk_thumb("./1.jpg","./new","76","115")
		 * -------------------------------------;
		 * 
		 */
		public function mk_thumb($img_path,$filename,$w=100,$h=80)
		{
			$info=getimagesize($img_path);
			$src_img="";
			// 判断是gig?jpg?png
			switch ($info[2]) {
				case 1:
				$src_img=imagecreatefromgif($img_path);
				break;
				case 2:
				$src_img=imagecreatefromjpeg($img_path);
				break;
				case 3:
				$src_img=imagecreatefrompng($img_path);
				break;
			}
			// 新建画板
			$dst_image=imagecreatetruecolor($w,$h);
			// 复制原图到新画板
			imagecopyresized($dst_image,$src_img, 0, 0, 0, 0, $w, $h, $info[0],$info[1]);
			switch ($info[2]) {
				case 1:
				$src_img=imagegif($dst_image,$filename.".gif");
				break;
				case 2:
				$src_img=imagejpeg($dst_image,$filename.".jpg");
				break;
				case 3:
				$src_img=imagepng($dst_image,$filename.".png");
				break;
			}
		}
		
		//用php从身份证中提取生日,包括15位和18位身份证 
		function getIDCardInfo($IDCard){ 
			$result['error']=0;//0：未知错误，1：身份证格式错误，2：无错误 
			$result['flag']='';//0标示成年，1标示未成年 
			$result['tdate']='';//生日，格式如：2012-11-15 
			if(!eregi("^[1-9]([0-9a-zA-Z]{17}|[0-9a-zA-Z]{14})$",$IDCard)){ 
				$result['error']=1; 
				return $result; 
			}else{ 
				if(strlen($IDCard)==18){ 
					$tyear=intval(substr($IDCard,6,4)); 
					$tmonth=intval(substr($IDCard,10,2)); 
					$tday=intval(substr($IDCard,12,2)); 
					if($tyear>date("Y")||$tyear<(date("Y")-100)){ 
						$flag=0; 
					}elseif($tmonth<0||$tmonth>12){ 
						$flag=0; 
					}elseif($tday<0||$tday>31){ 
						$flag=0; 
					}else{ 
						$tdate=$tyear."-".$tmonth."-".$tday." 00:00:00"; 
						if((time()-mktime(0,0,0,$tmonth,$tday,$tyear))>18*365*24*60*60){ 
							$flag=0; 
						}else{ 
							$flag=1; 
						} 
					} 
				}elseif(strlen($IDCard)==15){ 
					$tyear=intval("19".substr($IDCard,6,2)); 
					$tmonth=intval(substr($IDCard,8,2)); 
					$tday=intval(substr($IDCard,10,2)); 
					if($tyear>date("Y")||$tyear<(date("Y")-100)){ 
						$flag=0; 
					}elseif($tmonth<0||$tmonth>12){ 
						$flag=0; 
					}elseif($tday<0||$tday>31){ 
						$flag=0; 
					}else{ 
						$tdate=$tyear."-".$tmonth."-".$tday." 00:00:00"; 
						if((time()-mktime(0,0,0,$tmonth,$tday,$tyear))>18*365*24*60*60){ 
							$flag=0; 
						}else{ 
							$flag=1; 
						} 
					} 
				} 
			} 
			$result['error']=2;//0：未知错误，1：身份证格式错误，2：无错误 
			$result['isAdult']=$flag;//0标示成年，1标示未成年 
			$result['birthday']=$tdate;//生日日期 
			return $result; 
		} 
		
		/**
     * 函数说明：验证身份证是否真实
     * 注：加权因子和校验码串为互联网统计  尾数自己测试11次 任意身份证都可以通过
     * 传递参数：
     * $number身份证号码
     * 返回参数：
     * true验证通过
     * false验证失败
     */
		function isIdCard($number) {
			$sigma = '';
        //加权因子 
			$wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码串 
			$ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        //按顺序循环处理前17位 
			for ($i = 0;$i < 17;$i++) { 
            //提取前17位的其中一位，并将变量类型转为实数 
				$b = (int) $number{$i}; 
            //提取相应的加权因子 
				$w = $wi[$i]; 
            //把从身份证号码中提取的一位数字和加权因子相乘，并累加 得到身份证前17位的乘机的和 
				$sigma += $b * $w;
			}
    //echo $sigma;die;
        //计算序号  用得到的乘机模11 取余数
			$snumber = $sigma % 11; 
        //按照序号从校验码串中提取相应的余数来验证最后一位。 
			$check_number = $ai[$snumber];
			if ($number{17} == $check_number) {
				return true;
			} else {
				return false;
			}
		}
    //eg
		if (!isIdCard('000000000000000001')) {
			echo "身份证号码不合法";
		} else {
			echo "身份证号码正确";
		}


	/**
     * 生成唯一订单号  ( 这个已经是不给力了---暂不用 )
     */
	public function build_order_no()
	{
		$no = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        //检测是否存在
		$db = M('Order');
		$info = $db->where(array('number'=>$no))->find();
		(!empty($info)) && $no = $this->build_order_no();
		return $no;

	}
	
	
	/**
     * 生成唯一号  ( 最终不支持php高版本版 )
     */
	public function build_order_no($db,&$no)
	{
		$no1 = substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4);

		$no2 = mt_rand(1111,9999);
		$no = $no1.' '.$no2;

		// 查询所有
		$where['code'] = array('in',$no);
		$info = M("$db")->field('id,code')->where($where)->select();
		if(!empty($info) && $no)
		{
			$this->build_order_no($db,$no);
		}
		return $no;
	}
	
	/**
     * 生成唯一号 ( 最终支持版 )
     */
	public function build_order_no($db,$no)
	{
		global $no;
		$no1 = substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 4);

		$no2 = mt_rand(1111,9999);
		$no = $no1.' '.$no2;

		// 查询所有
		$where['code'] = array('in',$no);
		$info = M("$db")->field('id,code')->where($where)->select();
		if(!empty($info) && $no)
		{
			$this->build_order_no($db,$no);
		}
		return $no;
	}

	
	/* 生成随机不重复用户的抽奖码 */
	public function s()
	{
		$num = intval($_POST['num']);
		if($num != '')
		{

                // 判断数据库是否已经有这些了
			$username = M('choujiang')->where($where)->getField('username',true);
			if($username!='')
			{
                    // $where['is_choose'] = 0;
				$idinwhere['username'] = array('not in',$username);
				$idin = M('choujiang')->Distinct(true)->group('username')->field('id,username')->limit($num)->order('rand()')->where($idinwhere)->getField('id',true);
				$where1['id'] = array('in',$idin);
				$where1['is_choose'] = array('neq',1);
				$username = M('choujiang')->where(array('id'=>$where1['id']))->getField('username',true);
                    $is_au = M('choujiang')->where(array('username'=>array('in',$username),'is_choose'=>1))->getField('id',true);  // true = 重复了 , false = 反之
                    // dump($is_au);die;
                    if(!$is_au){
                    	$res = M('choujiang')->where($where1)->save(array('is_choose'=>1,'choose_time'=>time()));
                    	$this->success('中奖码生成成功',U('Choujiang/setChoujiang',array('token'=>$this->token)));exit;
                    }else{

                    	$this->success('请重新生成',U('Choujiang/setChoujiang',array('token'=>$this->token)));exit;
                    }
                }else{
                    // 再生成一遍
                	$idin = M('choujiang')->Distinct(true)->group('username')->field('id,username')->limit($num)->order('rand()')->getField('id',true);
                	$where1['id'] = array('in',$idin);
                	$where1['is_choose'] = array('neq',1);
                	$username = M('choujiang')->where(array('id'=>$where1['id']))->getField('username',true);
                        $is_au = M('choujiang')->where(array('username'=>array('in',$username),'is_choose'=>1))->getField('id',true);  // true = 重复了 , false = 反之
                        // dump($is_au);die;
                        if(!$is_au){
                        	$res = M('choujiang')->where($where1)->save(array('is_choose'=>1,'choose_time'=>time()));
                        	$this->success('中奖码生成成功',U('Choujiang/setChoujiang',array('token'=>$this->token)));exit;
                        }else{

                        	$this->success('请重新生成',U('Choujiang/setChoujiang',array('token'=>$this->token)));exit;
                        }


                    }
                }else{
                	$this->success('请输入中奖个数');exit;
                }
            }


            /* 门店订单导出最终版本 （无缝修复）  -->PHP版本 */
            public function MdOrderexcule()
            {
            	$filename='md_order'.date('Ymd',time());
            	header("Content-type:application/vnd.ms-excel"); 
            	header("Content-Disposition:attachment;filename=".$filename.".xls");
            	$cartData = array();
            	for ($i=0; $i <= 9; $i++) {
            		if($i == 0)
            		{
            			$data.='订单型号'."\t";
            			$data.='订单数量'."\t";
            		}else{
            			$data.='订单型号'.$i."\t";
            			$data.='订单数量'.$i."\t";

            		}
            	}
            	$orders = M('db')->order('addtime desc')->select();
            	$arr = array();
            	$data = array();
            	if(!empty($orders)){
            // 导出数据
            		foreach ($orders as &$v) {
            			$md_username = M('db')->where(array('id'=>$v['mdid']))->getField('username');
					// 省份ID
            			$sheng = M('region')->where(array('id'=>$v['md_provinceid']))->getField('region_name');
            			$shi = M('region')->where(array('id'=>$v['md_shi']))->getField('region_name');
            			if($v['yhq_sn'] == '')
            			{
            				$yhq_sn = '无';
            			}else{
            				$yhq_sn = $v['yhq_sn'];
            			}
            			$v['md_username'] = $md_username;
            			$v['sheng'] = $sheng;
            			$v['shi'] = $shi;
            			$v['yhq_sn'] = $yhq_sn;
            			$v['addtime'] = date("Y/m/d H:i:s",$v['addtime']);
            		}
            	}else{
            		return array();
            	}

            	$this->assign('list',$orders);
            	$this->display();
            }

	/* 门店订单导出最终版本  -->Html代码 
	<html xmlns:o="urn:schemas-microsoft-com:office:office" 
 xmlns:x="urn:schemas-microsoft-com:office:excel" 
 xmlns="http://www.w3.org/TR/REC-html40"> 
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

 <html> 
     <head> 
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" /> 
         <style id="Classeur1_16681_Styles"></style> 
     </head> 
     <body> 
         <div id="Classeur1_16681" align=center x:publishsource="Excel"> 
             <table x:str border=1 cellpadding=0 cellspacing=0 width=100% style="border-collapse: collapse"> 
                 <tr>
                  <td class=xl2216681 nowrap>id</td>
                  <td class=xl2216681 nowrap>门店用户</td>
                  <td class=xl2216681 nowrap>门店所在省份</td>
                  <td class=xl2216681 nowrap>门店所在城市</td>
                  <td class=xl2216681 nowrap>消费者姓名</td>
                  <td class=xl2216681 nowrap>消费者手机号</td>
                  <td class=xl2216681 nowrap>消费者地址</td>
                  <td class=xl2216681 nowrap>优惠券序号</td>
                  <td class=xl2216681 nowrap>订单总价格</td>
                  <for start="0" end="10">
                  <if condition="$i == 0">
                    <td class=xl2216681 nowrap>订单型号</td>
                    <td class=xl2216681 nowrap>订单数量</td>
                  <else/>
                    <td class=xl2216681 nowrap>订单型号.{lanrain:$i}</td>
                    <td class=xl2216681 nowrap>订单数量.{lanrain:$i}</td>
                  </if>
                  </for>
                  <td class=xl2216681 nowrap>提交时间</td>

                 </tr>
                 <volist name="list" id="v">
                  <tr>
                    <td class=xl2216681 nowrap>{lanrain:$v['id']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['md_username']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['sheng']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['shi']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['username']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['phone']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['address']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['yhq_sn']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['price']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao1']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number1']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao2']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number2']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao3']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number3']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao4']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number4']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao5']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number5']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao6']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number6']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao7']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number7']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao8']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number8']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['xinghao9']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['good_number9']}</td>
                    <td class=xl2216681 nowrap>{lanrain:$v['addtime']}</td>
                  </tr>
                  </volist>
             </table> 
         </div> 
     </body> 
 </html> 
 */

 /**************      在线客服聊天 ajax    */
 public function ajaxUserAbort()
 {
 	$this->display();
 }
 public function ajaxX()
 {
 	$sleep = 4;
 	$token = 1003;
			// ignore_user_abort(true);  // 一直挂起
 	set_time_limit(0);
 	$getRoot = getcwd();
 	$dir	 = str_replace('\\','/',$getRoot);
 	if (!file_exists($dir.'/testHe') || !is_dir($dir.'/testHe')){
 		@mkdir($dir.'/testHe',0777);
 	}
 	$file = $dir.'/testHe/footers'.date("Ymd").'.log';
			/*
			do{
				sleep($sleep);
				$status = R('Web/Testfooter/sendTemplate',array($token));
				$content = "[".date("Y-m-d H:i:s")."] 状态是 ：".$status."|正在进行...\n";
				R('Web/Testfooter/setFile',array($file, $content));
				if($status = 2)
				{
					break;
					echo json_encode(array('data'=>'已经结束'));exit;
				}else{
					echo json_encode(array('data'=>'数据一直来'));exit;
				}

			}while(true);
			*/
			$i = 0;
			while (true){     
			    //sleep(1);     
			    sleep(1);//0.5秒     
			    $i++;     
			    //若得到数据则马上返回数据给客服端，并结束本次请求     
			    $rand=rand(1,1);     
			    if($rand<=15){     
			    	$arr=array('success'=>"1",'name'=>'xiaocai','text'=>$rand);     
			    	echo json_encode($arr);     
			    	exit();
			    }     
			    //服务器($_POST['time']*0.5)秒后告诉客服端无数据     
			    if($i==$_POST['time']){
			    	$arr=array('success'=>"0",'name'=>'xiaocai','text'=>$rand);     
			    	echo json_encode($arr);     
			    	exit();
			    }     
			}
		}


/************************   js客服ajax **************
	 $("#btn").bind('click',{btn : $("#btn")},function(evdata)
    {
         $.ajax({  
            type:"POST",  
            dataType:"json",  
            url:'{lanrain::U("Server/ajaxX")}',  
            timeout:80000,  //ajax请求超时时间80秒  
            data:{time:"80"}, //40秒后无论结果服务器都返回数据  
            success: function(data, textStatus) { 
                //从服务器得到数据，显示数据并继续查询     
                if (data.success == "1") { 
                    $("#msg").append("<br>[有数据]" + data.text); 
                    evdata.data.btn.click(); 
                } 
                //未从服务器得到数据，继续查询     
                if (data.success == "0") { 
                    $("#msg").append("<br>[无数据]"); 
                    evdata.data.btn.click(); 
                } 
            }, 
            //Ajax请求超时，继续查询     
            error: function(XMLHttpRequest, textStatus, errorThrown) { 
                if (textStatus == "timeout") { 
                    $("#msg").append("<br>[超时]"); 
                    evdata.data.btn.click(); 
                } 
            } 
           });
    });
*/

/************** 方法 ：未登录情况下 ****/
if(empty($_COOKIE['pc_mendian'])){
			if(ACTION_NAME!='login')
			{
				$this->MendianFunc->trulLogin();
			}
		}




/* 未登录就跳转 */
	public function trulLogin()
	{
		$url = './index.php?g=Wap&m=Mendian&a=login';
		echo '<script type="text/javascript">alert("您未登录，正在跳转到登录页面");window.location.href = "'.$url.'"; </script>';
	}

}


?>