dojo.declare ("MMessage", null,
{
    context: null ,
    constructor: function() { },

    scrollUp: function ()
    {
        window.setTimeout(function () { window.scrollTo(0,0); }, 600 );
    },

    show: function ( divId, animate )
    {
        var messageDiv = dojo.byId(divId);
        
        if ( !messageDiv )
        {
            // You should add MMessage::getMessageContainer() on your form
            console.log("There's no div to put the message content");
            return;
        }

        if ( animate )
        {
            dojo.fx.wipeOut({ node: messageDiv, duration: 0 }).play();
            dojo.fx.wipeIn({ node: messageDiv, duration: 500 }).play();
        }
        this.scrollUp();
    },

    hideMessageDiv: function ( divId, animate )
    {
        var messageDiv = dojo.byId(divId);

        if ( messageDiv )
        {
            if ( animate )
            {
                dojo.fx.wipeOut({ node: messageDiv, duration: 500 }).play();
                window.setTimeout(
                    function()
                    {
                        if ( messageDiv.parentNode )
                        {
                            messageDiv.parentNode.removeChild( messageDiv );
                        }
                    }, 600);
            }
            else
            {
                if ( messageDiv.parentNode )
                {
                    messageDiv.parentNode.removeChild( messageDiv );
                }
            }
        }
    },

    // Connects the events to be called to hide de message div
    connectHideEvents: function ( divId, animate )
    {
        var change = dojo.connect(document, 'onchange',
            function()
            {
                mmessage.hideMessageDiv(divId, animate);
                dojo.disconnect(change);
            });
        var keypress = dojo.connect(document, 'onkeypress',
            function()
            {
                mmessage.hideMessageDiv(divId, animate);
                dojo.disconnect(keypress);
            });
    }
}
);

mmessage = new MMessage;