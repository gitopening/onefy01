/**
自动添加分隔符的格式化输入框效果
当我输入金额的时候，自动在每三位加分隔符
**/
function formatnumber(fnumber,fdivide,fpoint,fround){
	var fnum = fnumber + ’’;
	var revalue="";
	if(fnum==null){
		for(var i=0;i<fpoint;i++)revalue+="0";
		return "0."+revalue;
	}
	fnum = fnum.replace(/^\sall|\sall$/g,’’);
	if(fnum==""){
		for(var i=0;i<fpoint;i++)revalue+="0";
		return "0."+revalue;
	}
	fnum=fnum.replace(/,/g,"");
	if(fround){
		var temp = "0.";
		for(var i=0;i<fpoint;i++)temp+="0";
		temp += "5";
		fnum = Number(fnum) + Number(temp);
		fnum += ’’;
	}
	var arrayf=fnum.split(".");
	if(fdivide){
		if(arrayf[0].length>3){
			while(arrayf[0].length>3){
				revalue=","+arrayf[0].substring(arrayf[0].length-3,arrayf[0].length)+revalue;
				arrayf[0]=arrayf[0].substring(0,arrayf[0].length-3);
			}
		}
	}
	revalue=arrayf[0]+revalue;
	if(arrayf.length==2&&fpoint!=0){
		arrayf[1]=arrayf[1].substring(0,(arrayf[1].length<=fpoint)?arrayf[1].length:fpoint);
		if(arrayf[1].length<fpoint)
		for(var i=0;i<fpoint-arrayf[1].length;i++)arrayf[1]+="0";
		revalue+="."+arrayf[1];
	}else if(arrayf.length==1&&fpoint!=0){
		revalue+=".";
		for(var i=0;i<fpoint;i++)revalue+="0";
	}
	return revalue;
}

/**
填写小写的金额 , 自动输入大写
**/
var stmp = "";
function nst(t)
{
	if(t.value==stmp) return;
	var ms = t.value.replace(/[^\d\.]/g,"").replace(/(\.\d{2}).+$/,"$1").replace(/^0+([1-9])/,"$1").replace(/^0+$/,"0");
	var txt = ms.split(".");
	while(/\d{4}(,|$)/.test(txt[0]))
	txt[0] = txt[0].replace(/(\d)(\d{3}(,|$))/,"$1,$2");
	t.value = stmp = txt[0]+(txt.length>1?"."+txt[1]:"");
	bbb.value = number2num1(ms-0);
}
function number2num1(strg)
{
	var number = Math.round(strg*100)/100;
	number = number.toString(10).split('.');
	var a = number[0];
	if (a.length > 12)
	return "数值超出范围！支持的最大数值为 999999999999.99";
	var e = "零壹贰叁肆伍陆柒捌玖";
	var num1 = "";
	var len = a.length-1;
	for (var i=0 ; i<=len; i++)
	num1 += e.charAt(parseInt(a.charAt(i))) + [["圆","万","亿"][Math.floor((len-i)/4)],"拾","佰","仟"][(len-i)%4];
	if(number.length==2 && number[1]!="")
	{
		var a = number[1];
		for (var i=0 ; i<a.length; i++)
		num1 += e.charAt(parseInt(a.charAt(i))) + ["角","分"][i];
	}
	num1 = num1.replace(/零佰|零拾|零仟|零角/g,"零");
	num1 = num1.replace(/零{2,}/g,"零");
	num1 = num1.replace(/零(?=圆|万|亿)/g,"");
	num1 = num1.replace(/亿万/,"亿");
	num1 = num1.replace(/^圆零?/,"");
	if(num1!="" && !/分$/.test(num1))
	num1 += "整";
	return num1;
}
/**demo
小写金额：<input type="text" name="aaa" onkeyup="nst(this)"><br>
大写金额：<input type="text" name="bbb" size=80>
**/