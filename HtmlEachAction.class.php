<?php
/**
* HTML + thinkphp帮助例子
* @help : 本案例都是完全由thinkphp编写
* @author : Jeffery
* creattime : 2016-7-6
* @help-email : 1345199080@qq.com
*/
class HhtmlEachAction extends Action
{
	/**
	 * 批量生成随机不重复用户的抽奖码
	 */
	public function set中奖()
	{
		$num = intval($_POST['num']);
		if($num != '')
		{
			$username = M('choujiang')->where($where)->getField('username',true);
			if($username!='')
			{
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
                        $is_au = M('choujiang')->where(array('username'=>array('in',$username),'is_choose'=>1))->getField('id',true);
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


	/**
	 * 门店订单导出最终支持所有版本的excel表
	 */
	public function excel最终支持版()
	{
		$filename='md_order'.date('Ymd',time());
		header("Content-type:application/vnd.ms-excel"); 
		header("Content-Disposition:attachment;filename=".$filename.".xls");
		$orders = M('db')->order('addtime desc')->select();
		$arr = array();
		$data = array();
		if(!empty($orders)){
			foreach ($orders as &$v) {
				// 编写你的代码 ....... 
			}
		}else{
			return array();
		}

		$this->assign('list',$orders);
		$this->display();

		/**
		 * 相对应的html代码
		 */
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
		                  <td class=xl2216681 nowrap>所在省份</td>
		                  <td class=xl2216681 nowrap>提交时间</td>
		                 </tr>
		                 <volist name="list" id="v">
		                  <tr>
		                    <td class=xl2216681 nowrap>{lanrain:$v['id']}</td>
		                    <td class=xl2216681 nowrap>{lanrain:$v['sheng']}</td>
		                  </tr>
		                  </volist>
		             </table> 
		         </div> 
		     </body> 
		 </html> 
	}

	/**
	 * 在线客服聊天 - ajax
	 */
	public function set聊天()
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

		/**
		 * 相对应的html代码
		 */
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
	}




/******************   副增  ***************/
/**********************************************************
		 * li变单选
		 */
		<dd id="xm" value="radio">
			<ul>
				<li class="but1 checked" value="1">男单</li>
				<li class="but1" value="2">男双</li>
			</ul>
			<ul>
				<li class="but1 color-8309ff" value="3">女单</li>
				<li class="but1 color-8309ff" value="4">女双</li>
			</ul>
			<ul>
				<li class="but1 color-ffce09" value="5">混双</li>
			</ul>
			<input type="hidden" id="ul1_value" value="1">
		</dd>

		// js
		$("#xm li").click(function(){
			$(this).toggleClass("checked");
			$("#xm li[value!='"+ $(this).attr("value")+"']").removeClass("checked");

			if($(this).attr("class").indexOf("checked") > 0){
				ul1_value = $(this).attr("value");
			}else{
				ul1_value = 1;
			}
			$("#ul1_value").val(ul1_value);
			ul1_value = "";
		});

/**********************************************************
		 * 微信预览图片接口js
		 */
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
		if( window.addEventListener ){
			window.addEventListener('load', function() {
                var imgs = $('#pb1').find("img"),//查找要放大的图片
                imgsSrc = [],
                minWidth = 0;;
                for( var i=0,l=imgs.length; i<l; i++ ){
                	var src = imgs[i].src;
                	if( src ){
                		imgsSrc.push(src);
                		(function(src){
                			imgs[i].addEventListener('click', function(){
                				reviewImage(src);
                			});
                		})(src);
                	}
                }
                function reviewImage(src) {
                	if (typeof window.WeixinJSBridge != 'undefined') {
                		WeixinJSBridge.invoke('imagePreview', {
                			'current' : src,
                			'urls' : imgsSrc
                		});
                	}
                }
            }, false);
		}

/************   匹配出菜单,添加a新窗口 ****************/
        	var turl = $("#Menu dd ul li").eq(4).find('a');
        	// console.log(turl.attr('href'));
        	if(turl.attr('href') == 'index.php?g=User&m=Winning&a=cj&token=oieigr1387606536')
        	{
        		turl.parent().html('<a href="index.php?g=User&m=Winning&a=cj&token=oieigr1387606536"  target="_blank">好礼大抽奖</a>');
        	}
		
		
/**********************************************************
		 * 抽奖（类似于摇手http://assets.jq22.com/plugin/pc-aee06d36-2d96-11e4-8b02-000c29f61318.png）
	     * 生成唯一号 ( 最终支持版，递归方式 )
	     * @param $db : 数据表姓名
		 * @help : 通过号段进行抽奖
		 * @each : 000015  -  001000（抽取一个出来，并且这个号段显示已经被抽奖）
	     * @param $no : 参数可不填写
	     */
		public function sn($db,$no)
		{
			global $no;
			$data = M('haoduan')->where(array('is_use'=>0))->order('num_start asc')->select();
			$rdata = array_rand($data,1);
			$a = mt_rand($data[$rdata]['num_start'],$data[$rdata]['num_end']);
			$a = sprintf("%07d",$a);
			$no = $a;
			$where['num'] = $no;
			// echo json_encode(array($no));exit;
			$info = M("$db")->field('id,num')->where($where)->select();
			if(!empty($info) && $numo)
			{
				$this->sn($db,$no);
			}
			return $no;
		}
		
		// 抽奖
		public function ajaxCj()
		{
			/* 总共抽5次
			*/

			// 判断是否已经抽了5次了
			$count = M('winning_result')->count();
			if($count >=5)
			{
				echo json_encode(array(3));exit;
			}
			$sjnum = $this->sn('winning_result');

			// 出现$sjnum比号段小于
			$wdata['is_use'] = 0;
			$wdata['num_start'] = array('elt',$sjnum);
			$wdata['num_end'] = array('egt',$sjnum);

			$haoduanData = M('winning_haoduan')->where($wdata)->select();
			if(empty($haoduanData))
			{
				// 这里得判断当$sjnum  永远地出现在foreach->$haoduanData中的num_start 和 num_end
				// 的话，就继续生成$sjnum = $this->sn('winning_result')，生成到不小于为止
				echo json_encode(array(4,$sjnum));exit;
			}
			foreach ($haoduanData as $k => $v) {
					// 进入到指定的号段成功抽奖
					M('winning_haoduan')->where(array('id'=>$v['id']))->save(array('is_use'=>1));

					$reData = array(
						'num' => $sjnum,
						'addtime' => time(),
						'haoduan_id' => $v['id']
						);
					 $ids = M('winning_result')->add($reData);
					 // 判断
					 $reids = $ids;
			}
			if($reids >= 1)
			{
				echo json_encode(array(1,$sjnum));exit;
			}else{
				echo json_encode(array(2,$sjnum));exit;
			}
			
			/* html each */
			/* html 
				<div class="cj">
				<volist name="info" id="v" key='k'>
				<ul><li>{lanrain:$v.num}</li></ul>
				</volist>
				</div>
			*/
			// 随机抽奖效果  js 
			  var g_Interval = 1;
			  var g_PersonCount = '5000000';
			  var g_Timer;
			  var running = false;
			  var x=0;
			  function beginRndNum(trigger){
				if(running){
				  running = false;
				  $(trigger).html("开始");
				  clearTimeout(g_Timer);
				  $('#ResultNum').css('color','red');
				  var li122 = ".cj>ul>li.li"+x;
				  $(li122).html(sn);
				}
				else{
				  var submit = {

				  };
				  $.post("./index.php?g=User&m=Winning&a=ajaxCj",submit,function(data){
					if(data[0] == 1)
					{
						running = true;
						sn = data[1];
						x=x+1;
						$(".cj").append('<ul class="ul'+x+'"><li class="li'+x+'"></li></ul>');
						beginTimer();
					}else if(data[0] == 3)
					{
						$('#ResultNum').html('');
						alert('您已经抽了5次奖了');
					}else if(data[0] == 4)
					{
						$('#ResultNum').html('');
						alert('号段已经全部抽奖完');
						return false;
					}else{
					  // 请稍后再试
					  alert('请稍后再试');return false;
					  // window.location.href = '';
					}

				  },'json');
				  $(trigger).html("停止");
				}
			  }

			  function updateRndNum(){
				var num = Math.floor(Math.random()*g_PersonCount+1);
				var li1 = ".cj>ul>li.li"+x;
				$(li1).html(num);
			  }

			  function beginTimer(){
				g_Timer = setTimeout(beat, g_Interval);
			  }

			  function beat() {
				g_Timer = setTimeout(beat, g_Interval);
				updateRndNum();
			  }
		}
		
		
	/* 按照首字母排序 
		终结版
		可参照BasicsAction.class.php中的函数
	*/
	public function 首字母()
	{
		$list = array(array());  //二维数组 
		//取首字母排序  // BasicsAction.class.php  中的排序函数
        foreach ($list as $k => $v) {
               $list[$k]['sort'] = strtolower($this->_getFirstCharter($v['name'])); //取出汉字的第一个首字母
        }

         $list = $this->sortarr($list,order_sn,SORT_ASC);    // BasicsAction.class.php  中的排序函数
         $list =  array_merge($list);
		 return $list;
	}
	
	/* 删除当前图片路径 */
	public function delImgUrlIndex()
	{
		$imgname = trim($_POST['imgname']);  // 判断图片是否为空
		if(empty($imgname))
		{
			echo json_encode(array(2));exit;
		}
		$oldpath = $imgname;//原路径
		if(file_exists($oldpath)){
			$status = M('table')->where(array('id'=>trim($_POST['id'])))->save(array('touxiang'=>''));
			unlink($oldpath);//删除文件
			echo json_encode(array(1,$status));exit;
			clearstatcache();//清空缓存
		}else{
			$status = M('table')->where(array('id'=>trim($_POST['id'])))->save(array('touxiang'=>''));
			echo json_encode(array(2));exit;
		}
	}
}
