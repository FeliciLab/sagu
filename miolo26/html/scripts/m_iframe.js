// ===================================================================
// Most based on work from: Matt Kruse <matt@mattkruse.com>
// WWW: http://www.mattkruse.com/
//
// Adapted to use the X Library (http://www.cross-browser.com)
// Depends on prototype.js (http://prototype.conio.net/)
// Simplified to use only one level of frame (iframe inside a document, not iframe inside iframe)
//


dojo.declare("Miolo.Iframe",null,
{
    constructor: function(id, url, modal, top, left) {
		this.id = id; 
		this.top = top;
		this.left = left;
		this.height = 0;
		this.width = 0;
		this.url = url;
		this.modal = modal;
		this.status = '';
        this.allowDragOffScreen = false;
        this.raiseSelectedIframe = true;
        this.highestZIndex=250;
	    this.handles = new Array();
		this.reload = false;
		this.base = null; 
		this.dragElement = '';
	},
	modalOn: function() {
        var dialogModal = xGetElementById('m-dialog-modal');
		if (!dialogModal)
		{
            dialogModal = this.base.document.createElement('div');
            dialogModal.id = "m-dialog-modal";
            this.base.document.body.appendChild(dialogModal);
            var fullHeight = xClientHeight();
            var fullWidth = xClientWidth();
            var theBody = this.base.document.documentElement;
            var scTop = parseInt(theBody.scrollTop,10);
            var scLeft = parseInt(theBody.scrollLeft,10);
		    dialogModal.style.height = fullHeight + "px";
            dialogModal.style.width = fullWidth + "px";
            dialogModal.style.top = scTop + "px";
            dialogModal.style.left = scLeft + "px";
		}
        dialogModal.style.display = "block";
	},
	modalOff: function() {
        var dialogModal = xGetElementById('m-dialog-modal');
        dialogModal.style.display = "none";
	},
	cursorWaitOn: function() {
        xWalkTree(this.container, function(e){e.style.cursor = 'wait'});
	},
	cursorWaitOff: function() {
        xWalkTree(this.container, function(e){e.style.cursor = 'auto'});
	},
	getInnerWindow: function() {
		return this.base.frames[this.id];
    },
	getDocument: function() {
		iframe = this.iframe;
    	if (iframe.contentDocument != null) d = iframe.contentDocument;
        else if (iframe.contentWindow != null) d = iframe.contentWindow.document;
        else if (iframe.document != null) d = iframe.document;
	    else d = null;
	    return d;
	},
	getField: function(fieldName) {
	    return this.getDocument().getElementById(fieldName);
	},
	setPosition: function(top, left){
        xMoveTo(this.iframe,left,top);
	},
	show: function(){
        xZIndex(this.iframe, this.modal ? 201 : this.iframe.style.zIndex);
        xResizeTo(this.iframe,this.width,this.height);
	},
	hide: function(){
        xResizeTo(this.iframe,0,0);
	},
    open: function() {
		if (this.modal)
		{
			this.modalOn();
		}
        iframe = xGetElementById(this.id);
	    if (!iframe)
	    {
			this.container = this.base.document.getElementById('m-container');
            var iframe = this.base.document.createElement('iframe');
	        iframe.id = this.id;
	        iframe.name = this.id;
            iframe.src = this.url;
			this.width = parseInt(xWidth(this.container) * 0.7);
            iframe.style.width = this.width + 'px';
        	var top = (this.top != 0) ? this.top : xTop(this.container) + 50;
            var left = (this.left != 0) ? this.left : xLeft(this.container) + 50;
            iframe.setAttribute('frameBorder','0');
	        iframe.setAttribute('scrolling','none');
	        iframe.setAttribute('name', this.id);
            iframe.className = 'm-dialog-iframe';
            xMoveTo(iframe,left,top);
	        this.base.document.body.appendChild(iframe);
	        this.iframe = iframe;
			iframe.style.height = "0px";
	    }
	    else
	    {
    		if (this.reload)
	    	{
    		    d = this.getDocument();
				d.location.href = this.url;
	    	}
            this.show();
            this.status = 'opened';
    	}
	},
	parentField: function(field, value)
	{
		if (value == null)
		{
			return xGetElementById(field).value;
		}
        xGetElementById(field).value = value;
	},
    onload: function() { 
		this.status = 'opened';
	    var p = this.getField('m-container-content-popup');
        iframe.style.height = xHeight(p) + "px";
		this.height = xHeight(p);
        this.enableDrag(this.getField(this.dragElement));
  	    this.show();
	    var cmd = this.getField('dialogCommands');
	    if (cmd.value != '')
	    {
			eval(cmd.value);
	    }
    },
	close: function() {
        this.hide();
        xZIndex(this.iframe,0);
	    if (this.modal)
	    {
		    this.modalOff();
	    }
		this.status = 'closed';
	},
	free: function() {
        this.hide();
	    if (this.modal)
	    {
		    this.modalOff();
	    }
		this.status = 'closed';
		miolo.iFrame.dialogs[this.id] = null;
        this.base.document.body.removeChild(miolo.getElementById(this.id));
	},
	enableDrag: function(ele) {
		var win = this.getInnerWindow();
		var doc = this.getDocument();
        // Add handlers to child window
//	    if (typeof(this.mainHandlersAdded)=="undefined" || !this.mainHandlersAdded) {
            onMouseDownHandler = miolo.associateObjWithEvent(this,'onMouseDownHandler');
            onMouseUpHandler   = miolo.associateObjWithEvent(this,'onMouseUpHandler');
            onMouseMoveHandler = miolo.associateObjWithEvent(this,'onMouseMoveHandler');
			xAddEventListener(doc, 'mousedown', onMouseDownHandler, false);
			xAddEventListener(doc, 'mouseup'  , onMouseUpHandler,   false);
			xAddEventListener(doc, 'mousemove', onMouseMoveHandler, false);
		    this.handlersAdded = true;
		    this.mainHandlersAdded = true;
//	    }
        // Initialize relative positions for mouse down events
        this.iframeMouseDownLeft = 0;
	    this.iframeMouseDownTop = 0;
	    this.pageMouseDownLeft = 0;
	    this.pageMouseDownTop = 0;
        this.objectDragging = ele;
        this.handles[this.handles.length] = ele;
	},
    isHandleClicked: function (handle, objectClicked) {
        if (handle == objectClicked) { return true; }
        while (objectClicked.parentNode != null) {
            if (objectClicked == handle) {
                return true;
            }
            objectClicked = objectClicked.parentNode;
        }
        return false;
	},
    onMouseUpHandler: function(event, element){ 
        var e = new xEvent(event);
        xPreventDefault(e);
        this.dragging = false;
    },
    onMouseDownHandler: function(event, element){ 
        var e = new xEvent(event);
        xPreventDefault(e);
        if (this.handles==null || this.handles.length<1) {
            return;
	    }
	    var isHandle = false;
	    var t = e.target;
	    for (var i=0; i<this.handles.length; i++) {
		    if (this.isHandleClicked(this.handles[i],t)) {
			    isHandle=true;
			    break;
		    }
	    }
        if (!isHandle) { return false; }
        if (this.raiseSelectedIframe) {
		    this.iframe.style.zIndex = this.highestZIndex++;
	    }
	    this.dragging = true;
        var pos = {x: e.pageX, y: e.pageY};
        this.iframeMouseDownLeft = pos.x;
        this.iframeMouseDownTop  = pos.y;
        var o = {x: xPageX(this.iframe), y: xPageY(this.iframe)};
        this.pageMouseDownLeft = o.x - 0 + pos.x;
	    this.pageMouseDownTop  = o.y - 0 + pos.y;
    },
    onMouseMoveHandler: function(event, element){ 
	    if (this.dragging) {
            var e = new xEvent(event);
            xPreventDefault(e);
		    var pos = {x: e.pageX, y: e.pageY};
		    this.drag(pos.x - this.iframeMouseDownLeft, pos.y - this.iframeMouseDownTop);
        }
    },
	drag: function (x,y) {
        var o = {x: xPageX(this.iframe), y: xPageY(this.iframe)};
        // Don't drag it off the top or left of the screen?
	    var newPositionX = o.x - 0 + x;
        var newPositionY = o.y - 0 + y;
        if (!this.allowDragOffScreen) {
            if (newPositionX < 0) { newPositionX=0; }
            if (newPositionY < 0) { newPositionY=0; }
        }
        xMoveTo(this.iframe, newPositionX, newPositionY);
        this.pageMouseDownLeft += x;
        this.pageMouseDownTop  += y;
    }
});
