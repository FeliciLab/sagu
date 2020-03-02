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
 * @author Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 * */
class FrmAnalyticsSearch extends GForm
{
    /** @var BusinessGnuteca3Analytics */
    public $business;

    public function __construct()
    {
        if ( GForm::primeiroAcessoAoForm() )
        {
            //Limpa a unidade da biblioteca da sessão para evitar que seja feita
            //busca automática quando existe um acesso anterior a um formulário
            //que define na sessao a unidade, um exemplo disto é o ticket #12066
            $_SESSION['libraryUnitId'] = NULL;
        }

        $this->setAllFunctions('Analytics', array( 'beginDate', 'beginHour', 'endDate', 'endHour', 'analyticsId', 'query', 'libraryUnitId', 'operator', 'personId', 'time', 'ip', 'browser', 'logLevel', 'accessType', 'menu' ), 'analyticsId');
        parent::__construct();
    }

    public function mainFields()
    {
        $busLibrarynUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');

        $lblTimeSpent = new MLabel(_M('Tempo gasto'), $this->module);
        $beginTimeSpentS = new MFloatField('beginTimeSpentS');
        $endTimeSpentS = new MFloatField('endTimeSpentS');


        $fields[] = new MIntegerField('analyticsIdS', null, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('queryS', null, _M('Query', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('__actionS', null, _M('Ação', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('__eventS', null, _M('Evento', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('libraryUnitIdS', null, _M('Unidade de biblioteca', $this->module), $busLibrarynUnit->listLibraryUnit());
        $fields[] = new GSelection('operatorS', null, _M('Operador', $this->module), GOperator::listOperators());
        $fields[] = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'person');
        $fields[] = new MTextField('ipS', null, _M('Ip', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('browserS', null, _M('Navegador', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('logLevelS', null, _M('Nível de log', $this->module), $this->business->listLogLevel());
        $fields[] = new GSelection('accessTypeS', null, _M('Tipo de acesso', $this->module), $this->business->listAccessType());
        $fields[] = new MTextField('menuS', null, _M('Menu', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTimestampField('beginDateTimeS',null, _M('Data/Hora início',$this->module));
        $fields[] = new MTimestampField('endDateTimeS',null, _M('Data/Hora fim',$this->module));
        $fields[] = new GContainer('timeSpentContainer', array( $lblTimeSpent, $beginTimeSpentS, $endTimeSpentS ));
        
        $validators[] = new MTIMEValidator('beginHourS');
        $validators[] = new MTIMEValidator('endHourS');
        $validators[] = new MFloatValidator('beginTimeSpentS');
        $validators[] = new MFloatValidator('endTimeSpentS');

        $this->setFields($fields);
    }
}

?>