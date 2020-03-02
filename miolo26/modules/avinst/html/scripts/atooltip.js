dojo.declare ("ATooltip", null,
{
	setTooltip: function(id,message)
	{
		new dijit.Tooltip({
            // Two parentNode to achieve the line element (tr)
            connectId: [dojo.byId(id)],
		    label: message,
		    position: ['above','below'],  // before,after,above,below
		    showDelay: 50
			});
	},
}
);	    

atooltip = new ATooltip;
