window.CKEDITOR_BASEPATH='scripts/ckeditor/';

dojo.declare ("MEditor", null,
{
    connection: {},

    connect: function (id)
    {
        this.connection[id] = dojo.connect(miolo.webForm, 'onSubmit', function () {
            CKEDITOR.instances[id].updateElement();
            return true;
        });
    },

    disconnect: function (id)
    {
        if ( this.connection[id] )
        {
            dojo.disconnect(this.connection[id]);
        }
    },

    remove: function (id)
    {
        this.disconnect(id);

        if ( CKEDITOR.instances[id] )
        {
            CKEDITOR.instances[id].destroy();
        }

        // Warning! This will remove everything inside the parent node.
        if ( dojo.byId(id) )
        {
            dojo.byId(id).parentNode.innerHTML='';
        }
    },

    // This will be populated when using PHP's MEditor addCustomButton method.
    custombutton: []
});

meditor = new MEditor();