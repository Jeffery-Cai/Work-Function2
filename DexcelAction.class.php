<?php
/**
* 导入功能
* 只针对上传的csv后缀有效
* HTML + thinkphp帮助例子
* @help : 本案例都是完全由thinkphp编写
* @author : Jeffery
* creattime : 2016-7-6
* @help-email : 1345199080@qq.com
*/
class DexcelAction extends BaseAction
{
	/****************  住宅+车位+商铺  均为例子参考  **************/
    public $clearBth = 2;   

	// 住宅
	public function dr_zhuza()
	{
        $data['price'] ='合计';
        $data['price'] ='合计';
        $data['price'] ='合计';
        $data['price'] ='合计';
        $data['price'] ='合计';
        $this->importCsv('file',$data,'fee','index.php?g=User&m=s&a=s&token='.session('token'));
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
                $v['price'] = str_replace(',','',$v['price']);
                $v['price'] = str_replace(',','',$v['price']);
                $v['price'] = str_replace(',','',$v['price']);
                $v['price'] = str_replace(',','',$v['price']);
                $res = D("{$table}")->add($v);
			}
			if($res){
					$this->success("导入成功","{$url}");
			}else{
				  $this->error('导入失败');
			}
		}
	}


    /**
      * 住宅导出  （ 已经不支持低端excel版本，会出现乱码 ）
      * @help : 请使用HtmlEachAction.class.php中的
      * public function excel最终支持版() 函数
      */
    public function excute()
    {
        $filename='order'.date('Ymd',time());
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename.xls");
        $data='ID'."\t";
        $data='ID'."\t";
        $data='ID'."\t";
        $orders = D('data')->order('id desc')->select();
        if(!empty($orders)){
            // 导出数据
            foreach ($orders as $k => $v) {

                    $data.=$v['id']."\t";
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
}