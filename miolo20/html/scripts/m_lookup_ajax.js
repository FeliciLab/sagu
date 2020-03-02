// +-----------------------------------------------------------------+
// | MIOLO - Miolo Development Team - UNIVATES Centro Universitário  |
// +-----------------------------------------------------------------+
// | CopyLeft (L) 2006  Solis - Cooperativa de Solucoes Livres       |
// +-----------------------------------------------------------------+
// | Licensed under GPL: see COPYING.TXT or FSF at www.fsf.org for   |
// |                     further details                             |
// |                                                                 |
// | Site: http://www.miolo.org.br                                   |
// | E-mail: vilson@miolo.org.br                                     |
// +-----------------------------------------------------------------+
// | Abstract: This file contains the javascript functions           |
// |                                                                 |
// | Created: 2006/07/30 Vilson Cristiano Gärtner [vg]               |
// +-----------------------------------------------------------------+

var autoCompleteId;
var autoCompleteInfo;


/**
 *  LOOKUP
 *
function Lookup(url)
{
    window.open('lookup.php?' + url,'lookup',
                'toolbar=no,width='+screen.width+',height='+screen.height+',scrollbars=yes,' +
                'top=0,left=0,statusbar=yes,resizeable=yes');
}
*/

function Deliver(id,text)
{
    var lookup_form  = null;
    var lookup_field = null;
    var lookup_text  = null;
    
    url = String(window.location);
    
    // alert(url);
    
    if ( text != null )
    { var pos;
        
        // alert(text);
        
        while ( (pos = text.indexOf('+')) != -1 )
        {
            text = text.substring(0,pos) + ' ' + text.substring(pos+1);
        }
        
        text = unescape(text);
        
        // alert(text);
    }
    
    // separar caminho da url dos parâmetros
    var a = url.split('?');
    
    if ( a.length == 2 )
    {
        // separar os parâmetros
        var b = a[1].split('&');
        
        for ( i=0; i<b.length; i++ )
        {
            var c = b[i].split('=');
            
            if ( c.length == 2 )
            {
                var name  = c[0];
                var value = c[1];
                
                if ( name == 'lookup_form' )
                {
                    lookup_form = value;
                }
                else if ( name == 'lookup_field' )
                {
                    lookup_field = value;
                }
                else if ( name == 'lookup_text' )
                {
                    lookup_text = value;
                }
            }
        }
    }
    //echo lookup_form;
    if ( lookup_form != null )
    {
        if ( lookup_field != null )
        {
            eval("window.opener.document." + lookup_form + "." + 
                 lookup_field + ".value='" + id + "'");
        }
        
        if ( lookup_text != null )
        {
            eval("window.opener.document." + lookup_form + "." + 
                 lookup_text + ".value='" + text + "'");
        }
    }
    
    close();
}

function LookupContext()
{
}

var loadingImage = '/images/loading.gif';
var loadingImageDivId = new Array();
var loadingImageWidth = 13;
var loadingImageHeight = 13;
var loadingImagePosX = 4;
var loadingImagePosY = 4;

function sendRequest(action)
{
    updateImageStatus('block');

    http.open('get', action);
    http.onreadystatechange = handleResponse;
    http.send(null);

    return http.responseText;
}

var varDoc   = '';
var varForm  = '';
var varLookupField = '';
var varRelated = '';

function handleResponse()
{

    if( http.readyState == 4 )
    {
        updateImageStatus('none');

        var response = http.responseText;
        var update = new Array();

        if(response.indexOf('|' != -1)) 
        {
            update = response.split('|');
        }

        MIOLO_AutoCompleteDeliver_Ajax(document, varForm, '', varRelated, response);
    }
}

function MIOLO_AutoComplete_Ajax(lookup,basemodule)
{
    var url = 'autocomplete.php?module=' + basemodule + '&action=autocomplete' +
              '&name='    + escape(lookup.name) +
              '&lmodule=' + escape(lookup.module) +
              '&item='    + escape(lookup.item) +
              '&event='   + escape(lookup.event)+
              '&related=' + escape(lookup.related)+
              '&form='    + escape(lookup.form.name) +
              '&field='   + escape(lookup.field) +
              '&value='   + escape(lookup.form[lookup.field].value);
    var filter    = lookup.filter.split(',');
    var idxFilter = lookup.idxFilter.split(',');
    var filterString = '';

    for(i=0; i<filter.length; i++)
    {
        url += "&" + idxFilter[i] + "=" + escape(lookup.form[filter[i]].value);
        filterString += lookup.form[filter[i]].value + ',';
    }

    if ( filterString != '' )
    {
        url += '&filters=' + escape(filterString.substr(0, filterString.length-1));
    }

    varForm = lookup.form.name;
    varLookupField = lookup.field;
    varRelated = lookup.related;

    createImageDiv();
    sendRequest(url);
}

function createImageDiv()
{
    var splitRelated = varRelated.split(',');

    var fieldName = new Array();
    var newDiv    = new Array();
    var newImage  = new Array();
    //var campos = '';
    for (var i=0; i < splitRelated.length; i++)
    {
        try
        {
            fieldName[i] = document.getElementById('m_'+splitRelated[i]);
            campos += 'm_'+splitRelated[i] + ', ';
            fieldName[i].style.position = 'relative';
            loadingImageDivId[i] = 'loading-image_'+i;
    
            
    
            newDiv[i] = document.createElement('div');
            newDiv[i].id = loadingImageDivId[i];
            newDiv[i].style.backgroundColor = 'transparent';
            newDiv[i].style.border  = '0';
            newDiv[i].style.display = 'none';
            newDiv[i].style.height  = loadingImageWidth+'px';
            newDiv[i].style.width = loadingImageWidth+'px';
            newDiv[i].style.padding  = '0';
            newDiv[i].style.position = 'absolute';
            newDiv[i].style.left = loadingImagePosX+'px';
            newDiv[i].style.top  = loadingImagePosY+'px';
            fieldName[i].appendChild(newDiv[i]);
    
            newImage[i] = document.createElement('img');
            newImage[i].setAttribute('src', loadingImage);
            newImage[i].setAttribute('height', loadingImageHeight);
            newImage[i].setAttribute('width', loadingImageWidth);
            newImage[i].style.border = '0';
            newImage[i].style.backgroundColor = 'transparent';
            newImage[i].style.margin  = '0';
            newImage[i].style.padding = '0';
            newDiv[i].appendChild(newImage[i]);
        }
        catch(err)
        {
            txt  = "Erro: Campo " + fieldName[i] + " - " + err.description + "\n\n"
            txt += "OK para continuar.\n\n"
            //alert(txt)
        }
    }
    
    //alert(campos);


/*
tmp_var = varRelated.split(',');

if ( tmp_var.length > 0 )
{
        elementToUse = tmp_var[0];
}
else
{
        elementToUse = varRelated;
}


    fieldName.style.position = 'relative';

    var newDiv = document.createElement('div');
    newDiv.id = loadingImageDivId;
    newDiv.style.backgroundColor = 'transparent';
    newDiv.style.border = '0';
    newDiv.style.display = 'none';
    newDiv.style.height = loadingImageWidth+'px';
    newDiv.style.width = loadingImageWidth+'px';
    newDiv.style.padding = '0';
    newDiv.style.position = 'absolute';
    newDiv.style.left = loadingImagePosX+'px';
    newDiv.style.top = loadingImagePosY+'px';
    fieldName.appendChild(newDiv);

    var newImage = document.createElement('img');
    newImage.setAttribute('src', loadingImage);
    newImage.setAttribute('height', loadingImageHeight);
    newImage.setAttribute('width', loadingImageWidth);
    newImage.style.border = '0';
    newImage.style.backgroundColor = 'transparent';
    newImage.style.margin = '0';
    newImage.style.padding = '0';
    newDiv.appendChild(newImage);
    */
}

function updateImageStatus(stringStatus)
{
    var splitRelated = varRelated.split(',');
    var loadingImageDiv = new Array();

    for ( var i=0; i < splitRelated.length; i++ )
    {
        try
        {
            loadingImageDiv[i] = document.getElementById( loadingImageDivId[i] );
            loadingImageDiv[i].style.display = stringStatus;
        }
        catch(err)
        {
        }
    }

}

function MIOLO_AutoCompleteDeliver_Ajax(doc, form, debugMessage, related, info)
{
    related = related.split(',');
    
    // To see the resul of an autocomplete, uncomment the following alert:
    if(typeof(info) == 'string')
    {
        if ( info == 'nothing_found' )
        {
            alert('Nenhum dado encontrado!');
            info = '';
        }
        else if ( info == '' )
        {
            if ( doc.getElementById(varLookupField) )
            {
                doc.getElementById(varLookupField).value = '';
            }
        }
        else
        {
            info = info.split('|');
        }
    }

    for(var i=0; i<related.length; i++)
    {
        if(typeof(info[i]) == 'undefined')
        {
            if ( doc.getElementsByName(related[i])[0] )
            {
                doc.getElementsByName(related[i])[0].value = '';
            }
            continue;
        }

        try
        {
            doc.getElementsByName( related[i] )[0].value = info[i];
        }
        catch(err)
        {
        }
    }
}

function MIOLO_Lookup(lookup, basemodule, event)
{

    if ( basemodule == '' )
	{
        basemodule = 'common';
    }
    var field = lookup.form[lookup.field];
//	var url = 'lookup.php' +
    //alert(basemodule);

	var url = 'index.php?module=' + basemodule + '&action=lookup' +
		      '&name='    + escape(lookup.name) +
              '&lmodule=' + escape(lookup.module) +
              '&item='    + escape(lookup.item) +
              '&event='   + escape(lookup.event)+
              '&related=' + escape(lookup.related)+
              '&autopost='+ (lookup.autoPost ? '1' : '0');
    if ( field != null )
	{
        url = url + '&fvalue='  + escape(field.value);
    }
    
    if ( lookup.filter != null )
	{
       var aFilter   = lookup.filter.split(',');
       var idxFilter = lookup.idxFilter.split(',');
       useIdx        = idxFilter[0].charAt(0) != 0;

	   if (aFilter.length == 1 && ! useIdx )
	   {
          var field = lookup.form[aFilter[0]];
          if(field)
          {
	        url = url + '&filter' + '='  + escape(field.value);
          }
	   } 
	   else
       {
           for( var i=0; i < aFilter.length; i++ )
           {
              var field = lookup.form[aFilter[i]];
              idx       = useIdx ? idxFilter[i] : 'filter' + i;
    	      url       += '&' + idx + '='  + escape(field.value);
    	      url       += '&idx['+escape(aFilter[i])+']='+ escape(idxFilter[i]);
           }
	   }
    }
    var w = lookup.wWidth  ? lookup.wWidth  : 680;
    var h = lookup.wHeight ? lookup.wHeight : screen.height * 0.60;
//	var w = screen.width * 0.75;
//alert(url);
    if ( lookup.wType == 'iframe' )
    {
        win = document.getElementById('lookupIframe');

        if ( win == null )
        {
            win = document.createElement('iframe');
            win.id             = 'lookupIframe';
        }
        pos = MIOLO_getMousePosition( event );
        win.style.position = 'absolute';
        win.style.width    = w;
        win.style.height   = h;
        win.style.top      = (lookup.wTop.length  > 0 ? lookup.wTop  : pos[1])+'px';
        win.style.left     = (lookup.wLeft.length > 0 ? lookup.wLeft : pos[0])+'px';
        win.style.display  = '';

        document.body.appendChild( win );
        win.contentWindow.document.open( );
        win.contentWindow.document.write( '<div class="">Loading...</div>' );
        win.contentWindow.document.close( );
        win.src            = url;
    }
    else
    {

        window.open(url,window.document.forms[0].name,
                    'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
                    'top=0,left=0,statusbar=yes,resizeable=yes');
    }
}

function MIOLO_Deliver(name, key )
{   
    var lookup = eval('MIOLO_getWindow( true ).' + name);
	var event  = lookup.event;
    
	if (event == 'filler')
	{
        var aRelated = lookup.related.split(',');
        var exists = false;
        for(var i=0; i < aRelated.length; i++)
        {
            if(aRelated[i] == lookup.field)
            {
                exists = true;
            }
        }
        if( exists || ! lookup.field )
        {
            related = lookup.related;
        }
        else
        {
            related = lookup.field + ',' + lookup.related;
        }
       
        aRelated = related.split(',');
        var count = MIOLO_Deliver.arguments.length;

       
       for( var i=2; i<count; i++ )
       {
          var value = MIOLO_Deliver.arguments[i];
          var field = lookup.form['frm_'+aRelated[i-2]];
          if ( field == null )
          {
             var field = lookup.form[aRelated[i-2]];
          }
          
          if ( field != null )
          {
             field.value = value;

             field = lookup.form[field.name+'_sel'];
             if ( field != null )
             {
                 field.value = value;
             }
          }
       }
   	   MIOLO_getWindow( true ).focus();
	}
	else
	{
   	   MIOLO_getWindow( true )._doPostBack(event,MIOLO_Deliver.arguments[key+2]);
       lookup.form.submit();
	}
    if ( MIOLO_getDocument( ).getElementById('lookupIframe') )
    {
        MIOLO_getDocument( ).getElementById('lookupIframe').style.display = 'none';
    }
    else
    {
        window.close();
    }

    if ( lookup.autoPost )
    {
   	    MIOLO_getWindow( true )._doPostBack(event,MIOLO_Deliver.arguments[key+2]);
        lookup.form.submit( );
    }

}


function MIOLO_ActiveLookup(sLookupName, uTop, uLeft, uWidth, uHeight, sUrl, sStyle, sFieldId, zIndex)
{

    var field = xGetElementById(sFieldId);
    if ( field != null )
	{
        sUrl = sUrl + '&fvalue='  + escape(field.value);
    }
    MIOLO_IPopup(sLookupName, uTop, uLeft, uWidth, uHeight, sUrl, sStyle, sFieldId, zIndex);
}

function MIOLO_ActiveDeliver(name, key, event, related, args)
{   
	if (TrimString(event) == 'filler')
	{
       var aRelated = related.split(',');
       var count = MIOLO_ActiveDeliver.arguments.length;
       for( var i=4; i<count; i++ )
       {
          var value = MIOLO_ActiveDeliver.arguments[i];
		  var form = parent.document;
          var field = form.getElementById(aRelated[i-4]);
          if ( field != null )
          {
              field.value = value;
          }
       }
	}
	e = parent.document.getElementById(name);
    MIOLO_IPopup_Close(e);
}

function MIOLO_AutoCompleteDeliver(doc, form, debugMessage, related, info)
{
    related = related.split(',');
    if(typeof(info) == 'string')
    {
        info    = info.split(',');
    }

    for(var i=0; i<related.length; i++)
    {
        if(typeof(info[i]) == 'undefined') info[i] = '';
        if(field=form[related[i]+'_sel'])
        {
            field.value = info[i];
        }
        if(field=form[related[i]])
        {
            field.value = info[i];
        }
        else
        {
            if(debugMessage)
            {
                doc.addDebugInformation(debugMessage.replace('@1', related[i]));
            }
        }
    }
}
