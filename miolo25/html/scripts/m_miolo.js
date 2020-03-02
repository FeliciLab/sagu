dojo.declare("Miolo", null, {
    Version: 'Miolo2.5',
	webForm: null,
    fields: null,
    lastFocusElement : null,
    constructor: function() {
	},
    isIE: document.all ? true : false,
    i18n: null,
    iFrame: {
		object: null,
		dialogs: new Array(),
		sufix: 0,
		dragElement: null,
		base: window,
		parent: null,
		getById: function(id) {
            return this.parent.miolo.iFrame.dialogs[id];
		}
	},
    windows: {
		handle: new Array(),
		sufix: 0,
		base: window
	},
    getWindow: function(windowId) {
        return miolo.windows.handle[(windowId != '' ? windowId : 'current')];
	},
	addWindow: function(windowId) {
        miolo.windows.handle[windowId] = new Miolo.Window(windowId);
        return miolo.windows.handle[windowId];
	},
	setWindow: function(oWindow) {
        miolo.windows.handle['current'] = oWindow;
	},
	pushWindow: function(oWindow) {
        var win = miolo.windows.handle['current'];
        oWindow.parent = win;
        miolo.windows.handle['current'] = oWindow;
	},
	popWindow: function() {
        var win = miolo.windows.handle['current'];
        miolo.windows.handle['current'] = win.parent;
	},
    forms: {
		handle: new Array()
	},
    getForm: function(formId) {
        return miolo.forms.handle[formId];
	},
	addForm: function(formId) {
        miolo.forms.handle[formId] = new Miolo.Form(formId);
        return miolo.forms.handle[formId];
	},
	setForm: function(formId) {
        var form;
        miolo.webForm = ( form = miolo.getForm(formId)) ? form : miolo.addForm(formId);
	},
	getCurrentURL: function () {
		return this.webForm.getAction();
	},
    getElementById: function (e) {
        if(typeof(e)!='string') return e;
        if(document.getElementById) {e = dojo.byId(e);}
        else if(document.all) {e=document.all[e];}
        else {e=null;}
        return e;
    },
    getElementsByTagName: function (tagName, p) {
        var list = null;
        tagName = tagName || '*';
        p = p || document;
        if (p.getElementsByTagName) list = p.getElementsByTagName(tagName);
        return list || new Array();
    },
	setElementValueById: function (e, value) {
        ele = this.getElementById(e);
        if (ele != null)
	    {
		    ele.value = value;
	    } 
    },
    setElementAttribute: function (elementId, attribute, value) {
        dojo.attr(elementId, attribute, value);
    },
    gotoURL: function (url) {
        var prefix = 'javascript:';
        url = url.replace(/&amp;/g,"&");
        if ( url.indexOf(prefix) == 0 )
        {
            eval(url.substring(11) + ';');
        }
        else
        {
            window.location = url;
        }
    },
	window: function (url, target)
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
    },
    setTitle: function setTitle(title)
    {
        try
        {
    	    window.top.document.title = title;    	
        }
        catch (e)
        {
        }
    },
	associateObjWithEvent: function (obj, methodName){
    /* The returned inner function is intended to act as an event
       handler for a DOM element:-
    */
        return (function(e){return obj[methodName](e, this);});
    },
    isHandler: function(url) {
        return (url.indexOf('index.php') > -1);
    },
    submit: function() {
        return this.doPostBack('','',this.webForm.id);
    },
    afterSubmit: function() {
        this.webForm.validators = null;      
        this.disconnect();
    },
    registerEvent: function (id, event, handler, preventDefault) {
        try
        {
            var eventHandler = new Function("event", handler + (preventDefault ? " event.preventDefault();" : "" ));
            this.webForm.connect(id, event, eventHandler);
        }
        catch (e)
        {
            this.page.stdout('registerEvent ' + id + ':' + event + '. Error: ' + e);
        }
    },
    disconnect: function () {
        if (this.webForm)
        {
            this.webForm.disconnect();
        }
    },
    _doSubmit: function (eventTarget, eventArgument, formSubmit) {
        this.setElementValueById(formSubmit+'__ISPOSTBACK', 'yes');
        this.setElementValueById(formSubmit+'__EVENTTARGETVALUE', eventTarget);
        this.setElementValueById(formSubmit+'__EVENTARGUMENT', eventArgument); 
        this.setForm(formSubmit);
    },
    doPostBack: function (eventTarget, eventArgument, formSubmit) {
        this._doSubmit(eventTarget, eventArgument, formSubmit);
        var result = this.webForm.onSubmit();
        if (result)
        {
            this.afterSubmit();
            this.page.postback();
        }
        return result;
    },
    doLinkButton: function (url, eventTarget, eventArgument, formSubmit) {
        this._doSubmit(eventTarget, eventArgument, formSubmit);
        if (this.webForm.onSubmit())
        {
            this.afterSubmit();
            url = url.replace(/&amp;/g,"&");
            this.webForm.setAction(url);
            this.page.postback();
        }
    },
    doAjax: function (eventTarget, eventArgument, formSubmit) {
        this._doSubmit(eventTarget, eventArgument, formSubmit);
        this.page.ajax();
    },
    doLink: function (url, formSubmit) {
        this.doHandler(url, formSubmit);
        this.webForm.setAction(url);
        return false;
    },
    doHandler: function (url, formSubmit, historyRegistered) {
        this.disconnect();
        this.setForm(formSubmit);
        url = url.replace(/&amp;/g,"&");
        this.page.handler(url);

        historyControl.lastUrl = url;

        if ( !historyRegistered )
        {
            this.registerHistory(url);
        }
    },
    doRedirect: function (url, formSubmit) {
        if (this.isHandler(url)) {
            this.doHandler(url, formSubmit);
        }
        else {
            this.disconnect();
            window.location = url;
        }        
    },
	doDisableButton: function (buttonId) {
        this.getElementById(buttonId).disabled = true;
    },
    doPrintForm: function (url) {
        var w = screen.width * 0.75;
        var h = screen.height * 0.60;
        var print = window.open(url,'print',
        'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
        'top=0,left=0,statusbar=yes,resizeable=yes');
    },
	doPrintFile: function (eventTarget, eventArgument, formSubmit) {
        var ok = confirm(miolo.i18n.PRINT_FILE);
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
            this.doPostBack(eventTarget, eventArgument, formSubmit); 
            print.focus();
            form.target=tg;
        }
    },
    doShowPDF: function (eventTarget, eventArgument, formSubmit) {
        var ok = confirm(miolo.i18n.PRINT_PDF);
        if (ok)
        {
            this.doPostBack(eventTarget, eventArgument, formSubmit); 
        }
    },
    doWindow: function (url, element) {
            var tg = window.name;
            //var form = document.forms[0];
            var w = screen.width * 0.95;
            var h = screen.height * 0.80;
            var print = window.open(url,'print', 
            'toolbar=no,width='+w+',height='+h+',scrollbars=yes,' +
            'top=0,left=0,statusbar=yes,resizeable=yes');
            //form.target='print'; 
            //miolo.doPostBack(eventTarget, eventArgument, formSubmit);
            print.focus();
            //form.target=tg;
    },
    doPrintURL: function (url) {
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
    },
	showLoading: function() {
        try
        {
            this.getElementById("mLoadingMessageBg").style.display = "block";
            this.getElementById("mLoadingMessage").style.display   = "block";
            this.lastFocusElement = document.activeElement.id;
            this.setFocus("mLoadingMessageText");
        }
        catch(err)
        {}
		return true;
    },
    stopShowLoading: function()
    {
        try
        {
            this.getElementById("mLoadingMessageBg").style.display = "none";
            this.getElementById("mLoadingMessage").style.display   = "none";

            if ( this.lastFocusElement )
            {
                this.setFocus(this.lastFocusElement);
            }
        }
        catch(err)
        {}
		return true;
    },
	getMousePosition: function( e ) {
        is_ie = ( /msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent) );
        var posX;
        var posY;
        if ( is_ie )
        {
            posY = window.event.clientY + document.body.scrollTop;
            posX = window.event.clientX + document.body.scrollLeft;
        } else {
            posY = e.clientY + window.scrollY;
            posX = e.clientX + window.scrollX;
        }
        return new Array( posX, posY);
    },
    currency: function(field) {
    	var c = new Miolo.Currency;
        var f = miolo.getElementById(field);
	    c.format(f);
    },
    registerHistory: function(url, update) {
        params = url.split('index.php');
        dojo.hash(params[1], update);
    },
    configureHistory: function (url) {
        window.historyControl = {

            // the URL attribute is used for automatic calls of callback method
            url: url,

            // attribute to store the last URL
            lastUrl: '',

            // this method is called by onhashchange and by MIOLO's init function
            callback: function(hash) {
                if ( hash )
                {
                    this.url = this.url.split('index.php')[0] + 'index.php' + hash;
                }
                else
                {
                    this.url = url;
                }

                // call doHandler if it's the first access or if it's coming back from future
                if ( this.lastUrl == '' || this.url.split('index.php')[1] != this.lastUrl.split('index.php')[1] )
                {
                    miolo.doHandler(this.url, '__mainForm', true);
                }
            }
        }
        dojo.subscribe("/dojo/hashchange", window.historyControl, 'callback');
    },
    initHistory: function () {
        if ( dojo.hash() )
        {
            window.historyControl.callback(dojo.hash());
        }
        else
        {
            window.historyControl.callback(
                window.historyControl.url.split('index.php')[1]
            );
        }
    },
    loadDeps: function() {
        dojo.require("dojo.parser");
        dojo.require("dojo.hash");
        dojo.require("miolo.Dialog");
        dojo.require("dijit.form.DateTextBox");
        dojo.require("dojox.layout.ContentPane");
        dojo.require("dojo.string");
        dojo.require("dojo.i18n");
        dojo.registerModulePath("miolo", "../miolo");
        dojo.requireLocalization("miolo", "miolo");
        miolo.i18n = dojo.i18n.getLocalization("miolo", "miolo");
    },
    translate: function(key, substitutes) {
        var message = miolo.i18n[key];
        return substitutes ? dojo.string.substitute(message, substitutes) : message;
    },

    urlencode: function(s) {
        return encodeURIComponent(s).replace(/\%20/g, '+').replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/\~/g, '%7E');
    },

    urldecode: function(s) {
        return decodeURIComponent(s.replace(/\+/g, '%20').replace(/\%21/g, '!').replace(/\%27/g, "'").replace(/\%28/g, '(').replace(/\%29/g, ')').replace(/\%2A/g, '*').replace(/\%7E/g, '~'));
    },

    triggerEvent: function(element, event) {
        if ( event && event.slice(0, 2) == "on" )
        {
            event = event.slice(2);
        }

        if ( dojo.doc.createEvent )
        {
            var evObj = dojo.doc.createEvent("HTMLEvents");
            evObj.initEvent(event , true, true);
            dojo.byId(element).dispatchEvent(evObj);
        }
        else if (dojo.doc.createEventObject) // IE
        {
            dojo.byId(element).fireEvent("on" + event);
        }
    },

    setReadOnly: function (elementId, readOnly) {
        var element = dojo.byId(elementId);

        if ( element )
        {
            if (readOnly == true)
            {
                element.setAttribute('readonly',true);
                var cssClass = element.getAttribute('class');
                element.setAttribute('class', cssClass + ' mReadOnly');
            }
            else
            {
                element.removeAttribute('readonly');
                var cssClass = element.getAttribute('class');
                element.setAttribute('class', cssClass.replace(' mReadOnly',''));
            }
        }
        else
        {
            console.error("miolo.setReadOnly(): Element '" + elementId + "' not found.");
        }
    },

    setFocus: function (id) {
        var fields = dojo.query("[id='" + id + "']");

        // Set the focus on the last field in the case there's more than one field with the same id
        fields[fields.length-1].focus();
    },

    /**
     * Method which removes non numbers from a field. Can be used on events such as onchange, onkeyup, etc.
     *
     * @param object Input field.
     */
    removeNonNumbers: function (input) {
        // TODO: Handle negative numbers?
        if ( input.value.match( /[^\d]/g ) )
        {
            input.value = input.value.replace( /[^\d]/g, '' );
        }
    },

    /**
     * Return the URL parameters.
     *
     * @param string URL address. Default value is current URL.
     * @return array Parameters.
     */
    getURLParameters: function(url) {

        if ( !url )
        {
            url = window.location.href;
        }

        var map = {};
        url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
            map[key] = value;
        });

        return map;
    }
});

var miolo = new Miolo;

if (window.parent && window.parent.miolo)
{
    miolo.windows.base = window.parent.miolo.windows.base;
}

// FIXME: Remove this when Dojo is updated.
// Fix for Dojo under IE9. http://bugs.dojotoolkit.org/changeset/23718
var fixIE9 = function () {
    dijit._frames=new function(){
        var _18=[];
        this.pop=function(){
            var _19;
            if(_18.length){
                _19=_18.pop();
                _19.style.display="";
            }else{
                // The fix is here. Line above was: if(dojo.isIE){
                if(dojo.isIE < 9){
                    var _1a=dojo.config["dojoBlankHtmlUrl"]||(dojo.moduleUrl("dojo","resources/blank.html")+"")||"javascript:\"\"";
                    var _1b="<iframe src='"+_1a+"'"+" style='position: absolute; left: 0px; top: 0px;"+"z-index: -1; filter:Alpha(Opacity=\"0\");'>";
                    _19=dojo.doc.createElement(_1b);
                }else{
                    _19=dojo.create("iframe");
                    _19.src="javascript:\"\"";
                    _19.className="dijitBackgroundIframe";
                    dojo.style(_19,"opacity",0.1);
                }
                _19.tabIndex=-1;
            }
            return _19;
        };
        this.push=function(_1c){
            _1c.style.display="none";
            _18.push(_1c);
        };
    }();
}
setTimeout(fixIE9, 0);
