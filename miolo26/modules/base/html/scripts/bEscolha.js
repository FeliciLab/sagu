dojo.declare ("bEscolha", null,
{
    selecionarItem : function (nome, cont)
    {
        if ( cont > 0 )
        {
            // Obtém o elemento HTML onde a tabela com registros será criada.
            div = dojo.byId('divResposta' + nome + "Descricao");
            div.style.display='block';

            // Remove a seleção de todas as opções.
            for ( i=0; i < cont-1 ; i++ )
            {
                itemC = dojo.byId(nome+'Item'+i);
                itemC.className = itemC.className.replace('mTableRawRowSelected','');
            }

            // Seleciona o registro atual.
            //input = dojo.byId(nome+'Input');
            //value = input.value;

            //item = dojo.byId(nome+'Item'+value);

            // Adiciona a classe CSS de seleção no item atual.
            //item.className += 'mTableRawRowSelected';
        }
    },

    deselecionarItem : function (tabela)
    {
        // Obtém corpo da tabela;
        tBody = tabela.childNodes[1];
        for ( i = 0; i < (tBody.childElementCount * 2); i++)
        {
            // Obtém somente as linhas.
            if ( (i % 2) != 0)
            {
                // Obtém a linha.
                linha = tBody.childNodes[i];

                // Obtéma celula.
                celula = linha.childNodes[0];

                // Retira a seleção.
                celula.className = celula.className.replace('mTableRawRowSelected','');
            }
        }
    },

    onDoubleClick : function ( event, nome, chave, modulo, campos )
    {
        miolo.doAjax('onkeyUpEscolha', nome +'|%' + '|' + chave + '|' + modulo + '|' + campos ,'__mainForm');
    },


    /**
     * Mostra as opções de registros. Só é acionado após o segundo caractere e após aguardar 1 segundo sem digitar.
     *
     * @param event Evento javascript.
     * @param element Inner do elemento que irá receber o valor selecionado.
     **/
    onkeyUpEscolha : function ( event, element, nome, chave, modulo, campos)
    {
        div = dojo.byId('divResposta' + nome + "Descricao");

        // Definição das teclas padrão.
        keyEsc = 27;
        keyUp = 38;
        keyDown = 40;
        keyEnter = 13;
        keyTab = 9;
        keyRigth = 39;
        keyLeft  = 37;
        keyPercent = 16;

        // Obtém a tecla pressionada.
        keyCode = ( window.event ) ? event.keyCode : event.which;

        // Obtém o campo que possui qual é o elemento atual.
        //input = dojo.byId(nome+'Input');

        // Obtém o tabela com os registros.
        table = dojo.byId(nome+'Table');
        cont = 0;

        // Caso a tabela exista, calcula a quantidade de registros.
        if ( table )
        {
            tBody = table.childNodes[1];
            cont = tBody.childNodes.length/2;
            cont = parseInt( cont.toFixed() );
        }

        /*
         * Realiza a navegação entre as registros.
         * ESC: fecha a listagem;
         * Para cima: seleciona a opção anterior.
         * Para baixo: seleciona a próxima opção.
         * Enter: o registro é selecionado.
         * Tab: fecha listagem e remove o foco do elemento.
         * %: Mostra todas as opções.
         */
        if ( keyCode == keyEsc )
        {
            div.style.display='none';

            return false;
        }
        else if ( keyCode == keyUp )
        {
            /*if ( parseInt(input.value) > 0 )
            {
                input.value --;
            }*/

            bEscolha.selecionarItem(nome, cont);

            return false;
        }
        else if ( keyCode == keyDown )
        {
            /*if ( input.value == '')
            {
                input.value = '0';
            }
            else
            {
                if ( parseInt(input.value) < cont-2 )
                {
                    input.value ++;
                }
            }*/

            bEscolha.selecionarItem(nome,cont);

            return false;
        }
        else if ( keyCode == keyEnter )
        {
            return false;
        }
        else if ( keyCode == keyTab )
        {
            div.style.display = 'none';

            return true;
        }
        else if ( keyCode == keyRigth || keyCode == keyLeft )
        {
            return true;
        }
        else
        {
            if ( keyCode == keyPercent || (element.value.length > 1 && !dojo.byId('ajaxLoading')) )
            {
                //clearTimeout(t);
                setTimeout( "bEscolha.dictionaryAjax('"+element.id+"', '"+nome+"', '"+chave+"', '"+modulo+"', '"+campos+"');",500);
            }
            else
            {
                div.style.display='none';
                return true;
            }
        }

        return true;
    },

    /*ajax só é permitido em alguns casos*/
    dictionaryAjax : function ( elementId, nome, chave, modulo, campos)
    {
        element = dojo.byId(elementId);

        if ( element.value == '%' || element.value.length > 1 )
        {
             miolo.doAjax('onkeyUpEscolha', nome + '|' + element.value + '|' + chave + '|' + modulo + '|' + campos, '__mainForm');
        }
    },

    /* função chamada na resposta do onkeyup*/
    onkeyUpResponse : function (nome)
    {
        div = dojo.byId('divResposta' + nome + "Descricao");
        myTable = dojo.byId(nome+'Table');
        myElement = dojo.byId(nome + "Descricao");

        div.style.display='block';

        // Limpa a seleção atual. 
        //dojo.byId(nome+'Input').value = '';
        myElement.focus();
    },

    /*Funcao chamada quando o foco é tirado do campo GDictionaryField*/
    onblurEscolha : function (nome)
    {
        div = dojo.byId('divResposta' + nome + "Descricao");

        if ( div ) 
        { 
            setTimeout('div.style.display =\'none\';',100); 
        }
    }
});

bEscolha = new bEscolha;