/*
	@author:Jason Yonk;
	@created:21/11/2006
*/
$F = function(o){
	var rVal = "";
	if($("[@name="+o+"]")[0].tagName.toLowerCase() == "select"){
		$("[@name="+o+"]").children().each(function(i){if(this.selected)rVal += $(this).val()+",";});
		(rVal = rVal.split(",")).pop();
	}
	else if($("[@name="+o+"]").attr("type") == "checkbox" || $("[@name="+o+"]").attr("type") == "radio"){
		$("input[@name="+o+"]:checked").each(function(){rVal += this.value+","});
		(rVal = rVal.split(",")).pop();
	}
	else{
		rVal = $("[@name="+o+"]").val();
	}
	return rVal;
};

jQuery.fn.hash = function(ke,va){
	var hash = [];
	if(ke != null){
		$(this).each(function(){hash[$(this).attr(ke)] = ((va != null)? $(this).attr(va) : this);});
	}
	else{
		$(this).each(function(){hash[this] = this;});
	}
	return hash;
};

$FF = function(o,serial){
	var newVal = $("[@name="+o+"]").children().hash("name");
	var ar = "";
	if(serial){
		for(var i in newVal) ar += "&"+newVal[i].name+"="+$F(newVal[i].name);
	}
	else{
		ar = [];
		for(var i in newVal) ar[ar.length] = $F(newVal[i].name);
	}
	return ar;
};