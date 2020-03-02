var t; //necessário para poder reinicar o temporizador

/* Funciona ao selecionar um item*/
function selectedItem(name,cont)
{
    //caso tenha tabela
    if ( cont > 0 )
    {
        //mostra div
        div = dojo.byId(name+'Div');
        div.style.display='block';

        //limpa todos
        for ( i=0; i < cont-1 ; i++ )
        {
            itemC = dojo.byId(name+'Item'+i);
            itemC.className = itemC.className.replace('mTableRawRowSelected',''); //tira classe
        }

        //seleciona o atual
        input = dojo.byId(name+'Input');
        value = input.value;
        
        item = dojo.byId(name+'Item'+value);
        
        if ( item )
        {
            item.className += 'mTableRawRowSelected'; //coloca classe
        }
        
        firstItem = dojo.byId(name+'Item0');
        
        // Cálculo de posicionamento do scroll, onde 2 é a margem.
        div.scrollTop = value *  ( firstItem.clientHeight + 2 ) ;
        table = dojo.byId(name+'Table'); //obtem table
    }
}

function onDoubleClick( event,name )
{
    table = dojo.byId(name+'Table'); //obtem table

    if ( table )
    {
        tBody = table.childNodes[1]; //pega body da tabela, só funciona no firefox
        cont = tBody.childNodes.length/2; //quantidade de tr
        cont = parseInt( cont.toFixed() ); //tira as casas decimais

        selectedItem(name,cont);
    }
}

/*só executa caso tenha mais de 2 caracteres e não tive ajax, e fique 1 segundo sem digitar nada*/
function onkeyUpDictionary( event, element , name, related, item, filter, timeSearch)
{
    //definicação das teclas padrão
    keyEsc = 27;
    keyUp = 38;
    keyDown = 40;
    keyEnter = 13;
    keyTab = 9;
    keyRigth = 39;
    keyLeft  = 37;

    keyCode = ( window.event ) ? event.keyCode : event.which; //obtem tecla pressionada
    input = dojo.byId(name+'Input'); //obtem input
    table = dojo.byId(name+'Table'); //obtem table
    cont = 0;

    if ( table )
    {
        tBody = table.childNodes[1]; //pega body da tabela, só funciona no firefox
        cont = tBody.childNodes.length/2; //quantidade de tr
        cont = parseInt( cont.toFixed() ); //tira as casas decimais
    }

    //esc esconde listagem
    if ( keyCode == keyEsc )
    {
        dojo.byId(name+'Div').style.display='none';
        return false;
    }
    else if ( keyCode == keyUp )
    {
        if ( parseInt(input.value) > 0 )
        {
            input.value --;
        }

        selectedItem(name,cont);

        return false;
    }
    else if ( keyCode == keyDown )
    {
        if ( input.value == '')
        {
            input.value = '0';
        }
        else
        {
            if ( parseInt(input.value) < cont-2 )
            {
                input.value ++;
            }
        }

        selectedItem(name,cont);

        return false;
    }
    else if ( keyCode == keyEnter )
    {
        myOnPressEnter = element.getAttribute('myOnPressEnter' );

        //seleciona o atual
        value = input.value;

        if ( value != '' && cont > 0 && div.style.display != 'none')
        {
            item = dojo.byId(name+'Item'+value);
            item.onclick(); //seleciona ativando o onclick do objeto
        }
        else
        {
            //executa o onpressEnter do campo caso a tabela esteja escondida
            eval(myOnPressEnter);
        }
    }
    else if ( keyCode == keyTab )
    {
        div.style.display = 'none';

        return true; //para poder ir pro próximo campo
    }
    else if ( keyCode == keyRigth || keyCode == keyLeft )
    {

        return true; //para poder ir pro próximo campo

    }
    else
    {
        if ( element.value.length > 1 && !dojo.byId('ajaxLoading') )
        {
            clearTimeout(t);
            t = setTimeout( "dictionaryAjax('"+element.id+"', '"+name+"', '"+related+"', '"+item+"', '"+filter+"');", timeSearch);
        }
        else
        {
            div.style.display='none';
            return true;
        }
    }

    return true;
}

/*ajax só é permitido em alguns casos*/
function dictionaryAjax( elementId, name, related, item, filter )
{
    element = dojo.byId(elementId);

    if ( element.value.length > 1 )
    {
        miolo.doAjax('onkeyUpDictionary', 'name|~|'+name+'|#|related|~|'+related+'|#|item|~|'+item+'|#|filter|~|'+filter , frm);
    }
}

/* função chamada na resposta do onkeyup*/
function onkeyUpResponse(name)
{
   isRepetivive = dojo.byId(name).parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.nodeName == 'FIELDSET' ? true : false;

   myDiv = dojo.byId(name+'Div');
   myTable = dojo.byId(name+'Table');
   myElement = dojo.byId(name);
   myDiv.style.display='block';
   myDiv.style.left = myElement.offsetLeft-10+'px';
   myDiv.style.top = myElement.offsetTop+7+'px';
   myTable.style.minWidth = myElement.clientWidth-2+'px'
   dojo.byId(name+'Input').value = ''; //limpa a seleção atual
   myDiv.scrollTop = 0;
   offsetTopAdjust = -3; //Workaround para lidar com offset top no Firefox inferior a versão 14
   
   if ( isRepetivive && myElement)
   {
       onpressenter = myElement.getAttribute( 'onPressEnter' );
       onpressenterChanged = myElement.getAttribute( 'onPressEnterChanged' );

       //troca o evento onpressenter para myOnPressEnter para ser gerenciado pelo componente
       if ( !onpressenterChanged )
       {
            myElement.setAttribute('onPressEnter', "" );
            myElement.setAttribute('myOnPressEnter', onpressenter );
            myElement.setAttribute('onPressEnterChanged' ,true);
       }
   }
   
    //Se for firefox 14 ou maior
    if (dojo.isFF >= 14 )
    {
        offsetTopAdjust = 18; //Ajusta pixels
    }
    //Se nao for repetitivo e firefox for menor que 14, usa outro ajuste
    else if ( !isRepetivive )
    {
        offsetTopAdjust = 6; //Ajusta pixels
    }

    myDiv.style.top = myElement.offsetTop+offsetTopAdjust+'px';   
}

/*Funcao chamada quando o foco é tirado do campo GDictionaryField*/
function onblurDictionary(name)
{
    div = dojo.byId(name+'Div'); 
    
    if ( div ) 
    { 
        //Tem que ter timeout para que a div da tabela não seja escondida na hora
        //em que a requisição do ajax do GDictionaryField termine. Caso a tabela
        //seja escondida antes de terminar a requisição, não é possível clicar nela
        //para selecionar o dado desejado.
        setTimeout('div.style.display =\'none\';',100); 
    }
}
