// +-----------------------------------------------------------------+
// | MIOLO - Miolo Development Team - UNIVATES Centro Universitário  |
// +-----------------------------------------------------------------+
// | CopyLeft (L) 2001,2002  UNIVATES, Lajeado/RS - Brasil           |
// +-----------------------------------------------------------------+
// | Licensed under GPL: see COPYING.TXT or FSF at www.fsf.org for   |
// |                     further details                             |
// |                                                                 |
// | Site: http://miolo.codigolivre.org.br                           |
// | E-mail: vgartner@univates.br                                    |
// |         ts@interact2000.com.br                                  |
// +-----------------------------------------------------------------+
// | Abstract: This file contains the javascript functions           |
// |                                                                 |
// | Created: 2001/08/14 Vilson Cristiano Gärtner [vg]               |
// |                     Thomas Spriestersbach    [ts]               |
// |                                                                 |
// | History: Initial Revision                                       |
// |          2001/12/14 [ts] Added MultiTextField support functions |
// +-----------------------------------------------------------------+

var autoCompleteId;
var autoCompleteInfo;


/**
 *  LOOKUP
 */
function Lookup(url)
{
    window.open('lookup.php?' + url,'lookup',
                'toolbar=no,width='+screen.width+',height='+screen.height+',scrollbars=yes,' +
                'top=0,left=0,statusbar=yes,resizeable=yes');
}

/**
 *  AUTOCOMPLETE
 */
function AutoComplete(url,fieldId,fieldInfo)
{
    autoCompleteId   = fieldId;
    autoCompleteInfo = fieldInfo;
    
    url = 'autocomplete.php?' + url + '&hint=' + escape(fieldId.value);
    
    top.frames['util'].location = url;
}

function SetResult(info)
{
    // alert(info);
    if ( autoCompleteInfo != null )
    {
        autoCompleteInfo.value = info;
    }
}

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

function MIOLO_AutoComplete(lookup,basemodule)
{
    var url = 'index.php?module=' + basemodule + '&action=autocomplete' +
              '&name='    + escape(lookup.name) +
              '&lmodule=' + escape(lookup.module) +
              '&item='    + escape(lookup.item) +
              '&event='   + escape(lookup.event)+
              '&related=' + escape(lookup.related)+
              '&form='    + escape(lookup.form.name) +
              '&field='   + escape(lookup.field) +
              '&value='   + escape(lookup.form[lookup.field].value);
    setUtilLocation(url);
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

        window.open(url,'lookup',
                    'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
                    'top=0,left=0,statusbar=yes,resizeable=yes');
    }
}

function MIOLO_Deliver(name, key )
{
    try
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

                if ( field.onblur )
                {
                    field.onblur();
                }

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
        if ( MIOLO_getDocument(true).getElementById('lookupIframe') )
        {
            MIOLO_getDocument(true).getElementById('lookupIframe').style.display = 'none';
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
    catch(err)
    {
        window.opener.console.error(err);
        window.opener.console.log(err.message);
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
