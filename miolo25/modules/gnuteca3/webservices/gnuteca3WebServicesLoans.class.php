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
 * Class created on 05/01/2009
 *
 **/

include("GnutecaWebServices.class.php");

class gnuteca3WebServicesLoans extends GWebServices
{
    /**
     * Attributes
     */
    public $busLoan;
    public $busMaterial;



    /**
     * Contructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->busLoan      = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busMaterial  = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
    }



    /**
     * Retorna todos os empréstimos em aberto de uma ou mais pessoas
     *
     * @param integer $clientId
     * @param string $clientPassword
     * @param array integer $personId
     * @return xml or php_object
     */
    public function getLoanOpen($clientId, $clientPassword, $personId, $returnType = "xml")
    {
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("getLoanOpen", false))
        {
            return parent::__getErrorStr();
        }

        $loans = $this->busLoan->getLoanOpenByPerson($personId);

        if(!$loans)
        {
            return false;
        }

        foreach ($loans as $index => $loanObject)
        {
            $loans[$index]->author          = $this->busMaterial->getMaterialAuthorByItemNumber ($loanObject->itemNumber);
            $loans[$index]->title           = $this->busMaterial->getMaterialTitleByItemNumber  ($loanObject->itemNumber);
            $loans[$index]->materialType    = $this->busMaterial->getMaterialTypeByItemNumber   ($loanObject->itemNumber);
        }

        return $this->returnType($loans, $returnType);
    }






}//final classe
?>
