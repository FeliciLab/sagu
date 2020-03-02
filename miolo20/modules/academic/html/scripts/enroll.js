function getEnrollData(option)
{
	enrollId   = xGetElementById('enrollId').value;
	
	var args = Array(enrollId, option);
	
	xGetElementById('divLoading').innerHTML = 'Loading...<img src="/images/loading.gif"/>';
    xGetElementById('divLoading').style.display = 'block';
    
    cpaint_call(xGetElementById('currentUrl').value, "POST", "getEnrollData", args, showEnrollData, "TEXT");
    
    pastSelectedTab = xGetElementById('selected_tab').value;
    
    psTab = xGetElementById('button_' + pastSelectedTab);
    
    if (psTab)
	{
		psTab.className = 's-tabform-default';
	}
    
    sTab  = xGetElementById('button_' + option);
    
    sTab.className  = 's-tabform-selected';
    
    xGetElementById('selected_tab').value = option;
}

function showEnrollData(result)
{
    xGetElementById('divData').innerHTML     = result;
    xGetElementById('divData').style.display = 'block';
    xGetElementById('divLoading').innerHTML  = '';
    
    if (xGetElementById('note'))
    {
    	xGetElementById('note').focus();
   	}
    MIOLO_parseAjaxJavascript(result);
    
    stopShowLoading();
}