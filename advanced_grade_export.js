var selectedOptions = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

(function ()
{
	var orders = document.getElementsByTagName('select');
	for (i = 0;i < orders.length;i++)
	{
		console.log(orders[i]);
		if (orders[i].id !== undefined && orders[i].id.indexOf('order') >= 0)
		{
			console.log(orders[i]);
			selectedOptions[orders[i].selectedIndex] = orders.id;
		}
	}
	set_order();

})();

function set_order()
{
	items = document.getElementsByTagName('table');
	for (var i = 0; i < items.length; i++)
	{
		if (items[i].className == "grade_elements")
		{
			child_node(items[i].childNodes);
		}
	}
}

function child_node(element) {
	for (var j = 0;j < element.length;j++)
	{
		if (element[j].className == "fitemtitle") {
			element[j].style.width = "200px";
			element[j].style.textAlign = "left";
		}
		else if (element[j].tagName == "SELECT") {
			element[j].style.display = "none";
		}
		else if (element[j].childElementCount > 0) {
			if (ifie())
			{
				if (element[j].className == "felement fcheckbox")
				{
					element[j].style.width = "20px";
				}
			}
			child_node(element[j].childNodes);
		}
	}		
}

if (!document.addEventListener)
	document.attachEvent('onclick', clickEvent); 
else
	document.addEventListener('click', clickEvent, false); 

function clickEvent(e)
{
	if ((e.target.id.indexOf("id_itemids") + 1) && (e.target.tagName == "INPUT")) 
		select_show(e.target);
}

function select_show(e)
{
	var el = "id_sel_" + e.id.substr(3);
	if (e.checked) 
		document.getElementById(el).style.display = "";
	else {
		document.getElementById(el).style.display = "none";
		document.getElementById(el).options[0].selected = true;
		deleteObsolete(el);
	}
}

function ifie()
{
	var ua = navigator.userAgent.toLowerCase();
	if (ua.indexOf("msie") != -1 && ua.indexOf("opera") == -1)
	{
		return true;
	}
	return false;
}

function choosedOpts(e) 
{
	var index = e.selectedIndex;
	var lastInd = deleteObsolete(e.id);
	if (selectedOptions[index] === 0 || index === 0)
		selectedOptions[index] = e.id;
	else
	{
		for (var i = 1;i < selectedOptions.length;i++)
		{
			if (selectedOptions[i] === 0 && i != lastInd) {
				e.options[i].selected = true;
				selectedOptions[i] = e.id;
				break;
			}
		}
	}
}

function deleteObsolete(id)
{
	for (var i = 1;i < selectedOptions.length;i++)
		if (selectedOptions[i] == id) {
			selectedOptions[i] = 0;
			return i;
		}
	return 0;
}
