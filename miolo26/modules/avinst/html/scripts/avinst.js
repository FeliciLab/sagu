function changeEvaluation()
{
    selection = dojo.byId("adminSelection_evaluationId");
    selectData = selection.options[selection.selectedIndex];
    if (selectData.text == "0")
    {
        alert("Formulário da nova avaliação");
    }
    else
    {
        document.location.href = url;
    }
}

function editEvaluation()
{
    selection = dojo.byId("adminSelection_evaluationId");
    selectData = selection.options[selection.selectedIndex];
    alert("Aciona URL para nova avaliação");
}

function checkDescriptive(element, descriptive)
{
    descriptiveElement = dojo.byId(descriptive);
    if (element.checked == true)
    {
        descriptiveElement.style.display = 'block';
        descriptiveElement.focus();
    }
    else if (element.checked == false)
    {
        descriptiveElement.style.display = 'none';
        descriptiveElement.value = '';
    }
}

function setFocus(id)
{
	if( id )
	{
		dojo.byId(id).focus();
	}
	else
	{
		fields = dojo.query('input,select,textarea'); // Pega apesa campos com as tags input,select,textarea
		
		for( c = 1; c <= fields.length; c++ )
		{
			if( ! (fields[c].className.indexOf("mReadOnly") != -1) ) // Se não for apenas leitura
			{
				fields[c].focus();
				break;
			}
		}
	}				
}