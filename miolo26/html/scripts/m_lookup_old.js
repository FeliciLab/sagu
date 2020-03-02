/**
 * LOOKUP
 */
Miolo.lookup = Class.create();

Miolo.lookup.prototype = {
	initialize: function() {
	},
	setContext: function(context, baseModule) {
       this.context = context;
	   this.baseModule = baseModule == '' ? 'admin' : baseModule;
	},
	open: function() {
       var type = this.context.type;
       var field = miolo.getElementById(this.context.field);
       var url = 'index.php?module=' + this.baseModule + 
		      '&action='  + 'lookup' +
		      '&name='    + escape(this.context.name) +
              '&lmodule=' + escape(this.context.module) +
              '&item='    + escape(this.context.item) +
              '&event='   + escape(this.context.event)+
              '&related=' + escape(this.context.related) +
              '&wtype='    + escape(this.context.wtype);
       if ( field != null ) {
           url = url + '&fvalue='  + escape(this.context.value);
       }
       if ( this.context.filter != null ) {
           var aFilter = this.context.filter.split(',');
	       if (aFilter.length == 1)
	       {
              var field = miolo.getElementById(aFilter[0]);
	          url = url + '&filter' + '='  + escape(field.value);
	       } 
	       else
           {
               for( var i=0; i < aFilter.length; i++ )
               {
                  var field = miolo.getElementById(aFilter[i]);
        	      url = url + '&filter' + i + '='  + escape(field.value);
               }
	       }
       }
	   //alert(escape(this.context.wType));
       if (escape(this.context.wType) == 'popup')
       {
    	   var w = 760; // escape(this.context.wWidth)
	       var h = screen.height * 0.60; // escape(this.context.wHeight)
		   // var t = escape(this.context.wTop);
		   // var l = escape(this.context.wLeft);
           window.open(url,'lookup',
                'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
                'top=0,left=0,statusbar=yes,resizeable=yes');
       }
       if (escape(this.context.wType) == 'dialog')
       {
		   miolo.Dialog('dlgLookup', url, false, 50, 50,'');
       }
    },
	deliver: function(name, key, args) {
       var _window = window.opener ? 'window.opener' : 'window.parent';
	   var __window = eval(_window);
       var lookup  = eval(_window + '.' + name);
	   var context = lookup.context;
	   var arguments = this.deliver.arguments;
	   var isDialog = (escape(context.type) == 'dialog');
	   var event = context.event;
	   if (event == 'filler') {
          related = context.related;
          var aRelated = related.split(',');
          var count = arguments.length;
          for( var i=2; i<count; i++ ){
             var value = arguments[i];
             var field = context.form['frm_'+aRelated[i-2]];
             if ( field == null ) {
                var field = context.form[aRelated[i-2]];
             }
             if ( field != null ) {
                field.value = value;
                field = context.form[field.name+'_sel'];
                if ( field != null ) {
                   field.value = value;
                }
             }
          }
   	      if (!isDialog) {
		     __window.focus();
   	      }
	   }
	   else {
   	      __window.miolo.doPostBack(event,arguments[key+2], context.form);
          context.form.submit();
	   }
       var _frame = isDialog ? __window.miolo.iFrame.dialogs['dlgLookup'] : window;
	   _frame.close();
   }
}



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
    
    // separar caminho da url dos parÃ¢metros
    var a = url.split('?');
    
    if ( a.length == 2 )
    {
        // separar os parÃ¢metros
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

function MIOLO_Lookup(lookup, basemodule)
{
    var type = lookup.type;

    if ( basemodule == '' )
	{
        basemodule = 'admin';
    }
    var field = lookup.form[lookup.field];
//	var url = 'lookup.php' +

	var url = 'index.php?module=' + basemodule + 
		      '&action='  + 'lookup' +
		      '&name='    + escape(lookup.name) +
              '&lmodule=' + escape(lookup.module) +
              '&item='    + escape(lookup.item) +
              '&event='   + escape(lookup.event)+
              '&related=' + escape(lookup.related) +
              '&type='    + escape(lookup.type);
    if ( field != null )
	{
        url = url + '&fvalue='  + escape(field.value);
    }
    
    if ( lookup.filter != null )
	{
       var aFilter = lookup.filter.split(',');
	   if (aFilter.length == 1)
	   {
          var field = lookup.form[aFilter[0]];
	      url = url + '&filter' + '='  + escape(field.value);
	   } 
	   else
       {
           for( var i=0; i < aFilter.length; i++ )
           {
              var field = lookup.form[aFilter[i]];
    	      url = url + '&filter' + i + '='  + escape(field.value);
           }
	   }
    }
//	var w = screen.width * 0.75;
//alert(url);
    if (escape(lookup.type) == 'window')
    {
    	var w = 680;
	    var h = screen.height * 0.60;
        window.open(url,'lookup',
                'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
                'top=0,left=0,statusbar=yes,resizeable=yes');
    }
    if (escape(lookup.type) == 'dialog')
    {
		MIOLO_Dialog('dlgLookup', url, false, 50, 50);
    }
}

function MIOLO_Deliver(name, key, args)
{
    var _window = window.opener ? 'window.opener' : 'window.parent';
	var __window = eval(_window);
//	var isDialog = __window.MIOLO_Iframe_Dialogs['dlgLookup'] != null;
 	var lookup  = eval(_window + '.' + name);
	var isDialog = (escape(lookup.type) == 'dialog');
	var event = lookup.event;
	if (event == 'filler')
	{
//       related = lookup.field + ',' + lookup.related;
       related = lookup.related;
       
       var aRelated = related.split(',');
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
   	   if (!isDialog)
   	   {
		   __window.focus();
   	   }
	}
	else
	{
   	   __window._doPostBack(event,MIOLO_Deliver.arguments[key+2], lookup.form);
       lookup.form.submit();
	}
    var _frame = isDialog ? __window.MIOLO_Iframe_Dialogs['dlgLookup'] : window;
	_frame.close();
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
