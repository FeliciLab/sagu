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
 *
 * @since
 * Class created on 16/06/2011
 *
 **/
class FrmMaterialEvaluationSearch extends GForm
{
    public $gStar;
    public function __construct()
    {
        $this->setAllFunctions('MaterialEvaluation', array('materialEvaluationIdS','controlNumberS','personIdS'), array('materialEvaluationId','personIdS'));
        parent::__construct();
    }
    
    public function mainFields()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/controls/GStar.class.php','gnuteca3');
        
        $fields[] = new MTextField('materialEvaluationIdS', '', _M('Código',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('controlNumberS', '', _M('Número de controle',$this->module),FIELD_ID_SIZE );
        $fields[] = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'person');
        $labelData = new MLabel(_M('Período da solicitação', $this->module) . ':');
        $beginDateS = new MCalendarField('beginDateS', $this->beginRequestedDateS_DATE->value, '', FIELD_DATE_SIZE);
        $endDateS = new MCalendarField('endDateS', $this->beginRequestedDateS_DATE->value, '', FIELD_DATE_SIZE);
        $labelHora = new MDiv(null, _M('Hora', $this->module) . ':');
        $beginTimeS = new MTimeField('beginTimeS', $this->beginRequestedDateS_TIME->value, '', FIELD_TIME_SIZE);
        $endTimeS = new MTimeField('endTimeS', $this->beginRequestedDateS_TIME->value, '', FIELD_TIME_SIZE);
        $fields[] = new GContainer('container', array($labelData, $beginDateS, $labelHora, $beginTimeS, $endDateS, $labelHora, $endTimeS));

        $fields[] = new MTextField('commentS', '', _M('Comentário',$this->module),FIELD_DESCRIPTION_SIZE );
        $this->gStar = new GStar('evaluationS');
        $fields[] = new MHContainer('', array(new MLabeL( _M('Avaliação','gnuteca3'). ':'), $this->gStar));

        $this->setFields( $fields );

        $validators[] = new MIntegerValidator('materialEvaluationIdS');
        $validators[] = new MIntegerValidator('controlNumberS');
        $validators[] = new MIntegerValidator('personIdS');
        $validators[] = new MIntegerValidator('evaluationS');
        $validators[] = new MDateDMYValidator('dateS');
        $validators[] = new MTimeValidator('timeS');

        $this->setValidators( $validators );
    }
    
    public function getData()
    {
        $data = parent::getData();
        //por algum motivo o miolo não pega da forma padrão
        $data->evaluationS = $_REQUEST['evaluationS'];
        
        return $data;
    }
    
    public function onkeydown118() //F7 clear
    {
        $this->gStar->jsSetValue('evaluationS', 0);
        $this->setResponse('','limbo');
    }
}
?>