
var toolBarChangerState; //estado do changer da toolbar

/**
 * Gerencia todos os javascripts necessários para o funcionamento do modulo gnuteca
 */
dojo.declare ("Gnuteca", null,
{
	context: null ,
    mBoxOuterWidth : null,      //guarda tamanho anterior da tela, para restauração
    imgChangerLeft : null,      //url da imagem do Changer - esquerda
    imgChangerRight: null,      //url da imagem do Changer - direita
    lastActiveElement : '',     //último elemento ativo antes de abrir uma caixa de diálogo
    autoCompleteMarcFieldsCopy : Array() ,
    isPressControl: false,      //se está pressionando control
    evento_keydown:  null,
    bloqueia_tecla: false,

	constructor: function()
	{
		this.evento_keydown = dojo.connect( document, 'onkeydown', this.keydown);
            	dojo.connect( document, 'onkeyup', this.keyup);
                dojo.connect( window, 'onchange', this.onchange);
	}
    
    ,

    /**
     *faz focus considerando diversas situações
     */
    setFocus : function ( fieldId, now)
    {
        if ( !fieldId )
        {
            return;
        }

        element = dojo.byId(fieldId);

        if ( ! element )
        {
            return;
        }

        tagName = element.tagName;

        //caso não seja um elemento input define um tabindex para poder definir um foco
        if ( ! ( tagName == 'INPUT' || tagName == 'SELECT' || tagName == 'a') && ! element.getAttribute('tabindex'))
        {
            element.setAttribute('tabindex',0);
        }

        if ( now ) //agora
        {
            //try catch é para compatibilidade com o Internet Explorer que tem problema em definir foco em campos escondidos
            try
            {
                //seta o elemento último elemento ativo,
                gnuteca.setLastActiveElement( element );
                element.focus();
            }
            catch (err)
            {
                console.log('Erro ao definir o foco no elemento '+ fieldId);
            }
        }
        else //aguardar um pouco
        {
            setTimeout( 'gnuteca.setFocus( \'' + fieldId + '\', true )', 750);
        }
    }

    ,

    /**
     * Mostra ou esconde um elemento considerando diversas situações
     */
    setDisplay : function ( fieldId, label, display )
    {
        field =  document.getElementById( fieldId );

        if ( field )
        {
            //div não tem label
            if ( field.tagName == 'DIV' )
            {
                label = false;
            }

            if ( label )
            {
                //precisa acessar as div's superiores, para esconder a label
                if ( field.type == 'checkbox' || field.type == 'fieldset' || field.tagName == 'select' )
                {
                    field = field.parentNode.parentNode.parentNode;
                }
                else
                {
                    //para funcionar em casos normais e casos com container
                    if ( field.parentNode.className == 'mContainerHorizontal' )
                    {
                        field = field.parentNode;
                    }
                    else
                    {
                        field = field.parentNode.parentNode;
                    }
                }
            }

            field.style.display= display;
        }
    }

    ,
    
    onchange : function (e)
    {
        try
        {
            dojo.byId('isModified').value = 't';
        }
        catch (err) 
        {}
    }
    
    ,
    

    /**
     * Define o último elemento ativo
     * Utilizado para restaurar o foco
     */
    setLastActiveElement : function ( activeElement )
    {
        if ( activeElement )
        {
            try
            {
                tagName = activeElement.tagName;
                //caso não tenha janela diálogo aberta define último elemento ativo
                canDoFocus = tagName == 'INPUT' || tagName == 'SELECT' || tagName == 'A';

                if ( !dojo.byId('divPromptDown') && canDoFocus )
                {
                    this.lastActiveElement = activeElement.id;
                }
            }
            catch (err)
            {
                //esse try cath é feito em função do botão de 'browse' do input de upload
            }
        }
    }

    ,

    /**
    * Função chamado ao levantar/soltar uma levantar tecla
    * Utilizada no sistema de teclas de atalho do gnuteca
    */
	keyup : function (e)
	{
		if ( window.event ) // IE
		{
			keycode = e.keyCode;
		}
		else if ( e.which ) // Netscape/Firefox/Opera
		{
			keycode = e.which;
		}

        gnuteca.isPressControl = false; //desativa o pressionamento do control
        ajaxElement = dojo.byId('mLoadingMessage').style.display == 'block';
		
	    btnClose = document.getElementById('btnClose');

	    //HABILITA ESC QUANDO TIVER BTNCLOSE
	    if( keycode == 27 && !ajaxElement )
	    {
	        //e.preventDefault();
	        if (btnClose)
	        {
	            e.preventDefault();
	            btnClose.onclick();
	            return false;
	        }

	        e.preventDefault();

	        //FIXME isso esta muito hardcode, mas foi a solucao encontrada
	        if ( document.getElementById('personIdW') )
	        {
                //para recarregar as teclas de atalho
	        	miolo.doAjax('verifyUserOnClose', '', '__mainForm');
	        }

            //fecha a mWindow atual, caso exista
            if ( miolo.windows.handle['current'] )
            {
                //miolo.windows.handle['current'].dialog.hide();
                miolo.getWindow('').close();
            }

            stdout = dojo.byId('stdout');

            if ( stdout )
            {
                stdout.innerHTML = '';
            }
	    }
		
        return true;
	}
	
	,

    /**
    * Função chamado ao pressionar uma tecla
    * Utilizada no sistema de teclas de atalho do gnuteca
    */
	keydown : function (e)
	{
            // Se está adicionando items através do smartReader, desabilita teclas
            if ( !(window.sessaoLimpa === undefined) )
            {
                // 27, 113, 114, 115, 116, 117, 118,119,120,121,122,123
                if ( 
                     e.keyCode == 27 ||
                     e.keyCode == 113 ||
                     e.keyCode == 114 ||
                     e.keyCode == 115 ||
                     e.keyCode == 116 ||
                     e.keyCode == 117 ||
                     e.keyCode == 118 || 
                     e.keyCode == 119 ||
                     e.keyCode == 120 ||
                     e.keyCode == 121 ||
                     e.keyCode == 122 ||
                     e.keyCode == 123
                   )
                {
                    if ( window.sessaoLimpa == 0 )
                    {
                        return false;
                    }
                }
            }
		// pega a tecla e bota na variavel, trata por navegador
		if ( window.event ) // IE
		{
			keycode = e.keyCode;
		}
		else if ( e.which ) // Netscape/Firefox/Opera
		{
			keycode = e.which;
		}

	    btnClose	= document.getElementById('btnClose');
        ajaxElement = dojo.byId('mLoadingMessage').style.display == 'block';
	    keyCode     = document.getElementById('keyCode');
        
        //ativa pressionamento do control
        if ( keycode == 17 )
        {
            gnuteca.isPressControl = true;
            return true;
        }
        
        //verifica o pressionamento do control+S
        if ( gnuteca.isPressControl && keycode == 83 && !ajaxElement ) //83 = S
        {
            tbBtnSave = dojo.byId('tbBtnSave');
            
            if ( tbBtnSave )
            {
                if ( tbBtnSave.onclick )
                {
                    tbBtnSave.onclick();
                }
            }
            
            e.preventDefault();
            return false;
        }
        
        //dispara função de ajuda Chrome precisa que seja no onkeydown
	    if( keycode == 112 && !ajaxElement )
	    {
            gnuteca.help();
            e.preventDefault();
            return false;
        }

	    //bloqueia qualquer pressionamento de tecla caso o elemento de ajax loading esteja presente
	    if ( ajaxElement )
	    {
	    	e.preventDefault();
	        return false;
	    }
	    else
	    {
            //seta no elemento keyCode para conseguir pegar nos argumentos
	    	if (keyCode)
	    	{
	    		keyCode.value = keycode; 
	    	}
	    }

        activeElement = document.activeElement;
        //define o último elemento ativo a função verifica se precisa realmente definir
        gnuteca.setLastActiveElement( activeElement );
        gnuteca.removeValidatorMessage( activeElement );

        //Se a açao da tecla for em um INPUT ou um SELECT e a tecla NAO for ESC=27, ESC nao muda campo
        /*if ( (activeElement.tagName == 'SELECT' || activeElement.tagName == 'INPUT') && keycode != 27 )
        {
            alert('lugar 2');
            dojo.byId('isModified').value = 't';
        }*/

        /*
         *  verificação para manter o foco dentro da janela caso seja um diálogo
         *  referente a acessibilidade.
         */
        if ( keycode == dojo.keys.TAB )
        {
            //tem janela diálogo aberta
            if ( dojo.byId('divPromptDown') )
            {
                //verifica se existe botão de fechar na tela
                hasClose = dojo.byId('btnClose') ? true : false ;

                if ( activeElement.id == 'btnClose' || ( activeElement.id == 'btnYes' && ! hasClose ) )
                {
                    gnuteca.setFocus('popupTitle');
                    e.preventDefault();
                    return false;
                }
            }
        }

	    // HABILITA [ENTER] NA TELA DE SEARCH
	    // caso for pressionado a tecla ENTER
	    if( keycode == dojo.keys.ENTER )
	    {
               	//testa o onPressEnter do elemento ativo
            activeElement = document.activeElement

            if ( activeElement )
            {
                if( activeElement.type == 'textarea' )//desabilita caso seja texteArea
                {
                    return true; //true permite o enter normal
                }

                onPressEnter = activeElement.getAttribute('onpressenter');
                
                if (!onPressEnter)
                {
                    onPressEnter = activeElement.getAttribute('onPressEnter');
                }

                //só faz eval se encontrou evento
                if ( onPressEnter )
                {
	                eval(onPressEnter);
	                e.preventDefault();
		        return false;
                }
            }
	    	
            //Eventos de botões chamados automaticamente caso encontre estes botões no form
            //tenta localizar btnOk
            element = document.getElementById('btnOk');
            
            if ( !element ) //se não achar, tenta btnYes
            {
            	element = document.getElementById('btnYes');
            }
            
            if ( !element ) //caso ainda não ache tenta o btnLogin
            {
            	element = document.getElementById('btnLogin');
            }

	        if ( element )
	        {
	            element.onclick();
                e.preventDefault();
	            return false;
	        }
            else // em último caso chama o btnSearch, evento automático de busca
            if ( document.getElementById('btnSearch') )
            {
                element = document.getElementById('btnSearch')

                lookup = false;
               
                //procura por um lookup
                forms = document.getElementsByTagName('form');
                for (i = 0; i < forms.length; i++)
                {
                	if ( forms[i].id.indexOf('Dialog') >= 0 )
                	{
                		//Se tiver um lookup, não executa o btnSearch
                		lookup = true;
                		break;
                	}
                }

                canDoEnter = activeElement.tagName == 'INPUT' || activeElement.tagName == 'SELECT' || activeElement.tagName == 'BODY';

	            if ( element && !btnClose && !ajaxElement && !lookup && canDoEnter )
	            {
	                element.onclick();
                    e.preventDefault();
    	            return false;
	            }
            }

	        return true;
	    }
        
        //se codes (códigos de teclas pressionadas) estiver vazio, retorna
	    if ( ( typeof( codes ) == "undefined" ) )
	    {
            return true;
	    }
	    
	    //se apertar 
	    if( keycode == 27 && btnClose )
	    {
	    	return false;
	    }

	    //compara relação de teclas possíveis ( definidas por keyDownHandler )
        if ( codes != null)
        {
	        for (i = 0 ; i< codes.length ; i++)
	        {
	            if ( codes[i] == keycode)
	            {
	            	//se existir chama função ajax
	                miolo.doAjax('onkeydown'+keycode,'',frm);
	                
	                //se for diferente de 13 ( enter) previne o efeito padrao
	                if ( codes[i] != 13)
	                {
        	            e.preventDefault();
	                    return false;
	                }
	            }
	        }
        }
        
        return true;
	}
	,

    /**
     * Limpa os campos de um formulário
     */
    clearForm : function()
    {
    	divGrid = dojo.byId('divGrid');

    	if ( divGrid )
    	{
    		divGrid.innerHTML = '';
    	}

        var elements = document.getElementsByTagName('input');
        
        for (i=0; i < elements.length; i++)
        {
            if (elements[i].type == 'text')
            {
                elements[i].value = '';
            }
            
            // Quando existir este atributo, significa campo tipo data do dojo
            if (elements[i].getAttribute('aria-valuenow') != null )
            {
                elements[i].value = '';
                var hiddenName = elements[i].id;
                var list = document.getElementsByName(hiddenName); 
                for (y=0; y <  list.length; y++) 
                { 
                    list[y].value = ''; 
                }
            }

            if (elements[i].type == 'checkbox')
            {
                elements[i].checked = false;
            }
        }

		elements = document.getElementsByTagName('select');
        
        for (i=0; i < elements.length; i++)
        {
			elements[i].selectedIndex = 0;
        }
    },

    /**
     * Chama mensagem de ajuda do formulário atual
     */
    help : function()
    {
    	miolo.doAjax('help',document.activeElement.id,frm);
    }

    ,

    /*
     * Função que fechar a janela popup (injectcontent), retornando o foco ao campo utilizado antes da janela
     */
    closeAction : function()
    {
        dojo.byId('stdout').innerHTML = '';
		dojo.byId('divForm').innerHTML = '';
        gnuteca.setFocus(this.lastActiveElement,true);
	},

    /**
     * Desabilita uma aba, utilizado pelo componente de abas
     */
	disableTab : function ( tabId, disabled , tabControl)
	{
		button = document.getElementById(tabId+'Button');

		if (button)
		{
			if (disabled)
			{
                button.className    = 'a-tab-disabled';
                button.setAttribute('onclick', 'return false;' );
                firstTab(tabControl);
            }
            else
            {
                button.className    = 'a-tab';
                button.setAttribute('onclick', 'return gnuteca.changeTab(\''+tabId+'\',\''+tabControl+'\')' );
                gnuteca.changeTab( tabId, tabControl );
            }
        }

    }

	,
    /**
     * Remove uma aba, utilizado pelo componente de abas
     */
	removeTab : function ( tabId, tabControl )
    {
        button = document.getElementById(tabId+'Button');

        if ( button )
        {
            button.innerHTML = '';
            button.className = 'a-tab-removed';
            button.onclick   = null;
        }

        tab = document.getElementById(tabId);

        if ( tab )
        {
            tab.innerHTML = '';
        }

        firstTab(tabControl);

    }

    ,
    
    /**
     * Troca a aba ativa, utilizado pelo componente de abas
     */
	changeTab : function ( tabId , tabControl)
	{
        //FIXME solução temporária para funcionar os detalhes da pesquisa no IE
        // temporária?? Mesmo??
        if ( ! dojo.isIE > 0)
        {
            try
            {
                eval('focusTab ='+tabId+'Focus');

                if ( focusTab )
                {
                    gnuteca.setFocus( focusTab);
                    //focusTab = dojo.byId(focusTab);
                    //setTimeout( "focusTab.focus()", 1);
                }
            }
            catch ( err )
            {
                return false;
            }
        }

        //caso não conseguiu encontrar o elemento sai fora
        try
        {
    	    tabArray = eval(tabControl+'Tabs');
        }
        catch ( err )
        {
            return false;
        }

	    //Se nao existir a tab, continua na atual
	    var element = document.getElementById(tabId + 'Button');

	    if (!element)
	    {
            return false;
        }

        //desativa todas
		for (i = 0; i < tabArray.length; i++)
		{
            data    = tabArray[i];
            div     = document.getElementById( data );
            button  = document.getElementById( data+'Button' );

            if ( tabId == data )
            {
                button.className = 'a-tab-selected';
                div.style.display = 'block';
            }
            else
            {
                div.style.display = 'none';

                if ( button.className != 'a-tab-disabled' )
                {
                    if ( button.className != 'a-tab-removed' )
                    {
                        button.className  = 'a-tab'
                    }
                }
            }
		}

		return true;
	}

	,
	/**
     * Tabs: Troca para a primeira tab do controle
     */
	firstTab : function ( tabControl )
	{
        tabArray = eval(tabControl+'Tabs');
        gnuteca.changeTab( tabArray[0] , tabControl);
    }
	,
	
    /**
     * Mostra/esconde um campo considerando sua situação
     */
    changeDisplay : function ( divDisplay, divImage )
    {
        element    = document.getElementById(divDisplay);

        if ( divImage )
        {
            divImage   = document.getElementById(divImage);
        }

        if (divImage)
        {
            var images = divImage.getElementsByTagName('img');
        }

        if ( element.style.display == 'none' )
        {
            element.style.display = 'block';

            if (divImage)
            {
                images[0].src = imageMinus;
            }
        }
        else
        {
            element.style.display = 'none';

            if (divImage)
            {
                images[0].src = imagePlus;
            }
        }
    }

    ,

    /**
     * Toolbar: Troca estado da toolbar
     */
    toolBarChanger: function()
    {
        mBoxOuter       = dojo.query('.mBox')[0];
        toolBar         = dojo.byId('toolBar');
        toolbarChanger  = dojo.query('#toolbarChanger img')[0];

        if ( mBoxOuter && toolBar && toolbarChanger)
        {
            //mostra
            if ( toolBar.style.display == 'none')
            {
                toolBar.style.display = 'block';
                mBoxOuter.style.width =  this.mBoxOuterWidth+'px';
                toolbarChanger.src    =  this.imgChangerLeft;
                toolBarChangerState   = 'block';

            }
            else //esconde
            {
                this.mBoxOuterWidth   =  mBoxOuter.clientWidth; //guarda o tamanho antigo para restaurar
                toolBarChangerState   = 'none';
                toolBar.style.display = 'none' ;
                mBoxOuter.style.width = '98%'; //tamanho fullscreen
                toolbarChanger.src    =  this.imgChangerRight;
            }
        }
    }

    ,

    /**
     * Toolbar: esconde toolbar
     */
    hideToolBar : function()
    {
        mBoxOuter               = dojo.query('.mBox')[0];
        toolBar                 = dojo.byId('toolBar');
        toolbarChanger          = dojo.query('#toolbarChanger img')[0];

        if ( mBoxOuter && toolBar && toolbarChanger && toolBarChangerState == 'none')
        {
            toolBarChangerState     = 'none';
            toolBar.style.display   = 'none' ;
            mBoxOuter.style.width   = '99%';
            toolbarChanger.src      = this.imgChangerRight;
        }
    }
	
	,

    /**
     * Validadores: Limpar mensagens de erro de todos validadores
     */
    cleanValidatorsMessage : function (  )
	{
        //remove mensagem de erro
        errors= dojo.query('.gValidateErrorMessage');

        for ( i= 0; i < errors.length ; i++ )
        {
            errors[i].parentNode.removeChild(errors[i]);
        }

        //remove class de erro do campo
        errors= dojo.query('.gValidateFieldError');
        
        for ( i= 0; i < errors.length ; i++ )
        {
           errors[i].className = errors[i].className.replace( 'gValidateFieldError','');
        }
    }
    
    ,

    /**
     * Validadores: Adiciona mensagem de erro de validação a algum campo
     */
    addValidatorMessage : function ( fieldId, msg )
	{
        field = dojo.byId( fieldId );

        if ( field )
        {
            //campos data
            if ( field.className == 'dijitReset' )
            {
                field = field.parentNode.parentNode.parentNode;
            }

            field.className += ' gValidateFieldError';
            msgField = document.createElement('div');
            msgField.innerHTML = msg;
            msgField.id = fieldId +'MsgError';
            msgField.className = 'gValidateErrorMessage';
            field.parentNode.appendChild(msgField);
        }
    }

    ,

    //tira a mensagem de validação de um campo, passando o objeto do elemento
    removeValidatorMessage : function ( field )
    {
        try
        {
            if ( field )
            {
                myId = field.id.replace('_sel', ''); //para funcionar o onkeypress no combo
                msgError = dojo.byId( myId + 'MsgError' );

                if ( msgError )
                {
                    msgError.parentNode.removeChild(msgError);

                    if ( field.className == 'dijitReset' )
                    {
                        field = field.parentNode.parentNode.parentNode;
                    }
                    else
                    {
                      field = dojo.byId(myId); //para funcionar o onkeypress no combo
                    }

                    field.className = field.className.replace( 'gValidateFieldError','');
                }
            }
        }
        catch (err)
        {
            //esse try cath é feito em função do botão de 'browse' do input de upload
        }
    }
    ,

    /**
     * Define um valor para ser usado em um campo repetitivo
     */
    setValueForRepetitive : function ( fieldId, value )
    {
        field = dojo.byId(fieldId);
        defaultField = dojo.byId(fieldId+'_defaultValue');
        
        //caso não tenha valor, coloca valor padrão
        if ( defaultField )
        {
            value = value ? value : defaultField.value;
        }

        //MSelection
        if ( field.tagName == 'SELECT' && value == '' )
        {
            //define o elemento selecionado como o primeiro
            field.selectedIndex = 0;
        }

        //MCalendarField
        if ( field.className == 'dijitReset')
        {
            dijit.byId( fieldId ).attr( 'displayedValue', value );
        }

        //campos normais, desconsidera campos somente leitura e hidden
        if ( field.type != 'hidden' && field.className != 'mReadonly' )
        {
            field.value = '' + value;
        }

        //MRadiobuttonGroup
        var radio;

        for ( i=0; i < 10; i++ )
        {
            radio = dojo.byId(fieldId+'_' + i);

            if ( radio )
            {
                if ( radio.value == value )
                {
                    radio.checked = true;
                }
                else
                {
                    //radio.checked = false;
                }
            }
        }

        fieldSel = dojo.byId( fieldId+'_sel' );

        //MCombo
        if ( fieldSel )
        {
            fieldSel.value = '' + value;
        }
    }

    ,
    
    /**
     * Aciona uma ação do campo repetitivo
     */
    repetitiveFieldAction: function( nam, index, func )
    {
        dojo.byId('GRepetitiveField').value= nam;
        dojo.byId('arrayItemTemp').value= index;
        dojo.byId('isModified').value ='t'; //define como modificado
        miolo.doAjax( func ,'',frm) ;
    }

    ,

    /*
     * função utilizada na catalogação para copiar dados de um campo para outro
     * 
     */
    autoCompleteMarcFields : function( e , from, to, affectRecordsCompleted  )
    {
        //recebe as strings para montar os ids dos campos de origem e destino
        var fromFieldId = 'spreeadsheetField_'+from.replace('.','_').trim();
        var toFieldId = 'spreeadsheetField_'+to.replace('.','_').trim();

        //obtem os campos
        fromField = dojo.byId( fromFieldId );
        toField = dojo.byId( toFieldId );

        //caso não existam cancela funcionalidade
        if( !fromField ) 
        {
            console.error('Regras para completar campos marc: Campo de origem ' + fromFieldId +' não foi encontrado!');
            return;
        }

        if( !toField )
        {
            console.error('Regras para completar campos marc: Campo de destino' + toFieldId +' não foi encontrado!');
            return;
        }

        //a lógica abaixo determina se permite ou não copia dos dados

        //caso o destino seja vazio sempre pode copiar
        if ( toField.value.length == 0 )
        {
            gnuteca.autoCompleteMarcFieldsCopy[from] = true;
        }

        //caso possa copiar e o destino for maior que a origem
        if ( gnuteca.autoCompleteMarcFieldsCopy[from] == true && ( toField.value.length > fromField.value.length )  )
        {
            gnuteca.autoCompleteMarcFieldsCopy[from] = false;
        }

        //caso a variável esteja em estado inicial e exister dados no campo, impossibilita a cópia
        if ( gnuteca.autoCompleteMarcFieldsCopy[from] == undefined && toField.value.length > 0 )
        {
            gnuteca.autoCompleteMarcFieldsCopy[from] = false;
        }

        //efetua realmente a cópia, caso copy ou afeta
        if ( gnuteca.autoCompleteMarcFieldsCopy[from] == true || affectRecordsCompleted )
        {
            toField.value = fromField.value;
        }
    }

    ,
    
    /**
     * Utilizado pelo componente de estrelas/avaliação GStar
     */
    setStar : function ( starName , selectedItem, starCount )
    {
        input = dojo.byId( starName.replace( '_','') );

        for ( i = 1 ; i <= starCount ; i++ )
        {
            starImage = starName + 'star' + i+ 'img';
            starImage = dojo.byId( starImage);

            if ( starImage )
            {
                if ( i <= selectedItem )
                {
                    starImage.src = starImage.src.replace('star_disabled.png', 'star.png');
                }
                else
                {
                    starImage.src = starImage.src.replace('star.png', 'star_disabled.png');
                }
            }
        }

        if ( input )
        {
            input.value = selectedItem;
        }
    }

    ,
    
    /**
     * retorna true ou false caso os dados do usuário tenham sido modificados
     */
    isModified : function( )
    {
        //obtem informação de modificado
        isModifiedField = dojo.byId('isModified');

        if ( isModifiedField )
        {
            isModified = isModifiedField.value;
        }
        else
        {
            isModified = '';
        }

        //obtem informação de modo de função
        functionModeField = dojo.byId('functionMode');

        if ( functionModeField )
        {
            functionMode = functionModeField.value;
        }
        else
        {
            functionMode = '';
        }

        if ( isModified == 't' && functionMode == 'manage')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    ,
    
    /**
     * igual ao miolo.doLink com a diferença que este faz verificação de modificações por parte do usuário
     */
    doLink : function ( link, form )
    {
        if ( !form )
        {
            form = '__mainForm';
        }
        
        if ( gnuteca.isModified() )
        {
            //addSlashes
            link=link.replace(/\\/g,'\\\\');
            link=link.replace(/\'/g,'\\\'');
            link=link.replace(/\"/g,'\\"');
            link=link.replace(/\0/g,'\\0');
            
            miolo.doAjax('verifyModified','miolo.doLink(\''+ link + '\',\'' + form +'\');',frm);
        }
        else
        {
            miolo.doLink(link, form);
        }
    }

    ,
    
    /**
     * igual ao miolo.doAjax com a diferença que este faz verificação de modificações por parte do usuário
     */
    doAjax : function ( func, args, form )
    {
        if ( gnuteca.isModified() )
        {
            miolo.doAjax('verifyModified','miolo.doAjax(\''+ func + '\',\''+args+'\',\'' + form +'\');',frm);
        }
        else
        {
            miolo.doAjax( func, args, form );
        }
    }
}
);

gnuteca = new Gnuteca;


dojo.declare ("GnutecaSearch", null,
{
	context: null ,
	constructor: function()
	{
	},

    //se seleciona expressao esconde o (+)

    changeAddTermStatus : function()
    {
        imageAdd =  document.getElementById('addTerm');

        if (imageAdd)
        {
            if ( document.getElementById('termType[]').selectedIndex == document.getElementById('termType[]').options.length-1 )
            {
                imageAdd.style.display = 'none';
                //extraTerm0 = "<div id='extraTerms0'></div>";
                //document.getElementById('extraTermsContainer').innerHTML='uhu';

                //tem que ser 1 porque não é pra apagar o elemento 0
                for (i = 1 ; i < 20 ; i++)
                {
                	element = dojo.byId('divExtraTerms'+i);

                	if ( element )
                	{
                        element.innerHTML = null;
                	}
                }

                termControl = dojo.byId('termControl');

                if (termControl)
                {
                	termControl.value = 0;
                }
            }
            else
            {
                imageAdd.style.display = 'inline';
            }
        }
    },

    // esconde/mostra a busca especial
    changeAdvSearch : function()
    {
        element = document.getElementById('divAdvancedSearch');

        if (element)
        {
			if (element.style.display == 'none' )
			{
			   element.style.display = 'block';
               dojo.fx.wipeOut( {node: 'divAdvancedSearch', duration: 0} ).play();
               dojo.fx.wipeIn( {node: 'divAdvancedSearch', duration: 500} ).play();
               document.getElementById('showAdvSearch').value = 1;
 			}
			else
			{
			   element.style.display = 'none';
			   dojo.fx.wipeIn( {node: 'divAdvancedSearch', duration: 0} ).play();
			   dojo.fx.wipeOut( {node: 'divAdvancedSearch', duration: 500} ).play();
			   document.getElementById('showAdvSearch').value = 0;
			}
		}
    },

    adjustDetail : function()
    {
        var height = document.documentElement.clientHeight;

        height -= 300;

        divExemplarys       = document.getElementById("divExemplarys");
        divChildren         = document.getElementById("divChildren");
        divMaterialDetail   = document.getElementById("divMaterialDetail");

        if (divExemplarys)
        {
            divExemplarys.style.height =  height + "px"
        }

        if (divChildren)
        {
            divChildren.style.height =  height + "px"
        }

        if (divMaterialDetail)
        {
            divMaterialDetail.style.height =  height-50 + "px"
        }

    },

    //remove todos os filtros avançados
    clearAdvFilters : function()
    {
        var element;
 
        for (i=1; i <= document.getElementById("advFilterControl").value; i++)
        {
        	element = document.getElementById("advFilterControl" + i);

        	if ( element )
        	{
        		this.hideElement(i);
        	}
        }

        document.getElementById("advFilterControl").value = 0;
        document.getElementById("advFilterContainer").style.display = "none";
    },

    hideElement: function(id)
    {

    	document.getElementById('advFilterControl'+id).innerHTML = '';
    	document.getElementById('advFilterControl'+id).style.display = 'none';

    	if (document.getElementById('advFilterControl').value == id)
    	{
    		document.getElementById('advFilterControl').value -= 1;
    	}

        element0 = document.getElementById('advFilter0')

        if ( element0 )
        {
            if (element0.innerHTML == '')
            {
    		    element0.parentNode.removeChild(element0);
    	    	if (document.getElementById('advFilterControl').value == id)
    	    	{
    	    		document.getElementById('advFilterControl').value -= 1;
    	    	}
            }
        }

    	if (!(document.getElementById('advFilterControl').value > 0) )
    	{
    		document.getElementById('advFilterContainer').style.display = 'none';
    	}

    },

    removeTerm: function(id)
    {
    	element = document.getElementById('divExtraTerms'+id);

    	if (element)
    	{
    		element.parentNode.removeChild(element);
    		//element.innerHTML = ''
    	}
    }

    ,

    changeLetter : function (letter)
    {
        el = document.getElementsByTagName('button');

        for (i =0 ; i<el.length; i++)
        {
            el[i].style.color= 'black';
            el[i].style.fontWeight= 'normal';
        }
        
        dojo.byId('letter').value = letter;
        dojo.byId(letter).style.color='blue';
        dojo.byId(letter).style.fontWeight= 'bold';
        dojo.byId('btnSearch').onclick();
    },
    
    /**
     * Apaga o conteúdo de um campo da texto.
     */
    clearTextField : function(id)
    {
        var textField = document.getElementById(id);
        
        if( textField && textField.type == 'text' )
        {
            textField.value = '';
        }
    }

}
);

gnutecaSearch = new GnutecaSearch;
