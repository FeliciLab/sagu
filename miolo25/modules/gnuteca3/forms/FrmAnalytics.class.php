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
 * Class created on 29/07/2008
 *
 * */
class FrmAnalytics extends GForm
{

    /** @var BusinessGnuteca3BusLibraryUnit */
    public $business;
    public $valor;

    public function __construct()
    {
        $this->setAllFunctions('Analytics', null, 'analyticsId', 'analyticsId');
        parent::__construct();
    }

    public function mainFields()
    {
        $date = GDate::now();
        //Define campos da data.
        $controls[] = new MLabel(_M('Data/hora', $this->module));
        $controls[] = new MCalendarField('date', $date->getDate(GDate::MASK_DATE_DB));
        $controls[] = new MTextField('hour', ($date->getHour() . ":" . $date->getMinute()));
        $busAnalytics = $this->MIOLO->getBusiness($this->module, 'BusAnalytics');
        $busLibrarynUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');

        if ($this->function == 'update')
        {
            $fields[] = new MIntegerField('analyticsId', null, _M('Código', $this->module), FIELD_ID_SIZE, null, null, true);
        }

        $fields[] = new MTextField('query', NULL, _M('Query', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('__action', NULL, _M('Ação', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = $evento = new MTextField('__event', NULL, _M('Evento', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('libraryUnitId', NULL, _M('Unidade de biblioteca', $this->module), $busLibrarynUnit->listLibraryUnit());
        $fields[] = new GSelection('operator', NULL, _M('Operador', $this->module), GOperator::listOperators());
        $fields[] = new MTextField('personId', NULL, _M('Pessoa', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GContainer('timeContainer', $controls);
        $fields[] = new MTextField('ip', NULL, _M('Ip', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MFloatField('timeSpent', NULL, _M('Tempo gasto', $this->module), FIELD_TIME_SIZE);
        $fields[] = new MTextField('browser', NULL, _M('Navegador', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('logLevel', NULL, _M('Nível de log', $this->module), $this->business->listLogLevel());
        $fields[] = new GSelection('accessType', NULL, _M('Tipo de acesso', $this->module), $this->business->listAccessType());
        $fields[] = new MTextField('menu', NULL, _M('Menu', $this->module), FIELD_DESCRIPTION_SIZE);
        $this->setFields($fields);

        $validators[] = new MTIMEValidator('hour');
        $validators[] = new MRequiredValidator('logLevel');
        $validators[] = new MDATEDMYValidator('date');
        $validators[] = new MFloatValidator('timeSpent');

        $this->setFields($fields);
        $this->setValidators($validators);
        
        $this->valor = $evento->getValue();
        
        if ( $this->function == 'insert' )
        {
            // Obtém os dados em tempo real.
            $data = BusinessGnuteca3BusAnalytics::getRealTimeAnalytics();
            
            // Define os dados no formulário.
            $this->setData($data);
        }
    }

    /**
     * Repassa para o busAnalytics o $data->time no formato correto.
     *
     * @return object $data
     */
    public function getData()
    {
        $data = parent::getData();

        //só concatena caso exista
        if ($data->date && $data->hour)
        {
            $data->time = $data->date . ' ' . $data->hour;
        }
        
        $data->action = $data->__action;
        $data->event = $data->__event;

        return $data;
    }

    /**
     * @param object $data
     */
    public function setData($data)
    {
        $time = new GDate($data->time);
        $data->hour = $time->getHour() . ':' . $time->getMinute();
        $data->date = $time->getDate(GDate::MASK_DATE_USER);
        
        $data->__event = $data->event;
        $data->__action = $data->action;

        parent::setData($data, true);
    }

}

?>