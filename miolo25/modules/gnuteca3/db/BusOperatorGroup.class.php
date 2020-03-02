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
 * This file handles the connection and actions for basConfig table
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 *
 * @since
 * Class created on 03/05/2011
 *
 **/

class BusinessGnuteca3BusOperatorGroup extends GBusiness
{
    public $MIOLO,
           $idGroup,
           $groupName,
           $group,
           $access,
           $groupNameS;
    
    private $busGroup,
            $dbAcess;


    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->busGroup = $this->MIOLO->getBusiness('admin', 'group');
        $this->busAccess = $this->MIOLO->getBusiness('admin', 'access');
        parent::__construct();
    }

    /**
     * Obtém grupo de operador
     * 
     * @param (integer) $idGroup
     * @return BusinessGnuteca3BusOperatorGroup
     */
    public function getOperatorGroup($idGroup)
    {
        $this->busGroup->getById( $idGroup ); //obtém grupo
        $this->setData( $this->busGroup ); //seta os dados do business grupo nesse business
        $this->groupName = $this->group;
        
        $perms = $this->MIOLO->getPerms()->perms;
        $access = $this->busGroup->listAccessByIdGroup( $this->idGroup )->result;

        if ( is_array($access) )
        {
            $newAccess = array();
            foreach( $access as $i=> $value )
            {
                $newAccess[$value[0]]->idTransaction = $value[0];
                
                foreach( $perms as $keyPerm => $perm)
                {
                    if ( $keyPerm == $value[1] )
                    {
                        $newAccess[$value[0]]->$perm = DB_TRUE;
                    }
                }
            }
        }

        $this->access = $newAccess;

        return $this;
    }

    /**
     * Busca grupos de operadores
     *
     * @return (array) de dados
     */
    public function searchOperatorGroup()
    {
        //FIXME: filtrar pelo módulo gnuteca3
        
        $filter = new stdClass();
        $filter->group = strtoupper($this->groupNameS);
        $filter->idModule = 'gnuteca3';
        
        return $this->busGroup->listByFilters( $filter )->result;
    }

    /**
     * Insere grupo
     * 
     * @return (boolean) true caso for sucesso 
     */
    public function insertOperatorGroup()
    {
        //FIXME faz setData manual, pois o setData do group chama um método desnecessário
        $this->busGroup->idGroup = $this->idGroup;
        $this->busGroup->group = $this->groupName;
        $this->busGroup->idModule = 'gnuteca3';
        
        $this->busGroup->save();

        $idGroup = $this->busGroup->getId();

        if ( ($idGroup) && ( is_array($this->access)) )
        {
            $this->busAccess->updateGroupAccess( $idGroup, $this->access );
        }

        return $idGroup ? true : false;
    }

    /**
     * Atualiza operador
     * 
     * @return BusinessGnuteca3BusOperatorGroup
     */
    public function updateOperatorGroup()
    {
        //FIXME faz setData manual, pois o setData do group chama um método desnecessário
        $this->busGroup->idGroup = $this->idGroup;
        $this->busGroup->group = $this->groupName;
        $this->busGroup->idModule = 'gnuteca3';
        
        $this->busGroup->setPersistent( true );
        $this->busGroup->save();

        if ( is_array($this->access) )
        {
            $this->busAccess->updateGroupAccess( $this->idGroup, $this->access );
        }

        return $this->idGroup ? true : false;
    }

    /**
     * Deleta grupo
     *
     * @param (integer) $idGroup
     * @return (boolean)
     */
    public function deleteOperatorGroup($idGroup)
    {
        //apaga o grupo
        $this->busGroup->getById( $idGroup );
        $this->busGroup->delete();

        //apaga as permissões
        $this->busAccess->deleteAccessByGroup( $idGroup );

        return true;
    }
    
    /**
     * Lista todas as transações trazendo o id e nome
     * 
     * @return (array) de transações 
     */
    public static function listTransactions()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('db/BusMaterial.class.php', 'gnuteca3');
        
        $busTransaction = $MIOLO->getBusiness('admin', 'transaction');
        $transactions =  $busTransaction->listByModule('gnuteca3')->result; //lista as transações do módulo gnuteca3
        
        //ordernação do vetor
        $newValue = array();
        if ( is_array($transactions) )
        {
            foreach( $transactions as $key => $transaction )
            {
                // Traz a transação caso não tenha descrição de transação.
                $newValue[$transaction[0]] = $transaction[3] ? $transaction[3] : $transaction[1];
                $orderValue[$transaction[0]] = BusinessGnuteca3BusMaterial::prepareTopographicIndex($newValue[$transaction[0]]);
            }
        }
        
        $transactions = $newValue;
        array_multisort( $orderValue, $transactions ); //ordena o array, mas acaba destruindo as chaves
        
        //coloca a chave correta novamente
        $newTransaction = array();
        foreach( $transactions as $key => $value )
        {
            foreach($newValue as $key2 => $value2 )
            {
                if ( $value == $value2 )
                {
                    $newTransaction[$key2] = $value2;
                }
            }
        }
        
        return $newTransaction;
    }

}
?>
