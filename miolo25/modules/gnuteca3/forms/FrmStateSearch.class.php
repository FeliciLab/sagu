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
 * @author Jonas C. Rosa [jonas_rosa@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 11/07/2012
 *
 * */
class FrmStateSearch extends GForm
{
    public $business;

    public function __construct()
    {
        $this->setAllFunctions('State', array('stateIdS'), array('stateId'));
        parent::__construct();
    }

    public function mainFields()
    {

        $validators = array();
        $fields = array();
        
        $busCountry = $this->MIOLO->getBusiness($this->module, 'BusCountry');
        $countrys = $busCountry->listCountry();

        $fields[] = new MTextField('stateId', null, _M('UF', $this->module), FIELD_ID_SIZE, _M('2 Caracteres', $this->module));
        $fields[] = new MTextField('_name', null, _M('Nome', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('countryId', null, _M('País', $this->module), $countrys);
        $fields[] = new MTextField('ibgeId', null, _M('Código IBGE', $this->module), FIELD_ID_SIZE, _M('Ex.:1234567', $this->module));
        $validators[] = new MRequiredValidator('stateId', null, 2);
        $validators[] = new MRequiredValidator('_name');
        
        $this->setFields($fields);
        $this->setValidators($validators);
    }

}

?>
