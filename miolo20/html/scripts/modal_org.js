// Browser Detection
isMac = (navigator.appVersion.indexOf("Mac")!=-1) ? true : false;
NS4 = (document.layers) ? true : false;
IEmac = ((document.all)&&(isMac)) ? true : false;
IE4plus = (document.all) ? true : false;
IE4 = ((document.all)&&(navigator.appVersion.indexOf("MSIE 4.")!=-1)) ? true : false;
IE5 = ((document.all)&&(navigator.appVersion.indexOf("MSIE 5.")!=-1)) ? true : false;
ver4 = (NS4 || IE4plus) ? true : false;
NS6 = (!document.layers) && (navigator.userAgent.indexOf('Netscape')!=-1)?true:false;

// Body onload utility (supports multiple onload functions)
var gSafeOnload = new Array();
function SafeAddOnload(f)
{
	if (IEmac && IE4)  // IE 4.5 blows out on testing window.onload
	{
		window.onload = SafeOnload;
		gSafeOnload[gSafeOnload.length] = f;
	}
	else if (window.onload)
	{
		if (window.onload != SafeOnload)
		{
			gSafeOnload[0] = window.onload;
			window.onload = SafeOnload;
		}		
		gSafeOnload[gSafeOnload.length] = f;
	}
	else
		window.onload = f;
}
function SafeOnload()
{
	for (var i=0;i<gSafeOnload.length;i++)
		gSafeOnload[i]();
}

function isInt(numIn)
{
	var checknum = parseInt(numIn);
	return !isNaN(checknum);
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

// Move a layer
function moveTo(obj,xL,yL) 
{
        obj.left = xL;
        obj.top = yL;
}

// Browser window width
function getWindowWidth()
{
	if (NS4 || NS6)
		return window.innerWidth;
	else if (IE4plus)
		return document.body.clientWidth;
}

function getObjLoc(oIn)
{
	var oOut = new Object();
	oOut.top = 0;
	oOut.left = 0;

	if ((IE4plus && !isMac) || (IEmac && IE5) )
	{
		oOut.left = oIn.offsetLeft;
		oOut.top = oIn.offsetTop;
		var newp = oIn.offsetParent;
		while(newp != null)
		{
			oOut.left += newp.offsetLeft;
			oOut.top += newp.offsetTop;
			newp = newp.offsetParent;
		}
		if (IEmac)
		{	
			oOut.left += parseInt(document.body.leftMargin);
			oOut.top +=  parseInt(document.body.topMargin);
		}
	}
	else if (NS4)
	{
		oOut.left = oIn.x;
		oOut.top = oIn.y;
	}
	else if (isMac && IE4)
	{
		var el = oIn;
		do
		{	
			if (isInt(el.offsetTop))
				oOut.top += el.offsetTop;
			if (isInt(el.offsetLeft))
				oOut.left += el.offsetLeft;
			el = el.parentElement;
		} while (el.tagName != "BODY");
		if (navigator.appVersion.indexOf("4.5")>=0)
			oOut.top = oOut.top - 15;
	}
	else if (NS6)
	{
		var b=document.getElementsByTagName('body')[0];
		oOut.left = oIn.offsetLeft+b.offsetLeft;
		oOut.top = oIn.offsetTop+b.offsetTop;
	}
	return oOut;
} 


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

function DMD_GetContentHTML()
{
	var html = '<center><I><FONT color=white size="3"><STRONG>Keep Up-To-Date</STRONG></FONT></I><BR>';
	html += '<FONT color=white size="3"><STRONG>The JavaScript<BR>Newsletter</STRONG></FONT><BR>';
	html += '<IMG src="images/spacer.gif" width=2 height=8 border=0><BR>';
	html += '<a href="http://javascript.about.com/gi/pages/mmail.htm"><IMG src="images/subscribe.gif" width=66 height=17 border=0></a></center>';
	return html;
}

function DMD_GetWindowHTML()
{
	var html = '<map name="DMDMap">';
	html += '<area href="javascript:gDialog.ShowLayers(false)" shape="rect" coords="' + (this.width - 24) + ', 0, ' + this.width + ', 18"></map>';
	html += '<img src="' + this.imgURL + '" width="' + this.width + '" height="' + this.height + '" border="0" usemap="#DMDMap" >';

	return html;
}

function createOverlayLayer()
{
	var nL;
	var left = 0;
	var top = 0;
	var name = "overlay";
	var html = "";
	
	if (IE4plus)
	{
		var width = isMac ? document.body.offsetWidth : document.body.scrollWidth;
		var height = isMac ? document.body.offsetHeight : document.body.scrollHeight;
		var divhtml = '<div  id=' + name + ' style="visibility:visible;left:' + left + 
			'px;top:' + top + 'px;width:' + width + 
			'px;height:' + height + 'px;position:absolute; background: url(images/transoverlay.gif) repeat">' + 
			html + '</div>';
		document.body.insertAdjacentHTML('beforeEnd', divhtml);
		nL = document.all[name].style
	}
	else if (NS4)
	{
		var width = document.width;
		var height = document.height;
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
		nL.style.width = document.body.offsetWidth+document.body.offsetLeft;
		nL.style.height = document.body.offsetHeight+document.body.offsetTop;
		nL.style.background = "url(images/transoverlay.gif)";
		nL.id = name;
		nL = nL.style;
	}
	return nL;
}

function getCenter()
{
	var clientHeight;
	var clientWidth;
	var docTop;
	var docLeft;
	
    if (IE4plus) 
    {
      clientHeight = document.body.clientHeight;
      clientWidth = document.body.clientWidth;
      docTop = document.body.scrollTop;
      docLeft = document.body.scrollLeft;
    } 
    else if (NS4 || NS6)
    {
      // Fudge for the scrollbars
      clientHeight = window.innerHeight -20;
      clientWidth = window.innerWidth - 20;
      docTop = window.pageYOffset;
      docLeft = window.pageXOffset;
    }
    
    var loc = new Object();
    loc.x = docLeft + clientWidth/2;
    loc.y = docTop + clientHeight/2;
    return loc;
}

function DMD_Display()
{
	if (IE4plus || NS4 || NS6)
	{
		if (!this.dvo)
			this.dvo = createOverlayLayer();
		
		if (!this.dv)
			this.dv = createLayer("dmdwindow",0,0,this.width,this.height,this.GetWindowHTML());

		if (!this.dvc)
			this.dvc = createLayer("dmdcontent",0,0,this.width-4,this.height-18,this.GetContentHTML());

		var loc = getCenter();
		this.dv.left = loc.x - this.width/2;
		this.dv.top = loc.y - this.height/2;

		this.dvc.left = loc.x + 4 - this.width/2;
		this.dvc.top = loc.y + 18 - this.height/2;
		this.ShowLayers(true);
	}
}


function DMD_ShowLayers(show)
{
	if (show)
	{
		showObject(this.dv);
		showObject(this.dvc);
		showObject(this.dvo);
	}
	else
	{
		hideObject(this.dv);
		hideObject(this.dvc);	
		hideObject(this.dvo);	
	}
}

function DHTMLModalDialog(imageURL,width,height)
{
	this.width = width;
	this.height = height;
	this.imgURL = imageURL;
	
	this.dv = null;
	this.dvc = null;
	this.dvo = null;
	
	this.GetWindowHTML = DMD_GetWindowHTML;
	this.GetContentHTML = DMD_GetContentHTML;
	this.ShowLayers = DMD_ShowLayers;
	this.Display = DMD_Display
}

function HandleResize()
{
	location.reload();
	return false;
}

if (NS4)
{
	window.captureEvents(Event.RESIZE);
	window.onresize = HandleResize;
}

var gDialog = new DHTMLModalDialog("images/popinside.gif",145,114);

