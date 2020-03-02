<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 22/10/2008
 *
 **/
session_start();
class GrdSimpleSearch extends GAddChildGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busSearchFormat;
    public $busExemplaryControl;
    public $busMaterial;
    public $busMaterialControl;
    public $busExemplaryStatus;
    public $busSearchFormatColumn;
    public $businessSpreadsheet;
    public $busAuthenticate;
    public $hrefReserve;
    public $hrefRequestChangeExemplaryStatus;
    public $hrefShowArticles;
    public $hrefAC;
    public $busRequestChangeExemplaryStatusAccess;
    public $busFile;

    public $actionDetail;
    public $actionExemplaryes;
    public $actionGetControlNumber;
    public $actionChildren;
    public $actionReserve;
    public $actionChangeStatus;
    public $actionChangeMaterial;
    public $actionDuplicateMaterial;
    public $actionAddChild;

    /**
     * Palavras em destaque
     * 
     * @var array
     */
    public $highLight = array();

    public function __construct($data)
    {
    	//limpa sessão utilizada no pdf
        $_SESSION['SimpleSearchGridData']  = null;
        //bussiness
        $this->MIOLO                                    = MIOLO::getInstance();
        $this->module                                   = MIOLO::getCurrentModule();
        $this->action                                   = MIOLO::getCurrentAction();
        $this->busSearchFormat                          = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busExemplaryControl                      = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busMaterial                              = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busMaterialControl                       = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');
        $this->busExemplaryStatus                       = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busSearchFormatColumn                    = $this->MIOLO->getBusiness($this->module, 'BusSearchFormatColumn');
        $this->businessSpreadsheet                      = $this->MIOLO->getBusiness($this->module, 'BusSpreadsheet');
        $this->busAuthenticate                          = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busRequestChangeExemplaryStatusAccess    = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusAccess');
        $this->busFile                                  = $this->MIOLO->getBusiness($this->module, 'BusFile');

        //colunas
        $columns = array(
            new MGridColumn(_M('Número de controle', $this->module),    MGrid::ALIGN_LEFT,   null, null, $this->externalSearch(), null, false),
            new MGridColumn(_M('Imagem', $this->module),             MGrid::ALIGN_LEFT,   null, null, true, null, false),
            new MGridColumn(_M('Descrição', $this->module),       MGrid::ALIGN_LEFT,   null, null, true, null, false),
            new MGridColumn(_M('Exemplares', $this->module),        MGrid::ALIGN_LEFT,   null, null, true, null, false)
        );

        //precisamos fazer isto, pois não escondia as colunas em função do _M
        $realNameOfColumns = array( 'controlNumber','image', 'data', 'exemplarys' );

        //Verifica quais colunas deve mostrar de acordo com configuracao no SearchFormat
        $req = (Object)$_REQUEST;

        //caso não encontre search format, obtem padrão e define no request
        //isso é útil no caso de um link direto com o número de controle
        if ( !$req->searchFormat )
        {
            $_REQUEST['searchFormat'] = SIMPLE_SEARCH_SEARCH_FORMAT_ID;
            $req->searchFormat = SIMPLE_SEARCH_SEARCH_FORMAT_ID;
        }

        $this->busSearchFormatColumn->searchFormatIdS = $req->searchFormat;
        $search = $this->busSearchFormatColumn->searchSearchFormatColumn(TRUE);

        $hideColumns = array();

        //se tem colunas para esconder
        if ($search)
        {
        	//monta um array simples com os nomes das colunas
            foreach ($search as $searchFormatColumn)
            {
                $hideColumns[] = strtolower( trim($searchFormatColumn->column ) ); //relação de colunas a esconder
            }

            //passa pelas colunas para
            foreach ( $hideColumns as $line => $columnToHide )
            {
            	$index = array_search( $columnToHide, $realNameOfColumns ); //procura o indice desse dado no array de colunas reais
             	$columns[ $index ]->visible = FALSE; //esconde a coluna especifica
            }
        }

        //coloca as ações na vertical
        $this->actionAlign = 'vertical';
        //TODO não processar nada se a coluna não for visível no checkValues
        parent::__construct($data, $columns, $this->MIOLO->getCurrentURL(), LISTING_NREGS, 0, 'grdSimpleSearch');

        $this->addActionSelect(); //adiciona os checkboxes
        $this->actionIcons();
        $this->setIsScrollable();
        
        $this->setRowMethod($this, 'checkValues');

        //configura palavras para destaque
        $highLight = MIOLO::_REQUEST('termText');

        if ( is_array( $highLight ) )
        {
            foreach ( $highLight as $line => $word )
            {
                //só ativa palavra em destaque caso exista palavra
                if ( $word && !is_numeric( $word ) )
                {
                    $id['a'] = '$#@';
                    $id['e'] = '$#*';
                    $id['i'] = '$!@';
                    $id['o'] = '@$#';
                    $id['u'] = '$#!';
                    $id['c'] = '!@%';
                    $id['n'] = '!#@';

                    $myWord = new GString($word);
                    $myWord->toASCII();
                    $myWord->toLower();
                    //troca letras por caracteres especiais
                    $myWord->replace( array('a','e','i','o','u','c','n'), $id );
                    //troca caracteres especiais por letras para expressão regular
                    $myWord->replace( $id , array('[aáàâãä]','[eéèêë]','[iíìîï]','[oóòôõö]','[uúùûü]','[cç]','[nñ]'));
                    
                    // Escapa "/" para a função highlight.
                    $myWord->replace('/', '\/');
                    
                    // Escapa "(" para a função highlight.
                    $myWord->replace('(', '\(');
                    
                    // Escapa ")" para a função highlight.
                    $myWord->replace(')', '\)');

                    //somente conteúdo fora de tags
                    $this->highLight[] = "/(>[^>|^<]*)($myWord)([^<]*<)/iu";
                }
            }
        }
    }

    public function generate()
    {
        $data = $this->data;
        //gera Botão de Detalhes do Exemplar quando for um único exemplar (reservas e Empréstimos), só aparece na circulação de material
        if ( count($data) === 1 && is_array( $data ) && MIOLO::_REQUEST('action') == 'main:materialMovement' )
        {
        	$fotter                 = null;

            $footer[] = $text = new MTextField('uniqueItemNumber',$data[0]['CONTROLNUMBER'] );
            $text->addStyle('display','none');
            $footer[] = $btnLoan = new MButton('btnDetailLoan','<b>[F5]</b> '._M('Empréstimos', $this->module),':onkeydown116_120' , GUtil::getImageTheme('loan-16x16.png') );
            $footer[] = $btnReserve = new MButton('btnDetailReserve', '<b>[F6]</b> '._M('Reservas', $this->module),':onkeydown117_120' , GUtil::getImageTheme('reserve-16x16.png') );
        }

  	    //só mostra os botões caso existam dados
  	    if ($data)
  	    {
	        $footer[] = $btnFavorites = new MButton('btnFavorites', _M('Favoritos', $this->module ),':btnFavorites_click' );
            Gutil::accessibility( $btnFavorites, 20 );

	        if ( !$this->externalSearch() )
	        {
                    $footer[] = $btnReport = new MButton('btnReport', _M('Gerar PDF', $this->module), ':btnReport_click' );
                    Gutil::accessibility( $btnReport, 20 );
                    $footer[] = $btnMail = new MButton('btnMail', _M('Enviar PDF para o e-mail', $this->module), ':btnMail_click');
                    Gutil::accessibility( $btnMail, 20 );

                    if (GOperator::isLogged() || HABILITAR_EXPORTAR_ISO_2709 == DB_TRUE)
                    {
                        $footer[] = $btnExportIso = new MButton('btnIso', _M('Exportar ISO 2709', $this->module), ':btnExportISO2709_click');
                        Gutil::accessibility( $btnExportIso, 20 );
                    }
                    }
                }

        if ($footer)
        {
            $this->setFooter( $footer );
        }

        return parent::generate();
    }

    function externalSearch()
    {
        return (MIOLO::_REQUEST('action') == 'main:search:externalSearch') || $_SESSION['externalSearch'];
    }

    public function actionIcons()
    {
        $this->actionDetail = $this->addActionIcon( _M('Detalhes', $this->module), GUtil::getImageTheme('detail.png') , 'javascript:'.GUtil::getAjax('openMaterialDetail', '%0%') );

        $showActions = Mutil::getBooleanValue( SIMPLE_SEARCH_SHOW_EXTRA_ACTIONS );

        //Show exemplaryes action
        if ( $showActions )
        {
            $args['gotoTab'] = 'tabExemplary';
            $args['controlNumber'] = '%0%';
            $this->actionExemplaryes= $this->addActionIcon( _M('Exemplares', $this->module), GUtil::getImageTheme('catalogue.png'), GUtil::getAjax('openMaterialDetail', $args) );
        }

        if ( $this->externalSearch() )
        {
            $element = MIOLO::_REQUEST('parentElement');
            
            //Caso tenha a preferência de domínio preenchida (GNUTECA_DOMAIN), seta o document.domain = 'GNUTECA_DOMAIN';
            if(strlen(GNUTECA_DOMAIN) > 0)
            {
                $pDomain = "document.domain = '".GNUTECA_DOMAIN."';";
            }
            else
            {
                //Caso não esteja preenchido, não poe nada no local
                $pDomain = "";
            }
            
            $jsByDomain = "javascript: $pDomain this.opener.document.getElementById('{$element}').value = %0%; try{this.opener.document.getElementById('{$element}').onblur();}catch(e){} window.close();";
            
            $this->actionGetControlNumber = $this->addActionIcon( _M('Selecionar', $this->module), GUtil::getImageTheme('getControlNumber-28x28.png') , $jsByDomain);
            return;
        }

        if ( $showActions )
        {
            //Show articles action
            $args['gotoTab']        = 'tabChildren';
            $args['controlNumber'] = '%0%';
            $this->actionChildren   = $this->addActionIcon( _M('Artigos/Fascículos', $this->module), GUtil::getImageTheme('fascicle.png'), GUtil::getAjax('openMaterialDetail', $args) );
            $this->hrefShowArticles = $hrefShowArticles;
        }

        //ação de reserva
        $this->actionReserve    = $this->addActionIcon( _M('Reservar', $this->module), GUtil::getImageTheme('reserve.png') );

        //Com código do usuário, verifica se o grupo ativo tem permissão para requisitar congelados
        //Só mostrar botão Congelado quado houver um usuário com permissão ou operador na Circulação de material
        if ( ($this->busRequestChangeExemplaryStatusAccess->checkPersonAccess()) || ( (MIOLO::_REQUEST('action') == 'main:materialMovement') && (GOperator::isLogged()) ) )
        {
            $args['_id']              = '%0%';
            $this->actionChangeStatus = $this->addActionIcon( SEARCH_REQUEST_TITLE, GUtil::getImageTheme('congelado.png'), GUtil::getAjax('gridRequestChangeExemplaryStatus', $args) );
        }

        //ações administrativas
        if (GOperator::isLogged() && $showActions)
        {
        	if (GPerms::checkAccess('gtcMaterial', 'update', false))
        	{
	            $argsN['function']           = 'update';
	            $argsN['controlNumber']      = '%0%';
                $hrefUM = $this->MIOLO->getActionURL('gnuteca3', 'main:catalogue:material', null, $argsN) ;
                $this->actionChangeMaterial  = $this->addActionIcon( _M('Alterar material', $this->module), GUtil::getImageTheme('changeMaterial.png'), $hrefUM );
        	}

        	/**
        	 * Só mostra ações de duplicar a adicionar filho caso tenha permissão
        	 */
        	if ( $this->getMaterialPermission() )
            {
	            $argsN['function']             = 'duplicate';
	            $argsN['controlNumber']        = '%0%';
                $hrefDM = $this->MIOLO->getActionURL('gnuteca3', 'main:catalogue:material', null, $argsN) ;
	        	$this->actionDuplicateMaterial = $this->addActionIcon( _M('Duplicar material', $this->module), GUtil::getImageTheme('duplicateMaterial.png') , $hrefDM );

	        	$argsM['function']          = 'addChildren';
	            $argsM['controlNumber']     = '%0%';
	            $argsM['leaderString']      = '';
                $hrefAC = $this->MIOLO->getActionURL('gnuteca3', 'main:catalogue:material', null, $argsM ) ;
	            $this->actionAddChild       = $this->addActionIcon( _M('Adicionar filho', $this->module), GUtil::getImageTheme('addChild.png') , $hrefAC );
	            $this->hrefAC               = $hrefAC;
            }
        }
    }

    public function checkValues($i, $row, $actions, $columns)
    {
        list($colControlNumber,
             $colImage,
             $colData,
             $colExemplarys) = $columns; //$columns[3]

        //define o tabIndex para todas ações.
        foreach ( $actions as $line => $action )
        {
            $action->setTabIndex(20);
        }
        
    	$args                  = (object) $_REQUEST;
    	$data                  = $this->data;
    	$controlNumber         = $data[$i]['CONTROLNUMBER'];
    	$material              = $this->busMaterialControl->getMaterialControl( $controlNumber );
        $controlNumberFather   = $material->controlNumberFather;

        if ( $this->busFile->fileExists('cover', $controlNumber.'.') )
        {
            $coverArgs['coverControlNumber'] = $controlNumber;
            $link = GUtil::getAjax('showCover', $coverArgs);
            $colImage->control[$i]->setValue('<img src="file.php?folder=cover&file='.$controlNumber.'.&width=100px" alt="Capa para o material'.$controlNumber.'" onclick="'.$link.'" class="coverImage"></img>');
        }

        if ( !$this->externalSearch() && $this->actionChildren)
        {
            //Desabilita acao ver artigos/fasciculos caso nao exista
            $childrens = $this->busMaterialControl->getChildren( $controlNumber ); //select
            $function = ($childrens) ? 'enable' : 'disable';
            $this->actionChildren->$function();
        }

        //monta ação para adição de filho
        if ($this->actionAddChild)
        {
            $this->adjustChildAction($this->actionAddChild, $controlNumber);
        }

        //passa a categoria pega la em cima para evitar reselect no banco
        //$this->busSearchFormat->relationOfFieldsWithTable = false; //otimiza formatação não utilizando relação de campos com tabelas
    	$tempData = $this->busSearchFormat->formatSearchData( $args->searchFormat , $data[$i], null, $material->category );
        //troca palavras por versão em destque
        $highLight = preg_replace_callback($this->highLight,"highlight",$tempData);
        
        if ( $highLight && $this->validaHighlight($highLight) )
        {
            $tempData = $highLight;
        }
        
    	$tempDataDiv = new MDiv('materialContent'.$controlNumber, $tempData,'divMaterialContent');

    	// LISTA DE EXEMPLARES A IGNORAR NA PESQUISA
    	if( !GOperator::isLogged() && SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS != 'SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS' && strlen(SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS))
    	{
            $this->busExemplaryControl->exemplaryStatusIdNotIn = implode("','",explode(",", SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS));
    	}

    	//select pesado que lista todos exemplares
        $exemplarys         = $this->busExemplaryControl->getExemplaryOfMaterialByGrid($controlNumber, $args->libraryUnitId, $args->libraryUnitId ? true : false, true);

        $isFatherExemplary  = FALSE;
        $isFatherExemplary  = $this->busExemplaryControl->isFatherExemplar;

        //se não tiver exemplar mas tiver pai seta url de reserva para o número do pai
        $this->actionReserve->href = GUtil::getAjax('gridReserve', $isFatherExemplary ? $controlNumberFather : $controlNumber );

		//ativa/desativa o ação de exemplares/reserva caso não tenha exemplar
		if ($exemplarys)
		{
            if ( $this->actionExemplaryes )
            {
                $this->actionExemplaryes->enable();
            }

			if ( !$this->externalSearch() )
            {
                $this->actionReserve->enable();
            }

			//esta ação pode nem ter sido adicionada
			if ( $this->actionChangeStatus )
			{
                $this->actionChangeStatus->enable();
			}
		}
		else
		{
            if ( $this->actionExemplaryes )
            {
                $this->actionExemplaryes->disable();
            }
            
			if(!$this->externalSearch())
            {
                $this->actionReserve->disable();
            }

			//esta ação pode nem ter sido adicionada
			if ( $this->actionChangeStatus )
            {
                $this->actionChangeStatus->disable();
            }
		}

        $allIsLow           = TRUE; //Todos exemplares em baixa
        $exemplaryData      = null;
        $low_exemplaryes    = 0; //Conta o numero de exemplares em baixa

        if ( is_array($exemplarys) && ($exemplarys) )
        {

            foreach ($exemplarys as $libraryUnitId => $exemplaryStatus) //libraryUnitId
            {
                $libraryTotal = 0;
            	foreach ( $exemplaryStatus as $status => $materials )
            	{
        			foreach ( $materials as $materialTypeId => $materialPhysical)
        			{
            			foreach ( $materialPhysical as $materialPhysicalId => $item)
            			{
                            $statusTotal = 0;
                			foreach ($item as $line => $exemplary)
                			{
                				$emprestadoPara = null;

                				if ( ($exemplary->originalLibraryUnitId  != $exemplary->libraryUnitId)
                				        && MIOLO::_REQUEST('libraryUnitId')
                				        && ( MIOLO::_REQUEST('libraryUnitId') != $exemplary->libraryUnitId )
                				         )
                				{
                					$emprestadoPara = _M('Emprestado p/ ');
                				}

                				$exemplaryData[$libraryUnitId]->title = $emprestadoPara . $exemplary->libraryName;
                				$exemplaryStatusDescription           = $exemplary->exemplaryStatusDescription;
                				$materialTypeDescription              = $exemplary->materialTypeDescription;
                				$materialPhysicalDescription          = $exemplary->materialPhysicalTypeDescription;

                				$libraryTotal++;
                				$statusTotal++;

                                $exemplaryLibrary[$libraryUnitId]       = $exemplary->libraryName;
                                $exemplary->emprestadoPara              = $exemplaryData[$libraryUnitId]->title;
                            }
                            $exemplaryData[$libraryUnitId]->title .= ' - '. $libraryTotal . ' ' . _M('Exemplares', $this->module);

                            $colorClass = null;

                            //mostra em azul caso o exemplar esteja disponível
                            if ( $exemplary->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_DISPONIVEL )
                            {
                            	$colorClass = 'exemplaryStatusAvailable';
                            }

                            //mostra em vermelho caso esteja em baixa
                            if ( MUtil::getBooleanValue( $exemplary->isLowStatus ) )
                            {
                            	$colorClass = 'exemplaryStatusLow';
                            	$low_exemplaryes ++;
                            }
                            else
                            {
                            	$allIsLow = FALSE;
                            }

                            $exemplaryStatusDescription = new MDiv(null, $exemplaryStatusDescription,$colorClass);
                            $materialTypeDescription = new MDiv(null, $materialTypeDescription,$colorClass);
                            $materialPhysicalDescription = new MDiv(null, $materialPhysicalDescription, $colorClass);

                            $statusTotal = new MDiv(null, $statusTotal, $colorClass . ' exemplaryStatusTotal');

                			$tableData[] = array($exemplaryStatusDescription,$materialTypeDescription, $materialPhysicalDescription, $statusTotal );
                    	}
        			}
    			}

    	        $colTitle[0] = _M('Estado', $this->module);
                $colTitle[1] = _M('Tipo', $this->module);
                $colTitle[2] = _M('Físico', $this->module);
                $colTitle[3] = _M('Total', $this->module);
                $table = new MTableRaw(null, $tableData, $colTitle);
                $table->setCellAttribute(0, 0, 'width', '100');
                $table->setCellAttribute(0, 1, 'width', '80');
                $table->setCellAttribute(0, 2, 'width', '40');
                $table->setCellAttribute(0, 3, 'width', '40');
                $table->addAttribute('width', '100%');
                $table->setAlternate(true);
                $exemplaryData[$libraryUnitId]->content[$materialTypeId]->title = $table;
                unset($tableData);

                if ( count($exemplarys) >1 )
                {
                    $tree = new GTree('exemplaryTree', $exemplaryData );
                    $generate = $tree->generate();
                }
                else
                {
                    $generate = $table->generate();

                    //Se (unidade de biblioteca nao foi filtrada) OU (se mais de uma biblioteca foi escolhida) OU se (o label $emprestadoPara estiver definido)
                    if ( ( (!MIOLO::_REQUEST('libraryUnitId','POST')) || (strpos(MIOLO::_REQUEST('libraryUnitId','POST'), ","))  ) && $_SESSION['libraryCount'] > 1 || $emprestadoPara )
                    {

                        //POG originada por falta de tempo.
                        //Se ja tiver o Emprestado p adicionado no $exemplaryData[$libraryUnitId]->title, nao adicionar novamente.
                        $lbl = new MLabel( ( (strpos($exemplaryData[$libraryUnitId]->title, 'Emprestado p/') === false )?$emprestadoPara:'') . $exemplaryData[$libraryUnitId]->title  );
                        $lbl->setBold(TRUE);
                        $generate = $lbl->generate() . $generate;
                    }
                }
            }
        }

        //Salva os dados na sessao para ser utilizado no pdf
        //Cuidar muito com o que é colocado aqui os relatórios pdf da pesquisa depende disso
        $_SESSION['SimpleSearchGridData'][$controlNumber] = array(
            'exemplarys'        => $exemplarys,
            'exemplaryLibrary'  => $exemplaryLibrary,
            'data'              => $tempData,  //searchFormat
            'isFatherExemplary' =>$isFatherExemplary
        );

        //Se o material contem apenas exemplares baixados, define a cor vermelha para o campo Data na grid
        if ( $allIsLow && !$this->busMaterial->getContent($controlNumber, '856' , 'u')) 
        {
            if(!$this->externalSearch())
            {
                $this->actionReserve->disable();
            }

        	//esta ação pode nem ter sido adicionada
            if ( $this->actionChangeStatus )
            {
                $this->actionChangeStatus->disable();
            }

            //troca classe css para classe de baixa
            $tempDataDiv->setClass('exemplaryStatusLow');
        }
        
        $colData->control[$i]->setValue( $tempDataDiv->generate() );

        $description = '';

        if ($isFatherExemplary)
        {
            $description = ($isBook) ? _M('Exemplares do livro', $this->module) : _M('Exemplares do fascículo', $this->module);
            $description = '<b>' . $description . '</b>';
        }
        
        //adiciona links de redes sociais , os javascripts já foram adicionados anteriormente no FrmSimpleSearch
        if ( defined('SOCIAL_INTEGRATION') && MUtil::getBooleanValue( SOCIAL_INTEGRATION ) == DB_TRUE )
        {
            $data = $data[0];
            
            //trabalha os dados do material para fazer replace
            if ( is_array($data))
            {
                foreach ( $data as $line => $info )
                {
                    $socialReplace['$'.$line] = $info[0]->content;
                }
            }
            
            $socialContent = GUtil::strip_only( SOCIAL_CONTENT, 'script', true);
            $socialContent = str_replace( array_keys($socialReplace), array_values($socialReplace), $socialContent );
            
            $href = $this->MIOLO->getConf('home.url')."/index.php?controlNumber=".$controlNumber;
            $href = urlencode($href);
            
            $generate .= str_replace('$href', $href, $socialContent );
        }
        
        if ($generate)
        {
       	    $colExemplarys->control[$i]->setValue($description . $generate); //chama o generate da galera toda
        }
    }

    public function generateNavigationHeader()
    {
        $header = parent::generateNavigationHeader();
        $this->countLabel->addAttribute('tabindex','20');
        return $header;
    }
    
    public function validaHighlight($highlight)
    {
        $valid = TRUE;
        
        // Procura por tags quebradas.
        $valid = substr_count($highlight, '>/b>') == 0;
        $valid = substr_count($highlight, '>br/>') == 0;
        
        return $valid; 
    }
}

/**
 * Função usada para destacar as palavras pesquisadas.
 * Usada internamente em preg_match_callback
 *
 * @param array $matches
 * @return string
 */
function highlight($matches)
{
    return str_replace($matches[2], '<strong>'.$matches[2].'</strong>', $matches[0]);
}
?>