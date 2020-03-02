<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Class responsible for storing the functions of login.
 *
 * @author Samuel Koch [samuel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2009
 */
class webServicesBasic
{
    private $MIOLO;

    public function __construct()
    {
        chdir('../');

        $_SERVER['REQUEST_URI'] = 'module=basic';

        require_once 'classes/miolo.class';
        require_once 'classes/support.inc';

        $this->MIOLO = MIOLO::getInstance();

        $this->MIOLO->conf = new MConfigLoader();
        $this->MIOLO->conf->LoadConf();

        $this->MIOLO->Init();
        $this->MIOLO->Uses('classes/sagu.class', 'basic');
    }

    /**
     * Function to webservices that returns the informations of person
     *
     * @param: $personId (integer): Is code of student
     *
     * @return (array) $persons: Return a array of objects with the informations of persons
     * $person->personid
     * $person->persondv
     * $person->personmask
     * $person->personname
     * $person->shortName
     * $person->cityId
     * $person->cityname
     * $person->zipcode
     * $person->location
     * $person->locationTypeId
     * $person->locationtype
     * $person->complement
     * $person->neighborhood
     * $person->email
     * $person->emailalternative
     * $person->url
     * $person->number
     * $person->stateid
     * $person->statename
     * $person->countryname
     * $person->mioloUserName
     * $person->password
     * $persons = array( $person );
     */
    public function wsGetPerson($personId)
    {
        $busPerson = new BusinessBasicBusPerson();
        $wsPerson = $busPerson->getPerson($personId);

        if ( (strlen($wsPerson->personId) > 0 ) )
        {
            $person = new stdClass();
            $person->personId = $wsPerson->personId;
            $person->personDv = $wsPerson->personDv;
            $person->personMask = $wsPerson->personMask;
            $person->personName = $wsPerson->name;
            $person->shortName = $wsPerson->shortName;
            $person->cityId = $wsPerson->cityId;
            $person->cityName = $wsPerson->cityName;
            $person->zipCode = $wsPerson->zipCode;
            $person->location = $wsPerson->location;
            $person->locationTypeId = $wsPerson->locationTypeId;
            $person->locationType = $wsPerson->locationType;
            $person->complement = $wsPerson->complement;
            $person->neighborhood = $wsPerson->neighborhood;
            $person->email = $wsPerson->email;
            $person->emailAlternative = $wsPerson->emailAlternative;
            $person->url = $wsPerson->url;
            $person->number = $wsPerson->number;
            $person->stateId = $wsPerson->stateId;
            $person->stateName = $wsPerson->stateName;
            $person->countryName = $wsPerson->countryName;
            $person->mioloUserName = $wsPerson->mioloUserName;
            $dataLogin = $busPerson->getLoginAndPassword($wsPerson->personId);
            $person->password = $dataLogin->password;
        }

        return $person;
    }


}
?>
