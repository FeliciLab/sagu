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
 * @author Luiz Gilberto Gregory F [luiz@solis.coop.br]
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
 * Class created on 13/08/2009
 *
 **/

include("GnutecaWebServices.class.php");

class gnuteca3WebServicesFines extends GWebServices
{
    /**
     * Attributes
     */
    public $busFine;
    public $busMaterial;



    /**
     * Contructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->busFine      = $this->MIOLO->getBusiness($this->module, 'BusFine');
        $this->busMaterial  = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
    }



    /**
     * Retorna todas as multas em aberto
     *
     * @param integer $clientId
     * @param string $clientPassword
     * @param array integer $libraryUnitId
     * @param array integer $personId
     * @return xml or false
     */
    public function getFinesOpen($clientId, $clientPassword, $libraryUnitId = null, $personId = null, $returnType = "xml")
    {
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("getFinesOpen", false))
        {
            return parent::__getErrorStr();
        }

        $fines = $this->busFine->getFinesOpen($libraryUnitId, $personId);

        if(!$fines)
        {
            return false;
        }

        foreach ($fines as $index => $fineObject)
        {
            $fines[$index]->author          = $this->busMaterial->getMaterialAuthorByItemNumber ($fineObject->itemNumber);
            $fines[$index]->title           = $this->busMaterial->getMaterialTitleByItemNumber  ($fineObject->itemNumber);
            $fines[$index]->materialType    = $this->busMaterial->getMaterialTypeByItemNumber   ($fineObject->itemNumber);
        }

        return $this->returnType($fines, $returnType);
    }


    /**
     * Retorna todas as multas com pagamento via boleto.
     *
     * @param integer $clientId
     * @param string $clientPassword
     * @param array integer $libraryUnitId
     * @param array integer $personId
     * @param array or string date $period
     * @return xml or PHP_OBJECT
     */
    public function getFinePayRoll($clientId, $clientPassword, $libraryUnitId = null, $personId = null, $period = null, $offSet = 0, $limit = 1000, $returnType = "xml")
    {
        // Tira o limite de tempo de execução da rotina.
        set_time_limit( 0 );
        
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("getFinePayRoll", false))
        {
            return parent::__getErrorStr();
        }

        if(is_null($limit) || $limit > 1000)
        {
            return "[ERROR] - Este método não permite um limite maior de 1000.";
        }

        $fines = $this->busFine->getFinePayRoll($libraryUnitId, $personId, $period, $offSet, $limit);

        if(!$fines)
        {
            return false;
        }

        foreach ($fines as $index => $fineObject)
        {
            $fines[$index]->author          = $this->busMaterial->getMaterialAuthorByItemNumber ($fineObject->itemNumber);
            $fines[$index]->title           = $this->busMaterial->getMaterialTitleByItemNumber  ($fineObject->itemNumber);
            $fines[$index]->materialType    = $this->busMaterial->getMaterialTypeByItemNumber   ($fineObject->itemNumber);
        }

        return $this->returnType($fines, $returnType);
    }



    /**
     * Seta algumas multas como pagas
     *
     * @param integer $clientId
     * @param string $clientPassword
     * @param simple array integer $finesId
     * @param string $returnType
     * @return undefined
     */
    public function setFinePay($clientId, $clientPassword, $finesId, $returnType = "xml")
    {
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("setFinePay", false))
        {
            return parent::__getErrorStr();
        }

        if(!is_array($finesId))
        {
            $finesId = array($finesId);
        }

        foreach ($finesId as $fineId)
        {
            $this->busFine->clean();
            $this->busFine->observation         = "Esta multa foi paga por Web Services. ClientId: $clientId";
            $this->busFine->observationHistoric = "Esta multa foi paga por Web Services. ClientId: $clientId";

            $finesResult[$fineId] = $this->busFine->setFinePay($fineId, $this->getOperator());
        }

        return $this->returnType($finesResult, $returnType);
    }




    /**
     * Seta todas as multas abertas como pagas e retorna um relatorio.
     *
     * @param integer $clientId
     * @param string $clientPassword
     * @param integer $libraryUnitId
     * @param simple integer array $personId
     * @param xml | php_object $returnType
     * @return undefined
     */
    public function payAllFinesOpen($clientId, $clientPassword, $libraryUnitId = null, $personId = null, $returnType = "xml")
    {
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("payAllFinesOpen", false))
        {
            return parent::__getErrorStr();
        }

        // busca multas em aberto
        $fines = $this->busFine->getFinesOpen($libraryUnitId, $personId);

        if(!$fines)
        {
            return false;
        }

        foreach ($fines as $content)
        {
            $this->busFine->clean();
            $this->busFine->observation         = "Esta multa foi paga por Web Services. ClientId: $clientId";
            $this->busFine->observationHistoric = "Esta multa foi paga por Web Services. ClientId: $clientId";

            $finesResult[$content->fineId] = $content;
            $finesResult[$content->fineId]->pay = $this->busFine->setFinePay($content->fineId, $this->getOperator());
        }

        return $this->returnType($finesResult, $returnType);
    }




}//final classe
?>