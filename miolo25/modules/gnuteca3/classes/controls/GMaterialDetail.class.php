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
 * Classe que mostra os detalhes de um material (controlNumber), é montado de acordo com as permissões do usuário logado.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2008
 *
 * */
//controlador de eventos
$possibleEvents = array( 'changeSearchFormat', 'openMaterialDetail', 'saveEvaluation', 'makeLogin' );
$event = GUtil::getAjaxFunction();

if ( in_array($event, $possibleEvents) )
{
    try
    {
        GMaterialDetail::$event();
    }
    catch ( Exception $e )
    {
        GForm::error($e->getMessage());
    }
}

class GMaterialDetail extends MDiv
{
    public $MIOLO;
    public $module;
    public $busLibraryUnit;
    public $busLoan;
    public $busLoanType;
    public $busMaterialType;
    public $busSearchableField;
    public $busSearchFormat;
    public $busAuthenticate;
    public $busDictionary;
    public $busExemplaryControl;
    public $busExemplaryStatus;
    public $busMaterialControl;
    public $busMaterial;
    public $busGenericSearch;
    public $busReserve;
    public $busReserveStatus;
    public $busReserveType;
    public $busPerson;
    public $busOperatorLibraryUnit;
    public $busBond;
    public $librarys;
    public $imageFavoritesUrl;
    public $imageReportUrl;
    public $imageMailUrl;
    public $imageChangeMaterial;
    public $imageDuplicateMaterial;
    public $imageAddChield;
    public $imageExit;
    public $imageReserve;
    public $imageGnuteca;
    public $options;
    public $exemplarys = array( );
    //variaveis dos detalhes do material
    public $controlNumber;
    public $controlNumberFather;
    public $searchFormatId;
    public $isMaterialMovement;
    public $material;
    public $isBook; //DEPRECATED use TYPE
    public $isCollection;  //DEPRECATED use TYPE
    public $isCollectionFascicle; //DEPRECATED use TYPE
    public $isColletionArticle; //DEPRECATED use TYPE
    public $isBookArticle; //DEPRECATED use TYPE
    public $type;
    public $tabExemplaryTitle;
    public $isVerifyUser;
    public $isMyLibrary;
    public $showTabs; //array contendo tabs a serem mostradas/processadas
    public $googleBook;
    public $args;

    /**
     * Constrói todos objetos conforme necessário
     *
     * @param $controlNumber controlNumber para abrir o diálogo
     * @param $filter
     * @param $searchFormat formato de pesquisa passada
     * @param $gotoTab vai para a tab passada automáticamente
     * @param $showTabs tabs a mostrar/processar, por padrão (true) é todas possíveis por permissão.
     * @return object controls do miolo
     *
     */
    function __construct($controlNumber, $filter, $searchFormat = null, $gotoTab = null, $showTabs = true, $args = null)
    {
        parent::__construct('materialDetailContainer'); //para poder fazer post para si mesmo
        $this->isMaterialMovement = ( MIOLO::_REQUEST('action') == 'main:materialMovement' );
        $this->isVerifyUser = ( trim(MIOLO::_REQUEST('action')) == 'main:verifyUser' );
        $this->isMyLibrary = ( GUtil::getAjaxFunction() == 'showDetail' );
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $this->MIOLO = $MIOLO;
        $this->module = $module;
        $this->args = $args;

        //tratamento de variaveis, tenta achar padrões caso não tenham sido passados
        $controlNumber = $controlNumber ? $controlNumber : MIOLO::_REQUEST('_id');
        $controlNumber = $controlNumber ? $controlNumber : MIOLO::_REQUEST('controlNumber');
        $searchFormat = $searchFormat ? $searchFormat : MIOLO::_REQUEST('searchFormatS'); // tenta pegar o com S, dos detalhes
        $searchFormat = $searchFormat ? $searchFormat : MIOLO::_REQUEST('searchFormat');  // se não pega sem S , da pesquisa

        if ( ($this->isVerifyUser) || ($this->isMyLibrary) || ( MIOLO::_REQUEST('action') == 'main:myLibrary:myReserves' ) )
        {
            $searchFormat = SIMPLE_SEARCH_SEARCH_FORMAT_ID;
            //FIXME Foi feito assim, pois perdia o controlNumber do filho
            if ( (MIOLO::_REQUEST('_id')) && (MIOLO::_REQUEST('action') == 'main:myLibrary:myReserves') )
            {
                $controlNumber = MIOLO::_REQUEST('_id');
            }
        }

        $this->searchFormatId = $searchFormat;
        $this->controlNumber = $controlNumber;

        //FIXME Com a possibilidade de montar só as tabs necessárias, os includes de business
        //e de imagens devem ser feitos na aba a fim de evitar processamento desnecessário
        //business
        $this->busAuthenticate = $MIOLO->getBusiness($module, 'BusAuthenticate');
        $this->busDictionary = $MIOLO->getBusiness($module, 'BusDictionary');
        $this->busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $this->busExemplaryStatus = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
        $this->busMaterialControl = $MIOLO->getBusiness($module, 'BusMaterialControl');
        $this->busMaterial = $MIOLO->getBusiness($module, 'BusMaterial');
        $this->busMaterialType = $MIOLO->getBusiness($module, 'BusMaterialType');
        $this->busGenericSearch = $MIOLO->getBusiness($module, 'BusGenericSearch2');
        $this->busLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $this->busLoan = $MIOLO->getBusiness($module, 'BusLoan');
        $this->busLoanType = $MIOLO->getBusiness($module, 'BusLoanType');
        $this->busPerson = $MIOLO->getBusiness($module, 'BusPerson');
        $this->busReserve = $MIOLO->getBusiness($module, 'BusReserve');
        $this->busReserveStatus = $MIOLO->getBusiness($module, 'BusReserveStatus');
        $this->busReserveType = $MIOLO->getBusiness($module, 'BusReserveType');
        $this->busSearchableField = $MIOLO->getBusiness($module, 'BusSearchableField');
        $this->busSearchFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');
        $this->busOperatorLibraryUnit = $MIOLO->getBusiness($module, 'BusOperatorLibraryUnit');

        //imagens

        $this->imageFavoritesUrl = GUtil::getImageTheme('favorites-16x16.png');
        $this->imageReportUrl = GUtil::getImageTheme('report-16x16.png');
        $this->imageMailUrl = GUtil::getImageTheme('email-16x16.png');
        $this->imageChangeMaterial = GUtil::getImageTheme('changeMaterial-16x16.png');
        $this->imageDuplicateMaterial = GUtil::getImageTheme('duplicateMaterial-16x16.png');
        $this->imageAddChield = GUtil::getImageTheme('addChild-16x16.png');
        $this->imageExit = GUtil::getImageTheme('exit-16x16.png');
        $this->imageReserve = GUtil::getImageTheme('reserve-16x16.png');
        $this->imageGnuteca = GUtil::getImageTheme('gnuteca3-16x16.png');

        //TabControl que engloba todos componentes
        $tabControl = new GTabControl('tabDetail');
        $MIOLO = MIOLO::getInstance();
        $fields[] = $tabControl;

        //pega todos dados do material
        $this->material = $this->busMaterialControl->getMaterialControl($this->controlNumber);
        $this->controlNumberFather = $this->material->controlNumberFather;
        $this->type = $this->busMaterialControl->getTypeOfMaterial($this->controlNumber);

        if ( $this->type )
        {
            //aqui os selects estão com else, então só executa 1
            if ( $this->type == BusinessGnuteca3BusMaterialControl::TYPE_BOOK )
            {
                $this->isBook = true;
                $title = _M('Artigos', $this->module);
            }
            else if ( $this->type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION )
            {
                $this->isCollection = true;
                $title = _M('Fascículos', $this->module);
            }
            else if ( $this->type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION_FASCICLE )
            {
                $this->isCollectionFascicle = true;
                $title = _M('Artigos', $this->module);
            }
            else if ( $this->type == BusinessGnuteca3BusMaterialControl::TYPE_BOOK_ARTICLE )
            {
                $this->isBookArticle = true;
            }
            else if ( $this->type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION_ARTICLE )
            {
                $this->isColletionArticle = true;
            }
        }

        //se showtabs for true, que é o padrão, transforma em um array com todas abas
        if ( is_bool($showTabs) && $showTabs )
        {
            $showTabs = array( );
            $showTabs[] = 'main'; //aba principal com formato de pesquisa
            $showTabs[] = 'exemplary'; //listagem de exemplares
            $showTabs[] = 'googleBook'; //visualizador de informações do google, ativado via preferência
            $showTabs[] = 'googleViewer'; //leitor de livros do google, ativado via preferência
            $showTabs[] = 'children'; //filhos/fasciculos
            $showTabs[] = 'reserve'; //somente com permissão, logado como admin
            $showTabs[] = 'loan'; //somente com permissão, logado como admin
            $showTabs[] = 'cover'; //capa do material em zoom
            $showTabs[] = 'supplier'; //somente para coleção e logado como admin
            $showTabs[] = 'kardex'; //somente para coleção e logado como admin
            $showTabs[] = 'evaluation'; //avaliação de materiais, ativado via preferência
        }
        else if ( is_string($showTabs) )
        {
            //se passar uma string transforma em array
            $showTabs = array( $showTabs );
        }

        $this->showTabs = $showTabs;

        if ( is_array($showTabs) )
        {
            //sempre mostra a tab principal
            if ( in_array('main', $showTabs) )
            {
                $tabControl->addTab('tabMain', _M('Material', $this->module), $this->getMainTabFields());
            }

            $extraTagList = null;          //de onde vem isso??
            //só adiciona a tab se tive exemplares , essa função seta o $this->tabExemplaryTitle
            if ( in_array('exemplary', $showTabs) && $tabExemplaryControls = $this->getExemplaryTable($controlNumber, $extraTagList, $filter) )
            {
                $tabControl->addTab('tabExemplary', $this->tabExemplaryTitle, array( $tabExemplaryControls ));
            }

            if ( GB_INTEGRATION == DB_TRUE && in_array('googleBook', $showTabs) )
            {
                $busGoogleBook = $this->MIOLO->getBusiness($this->module, "BusGoogleBook");

                try
                {
                    $this->googleBook = $busGoogleBook->getGoogleBookByControlNumber($this->controlNumber);

                    if ( $this->googleBook )
                    {
                        if ( in_array('googleBook', $showTabs) && $gFields = $this->getGoogleBookFields() )
                        {
                            $tabControl->addTab('googleBook', _M('Google book', $this->module), $gFields);
                        }

                        //só libera visualização do livro caso ele esteja liberado digitalmente
                        if ( $this->googleBook->gbs_viewability == DB_TRUE || $this->googleBook->gbs_viewability == 'p' )
                        {
                            if ( in_array('googleViewer', $showTabs) && $gViewer = $this->getGoogleBookViewer() )
                            {
                                $tabControl->addTab('tabGoogleViewer', _M('Google viewer', $this->module), $gViewer);
                            }
                        }
                    }
                }
                catch ( Exception $e )
                {
                    // caso tenha erro de conexão informa mensagem dentro da aba, para ficar não obstrutivo
                    // isso acontece quando não existe conexão com internet
                    $eFields[] = new MDiv('', $e->getMessage());
                    $tabControl->addTab('googleBook', _M('Google book', $this->module), $eFields);
                }
            }

            if ( in_array('reserve', $showTabs) && $reserveFields = $this->getReserveFields() )
            {
                $tabControl->addTab('tabReserve', _M('Reserva', $this->module), $reserveFields);
            }

            if ( in_array('loan', $showTabs) && $loanFields = $this->getLoanFields() )
            {
                $tabControl->addTab('tabLoan', _M('Empréstimo', $this->module), $loanFields);
            }

            if ( in_array('cover', $showTabs) && $coverFields = $this->getCoverFields() )
            {
                $tabControl->addTab('tabCover', _M('Capa', $this->module), $coverFields);
            }

            if ( $this->type == BusinessGnuteca3BusMaterialControl::TYPE_COLLECTION && GPerms::checkAccess('gtcKardexControl', 'search', false) )
            {
                if ( in_array('supplier', $showTabs) && $supplierFields = $this->getSupplierFields() )
                {
                    $tabControl->addTab('tabSupplier', _M('Fornecedor', $this->module), $supplierFields);
                }

                if ( in_array('kardex', $showTabs) && $kardexFields = $this->getKardexFields() )
                {
                    $tabControl->addTab('tabKardexInfo', _M('Kardex', $this->module), $kardexFields);
                }
            }

            if ( MIOLO::_REQUEST('action') != 'main:catalogue:kardexControl' )
            {
                if ( in_array('children', $showTabs) && $divChildren = $this->getChildrenFields() )
                {
                    $tabControl->addTab('tabChildren', $title, array( $divChildren ));
                }
            }

            if ( MUtil::getBooleanValue(SIMPLE_SEARCH_EVALUATION) && in_array('evaluation', $showTabs) && $evalFields = $this->getEvalFields() )
            {
                $tabControl->addTab('tabEvaluation', _M('Avaliação', $this->module), $evalFields);
            }
        }

        // se tiver selecionado para mostrar somente uma tab e somente tiver uma, retira a tbv e mostra como uma div normal,
        // com  o mesmo id, para funcionar ajax e js
        if ( count($showTabs) == 1 && count($tabControl->getTab()) == 1 )
        {
            $tabs = $tabControl->getTab();
            $arrayKeys = array_keys($tabs);
            $tabName = $arrayKeys[0];
            $tabControl = new MDiv($tabName, $tabs[$tabName]->controls->getControls());
        }

        //pega os botões inferiores, caso seja só a cover não mostra os botões
        if ( $showTabs[0] == 'cover' && count($showTabs) == 1 )
        {
            $extraFields = null;
        }
        else
        {
            $hideCloseButton = $args->hideCloseButton;
            $extraFields = $this->getBottomButtons($hideCloseButton);
        }

        $fields = array_merge(array( $tabControl ), is_array($extraFields) ? $extraFields : array( ) );

        $gotoTab = $gotoTab ? $gotoTab : MIOLO::_REQUEST('gotoTab');

        //se tiver searchFormatS é porque a pessoas clicou em um botão de formato de pesquisa, NESTE CASO não é pra abrir outra tab
        if ( MIOLO::_REQUEST('searchFormatS') )
        {
            $gotoTab = null;
        }

        //troca para aba selecionada
        if ( $gotoTab )
        {
            $name = $tabControl->name;
            $this->page->onLoad("gnuteca.changeTab('{$gotoTab}', '{$name}')");
        }

        $this->setInner($fields);
    }

    /**
     * Obtem os campos de avaliação.
     *
     * @return MDiv
     */
    public function getEvalFields()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/controls/GStar.class.php', 'gnuteca3');

        if ( BusinessGnuteca3BusAuthenticate::checkAcces() ) //caso logado
        {
            $postFields[] = new MButton('btnLogin', _M('Clique aqui para efetuar uma avaliação', 'gnuteca3'), "gnuteca.changeDisplay('evalFields');", GUtil::getImageTheme('save-16x16.png'));

            $field[] = new MDiv('', _M('Abaixo é possível postar um comentário, clique nas estrelas para definir uma nota para este material.', 'gnuteca3'));
            $field[] = new MMultiLineField('comment', '', '', 10, 3, 90);

            $field[] = new GStar('evaluation');

            if ( defined('SOCIAL_INTEGRATION') && MUtil::getBooleanValue(SOCIAL_INTEGRATION) == DB_TRUE )
            {
                $socialContent .= '<span style="float:left;">Facebook</span> <input type="checkbox" id="checkFacebook" name="checkFacebook" value="checkFacebook">
                    <span style="float:left;">Twitter</span> <input type="checkbox" id="checkTwitter" name="checkTwitter" value="checkTwitter">';

                $field[] = $socialContent = new MDiv('', $socialContent);
                $socialContent->addStyle('height', '20px');
            }

            $field[] = new MButton('saveEvaluation', _M('Postar', 'gnuteca3'), GUtil::getAjax('saveEvaluation', array( 'controlNumber' => $this->controlNumber )), GUtil::getImageTheme('save-16x16.png'));

            $field = new MSpan('evalFields', $field);
            $field->addStyle('display', 'none');
            $field->addStyle('width', '100%');

            $postFields[] = $field;
        }
        else
        {
            $args->controlNumber = $this->controlNumber;
            $args->gotoTab = 'tabEvaluation';
            if ( (MIOLO::_REQUEST('action') != 'main:materialMovement' ) )
            {
                $postFields[] = new MButton('btnLogin', _M('Clique aqui para efetuar uma avaliação', 'gnuteca3'), GUtil::getAjax('makeLogin', $args), GUtil::getImageTheme('save-16x16.png'));
            }
        }

        $fields[] = new MDiv('postFields', $postFields);
        $comments = new MDiv('comments', $this->getEvaluationList($this->controlNumber));
        $comments->addStyle('width', '100%');

        $fields[] = $comments;

        return $fields;
    }

    /**
     * Função que faz o login dentro da aba de avaliação.
     *
     */
    public function makeLogin()
    {
        $MIOLO = MIOLO::getInstance();
        $args->loginType = LOGIN_TYPE_USER_AJAX;
        $frmLogin = $MIOLO->getUI()->getForm('gnuteca3', 'FrmLogin', $args);

        if ( !$frmLogin->isAuthenticated() )
        {
            GForm::injectContent($frmLogin->getLoginFields(), false, 'Login', '550px');
        }
        else
        {
            $args->gotoTab = MIOLO::_REQUEST('gotoTab');
            GMaterialDetail::openMaterialDetail(MIOLO::_REQUEST('controlNumber'), $args);
        }
    }

    /**
     * Obtem os campos da avaliação, com todas as avaliaçãoes deste material
     *
     * @param integer $controlNumber
     * @return MDiv
     */
    public function getEvaluationList($controlNumber)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/controls/GStar.class.php', 'gnuteca3');
        $busEval = $MIOLO->getBusiness('gnuteca3', 'BusMaterialEvaluation');
        $busEval = new BusinessGnuteca3BusMaterialEvaluation();

        $average = $busEval->getAverage($controlNumber);
        $average = $average[0];

        if ( $average[0] )
        {
            $content[] = $span = new MSpan('averageCount', _M('O material possui @1 avaliações com média final ', 'gnuteca3', $average[0]));
            $content[] = $gstar = new GStar('average', $average[1], true);
            $comments[] = new MDiv('total', $content);
        }

        $evals = $busEval->listEvalutionWithComment($controlNumber, true);

        if ( is_array($evals) )
        {
            foreach ( $evals as $key => $evaluation )
            {
                $controls = null;
                $photo = GUtil::getPersonPhoto($evaluation->personId, array( 'height' => '20px' ));
                $controls[] = new MDiv('', '<strong>' . $photo . ' - ' . $evaluation->name . ' - ' . GDate::construct($evaluation->date)->getDate(GDate::MASK_TIMESTAMP_USER) . '</strong>');
                $controls[] = new MDiv('', $evaluation->comment);

                $a[0] = new MDiv('eval' . $evaluation->materialEvaluationId, $controls);
                $a[1] = null;

                if ( $evaluation->evaluation > 0 )
                {
                    $a[1] = new GStar('evalStar' . $evaluation->materialEvaluationId, $evaluation->evaluation, true);
                }

                $tableFields[] = $a;
            }

            $comments[] = new MTableRaw('', $tableFields, array( _M('Outros comentários', 'gnuteca3'), _M('Nota', 'gnuteca3') ), 'evalTable');
        }
        else
        {
            if ( !$average )
            {
                $comments[] = new MDiv('', _M('Nenhuma avaliação para este material. Seja o primeiro a avaliá-lo.'));
            }
            else
            {
                $comments[] = new MDiv('');
            }
        }

        return $comments;
    }

    /**
     * Função que salva a avaliação atual.
     */
    public function saveEvaluation()
    {
        $MIOLO = MIOLO::getInstance();
        $args = (object) $_REQUEST;

        $busEval = $MIOLO->getBusiness('gnuteca3', 'BusMaterialEvaluation');

        //obtém comentários anteriores
        $oldComments = $busEval->listEvalutionWithComment($args->controlNumber, true);

        $busEval = new BusinessGnuteca3BusMaterialEvaluation();

        $busEval->controlNumber = $args->controlNumber;
        $busEval->comment = $args->comment;
        $busEval->evaluation = $args->evaluation;
        $busEval->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
        $busEval->personId = BusinessGnuteca3BusAuthenticate::getUserCode();

        $busMyLibrary = $MIOLO->getBusiness('gnuteca3', 'BusMyLibrary');

        $data = array( );
        $arrayPerson = array( );

        if ( is_array($oldComments) )
        {
            foreach ( $oldComments as $key => $evaluation )
            {
                //só adiciona mensagem para outras pessoas, pois posso ter comentário repetido
                if ( ( $evaluation->personId != $busEval->personId ) && (!in_array($evaluation->personId, $arrayPerson)) )
                {
                    $message = new stdClass();
                    $message->personId = $evaluation->personId;
                    $message->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
                    $message->message = stripslashes($busMyLibrary->getCommentMaterialMessage($busEval->personId, $evaluation->controlNumber));
                    $message->visible = DB_TRUE;
                    $data[] = $message;
                    $arrayPerson[] = $evaluation->personId; //controla para não repetir a mensagem para as pessoas
                }
            }
        }

        $ok = $busEval->insertMaterialEvaluation();

        //insere registros na minha biblioteca
        if ( $ok )
        {
            foreach ( $data as $i => $value )
            {
                $busMyLibrary->myLibraryId = null;
                $busMyLibrary->setData($value);
                $busMyLibrary->insertMyLibrary();
            }
        }

        $fields[] = MMessage::getStaticMessage('successMessage', _M('Avaliação efetuada com sucesso!', 'gnuteca3'), MMessage::TYPE_INFORMATION);
        $fields[] = GMaterialDetail::getEvaluationList($args->controlNumber);

        GForm::jsSetValue('comment', '');
        GStar::jsSetValue('evaluation', '');

        if ( defined('SOCIAL_INTEGRATION') && MUtil::getBooleanValue(SOCIAL_INTEGRATION) == DB_TRUE && $args->comment )
        {
            $href = $MIOLO->getConf('home.url') . "/index.php?controlNumber=" . $args->controlNumber;
            $href = urlencode($href);
            $busMaterial = $MIOLO->getBusiness('gnuteca3', 'BusMaterial');
            $busMaterial = new BusinessGnuteca3BusMaterial();
            $title = $busMaterial->getMaterialTitle($args->controlNumber);
            $author = $busMaterial->getMaterialAuthor($args->controlNumber);

            if ( $args->checkTwitter )
            {
                $js = "window.open('https://twitter.com/share?url=$href&text={$title} - {$author} - {$args->comment} - ');";
            }

            if ( $args->checkFacebook )
            {
                $js .= "window.open('https://www.facebook.com/sharer.php?u={$href}&t={$title} - {$author} - {$args->comment}');";
            }

            $MIOLO->page->onLoad($js);
        }

        $MIOLO->ajax->setResponse($fields, 'comments');
    }

    /**
     * Retorna os campos do fornecedor
     *
     * @return array de objetos/campos do miolo
     */
    public function getSupplierFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'gnuteca3';
        $businessMaterial = $MIOLO->getBusiness($module, 'BusMaterial');
        $businessSupplier = $MIOLO->getBusiness($module, 'BusSupplier');
        $businessMaterial->clean();

        $fornecedor = $businessMaterial->getContentTag($this->controlNumber, MARC_SUPPLIER_TAG);

        if ( !$fornecedor )
        {
            //return array( new MDiv('', _M("Não tem fornecedor", $module) ) );
            return null;
        }

        $supplierInfos = $businessSupplier->getSupplierCompleteData($fornecedor);

        if ( !$supplierInfos )
        {
            //return array( new MDiv('', _M("Sem informação no fornecedor.", $module)) );
            return null;
        }

        $tabControl = new GTabControl('supplierTypeAndLocation');

        $tabControl->addTab('tabBuy', _M('Compra', $module), $this->getSupplierTabFields("C", $supplierInfos));
        $tabControl->addTab('tabInterchange', _M('Permuta', $module), $this->getSupplierTabFields("P", $supplierInfos));
        $tabControl->addTab('tabDonation', _M('Doação', $module), $this->getSupplierTabFields("D", $supplierInfos));

        return array( $tabControl );
    }

    function getKardexFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'gnuteca3';
        $businessMaterial = $MIOLO->getBusiness($module, 'BusMaterial');
        $businessLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $businessTag = $MIOLO->getBusiness($module, 'BusTag');
        $businessKardexControl = $MIOLO->getBusiness($module, 'BusKardexControl');
        $businessMaterial->clean();
        $kardex = $businessMaterial->getContent($this->controlNumber, MARC_KARDEX_FIELD, null, null, true, true, "line, subfieldid");

        $businessFile = $MIOLO->getBusiness($module, 'BusFile');
        $businessFile->folder = 'materialType';

        if ( !$kardex )
        {
            return null;
        }

        $units = null;

        //ceomça a organizar por unidades
        foreach ( $kardex as $content )
        {
            if ( $content->subfieldid == MARC_KARDEX_LIBRARY_UNIT_ID_SUBFIELD )
            {
                $units[$content->content] = $content->line;
            }
        }

        $kardexUnits = null;

        foreach ( $units as $unit => $line )
        {
            foreach ( $kardex as $content )
            {
                if ( $line == $content->line )
                {
                    $kardexUnits[$unit][$content->subfieldid] = $content->content;
                }
            }
        }

        //ordena por unidade de biblioteca
        ksort($kardexUnits, SORT_NUMERIC);

        $tabKardex = new GTabControl('tabKardexDetail');

        //Collection infos
        $referenciados = null;

        $r = $businessKardexControl->getReferenced($this->controlNumber);

        //obtém exemplares
        $exemplarGroup = $this->busExemplaryControl->getExemplarysOfMaterialControlNumber($r);


        // Obtém as imagens dos tipos de material.
        $materialType = $this->busMaterialType->listMaterialType();
        $materialTypes = array( );

        if ( is_array($materialType) )
        {
            foreach ( $materialType as $type )
            {
                $businessFile->fileName = $type[0] . '.';
                $file = $businessFile->searchFile(true);

                if ( $file[0] )
                {
                    $image = new MImage(null, null, $file[0]->mioloLink);
                    $materialTypes[$type[0]] .= $image->generate();
                }
            }
        }

        if ( $r )
        {
            $images = '';

            foreach ( $r as $c )
            {
                $_controlNumber = $c[0]; //número de controle do fascículo

                $exemplarys = $exemplarGroup[$_controlNumber];

                if ( is_array($exemplarys) )
                {
                    foreach ( $exemplarys as $exem )
                    {
                        $controlNumberObj = new stdClass();
                        $controlNumberObj->image = $materialTypes[$exem->materialPhysicalTypeId];
                        $controlNumberObj->controlNumber = $_controlNumber;

                        $referenciados[$exem->libraryUnitId][$_controlNumber . '-' . $exem->materialPhysicalTypeId] = $controlNumberObj;
                    }
                }
            }
        }

        //para cada unidade monta uma tabela
        foreach ( $kardexUnits as $i => $content )
        {
            $table = new MTableRaw(_M("Informações do Kardex", $this->module), null, null, "kardexControlTableRawT_$i");
            $table->addStyle('width', '820px');
            $table->setAlternate(true);
            $date = new GDate();

            foreach ( $content as $subF => $conteudo )
            {
                $tagName = $businessTag->getTagName(MARC_KARDEX_FIELD, $subF);

                if ( ereg(MARC_KARDEX_FIELD . ".$subF", CATALOGUE_DATE_FIELDS) )
                {
                    $date->setDate($conteudo);
                    $conteudo = $date->getDate(GDate::MASK_DATE_USER);
                }
                else
                {
                    $value = $businessMaterial->relationOfFieldsWithTable(MARC_KARDEX_FIELD . ".$subF", $conteudo, true);
                    $conteudo = $value ? $value : $conteudo;
                }

                $table->array[] = array( $tagName, $conteudo );
            }

            //Collection info
            $colV = $colN = 0;
            $detalhamento = null;
            $detalhamentoOrder = null;

            //dados por biblioteca
            foreach ( $referenciados[$i] as $line => $controlNumberObj )
            {
                $controlNumber = $controlNumberObj->controlNumber;
                $image = $controlNumberObj->image;

                list($f, $s) = explode(".", MARC_PERIODIC_INFORMATIONS);

                if ( ereg("(v\. [^,]*,)?( ?no?\. [^,]*,)?( ?[^ ]* )?([0-9]{4})", $businessMaterial->getContent($controlNumber, $f, $s), $regs) )
                {
                    $fasciculo[] = $regs;

                    $ano = (int) trim($regs[4]); //obtém ano
                    $volume = trim(str_replace(',', '', str_replace('v.', '', $regs[1]))); //obtém volume
                    //se não tiver volume, coloca um "-"
                    $volume = $volume ? $volume : "-";
                    $numero = trim(str_replace(',', '', str_replace('n.', '', str_replace('no.', '', $regs[2])))); //obtém o número

                    $label = $numero ? $numero : _M('s/n', 'gnuteca3'); //rótulo a ser mostrado caso não tenha número
                    $index = $numero ? $numero : rand(); //indexador caso não tenha número (rand sempre retorna menor que 0

                    $link = new MLink('', $image . ' ' . $label, "javascript:" . GUtil::getAjax("openMaterialDetail", $controlNumber));
                    $detalhamento[$ano][$volume][$index][] = $link->generate();

                    //obtém número máximo de colunas para coluna de volumes
                    $colVolume = sizeof($detalhamento[$ano]);
                    if ( $colVolume > $colV )
                    {
                        $colV = $colVolume;
                    }

                    $colNumero[$ano]++;
                }
            }

            //obtém o número de colunas máximo
            $colN = 0;
            foreach ( $colNumero as $k => $col )
            {
                if ( $col > $colN )
                {
                    $colN = $col;
                }
            }

            $collectionTable = new MTableRaw(_M("Informações da coleção", $this->module), null, array( _M('Ano', $this->module), _M('Volume', $this->module), _M('Número', $this->module) ), "ColectionInfosTableRaw");
            $collectionTable->setAlternate(true);

            $collectionArray = null;
            $linha = 0;
            //Ordena por ano
            krsort($detalhamento, SORT_REGULAR);
            foreach ( $detalhamento as $ano => $n1 )
            {
                $collectionArray[$linha][0] = $ano; //ano
                $collectionTable->setCellAttribute($linha, 0, 'class', 'cellKardexInfoMainColumns'); //seta classe na primeira coluna
                //seta os volumes
                $volumes = $volumesOrdered = array_keys($n1); //obtém volumes

                $volumesOrdered = array_filter($volumesOrdered);
                //ordena volumes de fascículo
                sort($volumesOrdered, SORT_REGULAR);

                for ( $k = 1; $k <= $colV; $k++ )
                {
                    $collectionArray[$linha][$k] = $volumesOrdered[$k - 1] ? $volumesOrdered[$k - 1] : '';

                    if ( $k == $colV )
                    {
                        $class = 'cellKardexInfoMainColumns';
                    }
                    else
                    {
                        $class = 'cellKardexInfoColumns';
                    }

                    $collectionTable->setCellAttribute($linha, $k, 'class', $class); //seta classe na última celula do volume
                }

                //obtém os dados e ordena os números
                $new = array( );
                foreach ( $volumesOrdered as $l => $volume )
                {
                    //corre a quantidade de números
                    $count = 0;

                    foreach ( $n1[$volume] as $key => $val )
                    {
                        $array[] = $key;
                        $max = max($array) + 1;
                    }

                    //ordena números pela chave
                    // Ajusta as chaves previamente para ordenacao ficar correta
                    foreach ( $n1[$volume] as $key => $val )
                    {
                        // Testa se padrao de valor é um numero
                        list($first, $second) = GUtil::multiexplode(array( ' ', '-' ), $key);
                        if ( !is_numeric(trim($first)) )
                        {
                            unset($n1[$volume][$key]);
                            $n1[$volume][$max . $key] = $val;
                        }
                        elseif ( strlen($second) && !is_numeric(trim($second)) && $second != ' ' )
                        {
                            unset($n1[$volume][$key]);
                            $n1[$volume][$first . '.5'] = $val;
                        }
                    }

                    ksort($n1[$volume], SORT_NUMERIC);

                    $sum = ($colN + $colV + 1);
                    $keys = array_keys($n1[$volume]); //pega as chaves

                    foreach ( $n1[$volume] as $pk => $fasc )
                    {
                        foreach ( $fasc as $f )
                        {
                            $new[] = $f;
                        }
                    }
                }

                //prepara os dados para setData e adiciona estilo em algumas células
                $cont = 0;
                for ( $x = $colV + 1; $x < $sum; $x++ )
                {
                    if ( $x < $sum )
                    {
                        $collectionTable->setCellAttribute($linha + 2, $x, 'class', 'cellKardexInfoColumns'); //seta classe na última celula do número
                    }
                    $collectionArray[$linha][$x] = $new[$cont];
                    $cont++;
                }

                $linha++;
            }

            //seta os dados
            $collectionTable->setData($collectionArray);

            //seta colspan do volume
            $collectionTable->setHeadAttribute(1, 'colspan', $colV);
            //seta colspan do número
            $collectionTable->setHeadAttribute(2, 'colspan', $colN);

            $divX = new MDiv("noname", $collectionTable);

            $divTab = new MDiv("kardexControlTableRaw_$i", array( $table, $collectionTable ));
            $divTab->addAttribute("class", "kardexControlTableRaw");

            //adiciona a aba de unidade
            $tabKardex->addTab("tabKardexInfo_{$i}", $businessLibraryUnit->getLibraryName($i), array( $divTab ));

            $table = null;
        }

        return array( new MDiv('tableKardesInfos', new MDiv("divKardex", $tabKardex)) );
    }

    /**
     * Retorna os campos de uma tab de fornecedor
     *
     * @param char $type
     * @param stdClass $supplierInfos
     * @return array de campos
     */
    public function getSupplierTabFields($type, $supplierInfos)
    {
        $module = 'gnuteca3';
        $table = new MTableRaw(_M("Informações do fornecedor", $this->module), null, null, 'tableSupplierInfos');
        $table->setAlternate(true);
        $date = new GDate();

        foreach ( $supplierInfos->TypeLocation as $conteudo )
        {
            $typeMinuscula = strtolower($type);

            if ( $conteudo->type == $typeMinuscula )
            {
                $date->setDate($conteudo->date);
                $table->array[] = array( _M("Nome", $module), $supplierInfos->supplier->name );
                $table->array[] = array( _M("Nome da companhia", $module), $conteudo->companyName );
                $table->array[] = array( _M("CNPJ", $module), $conteudo->cnpj );
                $table->array[] = array( _M("Local", $module), $conteudo->location );
                $table->array[] = array( _M("Bairro", $module), $conteudo->neighborhood );
                $table->array[] = array( _M("Cidade", $module), $conteudo->city );
                $table->array[] = array( _M("CEP", $module), $conteudo->zipCode );
                $table->array[] = array( _M("Telefone", $module), $conteudo->phone );
                $table->array[] = array( _M("Telefone alternativo", $module), $conteudo->alternativePhone );
                $table->array[] = array( _M("Fax", $module), $conteudo->fax );
                $table->array[] = array( _M("E-mail", $module), $conteudo->email );
                $table->array[] = array( _M("E-mail alternativo", $module), $conteudo->alternativeEmail );
                $table->array[] = array( _M("Contato", $module), $conteudo->contact );
                $table->array[] = array( _M("Site", $module), $conteudo->site );
                $table->array[] = array( _M("Observação", $module), $conteudo->observation );
                $table->array[] = array( _M("Depósito bancário", $module), $conteudo->bankDeposit );
                $table->array[] = array( _M("Data", $module), $date->getDate(GDate::MASK_DATE_USER) );

                return array( $table );
            }
        }
    }

    /**
     * Mostra a capa do livro no tamanho original
     *
     * @return MImage
     */
    public function getCoverFields()
    {
        $busFile = $this->manager->getBusiness('gnuteca3', 'BusFile');
        $busFile = new BusinessGnuteca3BusFile();
        $busFile->folder = 'cover';
        $busFile->fileName = $this->controlNumber . '.';
        $cover = $busFile->searchFile(true);
        $cover = $cover[0];

        if ( $cover )
        {
            //obtem tamanho da imagem
            $size = getimagesize($cover->absolute);
            $height = $size[1];

            //caso a imagem tiver altura maior que 200px, limita a 200px
            if ( $height > 220 )
            {
                $extraParam = '&height=220px';
            }

            $link = '<a href="' . $cover->mioloLink . '" target="_blank" ><img border="0" title="" alt="" id="imgCover" src="' . $cover->mioloLink . $extraParam . '"></a>';
            $fields[] = new MDiv('', $link);
        }

        return $fields;
    }

    public function getGoogleBookViewer()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/controls/GoogleBookViewer.class.php', 'gnuteca3');

        $fields[] = new GoogleBookViewer('viewCanvas', 'isbn:' . $this->googleBook->isbn, $width, '270');

        return $fields;
    }

    public function getGoogleBookFields()
    {
        $data = (array) $this->googleBook;

        unset($data['thumbnail']);
        unset($data['controlNumber']);

        foreach ( $data as $line => $info )
        {
            $information = '';
            $line = str_replace('gbs_', '', $line); //retira dado desnecessário
            $line = str_replace('dc_', '', $line);

            //utilizado para compatibilidade com o PHP 5.2
            if ( !$info instanceof stdClass )
            {
                $information = "$info"; //converte pra string
            }

            if ( $information ) //se sobrou alguma string
            {
                //trata as informações para mostrar de forma amigável ao usuário

                if ( $line == 'embeddability' )
                {
                    $information = str_replace('t', _M('Sim', 'gnuteca3'), $information);
                    $information = str_replace('f', _M('Não', 'gnuteca3'), $information);
                }

                if ( $line == 'viewability' )
                {
                    $information = str_replace('t', _M('Sim', 'gnuteca3'), $information);
                    $information = str_replace('f', _M('Não', 'gnuteca3'), $information);
                    $information = str_replace('p', _M('Parcial', 'gnuteca3'), $information);
                }

                if ( $line == 'updated' )
                {
                    $information = new GDate(substr($information, 0, 10));
                    $information = $information->getDate(GDate::MASK_DATE_USER);
                }

                if ( $line != 'link' )
                {
                    $line = BusinessGnuteca3BusGoogleBook::translate($line);
                    $information = BusinessGnuteca3BusGoogleBook::translate($information);
                    $line = str_replace('id', _M('Código', 'gnuteca3'), $line);
                    $line = str_replace('Language', _M('Língua', 'gnuteca3'), $line);
                    $line = str_replace('contentVersion', _M('Versão do conteúdo', 'gnuteca3'), $line);
                }

                //linha mágica que retira os número e padroniza a strings
                $line = str_replace(array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ), '', trim(ucfirst($line))); //primeira letra em maiscula
                //caso já exista o dado, concatena
                if ( $tableData[$line] )
                {
                    $information = $tableData[$line][1] . '; ' . $information; //busca o dado que já existia e coloca no dado atual
                    unset($tableData[$line]);
                }

                $tableData[] = array( '<b>' . $line . '</b>', $information );
            }
        }

        if ( $this->googleBook->thumbnail )
        {
            $tableData[] = array( '<b>' . _M('Capa', $this->module) . '</b>', "<img src='{$this->googleBook->thumbnail}'></img>" );
        }

        $fields[] = $table = new MTableRaw(null, $tableData, null);
        $table->setAlternate(true);

        return $fields;
    }

    /**
     * Monta relação de empréstimos
     *
     * @return array de objetos do miolo
     */
    public function getLoanFields()
    {
        $exemplarys = is_array($this->exemplarys) ? $this->exemplarys : array( $this->exemplarys );

        //caso esteja usando só esta tab e não tenha vindo os exemplares, busca exemplares
        if ( !$exemplarys )
        {
            $exemplarys = GMaterialDetail::getExemplaryList($this->controlNumber);//, $extraTags );
        }

        //só mostra de estiver logado
        if ( !GOperator::isLogged() )
        {
            return null;
        }

        $tbData = array( );

        foreach ( $exemplarys as $ex )
        {
            $itemNumber[] = $ex->itemNumber;
        }

        // pega empréstimos abertos
        $loans = $this->busLoan->getLoanOpen($itemNumber);

        //passa pelos exemplares
        if ( !is_array($loans) )
        {
            return null;
        }

        foreach ( $loans as $line => $loan )
        {
            $loanType = $this->busLoanType->getLoanType($loan->loanTypeId)->description;
            $itemNumber = $loan->itemNumber;

            $lineData = array( );

            if ( $this->isMaterialMovement )
            {
                $lineData[] = $itemNumber;
            }

            $lineData[] = $loan->loanId;
            $lineData[] = $loan->personId;
            $lineData[] = $loan->personName;
            $lineData[] = GDate::construct($loan->loanDate)->getDate(GDate::MASK_TIMESTAMP_USER);
            $lineData[] = GDate::construct($loan->returnForecastDate)->getDate(GDate::MASK_DATE_USER);
            $lineData[] = $loanType;
            $lineData[] = $loan->libraryName;

            $tbData[] = $lineData;
        }

        $cols = array( );

        //Se acessar pelo módulo Circulação de material
        if ( $this->isMaterialMovement )
        {
            $cols[] = _M('Número do exemplar', $this->module);
        }

        $cols[] = _M('Código do empréstimo', $this->module);
        $cols[] = _M('Código da pessoa', $this->module);
        $cols[] = _M('Nome da pessoa', $this->module);
        $cols[] = _M('Data do empréstimo', $this->module);
        $cols[] = _M('Data prevista da devolução', $this->module);
        $cols[] = _M('Tipo de empréstimo', $this->module);
        $cols[] = _M('Unidade de biblioteca', $this->module);

        //só adiciona tab caso precise
        if ( $tbData )
        {
            //esse id (tableLoan) é importante pois é usado fora da classe
            $tbLoan = new MTableRaw(null, $tbData, $cols, 'tableLoan');
            $tbLoan->setAlternate(true);
            $loanControls[] = $tbLoan;
        }

        return $loanControls;
    }

    /**
     * Função que destaca item em negrito, caso necessário.
     *
     * @param $bold boolean
     * @param $string
     * @return string
     */
    public function bold($bold, $string)
    {
        if ( $bold )
        {
            $string = '<b>' . $string . '</b>';
        }

        return $string;
    }

    public function getReserveFields()
    {
        // se não estiver logado não faz nada, só pode ser mostrado para usuários administrativos
        if ( !GOperator::isLogged() )
        {
            return null;
        }

        $reserveStatusId = array( ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED );
        $reserves = $this->busReserve->getReservesOfMaterial($this->controlNumber, $reserveStatusId, null, null, 'A.reserveStatusId DESC, A.reserveId, B.itemNumber ASC', $this->args->libraryUnitId);

        //Reserve tab
        $tbData = array( );

        if ( is_array($reserves) )
        {
            foreach ( $reserves as $line => $reserve )
            {
                //se reserva for atendida ou comunicada destaca o item
                if ( $reserve->reserveStatusId == ID_RESERVESTATUS_ANSWERED || $reserve->reserveStatusId == ID_RESERVESTATUS_REPORTED )
                {
                    $bold = true; //destaca item
                }
                else
                {
                    $bold = false; // não destaca item
                }

                $lineData = array( );

                if ( $this->isMaterialMovement )
                {
                    $lineData[] = $this->bold($bold, $reserve->itemNumber);
                }

                $reserve->isConfirmed = MUtil::getBooleanValue($reserve->isConfirmed); //transforma em TRUE/FALSE
                //posição do usuário na fila de reserva
                $position = $this->busReserve->getReservePosition($reserve->reserveId);

                if ( $position[0][0] == 0 )
                {
                    $lineData[] = $this->bold($bold, 'Aguardando retirada'); //Quando reserva atendida
                }
                else
                {
                    $lineData[] = $this->bold($bold, $position[0][0] . 'º'); //Quando reserva solicitada, retorna a posição
                }
                $lineData[] = $this->bold($bold, $reserve->reserveStatus);
                $lineData[] = $this->bold($bold, GDate::construct($reserve->requestedDate)->getDate(GDate::MASK_TIMESTAMP_USER));
                $lineData[] = GDate::construct($reserve->limitDate)->getDate(GDate::MASK_TIMESTAMP_USER);
                $lineData[] = $this->bold($bold, $reserve->reserveId);
                $lineData[] = $this->bold($bold, $reserve->personId);
                $lineData[] = $this->bold($bold, $reserve->personName);
                $lineData[] = $this->bold($bold, $reserve->reserveType);
                $lineData[] = $this->bold($bold, $reserve->libraryUnitName);

                /*
                  - Manter as linhas que trazem: isconfirmed = 't' and reservestatusid in (2, 3) - Essas linhas são sempre 1º da fila
                  - Eliminar as linhas que trazem: isconfirmed = 'f' and reservestatusid in (2, 3)
                  - Manter todas as linhas que trazem: reservestatusid = 1 - Essas linhas são 2º da fila em diante
                 */
                if ( ( ( $reserve->reserveStatusId == ID_RESERVESTATUS_ANSWERED || $reserve->reserveStatusId == ID_RESERVESTATUS_REPORTED ) && $reserve->isConfirmed)
                        || ( $reserve->reserveStatusId == ID_RESERVESTATUS_REQUESTED ) )
                {
                    $tbDataReserve[] = $lineData; //adiciona a lista
                }
            }
        }

        $tbDataReserve = GUtil::orderMatrizByPositionVector($tbDataReserve, 1);
        $cols = array( );

        // Se acessar pelo módulo Circulação de material
        if ( $this->isMaterialMovement )
        {
            $cols[] = _M('Número do exemplar', $this->module);
        }

        $cols[] = _M('Posição na fila', $this->module);
        $cols[] = _M('Estado', $this->module);
        $cols[] = _M('Data da solicitação', $this->module);
        $cols[] = _M('Data limite', $this->module);
        $cols[] = _M('Código da reserva', $this->module);
        $cols[] = _M('Código da pessoa', $this->module);
        $cols[] = _M('Nome da pessoa', $this->module);
        $cols[] = _M('Tipo de reserva', $this->module);
        $cols[] = _M('Unidade de biblioteca', $this->module);

        //só adiciona tableRaw caso precise
        if ( $tbDataReserve )
        {
            $table = new MTableRaw(null, $tbDataReserve, $cols);
            $table->setAlternate(true);
            return array( $table );
        }

        return null;
    }

    public function getChildrenFields()
    {
        $meses = array('jan' => 1, 'fev' => 2, 'mar' => 3, 'abr' => 4, 'maio' => 5, 'jun' => 6, 'jul' => 7, 'ago' => 8, 'set' => 9, 'out' => 10, 'nov' => 11, 'dez' => 12);
        
        //só tem filhos caso seja livro, coleção ou fasciculo da coleção
        if ( $this->isBook || $this->isCollection || $this->isCollectionFascicle )
        {
            $tableData = array( );

            //define formato de pesquisa de acordo com o formato
            if ( $this->isBook )
            {
                $searchFormat = SIMPLE_SEARCH_SEARCH_FORMAT_ID_DETAIL_ARTICLE;
            }
            else if ( ( $this->isCollection ) || ( $this->isCollectionFascicle ) )
            {
                $searchFormat = SIMPLE_SEARCH_SEARCH_FORMAT_ID_DETAIL_FASCICLE;
            }

            //pega os filhos de acordo com a unidade selecionada. Para mostrar a aba Artigos, a biblioteca deve ser null
            $libraryUnitId = $this->isCollectionFascicle ? null : MIOLO::_REQUEST('libraryUnitId');

            $childrens = $this->busMaterialControl->getChildren($this->controlNumber, $libraryUnitId);

            if ( $this->isCollection )
            {
                $_childrens = array();
                $_fim = array();
                $count = array();

                foreach ( $childrens as $child )
                {
                    $ano = substr($this->busMaterial->getContent($child, '949', '4'), 0, 4);
                    if ( !$ano || strlen($ano) == 0 )
                    {
                        $_info = $this->busSearchFormat->getFormatedString($child, $searchFormat, 'detail');
                        
                        preg_match('/[1-9][0-9][0-9][0-9]./', $_info, $matches, PREG_OFFSET_CAPTURE);
                        $ano = trim($matches[0][0], '.');
                        
                        if ( strlen($ano) == 0 )
                        {
                            preg_match('/[1-9][0-9][0-9][0-9]/', $_info, $matches, PREG_OFFSET_CAPTURE);
                            $ano = trim($matches[0][0], '.');
                            
                            // Obtém o mês do fascículo.
                            $parts = explode(',', $_info);
                            $parts = explode(' ', trim(end($parts)));
                            $mes = strtolower(str_replace('.', '', $parts[0]));
                            if ( !$meses[$mes] )
                            {
                                $mes = substr($mes, 0, strpos($mes, '/'));
                                $mes = $meses[$mes];
                            }
                            else
                            {
                                $mes = $meses[$mes];
                            }
                        }   

                        if ( strlen($ano) > 4 )
                        {
                            preg_match('/[1-9][0-9][0-9][0-9]/', $ano, $matches, PREG_OFFSET_CAPTURE);
                            $ano = trim($matches[0][0], '.');
                        }
                    }

                    $_info = $this->busSearchFormat->getFormatedString($child, $searchFormat, 'detail');
                    preg_match("/no?\. [0-9]{1,4}?[\-\/0-9]{0,4}?[ \w\W]{0,100}\,/", $_info, $matches, PREG_OFFSET_CAPTURE);
                    $_lastN = $_n;
                    if ( strlen($matches[0][0]) > 0 )
                    {
                        $_n = trim(str_replace('n.', '', $matches[0][0]), ' ,.-_;');
                        if ( substr_count($_n, '-') > 0 )
                        {
                            $_n = explode('-', $_n);
                            $_n = $_n[0] . '.' . $_n[1];
                        }

                        if ( !is_numeric($_n) )
                        {
                            $_n = preg_match('/[0-9][0-9]/', $_n, $matches, PREG_OFFSET_CAPTURE);
                            if ( is_numeric($matches[0][0]) )
                            {
                                $_n = $matches[0][0];
                            }
                            else
                            {
                                $_n = $_lastN + 1;
                            }
                        }
                    }
                    else
                    {
                        $_n = 0;
                    }

                    if ( !$count[$ano] )
                    {
                        $count[$ano] = 10;
                    }

                    $_v = substr_count($_info, 'v.');
                    if ( !$_n && count($matches) == 0 && $_v == 0 )
                    {
                        $_fim[] = $child;
                        continue;
                    }
                    
                    if ( $_n > 0 )
                    {
                        $_childrens[$ano][$mes][$_n][] = $child;
                        $count[$ano] = $_n + 1;
                    }
                    else
                    {                        
                        $_childrens[$ano][$mes][-$count[$ano]][] = $child;
                        $count[$ano]++;
                    }
                }

                $childrens = array( );
                $i = 0;
                ksort($_childrens);
                foreach ( $_childrens as $ano => $_child )
                {   
                    ksort($_childrens[$ano]);
                   
                    foreach ( $_child as $mes => $ch )
                    {
                         ksort($_childrens[$ano][$mes]);
                         
                         foreach ($_ch as $num => $c )
                         {
                             ksort($_childrens[$ano][$mes][$num]);
                         }
                    }
                }
                
                if ( count($_fim) > 0 )
                {
                    foreach ( $_fim as $fim )
                    {
                        $childrens[$i] = $fim;
                        $i++;
                    }
                }
                
                foreach ( $_childrens as $ano => $_child )
                {
                    foreach ( $_child as $_c )
                    {
                        foreach ( $_c as $_ch )
                        {
                            foreach ( $_ch as $_cnumber )
                            {
                                $childrens[$i] = $_cnumber;
                                $i++;
                            }
                        }
                    }
                }
            }

            //se tiver filhos
            if ( $childrens && is_array($childrens) )
            {
                $order = array( );

                foreach ( $childrens as $line => $info )
                {
                    $tableData[$line] = null;
                    //FIXME adicionar uma imagem
                    $tableData[$line][0] = new MLinkButton('divSubDetail' . $info, _M('Detalhe', $this->module), "javascript:" . GUtil::getAjax('openMaterialDetail', $info));
                    $tableData[$line][1] = $this->busSearchFormat->getFormatedString($info, $searchFormat, 'detail');
                    $order[$line] = $this->checkRepeat($this->busSearchFormat->periodicInformationContent, $order);
                }

                //reordena os dados
                $newData = array( );

                foreach ( $tableData as $key => $val )
                {
                    $newData[] = $val;
                }

                krsort($newData);

                $tableData = array( );
                $tableData = array_values($newData); //redefine os array com indeces numéricos

                $titles = null;
                $titles[] = _M('Detalhes', $this->module);
                $titles[] = _M('Informação', $this->module);

                $tabChildrenControls = new MTableRaw(null, $tableData, $titles, 'children');
                $tabChildrenControls->setAlternate(true);
                $tabChildrenControls->addAttribute('width', '99%');

                $divChildren = new MDiv('divChildren', $tabChildrenControls);
                $divChildren->addStyle('overflow', 'auto');

                return $divChildren;
            }
        }

        return null;
    }

    function externalSearch()
    {
        return (MIOLO::_REQUEST('action') == 'main:search:externalSearch') || $_SESSION['externalSearch'];
    }

    /**
     * Obtem botões inferiores
     *
     * @return unknown_type
     */
    public function getBottomButtons($hideCloseButton)
    {
        $material = $this->material;

        //pega menus
        if ( GOperator::isLogged() )
        {
            $businessSpreadsheet = $this->MIOLO->getBusiness($this->module, "BusSpreadsheet");

            if ( $this->isBook )
            {
                $menu = $businessSpreadsheet->getMenus('BA', '4', false); //false faz pegar mesmo que esteja escondido/sem nome
            }
            else
            if ( $this->isCollection )
            {
                $menu = $businessSpreadsheet->getMenus('SE', '4', false);
            }
            else
            if ( $this->isCollectionFascicle )
            {
                $menu = $businessSpreadsheet->getMenus('SA', '4', false);
            }

            //se tem menu de filho adiciona o botão
            if ( $menu )
            {
                $menu = $menu[0];
                $menuoption = str_replace("#", "*", $menu->menuoption);

                //se não encontrou nome para o menu, coloca nome padrão
                if ( !$menu->menuname )
                {
                    $menu->menuname = _M('Novo filho', $this->module);
                }

                if ( GPerms::checkAccess('gtcMaterial', 'insert', false) )
                {
                    $fields[] = new GRealLinkButton('newChildren', $menu->menuname, "main:catalogue:material&function=addChildren&controlNumber={$this->controlNumber}&leaderString=$menuoption", $this->imageAddChield);
                }
            }

            //se tem permissão adiciona botão de alterar material
            if ( GPerms::checkAccess('gtcMaterial', 'update', false) )
            {
                $fields[] = new GRealLinkButton('changeMaterial', _M('Alterar material', $this->module), "main:catalogue:material&function=update&controlNumber={$this->controlNumber}", $this->imageChangeMaterial);
            }
            //se tem permissão o de duplicar material
            if ( GPerms::checkAccess('gtcMaterial', 'insert', false) )
            {
                $fields[] = new GRealLinkButton('duplicateMaterial', _M('Duplicar material', $this->module), "main:catalogue:material&function=duplicate&controlNumber={$this->controlNumber}", $this->imageDuplicateMaterial);
            }
        }

        //link de ir para o pai, caso exista um pai
        if ( $material->controlNumberFather )
        {
            $typeF = $this->busMaterialControl->getTypeOfMaterial($material->controlNumberFather);
            $parentName = $this->busMaterialControl->getNameOfTypeOfMaterial($typeF);

            $fields['controlFatherNumber'] = new MButton('controlNumberFather', _M('Ir para ', $this->module) . $parentName, GUtil::getAjax('openMaterialDetail', $material->controlNumberFather), $this->imageGnuteca);
        }

        //adiciona botão de reserva
        // FIXME
        // quando usado dentro da janela verifyUser ( F10 da circulação de material) e Favoritos da Minha Biblioteca, não mostra botão de reserva
        // isso foi uma resolução alternativa e temporária, o sistema de reserva web deve ser Reimplementado para que isso possa funcionar corretamente
        if ( (MIOLO::_REQUEST('action') != 'main:verifyUser') && (MIOLO::_REQUEST('action') != 'main:myLibrary:myReserves') && (MIOLO::_REQUEST('action') != 'main:myLibrary:favorite') && !$this->externalSearch() && !$this->args->disableReserveButton )
        {
            if ( ( $this->exemplarys ) || ($this->isBook) )
            {
                $reserveControlNumber = $this->controlNumber;
                $reserveButtonTitle = _M('Reservar', $this->module);
            }
            else if ( !$this->isCollection && !$this->isBook && !$this->isCollectionFascicle )
            {
                $reserveControlNumber = $this->controlNumberFather;
                $reserveButtonTitle = _M('Reservar fascículo', $this->module);
            }
            if ( $reserveControlNumber )
            {
                $url = GUtil::getAjax('gridReserve', $reserveControlNumber);
                $fields['reserve'] = new MButton('btnReserveDetail', $reserveButtonTitle, $url, $this->imageReserve);
            }
        }

        if ( !$hideCloseButton )
        {
            $fields['btnClose'] = new MButton('btnClose', _M('Fechar', $this->module), 'javascript:gnuteca.closeAction();', $this->imageExit);
        }

        return $fields;
    }

    /**
     *  Obtem tab principal
     *
     * @return unknown_type
     */
    public function getMainTabFields($args = null)
    {
        $args = (Object) $_REQUEST;

        // monta label 'Escolha seu formato de pesquisa...
        if ( SIMPLE_SEARCH_SEARCH_FORMAT_STRING != '' && SIMPLE_SEARCH_SEARCH_FORMAT_STRING != 'SIMPLE_SEARCH_SEARCH_FORMAT_STRING' )
        {
            $fields[] = new MDiv('', SIMPLE_SEARCH_SEARCH_FORMAT_STRING);
        }

        $searchFormatId = $args->searchFormatS ? $args->searchFormatS : $args->searchFormatS;

        if ( $this )
        {
            $searchFormatId = $this->searchFormatId;
            $controlNumber = $this->controlNumber;
            $fields[] = $this->getSearchButtons($controlNumber);

            if ( !is_numeric($searchFormatId) ) //&& $this->isMyLibrary
            {
                if ( $this->MIOLO->getCurrentAction() == 'main:catalogue:kardexControl' )
                {
                    $searchFormatId = SIMPLE_SEARCH_SEARCH_FORMAT_ID;
                }
                else
                {
                    $searchFormatId = FAVORITES_SEARCH_FORMAT_ID;
                }
            }
        }
        else
        {
            $controlNumber = $args->_id ? $args->_id : $args->controlNumber;
            $fields[] = GMaterialDetail::getSearchButtons($controlNumber);
        }

        $tableDetail = GMaterialDetail::getSearchFormatTable($controlNumber, $searchFormatId);
        $divMaterialDetail = new MDiv('divSearchFormat', $tableDetail);

        $fields[] = $divMaterialDetail;

        // FIXME  porque isto??
        if ( $tableDetail && $responseDiv = MIOLO::_REQUEST('responseDiv') )
        {
            return $divMaterialDetail;
        }

        return $fields;
    }

    public static function getSearchFormatTable($controlNumber, $searchFormatId)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busSFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');
        //pega string formatada para o número de controle
        $tempData = $busSFormat->getFormatedString($controlNumber, $searchFormatId, 'detail');
        //troca &nbsp (espaço do html), por nada
        $tempData = str_replace('&nbsp;', '', $tempData);
        //explode por <br> para por em linhas
        $detailData = preg_split('/<([ \/]{0,}?[bB][rR][ \/]{0,}?)>/U', $tempData);

        // converte para o formato da MTableRaw
        if ( is_array($detailData) )
        {
            foreach ( $detailData as $line => $info )
            {
                $detailData[$line] = array( $info );
            }

            $tableDetail = new MTableRaw(null, $detailData);
            $tableDetail->setAlternate(true);
            $tableDetail->addAttribute('width', '98%');

            return $tableDetail;
        }

        return null;
    }

    /**
     * Check if the value is present on array, if true, add "_" suffix N times on end of the value
     *
     * @param unknown_type $initialValue
     * @param array $order
     * @return unknown
     */
    public function checkRepeat($initialValue, array $order)
    {
        $i = 0;
        $value = $initialValue;
        while ( in_array($value, $order) )
        {
            $value = $initialValue . str_repeat('_', $i++);
        }
        return $value;
    }

    /**
     * Retorna botões com formatos de pesquisa possíveis
     *
     * @param $serchFormatId
     * @param $value
     * @return unknown_type
     */
    public function getSearchButtons($controlNumber)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        if ( $this )
        {
            $defaultValue = $this->searchFormatId ? $this->searchFormatId : SIMPLE_SEARCH_SEARCH_FORMAT_ID;
            $busSearchFormat = $this->busSearchFormat;
            $busSearchFormat->clean();
        }
        else
        {
            $defaultValue = MIOLO::_REQUEST('searchFormatS');
            $busSearchFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');
        }

        //verifica se é para mostrar formatos restritos ou não
        $isRectricted = GOperator::isLogged() ? false : true;
        //lista formatos
        $searchFormat = $busSearchFormat->listSearchFormat(false, $isRectricted);

        $btn = array( ); //zera o array de botões
        $btn[] = $btnSearch = new GSelection('searchFormatS', $defaultValue, null, $searchFormat, null, null, 1, true);

        $btnSearch->addAttribute('onchange', GUtil::getAjax('changeSearchFormat', "this.value +',$controlNumber'", '__mainForm', true));

        return new MHContainer('searchFormatButtons', $btn);
    }

    /**
     * Suporta troca de formato em todos detalhes
     * @param <stdClass> $args
     */
    public static function changeSearchFormat($args)
    {
        $MIOLO = MIOLO::getInstance();
        $explode = explode(',', Gutil::getAjaxEventArgs());
        $_REQUEST['searchFormatS'] = $explode[0];
        $_REQUEST['controlNumber'] = $explode[1];
        $MIOLO->ajax->setResponse(GMaterialDetail::getMainTabFields(), 'tabMain');
    }

    public static function openMaterialDetail()
    {

        if ( stripos(Gutil::getAjaxEventArgs(), '|#|') )
        {
            $args = GUtil::decodeJsArgs(GUtil::getAjaxEventArgs());
            $controlNumber = $args->controlNumber;
        }
        else
        {
            $controlNumber = GUtil::getAjaxEventArgs();
        }

        GForm::injectContent(new GMaterialDetail($controlNumber, null, null, $args->gotoTab), false, _M('Detalhes', 'gnuteca3'));
    }

    /**
     * Retorna relação de exemplares
     *
     * @param $controlNumber
     * @param $libraryUnitId
     * @param $extraTags
     * @return unknown_type
     */
    public static function getExemplaryList($controlNumber, $libraryUnitId = NULL, $extraTags = NULL)
    {
        $MIOLO = MIOLO::getInstance();

        if ( !$libraryUnitId )
        {
            $libraryUnitId = MIOLO::_REQUEST('libraryUnitId');
        }

        $busExemplaryControl = $MIOLO->getBusiness('gnuteca3', 'BusExemplaryControl');

        return $busExemplaryControl->getExemplaryOfMaterial($controlNumber, $libraryUnitId, true, $extraTags, $libraryUnitId ? true : false);
    }

    public function getExemplaryTable($controlNumber, $extraTagList = NULL, $filter = null, $libraryUnitId = null)
    {
        $controlNumberFather = $this->material->controlNumberFather;

        //somente instancia os business se for necessário
        if ( $this )
        {
            $busExemplaryControl = $this->busExemplaryControl;
            $busMaterialControl = $this->busMaterialControl;
            $busReserve = $this->busReserve;
            $busExemplaryStatus = $this->busExemplaryStatus;
            $busLoan = $this->busLoan;
        }
        else
        {
            $MIOLO = MIOLO::getInstance();
            $module = $MIOLO->getCurrentModule();
            $busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
            $busMaterialControl = $MIOLO->getBusiness($module, 'BusMaterialControl');
            $busReserve = $MIOLO->getBusiness($module, 'BusReserve');
            $busExemplaryStatus = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
            $busLoan = $MIOLO->getBusiness($module, 'BusLoan');
            ;
        }

        if ( !$libraryUnitId )
        {
            $libraryUnitId = MIOLO::_REQUEST('libraryUnitId');
        }

        //trabalha constante que define campos extras na relação de exemplares
        if ( !$extraTagList )
        {
            if ( GOperator::isLogged() )
            {
                if ( SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_OPERATOR != 'SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_OPERATOR' )
                {
                    $extraTagList = SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_OPERATOR;
                }
            }
            else
            {
                if ( SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_USER != 'SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_USER' )
                {
                    $extraTagList = SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_USER;
                }
            }
        }

        //verificação extra se existe ou não
        if ( $extraTagList )
        {
            $extraTagsData = explode(',', $extraTagList);
        }

        if ( is_array($extraTagsData) )
        {
            foreach ( $extraTagsData as $line => $info )
            {
                $temp = explode('=', $info);
                $extraTags[trim($temp[0])] = trim($temp[1]);
                $extraTitles[] = trim($temp[0]);
            }
        }

        $exemplarys = GMaterialDetail::getExemplaryList($controlNumber, $libraryUnitId, $extraTags);
        //$exemplarys = $busExemplaryControl->getExemplaryOfMaterial($controlNumber, $libraryUnitId, true , $extraTags, $libraryUnitId ? true : false);
        //seta na classe para usar após , mas só exemplares do pai
        if ( $this )
        {
            $this->exemplarys = $exemplarys;
        }

        //FIXME usar getExemplaryOfMaterial com getFatherExemplar
        //se não tiver exemplar e for artigo pega nova lista de exemplares
        if ( !$exemplarys && ( $busMaterialControl->isBookArticle($controlNumber) || $this->busMaterialControl->isColletionArticle($controlNumber) ) )
        {
            $controlNumberFather = $busMaterialControl->getControlNumberFather($controlNumber);

            if ( $controlNumberFather )
            {
                $exemplarys = $busExemplaryControl->getExemplaryOfMaterial($controlNumberFather, $libraryUnitId, true, $extraTags, $libraryUnitId ? true : false);
            }
        }

        if ( $filter ) //Chamado pela reserva, filtra a relação de exemplares, limitando-a
        {
            $exemplaryTemp = $exemplarys;
            $exemplarys = null;
            foreach ( $exemplaryTemp as $index => $exemplaryItem )
            {
                if ( in_array($exemplaryItem->itemNumber, $filter) )
                {
                    $exemplarys[] = $exemplaryItem;
                }
            }
        }

        $exemplarysData = null;

        if ( is_array($exemplarys) )
        {
            foreach ( $exemplarys as $line => $info )
            {

                if ( !GMaterialDetail::checkDisplayExemplary($info->exemplaryStatus->exemplaryStatusId) )
                {
                    continue;
                }

                //trata emprestado para no campo hardcode libraryUnitId
                $emprestadoPara = null;

                if ( $info->originalLibraryUnitId != $info->libraryUnitId && $libraryUnitId )
                {
                    $emprestadoPara = _M('Emprestado p/', $this->module);
                    $exemplarys[$line]->libraryName = $emprestadoPara . $info->libraryName;
                }

                $tempExemplary = null;
                $tempExemplary[] = $info->itemNumber;

                foreach ( $extraTags as $name => $tag )
                {
                    if ( strlen($tag) )
                    {
                        $desc = "{$tag}_DESC";
                        $dado = (strlen($info->$desc) > 0) ? $info->$desc : $info->$tag;

                        if ( $tag == MARC_EXEMPLARY_LIBRARY_UNIT_ID_TAG && $libraryUnitId )
                        {
                            //FIXME isso só é necessário em função de um bug na relationOfFieldsWithTable que não permite acesso a string com o nome da biblioteca caso não esteja logado como admin
                            if ( is_numeric($dado) )
                            {
                                $dado = $info->libraryName;
                            }
                            else
                            {
                                $dado = $emprestadoPara . ' ' . $dado;
                            }
                        }

                        $tempExemplary[] = $dado;
                    }
                }

                $tempExemplary[] = $info->exemplaryStatusDescription;

                $reserves = $busReserve->getReservesOfExemplary($info->itemNumber, array( ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_REPORTED ));

                $reserveCount = 0;

                if ( is_array($reserves) )
                {
                    $reserveCount = count($reserves);
                }

                $tempExemplary[] = $reserveCount;

                $returnForecastDate = '';

                //se tiver emprestado pega da prevista de devolução
                if ( $info->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_EMPRESTADO )
                {
                    $returnForecastDate = $busLoan->getReturnForecastDateFromItemNumber($info->itemNumber);
                    $returnForecastDate = GDate::construct($returnForecastDate)->getDate(GDate::MASK_DATE_USER);
                }

                $tempExemplary[] = $returnForecastDate;

                //Set grid color
                $isLowStatus = $busExemplaryStatus->getExemplaryStatus($info->exemplaryStatusId)->isLowStatus;
                $color = null;
                if ( $info->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_DISPONIVEL )
                {
                    $color = 'blue';
                }
                else if ( MUtil::getBooleanValue($isLowStatus) )
                {
                    $color = 'red';
                }

                foreach ( $tempExemplary as $i => $v )
                {
                    $span = new MSpan(null, $v);
                    if ( $color )
                    {
                        $span->setColor($color);
                    }
                    if ( $filter ) //Chamando da reserva
                    {
                        //Diminui o tamanho da fonte
                        $span->addStyle('font-size', '9px');
                    }
                    $tempExemplary[$i] = $span;
                }

                $exemplarysData[] = $tempExemplary;
            }

            $titles[] = _M('Número do exemplar', $this->module);
            $titles = array_merge($titles, $extraTitles ? $extraTitles : array( ) );
            $titles[] = _M('Estado', $this->module);
            $titles[] = _M('Quantidade de reservas', $this->module);
            $titles[] = _M('Data prevista da devolução', $this->module);


            $tableExemplarys = new MTableRaw('', $exemplarysData, $titles, 'exemplaryList');
            $tableExemplarys->setAlternate(true);
            $tableExemplarys->addAttribute('width', '99%');

            if ( $filter )
            {
                return $tableExemplarys;
            }

            $divExemplarys = new MDiv('divExemplarys', $tableExemplarys);

            $tabExemplaryControls[] = $divExemplarys;

            if ( $controlNumberFather )
            {
                $this->tabExemplaryTitle = _M('Exemplares do fascículo', $this->module);
            }
            else
            {
                $this->tabExemplaryTitle = _M('Exemplares', $this->module);
            }
        }

        return $divExemplarys;
    }

    public static function checkDisplayExemplary($exemplaryStatusId)
    {
        $MIOLO = MIOLO::getInstance();
        $operator = !GOperator::isLogged();

        // LISTA DE EXEMPLARES A IGNORAR NA PESQUISA
        if ( $operator && SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS != 'SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS' && strlen(SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS) )
        {
            $ignoreStatus = explode(",", SIMPLE_SEARCH_EXCLUDE_EXEMPLARY_STATUS);
            return array_search($exemplaryStatusId, $ignoreStatus) === false;
        }

        return true;
    }

    function getExtraTagList($extraTagList = null)
    {
        //trabalha constante que define campos extras na relação de exemplares
        if ( !$extraTagList )
        {
            if ( GOperator::isLogged() )
            {
                if ( SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_OPERATOR != 'SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_OPERATOR' )
                {
                    $extraTagList = SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_OPERATOR;
                }
            }
            else
            {
                if ( SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_USER != 'SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_USER' )
                {
                    $extraTagList = SIMPLE_SEARCH_EXEMPLAR_DETAIL_FIELD_LIST_USER;
                }
            }
        }

        return $extraTagList;
    }

    public function getExemplaryTableByExemplaryObject($exemplarys, $extraTagList = NULL, $libraryUnitId = null)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $busExemplaryControl = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $busMaterialControl = $MIOLO->getBusiness($module, 'BusMaterialControl');
        $busReserve = $MIOLO->getBusiness($module, 'BusReserve');
        $busExemplaryStatus = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
        $busLoan = $MIOLO->getBusiness($module, 'BusLoan');
        ;

        foreach ( $exemplarys as $line => $info )
        {
            if ( !GMaterialDetail::checkDisplayExemplary($info->exemplaryStatus->exemplaryStatusId) )
            {
                continue;
            }

            $tempExemplary = null;
            $tempExemplary[] = $info->itemNumber;
            $tempExemplary[] = $info->exemplaryStatusDescription;

            $reserves = $busReserve->getReservesOfExemplary($info->itemNumber, array( ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_REPORTED ));

            $reserveCount = 0;

            if ( is_array($reserves) )
            {
                $reserveCount = count($reserves);
            }

            $tempExemplary[] = $reserveCount;

            $returnForecastDate = '';

            //se tiver emprestado pega da prevista de devolução
            if ( $info->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_EMPRESTADO )
            {
                $returnForecastDate = $busLoan->getReturnForecastDateFromItemNumber($info->itemNumber);
                $returnForecastDate = GDate::construct($returnForecastDate)->getDate(GDate::MASK_DATE_USER);
            }

            $tempExemplary[] = $returnForecastDate;

            //Set grid color
            $isLowStatus = $busExemplaryStatus->getExemplaryStatus($info->exemplaryStatusId)->isLowStatus;
            $color = null;
            if ( $info->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_DISPONIVEL )
            {
                $color = 'blue';
            }
            else if ( MUtil::getBooleanValue($isLowStatus) )
            {
                $color = 'red';
            }

            foreach ( $tempExemplary as $i => $v )
            {
                $span = new MSpan(null, $v);
                if ( $color )
                {
                    $span->setColor($color);
                }
                if ( $filter ) //Chamando da reserva
                {
                    //Diminui o tamanho da fonte
                    $span->addStyle('font-size', '9px');
                }
                $tempExemplary[$i] = $span;
            }

            $exemplarysData[] = $tempExemplary;
        }

        $titles[] = _M('Número do exemplar', $this->module);
        $titles = array_merge($titles, $extraTitles ? $extraTitles : array( ) );
        $titles[] = _M('Estado', $this->module);
        $titles[] = _M('Quantidade de reservas', $this->module);
        $titles[] = _M('Data prevista da devolução', $this->module);

        $tableExemplarys = new MTableRaw('', $exemplarysData, $titles, 'exemplaryList');
        $tableExemplarys->setAlternate(true);
        $tableExemplarys->addAttribute('width', '99%');

        return $tableExemplarys;

        $divExemplarys = new MDiv('divExemplarys', $tableExemplarys);
        $divExemplarys->addStyle('overflow', 'auto');

        $tabExemplaryControls[] = $divExemplarys;

        $controlNumberFather = $this->busMaterialControl->getControlNumberFather($this->controlNumber);

        if ( $controlNumberFather )
        {
            $this->tabExemplaryTitle = _M('Exemplares do fascículo', $this->module);
        }
        else
        {
            $this->tabExemplaryTitle = _M('Exemplares', $this->module);
        }
    }
}

?>
