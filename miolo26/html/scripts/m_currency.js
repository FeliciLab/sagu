
dojo.declare("Miolo.Currency",null,
{
    constructor: function() {
	},
    format: function(field) {
    	var value = field.value;
	    var v = '';
        for ( var i=0; i < value.length; i++ )
        {
            var c = value.charAt(i);
            if ( (c >= '0') && (c <= '9') )
            {
			    v += c; 
            }
        }
	    var l = v.length;
	    if (l == 0)
	    {
		    return true;
	    }
	    if (l < 3)
	    {
                alert('Dígitos insuficientes para valor monetário!');
    	    field.focus();
		    return true;
	    }
	    v = v.slice(0,l-2) + ',' + v.slice(l-2,l);
	    v = this.add(v); 
	    field.value = v;
    },
    remove: function( strValue ) {
        var objRegExp = /\(/;
        var strMinus = '';

        //check if negative
        if(objRegExp.test(strValue)){
           strMinus = '-';
        }
        objRegExp = /\)|\(|[\.]/g;
        strValue = strValue.replace(objRegExp,'');
        if(strValue.indexOf('$') >= 0){
           strValue = strValue.substring(1, strValue.length);
        }
        return strMinus + strValue;
    },
    add: function ( strValue ) {
        var objRegExp = /-?[0-9]+\,[0-9]{2}$/;

        if( objRegExp.test(strValue)) {
           objRegExp.compile('^-');
           strValue = this.addDecimalPoints(strValue);
           if (objRegExp.test(strValue)){
              strValue = '(' + strValue.replace(objRegExp,'') + ')';
           }
        }
        return strValue;
    },
    addDecimalPoints: function ( strValue ) {
        var objRegExp  = new RegExp('(-?[0-9]+)([0-9]{3})');

        //check for match to search criteria
        while(objRegExp.test(strValue)) {
            //replace original string with first group match,
            //a comma, then second group match
            strValue = strValue.replace(objRegExp, '$1\.$2');
        }

        return strValue;
    }
});