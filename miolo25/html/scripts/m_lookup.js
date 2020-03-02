dojo.declare("Miolo.Lookup",null,
{
    context: null,
    url: '',
    constructor: function() {
    },
    setContext: function(context) {
        this.context = context;
    },
    start: function(autocomplete) {
        var field = miolo.getElementById(this.context.field);
        this.url = 'index.php?module=' + this.context.baseModule +
            '&action=' + 'lookup' +
            '&name=' + miolo.urlencode(this.context.name) +
            '&lmodule=' + miolo.urlencode(this.context.module) +
            '&item=' + miolo.urlencode(this.context.item) +
            '&event=' + miolo.urlencode(this.context.event)+
            '&related=' + miolo.urlencode(this.context.related) +
            '&title=' + miolo.urlencode(this.context.title) +
            '&wtype=' + miolo.urlencode(this.context.wtype);
        if ( field != null ) {
            this.url = this.url + '&fvalue=' + miolo.urlencode(this.context.value);
        }
        if ( this.context.filter != null ) {
            var aFilter = this.context.filter.split(',');
            var idxFilter = this.context.idxFilter.split(',');
            useIdx        = idxFilter[0].charAt(0) != 0;

            if (aFilter.length == 1 && !useIdx)
            {

                if ( ! autocomplete ) //otherwise var field is already defined
                {
                    field = miolo.getElementById(aFilter[0]);
                }
                this.url = this.url + '&filter' + '=' + miolo.urlencode(field.value);
            } 
            else
            {
                for( var i=0; i < aFilter.length; i++ )
                {
                    field = miolo.getElementById(aFilter[i]);
                    var idx   = useIdx ? idxFilter[i] : 'filter' + i;
                    this.url  += '&' + idx + '=' + miolo.urlencode(field.value);
                }

                this.url += '&idxFilter=' + this.context.idxFilter;
                
                if ( autocomplete )
                {
                    field = miolo.getElementById( this.context.field );
                    this.url += '&filter' + '=' + miolo.urlencode(field.value);
                }
            }
        }
        if (autocomplete)
        {
            this.url += '&autocomplete=1';
            this.autocomplete();
        }
        else
        {
            this.url += '&autocomplete=0';
            this.open();
        }
    },
    open: function() {
        miolo.addWindow(this.context.name);
//        miolo.getWindow(this.context.name).setTitle(this.context.title);
        miolo.getWindow(this.context.name).setHref(this.url);
        miolo.getWindow(this.context.name).open();
    },
    autocomplete: function() {
        var ajaxAutoComplete = new Miolo.Ajax({
            url: this.url,
            response_type: 'TEXT',
            parameters: {name: this.context.name, __ISAJAXCALL: 'yes'},
            callback_function: function(result, ioArgs) {
                var args = result;
                var name = ioArgs.args.content.name;
                var lookup = eval(name);
                lookup.deliver(name,0,args);
            }
        });
        ajaxAutoComplete.call();
    },
    deliver: function(name, key, args) {
        var lookup  = eval(name);
        var context = lookup.context;

        // "args" will be blank when no register is found (possible only via autocomplete)
        if ( args )
        {
            args = args.split('|');
        }

        var event = context.event;

        // Always fill with the result
        related = context.related;
        var aRelated = related.split(',');
        var count = aRelated.length;

        for( var i=0; i<count; i++ )
        {
            var value = '';

            if ( args )
            {
                value = miolo.urldecode(args[i]);
            }

            var field = context.form['frm_'+aRelated[i]];

            if ( field == null )
            {
                field = miolo.getElementById(aRelated[i]);
            }

            if ( field != null )
            {
                field.value = value ? value : '';
                field = miolo.getElementById(field.name+'_sel');

                if ( field != null )
                {
                    field.value = value;
                }
            }
        }

        // Call ajax event if it was informed
        if ( event != '' && event != 'filler' )
        {
            var ajaxArguments = null;

            if ( args )
            {
                ajaxArguments = args[key];
            }

            miolo.doAjax(event, ajaxArguments, this.context.form);
        }

        if ( miolo.getWindow(this.context.name) )
        {
            miolo.getWindow(this.context.name).close();
        }
    }
});
