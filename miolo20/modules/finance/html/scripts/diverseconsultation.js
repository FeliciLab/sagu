function getFinanceData(opt)
{
	showLoading();
	var personId = xGetElementById('personId').value;
	var args = Array(opt, personId);
    var selectedTab = xGetElementById('selectedTab');
	try
	{
 
		xGetElementById('button_' + selectedTab.value).className = 's-tabform-default';
		xGetElementById('button_' + opt).className = 's-tabform-selected';
	    xGetElementById('divFinanceData').innerHTML = 'Loading...<img src="/images/loading.gif">';
		selectedTab.value = opt;
		cpaint_call(xGetElementById('currentUrl').value, "POST", "getFinanceData", args, showFinanceData, "TEXT");
	}
	catch (e)
	{
		return false;
	}
}

function showFinanceData(result)
{
	xGetElementById('divFinanceData').innerHTML = result;
	MIOLO_parseAjaxJavascript(result);
	stopShowLoading();
}