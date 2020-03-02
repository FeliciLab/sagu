// Independent validation class. Used by AJAX validation
dojo.declare ("AvinstValidator", null,
{
    firstFieldFocused: false,

    addErrorToField: function (error, fieldId)
    {
        var input = dojo.byId(fieldId);
        var parent = input.parentNode;
        var son = input;
        
        // Cria elemento de identificação
        if (parent.tagName != 'TD')
        {
            // Search for the best place to put the error message: inside the field span
            while ( parent.tagName != 'TD' )
            {
                if ( !parent.parentNode || parent.parentNode.nodeName == 'BODY' )
                {
                    parent = null;
                    break;
                }
                son = parent;
                parent = parent.parentNode;
            }
        }
        // If the field span was not found, put the message on the input parent node
        if ( !parent )
        {
            parent = input.parentNode;
        }
        parent.style.backgroundColor = '#ffff3c';
    },
    removeErrorFromField: function (fieldId)
    {
        var input = dojo.byId(fieldId);
        var parent = input.parentNode;
        var son = input;
        
        if (parent.tagName != 'TD')
        {
            // Search for the best place to put the error message: inside the field span
            while ( parent.tagName != 'TD' )
            {
                if ( !parent.parentNode || parent.parentNode.nodeName == 'BODY' )
                {
                    parent = null;
                    break;
                }
                son = parent;
                parent = parent.parentNode;
            }
        }
        // If the field span was not found, put the message on the input parent node
        if ( !parent )
        {
            parent = input.parentNode;
        }
        parent.style.backgroundColor = 'transparent';
    },
    addErrorToDescriptiveField: function (error, fieldId)
    {
        var input = dojo.byId(fieldId);
        input.style.backgroundColor = '#ffff3c';
    },
    removeErrorFromDescriptiveField: function (fieldId)
    {
        var input = dojo.byId(fieldId);
        input.style.backgroundColor = '#FFFFFF';
    }
});

var AvinstValidator = new AvinstValidator();