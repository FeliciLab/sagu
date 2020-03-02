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
