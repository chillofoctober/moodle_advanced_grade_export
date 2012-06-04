function set_order()
{
	items=document.getElementsByTagName('table');
	for(var i=0; i<items.length; i++)
	{
		if (items[i].className=="grade_elements")
		{
			child_node(items[i].childNodes);
		}
	}
}

function child_node(element){
 for (var j=0;j<element.length;j++)
 {
	 if (element[j].className=="fitemtitle") {
		 element[j].style.width="200px";
		 element[j].style.textAlign="left";
	 }
	 else if (element[j].tagName=="SELECT") {
		 element[j].style.display="none";
	 }
	 else if (element[j].childElementCount>0) {
		 if (ifie())
		 {
			 if (element[j].className=="felement fcheckbox")
			 {
				 element[j].style.width="20px";
			 }
		 }
		 child_node(element[j].childNodes);
	 }

}		
}

if (!document.addEventListener)
 { document.attachEvent('onclick',clickEvent); }
else
 { document.addEventListener('click',clickEvent,false); }

function clickEvent(e)
{
	if ((e.target.id.indexOf("id_itemids")+1)&&(e.target.tagName=="INPUT")) { select_show(e.target); }
}

function select_show(e)
{
	var el="id_sel_"+e.id.substr(3);
	e.checked ? document.getElementById(el).style.display="" : document.getElementById(el).style.display="none";
}

function ifie()
{
	var ua = navigator.userAgent.toLowerCase();
	if (ua.indexOf("msie")!=-1 && ua.indexOf("opera")==-1)
	{
		return true;
	}
}

//fitemtitle
//fitem_checkbox
/*
 element[0].childNodes[j].className="fitemtitle"

 */