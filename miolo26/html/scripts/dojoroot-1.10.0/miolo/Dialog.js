dojo.provide("miolo.Dialog");

dojo.require("dijit.Dialog");

dojo.declare(
	"miolo.Dialog",
	[dijit.Dialog],
	{
        templateString:null,
        templateString:"<div class=\"dijitDialog\" tabindex=\"-1\" waiRole=\"dialog\" waiState=\"labelledby-${id}_title\"><div dojoAttachPoint=\"containerNode\" class=\"dijitDialogPaneContent\"></div></div>",
        _onKey: function () { /*disable _onKey function from dijit.Dialog. Fix window inside window problem */ }
	}
);
