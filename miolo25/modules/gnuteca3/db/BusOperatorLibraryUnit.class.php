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
 * OperatorLibraryUnit business
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 * Luiz Gregory Filho   [luiz@solis.coop.br]
 * Moises Heberle       [moises@solis.coop.br]
 *
 * @since
 * Class created on 06/01/2009
 *
 **/
class BusinessGnuteca3BusOperatorLibraryUnit extends GBusiness
{
    public $MIOLO;
    
    public $idUser;
    public $operator;
    public $libraryUnitId;
    public $operatorLibrary;
    public $allLibraries;
    public $nameS;
    
    public $operatorS;
    public $libraryUnitIdS;

    private $busUser,
            $busGroup,
            $busGroupUser;
    

    public function __construct()
    {
        parent::__construct('gtcOperatorLibraryUnit', 'operator', 'libraryUnitId');
        $this->MIOLO    = MIOLO::getInstance();
        $this->busUser = $this->MIOLO->getBusiness('admin', 'user');
        $this->busGroup = $this->MIOLO->getBusiness('admin', 'group');
        $this->busGroupUser = $this->MIOLO->getBusiness('admin', 'groupuser');
    }


    public function getOperatorLibraryUnit($operator)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->id);
        $this->setWhere('operator = ?');
        $rs = $this->query($this->select(array($operator)), true);
        $data = $rs[0];
        
        $this->clear();
        $this->setTables('gtcOperatorLibraryUnit A
                LEFT JOIN gtcLibraryUnit         B
                       ON (A.libraryUnitId = B.libraryUnitId)');
        $this->setColumns('A.operator,
                           A.libraryUnitId,
                           B.libraryName');
        $this->setWhere('A.operator = ?');
        $sql = $this->select(array($operator));
        $data->operatorLibrary = $this->query($sql, true);
        
        $this->setData($data);
        return $data;
    }

    /**
     * Busca os logins de operadores do Gnuteca
     *
     * @param (int) $operador
     * @return array com operadores
     */
    private function searchOperatorByLibraryUnit($operador=null)
    {
        if ( $this->libraryUnitIdS )
        {
            $args[] = $this->libraryUnitIdS;
            $this->setWhere('libraryUnitId = ?');
        }

        if ($operador)
        {
            $this->setWhere('operator = ?');
            $args[] =  $operador ;
        }

        $this->setColumns('DISTINCT operator');
        $this->setTables($this->tables);

        $sql = $this->select($args);
        $rs = $this->query( $sql );

        $result = array();
        if ( is_array($rs) )
        {
            foreach( $rs as $i=>$value )
            {
                $result[] = $value[0];
            }
        }

        return $result;
    }

    /**
     * Busca as unidades de biblioteca do operador
     *
     * @param (array) operadores
     * @return (array) com unidades
     */
    public function searchLibraryUnitForOperator($operadorS=null)
    {
        $operadorArray = array();
        if ($operadorS)
        {
            foreach ($operadorS as $val)
            {
                $operator = $operadorArray[] = $val;
                $this->clear();
                $this->setTables('gtcOperatorLibraryUnit  A
                        LEFT JOIN gtcLibraryUnit B
                               ON (A.libraryUnitId = B.libraryUnitId)');

                $this->setColumns('B.libraryUnitId, B.libraryName');

                $this->setWhere('A.operator = ?');

                $query = $this->query($this->select(array($val)));


                $librariesId = $librariesDesc = array();

                if ($query)
                {
                    foreach ($query as $val)
                    {
                        $librariesId[] = $val[0];
                        $librariesDesc[] = $val[1];
                    }

                    $data[] = array($operator, implode("<br/>", $librariesId), implode("<br/>", $librariesDesc));
                }
            }
        }

        return $data;
    }
    
    /**
     * Busca por sequencia de miolo_user_iduser_seq
     * 
     * @return <type>
     */
    public function getUserId()
    {
        $MIOLO = MIOLO::getInstance();
        
        $db = $MIOLO->getDatabase('admin');
        $result = $db->query("SELECT nextval('miolo_user_iduser_seq')");
        
        return $result->result[0][0];
    }
    
    /**
     * Busca os operadores
     * 
     * @return <type>
     */
    public function searchOperatorLibraryUnit()
    {
        $this->clear();
        $operator = new GOperator();
        
        if ( $this->libraryUnitIdS )
        {
            //busca operadores da gtcoperator
            $operatorS = $this->searchOperatorByLibraryUnit($this->operatorS); //obtém os logins através da unidade de biblioteca

            if ( !$operatorS )
            {
                return null;
            }
            
            $gtcData = $this->searchLibraryUnitForOperator($operatorS); //obtém unidades de biblioteca agrupadas do login de operador

            $mioloData = $operator->searchOperator($operatorS, $this->nameS); //foi chamado do GOperator porque preciso passar um array de login
        }
        else
        {
            $mioloData = $operator->searchOperator($this->operatorS, $this->nameS); //foi chamado do GOperator porque preciso passar um array de login

            if ( is_array($mioloData) )
            {
                $operatorS = array();
                foreach ( $mioloData as $i=>$value )
                {
                    $operatorS[] = $value[1];
                }
            }

            $gtcData = $this->searchLibraryUnitForOperator($operatorS); //obtém unidades de biblioteca agrupadas do login de operador
        }

        //mescla os dados vindos do Miolo e do Gnutea
        $newData = array();
        foreach ( $mioloData as $i=>$value )
        {
            foreach ( $gtcData as $k => $value2 )
            {
                if ( $value[1] == $value2[0] )
                {
                    $intern = array();
                    $intern[] = $value[0]; //iduser
                    $intern[] = $value[1]; //login
                    $intern[] = $value[2]; //nome
                    $intern[] = $value2[1];
                    $intern[] = $value2[2];

                    $newData[$i] = $intern;
                    
                    break;
                }
            }

            //caso não achar operador no Gnuteca
            if ( !is_array($newData[$i]) )
            {
                $intern = array();
                $intern[] = $value[0]; //iduser
                $intern[] = $value[1]; //login
                $intern[] = $value[2]; //nome
                $intern[] = DB_FALSE;
                $intern[] = _M('Sem unidade de biblioteca', $this->module);
              
                $newData[$i] = $intern;
            }

        }

        return $newData;
    }


    public function insertOperatorLibraryUnit()
    {
        //Se tiver todas unidades
        if ( MUtil::getBooleanValue($this->allLibraries) )
        {
            //Remove todos registros do operador na gtcOperatorLibraryUnit para 
            //não deixar registros duplicados
            $this->deleteOperatorLibraryUnit($this->idUser, null, true);
        }
        
    	$this->clear();
    	$this->setTables('gtcOperatorLibraryUnit');
    	$this->setColumns(array('operator', 'libraryUnitId'));

        $ok = true;
        if ( is_array($this->operatorLibrary) )
        {
            foreach ($this->operatorLibrary as $data)
            {
                if ($data->removeData)
                {
                    continue;
                }

                $sql = $this->insert(array($data->operator, $data->libraryUnitId));
                $ok  = $this->execute($sql);
            }
        }
        
    	return $ok;
    }


    public function updateOperatorLibraryUnit()
    {
        $this->deleteOperatorLibraryUnit($this->idUser, null, true);
        return $this->insertOperatorLibraryUnit();
    }

    /**
     * Faz a exclusão do operador o miolo e no gnuteca
     * 
     * @param int $idUser chave primária do miolo
     * @param String $operator login do operador
     * @param boolean $updateOperator
     * @return boolean 
     */
    public function deleteOperatorLibraryUnit($idUser, $operator, $updateOperator=false)
    {
        $user = $this->busUser->getById( $idUser );

        //somente quando chamado pelo excluir, pois este método também é chamado na edição de operadores
        if ( !$updateOperator )
        {
            //verifica se usuário possui vínculo que não é do Gnuteca
            $groups = $this->parseGroupsOfUser(false);

            $externalGroup = array();

            if ( is_array($groups) )
            {
                foreach( $groups as $key => $group )
                {
                    if ( $group->module != 'gnuteca3' )
                    {
                        $externalGroup[] = $group->groupId;
                    }
                }
            }

            //testa se tem vínculo externo
            if ( strlen(implode('', $externalGroup)) > 0)
            {
                //atualiza os vínculos do operador passando somente os grupos externos
                $ok = $this->busGroupUser->updateUserGroups($this->busUser->idUser, $externalGroup);
            }
            else
            {
                $this->busUser->delete(); //remove operador do miolo
            }
        }
        
        return $this->autoDelete($user->login); //remove operador da gtcOperatorLibraryUnit
    }
    
    /**
     * Obtém os grupos do operador e trata o array de dados
     * 
     * @param obtém apenas grupos do módulo Gnuteca3
     * @return array com dados
     */
    public function parseGroupsOfUser($onlyModule = true, $idUser = null)
    {
        if ( $idUser )
        {
            $this->busUser->getById($idUser); //popula o idUser
        }
        
        //grupos do usuário
        $groupsUser = $this->busUser->getArrayGroups();
        $groups = $this->busGroup->listAll()->result;

        $values = array();
         
        if ( is_array($groupsUser) && is_array($groups) )
        {
            $count = 0;
            foreach ( $groups as $key => $group )
            {
                foreach ( $groupsUser as $grpUser )
                {
                    if ( $group[1] == $grpUser ) //compara as descrições dos grupos. Ex: GTC_ROOT == GTC_ROOT
                    {
                        $data = new stdClass();
                        $data->groupId = $group[0];
                        $data->groupDesc = $group[1];
                        $data->module = $group[2];
                        
                        //só adiciona no array os grupos que forem do módulo gnuteca3
                        if ( $onlyModule )
                        {
                            if ( $data->module == 'gnuteca3' )
                            {
                                $values[$count] = $data;
                            }
                        }
                        else
                        {
                            $values[$count] = $data;
                        }
                    }
                }

                $count ++;
            }
        }
        
        return $values;
    }
    
    /**
     * Método que cria um array somente com o id dos grupos
     * 
     * @param array de objetos com grupos (dados preparados para a repetitivefield)
     * @return array com ids
     */
    public static function parseIdOfArrayGroups($arrayGroups)
    {
        $groups = array();
        
        if ( is_array($arrayGroups) )
        {
            foreach( $arrayGroups as $key=> $group )
            {
                //testa se dado foi apagado (quando dados vem da repetitivefield)
                if ( !$group->removeData )
                {
                    $groups[] = $group->groupId;
                }
            }
        }
        
        return $groups;
    }
}
?>
