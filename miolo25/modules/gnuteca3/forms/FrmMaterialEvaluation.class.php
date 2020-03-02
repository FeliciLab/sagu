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
 * @since
 * Class created on 17/06/2011
 *
 **/
class FrmMaterialEvaluation extends GForm
{
    /**
     * valor temporário da avaliação, utilizado para fazer setData do componente gstar
     * @var integer
     */
    protected $tempEvaluation;
    /**
     * atalho para objeto de avaliação
     * @var GStar
     */
    protected $gStar;

    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
        $this->setAllFunctions('MaterialEvaluation', null, array('materialEvaluationId'), array('personId','controlNumber'));

        parent::__construct();
    }


    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/controls/GStar.class.php','gnuteca3');

        if ( $this->function == 'update')
        {
            $fields[] = new MTextField('materialEvaluationId', '', _M('Código',$this->module), FIELD_ID_SIZE,'','',true);
        }
        
        $fields[] = new MTextField('controlNumber', '', _M('Número de controle',$this->module),FIELD_ID_SIZE );
        
        $fields[] = new GPersonLookup('personId', _M('Pessoa', $this->modules), 'person');

        $controls[] = new MLabel(_M('Data', $this->module));
        $controls[] = new MCalendarField('date', null, '', FIELD_DATE_SIZE);
        $controls[] = new MDiv(null, _M('Hora', $this->module) . ':');
        $controls[] = new MTimeField('time', null, '', FIELD_TIME_SIZE);
        $fields[] = new GContainer('hctDateTime', $controls );
        
        $fields[] = new MMultiLineField( 'comment', '', _M('Comentário',$this->module), 100, 10, 100);
        $this->gStar = new GStar('evaluation');
        $fields[] = new MHContainer('', array(new MLabeL( _M('Avaliação','gnuteca3'). ':'), $this->gStar ) );
        
        $this->setFields( $fields );

        $validators[] = new MIntegerValidator('materialevaluationid');
        $validators[] = new MIntegerValidator('controlNumber');
        $validators[] = new MRequiredValidator('controlNumber');
        $validators[] = new MIntegerValidator('personId');
        $validators[] = new MRequiredValidator('personId');
        $validators[] = new MRequiredValidator('date');
        $validators[] = new MIntegerValidator('evaluation');

        $this->setValidators( $validators );
    }

    public function setData($data)
    {
        $this->tempEvaluation = $data->evaluation;
        parent::setData($data);
    }

    public function  getData()
    {
        $data = parent::getData();
        $data->evaluation = MIOLO::_REQUEST('evaluation');
        return $data;
    }

    public function  loadFields()
    {
        parent::loadFields();
        $this->gStar->setValue( $this->tempEvaluation );
    }
    
    public function onkeydown118() //F7 clear
    {
        $this->gStar->jsSetValue('evaluation', 0);
        $this->setResponse('','limbo');
    }
    
}
?>
