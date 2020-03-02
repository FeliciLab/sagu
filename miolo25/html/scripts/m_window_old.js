Miolo.window = Class.create();
Object.extend(Miolo.window.prototype,Window.prototype);

Object.extend(Miolo.window.prototype, {
    _parent: Window.prototype,
	initialize: function(id, options) {
        this._parent.initialize.call(this, id, options);
		this.id = this.element.id;
        var handle = this.getWindow(this.id);
    	if (handle == null)
	    {
    		this.getBase().miolo.setWindow(this);
	    }
	},
    getBase: function() {
    	return miolo.windows.base;
	},
	getWindow: function(id) {
        return this.getBase().miolo.getWindow(id);
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
    open: function(modal, reload) {
        this.modal = modal;
		/*
		this.id = this.element.id;
        var handle = this.getWindow(this.id);
    	if (handle == null)
	    {
    		this.getBase().miolo.setWindow(this);
	    }
		*/
        if (this.options.url) {
           if ((!this.loaded) || (this.loaded && reload))
           {
               id = this.element.id + '_content';
               this.iframe = miolo.getElementById(id);
		       this.iframe.src = this.options.url;
           }
		}
  		this.show(this.modal);
	},
		/*
	close: function() {
        this.getBase().Windows.close(this.id);
	},
	*/
	reload: function() {
        if (this.iframe != null)
	       this.iframe.src = this.options.url;
	},
    onload: function() { 
	    var p = this.getField('m-container-content-popup');
        this.setSize(this.options.width,xHeight(p));
	    var cmd = this.getField('dialogCommands');
	    if (cmd != null)
	    {
			eval(cmd.value);
	    }
		this.loaded = true;
    },
	field: function(fieldName, value) {
        f = (this.iframe != null) ? this.getField(fieldName) : miolo.getElementById(fieldName);
		if (value == null)
		{
			return f.value;
		}
        f.value = value;
	},
	setWindowField: function(windowId, fieldName, value) {
		this.getWindow(windowId).field(fieldName, value);
	}
});