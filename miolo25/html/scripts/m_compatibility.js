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
    return miolo.getElementById(e);
}

function MIOLO_SetElementValueById(e, value) 
{
	miolo.setElementValueById(e, value);
}

function MIOLO_GotoURL(url)
{
	miolo.gotoURL(url);
}

function MIOLO_Window(url, target)
{
    miolo.window(url, target);
}

function MIOLO_SetTitle(title)
{
	miolo.setTitle(title);
}

function MIOLO_AssociateObjWithEvent(obj, methodName)
{
    return miolo.associateObjWithEvent(obj, methodName);
}

function MD5(entree)
{
    var md5 = new Miolo.md5;
    return md5.MD5(entree);
}