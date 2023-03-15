/*
*作者：吴家庚
*时间：2008-1-12
*功能：返回短信发送的状态，0为未发送，1为己发送
*/

var xmlHttp;

function createXMLHttpRequest()
{
	if (window.ActiveXObject)
	{
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTp");	
	}
	else if (window.XMLHttpRequest)
	{
		xmlHttp = new XMLHttpRequest();	
	}
}

function sendNote()
{
	document.getElementById("sendNote").innerHTML="<a href=\"javascript:sendNote();\"><img src='..\/themes\/default\/images\/aanniu_19.GIF\' border='0'><\/a>";
	document.getElementById("vtext").innerHTML="请注意查收短信，输入短信中的验证码";
	createXMLHttpRequest();
	var url = "SendNote.php";
	xmlHttp.onreadystatechange = handleStateChange;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

function handleStateChange()
{
	if (xmlHttp.readyState == 4)
	{
		if (xmlHttp.status == 200)
		{
			//alert(xmlHttp.responseText);
			//var SendInfo = xmlHttp.responseText;
		}
	}
}