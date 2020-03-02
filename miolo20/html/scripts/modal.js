// Browser Detection
isMac = (navigator.appVersion.indexOf("Mac")!=-1) ? true : false;
NS4 = (document.layers) ? true : false;
IEmac = ((document.all)&&(isMac)) ? true : false;
IE4plus = (document.all) ? true : false;
IE4 = ((document.all)&&(navigator.appVersion.indexOf("MSIE 4.")!=-1)) ? true : false;
IE5 = ((document.all)&&(navigator.appVersion.indexOf("MSIE 5.")!=-1)) ? true : false;
ver4 = (NS4 || IE4plus) ? true : false;
NS6 = (!document.layers) && (navigator.userAgent.indexOf('Netscape')!=-1)?true:false;

function createLayer(name,left,top,width,height,html)
{
	var nL;
	
	if (IE4plus)
	{
		var divhtml = '<div id=' + name + ' style="visibility:hidden;left:' + left + 
			'px;top:' + top + 'px;width:' + width + 
			'px;height:' + height + 'px;position:absolute">' + 
			html + '</div>';
		document.body.insertAdjacentHTML('beforeEnd', divhtml);
		nL = document.all[name].style
	}
	else if (NS4)
	{
		nL=new Layer(width);
		nL.name = name;
		nL.left=left;
		nL.top=top;
		nL.clip.width=width;
		nL.clip.height=height;
		nL.document.open();
		nL.document.write(html);
		nL.document.close();
	}
	else if (NS6)
	{
		var nL = document.createElement("DIV");
		nL.innerHTML = html;
		var mybody=document.body;
		mybody.appendChild(nL);
		nL.style.position = "absolute";
		nL.style.visibility = "hidden";
		nL.style.left = left;
		nL.style.top = top;
		nL.style.width = width;
		nL.style.height = height;
		nL.id = name;
		nL = nL.style;
	}
	return nL;
}

function createOverlayLayer()
{
	var nL;
	var left = 0;
	var top = 0;
	var name = "overlay";
	var html = "";
	
	var width = xClientWidth();;
	var height = xClientHeight();

	if (IE4plus)
	{
		var divhtml = '<div  id=' + name + ' style="visibility:visible;left:' + left + 
			'px;top:' + top + 'px;width:' + width + 
			'px;height:' + height + 'px;position:absolute; background: url(images/transoverlay.gif) repeat">' + 
			html + '</div>';
		document.body.insertAdjacentHTML('beforeEnd', divhtml);
		nL = document.all[name].style
	}
	else if (NS4)
	{
		nL=new Layer(width);
		nL.name = name;
		nL.left=left;
		nL.top=top;
		nL.height = height;
		nL.clip.width = width;
		nL.clip.height = height;
		nL.visibility = "show";
		nL.background = "images/transoverlay.gif";
		nL.document.open();
		nL.document.write('<table background="images/transoverlay.gif"><tr><td><img src="images/spacer.gif" width="' + width + '" height="' + height +'"></td></tr></table>');
		nL.document.close();
	}
	else if (NS6)
	{
		var nL = document.createElement("DIV");
		nL.innerHTML = "";
		var mybody=document.body;
		mybody.appendChild(nL);
		nL.style.position = "absolute";
		nL.style.visibility = "visible";
		nL.style.left = left;
		nL.style.top = top;
		nL.style.width = width;
		nL.style.height = height;
		nL.style.background = "url(images/transoverlay.gif)";
		nL.id = name;
		nL = nL.style;
	}
	return nL;
}

function DMD_Display()
{
    ele = xGetElementById(this.divid);
    html = ele.innerHTML;
	l = xPageX(ele);
	t = xPageY(ele);
	w = xWidth(ele);
	h = xHeight(ele);
	if (!this.dvo)
		this.dvo = createOverlayLayer();
	if (!this.dv)
		this.dv = createLayer('_mdl_'+this.divid,l,t,w,h,html);
	this.ShowLayers(true);
}

// Make an object visible
function showObject(obj) 
{
        if (NS4) obj.visibility = "show";
        else if (IE4plus||NS6) obj.visibility = "visible";
}

// Hides an object
function hideObject(obj) 
{
        if (NS4) obj.visibility = "hide";
        else if (IE4plus||NS6) obj.visibility = "hidden";
}

function DMD_ShowLayers(show)
{
	if (show)
	{
		showObject(this.dv);
		showObject(this.dvo);
	}
	else
	{
		hideObject(this.dv);
		hideObject(this.dvo);	
	}
}

function DHTMLModalDialog(id)
{
	this.dv = null;
	this.dvc = null;
	this.dvo = null;

	this.divid = id;
	
	this.ShowLayers = DMD_ShowLayers;
	this.Display = DMD_Display
}