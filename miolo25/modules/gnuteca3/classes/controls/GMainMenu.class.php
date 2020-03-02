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
 * Class GmainMenu
 *
 * @author Luís Mercado [luis_augusto@solis.com.br]
 * @author Jader Fiegenbaum [jader@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Luís Mercado [luis_augusto@solis.com.br]
 * Jader Fiegenbaum [jader@solis.com.br]
 *
 *
 * @since
 * Class created on 29/12/2014
 *
 * */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript("gmainmenuv2.js", "gnuteca3");

class GMainMenu extends GMenu
{
    /**
     * Identificador do trigger do menu
     * 
     * @final {String} Id da div que conterá o menu 
     */
    const ID_MENU = "gMainMenu";
    
    /**
     * 
     * @var {Array} Contém os itens a serem adicionados ao menu 
     */
    public $itens = array();
    
    
    /**
     * Gera o código para setup inicial do menu
     * 
     * @return {String} Código JS a ser adicionado ao HTML para setup do menu
     */
    public function inicializa()
    {
        // Carrega para o array 'itens' as opções do menu
        $this->carregaDados();
        
        $js = "
            var gMainMenu = null;
            
            window.carregaMenu = function()
            {
                var interval = setInterval(function()
                {
                    // Executar apenas quando a classe 'GMainMenu' estiver declarada
                    if( typeof GMainMenu !== 'undefined' )
                    {
                        gMainMenu = new GMainMenu();

                        gMainMenu.setup('" . self::ID_MENU . "');

                        // Converte os itens para serem efetivos no JS
                        {$this->geraItens()}

                        // Faz o setup
                        gMainMenu.startup();

                        clearInterval(interval);

                    }

                }, 5);
                
            }

        ";
        
        return $js;
    }
    
    /**
     * Junta o array de itens para ser interpretado pelo JS
     * 
     * @return {String} String para a ser interpretada pelo JS
     */
    public function geraItens()
    {
        $retorno = implode(";\n", $this->itens);
        
        // O último item também precisa do ponto e vírgula
        $retorno .= ";";
        
        return $retorno;
    }
    
    /**
     * Carrega os os itens para o array 'itens'
     *  
     */
    public function carregaDados()
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'gnuteca3';
        $adminModule = $MIOLO->mad;
        
        //segurança para não efetuar os sqls em funções ajax
        if (GUtil::getAjaxFunction() && GUtil::getAjaxFunction() != 'tbBtnNew:click' )
        {
            return false;
        }

        //para funcionar no syncDB
        if ($MIOLO->getCurrentModule() == 'base')
        {
            return false;
        }

        $this->adicionaItemUsuario("gtcMaterialMovement", A_ACCESS, _M("Circulação de material", $module), "main:materialMovement", null, null, "iconeCirculacaoMaterial");
        
        $this->criaMenuPesquisa();
        $this->criaMenuDocumento();
        $this->criaMenuAdministracao();
        $this->criaMenuCatalogacao();
        $this->criaMenuConfiguracao();
        
        if ($MIOLO->getLogin()->id)
        {
            $this->adicionaItem('trocaUnidade', _M('Trocar unidade', 'gnuteca3'), 'javascript:' . GUtil::getAjax('statusChangeLoggedLibrary', null, null, null, 'gnuteca'), '', '', 'iconeLibraryUnit');
            $this->adicionaItem('sobre', _M('Sobre'), 'javascript:' . GUtil::getAjax('statusAbout'), '', '', 'iconeInfo');
            $this->adicionaItem('sair', _M('Sair', 'gnuteca3'), 'logout', '', '', 'iconeExit');
        }
    }
    
    /**
     * Menu pesquisa.
     */
    public function criaMenuPesquisa()
    {
        $MIOLO = MIOLO::getInstance();
        $module = "gnuteca3";
        
        $home = 'main:search';
        $parent = "search";
        
        // Cria o parent
        //$this->adicionaMenu($parent, );
        
        $menuPequisa = new GMenu($parent, _M("Pesquisa", $module), "iconeBusca");
        
        //Busca pesquisas definidas pelo administrador
        $busFormContent = $MIOLO->getBusiness($module, 'BusFormContent');

        //segurança para base zerada
        $FORM_CONTENT_TYPE_ADMINISTRATOR = FORM_CONTENT_TYPE_ADMINISTRATOR;

        if ($FORM_CONTENT_TYPE_ADMINISTRATOR == 'FORM_CONTENT_TYPE_ADMINISTRATOR')
        {
            $FORM_CONTENT_TYPE_ADMINISTRATOR = 1;
        }

        $busFormContent->formContentType = $FORM_CONTENT_TYPE_ADMINISTRATOR;
        $search = $busFormContent->searchFormContent(TRUE);

        if ($search)
        {
            foreach ($search as $v)
            {
                //nome especifico somente usado dentro da circulação de material, então pula ele na relação
                if ($v->name == 'materialMovement')
                {
                    continue;
                }
                
                //Lista todas as pesquisas criadas pelo administrador
                $menuItem[] = array($v->name, "iconeBusca", "{$home}:simpleSearch", '', $v->formContentId);
            }
        }

        if (GPerms::checkAccess('gtcZ3950', null, false))
        {
            $menuItem[] = array(_M('Z3950', $module), "iconeBusca", "$home:simpleSearch&subForm=Z3950");
        }

        if (GB_INTEGRATION == DB_TRUE)
        {
            $menuItem[] = array(_M('Google Book', $module), "iconeBusca", "$home:simpleSearch&subForm=GoogleBook");
        }

        $menuItem[] = array(_M('Biblioteca nacional', $module), "iconeBusca", "$home:simpleSearch&subForm=FBN");

        foreach ($menuItem as $m)
        {
            $formContentId = $m[4];

            if ($formContentId)
            {
                $args = array('formContentId' => $formContentId, 'formContentTypeId' => FORM_CONTENT_TYPE_ADMINISTRATOR);
            }
            else
            {
                $args = array();
            }

            $menuPequisa->adicionaItem(str_replace(" ", "", $m[0]), $m[0], $m[2], null, $args, $m[1], false);
            
        }
        
        // Adiciona o menu ao menu raiz
        $this->adicionaMenu($menuPequisa);
        
    }
    
    /**
     * Menu Documento.
     */
    public function criaMenuDocumento()
    {
        $MIOLO = MIOLO::getInstance();
        $module = "gnuteca3";
        
        //DOCUMENTOS
        $home = 'main:documents';
        $parentDocumento = 'documents';
        $parentRelatorio = 'report';
        
        //$documentMenu = new GMainMenu('documents', _M('Documentos', $module), 'report-16x16.png');
        $menuDocumento = new GMenu($parentDocumento, _M("Documentos", $module), "iconeDocumento");
        //$reportAdminMenu = new GMainMenu('report', _M('Relatórios', $module), 'report-16x16.png');
        $menuRelatorio = new GMenu($parentRelatorio, _M("Relatórios", $module), "iconeDocumento");
        
        $busReport = $MIOLO->getBusiness("gnuteca3", "BusReport");
        $reportGroup = BusinessGnuteca3BusDomain::listForSelect('REPORT_GROUP');
        
        // Cria os submenus
        if(is_array($reportGroup))
        {
            foreach ($reportGroup as $line => $group)
            {
                $reportMenus[$group[0]] = new GMenu('report' . $group[0], $group[1], "iconeDiretorio");
            }
        }

        $printAdminMenu = new GMenu("printAdmin", _M("Impressão", $module), "iconeImprimir");
        $printAdminMenu->adicionaItemUsuario("gtcBackOfBook", A_ACCESS, _M("Lombada", $module), "$home:backofbook", null, null, "iconeRetornoLivro");
        $printAdminMenu->adicionaItemUsuario("gtcBarcode", A_ACCESS, _M('Código de barras', $module), "$home:barcode", null, array("function" => "search"), "iconeCodigoBarras");
        
        //lista somente ativos
        $busReport->isActiveS = 't';
        $reportList = $busReport->searchReport(true, true);
        
        if (is_array($reportList))
        {
            foreach ($reportList as $line => $info)
            {
                if ($info[9])
                {
                    if ($info[9] == 'IMP') //O grupo impressão é adicionado no menu de impressões
                    {
                       $printAdminMenu->adicionaItemUsuario("gtcAdminReport", A_ACCESS, $info[1], "$home:adminReport&menuItem=" . $info[0], null, array("reportId" => $info[0]), "iconeDocumento");
                       
                    }
                    elseif ($reportMenus[$info[9]]) //Adiciona em um grupo
                    {
                        $reportMenus[$info[9]]->adicionaItemUsuario("gtcAdminReport", A_ACCESS, $info[1], "$home:adminReport&menuItem=" . $info[0], null, array("reportId" => $info[0]), "iconeDocumento");
                    }
                }
            }
        }

        foreach ($reportMenus as $repMenu)
        {
            $menuRelatorio->adicionaMenu($repMenu);
        }
        
        $menuDocumento->adicionaMenu($menuRelatorio);
        $menuDocumento->adicionaMenu($printAdminMenu);
        $this->adicionaMenu($menuDocumento);
    }
    
    /**
     * Menu administração.
     */
    public function criaMenuAdministracao()
    {
        $MIOLO = MIOLO::getInstance();
        $module = "gnuteca3";
        
        $home = 'main:administration';
        $adminMenu = new GMenu('administration', _M("Administração", $module), "iconeAdministration");
        
        $adminMaterialMovementMenu = new GMenu('adminMaterialMovement', _M('Circulação de material', $module), 'iconeMaterialMovement');
        $adminMaterialMovementMenu->adicionaItemUsuario('gtcLoan', A_ACCESS, _M('Empréstimo', $module), "$home:loan", null, null, 'iconeLoan');
        $adminMaterialMovementMenu->adicionaItemUsuario('gtcReserve', A_ACCESS, _M('Reserva', $module), "$home:reserve", null, null, 'iconeReserve');
        $adminMaterialMovementMenu->adicionaItemUsuario('gtcRenew', A_ACCESS, _M('Renovação', $module), "$home:renew", null, null, 'iconeRenew');
        $adminMaterialMovementMenu->adicionaItemUsuario('gtcFine', A_ACCESS, _M('Multa', $module), "$home:fine", null, null, 'iconeFine');
        $adminMaterialMovementMenu->adicionaItemUsuario('gtcInterchange', A_ACCESS, _M('Permuta/Doação', $module), 'main:administration:interchange', null, null, 'iconeInterchange');
        $adminMaterialMovementMenu->adicionaItemUsuario('gtcLoanbetweenLibrary', A_ACCESS, _M('Empréstimo entre bibliotecas', $module), 'main:administration:loanbetweenlibrary', null, null, 'iconeLoanbetweenlibrary');
        $adminMaterialMovementMenu->adicionaItemUsuario('gtcPurchaseRequest', A_ACCESS, _M('Solicitação de compras', $module), "$home:purchaseRequest", null, null, 'iconePurchaseRequest');
        $adminMaterialMovementMenu->adicionaItemUsuario('gtcExemplaryFutureStatusDefined', A_ACCESS, _M('Estado futuro do exemplar', $module), 'main:administration:exemplaryFutureStatusDefined', null, null, 'iconeExemplaryfuturestatusdefined');
        $adminMaterialMovementMenu->adicionaItemUsuario('gtcRequestChangeExemplaryStatus', A_ACCESS, _M('Requisição de alteração de estado', $module), 'main:administration:requestChangeExemplaryStatus', null, null, 'iconeRequestChangeExemplaryStatus');
       
        $adminMenu->adicionaMenu($adminMaterialMovementMenu);
       
        $adminPessoaMenu = new GMenu('adminPessoa', _M('Pessoas', $module), 'iconePerson');
        $adminPessoaMenu->adicionaItemUsuario('gtcPerson', A_ACCESS, _M('Pessoa', $module), "$home:person", null, null, 'iconePerson');
        $adminPessoaMenu->adicionaItemUsuario('gtcBond', A_ACCESS, _M('Vínculo', $module), "$home:bond", null, null, 'iconeBond');
        $adminPessoaMenu->adicionaItemUsuario('gtcPenalty', A_ACCESS, _M('Penalidade', $module), "$home:penalty", null, null, 'iconePenalty');
        $adminPessoaMenu->adicionaItemUsuario('gtcSupplier', A_ACCESS, _M('Fornecedor', $module), "$home:supplier", null, null, 'iconeSupplier');
        $adminMenu->adicionaMenu($adminPessoaMenu);
        
        $procAdminMenu = new GMenu('proccess', _M('Tarefas', $module), 'iconeProccess');
        $procAdminMenu->adicionaItemUsuario('gtcSendMailReturn', A_ACCESS, _M('Comunicar devoluções', $module), 'main:administration:devolution', null, null, 'iconeDevolution');
        $procAdminMenu->adicionaItemUsuario('gtcSendMailDelayedLoan', A_ACCESS, _M('Empréstimo atrasado', $module), 'main:administration:delayedLoan', null, null, 'iconeDelayedloan');
        $procAdminMenu->adicionaItemUsuario('gtcSendMailNotifyAcquisition', A_ACCESS, _M('Notificação de aquisições', $module), 'main:administration:notifyacquisition', null, null, 'iconeNotifyacquisition');
        $procAdminMenu->adicionaItemUsuario('gtcSendMailAnsweredReserves', A_ACCESS, _M('Comunicação de reservas', $module), 'main:administration:answeredreserves', null, null, 'iconeAnsweredreserves');
        $procAdminMenu->adicionaItemUsuario('gtcSendMailReserveQueue', A_ACCESS, _M('Reorganização da fila de reserva', $module), 'main:administration:reservequeue', null, null, 'iconeReservequeue');
        $procAdminMenu->adicionaItemUsuario('gtcVerifyLinks', A_ACCESS, _M('Verificação links', $module), 'main:administration:verifyLinks', null, null, 'iconeReservequeue');
        $procAdminMenu->adicionaItemUsuario('gtcSendMailNotifyEndRequest', A_ACCESS, _M('Notificação de fim de requisição', $module), 'main:administration:notifyEndRequest', null, null, 'iconeAnsweredreserves');
        $procAdminMenu->adicionaItemUsuario('gtcDeleteValuesOfSpreadSheet', A_ACCESS, _M('Exclusão de valores das planilhas', $module), 'main:administration:deleteValuesOfSpreadSheet', null, null, 'iconeDeleteValuesOfSpreadSheet');
        $adminMenu->adicionaMenu($procAdminMenu);

        $geralAdminMenu = new GMenu('geralAdmin', _M('Geral', $module), 'iconeGnuteca3');
        $geralAdminMenu->adicionaItemUsuario('gtcNews', A_ACCESS, _M('Notícias', $module), "$home:news", null, null, 'iconeNews');
        $geralAdminMenu->adicionaItemUsuario('gtcCostCenter', A_ACCESS, _M('Centro de custo', $module), "$home:costCenter", null, null, 'iconeCostCenter');
        $geralAdminMenu->adicionaItemUsuario('gtcFile', A_ACCESS, _M('Arquivo', $module), "$home:file", null, null, 'iconeFolder');
        $geralAdminMenu->adicionaItemUsuario('gtcBackgroundTaskLog', A_ACCESS, _M('Tarefas em segundo plano', $module), "main:administration:backgroundTaskLog", null, null, 'iconeBackgroundTaskLog');
        $geralAdminMenu->adicionaItemUsuario('gtcanalytics', A_ACCESS, _M('Acesso', $module), "main:administration:analytics", null, null, 'iconeAccess');
        $geralAdminMenu->adicionaItemUsuario('gtcMaterialEvaluation', A_ACCESS, _M('Avaliações de material', $module),  "main:administration:materialEvaluation", null, null, 'iconeMaterialEvaluation');
        $geralAdminMenu->adicionaItemUsuario('gtcMyLibrary', A_ACCESS, _M('Moderação da minha biblioteca', $module), "main:administration:myLibrary", null, null, 'iconeMyLibrary');
        $adminMenu->adicionaMenu($geralAdminMenu);

        $acervoAdminMenu = new GMenu('acervoAdmin', _M('Acervo', $module), 'iconeCatalogue');
        //gtcReturnType
        $acervoAdminMenu->adicionaItemUsuario('gtcReturnRegister', A_ACCESS, _M('Registro de tipo de devoluções', $module), 'main:administration:returnregister', null, null, 'iconeReturnregister');
        $acervoAdminMenu->adicionaItemUsuario('gtcExemplaryStatusHistory', A_ACCESS, _M('Histórico de estados do exemplar', $module), "main:administration:exemplarystatushistory", null, array("function" => "search"), 'iconeExemplarystatushistory');
        $acervoAdminMenu->adicionaItemUsuario('gtcMaterialHistory', A_ACCESS, _M('Histórico de alteração do material', $module), "main:administration:materialhistory", null, null, 'iconeExemplarystatushistory');
        $acervoAdminMenu->adicionaItemUsuario('gtcInventoryCheck', A_ACCESS, _M('Verificação do inventário', $module), "main:administration:inventoryCheck", null, null, 'iconeInventoryCheck');

        $adminMenu->adicionaMenu($acervoAdminMenu);
        $this->adicionaMenu($adminMenu);
    }
    
    /**
     * Menu catalogação.
     */
    public function criaMenuCatalogacao()
    {
        $MIOLO = MIOLO::getInstance();
        $module = "gnuteca3";
        
        $home = 'main:catalogue';
        $catMenu = new GMenu('catalogue', _M('Catalogação', $module), 'iconeCatalogue');

        if (($MIOLO->perms->checkAccess('gtcPreCatalogue', A_INSERT, false)) || ($MIOLO->perms->checkAccess('gtcMaterial', A_INSERT, false)))
        {
            $newMaterialCatMenu = new GMenu('newMaterial', _M('Novo material', $module), 'iconeNewMaterial');
            $newMaterialCatMenu->adicionaItem('materialPadrao', _M('Padrão', $module),"$home:material", null, array('function' => 'new'), 'iconeNewMaterial');
            $businessSpreadsheet = $MIOLO->getBusiness('gnuteca3', 'BusSpreadsheet');
            $menus = $businessSpreadsheet->getMenus();

            if ($menus)
            {
                foreach ($menus as $k => $content)
                {
                    $args = array('function' => 'dinamicMenu', "leaderString" => str_replace("#", "*", $content->menuoption));
                    $newMaterialCatMenu->adicionaItem(dinamicMenu . $k, $content->menuname,  "$home:material", null, $args, 'iconeNewColection');
                }
            }

            $catMenu->adicionaMenu($newMaterialCatMenu);
        }

        if ($MIOLO->perms->checkAccess('gtcPreCatalogue', A_INSERT, false))
        {
            $catMenu->adicionaItemUsuario('gtcPreCatalogue', A_ACCESS, _M('Catalogação facilitada', $module), "$home:easyCatalogue", null, array('function' => 'insert'), 'iconeEasyCatalogue');
        }

        $catMenu->adicionaItemUsuario('gtcMaterial', A_ACCESS, _M('Material', $module), "$home:material", null, array('function' => 'search'), 'iconeChangeMaterial');
        $catMenu->adicionaItemUsuario('gtcKardexControl', A_ACCESS, _M('Controle do Kardex', $module), "$home:kardexControl", null, array('function' => 'search'), 'iconeKardexControl');
        $catMenu->adicionaItemUsuario('gtcPreCatalogue', A_ACCESS, _M('Pré-catalogação', $module), "$home:preCatalogue", null, array('function' => 'search'), 'iconePreCatalogue');

        $confCatMenu = new GMenu('dictionary', _M('Dicionário', $module), 'iconeConfig');
        $confCatMenu->adicionaItemUsuario('gtcDictionary', A_ACCESS, _M('Cadastro', $module), "$home:dictionary", null, null, 'iconeDictionary');
        $confCatMenu->adicionaItemUsuario('gtcDictionaryContent', A_ACCESS, _M('Conteúdo', $module), "$home:dictionarycontent", null, null, 'iconeDictionarycontent');
        $catMenu->adicionaMenu($confCatMenu);
        
        //menu ISO
        $isoCatMenu = new GMenu('import', _M('Importação', $module), 'iconeIso2709');
        $isoCatMenu->adicionaItemUsuario('gtcISO2709Import', A_ACCESS, _M('ISO2709', $module), 'main:catalogue:iso2709:import', null, array('function' => 'insert'), 'iconeImportIso2709');
        $isoCatMenu->adicionaItemUsuario('gtcMarc21Import', A_ACCESS, _M('Marc21', $module), 'main:catalogue:marc21import', null, array('function' => 'insert'), 'iconeImportIso2709');
        $catMenu->adicionaMenu($isoCatMenu);
        
        $this->adicionaMenu($catMenu);
    }
    
    /**
     * Menu configuração.
     */
    public function criaMenuConfiguracao()
    {
        $MIOLO = MIOLO::getInstance();
        $module = "gnuteca3";
        
        $home = 'main:configuration';
        $confMenu = new GMenu('configuration', _M('Configuração', $module), 'iconeConfig');

        $libraryMenu = new GMenu('library', _M('Unidade de biblioteca', $module), 'iconeLibraryUnit');
        $libraryMenu->adicionaItemUsuario('gtcLibraryUnit', A_ACCESS, _M('Unidade', $module), "$home:libraryUnit", null, null, 'iconeLibraryUnit');
        $libraryMenu->adicionaItemUsuario('gtcHoliday', A_ACCESS, _M('Feriado', $module), "$home:holiday", null, null, 'iconeHoliday');
        $libraryMenu->adicionaItemUsuario('gtcAssociation', A_ACCESS, _M('Associação', $module), "$home:libraryAssociation", null, null, 'iconeLibraryAssociation');
        $libraryMenu->adicionaItemUsuario('gtcPrivilegeGroup', A_ACCESS, _M('Grupo de privilégio', $module), "$home:privilegeGroup", null, null, 'iconeGroupRight');
        $libraryMenu->adicionaItemUsuario('gtcLibraryGroup', A_ACCESS, _M('Grupos de unidade', $module), "$home:libraryGroup", null, null, 'iconeLibraryGroup');
        $confMenu->adicionaMenu($libraryMenu);

        $geographiMenu = new GMenu('geographi', _M('Geografia', $module), 'iconeGeographi');
        $geographiMenu->adicionaItemUsuario('gtcCountry', A_ACCESS, _M('País', $module), "$home:country", null, null, 'iconeCountry');
        $geographiMenu->adicionaItemUsuario('gtcState', A_ACCESS, _M('Estado', $module), "$home:state", null, null, 'iconeState');
        $geographiMenu->adicionaItemUsuario('gtcCity', A_ACCESS, _M('Cidade', $module),  "$home:city", null, null, 'iconeCity');
        $geographiMenu->adicionaItemUsuario('gtcState', A_ACCESS, _M('Tipo de logradouro', $module), "$home:locationType", null, null, 'iconeLocationType');
        $confMenu->adicionaMenu($geographiMenu);

        $rapMenu = new GMenu('circulation', _M('Circulação', $module), 'iconePolicy');
        $rapMenu->adicionaItemUsuario('gtcUserGroup', A_ACCESS, _M('Grupo de usuário', $module), "$home:userGroup", null, null, 'iconeUserGroup');
        $rapMenu->adicionaItemUsuario('gtcRight', A_ACCESS, _M('Direito', $module), "$home:groupRight", null, null, 'iconeGroupRight');
        $rapMenu->adicionaItemUsuario('gtcPolicy', A_ACCESS, _M('Política', $module), "$home:policy", null, null, 'iconeGeneralPolicy');
        $rapMenu->adicionaItemUsuario('gtcGeneralPolicy', A_ACCESS, _M('Política geral', $module), "$home:generalPolicy", null, null, 'iconePrivilegeGroup');
        $rapMenu->adicionaItemUsuario('gtcLocationForMaterialMovement', A_ACCESS, _M('Local', $module), 'main:configuration:locationForMaterialMovement', null, null, 'iconeLocationForMaterialMovement');
        $rapMenu->adicionaItemUsuario('gtcRulesForMaterialMovement', A_ACCESS, _M('Regra', $module), 'main:configuration:rulesForMaterialMovement', null, null, 'iconeRulesForMaterialMovement');
        $rapMenu->adicionaItemUsuario('gtcOperation', A_ACCESS, _M('Operação', $module), 'main:configuration:operation', null, null, 'iconeOperation');
        $rapMenu->adicionaItemUsuario('gtcClassificationArea', A_ACCESS, _M('Área de classificação', $module), "$home:classificationArea", null, null, 'iconeClassificationArea');
        $rapMenu->adicionaItemUsuario('gtcReturnType', A_ACCESS, _M('Tipo de devolução', $module), 'main:configuration:returntype', null, null, 'iconeReturntype');
        $rapMenu->adicionaItemUsuario('gtcExemplaryStatus', A_ACCESS, _M('Estado do exemplar', $module), 'main:configuration:exemplaryStatus', null, null, 'iconeExemplaryStatus');
        $rapMenu->adicionaItemUsuario('gtcFineStatus', A_ACCESS, _M('Estado da multa', $module), 'main:configuration:fineStatus', null, null, 'iconeFinestatus');
        $confMenu->adicionaMenu($rapMenu);

        $searchMenu = new GMenu('search', _M('Pesquisa', $module), 'iconeSearch');
        $searchMenu->adicionaItemUsuario('gtcSearchableField', A_ACCESS, _M('Campos pesquisáveis', $module), "main:configuration:searchablefield", null, null, 'iconeSearchablefield');
        $searchMenu->adicionaItemUsuario('gtcSearchFormat', A_ACCESS, _M('Formato da pesquisa', $module), "main:configuration:searchformat", null, null, 'iconeSearchformat');
        $searchMenu->adicionaItemUsuario('gtcz3950servers', A_ACCESS, _M('Servidores Z39.50', $module), "main:configuration:z3950servers", null, null, 'iconeZ3950Servers');
        $confMenu->adicionaMenu($searchMenu);

        
        $prefMenu = new GMenu('libraryPreference', _M('Preferência', $module), 'iconeLibraryPreference');

        $preferences = BusinessGnuteca3BusDomain::listForSelect('ABAS_PREFERENCIA', true, false);

        if (is_array($preferences))
        {
            foreach ($preferences as $line => $preference)
            {
                $prefMenu->adicionaItemUsuario('gtcLibraryPreference', A_ACCESS, $preference->label, "$home:libraryPreference&menuItem=" . $preference->key, null, array('function' => 'update', 'tabId' => $preference->key, 'tabName' => $preference->label), 'iconeLibraryPreference');
            }
        }

        $confMenu->adicionaMenu($prefMenu);

        $otherMenu = new GMainMenu('system', _M('Sistema', $module), 'iconePolicy');
        $otherMenu->adicionaItemUsuario('gtcConfigReport', A_ACCESS, _M('Relatório', $module), "main:configuration:configReport", null, null, 'iconeReport');
        $otherMenu->adicionaItemUsuario('gtcPreference', A_ACCESS, _M('Preferência', $module), "$home:preference", null, null, 'iconePreference');
        $otherMenu->adicionaItemUsuario('gtcRequestChangeExemplaryStatusAccess', A_ACCESS, _M('Permissão para alteração de estado', $module), "$home:requestChangeExemplaryStatusAccess", null, null, 'iconeRequestChangeExemplaryStatus');
        $otherMenu->adicionaItemUsuario('gtcFormatBackOfBook', A_ACCESS, _M('Formato da lombada', $module), "main:configuration:formatbackofbook", null, array("function" => "search"), 'iconeFormatbackofbook');
        $otherMenu->adicionaItemUsuario('gtcLabelLayout', A_ACCESS, _M('Modelo de etiqueta', $module), "main:configuration:labelLayout", null, array("function" => "search"), 'iconeLabelLayout');
        $otherMenu->adicionaItemUsuario('basDomain', A_ACCESS, _M('Domínio', $module), "$home:domain", null, null, 'iconeDomain');
        $otherMenu->adicionaItemUsuario('gtcHelp', A_ACCESS, _M('Ajuda', $module), "$home:help", null, null, 'iconeHelp');
        $otherMenu->adicionaItemUsuario('gtcConfigWorkflow', A_ACCESS, _M('Estado do workflow', $module), "main:configuration:workflowStatus", null, null, 'iconeSupplier');
        $otherMenu->adicionaItemUsuario('gtcConfigWorkflow', A_ACCESS, _M('Transição do workflow', $module), "main:configuration:workflowTransition", null, null, 'iconeSupplier');
        
        if( BIOMETRIC_INTEGRATION == DB_TRUE )
        {
            $otherMenu->adicionaItemUsuario('gtcSyncBiometry', A_ACCESS, _M('Sincronizar Biometrias', $module),  "main:configuration:syncBiometry", null, null, 'iconeMaterialMovement');
        }
        
        $otherMenu->adicionaItemUsuario('gtcIntegrationServer', A_ACCESS, _M('Biblioteca virtual', $module), "main:configuration:integrationServer", null, null, 'iconeGroup');
        
        if(VIRTUAL_LIBRARY_INTEGRATION == DB_TRUE)
        {
            $otherMenu->adicionaItemUsuario('gtcIntegrationClient', A_ACCESS, _M('Participantes biblioteca virtual', $module), "main:configuration:integrationClient", null, null, 'iconeInventoryCheck');            
        }
        
        $otherMenu->adicionaItemUsuario('gtcSipEquipament', A_ACCESS, _M('Equipamento SIP', $module),  "main:configuration:sipEquipament", null, null, 'iconePreference');
        $otherMenu->adicionaItemUsuario('gtcScheduleTask', A_ACCESS, _M('Agendamento de tarefa', $module), "main:configuration:scheduletask", null, null, 'iconeScheduleTask');
        $otherMenu->adicionaItemUsuario('gtcDependencyCheck', A_ACCESS, _M('Conferir dependências', $module), "$home:dependencyCheck", null, null, 'iconePreference');
        $otherMenu->adicionaItemUsuario('gtcDocumentType', A_ACCESS, _M('Tipo de documento', $module), "$home:documentType", null, null, 'iconeDocumenttype');

        if (MUtil::getBooleanValue($MIOLO->getConf('gnuteca.debug')) && GOperator::hasSomePermission())
        {
            $otherMenu->adicionaItemUsuario(_M('Teste unitário', $module), 'unitTest', null, null, 'iconeUnitTest');
        }

        $otherMenu->adicionaItemUsuario('gtcFormContent', A_ACCESS, _M('Conteúdo do formulário', $module), "main:administration:formContent", null, null, 'iconeFormContent');
        $otherMenu->adicionaItemUsuario('gtcBackup', A_ACCESS, _M('Cópia de segurança', $module), "$home:backup", null, null, 'iconeBackup');

        $confMenu->adicionaMenu($otherMenu);

        $materialMenu = new GMenu('catalogue', _M('Catalogação', $module), 'iconeCatalogue');
        $materialMenu->adicionaItemUsuario('gtcSpreadsheet', A_ACCESS, _M('Planilha', $module), "main:catalogue:spreadsheet", null, null, 'iconeSpreadsheet');
        $materialMenu->adicionaItemUsuario('gtcTag', A_ACCESS, _M('Etiqueta', $module),  "main:catalogue:tag", null, null, 'iconeTag');
        $materialMenu->adicionaItemUsuario('gtcMarcTagListing', A_ACCESS, _M('Listagem de campos Marc', $module), "$home:marcTagListing", null, null, 'iconeMarcTagListing');
        $materialMenu->adicionaItemUsuario('gtcRulesToCompleteFieldsMarc', A_ACCESS, _M('Regras para completar campos marc', $module), "main:catalogue:rulestocompletefieldsmarc", null, null, 'iconeRulestocompletefieldsmarc');
        $materialMenu->adicionaItemUsuario('gtcLinkOfFieldsBetweenSpreadsheets', A_ACCESS, _M('Relação de campos entre planilhas', $module), "main:catalogue:linkoffieldsbetweenspreadsheets", null, null, 'iconeLinkoffieldsbetweenspreadsheets');
        $materialMenu->adicionaItemUsuario('gtcSeparator', A_ACCESS, _M('Separador', $module), "main:catalogue:separator", null, null, 'iconeSeparator');
        $materialMenu->adicionaItemUsuario('gtcPrefixSuffix', A_ACCESS, _M('Prefixo Sufixo', $module), "main:catalogue:prefixsuffix", null, null, 'iconePrefixsuffix');
        $materialMenu->adicionaItemUsuario('gtcMaterialType', A_ACCESS, _M('Tipo', $module), "$home:materialType", null, null, 'iconeMaterialType');
        $materialMenu->adicionaItemUsuario('gtcMaterialPhysicalType', A_ACCESS, _M('Tipo físico', $module), "$home:materialPhysicalType", null, null, 'iconeMaterialPhysicalType');
        $materialMenu->adicionaItemUsuario('gtcMaterialGender', A_ACCESS, _M('Gênero', $module), "$home:materialGender", null, null, 'iconeMaterialGender');
        $confMenu->adicionaMenu($materialMenu);

        $operatorMenu = new GMenu('operator', _M('Operador', $module), 'iconeOperatorlibraryunit');
        $operatorMenu->adicionaItemUsuario('gtcOperatorLibraryUnit', A_ACCESS, _M('Operador', $module), "main:configuration:operatorlibraryunit", null, null, 'iconeOperatorlibraryunit');
        $operatorMenu->adicionaItemUsuario('gtcOperatorGroup', A_ACCESS, _M('Grupo de operador', $module), "$home:operatorGroup", null, null, 'iconeOperatorGroup');
        
        $confMenu->adicionaMenu($operatorMenu);
        $this->adicionaMenu($confMenu);
    }

}

class GMenu
{
    public $id;
    public $descricao;
    public $icone;
    public $desabilitado;
    public $pai;
    public $itens;
    
    /**
     * Construtor da classe
     * 
     * @param {String} $id
     * @param {String} $descricao
     * @param {String} $icone
     * @param {Boolean} $desabilitado
     */
    public function __construct($id, $descricao, $icone, $desabilitado = false)
    {
        $this->id = $id;
        $this->descricao = $descricao;
        $this->icone = $icone;
        $this->desabilitado = json_encode($desabilitado);
        
        $_SESSION['menuItems'][$id]['descricao'] = $descricao;
        $_SESSION['menuItems'][$id]['icone'] = $icone;
    }
    
    public function getItens()
    {
        foreach( $this->itens as $key => $item )
        {
            if( $item instanceof GMenu )
            {
                $this->itens = array_merge($this->itens, $item->getItens());
            
                unset($this->itens[$key]);
                
            }
            
        }
        
        return $this->itens;
        
    }
    
    /**
     * Adiciona um item ao menu
     * 
     * @param {String} $id Identificador do item
     * @param {String} $descricao Nome amigável
     * @param {String} $acao Ação a ser executada ao clicar
     * @param {String} $item Parâmetro adicional
     * @param {Array} $args Argumentos para passar na requisição
     * @param {String} $icone Classe do icone (ex.: iconeConfiguracao)
     * @param {Boolean} $desabilitado Se este item deve ou não estar desabilitado
     */
    public function adicionaItem($id, $descricao, $acao, $item = null, $args = null, $icone = "", $desabilitado = false)
    {
        $MIOLO = MIOLO::getInstance();
        $module = "gnuteca3";
        
        // Para o boolean ser efetivo, deve ser convertido
        $desabilitado = json_encode($desabilitado);
        
        // Gera a ação
       // $href = $MIOLO->getActionURL($module, $acao, $item, $args);
        if (stripos($acao, 'javascript:') === 0)
        {
            $href = str_replace("'", "\'", $acao);
        }
        else
        {
            $href = $MIOLO->getActionURL($module, $acao, $item, $args);
            $descricao = '<a href="' . $href . '" class = "linkMenu">' . $descricao . '</a>';
            
            $handlers = explode(':', $acao);
            $handler = $handlers[count($handlers) - 1];
            
            $_SESSION['menuItems'][$handler]['descricao'] = $descricao;
            $_SESSION['menuItems'][$handler]['icone'] = $icone;
            $_SESSION['menuItems'][$handler]['pai'] = $this->id;
        }
        
        // Adiciona ao Array
        $this->itens[] = "gMainMenu.addItem('{$id}', '{$descricao}', '{$href}', '{$icone}', {$desabilitado}, '{$this->id}')";
    }
    
    /**
     * Adiciona um item ao menu se o usuário possui permissão
     * 
     * @param {String} $id Identificador do item
     * @param {Integer} $acesso Nível de acesso do usuário
     * @param {String} $descricao Nome amigável
     * @param {String} $acao Ação a ser executada ao clicar
     * @param {String} $item Parâmetro adicional
     * @param {Array} $args Argumentos para passar na requisição
     * @param {String} $icone Classe do icone (ex.: iconeConfiguracao)
     * @param {Boolean} $desabilitado Se este item deve ou não estar desabilitado
     */
    public function adicionaItemUsuario($id, $acesso, $descricao, $acao, $item = null, $args = null, $icone = "", $desabilitado = false)
    {
        $MIOLO = MIOLO::getInstance();
        
        // Se possui a permissão
        if ($MIOLO->perms->checkAccess($id, $acesso))
        {
            $this->adicionaItem($id, $descricao, $acao, $item, $args, $icone, $desabilitado);
        }
        
    }
    
    /**
     * Cria um submenu
     * 
     * @param {GMenu} $menu
     * @param {String} $pai
     */
    public function adicionaMenu($menu)
    {
        if( $menu instanceof GMenu )
        {
            if ( count($menu->getItens()) > 0 )
            {
                $_SESSION['menuItems'][$menu->id]['descricao'] = $menu->descricao;
                $_SESSION['menuItems'][$menu->id]['icone'] = $menu->icone;
                $_SESSION['menuItems'][$menu->id]['pai'] = $this->id;
                
                $this->itens[] = "gMainMenu.addSubMenu('{$menu->id}', '{$menu->descricao}', '{$menu->icone}', {$menu->desabilitado}, '{$this->id}')";
                $this->itens = array_merge($this->itens, $menu->getItens());
            }
            
        }
        
    }
    
        
}

?>
