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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 28/07/2008
 *
 * */
class BusinessGnuteca3BusHoliday extends GBusiness
{
    public $busLibraryUnitIsClosed;
    public $colsNoId;
    public $holidayId;
    public $date;
    public $description;
    public $occursAllYear;
    public $libraryUnitId;
    public $holidayIdS;
    public $dateS;
    public $beginDateS;
    public $endDateS;
    public $descriptionS;
    public $occursAllYearS;
    public $libraryUnitIdS;

    public function __construct()
    {
        parent::__construct();
        $this->tables = 'gtcHoliday';
        $this->colsNoId = 'date,
                           description,
                           occursAllYear,
                           libraryUnitId';
        $this->id = 'holidayId';
        $this->columns = 'holidayId, ' . $this->colsNoId;
        $this->busLibraryUnitIsClosed = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitIsClosed');
    }

    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     * */
    public function listHoliday($object = FALSE)
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs = $this->query($sql, $object);
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
    public function getHoliday($holidayId)
    {
        $data = array( $holidayId );

        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('holidayId = ?');
        $sql = $this->select($data);
        $rs = $this->query($sql, $toObject = true);
        $this->setData($rs[0]);

        return $this;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $filters (object): Search filters
     *
     * @return (array): An array containing the search results
     * */
    public function searchHoliday()
    {
        $this->clear();

        if ( $v = $this->holidayIdS )
        {
            $this->setWhere('holidayId = ?');
            $data[] = $v;
        }
        if ( $v = $this->dateS )
        {
            $this->setWhere('date(date) >= ?');
            $data[] = $v;
        }
        if ( $v = $this->beginDateS )
        {
            $this->setWhere('date(date) >= ?');
            $data[] = $v;
        }
        if ( $v = $this->endDateS )
        {
            $this->setWhere('date(date) <= ?');
            $data[] = $v;
        }
        if ( $v = $this->descriptionS )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = $v . '%';
        }
        if ( $v = $this->occursAllYearS )
        {
            $this->setWhere('occursallyear = ?');
            $data[] = $v;
        }
        if ( $this->libraryUnitIdS )
        {
            $this->setWhere('A.libraryUnitId in (' . $this->libraryUnitIdS . ')');
        }

        $this->setTables('gtcHoliday       A
                LEFT JOIN gtcLibraryUnit   B
                       ON (A.libraryUnitId = B.libraryUnitId)');
        $this->setColumns('A.holidayId,
                           A.date,
                           A.description,
                           A.occursallyear,
                           A.libraryUnitId AS libraryUnitId,
                           B.libraryName AS libraryName');
        $this->setOrderBy('date DESC');
        $sql = $this->select($data);
        $rs = $this->query($sql);

        return $rs;
    }

    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     * */
    public function insertHoliday()
    {
        $data = $this->associateData($this->colsNoId);

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $sql = $this->insert($data);
        $rs = $this->execute($sql);

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
    public function updateHoliday()
    {
        $data = $this->associateData($this->colsNoId . ', holidayId');

        $this->clear();
        $this->setColumns($this->colsNoId);
        $this->setTables($this->tables);
        $this->setWhere('holidayId = ?');
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
    public function deleteHoliday($holidayId)
    {
        $data = array( $holidayId );

        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere('holidayId = ?');
        $sql = $this->delete($data);
        $rs = $this->execute($sql);

        return $rs;
    }

	/**
     * Função calcula dias de feriado entre duas datas.
     * Desconsidera na contagem os dias em que a biblioteca estiver fechada.
     *
     * @return dias de feriados entre períodos
	 *
     * @param $beginData data inicial
     * @param $endDate data final do período de verificação
     * @para $libraryUnitId código da unidade de biblioteca 
     *
     * */
    public function amountDays($beginDate, $endDate, $libraryUnitId)
    {
        $this->clear();
        $this->setTables('gtcHoliday H');
        $this->setColumns('COUNT(*)');
        $this->setWhere('date BETWEEN ? AND ?');
        $args[] = $beginDate;
        $args[] = $endDate;

        //Obtem somente feriados que não caiam no mesmo dia em que a bibliteca estiver fechada #12580
        $this->setWhere('(H.libraryUnitId = ? 
                       OR H.libraryUnitId is null)
                      AND EXTRACT (DOW FROM H.date) NOT IN (SELECT CASE weekdayid WHEN 7 THEN 0 ELSE weekdayid END 
                                                              FROM gtclibraryunitisclosed 
                                                             WHERE CASE WHEN H.libraryUnitId IS NOT NULL THEN libraryUnitId = H.libraryUnitId ELSE 1=1 END)');
        $args[] = $libraryUnitId;

        $rs = $this->query( $this->select($args) );

        return $rs[0][0];
    }

    /**
     * Return true or false id passed day is a holyday
     *
     * @param $date date as yyyy-mm-dd format (Y-m-d)
     * @param $libraryUnitId the library to verifies the holiday, if you not pass the libraryUnitId the system will consider all units
     *
     */
    public function isHoliday($date, $libraryUnitId = NULL)
    {
        //verifies if has exactely date
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setWhere('date = ?');
        $args[] = $date;

        if ( !$libraryUnitId )
        {
            $this->setWhere('libraryUnitId is null'); //todos
        }
        else
        {
            $this->setWhere('coalesce(libraryunitid = ? , libraryunitid is null)'); //todos e o do unidade
            $args[] = $libraryUnitId;
        }
        $sql = $this->select($args);
        $rs = $this->query($sql, true);

        if ( $rs )
        {
            return true;
        }
        else // if not find date, try to search in all years
        {
            $this->clear();
            $this->setColumns($this->columns);
            $this->setTables($this->tables);
            $this->setWhere('occursAllYear = true');

            if ( $libraryUnitId )
            {
                $this->setWhere('( libraryunitid is null or libraryunitid = ? )');
                $sql = $this->select($libraryUnitId);
            }
            else
            {
                $this->setWhere('libraryunitid is null');
                $sql = $this->select();
            }

            $rs = $this->query($sql, true);

            if ( $rs )
            {
                foreach ( $rs as $line => $holiday )
                {
                    $dateH = new GDate($holiday->date);
                    $date = new GDate($date);

                    if ( ( $dateH->getDay() == $date->getDay() ) && ($dateH->getMonth() == $date->getMonth() ) )
                    {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * This functions verifies the date you want do pass, it verifies if is holyday or if the library is closed, in case of loan you must pass the forecast date, the system will not add days directely, it will only add if has holyday or library is closed.
     *
     * Take in mind that if don't has holyday, or don't is closed, the returned date is the same you pass.
     *
     * @param $timestampUnix the timestamp date you pass to system verifies
     * @param additionalDays days to add if the date if holiday.
     * @param libraryUnitId the library code the verifies the date
     *
     * @return return a timestamp the is the date that can be used in the place that you passed
     *
     * */
    public function checkHolidayDate($timestampUnix, $additionalDays, $libraryUnitId = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $dayOfWeek = date('N', $timestampUnix);

        $date = new GDate($timestampUnix);

        if ( $libraryUnitId )
        {
            $busLibraryUnitIsClosed = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitIsClosed');
            $isClosed = $busLibraryUnitIsClosed->getLibraryUnitIsClosed($libraryUnitId, $dayOfWeek);

            if ( $isClosed )
            {
                $date->addDay(1);
                $timestampUnix = $date->getTimestampUnix();
                return $this->checkHolidayDate($timestampUnix, $additionalDays, $libraryUnitId);
                ;
            }
        }

        $isHoliday = $this->isHoliday($date->getDate(GDate::MASK_DATE_DB), $libraryUnitId);

        if ( $isHoliday && $additionalDays != '0' && !is_null($additionalDays))
        {
            $date->addDay($additionalDays);
            $timestampUnix = $date->getTimestampUnix();

            return $this->checkHolidayDate($timestampUnix, $additionalDays, $libraryUnitId);
        }

        return $timestampUnix;
    }

    /**
     * Verifica feriados e dias fechados para a data
     * 
     * @param GDate $initialDate
     * @param integer $additionalDays
     * @param integer $libraryUnitId
     * @return GDate 
     */
    public function checkHolidayBetweenDate($initialDate, $additionalDays, $libraryUnitId)
    {
        /*
          endDate = Soma a initialData com o additionDays
          Verfica se existem feriados e dias que a biblioteca está fechada entre a data initialDate e endDate
          $feriados = data <= endDate and data > initalDate
          $fechada = analizar se a bibliteca tá fechada desconsiderando o dia atual
          Se a feriado e fechado forem nulo, retorna o endDate;
          Else. Verifcar quantos feriados e fechado cuidando para mesclar os dois
          chama a funcao de novo e passa o endDate, quandidadeDeFeriadoFechada */

        if ( $initialDate instanceof GDate )
        {
            $date = $initialDate;
        }
        else
        {
            $date = new GDate($initialDate);
        }

        $endDate = clone( $date );
        $endDate->addDay($additionalDays);
        $count = 0;

        for ( $i = 0; $i < $additionalDays; $i++ )
        {
            $date->addDay(1);
            $isHoliday = $this->isHoliday($date->generate(), $libraryUnitId);
            $isClosed = $this->busLibraryUnitIsClosed->isClosed($libraryUnitId, $date->getDayOfWeek());

            if ( $isHoliday || $isClosed )
            {
                $count++;
            }
        }

        if ( $count )
        {
            return $this->checkHolidayBetweenDate($endDate, $count, $libraryUnitId);
        }

        return $endDate->generate();
    }
}
?>
