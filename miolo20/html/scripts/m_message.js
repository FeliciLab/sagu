var mmessage =
{
    context: null ,

    scrollUp: function ()
    {
        window.setTimeout(function () { window.scrollTo(0,0); }, 600 );
    },

    show: function ( divId )
    {
        var messageDiv = document.getElementById(divId);
        
        if ( !messageDiv )
        {
            // You should add MMessage::getMessageContainer() on your form
            console.log("There's no div to put the message content");
            return;
        }

        this.scrollUp();
    },

    hideMessageDiv: function ( divId )
    {
        var messageDiv = document.getElementById(divId);

        if ( messageDiv && messageDiv.parentNode )
        {
            messageDiv.parentNode.removeChild( messageDiv );
        }
    },

    // Connects the events to be called to hide de message div
    connectHideEvents: function ( divId, animate )
    {
        document.onchange = function()
            {
                mmessage.hideMessageDiv(divId, animate);
            };
        document.onkeypress = function()
            {
                mmessage.hideMessageDiv(divId, animate);
            };
    }
}
