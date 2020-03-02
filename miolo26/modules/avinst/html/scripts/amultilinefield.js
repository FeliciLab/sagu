dojo.declare ("AMultiLineField", null,
{
	select: function (id, option, optionValue)
	{
		// Tira a seleção de todos os botões
		dojo.query('> div', dojo.byId('bsgDiv_'+id)).forEach(function(node, index, arr){
		      												   dojo.byId(node).className = 'aButtonSelectGroup';
		  								       				 });
		// Adiciona a seleção no botão que o usuário clicou
		option.className = 'aButtonSelectGroupSelected';
		// Muda o valor do hidden field
		dojo.byId(id).value = optionValue;		
	},
	showTooltip: function(id,node,position)
	{
		new dijit.Tooltip({
            // Two parentNode to achieve the line element (tr)
            connectId: [dojo.byId(id)],
		    label: message,
		    position: ['above','below'],  // before,after,above,below
		    showDelay: 50
			});
	},
	hideTooltip: function(id,node,position)
	{
		new dijit.Tooltip({
            // Two parentNode to achieve the line element (tr)
            connectId: [dojo.byId(id)],
		    label: message,
		    position: ['above','below'],  // before,after,above,below
		    showDelay: 50
			});
	}
}
);	    

amultilinefield = new AMultiLineField;