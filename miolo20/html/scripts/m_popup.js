function MIOLO_IPopup_Parent(e)
{
  var p=null;
  if (e.parentNode != null) p=e.parentNode;
  else if (e.parentElement != null) p=e.parentElement;
  return p;
}

function MIOLO_IPopup(sId,uTop, uLeft, uWidth, uHeight, sUrl, sStyle, eRef, zIndex)
{
  if (document.getElementById &&
      document.createElement &&
      document.body &&
      document.body.appendChild)
  { 
    if (xGetElementById(sId) != null)
    {
		var f = xGetElementById(sId + '_iframe');
        f.src = sUrl;
		return;
    }
    // create popup element
    var e = document.createElement('DIV');
    var f = document.createElement('IFRAME');
    e.appendChild(f);
    this.ele = e;
    e.id = sId;
    e.style.width = uWidth+'px';
    e.style.height = uHeight+'px';
    f.style.width = '100%';
    f.style.height = '100%';
    f.className = sStyle;
    f.src = sUrl;
	f.id = sId + '_iframe';
	if (eRef == null)
	{
        e.style.position = 'absolute';
        e.style.top = uTop+'px';
        e.style.left = uLeft+'px';
        document.body.appendChild(e);
	}
	else
    {
		r = xGetElementById(eRef);
		r.parent = xParent(r,true);
        e.style.position = 'static';
        r.parent.appendChild(e);
    }

    xShow(e);
  } 
}

function MIOLO_IPopup_Close(eElement)
{
	  if(typeof(eElement)=='string') eElement = xGetElementById(eElement);
      p = MIOLO_IPopup_Parent(eElement);
	  p.removeChild(eElement);
}
