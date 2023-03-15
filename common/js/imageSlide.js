// JavaScript Document
var sPicArr = new Array();
var gIndex  = new Array();
var sid  = new Array();
var timeout = 6000;
var isIe = ('Microsoft Internet Explorer' == navigator.appName);
function initSlide(name,timeout1) {
	sPicArr[name] = new Array();
	gIndex[name]  = 0;
	sid[name] = null;
	if (timeout1) {
		timeout = timeout1;
	}
}
/*
content = new Array(图片路径,标题，URL);
*/
function initData(name, index, content) {
	sPicArr[name][index] = content;
	
}

function initFirst(name) {
	document.images[name + '_pic'].src = sPicArr[name][0][0];
	document.images[name + '_pic'].alt = sPicArr[name][0][1];
	$(name + '_picLink').href 		   = sPicArr[name][0][2];
	$(name + '_title').innerHTML 	   = sPicArr[name][0][1];
	$(name + '_title').href 		   = sPicArr[name][0][2];
}

function $(objName) {
	if(document.getElementById) {
		return eval('document.getElementById("' + objName + '")');
	} else if (document.layers) {
		return eval("document.layers['" + objName +"']");
	}else{
		return eval('document.all.' + objName);
	}
}
var plPic = new Image();
function SlidePic(name,index){
	gIndex[name] = index;

	if (isIe) {
		//document.images[name + '_pic'].filters.item(0).Transition = 12;
		document.images[name + '_pic'].filters.item(0).Apply();
	}

	document.images[name + '_pic'].src = sPicArr[name][index][0];
	document.images[name + '_pic'].alt = sPicArr[name][index][1];
	$(name + '_picLink').href 		   = sPicArr[name][index][2];
	$(name + '_title').innerHTML 	   = sPicArr[name][index][1];
	$(name + '_title').href 		   = sPicArr[name][index][2];

	if((index+1)<sPicArr[name].length) {
		plPic.src = sPicArr[name][index+1][0];//preload;
	}

	if (isIe)	{
		document.images[name + '_pic'].filters.item(0).play();
	}
}

function NextPic(name) {
	gIndex[name] = ((gIndex[name]+1)>=sPicArr[name].length ? 0 : (gIndex[name]+1));
	SlidePic(name, gIndex[name]);
}

function PrevPic(name) {
	gIndex[name] = ((gIndex[name]-1)<0 ? (sPicArr[name].length-1) : (gIndex[name]-1));
	SlidePic(name, gIndex[name]);
}

function inislide(name) {
	if(sid[name]==null) {
		sid[name] = setInterval('NextPic("'+name+'")', timeout);
	}
}
function pauseslide(name) {
	clearInterval(sid[name]);
	sid[name] = null;
}
