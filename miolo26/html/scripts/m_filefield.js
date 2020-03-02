dojo.declare ("MFileField", null,
{
    lastNumber: {},

    addFile: function(name) {

        if ( typeof(this.lastNumber.name) != 'undefined' )
        {
            this.lastNumber.name++;
        }
        else
        {
            this.lastNumber.name = 1;
        }

        var br = document.createElement('br');

        var input = dojo.byId(name + '[]');
        var newInput = dojo.clone(input);
        newInput.id = name + '[' + this.lastNumber.name + ']';

        var remove = dojo.clone(dojo.byId(name + '_removeButton'));
        remove.id = newInput.id + '_removeButton';
        remove.onclick = function () { mfilefield.removeFile(newInput.id); };
        remove.style.display = null;

        var container = document.createElement('span');
        container.id = newInput.id + '_container';
        container.appendChild(br);
        container.appendChild(newInput);
        container.innerHTML += ' ';
        container.appendChild(remove);

        input.parentNode.appendChild(container);

        if ( typeof jQuery != 'undefined' )
        {
            remove.setAttribute('data-enhance', 'true');
            $(remove.parentNode).trigger('create');
        }
    },

    removeFile: function(id) {
        dojo.destroy(id + '_container');
    }
});

mfilefield = new MFileField;
