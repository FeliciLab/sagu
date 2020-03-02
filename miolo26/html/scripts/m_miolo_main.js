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
        miolo.windows.handle[windowId] = new MWindow(windowId);
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
    loadDeps: function(isMobile) {

        dojo.require("dojox.layout.ContentPane");

        if ( isMobile )
        {
            dojo.require("dojox.mobile");
            dojo.require("dojox.mobile.parser");
            dojo.requireIf(!dojo.isWebKit, "dojox.mobile.compat");
            dojo.ready(function(){
                dojox.mobile.parser.instantiate([dojo.byId('__mainForm')]);
                dojox.mobile.parser.instantiate([dojo.byId('__mainForm__scripts')]);
            });
        }
        else
        {
            dojo.require("dojo.parser");
            dojo.ready(function(){
                dojo.parser.instantiate([dojo.byId('__mainForm')]);
                dojo.parser.instantiate([dojo.byId('__mainForm__scripts')]);
            });
        }

        dojo.require("miolo.Dialog");
        dojo.require("dijit.form.DateTextBox");
        dojo.require("dojo.hash");
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

miolo = new Miolo;

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
                    var _1a=dojo.config["dojoBlankHtmlUrl"]||(require.toUrl("dojo","resources/blank.html")+"")||"javascript:\"\"";
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
dojo.declare("Miolo.Hash",null, {
	length: 0,
	items: new Array(),
    constructor: function() {
        this.length = 0;
    },
	remove: function(in_key) {
		var tmp_value;
		if (typeof(this.items[in_key]) != 'undefined') {
			this.length--;
			var tmp_value = this.items[in_key];
			delete this.items[in_key];
		}
		return tmp_value;
	},
	get: function(in_key) {
		return this.items[in_key];
	},
	set: function(in_key, in_value) {
		if (typeof(in_value) != 'undefined') {
			if (typeof(this.items[in_key]) == 'undefined') {
				this.length++;
			}
			this.items[in_key] = in_value;
		}
		return in_value;
	},
	has: function(in_key) {
		return typeof(this.items[in_key]) != 'undefined';
	}
});alertarErroAjax = false;

dojo.require("dojo.io.script");

dojo.declare("Miolo.Page", null, {
    version: '0.1',
    scripts: new Miolo.Hash,
    controls: new Miolo.Hash,
    tokenId: '',
    ajaxEvent: 'no',
    fileUpload: 'no',
    themeLayout: 'default', // 'default' (link or handler), 'dynamic' (postback or ajax), 'window'
    constructor: function() {
        this.obj = this; 
    },
    getContent: function() {
        return {
            __FORMSUBMIT: miolo.webForm ? miolo.webForm.id : '',
            __ISAJAXCALL: 'yes',
            __THEMELAYOUT: miolo.page.themeLayout,
            __MIOLOTOKENID: miolo.page.tokenId,
            __ISFILEUPLOAD: miolo.page.fileUpload,
            __ISAJAXEVENT: miolo.page.ajaxEvent
        }
    },
    handler: function(gotourl) {
        this.themeLayout = 'default';
        this.fileUpload = 'no';
        var ajaxHandler = new Miolo.Ajax({
            url: gotourl,
            response_type: 'JSON',
            parameters: miolo.page.getContent(),
            callback_function: function(result,ioArgs) {
                miolo.page.evalresult(result);
            }
        });
        ajaxHandler.call();
    },
    ajax: function() {
        this.setValidators(false);
        if (miolo.webForm.onSubmit())
        {
            this.setValidators(true);
            this.ajaxEvent = 'yes';
            this.themeLayout = 'dynamic';
            this.fileUpload = 'no';
            var ajaxSubmit = new Miolo.Ajax({
                form: 'frm_'+miolo.webForm.id,
                content: miolo.page.getContent(),
                response_type: 'JSON',
                callback_function: function(result,ioArgs) {
                    if ( alertarErroAjax )
                    {
                        if ( result == null )
                        {
                            alert('Ops! Houve algum problema de conexão com o servidor. Por favor, tente mais tarde.');
                        }
                    }
                    
                    miolo.page.evalresult(result);
                }
            });
            ajaxSubmit.call();
        }
    },
    postback: function() {
        this.themeLayout = 'dynamic';
        var fileUpload = miolo.getElementById('__ISFILEUPLOADPOST');
        this.fileUpload = fileUpload ? fileUpload.value : this.fileUpload;

        var ajaxPostBack = new Miolo.Ajax({
            form: 'frm_'+miolo.webForm.id,
            content: miolo.page.getContent(),
            response_type: 'JSON',
            callback_function: function(result,ioArgs) {
                miolo.page.evalresult(result);
            }
        });
        ajaxPostBack.call();
    },
    getWindow: function(winid, domid, winurl) {
        this.themeLayout = 'window';
        this.fileUpload = 'no';
        var ajaxWindow = new Miolo.Ajax({
            url: winurl,
            response_type: 'JSON',
            parameters: miolo.page.getContent(),
            callback_function: function(result,ioArgs) {
                var winid = miolo.webForm.id;
                miolo.page.evalresult(result, winid);
            }
        });
        ajaxWindow.call();
    },
    setValidators: function(status) {
        if (miolo.webForm.validators) {
            miolo.webForm.validators.on = status;
        }
    },
    clearelement: function(element) {
        if (element)
        {
            if ( element.hasChildNodes() )
            {
                while ( element.childNodes.length >= 1 )
                {
                    element.removeChild( element.firstChild );       
                } 
            }   
        }
    },
    includejs: function(tag) {
        var md5 = new Miolo.md5();
        var regexp = /src=\"(.*)\"/mg;
        while (f = regexp.exec(tag))
        {
            var name = md5.MD5(f[1]);
            if (!this.scripts.get(name)) // not loaded yet
            {
                dojo.xhrGet({
                    url: f[1],
                    handleAs: "javascript",
                    sync: true
                });
                this.scripts.set(name,f[1]);
            }
        }
    },

    /**
     * Include a JavaScript file from a different domain.
     * 
     * @param url string URL.
     * @param onLoad function Function to call on load.
     * @param persistent boolean Whether to don't reload it each request.
     */
    includeexternaljs: function(url, onLoad, persistent) {
        var md5 = new Miolo.md5();
        var name = md5.MD5(url);

        if ( !onLoad )
        {
            onLoad = function() {};
        }

        // Check persistence or if it isn't loaded yet
        if ( !persistent || !this.scripts.get(name) )
        {
            dojo.io.script.get({ 
                url: url, 
                load: onLoad 
            })

            this.scripts.set(name, url);
        }
    },

    evalresult: function(result, winid) {
        if (result)
        {
            var response = result.data;
            miolo.page.evalresponse(response, winid);
        }
        else
        {
            miolo.page.tokenId = '';
        }
    },
    evalelement: function(response) {
        if (response.html)
        {
            for(i = 0; i < response.html.length; i++)
            {
                response.html[i] = response.html[i].replace(/<--ta-->/g,'</textarea>');
                var element = dijit.byId(response.element[i]);
                if (element)
                {
//                    element.destroyDescendants();
                    try
                    {
                        dojo.forEach(element.getChildren(), function(widget){ widget.destroyRecursive(); });

                        if ( element && (typeof element.setContent === 'function') )
                        {
                            element.setContent(response.html[i]);
                        }
                        else if ( typeof element.set === 'function' )
                        {
                            element.set('content', response.html[i]);
                        }
                    }
                    catch (err)
                    {
                        console.log(err);
                    }
                }
                else if (element = miolo.getElementById(response.element[i]))
                {
                    this.clearelement(element);
                    element.innerHTML = response.html[i];
                }
                
            }
        }
    },
    evalresponse: function(response, winid) {
        var errorMsg = response.scripts[4];
        if (errorMsg != '')
        {
            miolo.page.stdout(errorMsg);
            miolo.page.tokenId = '';
        }
        else 
        {
            if (response.scripts[0] != '')
            {
                miolo.page.includejs(response.scripts[0]);
            }
            this.evaljs(response.scripts[1]);
            this.evalelement(response);
            if (winid) 
            {
                dijit.byId(winid).show();
            }
            if (response.form)
            {
                miolo.page.onload(response);
            }
        }
    },
    onload: function(response) {
        var form = response.form;
        var scripts = "<script type=\"text/javascript\">\n"+
            "miolo.addForm( '" + form + "');\n " +
            "miolo.getForm( '" + form + "').onLoad = function() { " + response.scripts[2] +  "};\n" +
            "miolo.getForm( '" + form + "').onSubmit = function() { " + response.scripts[3] + "};\n" +
            "miolo.setForm( '" + form + "');\n"+
            "miolo.getForm( '" + form + "').onLoad();\n"+
            "</script>";

        var scriptsDiv = dijit.byId(form + '__scripts');
        if ( typeof scriptsDiv.setContent === 'function' )
        {
            scriptsDiv.setContent(scripts);
        }
        else
        {
            scriptsDiv.set('content', scripts);
        }
    },
    evaljs: function(script) {
        if (script != '')
        {
            dojo.eval(script);
        }
    },
    stdout: function(msg) {
        var s = miolo.getElementById('stdout');
        s.innerHTML += '<br>' + msg;
    },
    setDynamicStyle: function(css) {
        var styleElement = miolo.getElementById('mPageDynamicStyle');

        if ( styleElement )
        {
            document.getElementsByTagName("head")[0].removeChild(styleElement);
        }

        styleElement = document.createElement('style');
        styleElement.type = 'text/css';
        styleElement.id = 'mPageDynamicStyle';

        if ( styleElement.styleSheet )
        {
            styleElement.styleSheet.cssText = css;
        }
        else
        {
            styleElement.appendChild(document.createTextNode(css));
        }

        document.getElementsByTagName("head")[0].appendChild(styleElement);
    }
});

miolo.page = new Miolo.Page;
var urlFileUpload = 'fileUpload.php';

dojo.declare("Miolo.Ajax",null,
{
    loading: "<img src=\"images/loading.gif\" border=\"0\" alt=\"\">",
    url: null,
    form: null,
    response_type: 'JSON',
    updateElement: null,
    parameters: null,
    content: null,
    remote_method: '',
    constructor: function(obj) {
		if (obj.url) this.url = obj.url;
		if (obj.form) this.form = obj.form;
		if (obj.content) this.content = obj.content;
		if (obj.response_type) this.response_type = obj.response_type;
		if (obj.updateElement) this.updateElement = obj.updateElement;
        if (obj.parameters) this.parameters = obj.parameters;
        if (obj.remote_method) this.remote_method = obj.remote_method;
		if (obj.callback_function) this.callback_function = obj.callback_function;
	},
	update: function (result, ioArgs) {
        miolo.getElementById(this.updateElement).innerHTML = result;
        miolo.stopShowLoading();
	},
    error: function(error,ioArgs) {
        if (errDiv = miolo.getElementById('stdout'))
        {
            errDiv.innerHTML = ioArgs.xhr.responseText;
        }
        miolo.stopShowLoading();
        
        var div = document.getElementById("divErroConexao");
    
        if( div !== null )
        {
            div.style.display = "block";
            div.style.marginTop = "55px";
            div.style.marginBottom = "-50px";
            div.className = "mMessage mMessageError";
            
            // Se foi um erro de conexão
            if( error.status === 0 )
            {
                div.innerHTML = "<strong>Ocorreu um erro ao tentar receber a resposta do servidor. "
                              + "Verifique sua conexão com a internet.<br/></strong>";
                
            }
            else if( error.status === 500 )
            {
                div.innerHTML = "<strong>Erro (código 500): Ocorreu um erro no servidor. Por favor, tente novamente. "
                              + "Caso o problema persista, contate a equipe de suporte da SOLIS.<br/></strong>";
                
            }
            else
            {
                div.innerHTML = "<strong>Ocorreu um erro inesperado. Por favor, tente novamente. "
                              + "Caso o problema persista, contate a equipe de suporte da SOLIS.<br/></strong>";
                
            }
            
            window.scrollTo(0,0);

        }
                
    },
    load: function() {
        miolo.stopShowLoading();
    },
    ioerror: function(error,ioArgs) {
        console.log('Error!');
        console.log(ioArgs);
        console.log(error);
    },
    call: function() {
        miolo.showLoading();
        var response_type = this.response_type.toLowerCase();
        if (miolo.getElementById('__ISAJAXCALL'))
        {
            miolo.setElementValueById('__ISAJAXCALL', 'yes');
        }
        if (miolo.getElementById('cpaint_response_type'))
        {
            miolo.setElementValueById('cpaint_response_type', response_type);
        }
		if (this.updateElement) {
           this.update(this.loading);
		}
		var callback_function = this.callback_function ? this.callback_function : this.update;
        if (this.form != null) {
            this.content.cpaint_response_type = response_type;

            var actionVars = miolo.getURLParameters(miolo.webForm.getAction());

            if ( miolo.page.fileUpload == 'yes' && actionVars['action'] != 'lookup' )
            {
                dojo.io.iframe.send({
                    url: urlFileUpload,
                    form: this.form,
                    method: "post",
                    content: this.content,
                    timeoutSeconds: 5,
                    preventCache: true,
                    error: this.ioerror,
                    handleAs: "json",
                    handle: function(result,ioArgs) {
                        if ( result.uploaded == 'true' && result.uploadInfo )
                        {
                            this.content.uploadInfo = result.uploadInfo;
                            this.content.uploadErrors = result.uploadErrors;
                        }
                        else if ( result.uploadErrors )
                        {
                            this.content.uploadErrors = result.uploadErrors;
                        }
                        miolo.page.fileUpload = 'no';
                        this.content.__ISFILEUPLOAD = 'no';

                        dojo.xhrPost({
                            form: this.form,
                            content: this.content,
                            error: this.error,
                            load: function() { miolo.stopShowLoading() },
                            handleAs: "json",
                            handle: callback_function
                        });
                    }
                });
            }
            else
            {
                dojo.xhrPost({
                    form: this.form,
                    content: this.content,
                    error: this.error,
                    load: this.load,
                    handleAs: "json",
                    handle: callback_function
                });
            }
        }
        else {
            var goUrl = this.url ? this.url : miolo.getCurrentURL();
            var parameters = {};
            if (this.parameters != null)
            {
                if (dojo.isFunction(this.parameters))
                {
                    parameters = this.parameters();
                    if (!dojo.isObject(parameters))
                    {
                        parameters = {__EVENTARGUMENT: parameters};
                    }
                }
                else
                {
                    parameters = this.parameters;
                }
            }
            parameters.__ISAJAXCALL = 'yes';
            parameters.__EVENTTARGETVALUE = this.remote_method;
            if (response_type == 'object')
            {
                response_type = 'json';
            }
            /*
            if (goUrl.indexOf('?') > -1)
            {

                var nv = goUrl.substr(goUrl.indexOf('?')+1).split('&');
                var qs = '';
                for(i = 0; i < nv.length; i++)
                {
                    eq = nv[i].indexOf('=');
                    qs = qs + encodeURIComponent(nv[i].substring(0,eq)) + '=' + encodeURIComponent(nv[i].substring(eq + 1)) + '&';
                }
                goUrl = goUrl.substr(0,goUrl.indexOf('?')) + '?' + qs;
            }
            */
            parameters.cpaint_response_type = response_type;
            dojo.xhrPost({
                updateElement: this.updateElement,
                url: goUrl,
                content: parameters,
                error: this.error,
                load: this.load,
                handleAs: response_type,
                handle: callback_function
            });
        }
	}
});
/**
*
*  UTF-8 data encode / decode
*  Adaptado a partir de: http://www.webtoolkit.info/
*
**/

dojo.declare("Miolo.utf8",null,
{
	constructor: function() {
	},
    // public method for url encoding
    encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // public method for url decoding
    decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }
});
dojo.declare("Miolo.Box",null,
{
	constructor: function() {
	    this.boxMoving = false;
        //control move box (drag)
        this.boxToMove = null;
        //control how box will be moving
        this.boxPositions = new Array(2);
        this.onMouseMoveHandler = miolo.associateObjWithEvent(this,'boxPosition');
	},
    showBox: function( event, div ) {
		var div = Event.element(event);
        var id = div.id.substring( 4 );
        var aux = miolo.getElementById(id);
        aux.style.display = '';
        div.style.display = 'none';
		Event.stopObserving(document,'mousemove',this.onMouseMoveHandler);
    },
    closeBox: function( e, boxId ) {
		var box = miolo.getElementById(boxId);
        var div  = miolo.getElementById('min_' + boxId);
		Event.stopObserving(document,'mousemove',this.onMouseMoveHandler);
        if ( div == null )
        {
            var cont = miolo.getElementById('m-container-minbar');
            if ( ! cont )
            {
                return false;
            }
            div  = document.createElement('span');
            div.id   = 'min_' + boxId;
            onMouseClick = miolo.associateObjWithEvent(this,'showBox');
			Event.observe(div,'click',onMouseClick);
            var text;
			var ele = document.getElementsByClassName('caption',box);
            if ( ele.length > 0 )
            {
               text = ele[0].innerHTML;
            }
            else
            {
               text = 'Box';
            }
            div.innerHTML = text;
            div.className = 'm-box-title-minimized';

            cont.appendChild(div);
            box.style.display = 'none';
            cont.style.textAlign = 'left';
        }
        else
        {
            div.style.display = div.style.display == 'none' ? '' : 'none';
            box.style.display = 'none';
        }
    },
    hideBoxContent: function ( box ) {
        //hide all box contents
        for ( var i = miolo.isIE ? 1 : 2; i< box.childNodes.length; i++ )
        {
            style = box.childNodes[i].style;
            if( typeof(style) != 'undefined') style.display = style.display == 'none' ? '' : 'none';
        }
    },
    moveBox: function( e, box, move ) {
        if( box.style.display == 'none' )
            return false;
        if ( ! Event.isLeftClick(e) ) { //case the mouse button is not the left
            return this.closeBox( e, box );
        }
        //control the box click and drag
		if (move) {
            Event.observe(document,'mousemove',this.onMouseMoveHandler);
		}
		else {
    		Event.stopObserving(document,'mousemove',this.onMouseMoveHandler);
		}
        this.boxToMove = box;
        this.boxToMove.style.position = 'relative';

        if( move )  //if click, control the initial positions
        {
            var diffLeft = this.boxToMove.style.left ? parseInt(this.boxToMove.style.left) : 0;
            var diffTop  = this.boxToMove.style.top  ? parseInt(this.boxToMove.style.top ) : 0;
            this.boxPositions[0] = Event.pointerX(e) - diffLeft;
            this.boxPositions[1] = Event.pointerY(e) - diffTop;
        }
        this.boxMoving = move; //control if is to move the box

        if ( ! move )
        {
            this.boxToMove.style.position = 'absolute';
            document.cookie = this.boxToMove.id + '_position=' + this.boxToMove.style.left + ',' +
                          this.boxToMove.style.top         + ',' + this.boxToMove.tagName + ',' +
                          this.boxToMove.className;
            this.boxToMove.style.position = 'relative';
        }
        return ! move; //if move = false, disable text selection else enable
    },
    boxPosition: function ( event, element ) {
        var posX = Event.pointerX(event); //control the top left
        var posY = Event.pointerY(event);; //control the top position
        var st = this.boxToMove.style; //the box style
        st.left = (posX - this.boxPositions[0] ) + "px"; //set the left position
        st.top  = (posY - this.boxPositions[1] ) + "px"; //set the top  position
    },
    setBoxPositions: function( ) {

        var cookies = document.cookie.split(';');

        for( var i=0; i < cookies.length; i++ )
        {
            var pos = cookies[i].indexOf('_position');
            if( pos > 0 )
            {
                var id  = cookies[i].substr( 1, pos-1);
                var box = miolo.getElementById( id );
                var aux = cookies[i].split('=')[1].split(',');

                if( box != null && box.tagName == aux[2] && box.className == aux[3] )
                {
                    box.style.position = 'absolute';
                    box.style.left     = aux[0];
                    box.style.top      = aux[1];
                    box.style.position = 'relative';
                }
            }
        }
    }
});

miolo.box = new Miolo.Box();dojo.declare("Miolo.Form", null, {
    id: null,
    onLoad: null,
    onSubmit: null,
    validators: null,
    connections: null,
    constructor: function(id) {
		this.id = id;
        this.connections = [];
	},
    setFocus: function (fieldName) {
		if (fieldName == '') {
			var element = null;
			var f = miolo.getElementById(this.id);
    	    var children = f.getElementsByTagName('input');
			if (children.length == 0) {
        	    var children = f.getElementsByTagName('select');
    			if (children.length > 0) {
					element = children[0];
				}
			} else {
				element = children[0];
			}
		} else {
			var element = miolo.getElementById(fieldName);
		}
        if (element != null) {
           element.focus();
        }
    },
	getInputs: function() {
      var getstr = new Object();
	  var f = miolo.getElementById(this.id);
      var inputs = f.getElementsByTagName('input');
      for (var i = 0, length = inputs.length; i < length; i++) {
	      var input = inputs[i];
		  if ((input.type == "text") || (input.type == "hidden")) {
			  if (getstr[input.name])
			  {
	  			  getstr[input.name] += "&" + input.value;
			  } else {
    			  getstr[input.name] = input.value;
			  }
		  }
		  if (input.type ==	"checkbox") {
			  if (input.checked) {
				  getstr[input.name] = (input.value == '' ? 'on' : input.value);
			  }
		  } 
		  if (input.type ==	"radio") {
			  if (input.checked) {
				  getstr[input.name] = input.value;
			  }
		  } 
      }
      var inputs = f.getElementsByTagName('select');
      for (var i = 0, length = inputs.length; i < length; i++) {
	      var input = inputs[i];
		  getstr[input.name] = input.options[input.selectedIndex].value;
	  }
	  return getstr;
	},
    getForm: function() {
        return miolo.getElementById('frm_'+this.id);               
    },
    setAction: function(url) {
        miolo.getElementById('frm_'+this.id).action = url;               
    },
    getAction: function() {
        return miolo.getElementById('frm_'+this.id).action;
    },
    setEnctype: function(enctype) {
        miolo.getElementById('frm_'+this.id).setAttribute('enctype', enctype);
    },
    connect: function(elementId, event, handler) {
        var node = dojo.byId(elementId);
        if (!node) return;
        this.connections.push(
           dojo.connect(node,event,handler)
        );
    },
    disconnect: function() {
        dojo.forEach(this.connections, dojo.disconnect);
        this.connections.length = 0;
    },
    hideScroll: function() {
        if (document.all && document.createAttribute && document.compatMode != 'BackCompat') {
            // IE6 (and above) in standards mode
            document.getElementsByTagName('html')[0].style.overflow = 'hidden'; 
        } else {
            document.body.style.overflow = 'hidden';
        }
    },
    showScroll: function() {
        if (document.all && document.createAttribute && document.compatMode != 'BackCompat') {
            // IE6 (and above) in standards mode
            document.getElementsByTagName('html')[0].style.overflow = '';
        } 
        else {
            document.body.style.overflow = '';
        }
    }
});/*
 *  md5.jvs 1.0b 27/06/96
 *
 * Javascript implementation of the RSA Data Security, Inc. MD5
 * Message-Digest Algorithm.
 *
 * Copyright (c) 1996 Henri Torgemane. All Rights Reserved.
 *
 * Permission to use, copy, modify, and distribute this software
 * and its documentation for any purposes and without
 * fee is hereby granted provided that this copyright notice
 * appears in all copies. 
 *
 * Of course, this soft is provided "as is" without express or implied
 * warranty of any kind.
 *
 * $Id: md5.js,v 1.1.1.1 2000/04/17 16:40:07 kk Exp $
 *
 * Adapted to Miolo by Ely Matos (ely.matos@ufjf.edu.br)
 */

function md5_array(n) {
  for(i=0;i<n;i++) this[i]=0;
  this.length=n;
}

dojo.declare("Miolo.md5",null,
{
    constructor: function() {
    },
    array: function (n) {
        var a;
        for(i=0;i<n;i++) a[i]=0;
        a.length=n;
        return a;
    },
 /* Some basic logical functions had to be rewritten because of a bug in
 * Javascript.. Just try to compute 0xffffffff >> 4 with it..
 * Of course, these functions are slower than the original would be, but
 * at least, they work!
 */
    integer: function (n) { return n%(0xffffffff+1); },
    shr: function (a,b) {
        a=this.integer(a);
        b=this.integer(b);
        if (a-0x80000000>=0) {
            a=a%0x80000000;
            a>>=b;
            a+=0x40000000>>(b-1);
        } else
            a>>=b;
        return a;
    },
    shl1: function(a) {
        a=a%0x80000000;
        if (a&0x40000000==0x40000000){
            a-=0x40000000;  
            a*=2;
            a+=0x80000000;
        } else
            a*=2;
        return a;
    },
    shl: function (a,b) {
        a=this.integer(a);
        b=this.integer(b);
        for (var i=0;i<b;i++) a=this.shl1(a);
        return a;
    },
    and: function (a,b) {
        a=this.integer(a);
        b=this.integer(b);
        var t1=(a-0x80000000);
        var t2=(b-0x80000000);
        if (t1>=0) 
            if (t2>=0) 
              return ((t1&t2)+0x80000000);
            else
              return (t1&b);
        else
            if (t2>=0)
              return (a&t2);
            else
              return (a&b);  
    },
    or: function (a,b) {
  a=this.integer(a);
  b=this.integer(b);
  var t1=(a-0x80000000);
  var t2=(b-0x80000000);
  if (t1>=0) 
    if (t2>=0) 
      return ((t1|t2)+0x80000000);
    else
      return ((t1|b)+0x80000000);
  else
    if (t2>=0)
      return ((a|t2)+0x80000000);
    else
      return (a|b);  
},
    xor: function (a,b) {
  a=this.integer(a);
  b=this.integer(b);
  var t1=(a-0x80000000);
  var t2=(b-0x80000000);
  if (t1>=0) 
    if (t2>=0) 
      return (t1^t2);
    else
      return ((t1^b)+0x80000000);
  else
    if (t2>=0)
      return ((a^t2)+0x80000000);
    else
      return (a^b);  
},
    not:function (a) {
  a=this.integer(a);
  return (0xffffffff-a);
},
/* Here begin the real algorithm */

    state : new md5_array(4),
    count : new md5_array(2),
//	count[0] = 0,
//	count[1] = 0,                     
    buffer : new md5_array(64),
    transformBuffer : new md5_array(16),
    digestBits : new md5_array(16),

    S11 : 7,
    S12 : 12,
    S13 : 17,
    S14 : 22,
    S21 : 5,
    S22 : 9,
    S23 : 14,
    S24 : 20,
    S31 : 4,
    S32 : 11,
    S33 : 16,
    S34 : 23,
    S41 : 6,
    S42 : 10,
    S43 : 15,
    S44 : 21,

    F: function (x,y,z) {
	return this.or(this.and(x,y),this.and(this.not(x),z));
    },

    G: function (x,y,z) {
	return this.or(this.and(x,z),this.and(y,this.not(z)));
    },

    H: function (x,y,z) {
	return this.xor(this.xor(x,y),z);
    },

    I: function(x,y,z) {
	return this.xor(y ,this.or(x , this.not(z)));
    },

    rotateLeft: function(a,n) {
	return this.or(this.shl(a, n),(this.shr(a,(32 - n))));
    },

    FF: function(a,b,c,d,x,s,ac) {
        a = a+this.F(b, c, d) + x + ac;
	a = this.rotateLeft(a, s);
	a = a+b;
	return a;
    },

    GG: function(a,b,c,d,x,s,ac) {
	a = a+this.G(b, c, d) +x + ac;
	a = this.rotateLeft(a, s);
	a = a+b;
	return a;
    },

    HH: function(a,b,c,d,x,s,ac) {
	a = a+this.H(b, c, d) + x + ac;
	a = this.rotateLeft(a, s);
	a = a+b;
	return a;
    },

    II: function(a,b,c,d,x,s,ac) {
	a = a+this.I(b, c, d) + x + ac;
	a = this.rotateLeft(a, s);
	a = a+b;
	return a;
    },

    transform: function(buf,offset) { 
	var a=0, b=0, c=0, d=0; 
	var x = this.transformBuffer;
	
	a = this.state[0];
	b = this.state[1];
	c = this.state[2];
	d = this.state[3];
	
	for (i = 0; i < 16; i++) {
	    x[i] = this.and(buf[i*4+offset],0xff);
	    for (j = 1; j < 4; j++) {
		x[i]+=this.shl(this.and(buf[i*4+j+offset] ,0xff), j * 8);
	    }
	}

	/* Round 1 */
	a = this.FF ( a, b, c, d, x[ 0], this.S11, 0xd76aa478); /* 1 */
	d = this.FF ( d, a, b, c, x[ 1], this.S12, 0xe8c7b756); /* 2 */
	c = this.FF ( c, d, a, b, x[ 2], this.S13, 0x242070db); /* 3 */
	b = this.FF ( b, c, d, a, x[ 3], this.S14, 0xc1bdceee); /* 4 */
	a = this.FF ( a, b, c, d, x[ 4], this.S11, 0xf57c0faf); /* 5 */
	d = this.FF ( d, a, b, c, x[ 5], this.S12, 0x4787c62a); /* 6 */
	c = this.FF ( c, d, a, b, x[ 6], this.S13, 0xa8304613); /* 7 */
	b = this.FF ( b, c, d, a, x[ 7], this.S14, 0xfd469501); /* 8 */
	a = this.FF ( a, b, c, d, x[ 8], this.S11, 0x698098d8); /* 9 */
	d = this.FF ( d, a, b, c, x[ 9], this.S12, 0x8b44f7af); /* 10 */
	c = this.FF ( c, d, a, b, x[10], this.S13, 0xffff5bb1); /* 11 */
	b = this.FF ( b, c, d, a, x[11], this.S14, 0x895cd7be); /* 12 */
	a = this.FF ( a, b, c, d, x[12], this.S11, 0x6b901122); /* 13 */
	d = this.FF ( d, a, b, c, x[13], this.S12, 0xfd987193); /* 14 */
	c = this.FF ( c, d, a, b, x[14], this.S13, 0xa679438e); /* 15 */
	b = this.FF ( b, c, d, a, x[15], this.S14, 0x49b40821); /* 16 */

	/* Round 2 */
	a = this.GG ( a, b, c, d, x[ 1], this.S21, 0xf61e2562); /* 17 */
	d = this.GG ( d, a, b, c, x[ 6], this.S22, 0xc040b340); /* 18 */
	c = this.GG ( c, d, a, b, x[11], this.S23, 0x265e5a51); /* 19 */
	b = this.GG ( b, c, d, a, x[ 0], this.S24, 0xe9b6c7aa); /* 20 */
	a = this.GG ( a, b, c, d, x[ 5], this.S21, 0xd62f105d); /* 21 */
	d = this.GG ( d, a, b, c, x[10], this.S22,  0x2441453); /* 22 */
	c = this.GG ( c, d, a, b, x[15], this.S23, 0xd8a1e681); /* 23 */
	b = this.GG ( b, c, d, a, x[ 4], this.S24, 0xe7d3fbc8); /* 24 */
	a = this.GG ( a, b, c, d, x[ 9], this.S21, 0x21e1cde6); /* 25 */
	d = this.GG ( d, a, b, c, x[14], this.S22, 0xc33707d6); /* 26 */
	c = this.GG ( c, d, a, b, x[ 3], this.S23, 0xf4d50d87); /* 27 */
	b = this.GG ( b, c, d, a, x[ 8], this.S24, 0x455a14ed); /* 28 */
	a = this.GG ( a, b, c, d, x[13], this.S21, 0xa9e3e905); /* 29 */
	d = this.GG ( d, a, b, c, x[ 2], this.S22, 0xfcefa3f8); /* 30 */
	c = this.GG ( c, d, a, b, x[ 7], this.S23, 0x676f02d9); /* 31 */
	b = this.GG ( b, c, d, a, x[12], this.S24, 0x8d2a4c8a); /* 32 */

	/* Round 3 */
	a = this.HH ( a, b, c, d, x[ 5], this.S31, 0xfffa3942); /* 33 */
	d = this.HH ( d, a, b, c, x[ 8], this.S32, 0x8771f681); /* 34 */
	c = this.HH ( c, d, a, b, x[11], this.S33, 0x6d9d6122); /* 35 */
	b = this.HH ( b, c, d, a, x[14], this.S34, 0xfde5380c); /* 36 */
	a = this.HH ( a, b, c, d, x[ 1], this.S31, 0xa4beea44); /* 37 */
	d = this.HH ( d, a, b, c, x[ 4], this.S32, 0x4bdecfa9); /* 38 */
	c = this.HH ( c, d, a, b, x[ 7], this.S33, 0xf6bb4b60); /* 39 */
	b = this.HH ( b, c, d, a, x[10], this.S34, 0xbebfbc70); /* 40 */
	a = this.HH ( a, b, c, d, x[13], this.S31, 0x289b7ec6); /* 41 */
	d = this.HH ( d, a, b, c, x[ 0], this.S32, 0xeaa127fa); /* 42 */
	c = this.HH ( c, d, a, b, x[ 3], this.S33, 0xd4ef3085); /* 43 */
	b = this.HH ( b, c, d, a, x[ 6], this.S34,  0x4881d05); /* 44 */
	a = this.HH ( a, b, c, d, x[ 9], this.S31, 0xd9d4d039); /* 45 */
	d = this.HH ( d, a, b, c, x[12], this.S32, 0xe6db99e5); /* 46 */
	c = this.HH ( c, d, a, b, x[15], this.S33, 0x1fa27cf8); /* 47 */
	b = this.HH ( b, c, d, a, x[ 2], this.S34, 0xc4ac5665); /* 48 */

	/* Round 4 */
	a = this.II ( a, b, c, d, x[ 0], this.S41, 0xf4292244); /* 49 */
	d = this.II ( d, a, b, c, x[ 7], this.S42, 0x432aff97); /* 50 */
	c = this.II ( c, d, a, b, x[14], this.S43, 0xab9423a7); /* 51 */
	b = this.II ( b, c, d, a, x[ 5], this.S44, 0xfc93a039); /* 52 */
	a = this.II ( a, b, c, d, x[12], this.S41, 0x655b59c3); /* 53 */
	d = this.II ( d, a, b, c, x[ 3], this.S42, 0x8f0ccc92); /* 54 */
	c = this.II ( c, d, a, b, x[10], this.S43, 0xffeff47d); /* 55 */
	b = this.II ( b, c, d, a, x[ 1], this.S44, 0x85845dd1); /* 56 */
	a = this.II ( a, b, c, d, x[ 8], this.S41, 0x6fa87e4f); /* 57 */
	d = this.II ( d, a, b, c, x[15], this.S42, 0xfe2ce6e0); /* 58 */
	c = this.II ( c, d, a, b, x[ 6], this.S43, 0xa3014314); /* 59 */
	b = this.II ( b, c, d, a, x[13], this.S44, 0x4e0811a1); /* 60 */
	a = this.II ( a, b, c, d, x[ 4], this.S41, 0xf7537e82); /* 61 */
	d = this.II ( d, a, b, c, x[11], this.S42, 0xbd3af235); /* 62 */
	c = this.II ( c, d, a, b, x[ 2], this.S43, 0x2ad7d2bb); /* 63 */
	b = this.II ( b, c, d, a, x[ 9], this.S44, 0xeb86d391); /* 64 */

	this.state[0] +=a;
	this.state[1] +=b;
	this.state[2] +=c;
	this.state[3] +=d;

    },

    init: function() {
	this.count[0]=this.count[1] = 0;
	this.state[0] = 0x67452301;
	this.state[1] = 0xefcdab89;
	this.state[2] = 0x98badcfe;
	this.state[3] = 0x10325476;
	for (i = 0; i < this.digestBits.length; i++)
	    this.digestBits[i] = 0;
    },

    update: function(b) { 
	var index,i;
	
	index = this.and(this.shr(this.count[0],3) , 0x3f);
	if (this.count[0]<0xffffffff-7) 
	  this.count[0] += 8;
        else {
	  this.count[1]++;
	  this.count[0]-=0xffffffff+1;
          this.count[0]+=8;
        }
	this.buffer[index] = this.and(b,0xff);
	if (index  >= 63) {
	    this.transform(this.buffer, 0);
	}
    },

    finish: function() {
	var bits = new md5_array(8);
	var	padding; 
	var	i=0, index=0, padLen=0;

	for (i = 0; i < 4; i++) {
	    bits[i] = this.and(this.shr(this.count[0],(i * 8)), 0xff);
	}
        for (i = 0; i < 4; i++) {
	    bits[i+4]=this.and(this.shr(this.count[1],(i * 8)), 0xff);
	}
	index = this.and(this.shr(this.count[0], 3) ,0x3f);
	padLen = (index < 56) ? (56 - index) : (120 - index);
	padding = new md5_array(64); 
	padding[0] = 0x80;
        for (i=0;i<padLen;i++)
	  this.update(padding[i]);
        for (i=0;i<8;i++) 
	  this.update(bits[i]);

	for (i = 0; i < 4; i++) {
	    for (j = 0; j < 4; j++) {
		this.digestBits[i*4+j] = this.and(this.shr(this.state[i], (j * 8)) , 0xff);
	    }
	} 
    },

/* End of the MD5 algorithm */

hexa: function(n) {
 var hexa_h = "0123456789abcdef";
 var hexa_c=""; 
 var hexa_m=n;
 for (hexa_i=0;hexa_i<8;hexa_i++) {
   hexa_c=hexa_h.charAt(Math.abs(hexa_m)%16)+hexa_c;
   hexa_m=Math.floor(hexa_m/16);
 }
 return hexa_c;
},


ascii : "01234567890123456789012345678901" + " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ"+
          "[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~",

MD5: function(entree) 
{
 var l,s,k,ka,kb,kc,kd;

 this.init();
 for (k=0;k<entree.length;k++) {
   l=entree.charAt(k);
   this.update(this.ascii.lastIndexOf(l));
 }
 this.finish();
 ka=kb=kc=kd=0;
 for (i=0;i<4;i++) ka+=this.shl(this.digestBits[15-i], (i*8));
 for (i=4;i<8;i++) kb+=this.shl(this.digestBits[15-i], ((i-4)*8));
 for (i=8;i<12;i++) kc+=this.shl(this.digestBits[15-i], ((i-8)*8));
 for (i=12;i<16;i++) kd+=this.shl(this.digestBits[15-i], ((i-12)*8));
 s=this.hexa(kd)+this.hexa(kc)+this.hexa(kb)+this.hexa(ka);
 return s; 
}
});
dojo.declare ('MUtil', null,
{
    setRightClickAjaxAction: function(formId, controlId, action)
    {
        var handle = dojo.connect(dojo.byId(controlId).parentNode, 'oncontextmenu', function (event) {
            event.preventDefault();
            var args = event.pageX + ':' + event.pageY;
            miolo.doAjax(action, args, formId);
            dojo.disconnect(handle);
        });
    }
});

mutil = new MUtil();dojo.declare ("MMessage", null,
{
    context: null ,
    constructor: function() { },

    scrollUp: function ()
    {
        window.setTimeout(function () { window.scrollTo(0,0); }, 600 );
    },

    show: function ( divId, animate )
    {
        var messageDiv = dojo.byId(divId);
        
        if ( !messageDiv )
        {
            // You should add MMessage::getMessageContainer() on your form
            console.log("There's no div to put the message content");
            return;
        }

        if ( animate )
        {
            dojo.fx.wipeOut({ node: messageDiv, duration: 0 }).play();
            dojo.fx.wipeIn({ node: messageDiv, duration: 500 }).play();
        }
        this.scrollUp();
    },

    hideMessageDiv: function ( divId, animate )
    {
        var messageDiv = dojo.byId(divId);

        if ( messageDiv )
        {
            if ( animate )
            {
                dojo.fx.wipeOut({ node: messageDiv, duration: 500 }).play();
                window.setTimeout(
                    function()
                    {
                        if ( messageDiv.parentNode )
                        {
                            messageDiv.parentNode.removeChild( messageDiv );
                        }
                    }, 600);
            }
            else
            {
                if ( messageDiv.parentNode )
                {
                    messageDiv.parentNode.removeChild( messageDiv );
                }
            }
        }
    },

    // Connects the events to be called to hide de message div
    connectHideEvents: function ( divId, animate )
    {
        var change = dojo.connect(document, 'onchange',
            function()
            {
                mmessage.hideMessageDiv(divId, animate);
                dojo.disconnect(change);
            });
        var keypress = dojo.connect(document, 'onkeypress',
            function()
            {
                mmessage.hideMessageDiv(divId, animate);
                dojo.disconnect(keypress);
            });
    }
}
);

mmessage = new MMessage;