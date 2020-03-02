MIOLO_FloatField = {

    validate: function(input, fixed, separator)
    {
        var value = input.value;

        if ( separator == ',' )
        {
            replace = '.';
        }
        else
        {
            replace = ',';
        }

        var re = new RegExp("^[+-]?((\\d+|\\d{1,3}(\\" + replace + "\\d{3})+)(\\" + separator + "\\d*)?|\\" + separator + "\\d+)$");
        var reFixed = new RegExp("^[+-]?((\\d+|\\d{1,3}(\\" + replace + "\\d{3})+)(\\" + separator + "\\d{" + fixed + "})?|\\" + separator + "\\d{" + fixed + "})$");

        if ( !value.match(re) )
        {
            value = value.replace(replace, separator);
            value = value.replace(new RegExp("[^-\\" + separator + "\\d]|", 'g'), '');

            while ( value.indexOf(separator) != value.lastIndexOf(separator) )
            {
                value = value.substr(0, value.lastIndexOf(separator)) + value.substr(value.lastIndexOf(separator)+1);
            }

            value = value.replace(new RegExp("^\\" + separator + "+"), '0' + separator);
            value = value.replace(new RegExp("[^\\" + separator + "\\d]+$"), '');
        }
        else if ( value.match(new RegExp("^\\" + separator + "")) )
        {
            value = value.replace(new RegExp("^\\" + separator + "+"), '0' + separator);
        }

        if ( !value.match(reFixed) )
        {
            // Adjust number of digits after separator
            var sides = value.split(separator)
            var left = sides[0];
            var right = sides[1];

            if ( right )
            {
                right = right.substr(0, fixed);
                value = left + separator + right;
            }
        }

        // Avoid unnecessary updates
        if ( value != input.value )
        {
            input.value = value;
        }
    },

    update: function(input, targetId, fixed, separator)
    {
        this.validate(input, fixed, separator);

        if ( input.value != '' )
        {
            var value = input.value;

            if ( separator == ',' )
            {
                value = value.replace(',', '.');
            }

            var number = new Number(value);
            document.getElementById(targetId).value = number.toFixed(fixed);
        }
        else
        {
            document.getElementById(targetId).value = '';
        }
    }
};
