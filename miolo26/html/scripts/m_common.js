// +-----------------------------------------------------------------+
// | MIOLO - Miolo Development Team - UNIVATES Centro UniversitÃ¡rio  |
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
// | Created: 2001/08/14 Vilson Cristiano GÃ¤rtner [vg]               |
// |                     Thomas Spriestersbach    [ts]               |
// |                                                                 |
// | History: Initial Revision                                       |
// |          2001/12/14 [ts] Added MultiTextField support functions |
// +-----------------------------------------------------------------+

var Miolo = {
  Version: '2-beta1'
}

var frameUtil;

if ( document.all != null )
{
    document.write('<iframe style="visibility:hidden" id="frameUtil" src="" width=0 height=0>&nbsp;</iframe>');
    frameUtil = document.getElementById('frameUtil');
}
else
{
    frameUtil = document.createElement('iframe');
    frameUtil.style.display = 'none';
    frameUtil.id = 'frameUtil';
    document.lastChild.appendChild(frameUtil);
}

function setUtilLocation(url)
{
    if ( frameUtil.tagName == 'IFRAME' )
    {
        frameUtil.src = url;
    }
    else
    {
        frameUtil.location = url;
    }
}

function TrimString(sInString) {
    sInString = sInString.replace( /^\s+/g, "" );// strip leading
    return sInString.replace( /\s+$/g, "" );// strip trailing
}

function MIOLO_GetElementById(e) 
{
    if(typeof(e)!='string') return e;
    if(document.getElementById) {e=document.getElementById(e);}
    else if(document.all) {e=document.all[e];}
    else {e=null;}
    return e;
}

function MIOLO_SetElementValueById(e, value) 
{
    ele = MIOLO_GetElementById(e);
	if (ele != null)
	{
		ele.value = value;
	} 
}

function MIOLO_GotoURL(url)
{
    var prefix = 'javascript:';
    
    if ( url.indexOf(prefix) == 0 )
    {
        eval(url.substring(11) + ';');
    }
    else
    {
        window.location = url;
    }
}

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

function MIOLO_SetTitle(title)
{
    try
    {
    	window.top.document.title = title;    	
    }
    catch (e)
    {
    }

}

function MIOLO_AssociateObjWithEvent(obj, methodName){
    /* The returned inner function is intended to act as an event
       handler for a DOM element:-
    */
    return (function(e){
        return obj[methodName](e, this);
    });
}

function _doPostBack(EventTarget, EventArgument, FormSubmit)
{
    var form = document.forms[0];
    form['__ISPOSTBACK'].value = 'yes';
    form['__EVENTTARGETVALUE'].value = EventTarget;
    form['__EVENTARGUMENT'].value = EventArgument; 
    form['__FORMSUBMIT'].value = FormSubmit; 
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
    var ok = confirm("Aguarde a geraÃ§Ã£o do relatÃ³rio.\nO resultado serÃ¡ exibido em uma nova janela.");
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
    var ok = confirm("Aguarde a geraÃ§Ã£o do arquivo PDF.\nO resultado serÃ¡ exibido em uma nova janela.");
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