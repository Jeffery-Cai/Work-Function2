<?php
if($_POST)
{
    $base64_image_content = $_POST['va'];
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
        $type = $result[2];
        $time = time();
        $new_file = "upload/{$time}.{$type}";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            echo '新文件保存成功：', $new_file;
        }

    }
}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta http-equiv="content-type" content="text/html;charset=utf-8">
</head>
<style>

.btn-upload {
  width: 106px;
  height: 32px;
  position: relative;
  margin-bottom: 10px;
}
.btn-upload a {
  display: block;
  width: 104px;
  line-height: 18px;
  padding: 6px 0;
  text-align: center;
  color: #4c4c4c;
  background: #fff;
  border: 1px solid #cecece;
}
.btn-upload input {
  width: 106px;
  height: 32px;
  position: absolute;
  left: 0px;
  top: 0px;
  z-index: 1;
  filter: alpha(opacity=0);
  -moz-opacity: 0;
  opacity: 0;
  cursor: pointer;
}
.icon-upload {
  display: inline-block;
  width: 17px;
  height: 17px;
  background: url(./icons.png) -78px 0 no-repeat;
  vertical-align: middle;
  margin-right: 5px;
  background-position: -144px -24px;
}

</style>
<body>

<div class="control-group js_uploadBox">
    <label>商品封面：</label>
    <div class="btn-upload">
      <a href="javascript:void(0);"><i class="icon-upload"></i><span class="js_uploadText">上传</span>商品</a>
      <input class="js_upFile" type="file" name="cover">
    </div>
    
    <div class="js_showBox "></div>
</div>
<script type="text/javascript" src="https://cdn.bootcss.com/jquery/2.2.2/jquery.min.js"></script>
<script type="text/javascript" src="jquery.uploadView.js"></script>

	<script>
$(".js_upFile").uploadView({
	uploadBox: '.js_uploadBox',//设置上传框容器
	showBox : '.js_showBox',//设置显示预览图片的容器
	width : 100, //预览图片的宽度，单位px
	height : 100, //预览图片的高度，单位px
	allowType: ["gif", "jpeg", "jpg", "bmp", "png"], //允许上传图片的类型
	maxSize :2, //允许上传图片的最大尺寸，单位M
	success:function(e){
//		是否base64流图像转换成头像路径
		var va = $(".js_showBox").find('img').prop('src');
		$.post('',{va:va},function(d){

		},'json');
		$(".js_uploadText").text('更改');
		alert('图片上传成功');
	}
});
</script>

</body>
</html>