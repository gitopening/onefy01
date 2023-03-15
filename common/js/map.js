// JavaScript Document
/* ======================================================================================
下面的函数在用户点击表单中的“插入地图”时打开地图窗口。
您可以配制下面的各种参数：CID,width,height等参数需要您在部署代码时设置好；而name等参数值将从网页表单中
读取；如果您传递了NID参数的值，将修改此NID代表的标点，而不添加新的标点（参考说明文档）。                          
====================================================================================== */
function insertMap() {
    var url = "proxy.php?api=template1000&CID=anleye&tid=tid1000&width=600&height=400&zoom=10&control=2		   &cityName="+encodeURIComponent(document.getElementById("cityName").value)+"&nid="+document.getElementById("nid").value+
          "&name="+encodeURIComponent(document.getElementById("name").value)+
          "&address="+encodeURIComponent(document.getElementById("address").value)+
          "&phone="+encodeURIComponent(document.getElementById("phone").value);
	var winname = window.open(url, '_blank');
  }


/* ======================================================================================
点击“提交”按钮后，程序会自动调用setNid方法把自动生成的nid传进来，用户可以自己在方法里添加代码把nid保存起来                          
====================================================================================== */
function setNid(nid) {
    document.getElementById("nid").value = nid;
  }