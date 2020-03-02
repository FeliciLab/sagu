dojo.declare ("MFloatField", null,
{
    validate: function(input, comma)
    {
        var re = /^([+-]?(((\d+(\.)?)|(\d*\.\d+))([eE][+-]?\d+)?))$/g;
        
        if ( !input.value.match(re) )
        {
            input.value = input.value.replace(',', '.');
            input.value = input.value.replace(/[^.\d]|\..*\./g, '');
        }
    },

    fixPrecision: function(element, precision)
    {
        var value = element.value;
        if ( value )
        {
            var number = new Number(value);
            value = number.toFixed(precision);

            if ( isNaN(value) )
            {
                value = '';
            }

            element.value = value;
        }
    }
});

miolo.floatfield = new MFloatField;
