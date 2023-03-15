function changeSearch(formDom){
	location.href=formDom.value;
}
function quickSearch(domValue,domType){
	var inputDom = document.getElementById(domType);
	if(inputDom){
		inputDom.value=domValue;
	}
    var priceValue = document.getElementById('price').value;
	var roomValue = document.getElementById('room').value;
	var cityareaValue = document.getElementById('cityarea').value;
	var boroughValue = document.getElementById('borough').value;
	if(domType == 'cityarea') boroughValue = '';
	/*
	这里的搜索不包扩关键字搜索
	*/
	var qValue = document.getElementById('S_q').value;
	if(qValue == "可输入小区名、路名或划片学校"){
		qValue ="";
	}
	var typeValue = document.getElementById('type').value;
	var featureValue = document.getElementById('feature').value;
	var switchValue = document.getElementById('switch').value;
	var totalareaValue = document.getElementById('totalarea').value;
	var list_numValue = document.getElementById('list_num').value;
	var list_orderValue = document.getElementById('list_order').value;
        var showstyleValue = document.getElementById('showstyle').value;
		 var pagenoValue = document.getElementById('pageno').value;

	location.href='index.php?q='+qValue+'&price='+priceValue+'&switch='+switchValue+'&room='+roomValue+'&cityarea='+cityareaValue+'&borough='+boroughValue+'&feature='+featureValue+'&type='+typeValue+'&totalarea='+totalareaValue+'&pageno='+pagenoValue+'&list_num='+list_numValue+'&list_order='+list_orderValue+'&showstyle='+showstyleValue;
	return false;
}
function dj(i)
{
  for(j=1;j<6;j++)
    {
  if(i==j){
   document.getElementById("a"+j).className="li2";
   document.getElementById("b"+j).style.display="";
          }
   else
    {
   document.getElementById("a"+j).className="li1";
   document.getElementById("b"+j).style.display="none";
      }
     }

}

function fg(r)
{
  for(t=1;t<6;t++)
    {
  if(r==t){
   document.getElementById("c"+t).className="lid";
   document.getElementById("d"+t).style.display="";
          }
   else
    {
   document.getElementById("c"+t).className="lic";
   document.getElementById("d"+t).style.display="none";
      }
     }

}

function cancelProp(prop){
		var inputDom = document.getElementById(domType);
	if(inputDom){
		inputDom.value=domValue;
	}
	    var priceValue = document.getElementById('price').value;
	var roomValue = document.getElementById('room').value;
	var cityareaValue = document.getElementById('cityarea').value;
	var boroughValue = document.getElementById('borough').value;
	if(domType == 'cityarea') boroughValue = '';
	/*
	这里的搜索不包扩关键字搜索
	*/
	var qValue = document.getElementById('q2').value;
	if(qValue == "可输入小区名、路名或划片学校"){
		qValue ="";
	}
	var typeValue = document.getElementById('type').value;
	var featureValue = document.getElementById('feature').value;
	var switchValue = document.getElementById('switch').value;
	var totalareaValue = document.getElementById('totalarea').value;
	var list_numValue = document.getElementById('list_num').value;
	var list_orderValue = document.getElementById('list_order').value;
    var showstyleValue = document.getElementById('showstyle').value;
	var pagenoValue = document.getElementById('pageno').value;
	var house_ageValue = document.getElementById('house_age').value;	
	var house_floorValue = document.getElementById('house_floor').value;	
	var companyValue = document.getElementById('company').value;	
switch (prop)
   {
   case 'q':
     qValue=''
     break
   case 'room':
     roomValue=''
	 break	
   case 'cityarea':
     cityareaValue=''
	 break
   case 'price':
     priceValue=''
	 break	 
   case 'type':
     typeValue=''
	 break
   case 'feature':
     featureValue=''
     break
   case 'switchValue':
     switchValue=''
     break  
   case 'totalarea':
     totalareaValue=''
     break 
   case 'list_num':
     list_numValue=''
     break
   case 'list_order':
     list_orderValue=''
     break
   case 'showstyle':
     showstyleValue=''
     break	
   case 'pageno':
     pagenoValue=''
     break
   case 'house_age':
     house_ageValue=''
     break
   case 'house_floor':
     house_floorValue=''
     break	 
   case 'company':
     companyValue=''
     break		 
}


	location.href='index.php?q='+qValue+'&price='+priceValue+'&switch='+switchValue+'&room='+roomValue+'&cityarea='+cityareaValue+'&borough='+boroughValue+'&feature='+featureValue+'&type='+typeValue+'&totalarea='+totalareaValue+'&pageno='+pagenoValue+'&list_num='+list_numValue+'&list_order='+list_orderValue+'&showstyle='+showstyleValue+'&house_age='+house_ageValue+'&house_floor='+house_floorValue+'&company='+companyValue;
	return false;
}