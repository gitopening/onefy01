//普通窗口
function openWindow(dialogName, pageUrl, width, height,  resizable) {
	try	{
		screenWidth	= screen.width ;
		screenHeight = screen.height ;
	} catch (e) {
		screenWidth	= 1024 ;
		screenHeight = 768 ;
	}
	var iTop  = (screenHeight - height) / 2 ;
	var iLeft = (screenWidth  - width)  / 2 ;
	
	if (!width) {
		width = screenWidth;
	}
	if (!height) {
		height = screenHeight;
	}
	if (!resizable) {
		resizable = true;
	}


	/*var sOption  = "location=no,menubar=no,scrollbars=auto,toolbar=no,dependent=yes,'',minimizable=no,modal=yes,alwaysRaised=yes" +
		",resizable="  + ( resizable ? 'yes' : 'no' ) +
		",width="  + width +
		",height=" + height +
		",top="  + iTop +
		",left=" + iLeft ;*/
	var sOption  = "location=no,menubar=no,scrollbars=yes,toolbar=no,dependent=yes,'',minimizable=no,modal=yes,alwaysRaised=yes" +
		",resizable="  + ( resizable ? 'yes' : 'no' ) +
		",width="  + width +
		",height=" + height +
		",top="  + iTop +
		",left=" + iLeft ;

	var	parentWindow = window ;
	
	var oWindow = parentWindow.open( pageUrl, 'Dialog_' + dialogName, sOption, true );
	
	if ( !oWindow )
	{
		alert('窗口被拦截，请关闭广告拦截工具！');
		return ;
	}
		
	oWindow.moveTo( iLeft, iTop ) ;
	oWindow.resizeTo( width, height ) ;
	oWindow.focus();
}
function OpenFileBrowserFck(type)
{
	if (!type) type = 'Image';
	url = '/cncmax/lib/FCKeditor/editor/filemanager/browser/default/browser.html?Type='+type+'&Connector=connectors/php/connector.php';
	try	{
		screenWidth	= screen.width ;
		screenHeight = screen.height ;
	} catch (e) {
		screenWidth	= 1024 ;
		screenHeight = 768 ;
	}
	openWindow('FCKBrowseWindow',url,screenWidth*0.7,screenHeight*0.7);
}

function SetUrl(url) {
	document.getElementById(document.getElementById('ofbn').value).value = url;
}

function OpenFileBrowser(elementName,type) {
	document.getElementById('ofbn').value = elementName;
	OpenFileBrowserFck(type)
}

document.write('<input id="ofbn" name="ofbn" type="hidden" value="" />');
