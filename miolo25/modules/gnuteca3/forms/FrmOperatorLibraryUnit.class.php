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
 * OperatorLibraryUnit form
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Bruno E. Fuhr [bruno@solis.coop.br]
 *
 * @since
 * Class created on 06/01/2009
 *
 **/
$MIOLO->uses('db/BusOperatorLibraryUnit.class.php', 'gnuteca3');
class FrmOperatorLibraryUnit extends GForm
{
    public $MIOLO;
    public $module;
    public $busLibraryUnit;
    /** @var BusinessAdminUser **/
    public $busUser;
    public $busGroupUser;
    public $busGroup;
    public $busOperatorLibraryUnit;
    public $_operatorLibrary;

    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
        $this->setAllFunctions('OperatorLibraryUnit', 'operator', array('idUser', 'operator'), 'operator');
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busUser = $this->MIOLO->getBusiness('admin', 'user');
        
        $this->busOperatorLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusOperatorLibraryUnit');
        
        $this->busGroupUser = $this->MIOLO->getBusiness('admin', 'groupuser');
        $this->busGroup = $this->MIOLO->getBusiness( 'admin', 'group' );
        parent::__construct();

       
        $this->page->addJsCode("
                            checkDisable_libraryUnit = function()
                            {
                                document.getElementById('operatorLibrary').style.display = ( document.getElementById('allLibraries').checked ) ? 'none' : 'block';
                            }");

        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('group');
            GRepetitiveField::clearData('operatorLibrary');
        }
    }


    public function mainFields()
    {

        if ( $this->function != 'insert' )
        {
            $fields[] = new MTextField( 'idUser', '', _M( 'Código', $module ), FIELD_ID_SIZE, null, null, true );
        }
        
        if ( PERSON_IS_A_OPERATOR == DB_FALSE )
        {
            $fields[] = new MTextField( 'fullname', '', _M( 'Nome', $module ), FIELD_DESCRIPTION_SIZE );
            $fields[] = $login = new MTextField( 'username', '', _M( 'Login', $module ), FIELD_DESCRIPTION_SIZE, null, null, $this->function == 'update');
            $fields[] = $operator = new MTextField('operator');
            $operator->addStyle('display', 'none');
            
            if ( $this->function == 'update' )
            {
                $login->setReadOnly(true);
            }
        }
        else
        {
            //FIXME substituir por um GPersonLookup
            $lookup[] = new MTextField( 'fullname', '', null, FIELD_DESCRIPTION_SIZE, '', null, true);
            $fields[] = $userName = new GLookupField('username', null, _M('Login', $module ), 'personIsOperator', $lookup );
            
            if ( $this->function == 'update' )
            {
                $userName->lookupTextField->setReadOnly(true);
            }
        }
            
        $fields[] = new MPasswordField( 'password', '', _M( 'Senha', $admin ), FIELD_DESCRIPTION_SIZE, $this->function == 'update' ? _M('Quando não preenchido, mantém a senha anterior', $this->module) : null);
        $fields[] = new MSeparator('<br>');

        //repetitive de grupo
        $filter = new stdClass();
        $filter->idModule = 'gnuteca3';
        $grouList = $this->busGroup->listByFilters($filter)->chunkResult(); //lista os grupos do módulo gnuteca3
        asort($grouList); //ordena os grupos pelo descrição
        
        $fldGroup[] = new GSelection('groupId', '', _M('Grupo', $this->module), $grouList );
        $validsG[] = new GnutecaUniqueValidator('groupId');
        $validsG[] = new MRequiredValidator('groupId');

        $group = new GRepetitiveField('group', _M('Grupo de operador', $this->module), NULL, NULL, array('edit', 'remove'));
        $group->setFields( $fldGroup );
        $group->setValidators( $validsG );
       
        $columns[] = new MGridColumn( _M('Grupo',    $this->module), 'left', true, null, false, 'groupId' );
        $columns[] = new MGridColumn( _M('Grupo de operador',    $this->module), 'left', true, null, true, 'groupDesc' );
        $group->setColumns($columns);

        $fields[] = $group;
        $fields[] = new MSeparator('<br>');

        $fields[] = $allLibraries = new MCheckBox('allLibraries', DB_TRUE, _M('Todas bibliotecas', $this->module));
        $allLibraries->setAttribute('onchange', 'checkDisable_libraryUnit()');

        $columns = array(
            new MGridColumn(_M('Unidade de biblioteca', $this->module), 'left', true, null, true,  'libraryName'),
            new MGridColumn(_M('Código da biblioteca', $this->module), 'left', true, null, false, 'libraryUnitId')
        );

        $flds[] = new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module) , $this->busLibraryUnit->listLibraryUnit());
        $flds[] = new MHiddenField('libraryName');
        $valids[] = new MRequiredValidator('libraryUnitId');
        $valids[] = new GnutecaUniqueValidator('libraryUnitId');

        $this->_operatorLibrary = new GRepetitiveField('operatorLibrary', _M('Operador da biblioteca', $this->module));
        $this->_operatorLibrary->setFields( $flds );
        $this->_operatorLibrary->setColumns($columns);
        $this->_operatorLibrary->setValidators($valids);
        $fields[] = $this->_operatorLibrary;

        $this->setFields($fields);

        $validators[] = new MRequiredValidator('fullname', _M('Nome', $this->module));
        $validators[] = new MRequiredValidator('username', _M('login', $this->module));
        $validators[] = new MRequiredValidator('group');

        if ( $this->function != 'update' )
        {
            $validators[] = new MRequiredValidator('password');
        }

        $this->setValidators($validators);
    }

    /**
     * Método que salva na base o operador do sistema
     *
     * @param object FormData com dados do form
     * @return boolean
     */
    public function saveUser($data = null)
    {
        if ( !$data )
        {
            $data = $this->getData();
        }

        $data->nickname = $data->username; //seta o login
        
        if ( $this->function == 'insert' )
        {
            //busca na base um operador com o mesmo username
            $operator = $this->busUser->getByLogin($data->username);

            //verifica se usuário já existe no miolo
            if( strlen($operator->nickname) == 0 )
            {
                $data->password = md5($data->password); //faz o md5 da senha
                $this->busUser->setData($data); //seta os dados no business
                
                if ( PERSON_IS_A_OPERATOR == DB_FALSE )
                {
                    //$id = $this->busUser->getNewId(); //obtém id da tabela de sequência
                    //Método adicionado no Gnuteca 3.8
                    $id = $this->busOperatorLibraryUnit->getUserId();
                    
                }
                else
                {
                    $id = $data->nickname; //id será igual ao login
                }

                $this->busUser->idUser = $id; //seta o idUser
                $ok = $this->busUser->insert(); //insere operador
            }
            else
            {
                $ok = true; //necessário para editar os grupos
                //grupos do usuário
                $groupUser = $this->business->parseGroupsOfUser(false, $this->busUser->idUser); //obtém os grupos já existentes
                $oldGroups = BusinessGnuteca3BusOperatorLibraryUnit::parseIdOfArrayGroups($groupUser); //array com ids dos grupos que não pertencem ao módulo gnuteca3
            }
        }
        elseif ( $this->function == 'update' )
        {
            $bus = $this->busUser->getById(MIOLO::_REQUEST('idUser')); //obtém a pessoa
            $groupUser = $this->business->parseGroupsOfUser(false, $this->busUser->idUser); //obtém os grupos já existentes, é necessário pois preciso de todos os grupos para editar os grupos do operador
            $oldGroups = BusinessGnuteca3BusOperatorLibraryUnit::parseIdOfArrayGroups($groupUser); //array com ids dos grupos que não pertencem ao módulo gnuteca3
            
            if ( strlen($data->password) == 0 )
            {
                $data->password = $bus->password; //seta a mesma senha que já está na base
            }
            else
            {
                $data->password = md5($data->password); //faz o md5 da senha
            }

            $this->busUser->setData($data); //seta os dados no business
            $ok = $this->busUser->update(); //atualiza os dados
        }
            
        /**FIXME: necessário pesquisa filtrar por idUser. Este form é específico, pois faz um merge das telas de operador do miolo e
        do Gnuteca. No miolo, a chave primária é o idUser, pois vários operadores podem ter o mesmo login. E no Gnuteca (gtcoperadorlibraryunit)
        o operador é referenciado através do login', foi necessário alguns "jeitinhos" para que o Gnuteca obtesse o operador através do login."
         */
        $_REQUEST['idUser'] = $this->busUser->idUser;
        $_REQUEST['operator'] = $data->username;
        
        //obtém os grupos
        $groups = BusinessGnuteca3BusOperatorLibraryUnit::parseIdOfArrayGroups($data->group); //grupos adicionados na repetitivefield do form
        
        //trata os grupos, justando com os grupos que já existem na base
        $newGroups = array();
        if ( (strlen(implode('', $groups)) > 0 ) && (strlen(implode('', $oldGroups)) > 0) )
        {
            $newGroups = $groups;
        }
        elseif ( strlen(implode('', $groups)) > 0  )
        {
            $newGroups = $groups;
        }
        elseif ( strlen(implode('', $oldGroups)) > 0 )
        {
            $newGroups = $oldGroups;
        }
        
        //atualiza os grupos
        if ( $ok )
        {
            $ok = $this->busGroupUser->updateUserGroups($this->busUser->idUser, array_unique($newGroups));
        }

        return $ok;
    }

    public function tbBtnSave_click($sender = null)
    {
        $this->mainFields();
    	$data = $this->getData();
        $data->operator = $data->username;
        
        if ( is_array($data->operatorLibrary) )
        {
            foreach( $data->operatorLibrary as $i=>$value )
            {
                $data->operatorLibrary[$i]->operator = $data->username;
            }
        }
        //valida os campos
        if ( !$this->validate($data) )
        {
            return false;
        }

    	if ( $data->allLibraries == DB_TRUE )
    	{
    		//Save data with null libraryUnitId
    		$tmp = $data;
    		unset($tmp->libraryUnitId);
            unset($tmp->operatorLibrary);
    		$data->operatorLibrary[] = $tmp;
    	}

        //salva operador do sistema
        $this->saveUser($data);
        
    	parent::tbBtnSave_click($sender, $data);
    }
    
    public function loadFields()
    {
        //carrega os dados vindos
    	$bus = $this->busUser->getById( MIOLO::_REQUEST('idUser') );
        $bus->username = $bus->login;
        $bus->password = null;
    	$this->setData($bus);

        //seta os dados da repetitive field de grupos
        GRepetitiveField::setData($this->business->parseGroupsOfUser(true, $this->busUser->idUser ), 'group');

        //obtém as bibliotecas
        if ( MIOLO::_REQUEST('libraryUnits') != DB_FALSE )
        {
            $this->business->getOperatorLibraryUnit($bus->username);
            
            //Se tiver operador e nao tiver unidade de biblioteca definida
            if ( $this->business->operatorLibrary[0]->operator && !$this->business->operatorLibrary[0]->libraryUnitId )
            {
                //O operador tem acesso a todas unidades de biblioteca.
                $this->allLibraries->checked = true;
            }
            else
            {
                //Se nao tiver operador definido, entao ele nao tem acesso a nenhuma unidade de biblioteca.
                GRepetitiveField::setData($this->business->operatorLibrary, 'operatorLibrary');
                $this->allLibraries->checked = false;                
            }

            $this->page->onload('checkDisable_libraryUnit()');
        }
    }

     /**
     * Trata os dados do grupo
     *
     * @param $data
     * @return dados tratados
     */
    public function groupParse($data)
    {
        if (is_array($data))
        {
            $arr = array();
            foreach ($data as $val)
            {
                $arr[] = $this->groupParse($val);
            }

            return $arr;
        }
        else if (is_object($data))
        {
        	$groups = $this->busGroup->listAll()->chunkResult();
            $data->groupDesc = $groups[$data->groupId];

            return $data;
        }
    }

     /**
     * Trata os dados da uniade de biblioteca
     *
     * @param $data
     * @return dados tratados
     */
    public function operatorLibraryParse($data)
    {
        if (is_array($data))
        {
            $arr = array();
            foreach ($data as $val)
            {
                $arr[] = $this->groupParse($val);
            }

            return $arr;
        }
        else if (is_object($data))
        {
        	$data->libraryName = $this->busLibraryUnit->getLibraryUnit($data->libraryUnitId)->libraryName;
                    
            return $data;
        }
    }


    public function addToTable($args, $forceMode = FALSE)
    {
    	$item = $args->GRepetitiveField;
    	switch($item)
    	{
    		case 'group':
    			$args = $this->groupParse($args);
    			break;

            case 'operatorLibrary':
                $args = $this->operatorLibraryParse($args);
                break;
    	}

    	($forceMode) ? parent::forceAddToTable($args) : parent::addToTable($args);
    }


    public function forceAddToTable($args)
    {
        $this->addToTable($args, TRUE);
    }
}
?>
