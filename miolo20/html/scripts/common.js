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



/**
 * COMBOBOX
 */
function ComboBox_onTextChange(label,textField,selectionList)
{
    var text = textField.value;
    
    for ( var i=0; i<selectionList.options.length; i++ )
    {
        if ( selectionList.options[i].value == text )
        {
            selectionList.selectedIndex = i;
            return;
        }
    }
    
    alert("!!! ATENÇÃO !!!\n\nNão existe uma opção correspondente ao valor '" + 
          text + "'\ndo campo '" + label + "'!");
    
    textField.focus();
}
 
function ComboBox_onSelectionChange(label,selectionList,textField)
{
     var index = selectionList.selectedIndex;
     if ( index != -1 )
     {
         textField.value = String(selectionList.options[index].value);
     }
} 

/**
 *  GOTOURL
 */
function GotoURL(url)
{
    var prefix = 'javascript:';
    
//    alert(escape(url));
    
    if ( url.indexOf(prefix) == 0 )
    {
        eval(url.substring(11) + ';');
    }
    
    else
    {
        window.location = url;
    }
}

/**
 * TABBEDFORM
 */
function _MIOLO_TabbedForm_GotoPage(frmName,pageName)
{
    // alert('_MIOLO_TabbedForm_GotoPage("' + frmName + "','" + pageName + '")');
    var form = document.forms[0];
    
    if ( form != null )
    {
        form.frm_currpage_.value = pageName;
//        form.frm_submit_.value   = 0;
        
        if ( eval('miolo_onSubmit()') )
        {
            form.submit();
        }
    }
    else
    {
        alert('MIOLO INTERNAL ERROR:\n\nForm ' + frmName + ' not found!');
    }
}
/**
 * LINKBUTTON
 */
function _MIOLO_LinkButton(frmName, url, event, param)
{
    var form = document.forms[0];
    
    if ( form != null )
    {
  	  if ( eval('miolo_onSubmit()') )
      {
   	     form.action = url;
		 window._doPostBack(event, param);
         form.submit();
      }
    }
    else
    {
        alert('MIOLO INTERNAL ERROR: LinkButton\n\nForm ' + frmName + ' not found!');
    }
}

/**
 * PRINT
 */

function MIOLO_Print()
{
	var w = screen.width * 0.75;
	var h = screen.height * 0.60;
    var print = window.open('print.php','print',
                'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
                'top=0,left=0,statusbar=yes,resizeable=yes');
}

/**
 * POPUP
 */
function MIOLO_Popup(url,w,h)
{
    var popup = window.open(url,'popup',
                'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
                'top=0,left=0,statusbar=no,resizeable=yes');
}

/**
 * Window
 */
function MIOLO_Window(url, target)
{
       var mioloWindow = new xWindow(
        target,                // target name
        0, 0,                   // size: width, height
        0, 0,                   // position: left, top
        0,                      // location field
        1,                      // menubar
        1,                      // resizable
        1,                      // scrollbars
        0,                      // statusbar
        1);                     // toolbar
       return mioloWindow.load(url);
}

/**
 * PAGE FUNCTIONS
 */

/**
 * MIOLO Form Event Handler\n";
 */
function _doPostBack(EventTarget, EventArgument)
{
	var form = document.forms[0];
	form['__ISPOSTBACK'].value = 'yes';
	form['__EVENTTARGETVALUE'].value = EventTarget;
	form['__EVENTARGUMENT'].value = EventArgument; 
}

function _doPrintForm(url)
{
		var w = screen.width * 0.75;
		var h = screen.height * 0.60;
		var print = window.open(url,'print',
		                   'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
		                   'top=0,left=0,statusbar=yes,resizeable=yes');
}

function _doPrintFile()
{
       var ok = confirm('Aguarde a geração do relatório.\\nO resultado será exibido em uma nova janela.');
       if (ok)
       {
          var tg = window.name;
 	      var form = document.forms[0];
    	  var w = screen.width * 0.95;
		  var h = screen.height * 0.80;
		  var print = window.open('','print', 
		                     'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
		                     'top=0,left=0,statusbar=yes,resizeable=yes');
 		        form.target='print'; 
		        form.submit(); 
		        print.focus();
 		        form.target=tg;
       }
}

function _doShowPDF()
{
       var ok = confirm("Aguarde a geração do arquivo PDF.\nO resultado será exibido em uma nova janela.");
       if (ok)
       {
          var tg = window.name;
 	      var form = document.forms[0];
    	  var w = screen.width * 0.95;
		  var h = screen.height * 0.80;
		  var print = window.open('','print', 
		                     'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
		                     'top=0,left=0,statusbar=yes,resizeable=yes');
 		        form.target='print'; 
		        form.submit(); 
		        print.focus();
 		        form.target=tg;
       }
}

function _doPrintURL(url)
{
       var ok = confirm('Clique Ok para imprimir.');
       if (ok)
       {
          var tg = window.name;
 	      var form = document.forms[0];
    	  var w = screen.width * 0.95;
		  var h = screen.height * 0.80;
		  var print = window.open(url,'print', 
		                     'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
		                     'top=0,left=0,statusbar=yes,resizeable=yes');
          print.focus();
		  window.print();
          form.target=tg;
       }
}
