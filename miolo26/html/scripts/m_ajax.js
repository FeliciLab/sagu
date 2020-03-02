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
