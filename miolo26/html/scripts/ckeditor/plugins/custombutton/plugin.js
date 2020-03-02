CKEDITOR.plugins.add('custombutton',
{
    init: function(editor)
    {
        var pluginName = 'custombutton';

        for ( var i=0; i < meditor.custombutton.length; i++ )
        {
            if ( !meditor.custombutton[i] )
            {
                continue;
            }

            editor.addCommand(pluginName + i, { exec: meditor.custombutton[i].command });

            editor.ui.addButton(pluginName + i, {
                label: meditor.custombutton[i].label,
                icon: meditor.custombutton[i].icon,
                command: pluginName + i
            });
        }
    }
});