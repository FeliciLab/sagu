dojo.provide("MTimeTextBox");

dojo.require("dijit.form.TimeTextBox");

dojo.declare("MTimeTextBox", [dijit.form.TimeTextBox], {
    maxLength: 8,
    _onKey: function (e) {
        //invoke parent onkeypress
        this.inherited(arguments);
    
        var value = this.attr('displayedValue');
        if ((value.length==2 || value.length==5 ) && e.charOrCode != dojo.keys.BACKSPACE)
        {
            this.attr('displayedValue', this.attr('displayedValue') + ':');
        }
    },
    // overrides the onChange function to call the event of the MTimeField component
    onChange: function () {
        // changes the value in the valueNode to get the new one in the POST
        this.valueNode.value = this.attr('displayedValue');

        // invokes parent onChange
        this.inherited(arguments);

        // tests if the whole time has been informed and then fires the change event
        if ( this.valueNode.value.length == this.maxLength )
        {
            if ( dojo.doc.createEvent )
            {
                var evObj = dojo.doc.createEvent("HTMLEvents");
                evObj.initEvent( 'change' , true, true);
                dojo.byId( this.id ).dispatchEvent(evObj);
            }
            else if (dojo.doc.createEventObject) // IE
            {
                dojo.byId( this.id ).fireEvent( 'onchange' );
            }
        }
    },
    onBlur: function () {
        // invokes parent onBlur
        this.inherited(arguments);
        // changes the value node to get the time without the T at the beginning
        this.valueNode.value = this.attr('displayedValue');
    }
});
