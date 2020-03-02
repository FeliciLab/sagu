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
 * Library Unit form
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
class FrmCity extends GForm
{
    /** @var BusinessGnuteca3BusBusCountry */
    public $business;

    public function __construct()
    {
        $this->setAllFunctions('City', null, array( 'cityId' ), array( 'name' ));
        parent::__construct();
    }

    public function mainFields()
    {
        $validators = array( );
        $fields = array( );
        $cityId = null;

        $busCountry = $this->MIOLO->getBusiness($this->module, 'BusCountry');
        $countrys = $busCountry->listCountry();

        $fields[] = new MHiddenField('cityId', null, _M('Código da cidade', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('_name', null, _M('Nome', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('zipCode', null, _M('CEP', $this->module), FIELD_DESCRIPTION_SIZE, _M('Ex.:99999-999', $this->module));
        $fields[] = $countryId = new GSelection('countryId', null, _M('País', $this->module), $countrys);

        $countryId->addAttribute('onchange', 'javascript:' . GUtil::getAjax('countryIdOnChange'));
        $fields[] = new MDiv('state', $this->getStateField());
        $fields[] = new MTextField('ibgeId', null, _M('Campo de ID do IBGE para a cidade', $this->module), FIELD_ID_SIZE);

        $validators[] = new MRequiredValidator('_name');
        $validators[] = new MRequiredValidator('countryId', _M('País', $this->module));
        $validators[] = new MRequiredValidator('stateId', _M('Código do estado/província da federação', $this->module));
        $validators[] = new MCEPValidator('zipCode', _M('CEP', $this->module));

        $this->setFields($fields);
        $this->setValidators($validators);
    }

    public function countryIdOnChange($args)
    {
        $this->setResponse($this->getStateField($args->countryId), 'state');
    }

    public function getStateField($countryId = null, $stateId = null )
    {
        $states = null;

        if ( $countryId )
        {
            $busState = $this->MIOLO->getBusiness($this->module, 'BusState');
            $busState->countryIdS = $countryId;
            $states = $busState->listState();
        }

        return new GContainer('myState', array( new GSelection('stateId', $stateId, _M('Código do estado/província da federação', $this->module), $states) ));
    }
    
    public function setData( $data, $doRepetitiveField = false )
    {
        parent::setData($data, $doRepetitiveField);
        $this->state->setInner( $this->getStateField($data->countryId, $data->stateId ) );
    }        
}
?>