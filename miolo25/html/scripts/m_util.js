dojo.declare ('MUtil', null,
{
    setRightClickAjaxAction: function(formId, controlId, action)
    {
        var handle = dojo.connect(dojo.byId(controlId).parentNode, 'oncontextmenu', function (event) {
            event.preventDefault();
            var args = event.pageX + ':' + event.pageY;
            miolo.doAjax(action, args, formId);
            dojo.disconnect(handle);
        });
    }
});

var mutil = new MUtil();