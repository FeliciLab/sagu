
Miolo.prototype.Dialog = function (id, url, modal, top, left, param, reload) 
{
	var m = miolo.iFrame.base.miolo;
	id =  id + ++m.iFrame.sufix;
    var dlg = m.iFrame.dialogs[id];
	if (dlg == null)
	{
	    dlg = new Miolo.Iframe(id, url, modal, top, left);
		dlg.base = miolo.iFrame.base;
		m.iFrame.dialogs[id] = dlg;
	}
	dlg.reload = false || reload; 
	if (param != '')
	{
    	var par = ''; 
    	param += ',';
        var aParam = param.split(',');
	    for( var i = 0; i < (aParam.length-1); i++ )
        {
            par = par + '&' + aParam[i] + '=' + xGetElementById(aParam[i]).value;
	    }
        dlg.url = url + par;
//		MIOLO_Iframe_Dialogs[id] = null; // force reload
		dlg.reload = true;
	}
	dlg.open();
}