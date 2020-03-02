dojo.require('dojo.fx');

dojo.declare("MExpandDiv", null,
{
    context: null,
    constructor: function() { },

    expand: function( id, collapsedHeight )
    {
        var box = dojo.byId(id);
        var boxButton = dojo.byId(id + "Button");

        if (box.offsetHeight < box.scrollHeight)
        {
            boxButton.className = "mExpandDivButton mExpandDivButtonExpanded"
            dojo.fx.wipeIn( { node: box, duration: 500 }).play();
        }
        else
        {
            boxButton.className = "mExpandDivButton mExpandDivButtonCollapsed";
            dojo.animateProperty( { node: box, duration: 500, properties: { height: { end: collapsedHeight } } }).play();
        }
    }
}
);

mexpanddiv = new MExpandDiv;
