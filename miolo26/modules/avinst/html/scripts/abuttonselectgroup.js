dojo.declare ("AButtonSelectGroup", null,
{
	select: function (id, option, optionValue, colors)
	{
		colors = colors.split(',');
		// Tira a seleção de todos os botões
		dojo.query('> div', dojo.byId('bsgDiv_'+id)).forEach(function(node, index, arr){
															   dojo.byId(node).className = 'aButtonSelectGroup';
															   dojo.byId(node).style.backgroundColor = colors[index];
		  								       				 });
		// Adiciona a seleção no botão que o usuário clicou
		option.className = 'aButtonSelectGroupSelected';
		option.style.backgroundColor = '#FF8C00';
		// Muda o valor do hidden field
		dojo.byId(id).value = optionValue;
		// Remove o erro se tiver
		AvinstValidator.removeErrorFromField(id);
	},
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
	setColorButton: function(id,color)
	{
		dojo.byId(id).style.backgroundColor = color;
	}
}
);	    

abuttonselectgroup = new AButtonSelectGroup;