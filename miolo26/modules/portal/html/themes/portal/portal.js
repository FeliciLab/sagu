function displayMenuPanel()
{
    if(document.getElementById('divMiniPainel'))
    {   
        $('#divMiniPainel').slideToggle('fast');
    }
};

/**
 * Pega uma dada propriedade de um dado elemento
 * 
 * @param {Element} elemento Elemento do qual se quer o estilo
 * @param {String} propriedade Propriedade a ser informada
 * @returns {String} Valor da propriedade requisitada
 */
var getEstilo = function(elemento, propriedade)
{
    var camelize = function(texto)
    {
        return texto.replace(/\-(\w)/g, function(texto, letter)
        {
            return letter.toUpperCase();

        });

    };

    if (elemento.currentStyle)
    {
        return elemento.currentStyle[camelize(propriedade)];

    }
    else if(document.defaultView && document.defaultView.getComputedStyle)
    {
        return document.defaultView.getComputedStyle(elemento,null)
                                   .getPropertyValue(propriedade);
    }
    else
    {
        return elemento.style[camelize(propriedade)];

    }

};

/**
 * Ajusta a fonte de um dado elemento
 * 
 * @param {String} id Identificiador do elemento que deve ter sua fonte ajustada
 * @returns {Boolean} False se ocorreu algum erro, True caso contrário
 */
var ajustaFonteElemento = function(id)
{
    try
    {
        var elemento = document.getElementById(id);

        var fonte = parseFloat(window.getEstilo(elemento, 'font-size').replace("px", ""));

        fonte = parseFloat(fonte.toFixed(1));

        var destinoWidth = elemento.offsetWidth;
        var atualWidth = elemento.scrollWidth;

        var novaFonte = Math.floor((destinoWidth * fonte) / atualWidth);

        elemento.style.fontSize = novaFonte + "px";

        return true;
        
    }
    catch(e)
    {
        return false;
        
    }
        
};