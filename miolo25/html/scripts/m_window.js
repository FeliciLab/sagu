dojo.declare("Miolo.Window",null,
{
    id: '',
    title: '',
    href: '',
    parent: null,
    form: '',
    dialog: null,
    scripts: null,
    constructor: function(id) {
        this.obj = this;
        this.id = id;
	},
    setTitle: function(title) {
        this.title = title;
    },
    setHref: function(href) {
        this.href = href;
    },
    open: function() {
        this.dialog = new miolo.Dialog();
        this.scripts = new dojox.layout.ContentPane({id:this.dialog.domNode.id+'__scripts',executeScripts:true, cleanContent:true});
        this.obj.form = miolo.webForm.id;
        miolo.addForm(this.dialog.domNode.id);
        miolo.setForm(this.dialog.domNode.id);
        miolo.pushWindow(this.obj);
        dojo.body().appendChild(this.dialog.domNode);
        dojo.body().appendChild(this.scripts.domNode);
        miolo.page.getWindow(this.obj.id,this.dialog.domNode.id,this.obj.href);
    },
    close: function() {
        this.dialog.hide();
        miolo.getForm(this.dialog.domNode.id).disconnect();
        var outer = miolo.getElementById(this.dialog.domNode.id + '_underlay_wrapper');
        dojo.body().removeChild(this.scripts.domNode);
        dojo.body().removeChild(this.dialog.domNode);
        if ( outer )
        {
            dojo.body().removeChild(outer);
        }
        miolo.popWindow();
        miolo.setForm(this.obj.form);
    }
});