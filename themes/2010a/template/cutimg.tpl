<html>
<head>
<title>剪切头像</title>
<META http-equiv=Pragma content=no-cache>
<!--{$jsFiles}-->
<!--{$cssFiles}-->
<script language="Javascript">
var ImgWidth,ImgHeight;
function getImgSize(obj)        //检测图像属性  
{  
	FileObj=obj;  
		
	if(ImgObj.readyState!="complete")    //如果图像是未加载完成进行循环检测  
	{  
		setTimeout("getImgSize(FileObj)",500);  
		return false;  
	}  
	
	ImgFileSize=Math.round(ImgObj.fileSize/1024*100)/100;//取得图片文件的大小  
	ImgWidth=ImgObj.width            //取得图片的宽度  
	ImgHeight=ImgObj.height;        //取得图片的高度  
}

var ImgObj=new Image();  
ImgObj.src="<!--{$srcimg}-->"; 
getImgSize(ImgObj); 

$(function(){
	$('#cropbox').Jcrop({
		onChange: showPreview,
		onSelect: showPreview,
		aspectRatio: 100/124,
		minSize: [ 100, 124 ],
//		setSelect: [ 100, 100, 200, 224 ],
		onSelect: updateCoords
	});
	
});
function updateCoords(c)
{
	$('#x').val(c.x);
	$('#y').val(c.y);
	$('#w').val(c.w);
	$('#h').val(c.h);
};
function checkCoords()
{
	if (parseInt($('#w').val())) return true;
	alert('请先选取头像截取的位置');
	return false;
};
function showPreview(coords)
{
	if (parseInt(coords.w) > 0)
	{
		var rx = 100 / coords.w;
		var ry = 124 / coords.h;
		
		jQuery('#preview').css({
			width: Math.round(rx * ImgWidth) + 'px',
			height: Math.round(ry * ImgHeight) + 'px',
			marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			marginTop: '-' + Math.round(ry * coords.y) + 'px'
		});
	}
}

</script>
</head>
<body onBlur="window.focus()">
<div>
	<div style="float:left; margin:5px; width:400px; height:318px; overflow:hidden; background:#f8f8f8;">
		<p style="font-size:14px; font-weight:bold; color:#C9620F; margin-bottom:2px;">您上传的原图：</p>
		<p style="width:400px; height:300px; overflow:hidden;"><img src="<!--{$srcimg}-->" id="cropbox" /></p>
	</div>
	<div style="float:left; margin:5px; width:105px; height:318px; background:#f8f8f8;">
		<p style="font-size:14px; font-weight:bold; color:#C9620F; margin-bottom:2px;">剪切预览：</p>
		<p style="width:100px; height:124px; overflow:hidden;"><img src="<!--{$srcimg}-->" id="preview" /></p>
		<p style="margin-top:10px; font-weight:bold; color:#C9620F;">剪切方法：</p>
		<p style="margin-top:7px;">1、鼠标点住原图，拖动鼠标，画出大小适中的选取框；</p>
		<p style="margin-top:7px;">2、移动选取框至合适部位；</p>
		<p style="margin-top:7px;">3、查看预览效果；</p>
		<p style="margin-top:12px;">
			<form action="?action=cutimg" method="post" onSubmit="return checkCoords();">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" id="imgsrc" name="imgsrc" value="<!--{$srcimg}-->" />
			<input type="submit" class="cutimg_button" value="确定剪切" />
		</form>
		</p>
	</div>
</div>
</body>
</html>
