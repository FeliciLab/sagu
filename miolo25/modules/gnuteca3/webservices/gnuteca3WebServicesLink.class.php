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

class gnuteca3WebServicesLink extends GWebServices
{
    /**
     * Attributes
     */
    public $busBond;



    /**
     * Contructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
    }


    /**
     * Retorna os vinculos de uma ou mais pessoas
     *
     * @param integer $clientId
     * @param base 64 encode $clientPassword
     * @param integer or simple array $personId
     * @param xml or php_object $returnType
     * @return unknown
     */
    public function getPersonLink($clientId, $clientPassword, $personId, $returnType = "xml")
    {
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("getPersonLink", false))
        {
            return parent::__getErrorStr();
        }

        $personId = !is_array($personId) ? array($personId) : $personId;

        if(!is_array($personId) || !count($personId))
        {
            return false;
        }

        return parent::returnType($this->busBond->getLinksByPersonId($personId), $returnType);
    }



    /**
     * Deleta todos os vínculos de uma ou mais pessoas.
     *
     * @param integer $clientId
     * @param base 64 encode $clientPassword
     * @param integer or simple array $personId
     * @return boolean
     */
    public function deletePersonLink($clientId, $clientPassword, $personId)
    {
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("deletePersonLink", false))
        {
            return parent::__getErrorStr();
        }

        $personId = !is_array($personId) ? array($personId) : $personId;

        if(!is_array($personId) || !count($personId))
        {
            return false;
        }

        return $this->busBond->deletePersonLink($personId);
    }




    /**
     * Deleta todos os vínculos de uma pessoa ou de um grupo.
     *
     * @param integer $clientId
     * @param base 64 encode $clientPassword
     * @param integer $personId
     * @param integer $linkId
     * @return boolean
     */
    public function deleteLink($clientId, $clientPassword, $personId = null, $linkId = null)
    {
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("deleteBond", false))
        {
            return parent::__getErrorStr();
        }

        return $this->busBond->deleteBond($personId, $linkId);
    }




    /**
     * Insere um novo vinculo
     *
     * @param integer $clientId
     * @param base 64 encode $clientPassword
     * @param integer $personId
     * @param integer $linkId
     * @param date $dateValidate
     * @return boolean
     */
    public function insertPersonLink($clientId, $clientPassword, $personId, $linkId, $dateValidate)
    {
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("insertPersonLink", false))
        {
            return parent::__getErrorStr();
        }

        $this->busBond->personId        = $personId;
        $this->busBond->linkId          = $linkId;
        $this->busBond->dateValidate    = $dateValidate;

        return $this->busBond->insertBond();
    }



}//final classe
?>