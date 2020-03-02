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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 05/10/2011
 *
 * */
class BusinessGnuteca3BusMyLibrary extends GBusiness
{

    /**
     * Código da mensagem da minha biblioteca
     * @var integer 
     */
    public $myLibraryId;
    public $personId;
    public $tableName;
    public $tableId;
    public $date;
    public $message;
    public $visible;
    public $myLibraryIdS;
    public $personIdS;
    public $tableNameS;
    public $tableIdS;
    public $dateS;
    public $messageS;
    public $visibleS;
    public $beginDateS;
    public $endDateS;

    public function __construct()
    {
        $table = 'gtcMyLibrary';
        $pkeys = 'myLibraryId';
        $cols = 'personId,
                  tableName,
                  tableId,
                  date, 
                  message,
                  visible';

        parent::__construct($table, $pkeys, $cols);
    }

    public function insertMyLibrary()
    {
        return $this->autoInsert();
    }

    public function updateMyLibrary()
    {
        return $this->autoUpdate();
    }

    public function deleteMyLibrary($myLibraryId)
    {
        return $this->autoDelete($myLibraryId);
    }

    public function getMyLibrary($myLibraryId)
    {
        $this->clear();
        return $this->autoGet($myLibraryId);
    }

    /**
     * Busca registros da minha biblioteca
     * 
     * @param boolean $object
     * @param int $offset
     * @param int $limit
     * @param String $orderBy
     * @return array de valores 
     */
    public function searchMyLibrary($object = false, $offset = null, $limit = null, $orderBy = null)
    {
        unset($this->myLibraryId); //estava ocorrendo bug pos-busca no formulario
        $this->clear();
        $this->setColumns('myLibraryId, personId,tableName,tableId, date,message,visible');
        $this->setTables('gtcMyLibrary');
        $filters = array(
            'myLibraryId' => 'equals',
            'personId' => 'equals',
            'tableName' => 'ilike',
            'tableId' => 'ilike',
            'date' => 'date',
            'message' => 'ilike',
            'visible' => 'equals'
        );

        $args = $this->addFilters($filters);

        if ($offset)
        {
            $this->setOffset($offset);
        }

        if ($limit)
        {
            $this->setLimit($limit);
        }

        if ($orderBy)
        {
            $this->setOrderBy($orderBy);
        }

        if (!empty($this->beginDateS))
        {
            $this->setWhere('date >= ?');
            $args[] = $this->beginDateS;
        }
        if (!empty($this->endDateS))
        {
            $this->setWhere('date <= ?');
            $args[] = $this->endDateS;
        }

        $sql = $this->select($args);
        $rs = $this->query($sql, $object);

        return $rs;
    }

    /**
     * Obtém o número total de registros
     * 
     * @return int total de registros 
     */
    public function getTotalMessages()
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns('count(*)');

        $data = array();
        if ($this->personIdS)
        {
            $this->setWhere('personId = ?');
            $data[] = $this->personIdS;
        }

        $this->setWhere('visible = ?');
        $data[] = DB_TRUE;

        $sql = $this->select($data);
        $result = $this->query($sql);

        return $result[0][0];
    }

    /**
     * Mensagem de devolução
     * 
     * @param String $itemNumber
     * @return String mensagem 
     */
    public function getReturnMessage($itemNumber)
    {
        $MIOLO = MIOLO::getInstance();
        $busSearchFormat = $MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');
        $busExemplaryControl = $MIOLO->getBusiness('gnuteca3', 'BusExemplaryControl');
        $controlNumber = $busExemplaryControl->getControlNumber($itemNumber); //obtém o número de controle do itemNumber
        //formato de pesquisa
        $data = $busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID);

        //link para acessar o material
        $opts = array('controlNumber' => $controlNumber,
            'gotoTab' => 'tabEvaluation');
        $url = 'javascript:' . GUtil::getAjax('openMaterialDetail', $opts);
        $link = new MLink('linkMaterial', _M('aqui', 'gnuteca3'), $url);

        $message .= '<div class="mContainerHorizontal" id="26">
                        <img alt="" src="file.php?folder=theme&amp;file=materialMovement-32x32.png" style="width: 32px; height: 32px;" /><strong>&nbsp;&nbsp; ' . _M('Você devolveu a obra:', 'gnuteca3') . '</strong><br />
                        <br /> <br />' . $data . '</br ><br />' .
                _M('Deixe seu comentário e avaliação @1', 'gnuteca3', $link->generate()) .
                '</div>';

        return $message;
    }

    /**
     * Mensagem de comentário
     * 
     * @param String $personName nome da pessoa que comentou
     * @param int $controlNumber número de controle da obra comentada
     * @return String mensagem 
     */
    public function getCommentMaterialMessage($personId, $controlNumber)
    {
        $MIOLO = MIOLO::getInstance();
        $busExemplaryControl = $MIOLO->getBusiness('gnuteca3', 'BusExemplaryControl');
        $busPerson = $MIOLO->getBusiness('gnuteca3', 'BusPerson');
        $person = $busPerson->getBasicPersonInformations($personId);

        //link para acessar o material
        $opts = array('controlNumber' => $controlNumber,
            'gotoTab' => 'tabEvaluation');
        $url = 'javascript:' . GUtil::getAjax('openMaterialDetail', $opts);
        $link = new MLink('linkMaterial', _M('Ver comentário', 'gnuteca3'), $url);

        $message = '<div class="mContainerHorizontal" id="26">
                       <img alt="" src="file.php?folder=theme&amp;file=star.png" style="width: 28px; height: 28px;" /><strong>&nbsp;&nbsp; ' . $person->name . ' </strong>' .
                _M('comentou um material que você havia comentado. @1', 'gnuteca3', $link->generate()) .
                '</div>';

        return $message;
    }

    /**
     * Mensagem de sugestão de material
     * 
     * @param integer $controlNumber
     * @return string mensagem
     */
    public function getSuggestedMaterialsMessage($controlNumber)
    {
        $MIOLO = MIOLO::getInstance();
        $busSearchFormat = $MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');
        //formato de pesquisa
        $data = $busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID);

        //link para acessar o material
        $opts = array('controlNumber' => $controlNumber,
            'gotoTab' => 'tabMain');
        $url = 'javascript:' . GUtil::getAjax('openMaterialDetail', $opts);
        $link = new MLink('linkMaterial', _M('aqui', 'gnuteca3'), $url);

        $message = '<div class="mContainerHorizontal" id="26">
                   <img alt="" src="file.php?folder=theme&amp;file=suggestMaterial-32x32.png" style="width: 32px; height: 32px;" /><strong>&nbsp;&nbsp; ' .
                _M('Sugestão de leitura:', 'gnuteca3') . '</strong>
                    <br /> <br />' . $data . '<br /><br />' .
                _M('Clique @1 para reservar o material', 'gnuteca3', $link->generate()) .
                '</div>';

        return $message;
    }

    /**
     * Adiciona sugestão de material
     * @return boolean true se inseriu todos 
     */
    public function suggestMaterial()
    {
        $sql = "SELECT * FROM getSuggestionMaterial();";
        $result = $this->query($sql);

        $ok = array();
        if (is_array($result))
        {
            foreach ($result as $i => $value)
            {
                $this->myLibraryId = null;
                $this->personId = $value[0];
                $this->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
                $this->message = stripslashes($this->getSuggestedMaterialsMessage($value[1]));
                $this->tableName = 'gtcMaterial';
                $this->tableId = $value[1];
                $this->visible = DB_TRUE;
                $ok[] = $this->insertMyLibrary();
            }
        }

        return !in_array(false, $ok);
    }

    /**
     * Mensagem de novas aquisições
     * 
     * @return string mensagem
     */
    public function getNewAcquisitionMessage()
    {
        $message = '<div class="mContainerHorizontal" id="26">
                      <img alt="" src="file.php?folder=theme&amp;file=catalogue-32x32.png" style="width: 32px; height: 32px;" /><strong>&nbsp;&nbsp; ' .
                _M('Foram adquiridas novas aquisições. Verifique seu e-mail.', 'gnuteca3') . '</strong>
                    </div>';

        return $message;
    }

    /**
     * Mensagem de empréstimo atrasado
     * 
     * @return string mensagem
     */
    public function getDelayedLoanMessage()
    {
        $MIOLO = MIOLO::getInstance();

        //link para acessar o material
        $opts = array('subForm' => 'MyLoan',
            'myLibrary' => DB_TRUE);
        $url = $this->MIOLO->getActionURL('gnuteca3', 'main:search:simpleSearch', null, $opts);
        $link = new MLink('linkLoan', _M('aqui', 'gnuteca3'), $url);

        $message = '<div class="mContainerHorizontal" id="26">
                      <img alt="" src="file.php?folder=theme&amp;file=delayedLoan-32x32.png" style="width: 32px; height: 32px;" />&nbsp;&nbsp; <strong>' .
                _M('Você possui materiais em atraso.', 'gnuteca3') . '</strong> ' .
                _M('Clique @1 para ver.', 'gnuteca3', $link->generate()) . '<br />
                    </div>';

        return $message;
    }

    public function setData($data)
    {
        $this->myLibraryId =
                $this->personId =
                $this->tableName =
                $this->tableId =
                $this->date =
                $this->message =
                $this->visible = null;

        parent::setData($data);
    }

}

?>