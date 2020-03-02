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
 * This file handles the connection and actions for policy table
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 04/08/2008
 *
 **/
class BusinessGnuteca3BusPolicy extends GBusiness
{
    public $colsNoId;
    public $busLibraryUnit;
    public $busPrivilegeGroup;
    public $busMaterialGender;
    public $update_repeat;

    public $privilegeGroupId;
    public $linkId;
    public $materialGenderId;
    public $loanDate;
    public $loanDays;
    public $forecastDate, $loanForecastDate; //calculted in getPolicy
    public $loanLimit;
    public $renewalLimit;
    public $reserveLimit;
    public $fineValue;
    public $penaltyByDelay;
    public $daysOfWaitForReserve;
    public $renewalWebLimit;
    public $renewalWebBonus;
    public $reserveLimitInInitialLevel;
    public $daysOfWaitForReserveInInitialLevel;
    public $momentaryFineValue;
    
    public $additionalDaysForHolidays;
    public $materialGenderList;
    public $linkList;
    public $privilegeGroupIdS;
    public $linkIdS;
    public $materialGenderIdS;
    public $beginLoanDateS;
    public $endLoanDateS;
    public $loanDaysS;
    public $loanLimitS;
    public $momentaryFineValueS;



    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables   = 'gtcPolicy';
        $this->colsNoId = 'loanDate,
                           loanDays,
                           loanLimit,
                           renewalLimit,
                           reserveLimit,
                           fineValue,
                           penaltyByDelay,
                           daysOfWaitForReserve,
                           renewalWebLimit,
                           renewalWebBonus,
                           additionalDaysForHolidays,
                           reserveLimitInInitialLevel,
                           daysOfWaitForReserveInInitialLevel,
                           momentaryFineValue';
        $this->colsId   = $this->id = 'privilegeGroupId,
                           linkId,
                           materialGenderId';
        $this->columns  = $this->colsId . ',' . $this->colsNoId;
        $MIOLO = MIOLO::getInstance();
        $this->MIOLO = null;
        $this->busLibraryUnit 		= $MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busPrivilegeGroup 	= $MIOLO->getBusiness($this->module, 'BusPrivilegeGroup');
        $this->busMaterialGender   	= $MIOLO->getBusiness($this->module, 'BusMaterialGender');
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listPolicy()
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * ATTENTION: this funcition calculates the forecast Date automaticaly, but you need to verify it with checkHolidayDate.
     *
     * @param $privilegeGroupId     the code of the privilege group.
     * @param $linkId               the code of link/bond.
     * @param $materialgenderId       the code of material gender.
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getPolicy( $privilegeGroupId, $linkId, $materialGenderId, $return = FALSE, $forceLoanDays = FALSE)
    {
        $data[] = $privilegeGroupId;
        $data[] = $linkId;
        $data[] = $materialGenderId;
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('privilegeGroupId = ?');
        $this->setWhere('linkId = ?');
        $this->setWhere('materialGenderId = ?');
        $sql = $this->select($data);
        $rs  = $this->query($sql, true);
        $rs  = $rs[0]; //pega só o primeiro para retornar um objeto não um array
        //calculte forecast date
        if ($rs)
        {
            if ((!$rs->loanDate) || ($forceLoanDays))
            {
                $rs->forecastDate = $this->policyForecastDateCalculate($rs->loanDays);
            }
            else
            {
                $loanDate = new GDate($rs->loanDate);
                $rs->forecastDate = $loanDate->getDate(GDate::MASK_DATE_USER);
            }
        }
        if ( !$return )
        {
            $this->setData( $rs );
            return $this;
        }
        else
        {
            return $rs;
        }
    }


    /**
     * Este metodo soma os dias de emprestimos dependo da politica.
     *
     * @param number day $loanDays
     * @return date
     */
    public function policyForecastDateCalculate($loanDays)
    {
        $now = null;

        // SE RECEBER A DATA PREVISTA DE RENOVAÇÂO, TENTA INCREMENTA-LA
        if( ! is_null($this->loanForecastDate) && defined("DAYS_BEFORE_DATE_OF_RETURN_CAN_INCREASE") && is_numeric(DAYS_BEFORE_DATE_OF_RETURN_CAN_INCREASE) && DAYS_BEFORE_DATE_OF_RETURN_CAN_INCREASE > 0 )
        {
            $hoje = GDate::now();
            $loanForecastDays = new GDate($this->loanForecastDate);
            $loanForecastDays->addDay(-DAYS_BEFORE_DATE_OF_RETURN_CAN_INCREASE);

            // VERIFICA SE ESTA NO PRASO PERMITIDO PARA INCREMENTAR O FORECASTDATE
            if( $hoje->compare($loanForecastDays, '>=') )
            {
                $now = new GDate($this->loanForecastDate);
            }
        }

        if( is_null($now) )
        {
            $now = GDate::now();
        }

        $now->addDay($loanDays);

        return $now->getDate(GDate::MASK_DATE_USER);
    }



    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     **/
    public function searchPolicy()
    {
        $this->clear();

        $columns = 'A.privilegegroupid,
                    B.description,
                    A.linkid,
                    C.description,
                    A.materialgenderid,
                    D.description,
                    A.loanDate,
                    A.loanDays,
                    A.loanLimit,
                    A.renewalLimit,
                    A.fineValue,
                    A.momentaryFineValue,
                    A.penaltyByDelay,
                    A.reserveLimit,
                    A.daysOfWaitForReserve,
                    A.reserveLimitIninitialLevel,
                    A.daysOfWaitForReserveIninitialLevel,
                    A.renewalWebLimit,
                    A.renewalWebBonus,
                    A.additionalDaysForHolidays';

        if ( !empty($this->privilegeGroupIdS) )
        {
            $this->setWhere('A.privilegegroupid = ?');
            $data[] = $this->privilegeGroupIdS;
        }

        if ( !empty($this->linkIdS) )
        {
            $this->setWhere('A.linkid = ?');
            $data[] = $this->linkIdS;
        }

        if ( !empty($this->materialGenderIdS) )
        {
            $this->setWhere('A.materialgenderid = ?');
            $data[] = $this->materialGenderIdS;
        }

        if (!empty($this->beginLoanDateS) )
        {
            $this->setWhere('date(A.loanDate) >= ?');
            $data[] = $this->beginLoanDateS;
        }

        if (!empty($this->endLoanDateS) )
        {
            $this->setWhere('date(A.loanDate) <= ?');
            $data[] = $this->endLoanDateS;
        }

        if ( !empty($this->loanDaysS) )
        {
            $this->setWhere('A.loanDays = ?');
            $data[] = $this->loanDaysS;
        }

        if ( !empty($this->loanLimitS) )
        {
            $this->setWhere('A.loanLimit = ?');
            $data[] = $this->loanLimitS;
        }
        
        if ( !empty($this->momentaryFineValueS) )
        {
            $this->setWhere('A.momentaryFineValue = ?');
            $data[] = $this->momentaryFineValueS;
        }        

        $this->setColumns($columns);
        $this->setTables('
                        gtcPolicy           A
            LEFT JOIN   gtcprivilegegroup   B
                   ON   (A.privilegegroupid = B.privilegegroupid)
            LEFT JOIN   baslink             C
                   ON   (A.linkid = C.linkid)
            LEFT JOIN   gtcmaterialgender     D
                   ON   (A.materialgenderid = D.materialgenderid)
        ');
        $this->setOrderBy('A.privilegegroupid, A.linkId, A.materialgenderId');
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        return $rs;
    }


    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertPolicy()
    {
        foreach ( $this->materialGenderList as $materialData )
        {
            foreach ( $this->linkList as $linkData )
            {
                $this->linkId         = $linkData;
                $this->materialGenderId = $materialData;
                $getPolicy = $this->getPolicy($this->privilegeGroupId, $this->linkId, $this->materialGenderId , true );

                if ( !$getPolicy )
                {
                    $this->clear();
                    $this->setColumns( $this->colsId . ',' . $this->colsNoId);
                    $this->setTables($this->tables);
                    $sql = $this->insert( $this->associateData(   $this->colsId . ',' . $this->colsNoId) );
                    $rs  = $this->execute($sql);
                }
                else if ($this->update_repeat == DB_TRUE)
                {
                    $rs = $this->updatePolicy();
                }
                else
                {
                	$rs = true;
                }
            }
        }
        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updatePolicy()
    {
        $data = $this->associateData( $this->colsNoId . ',' . $this->colsId );

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('privilegegroupid = ?');
        $this->setWhere('linkid = ?');
        $this->setWhere('materialgenderid = ?');
        $sql = $this->update($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $moduleConfig (string): Primary key for deletion
     * @param $parameter (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deletePolicy($privilegeGroupId, $linkId, $materialGenderId)
    {
        $data = array(
            $privilegeGroupId,
            $linkId,
            $materialGenderId
        );

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('
                privilegegroupid = ?
            AND linkid = ?
            AND materialgenderid = ?
        ');
        $sql = $this->delete($data);
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Get constants for a specified module
     *
     * @param $moduleConfig (string): Name of the module to load values from
     *
     * @return (array): An array of key pair values
     *
     **/
    public function getPrivilegeGroupValues($privilegeGroupId)
    {
        $this->clear();

        $columns = 'A.privilegegroupid,
                    A.description';
        $tables  = 'gtcPrivilegeGroup A';

        $where   = 'A.privilegegroupId = ?';
        $data    = array($privilegeGroupId);

        $this->setColumns($columns);
        $this->setTables($tables);
        $this->setWhere($where);
        $sql = $this->select($data);
        $rs  = $this->query($sql);

        return $rs;
    }


    public function getUserPolicy( $libraryUnitId = null , $personId, $linkId, $materialGenderId=null )
    {
        $this->clear();
        if ($materialGenderId)
        {
            $this->setWhere('A.materialGenderId = ?');
            $data[] = $materialGenderId;
        }
        if ($libraryUnitId)
        {
            $this->setWhere('B.libraryUnitId = ?');
            $data[] = $libraryUnitId;

            $this->setTables('gtcPolicy       A
                LEFT JOIN gtcLibraryUnit  B
                       ON (A.privilegeGroupId = B.privilegeGroupId)
                LEFT JOIN basPersonLink   C
                       ON (A.linkId = C.linkId)
                LEFT JOIN gtcPrivilegeGroup PG
                       ON (A.privilegeGroupId = PG.privilegeGroupId)
                LEFT JOIN basLink         L
                       ON (A.linkId = L.linkId)
                LEFT JOIN gtcMaterialGender MG
                       ON (A.materialGenderId = MG.materialGenderId)');
        }
        else
        {
        	$this->setTables('gtcPolicy       A
                LEFT JOIN basPersonLink   C
                       ON (A.linkId = C.linkId)
                LEFT JOIN gtcPrivilegeGroup PG
                       ON (A.privilegeGroupId = PG.privilegeGroupId)
                LEFT JOIN basLink         L
                       ON (A.linkId = L.linkId)
                LEFT JOIN gtcMaterialGender MG
                       ON (A.materialGenderId = MG.materialGenderId)');
        }
        

        $this->setColumns('DISTINCT A.privilegeGroupId,
                           A.linkId,
                           A.materialGenderId,
                           A.loanDate,
                           A.loanDays,
                           A.loanLimit,
                           A.renewalLimit,
                           A.reserveLimit,
                           A.fineValue,
                           A.momentaryFineValue,
                           A.penaltyByDelay,
                           A.daysOfWaitForReserve,
                           A.renewalWebLimit,
                           A.renewalWebBonus,
                           A.additionalDaysForHolidays,
                           A.reserveLimitInInitialLevel,
                           A.daysOfWaitForReserveInInitialLevel,
                           PG.description AS privilegeGroup,
                           L.description AS link,
                           MG.description AS materialGender');

        $data[] = $personId;

        $this->setWhere('C.personId = ?');

        if ($linkId)
        {
            $this->setWhere('C.linkId = ?');
            $data[] = $linkId;
        }

        $this->setOrderBy('2');

        $sql = $this->select($data);
        $rs  = $this->query($sql, TRUE);
        return (array)$rs;
    }


    public function getLibraryUnitPolicy($libraryUnitId, $linkId, $extraInfo = FALSE, $forceLoanDays = FALSE, $personId = null )
    {
    	$MIOLO      = MIOLO::getInstance();
        $library    = $this->busLibraryUnit->getLibraryUnit($libraryUnitId);

        $this->clear();

        $this->setTables($this->tables);
        $this->setColumns($this->columns);
        $this->setWhere('privilegeGroupId = ?');
        $this->setWhere('linkId = ?');
        $args[] = $library->privilegeGroupId;
        $args[] = $linkId;
        $sql    = $this->select($args);
        $query  = $this->query($sql, true);
        
        if (is_array($query) && $extraInfo || 1)
        {

        	foreach ($query as $line => $info)
        	{

				$privilegeGroup = $this->busPrivilegeGroup->getPrivilegeGroup($info->privilegeGroupId, true);
				$query[$line]->privilegeGroupDescription = $privilegeGroup->description;
				$materialGender   = $this->busMaterialGender->getMaterialGender($info->materialGenderId, true);
				$query[$line]->materialGenderDescription   = $materialGender->description;

        	    //calculte forecast date

                if ($info)
                {

                    if ( (!$info->loanDate) || ($forceLoanDays) )
                    {

                        $query[$line]->forecastDate = $this->policyForecastDateCalculate($info->loanDays);
                    }
                    else
                    {
                        $infoLoanDate = new GDate ($info->loanDate);
                        $query[$line]->forecastDate = $infoLoanDate->getDate(GDate::MASK_DATE_USER);
                    }
                }
        	}
        }

        //se passou o código da pessoa é porque quer pegar reservas
        if ( is_array( $query) && $personId  )
        {
            $busReserve      = $MIOLO->getBusiness($this->module, 'BusReserve');

            $requestedReserves = $busReserve->getReservesOfAssociation( $libraryUnitId, $personId, array( ID_RESERVESTATUS_REQUESTED ), true );
            $answeredReserves  = $busReserve->getReservesOfAssociation( $libraryUnitId, $personId, array( ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED ), true );

            foreach ( $query as $line => $info)
            {
                $query[ $line ]->requestedReserves = 0;
                $query[ $line ]->answeredReserves  = 0;
                $query[ $line ]->reserves          = 0;

                    //foreach pelas reservas
                    if ( is_array( $requestedReserves ) )
                    {
                        foreach ( $requestedReserves as $l => $i )
                        {
                            //e pelas composições
                            $found = false;
                            //passa pela composição conferindo se tem algum exemplar do materialGenderRequisitado
                            $reserveComposition = $i->reserveComposition;
                            if ( is_array( $reserveComposition ) )
                            {
                                foreach ( $reserveComposition as $lin => $inf)
                                {
                                    //compara se é o mesmo materialGender e adiciona um a contagem
                                    if ( $query[ $line ]->materialGenderId == $inf->materialGenderId)
                                    {
                                        $found = true;
                                    }
                                }
                            }

                            //se encontrou soma
                            if ( $found )
                            {
                                $query[ $line ]->requestedReserves = $query[ $line ]->requestedReserves+1;
                            }
                        }
                    }

                    if ( is_array( $answeredReserves ) )
                    {
                        foreach ( $answeredReserves as $l => $i )
                        {
                            //e pelas composições

                        	$found = false;

                            $reserveComposition = $i->reserveComposition;
                            if ( is_array( $reserveComposition ) )
                            {
                                foreach ( $reserveComposition as $lin => $inf)
                                {
                                    //compara se é o mesmo materialGender e adiciona um a contagem
                                    if ( $query[ $line ]->materialGenderId == $inf->materialGenderId)
                                    {
                                    	$found = true;
                                    }
                                }
                            }

                            //se achou soma, faz contar só uma vez
                            if ( $found )
                            {
                                $query[ $line ]->answeredReserves = $query[ $line ]->answeredReserves+1;
                            }
                        }
                    }

                    $query[ $line ]->reserves = $query[ $line ]->answeredReserves + $query[ $line ]->requestedReserves;
                }
        }

        return $query;
    }
}
?>
