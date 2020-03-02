 /**
 *
 *
 * @author Eduardo Beal Miglioransa [eduardo@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Beal Miglioransa
 *
 * @since
 * Class created on 20/01/2006
 *
 * \b @organization \n
 * SOLIS - Cooperativa de Soluções Livres \n
 * The Sagu2 development team
 *
 * \b Copyleft \n
 * Copyleft (L) 2005 - SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html )
 *
 * \b History \n
 * This function select and retun a value correctly from document javascript form 
 */

function DocSelectedRadioButton( name , nameFields , options )
{
    var arrayNameFields = nameFields.split(','); 
    var arrayOptions    = options.split(','); 
    var fields = document.getElementsByName( name );
    for( var i=0; i<fields.length; i++)
    {
        if( fields[i].checked )
        {
            value = fields[i].value;
        }
    }
    for( var i=0; i<=arrayNameFields.length; i++)
    {
        document.getElementById( 'm_' + arrayNameFields[i] ).style.display = value == arrayOptions[i] ? '' : 'none';
    }
    
} 
