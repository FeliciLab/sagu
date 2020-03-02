function getContractData(option)
{
	periodId   = xGetElementById('periodId').value;
	contractId = xGetElementById('selected_contract').value;
	
	var args = Array(contractId, periodId, option);
	
	xGetElementById('divContractData').innerHTML = 'Loading...<img src="/images/loading.gif"/>';
    xGetElementById('divContractData').style.display = 'block';
    
    cpaint_call(xGetElementById('currentUrl').value, "POST", "getContractData", args, showContractData, "TEXT");
    
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

function setSelectedContract(contractId)
{
	pastSelectedContract = xGetElementById('selected_contract').value;
	
	psContractButton = xGetElementById('buttonContract' + pastSelectedContract);
	sContractButton  = xGetElementById('buttonContract' + contractId);
	
	if (psContractButton)
	{
		psContractButton.className = 'm-button';
 	}
	
	sContractButton.className = 's-button-selected';
	
	xGetElementById('selected_contract').value = contractId;
	
	getContractData(xGetElementById('selected_tab').value);
}

function showContractData(result)
{
    xGetElementById('divContractData').innerHTML     = result;
    xGetElementById('divContractData').style.display = 'block';
    MIOLO_parseAjaxJavascript(result);
    
    stopShowLoading();
}
