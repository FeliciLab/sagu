/**
 *
 * @author Leovan Tavares da Silva [leovan@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Leovan Tavares da Silva [leovan@solis.coop.br]
 * Luís Felipe Wermann [luis_felipe@solis.com.br]
 *
 * @since
 * Class created on 17/07/2008
 *
 * \b @organization \n
 * SOLIS - Cooperativa de SoluÃ§Ãµes Livres \n
 *
 * \b Copyleft \n
 * Copyleft (L) 2005 - SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html )
 *
 * \b History \n
 * This function select and retun a value correctly from document javascript form 
 */

function showOrdemParams(result)
{
    xGetElementById('ordemParams').value = result;
    MIOLO_parseAjaxJavascript(result);
}

function setParametersCount()
{
    xGetElementById('parametersCount').value = 'Carregando...';
    //xGetElementById('divParametersCount').style.display = 'block';
    
    var sql = xGetElementById('sql').value;
    
    cpaint_call(xGetElementById('currentUrl').value, "POST", "setParametersCountValue", sql, showParametersCount, "TEXT");
}

function addParameter()
{
    xGetElementById('divParametersForm').innerHTML = 'Carregando...<img src="/images/loading.gif"/>';
    xGetElementById('divParametersForm').style.display = 'block';

    if (xGetElementById('lineNumber'))
    {
        xGetElementById('lineNumber').value = '';
    }
    
    var args = Array(xGetElementById('parametersCount').value, xGetElementById('gridData').value);
    
    cpaint_call(xGetElementById('currentUrl').value, "POST", "parameterInfo", args, showParameter, "TEXT");
}

function updateParameter(lineNumber)
{
    xGetElementById('divParametersForm').innerHTML = 'Loading...<img src="/images/loading.gif"/>';
    xGetElementById('divParametersForm').style.display = 'block';
    
    var gridData = xGetElementById('gridData').value;
    var args     = new Array(xGetElementById('parametersCount').value, gridData, lineNumber);
    
    cpaint_call(xGetElementById('currentUrl').value, "POST", "parameterInfo", args, showParameter, "TEXT");
}

function deleteParameter(lineNumber)
{
    xGetElementById('divParametersForm').innerHTML = 'Loading...<img src="/images/loading.gif"/>';
    xGetElementById('divParametersForm').style.display = 'block';
    
    cpaint_call(xGetElementById('currentUrl').value, "POST", "deleteParameterInfo", lineNumber, showDeleteParameter, "TEXT");
}

function deleteConfirmed(lineNumber)
{
    var gridData = xGetElementById('gridData').value;
    var args     = new Array(gridData, lineNumber);
    
    cpaint_call(xGetElementById('currentUrl').value, "POST", "deleteConfirmed", args, refreshGrid, "TEXT");
}

function showParametersCount(result)
{
    xGetElementById('parametersCount').value = result;
    MIOLO_parseAjaxJavascript(result);
}

function showParameter(result)
{
    xGetElementById('divParametersForm').innerHTML = result;
    MIOLO_parseAjaxJavascript(result);
    
    stopShowLoading();
    
    selectFieldType(xGetElementById('gridData').value);
}

function showDeleteParameter(result)
{
    div = document.getElementById('divDeleteParameter');
    divGrid = xGetElementById('divParametersGrid');
    
    if (div == null)
    {
        div = document.createElement("div");
        
        div.id = 'divDeleteParameter';
        div.className = 'm-container';
    }

    div.innerHTML = result;
    div.style.display = 'block';
    div.style.position = 'absolute';
    //div.style.align = 'center';
    div.style.width = '100%';
    div.style.heigth = '100%';
    div.style.verticalAlign = 'middle';
    
    div.style.top = findPosY(divGrid)+'px';
    div.style.left = findPosX(divGrid)+'px';
    div.style.zIndec = 10;
    
    //document.forms[0].appendChild(div);
    
    xGetElementById('divParametersForm').innerHTML = result;
    
    MIOLO_parseAjaxJavascript(result);
    stopShowLoading();
}

function selectFieldType(gridData)
{
    var fieldType = xGetElementById('fieldType');
    
    if (fieldType.value.length > 0 && fieldType.value !== "separador")
    {
        xGetElementById('divFieldTypeInfo').innerHTML = 'Loading...<img src="/images/loading.gif"/>';
        xGetElementById('divFieldTypeInfo').style.display = 'block';
        
        var args = null;
        
        if (gridData)
        {
            if (xGetElementById('lineNumber'))
            {
                args = new Array(fieldType.value, gridData, xGetElementById('lineNumber').value);
            }
            else
            {
                args = new Array(fieldType.value, gridData);
            }
        }
        else
        {
            args = new Array(fieldType.value, 'NULL');
        } 
        
        // Se for um campo personalizado
        if( tipoDeCampoIndicaCampoPersonalizado(args[0]) )
        {
            cpaint_call(xGetElementById('currentUrl').value, "POST", "configurarCampoPersonalizado", args, showFieldTypeInfo, "TEXT");
        }
        else
        {
            cpaint_call(xGetElementById('currentUrl').value, "POST", "fieldTypeInfo", args, showFieldTypeInfo, "TEXT");
        }
    }
    else
    {
        xGetElementById('divFieldTypeInfo').innerHTML = '';
        xGetElementById('divFieldTypeInfo').style.display = 'none';
    }
}

function autoPreencheRotuloSeCampoPersonalizado()
{
    var fieldType = document.getElementById('fieldType');
    
    // Se for um campo personalizado
    if( tipoDeCampoIndicaCampoPersonalizado(fieldType.value) )
    {
        var texto = fieldType.options[fieldType.selectedIndex].innerHTML;
        
        texto = texto.replace(/\*\s*/, "");
        
        document.getElementById('parameterLabel').value = texto;    
    }
    
}

function tipoDeCampoIndicaCampoPersonalizado(tipo)
{
    // A variável é deifinda no onload do formulário de relatórios genéricos
    return tipo.indexOf(window.PREFIXO_CAMPO_PERSONALIZADO) === 0;
    
}

function showFieldTypeInfo(result)
{
    xGetElementById('divFieldTypeInfo').innerHTML = result;
    MIOLO_parseAjaxJavascript(result);
    
    stopShowLoading();
}

function saveParameter(i)
{
    var parameterNumber;
    var parameterLabel;
    var fieldType;
    var defaultValue;
    var size;
    var fieldValidator;
    var hint;
    var fieldColumns;
    var fieldRows;
    var fixedOptions;
    var options;
    var help;

    if (xGetElementById('parameterNumber'))
    {
        parameterNumber = xGetElementById('parameterNumber').value;
    }
    
    if (xGetElementById('parameterLabel'))
    {
        parameterLabel = xGetElementById('parameterLabel').value;
    }
    
    if (xGetElementById('fieldType'))
    {
        fieldType = xGetElementById('fieldType').value;
    }
    
    if (xGetElementById('defaultValue'))
    {
        defaultValue = xGetElementById('defaultValue').value;
    }
    
    if (xGetElementById('size'))
    {
        size = xGetElementById('size').value;
    }
    
    if (xGetElementById('fieldValidator'))
    {
        fieldValidator = xGetElementById('fieldValidator').value;
    }
    
    if (xGetElementById('hint'))
    {
        hint = xGetElementById('hint').value;
    }
    
    if (xGetElementById('fieldColumns'))
    {
        fieldColumns = xGetElementById('fieldColumns').value;
    }
    
    if (xGetElementById('fieldRows'))
    {
        fieldRows = xGetElementById('fieldRows').value;
    }
    
    if (xGetElementById('fixedOptions_0'))
    {
        if (xGetElementById('fixedOptions_0').checked)
        {
            fixedOptions = xGetElementById('fixedOptions_0').value;
        }
    }
    
    if (xGetElementById('fixedOptions_1'))
    {
        if (xGetElementById('fixedOptions_1').checked)
        {
            fixedOptions = xGetElementById('fixedOptions_1').value;
        }
    }
    
    if (xGetElementById('options'))
    {
        options = escape(xGetElementById('options').value);
    }
    
    if (xGetElementById('help'))
    {
        help = xGetElementById('help').value;
    }
    
    var args = new Array(xGetElementById('gridData').value, 
                         parameterNumber, 
                         parameterLabel, 
                         fieldType, 
                         defaultValue, 
                         size, 
                         fieldValidator, 
                         hint, 
                         fieldColumns, 
                         fieldRows, 
                         fixedOptions, 
                         options, 
                         help, 
                         i);
    
    cpaint_call(xGetElementById('currentUrl').value, "POST", "saveParameter", args, refreshGrid, "TEXT");
}

function refreshGrid(result)
{
    xGetElementById('divParametersForm').innerHTML = '';    
    xGetElementById('divParametersGrid').innerHTML = 'Loading...<img src="/images/loading.gif"/>';
    xGetElementById('divParametersGrid').style.display = 'block';
    
    xGetElementById('gridData').value = result;
  
    cpaint_call(xGetElementById('currentUrl').value, "POST", "refreshGrid", result, refreshGridResult, "TEXT");
}

function refreshGridResult(result)
{
    xGetElementById('divParametersGrid').innerHTML = result;
    MIOLO_parseAjaxJavascript(result);
    
    stopShowLoading();
}
