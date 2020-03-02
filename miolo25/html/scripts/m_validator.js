
// Independent validation class. Used by AJAX validation
dojo.declare ("MValidator", null,
{
    firstFieldFocused: false,

    addErrorToField: function (error, fieldId)
    {
        var input = dojo.byId(fieldId);

        if ( !this.firstFieldFocused )
        {
            input.focus();
            this.firstFieldFocused = true;
        }

        var errorNode = document.createElement('span');
        errorNode.className = 'mValidatorError';
        errorNode.innerHTML = error;
        errorNode.id = fieldId + 'Error';

        var parent = input.parentNode;
        var son = input;

        // Search for the best place to put the error message: inside the field span
        while ( parent.className != 'field' )
        {
            if ( !parent.parentNode || parent.parentNode.nodeName == 'BODY' )
            {
                parent = null;
                break;
            }

            son = parent;
            parent = parent.parentNode;
        }

        // If the field span was not found, put the message on the input parent node
        if ( !parent )
        {
            parent = input.parentNode;
        }

        if ( son.tagName == 'DIV' )
        {
            son.style.cssText = 'float: left;';
            errorNode.style.cssText = 'float: left; margin: 2px 0;';
        }

        parent.appendChild(errorNode);
    },
    removeAllErrors: function ()
    {
        this.firstFieldFocused = false;
        dojo.query('.mValidatorError').forEach(function(errorLabel) {
            errorLabel.parentNode.removeChild(errorLabel);
        });
    }
});


var mvalidator = new MValidator();

