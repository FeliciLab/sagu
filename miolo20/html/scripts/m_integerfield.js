window.mintegerfield = {
    validate: function(input)
    {
        // TODO: negative numbers
        if ( input.value.match( /[^\d]/g ) )
        {
            input.value = input.value.replace( /[^\d]/g, '' );
        }
    }
};
