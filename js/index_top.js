function RL_H_Set(elms, id){
	var cur = elms.id.replace(/-h/ig,"");
	for(var i=1;i<3;i++){
		document.getElementById("RL-Item-"+i).style.display="none";
		document.getElementById("RL-Item-H-"+i).className="";
	}
	elms.className = "selected";
	document.getElementById(cur).style.display="block";
}