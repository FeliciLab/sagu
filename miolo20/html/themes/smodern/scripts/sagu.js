/**
 *
 * @author William Prigol Lopes [william@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * William Prigol Lopes     [william@solis.coop.br]
 * Eduardo Beal Miglioransa [eduardo@solis.coop.br]
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @since
 * Class created on 05/06/2006
 *
 * \b @organization \n
 * SOLIS - Cooperativa de Soluções Livres \n
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

/*******************************************
 *
 * FUNCOES RELATIVAS A POSICAO DE MOUSE
 * 
 ******************************************/

// Detect if the browser is IE or not.
// If it is not IE, we assume that the browser is NS.
var IE = document.all ? true : false;

// If NS -- that is, !IE -- then set up for mouse capture
if (!IE)
{
    document.captureEvents(Event.MOUSEMOVE);
}

// Set-up to use getMouseXY function onMouseMove
document.onmousemove = getMouseXY;

// Temporary variables to hold mouse x-y pos.s
var currentMouseX = 0;
var currentMouseY = 0;

// Main function to retrieve mouse x-y pos.s

function getMouseXY(e)
{
    if (IE)
    { // grab the x-y pos.s if browser is IE
        currentMouseX = event.clientX + document.body.scrollLeft;
        currentMouseY = event.clientY + document.body.scrollTop;
    }
    else
    {  // grab the x-y pos.s if browser is NS
        currentMouseX = e.pageX;
        currentMouseY = e.pageY;
    }  
    // catch possible negative values in NS4
    if (currentMouseX < 0)
    {
        currentMouseX = 0;
    }
    if (currentMouseY < 0)
    {
        currentMouseY = 0;
    }

    return true;
}

/**
 * Posiciona uma div existente para a posicao X e Y atual do mouse
 */
function adjustDivToMousePosition(divName)
{
    objDiv = document.getElementById(divName);
    
    objDiv.style.position = 'absolute';
    objDiv.style.top = currentMouseY + 'px';
    objDiv.style.left = currentMouseX + 'px';
}

/***************************************
 * FIM DE FUNCOES RELATIVAS A MOUSE
 ***************************************/


/*
 * Function to show or hide a specific element data
 */
function showElements( elementName, elementToThreat )
{
    var fields = document.getElementsByName( elementName );
    for (var i = 0; i<fields.length; i++)
    {
        if (fields[i].checked)
        {
            value = fields[i].value;
        }
    }
    document.getElementById( 'm_' + elementToThreat ).style.display = value == 'true' ? '' : 'none';
} 

/*
 * Function close window and reload te opener page
 */
function closeAndReload()
{
    window.close();
    window.opener.location.reload();
}

/*
 * Function to redirect the opener for a specific place and optionally close the main window
 */ 
function openGoAndExit(url, goClose)
{
   window.opener.frames["content"].location=url;
}

/*
 * Function to round a number specifying the decimals quantity
 */
function roundNumber(number, decimals)
{
    return Math.round(number*Math.pow(10,decimals))/Math.pow(10,decimals);
}

/*
 * Function to check if a field value is numeric
 */
function isNumeric(control)
{
    return (! isNaN(parseFloat(control.value))) && (parseFloat(control.value) == control.value)
}

/*
 * Function to get the mouse pointer position on click moment
 */
function getMousePos( ev )
{
    is_ie = ( /msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent) );

    var posX;
    var posY;
    
    if ( is_ie ) 
    {
        posY = window.event.clientY + document.body.scrollTop;
        posX = window.event.clientX + document.body.scrollLeft;
    } 
    else 
    {
        posY = ev.clientY + window.scrollY;
        posX = ev.clientX + window.scrollX;
    }
                                
    return new Array( posX, posY);
}

/******************************************
 * Functions to manipulate form elements
 ******************************************/
/*
 * Function to expand and/or retract a container
 */
function expandRetractContainer(elementId)
{
    if (document.getElementById(elementId).style.display != 'none')
    {
        document.getElementById(elementId).style.display = 'none';
    }
    else
    {
        document.getElementById(elementId).style.display = 'block';
    }
}

/*
 * Function to expand (show) the specified element. Useful when you want to
 * show a container which was previously hidden by a call to retractContainer.
 */
function expandContainer(elementId)
{
    document.getElementById(elementId).style.display = 'block';
}

/*
 * Function to retract (hide) the specified element. Useful when you want to
 * hide a container which was previously shown by a call to expandContainer.
 */
function retractContainer(elementId)
{
    document.getElementById(elementId).style.display = 'none';
}

/*
 * Function to switch between two images
 */
function changeElementImage(elementId, image1, image2)
{
    if (document.getElementById(elementId).src == image1)
    {
        document.getElementById(elementId).src = image2;
    }
    else
    {
        document.getElementById(elementId).src = image1;
    }
}

/*
 * Function to get the CSS left property (X coordinate) of an element 
 * (from http://blog.firetree.net/2005/07/04/javascript-find-position/)
 */
function findPosX(obj)
{
    var curleft = 0;
    
    if(obj.offsetParent)
    {
        while(1) 
        {
            curleft += obj.offsetLeft;
            
            if(!obj.offsetParent)
            {
                break;
            }
            
            obj = obj.offsetParent;
        }
    }
    else if(obj.x)
    {
        curleft += obj.x;
    }
        
    return curleft;
}

/*
 * Function to get the CSS top property (Y coordinate) of an element
 * (from http://blog.firetree.net/2005/07/04/javascript-find-position/)
 */
function findPosY(obj)
{
    var curtop = 0;

    if(obj.offsetParent)
    {
        while(1)
        {
            curtop += obj.offsetTop;

            if(!obj.offsetParent)
            {
                break;
            }
            
            obj = obj.offsetParent;
        }
    }
    else if(obj.y)
    {
        curtop += obj.y;
    }

    return curtop;
}

/**
 * Set initial focus
 * 
 */
function setInitialFocus(form)
{
    if ( form != null )
    {
        var found = false;
        var i = 0;
        
        for ( i=0; i<form.elements.length && !found; i++ )
        {
            if ( form.elements[i].type == 'select-one' || form.elements[i].type == 'text' )
            {
                found = true;
            }
        }
        
        if ( found )
        {
            form.elements[i].focus();
        }
    }
}

// disable all overflow properties of all divs, prints the document
// and reenable all div's overflow properties. 
function printEntireForm()
{
    // get all divs in document
    var divs = document.getElementsByTagName("div");
    
    // vectors to store all modified elements in order to set the correct properties back
    var elements = [];
    var props = [];
    var values = [];
    
    // iterate through all divs disabling overflow properties
    for ( var i=0; i<divs.length; i++ )
    {
        for ( var prop in divs[i].style )
        {
            if ( prop.indexOf('overflow') != -1 )
            {
                if ( divs[i].style[prop].length > 0 )
                {
                    // store a backup
                    elements.push(divs[i]);
                    props.push(prop);
                    values.push(divs[i].style[prop]);
                    // unset all overflows
                    divs[i].style[prop] = '';
                }
            }
        }
    }

    // print document
    window.print();

    // when focus goes back to current window, reenable overflows
    window.onfocus = function()
    {
        for ( var i=0; i<elements.length; i++ )
        {
            elements[i].style[props[i]] = values[i];
        }
    };
}


//Check or uncheck all checkboxes with html class="className"
//See example at modules/finance/grids/GrdDiverseConsultationPerson.class
 function checkOrUncheckAll(obj, className)
 {
    var elements = document.getElementsByTagName('input');
    for (i=0; i < elements.length; i++)
    {
        if (elements[i].className == className)
        {
            elements[i].checked = obj.checked;
        }
    }
}

function fileChange(obj)
{
    var cloneField = obj.cloneNode(false); //Clone o input de arquivo para replicar
    cloneField.value = '';
    cloneField.name = cloneField.name + '1';

    var parent = obj.parentNode; //Obtem a div que esta encobrindo este input
    
    parent.appendChild( cloneField ); //Adiciona o clone nesta div
}

function markFileAsDelete(divName, fileId, divDelName)
{
    if ( divDelName == undefined )
    {
        divDelName = 'fileDel';
    }

    var del = document.getElementById( divDelName );
    if ( del )
    {
        del.value  = del.value + fileId + ',';
    }


    var oldDiv = document.getElementById(divName + 'Old');
    if ( oldDiv )
    {
        oldDiv.value = '';
    }

    document.getElementById(divName + 'Del').value = 1;
    document.getElementById(divName + 'Rmv').innerHTML = '';
    document.getElementById(divName + 'File').style.display = 'block';
}

/* funcao a ser invocada ao click no action da grid */
function doIt(destination)
{
    /* pega a url que vem como parametro e concatena nela
     * o campo e o valor associado
     */
    if ( document.getElementById('commitmentTermType') ) //Utilizado na grid historico de termos de compromisso (GrdTrainingHistory)
    {
        window.location = destination + '&commitmentTermType=' + document.getElementById('commitmentTermType').value;
    }
    else if ( document.getElementById('reportFormat') ) //Utilizado em algumas grids para informar o formato do documento
    {
        window.location = destination + '&reportFormat=' + document.getElementById('reportFormat').value;
    }
    else if ( (!document.getElementById('generateOption_optPdf')) || ((document.getElementById('generateOption_optPdf').checked == true) && (document.getElementById('generateOption_optSxw').checked == false)) )
    {
        url = destination + '&generateOption=' + 'pdf';
        window.location = url;
    }
    else if ( (document.getElementById('generateOption_optSxw').checked == true) && (document.getElementById('generateOption_optPdf').checked == false) )
    {
        url = destination + '&generateOption=' + 'sxw';
        window.location = url;
    }
}

/**
 * Used by SBeginEndPeriod, when the "Today" button is enabled.
 * Receives the names of the 4 components used to specify a period and a date
 * with which the fields will be filled.
 * 
 * All parameters are strings.
 */
function fillToday(beginDate, beginTime, endDate, endTime, dateToFill)
{
    var e;
    
    // fill begin date
    e = xGetElementById(beginDate);
    if ( e )
    {
        e.value = dateToFill;
    }
    
    // fill begin time
    e = xGetElementById(beginTime);
    if ( e )
    {
        e.value = '00:00';
    }
    
    // fill end date
    e = xGetElementById(endDate);
    if ( e )
    {
        e.value = dateToFill;
    }

    // fill end time
    e = xGetElementById(endTime);
    if ( e )
    {
        e.value = '23:59';
    }
}

/**
 * Valida onBlur se o cpf é válido ou n?o, 
 * caso seja digitado um.
 * 
 * param input element.
 */
function validateOnBlurCPF(element)
{
    var cpf = element.value;
    var len = element.value.length;
    
    if ( len == 11 ) // Sem máscara
    {
        if ( parseInt(element.value) )
        {
            var maskcpf = cpf.substring(3,0) + "." + cpf.substring(3,6) + "." + cpf.substring(6,9) + "-" + cpf.substring(9,11);
            
            if ( MIOLO_Validate_Check_CPF(maskcpf) )
            {
                element.value = maskcpf;
            }
        }
    }
    else if ( len == 14 ) // Com máscara.
    {
        if ( cpf.replace("-", "") )
        {
            cpf = cpf.replace("-", "");
            var splt = cpf.split(".");
            
            if ( splt.length == 3 )
            {
                if ( !MIOLO_Validate_Check_CPF(element.value) )
                {
                    alert("CPF Inválido");
                    element.value = "";
                    element.focus();
                }
            }
        }
    }
}

/**
 * Seta estilo alternativo para home page do sagu.
 */
function styleHomePage()
{
    document.getElementById('extContent').setAttribute('style', 'width:95%; margin-left: auto; margin-right:auto;');
    var elements = document.getElementsByTagName('div');
    
    for ( var x = 0; x < elements.length; x++ )
    {
        if ( elements[x].className == 'm-box-title' )
        {
            elements[x].setAttribute('style', 'display: none');
        }
        
        if ( elements[x].className == 'm-form-body' )
        {
            elements[x].setAttribute('style', 'background-image: none; background: none; background-color: none; border: 0px solid #ccc;');
        }
        
        if ( elements[x].className == 'm-box-box' )
        {
            elements[x].setAttribute('style', 'background-color: transparent !important;');
        }
    }
}

function estaProcessandoAjax()
{
    return document.getElementById("m-loading-message-bg").style.display == 'block';
}

function validaCampoDouble(campo)
{
    value = document.getElementById(campo).value;   
    value = value.replace(',', '.');
 
    if ( isNaN(value * 1) )
    {
        alert('Deve ser informado apenas números reais válidos. Ex.: 1, 3.7, 4.9');
        document.getElementById(campo).value = '';
        document.getElementById(campo).focus();

        return false;
    }   
                
    document.getElementById(campo).value = value;
}