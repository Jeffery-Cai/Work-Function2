<?php
/**
* 基础函数库
* @createtime : 2016-1-31
* HTML + thinkphp帮助例子
* @help : 本案例都是完全由thinkphp编写
* @author : Jeffery
* @help-email : 1345199080@qq.com
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

		/**
		 * 用php从身份证中提取生日,包括15位和18位身份证
		 * @param $IDCard : 身份证号码
		 */
		publc function getIDCardInfo($IDCard)
		{
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
		public function isIdCard($number)
		{
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
        	//计算序号  用得到的乘机模11 取余数
			$snumber = $sigma % 11; 
        	//按照序号从校验码串中提取相应的余数来验证最后一位。 
			$check_number = $ai[$snumber];
			if ($number{17} == $check_number) {
				return true;
			} else {
				return false;
			}
			/* eg
				if (!isIdCard('000000000000000001')) {
					echo "身份证号码不合法";
				} else {
					echo "身份证号码正确";
				}
			*/
		}


		/**
	     * 生成唯一订单号  ( 这个已经是不给力了 --- 失效中)
	     */
		public function build_order_no1()
		{
			$no = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
	        //检测是否存在
			$db = M('Order');
			$info = $db->where(array('number'=>$no))->find();
			(!empty($info)) && $no = $this->build_order_no();
			return $no;
		}

		/**
	     * 生成唯一号  ( 最终不支持php高版本版，指针方式 )
	     * @param $db : 数据表姓名
	     * @param $no : 参数可不填写
	     */
		public function build_order_no2($db,&$no)
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
	     * 生成唯一号 ( 最终支持版，递归方式 )
	     * @param $db : 数据表姓名
	     * @param $no : 参数可不填写
	     */
		public function build_order_no3($db,$no)
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

	    /**
	     * 跳转链接
	     */
	    public function trulLogin()
	    {
	    	$url = './index.php?g=Wap&m=Userinfo&a=login';
	    	echo '<script type="text/javascript">alert("您未登录，正在跳转到登录页面");window.location.href = "'.$url.'"; </script>';
	    }

    	/**
    	  * 从1转到一，二，三方法...
    	  * @param $list : 二维数组
    	  */
    	public function getvideo($list = array())
    	{
	    	foreach ($list as $k => $v) {
	    		$kpai = array(1,2,3,4,5,6,7,8,9);
	    		$kpaiQ = array('二','三','四','五','六','七','八','九','十');
				// 判断是否是$k = 0（第一名）
	    		if($k == 0)
	    		{
	    			$kpaiRe = str_replace('0', '一', $k);
	    		}else{
	    			$kpaiRe = $this->numToWord($k);
	    		}
	    		$list[$k]['paixu'] = $kpaiRe;
	    		$list[$k]['kp'] = $k;
	    	}

	    	return $list;
    	}

		/**
		* 把数字1-1亿换成汉字表述，如：123->一百二十三
		* each : 以上的$this->getvideo();
		* @param [num] $num [数字]
		* @return [string] [string]
		*/
		public function numToWord($num)
		{
			$chiNum = array('一', '二', '三', '四', '五', '六', '七', '八', '九');
			$chiUni = array('','十', '百', '千', '万', '亿', '十', '百', '千');
			$chiStr = '';

			$num_str = (string)$num;
			$count = strlen($num_str);
			$last_flag = true; //上一个 是否为0
			$zero_flag = true; //是否第一个
			$temp_num = null; //临时数字

			$chiStr = '';//拼接结果
			if ($count == 2) {//两位数
				$temp_num = $num_str[0];
				$chiStr = $temp_num == 1 ? $chiUni[1] : $chiNum[$temp_num].$chiUni[1];
				$temp_num = $num_str[1];
				$chiStr .= $temp_num == 0 ? '' : $chiNum[$temp_num]; 
			}else if($count > 2){
				$index = 0;
				for ($i=$count-1; $i >= 0 ; $i--) { 
					$temp_num = $num_str[$i];
					if ($temp_num == 0) {
						if (!$zero_flag && !$last_flag ) {
							$chiStr = $chiNum[$temp_num]. $chiStr;
							$last_flag = true;
						}
					}else{
						$chiStr = $chiNum[$temp_num].$chiUni[$index%9] .$chiStr;
						$zero_flag = false;
						$last_flag = false;
					}
					$index ++;
				}
			}else{
				$chiStr = $chiNum[$num_str[0]]; 
			}
			return $chiStr;
		}
		
		/**
	     * 生成唯一号 ( 查询二维数组最终支持版，递归方式 )
		 * 作用 ： 用来查询所有数据（二维数组），进行随机抽奖
	     * @param $db : 数据表姓名
	     * @param $no : 参数可不填写
	     */
		public function sn($db,$no)
			{
			global $no;
			$data = M('data')->order('start asc')->select();
			$rdata = array_rand($data,1);
			$a = mt_rand($data[$rdata]['start'],$data[$rdata]['num_end']);
			$a = sprintf("%07d",$a);
			$no = $a;
			$where['num'] = $no;
			// echo json_encode(array($no));exit;
			$info = M("$db")->field('id,num')->where($where)->select();
			if(!empty($info) && $no)
			{
				$this->sn($db,$no);
			}
			return $no;
		}

}