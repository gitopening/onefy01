function RL_H_Set(elms){
	var cur = elms.id.replace(/-h/ig,"");
	for(var i=1;i<3;i++){
		document.getElementById("RL-Item-"+i).style.display="none";
		document.getElementById("RL-Item-H-"+i).className="";
	}
	elms.className = "selected";
	document.getElementById(cur).style.display="block";
}
var Is_H_S_S_Flag=false,O_H_S_S_All=null;;
function H_S_S_Click(){
	Is_H_S_S_Flag=Is_H_S_S_Flag?false:true;
	var all = O_H_S_S_All;
	for(var i=2;i<all.length;i++){all[i].style.display=(Is_H_S_S_Flag?"block":"none");}
}
function H_S_S_Init(){
	O_H_S_S_All = document.getElementById("Header-Search-Selecter").getElementsByTagName("div");
	var all=O_H_S_S_All;
	for(var i=2;i<all.length;i++){
		var o=all[i].style;o.display="none";
		all[i].onclick=function(){document.getElementById("showSelecterText").innerHTML=this.innerHTML;H_S_S_Click();}
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
function xz(y)
{ 
  for(p=1;p<6;p++)
    {
  if(y==p){
   document.getElementById("r"+p).className="liv";
   document.getElementById("h"+p).style.display="";
          }
   else
    {
   document.getElementById("r"+p).className="liq";
   document.getElementById("h"+p).style.display="none";
      }
     }
  
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
function nTabs(thisObj,Num){
if(thisObj.className == "active")return;
var tabObj = thisObj.parentNode.id;
var tabList = document.getElementById(tabObj).getElementsByTagName("li");
for(i=0; i <tabList.length; i++)
{
  if (i == Num)
  {
   thisObj.className = "active"; 
      document.getElementById(tabObj+"_Content"+i).style.display = "block";
  }else{
   tabList[i].className = "normal"; 
   document.getElementById(tabObj+"_Content"+i).style.display = "none";
  }
} 
}
function op(k)
{ 
  for(p=1;p<3;p++)
    {
  if(k==p){
   document.getElementById("m"+p).className="lit";
   document.getElementById("n"+p).style.display="";
          }
   else
    {
   document.getElementById("m"+p).className="lis";
   document.getElementById("n"+p).style.display="none";
      }
     }
  
}