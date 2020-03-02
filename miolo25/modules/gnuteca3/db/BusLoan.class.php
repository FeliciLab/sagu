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
 * This file handles the connection and actions for general loan table
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
 * Class created on 04/08/2008
 *
 * */
class BusinessGnuteca3BusLoan extends GBusiness
{

    public $MIOLO;
    public $module;
    public $orderByLibraryUnit;
    public $busLibraryAssociation;
    public $busLibraryUnitIsClosed;
    public $busLibraryUnitConfig;
    public $busOperatorLibraryUnit;
    public $busExemplaryControl;
    public $busHoliday;
    public $busPolicy;
    public $busRenew;
    public $loanId,
            $loanTypeId,
            $personId,
            $linkId,
            $itemNumber,
            $libraryUnitId,
            $loanDate,
            $loanOperator,
            $returnForecastDate,
            $returnDate,
            $returnOperator,
            $renewalAmount,
            $renewalWebAmount,
            $renewalWebBonus,
            $privilegeGroupId,
            $status,
            $privilegeGroup; //utilizado no formulário de empréstimos
    public $loanIdS,
            $loanTypeIdS,
            $personIdS,
            $linkIdS,
            $itemNumberS,
            $libraryUnitIdS,
            $loanDateS,
            $beginLoanDateS,
            $endLoanDateS,
            $loanOperatorS,
            $beginReturnForecastDateS,
            $endReturnForecastDateS,
            $returnDateS,
            $beginReturnDateS,
            $endReturnDateS,
            $returnOperatorS,
            $renewalAmountS,
            $renewalWebAmountS,
            $renewalWebBonusS;

    /**
     * Class constructor
     * */
    function __construct()
    {
        parent::__construct();

        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();

        $this->busLibraryAssociation = $this->MIOLO->getBusiness($this->module, 'BusLibraryAssociation');
        $this->busLibraryUnitIsClosed = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitIsClosed');
        $this->busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');
        $this->busOperatorLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusOperatorLibraryUnit');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busHoliday = $this->MIOLO->getBusiness($this->module, 'BusHoliday');
        $this->busPolicy = $this->MIOLO->getBusiness($this->module, 'BusPolicy');
        $this->busRenew = $this->MIOLO->getBusiness($this->module, 'BusRenew');
        $this->MIOLO->getClass($this->module, 'GOperator');

        $this->table = 'gtcLoan';
        $this->colsNoId = 'loanTypeId,
                           personId,
                           linkId,
                           itemNumber,
                           libraryUnitId,
                           loanDate,
                           loanOperator,
                           returnForecastDate,
                           returnDate,
                           returnOperator,
                           renewalAmount,
                           renewalWebAmount,
                           renewalWebBonus,
                           privilegeGroupId';
        $this->colsId = 'loanId';
        $this->cols = $this->colsId . ',' . $this->colsNoId;
    }

    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     * */
    public function listLoan()
    {
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->table);
        $sql = $this->select();
        $rs = $this->query($sql);
        return $rs;
    }

    /**
     * Return a specific record from the database
     *
     * @param $moduleConfig (integer): Primary key of the record to be retrieved
     * @param $parameter (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     * */
    public function getLoan($id, $return = FALSE, $extraInfo = false)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns($this->cols);
        $this->setWhere($this->colsId . ' = ? ');
        $sql = $this->select(array($id));
        $rs = $this->query($sql, true);

        if ($rs[0] && $extraInfo)
        {
            $data = $rs[0];

            $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
            $busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
            $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
            $controlNumber = $busExemplaryControl->getControlNumber($data->itemNumber);
            $rs[0]->controlNumber = $controlNumber;

            if ($controlNumber)
            {
                $rs[0]->title = $busMaterial->getContentTag($controlNumber, MARC_TITLE_TAG);
                $rs[0]->author = $busMaterial->getContentTag($controlNumber, MARC_AUTHOR_TAG);
                $rs[0]->libraryUnit = $busLibraryUnit->getLibraryUnit($data->libraryUnitId, true);
            }

            $this->busRenew->loanIdS = $id;
            $renew = $this->busRenew->searchRenew(true);
            $rs[0]->renew = $renew;
        }

        //utlizado no formulário de empréstimos
        if ($rs[0]->privilegeGroupId)
        {
            $busPrivilegeGroup = $this->MIOLO->getBusiness($this->module, 'BusPrivilegeGroup');
            $privilegeGroup = $busPrivilegeGroup->getPrivilegeGroup($rs[0]->privilegeGroupId, true);
            $rs[0]->privilegeGroup = $privilegeGroup->description;
        }

        if (!$return)
        {
            $this->setData($rs[0]);
            return $this;
        }
        else
        {
            return $rs[0];
        }
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     * */
    public function searchLoan($toObject = FALSE, $orderBy = 'loanId DESC')
    {
        $doSearch = false;
        $this->clear();
        if ($this->loanIdS)
        {
            $this->setWhere('loanId = ?');
            $data[] = $this->loanIdS;
            $doSearch = true;
        }

        if ($this->loanTypeIdS)
        {
            $this->setWhere('L.loanTypeId = ?');
            $data[] = $this->loanTypeIdS;
            $doSearch = true;
        }

        if ($this->personIdS)
        {
            $this->setWhere('L.personId = ?');
            $data[] = $this->personIdS;
            $doSearch = true;
        }

        if ($this->personId)
        {
            $this->setWhere('L.personId = ?');
            $data[] = $this->personId;
            $doSearch = true;
        }

        /*  if ( $this->linkIdS )
          {
          $this->setWhere('L.linkId = ?');
          $data[] = $this->linkIdS;
          $doSearch = true;
          } */

        if ($this->itemNumber)
        {
            $this->setWhere('L.itemNumber = ?');
            $data[] = $this->itemNumber;
            $doSearch = true;
        }

        if ($this->itemNumberS)
        {
            $this->setWhere('L.itemNumber = ?');
            $data[] = $this->itemNumberS;
            $doSearch = true;
        }

        if ($this->libraryUnitIdS)
        {
            $this->setWhere('L.libraryUnitId in (' . $this->libraryUnitIdS . ')');
            $doSearch = true;
        }

        if ($this->loanDateS)
        {
            $this->setWhere('date(L.loanDate) = ?');
            $data[] = $this->loanDateS;
            $doSearch = true;
        }


        if ($this->beginLoanDateS)
        {
            $this->setWhere('date(L.loanDate)>= ?');
            $data[] = $this->beginLoanDateS;
            $doSearch = true;
        }
        
        if ($this->endLoanDateS)
        {
            $this->setWhere('date(L.loanDate)<= ?');
            $data[] = $this->endLoanDateS;
            $doSearch = true;
        }

        if ($this->loanOperatorS)
        {
            $this->setWhere('L.loanOperator = ?');
            $data[] = $this->loanOperatorS;
            $doSearch = true;
        }

        if ($this->beginReturnForecastDateS)
        {
            $this->setWhere('date(L.returnForecastDate) >= ?');
            $data[] = $this->beginReturnForecastDateS;
            $doSearch = true;
        }
        
        if ($this->endReturnForecastDateS)
        {
            $this->setWhere('date(L.returnForecastDate) <= ?');
            $data[] = $this->endReturnForecastDateS;
            $doSearch = true;
        }

        if ($this->returnDateS)
        {
            $this->setWhere('date(L.returnDate) = ?');
            $data[] = $this->returnDateS;
            $doSearch = true;
        }

        if ($this->beginReturnDateS)
        {
            $this->setWhere('date(L.returnDate) >= ?');
            $data[] = $this->beginReturnDateS;
            $doSearch = true;
        }

        if ($this->endReturnDateS)
        {
            $this->setWhere('date(L.returnDate) <= ?');
            $data[] = $this->endReturnDateS;
            $doSearch = true;
        }

        if ($this->returnOperatorS)
        {
            $this->setWhere('L.returnOperator = ?');
            $data[] = $this->returnOperatorS;
            $doSearch = true;
        }

        //Se o valor digitado foi maior ou igual a zero e se não é em branco
        if (intval($this->renewalAmountS) >= 0 && strlen($this->renewalAmountS) > 0)
        {
            $this->setWhere('L.renewalAmount= ?');
            $data[] = $this->renewalAmountS;
            $doSearch = true;
        }

        //Se o valor digitado foi maior ou igual a zero e se não é em branco
        if ((intval($this->renewalWebAmountS) >= 0) && (strlen($this->renewalWebAmountS) > 0))
        {
            $this->setWhere('L.renewalWebAmount = ?');
            $data[] = $this->renewalWebAmountS;
            $doSearch = true;
        }

        if ($this->renewalWebBonusS)
        {
            $this->setWhere('L.renewalwebbonus = ?');
            $data[] = $this->renewalWebBonusS;
            $doSearch = true;
        }

        if ($this->status == 1)
        {
            $this->setWhere('returnDate IS NULL');
            $doSearch = true;
        }
        else if ($this->status == 2)
        {
            $this->setWhere('returnDate IS NULL AND returnForecastDate < ?');
            $data[] = GDate::now()->getDate(GDate::MASK_DATE_DB);
            $doSearch = true;
        }

        $this->setColumns(' loanId,
                            L.LoanTypeId,
                            LT.description,
                            L.personId,
                            P.name,
                            itemNumber,
                            null AS number_of_tomo,
                            null AS data,
                            LoanDate,
                            returnForecastDate,
                            returnDate,
                            null AS total,
                            renewalamount,
                            renewalwebamount,
                            renewalwebbonus,
                            Loanoperator,
                            returnOperator,
                            L.linkId,
                            BL.description,
                            L.LibraryUnitId,
                            LU.libraryName');
        $this->setTables(' gtcLoan L
                            LEFT JOIN gtcLoanType LT
                            ON L.loanTypeId = LT.loanTypeId
                            LEFT JOIN ONLY basPerson P
                            ON L.personId  = P.personId
                            LEFT JOIN basLink BL
                            ON L.linkid = BL.linkId
                            LEFT JOIN gtcLibraryUnit LU
                            ON L.libraryUnitId = LU.libraryUnitId');
        $this->setOrderBy($orderBy);

        //por motivos de segurança e perfomance a busca do Loan só é executa caso aconteça algum where, caso contrário o sistema pode ser comprometido
        if ($doSearch)
        {
            $sql = $this->select($data);
            $rs = $this->query($sql, ($toObject) ? TRUE : FALSE);
            return $rs;
        }
        else
        {
            return false;
        }
    }

    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     * */
    public function insertLoan()
    {
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->table);
        $this->verifyTimeStampFields();
        $sql = $this->insert($this->associateData($this->colsNoId));
        $sql .= ' RETURNING loanId';
        $rs = $this->query($sql);
        
        
        $this->loanId = $rs[0][0];
        $this->loanIdS = $rs[0][0];

        return $rs;
    }

    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     * */
    public function updateLoan()
    {
        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->table);
        $this->setWhere($this->colsId . ' = ?');
        $this->verifyTimeStampFields();
        $data = $this->associateData($this->colsNoId . ',' . $this->colsId);
        $sql = $this->update($data);
        $rs = $this->execute($sql);
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
     * */
    public function deleteLoan($id)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setWhere($this->colsId . ' = ?');
        $sql = $this->delete(array($id));
        $rs = $this->execute($sql);
        return $rs;
    }

    public function getLoanOpen($itemNumber)
    {
        //detecta se o parametro passado foi array, para retonar como array, caso seja passado um único itemNumber, retorna objeto
        //isto foi feito desta forma para manter compatibilidade com a função que antes retornava somente loan de um único exemplar
        $returnArray = is_array($itemNumber);

        if (!is_array($itemNumber))
        {
            $itemNumber = array($itemNumber);
        }

        $this->clear();
        $this->setColumns(' loanId,
							loanTypeId,
							l.personId,
							linkId,
							itemNumber,
							l.libraryUnitId,
							loanDate,
							loanOperator,
							returnForecastDate,
							returnDate,
							returnOperator,
							renewalAmount,
							renewalWebAmount,
							renewalWebBonus,
							l.privilegeGroupId,
							libraryName,
							name as personName,
							P.email');

        $this->setTables('gtcLoan L
                 LEFT JOIN gtclibraryUnit
                        ON (L.libraryUnitId = gtclibraryUnit.libraryUnitId )
            LEFT JOIN ONLY basPerson P
                        ON ( L.personId = P.personId)');

        $this->setWhere('itemNumber IN (\'' . implode('\',\'', $itemNumber) . '\') AND returnDate IS NULL');
        $sql = $this->select();

        $rs = $this->query($sql, true);

        if ($returnArray)
        {
            return $rs;
        }

        return $rs[0]; //or false;
    }

    public function getLoanOpenByPerson($personId)
    {
        $personId = is_array($personId) ? $personId : array($personId);
        $personId = implode("', '", $personId);

        $this->clear();
        $this->setColumns("A.loanId,
                           A.loanTypeId,
                           A.personId,
                           A.linkId,
                           A.itemNumber,
                           A.libraryUnitId,
                           A.loanDate,
                           A.loanOperator,
                           A.returnForecastDate,
                           A.returnDate,
                           A.returnOperator,
                           A.renewalAmount,
                           A.renewalWebAmount,
                           A.renewalWebBonus,
                           A.privilegeGroupId,
                           B.libraryName");
        $this->setTables("gtcLoan A INNER JOIN gtcLibraryUnit B USING (libraryUnitId)");
        $this->setWhere("A.personid IN ('$personId') AND A.returnDate IS NULL");
        $sql = $this->select();
        $rs = $this->query($sql, true);

        return $rs; //or false;
    }

    public function getReturnForecastDateFromItemNumber($itemNumber)
    {
        if (!$itemNumber)
        {
            return false;
        }
        $sql = "SELECT returnForecastDate FROM gtcLoan WHERE itemNumber = '$itemNumber' AND returnDate IS NULL limit 1";
        $rs = $this->query($sql);
        return $rs[0][0];
    }

    /**
     * Retorna a data que foi feito o emprestimo
     *
     * @param integer $loanId
     * @return timestamp
     */
    public function getLoanDate($loanId)
    {
        $this->clear();
        $this->setColumns("loanDate");
        $this->setTables($this->table);
        $this->setWhere('loanId = ?');
        $sql = $this->select(array($loanId));
        $rs = $this->query($sql, true);

        return $rs[0] ? $rs[0]->loanDate : false;
    }

    /**
     * Retorna a data prevista para devolução
     *
     * @param integer $loanId
     * @return timestamp
     */
    public function getReturnForecastDate($loanId)
    {
        $this->clear();
        $this->setColumns("returnForecastDate");
        $this->setTables($this->table);
        $this->setWhere('loanId = ?');
        $sql = $this->select(array($loanId));
        $rs = $this->query($sql, true);

        return $rs[0] ? $rs[0]->returnForecastDate : false;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $loanId
     * @return unknown
     */
    public function delayDays($loanId)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns('returnDate, returnForecastDate');
        $this->setWhere($this->colsId . ' = ?');
        $sql = $this->select(array($loanId));
        $rs = $this->query($sql, TRUE);
        $data = $rs[0];

        $data->returnForecastDate = new GDate($data->returnForecastDate);
        $data->returnDate = new GDate($data->returnDate);
        $diff = $data->returnForecastDate->diffDates($data->returnDate);

        return $diff->days;
    }

    /**
     * devolve um determinado material
     *
     * @param integer $itemNumber
     * @param timestamp $returnDate
     * @param string $returnOperator
     * @return boolean
     */
    public function returnLoan($itemNumber, $returnDate, $returnOperator)
    {
        $this->clear();
        $columns = 'returnDate,
                    returnOperator';
        $this->setColumns($columns);
        $this->setTables($this->table);
        $this->setWhere($this->colsId . ' = ?');
        $sql = $this->update(array($returnDate, $returnOperator, $itemNumber));
        
        
        $rs = $this->execute($sql);
        return $rs;
    }

    /**
     * Calcula multas para um loanId, pode passar o objeto do loan se for necessário, para evitar novos selects
     * 
     * @param $loanId
     * @param $loan
     * @return unknown_type
     */
    public function calculatesFine($loanId = null, $loan = null)
    {
        if ($loan)
        {
            $days = $loan->delayDays;
            $loanId = $loan->loanId;
        }
        else
        {
            $days = $this->delayDays($loanId);
        }

        if ($days <= 0)
        {
            return 0;
        }

        if (!$loan)
        {
            $loan = $this->getLoan($loanId);
        }

        $holidayDays = 0; //define como 0 para não dar problema de cálculo
        $closedDays = 0;

        $parameters = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($loan->libraryUnitId, array('CHARGE_FINE_IN_THE_HOLIDAY', 'CHANGE_FINE_WHEN_THE_LIBRARY_UNIT_IS_CLOSED'));

        // Se não for para cobrar multa quando for feriado desconta os dias do valor total
        if (!Mutil::getBooleanValue($parameters['CHARGE_FINE_IN_THE_HOLIDAY']))
        {
            $holidayDays = $this->busHoliday->amountDays($loan->returnForecastDate, $loan->returnDate, $loan->libraryUnitId);
        }

        // Se não for para cobrar multa quando a biblioteca estiver fechada desconta os dias do valor total
        if (!Mutil::getBooleanValue($parameters['CHANGE_FINE_WHEN_THE_LIBRARY_UNIT_IS_CLOSED']))
        {
            $closedDays = $this->busLibraryUnitIsClosed->amountDays($loan->returnForecastDate, $loan->returnDate, $loan->libraryUnitId);
        }

        $days = $days - ($holidayDays + $closedDays);

        if ($days > 0)
        {
            if ($loan->materialGenderId)
            {
                $materialGenderId = $loan->materialGenderId;
            }
            else
            {
                $materialGenderId = $this->busExemplaryControl->getMaterialGender($loan->itemNumber);
            }

            //Verifica se existe um grupo de privilégio para o empréstimo caso não exista
            //define o grupo padrão na política.
            if( !isset( $loan->privilegeGroupId ) || $loan->privilegeGroupId == NULL )
            {
                $policy = $this->busPolicy->getPolicy( DEFAULT_VALUE_PRIVILEGEGROUP_LOAN, $loan->linkId, $materialGenderId );
            }
            else
            {
                $policy = $this->busPolicy->getPolicy( $loan->privilegeGroupId, $loan->linkId, $materialGenderId );
            }
            
            //Se for emprestimo momentaneo por dia
            if ( $loan->loanTypeId == ID_LOANTYPE_MOMENTARY && defined('LOAN_MOMENTARY_PERIOD') && LOAN_MOMENTARY_PERIOD == 'D'  )
            {
                //Usa o valor momentaneo para aplicar durante os dias.
                $fineValue = $policy->momentaryFineValue;
            }
            else
            {
                //Caso contrario utiliza o valor do emprestimo normal.
                $fineValue = $policy->fineValue ;
            }
            
            $value = $fineValue * $days;
            return $value;
        }

        return 0;
    }

    /**
     * Calcula valor de multa para o caso de especial de empréstimo momentâneo em horas, 
     * 
     * @param integer $hoursLate horas de atraso
     * @param integer $materialGenderId código do genero do material
     * @param integer $linkId código do vínculo da pessoa
     * @return float valor da multa
     */
    public function calculateFineHour($hoursLate, $materialGenderId, $linkId)
    {
        $policy = $this->busPolicy->getPolicy(DEFAULT_VALUE_PRIVILEGEGROUP_LOAN, $linkId, $materialGenderId);

        return $policy->momentaryFineValue * $hoursLate;
    }

    /**
     * List the delay loan for an library Unit and Person, it will search on associaties libraries.
     *
     * @param int $libraryUnitId the code of the library
     * @param int $personId the code of the person
     * @param boolean $extraInfo
     * @return an array of stdclass
     */
    public function getLoansOpenOfAssociation($libraryUnitId, $personId, $extraInfo = false)
    {
        $libraries = $this->busLibraryAssociation->getLibrariesAssociationOf($libraryUnitId);

        if ($libraries)
        {
            $this->clear();

            $this->setTables('gtcLoan L LEFT JOIN gtcExemplaryControl E ON L.itemnumber = E.itemnumber');
            $this->setColumns(' loanId,
                                loanTypeId,
                                personId,
                                linkId,
                                L.itemNumber,
                                L.libraryUnitId,
                                loanDate,
                                loanOperator,
                                returnForecastDate,
                                returnDate,
                                returnOperator,
                                renewalAmount,
                                renewalWebAmount,
                                renewalWebBonus,
                                privilegeGroupId,
                                controlNumber');
            $this->setWhere('L.returnDate IS NULL');
            $this->setWhere('L.libraryUnitId IN (' . implode(',', $libraries) . ')');
            $this->setWhere('L.personId = ?');
            $args[] = $personId;
            $sql = $this->select($args);
            $query = $this->query($sql, true);

            if (is_array($query) && $extraInfo)
            {
                $busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
                $busMaterialGender = $this->MIOLO->getBusiness($this->module, 'BusMaterialGender');

                foreach ($query as $line => $info)
                {
                    $materialGenderId = $busExemplaryControl->getMaterialGender($info->itemNumber);
                    $query[$line]->materialGenderId = $materialGenderId;
                    $materialGender = $busMaterialGender->getMaterialGender($materialGenderId, true);
                    $query[$line]->materialGenderDescription = $materialGender->description;
                }
            }
            return $query;
        }
        else
        {
            return array();
        }
    }

    /**
     * Amount of open loan for an library Unit and Person, it will search on associaties libraries.
     *
     * @param int $libraryUnitId the code of the library
     * @param int $personId the code of the person
     * @param boolean $extraInfo
     * @return an array of stdclass
     */
    public function amountLoansDelayOfAssociation($libraryUnitId, $personId)
    {
        $loans = $this->getLoansOpenOfAssociation($libraryUnitId, $personId);
        $amountLoans = 0;

        foreach ($loans as $loan)
        {
            $forecastDate = new GDate($loan->returnForecastDate);

            //Conta somenete os materiais que tiverem 1 dia completo de atraso. As horas são ignoradas.
            $forecastDate->setHour('00');
            $forecastDate->setMinute('00');
            $forecastDate->setSecond('00');

            $now = GDate::now();
            $now->setHour('00');
            $now->setMinute('00');
            $now->setSecond('00');

            if ($forecastDate->diffDates($now)->days < 0)
            {
                $amountLoans++;
            }
        }

        return $amountLoans;
    }

    /**
     * Amount of open loan for an library Unit and Person, it will search on associaties libraries.
     *
     * @param int $libraryUnitId the code of the library
     * @param int $personId the code of the person
     * @param boolean $extraInfo
     * @return an array of stdclass
     */
    public function amountLoansOpenOfAssociation($libraryUnitId, $personId, $extraInfo = false)
    {
        $loans = $this->getLoansOpenOfAssociation($libraryUnitId, $personId);
        return count($loans);
    }

    public function getLoansOpen($beginDate = null, $endDate = null, $extraInfo = null)
    {
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns('loanId');
        $this->setOrderBy('returnForecastDate');

        if ($beginDate)
        {
            $this->setWhere('returnForecastDate::date >= ?');
            $args[] = $beginDate;
        }

        if ($endDate)
        {
            $this->setWhere('returnForecastDate::date <= ?');
            $args[] = $endDate;
        }

        if ($this->personId)
        {
            $this->setWhere('personId = ?');
            $args[] = $this->personId;
        }

        if ($this->orderByLibraryUnit)
        {
            $this->setOrderBy('libraryUnitId');
        }

        $this->setWhere('returnDate IS NULL');
        $sql = $this->select($args);
        $query = $this->query($sql);
        $loans = array();
        if (count($query) > 0 && is_array($query))
        {
            foreach ($query as $val)
            {
                $loans[] = $this->getLoan($val[0], true, $extraInfo);
            }
        }
        return $loans;
    }

    //Função utilizada para enviar e-mails de materiais atrasados e de aviso antes de vencer
    public function getLoansOpenLibrary($beginDate = null, $endDate = null, $libraryUnitId = null)
    {
        $cols = str_replace('personId', 'L.personId', $this->cols);
        $cols = str_replace('itemNumber', 'L.itemNumber', $cols);
        $cols = str_replace('libraryUnitId', 'L.libraryUnitId', $cols);
        $cols = $cols . ', P.name AS personName,
                           P.email AS personEmail,
                           EC.controlNumber AS controlNumber';

        $this->clear();
        $this->setTables('gtcLoan               L
          INNER JOIN ONLY basPerson             P
                       ON (L.personId = P.personId)
               INNER JOIN gtcExemplaryControl   EC
                       ON (L.itemNumber = EC.itemNumber)');
        $this->setColumns($cols);
        $this->setOrderBy('returnForecastDate');

        if ($beginDate)
        {
            $this->setWhere('returnForecastDate::date >= ?');
            $args[] = $beginDate;
        }

        if ($endDate)
        {
            $this->setWhere('returnForecastDate::date <= ?');
            $args[] = $endDate;
        }

        if ($libraryUnitId)
        {
            $this->setWhere('L.libraryUnitId = ?');
            $args[] = $libraryUnitId;
        }

        if ($this->orderByLibraryUnit)
        {
            $this->setOrderBy('L.libraryUnitId');
        }

        $this->setWhere('returnDate IS NULL');
        $sql = $this->select($args);
        $query = $this->query($sql, true);
        return $query;
    }

    /**
     * Altera os registro de um usuário por outro
     *
     * @param integer $currentPersonId
     * @param integer $newPersonId
     * @return boolean
     */
    public function updatePersonId($currentPersonId, $newPersonId)
    {
        $this->clear();
        $this->setColumns("personId");
        $this->setTables($this->table);
        $this->setWhere(' personId = ?');
        $sql = $this->update(array($newPersonId, $currentPersonId));
        $rs = $this->execute($sql);
        return $rs;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $data
     */
    public function setData($data)
    {
        //Fix bug
        if ($data->renewalWebAmount == 0)
        {
            $this->renewalWebAmount = 0;
        }
        parent::setData($data);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $loanId
     * @return unknown
     */
    public function checkAccessLoan($loanId)
    {
        //Get loan
        $this->clear();
        $this->setTables($this->table);
        $this->setColumns($this->cols);
        $this->setWhere('loanId = ?');
        $res = $this->query($this->select($loanId), TRUE);
        $res = $res[0];

        if (!$res)
        {
            return FALSE;
        }

        if (GOperator::getOperatorId() == 'gnuteca3')
        {
            return true;
        }

        $libraries = $this->busOperatorLibraryUnit->getOperatorLibraryUnit(GOperator::getOperatorId());
        $libraries = $libraries->operatorLibrary;
        if (!$libraries)
        {
            return FALSE;
        }
        foreach ($libraries as $v)
        {
            if (($v->operator) && (!$v->libraryUnitId)) //Access to all libraries
            {
                return TRUE;
            }
            else if ($v->libraryUnitId == $res->libraryUnitId) //Has access to libraryUnit
            {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Metodo criado para garantir que os campos MTimeStampField sejam definidos como vazio ''
     * corretamente, pois existe um bug no componente que deixa espaco em branco.
     */
    public function verifyTimeStampFields()
    {
        //TODO: Este metodo deve ser removido assim que o bug do MTimeStampField for resolvido.
        $this->returnForecastDate = trim($this->returnForecastDate);
        $this->returnDate = trim($this->returnDate);
        $this->loanDate = trim($this->loanDate);
    }
    
    public function getDelayedLoanByUser()
    {
        
        $this->clear();
        $this->setTables($this->table);
        
        $this->setColumns('loanId');
        
        //$this->setOrderBy('returnForecastDate');

        if ($this->personId)
        {
            $this->setWhere('personId = ?');
            $args[] = $this->personId;
        }
        
        if ($this->returnForecastDate)
        {
            $this->setWhere('returnForecastDate::date < ?');
            $args[] = $this->returnForecastDate;
        }

        $this->setWhere('returnDate IS NULL');
        
        $sql = $this->select($args);
        
        
        $query = $this->query($sql);
        $loans = array();
        if (count($query) > 0 && is_array($query))
        {
            foreach ($query as $val)
            {
                $loans[] = $this->getLoan($val[0], true);
            }
        }
        return $loans;

    }

}

?>
