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

class gnuteca3WebServicesUnifyRegister extends GWebServices
{
    /**
     * Attributes
     */
    public $busLoanBetweenLibrary;
    public $busBond;
    public $busReserve;
    public $busPersonLibraryUnit;
    public $busPenalty;
    public $busLoan;
    public $busRequestChangeExemplaryStatus;
    public $busInterestsArea;
    public $busFine;
    public $busFavorite;
    public $busPersonConfig;
    public $busFormContent;



    /**
     * Contructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->busLoanBetweenLibrary            = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibrary');
        $this->busBond                          = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busReserve                       = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $this->busPersonLibraryUnit             = $this->MIOLO->getBusiness($this->module, 'BusPersonLibraryUnit');
        $this->busPenalty                       = $this->MIOLO->getBusiness($this->module, 'BusPenalty');
        $this->busLoan                          = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busRequestChangeExemplaryStatus  = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatus');
        $this->busInterestsArea                 = $this->MIOLO->getBusiness($this->module, 'BusInterestsArea');
        $this->busFavorite                      = $this->MIOLO->getBusiness($this->module, 'BusFavorite');
        $this->busPersonConfig                  = $this->MIOLO->getBusiness($this->module, 'BusPersonConfig');
        $this->busFormContent                   = $this->MIOLO->getBusiness($this->module, 'BusFormContent');
    }



    /**
     * Altera os registro de um determinado usuário por outro.
     *
     * No caso dos vínculos, será removido os vínculos do usuário inválido.
     * Nas demais tabelas será feito um update trocando um usuário por outro.
     *
     *
     * @param integer $clientId
     * @param base 64 encode $clientPassword
     * @param integer $invalidPersonId
     * @param integer $correctPersonId
     * @return boolean
     */
    public function unifyPerson($clientId, $clientPassword, $invalidPersonId, $correctPersonId)
    {
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("unifyPerson", false))
        {
            return parent::__getErrorStr();
        }

        $ok['loanBetweenLIbrary']               = $this->busLoanBetweenLibrary->            updatePersonId($invalidPersonId, $correctPersonId);
        $ok['reserve']                          = $this->busReserve->                       updatePersonId($invalidPersonId, $correctPersonId);
        $ok['personLibraryUnit']                = $this->busPersonLibraryUnit->             updatePersonId($invalidPersonId, $correctPersonId);
        $ok['penalty']                          = $this->busPenalty->                       updatePersonId($invalidPersonId, $correctPersonId);
        $ok['loan']                             = $this->busLoan->                          updatePersonId($invalidPersonId, $correctPersonId);
        $ok['requestChangeExemplaryStatus']     = $this->busRequestChangeExemplaryStatus->  updatePersonId($invalidPersonId, $correctPersonId);
        $ok['interestsArea']                    = $this->busInterestsArea->                 updatePersonId($invalidPersonId, $correctPersonId);
        $ok['favorite']                         = $this->busFavorite->                      updatePersonId($invalidPersonId, $correctPersonId);
        $ok['personConfig']                     = $this->busPersonConfig->                  updatePersonId($invalidPersonId, $correctPersonId);
        $ok['formContent']                      = $this->busFormContent->                   updatePersonId($invalidPersonId, $correctPersonId);
        $ok['bond']                             = $this->busBond->                          deletePersonLink($invalidPersonId);

        return parent::returnType($ok, "xml");
    }



}//final classe
?>