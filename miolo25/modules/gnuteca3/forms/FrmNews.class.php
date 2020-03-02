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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 20/03/2009
 *
 **/
$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
class FrmNews extends GForm
{
	public $MIOLO;
	public $module;
	public $tables;
    public $businessGroupAccess;
    public $businessLibraryGroup;
    public $businessUserGroup;
    public $businessLibraryUnit;
    
    private $busBond;

    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
        $this->businessLibraryGroup   = $this->MIOLO->getBusiness($this->module, 'BusLibraryGroup');
        $this->businessUserGroup      = $this->MIOLO->getBusiness($this->module, 'BusUserGroup');
        $this->businessLibraryUnit    = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busBond                = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->setAllFunctions('News', 'newsId', array('newsId', 'place', 'title1', 'date'), array('newsId'));
        parent::__construct();

        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('group');
        }
    }

    public function mainFields()
    {   
        if ( $this->function == 'update' )
        {
            $fields[] = new MTextField('newsId', null, _M('Código', $this->module), FIELD_ID_SIZE,null, null, true);
        }

        $fields[]       = $place = new GSelection('place', $this->place->value, _M('Lugar', $this->module), $this->business->listPlace(), null, null, null, TRUE);
        $defaultPlace = BusinessGnuteca3BusNews::PLACE_TYPE_INITIAL_SCREEN;
        $place->addAttribute('onchange', "gnuteca.setDisplay( this.value == {$defaultPlace} ? 'dFields' : 'libraryUnitId', true, 'none' ); gnuteca.setDisplay( this.value != {$defaultPlace} ? 'dFields' : 'libraryUnitId', true, 'block' )");
        //chama o onchange do local para esconder/mostrar os campos necessários
        $this->page->onload("dojo.byId('place').onchange();");
        $fields[]       = new MTextField('title1', $this->titleS->value, _M('Título',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]       = $editor = new GEditor( 'news', '',_M('Conteúdo', $this->module));
        
        $fields[]       = new MCalendarField('date', GDate::now()->getDate(GDate::MASK_DATE_DB), _M('Data', $this->module), FIELD_DATE_SIZE );
        $fields[]       = new MCalendarField('beginDate', $this->beginDate->value, _M('Data inicial', $this->module), FIELD_DATE_SIZE, null);
        $fields[]       = new MCalendarField('endDate', $this->endDate->value, _M('Data final', $this->module), FIELD_DATE_SIZE, null);
        $fields[]       = $operator = new MTextField('operator', GOperator::getOperatorId(), _M('Operador', $this->module),FIELD_ID_SIZE, null, null, true);
        $fields[]       = new GRadioButtonGroup('isActive', _M('Ativo', $this->module) , GUtil::listYesNo(1), DB_TRUE );

        $fields[]       = new GSelection('libraryUnitId',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->businessLibraryUnit->listLibraryUnit());
        $dFields[]      = new GRadioButtonGroup('isRestricted', _M('Restrito', $this->module) , GUtil::listYesNo(1), DB_FALSE);

        $dFields[] = new MSeparator('<br/>');

        $fldGroup[] = new GSelection('linkId', '', _M('Código do vínculo', $this->module), $this->busBond->listBond(true));
        $fldGroup[] = new MHiddenField('description');
        $columns[] = new MGridColumn( _M('Código', $this->module), 'left', true, null, true, 'linkId' );
        $columns[] = new MGridColumn( _M('Descrição', $this->module), 'left', true, null, true, 'description' );
        $valids[] = new MIntegerValidator('linkId', _M('Código do vínculo', $this->module), 'required');
        $dFields[] = $group = new GRepetitiveField('group', _M('Grupos com acesso', $this->module), $columns, $fldGroup, array('edit', 'remove'));
        $group->setValidators($valids);

        $fields[] = new MDiv('dFields',$dFields);

        $validators[] = new MRequiredValidator('title1');
        $validators[] = new MRequiredValidator('news');
        $validators[] = new MRequiredValidator('date');
        $validators[] = new MDateDMYValidator('beginDate');
        $validators[] = new MDateDMYValidator('endDate');
        $validators[] = new MRequiredValidator('operator');

        $this->setFields($fields);
        $this->setValidators($validators);
    }

    public function loadFields()
    {
        $data = $this->business->getNews( MIOLO::_REQUEST('newsId') );
        
        //date
        $date = new GDate($this->business->date);
        $this->business->date = $date->getDate(GDate::MASK_DATE_USER);
        
        //beginDate
        $date = new GDate($this->business->beginDate);
        $this->business->beginDate = $date->getDate(GDate::MASK_DATE_USER);
        
        //endDate
        $date = new GDate($this->business->endDate);
        $this->business->endDate = $date->getDate(GDate::MASK_DATE_USER);
        
        $this->setData($this->business);
        //setData na repetitiveField
        GRepetitiveField::setData($this->groupParse($this->business->group), 'group');
    }

    public function tbBtnSave_click($sender=NULL)
    {
        $data = $this->getData();
        $data->group = GRepetitiveField::getData('group');
        
        if ( $data->group )
        {
            foreach ($data->group as $g)
            {
                $linkId = $this->businessUserGroup->getUserGroup($g->linkId)->linkId;
                
            	if (!$g->linkId || $g->linkId != $linkId)
            	{
            		$errors[] = _M('Sem grupo para @1.', $this->module, $g->linkId);
            	}
            }
        }

    	parent::tbBtnSave_click($sender, $data, $errors);
    }
    
   /**
     * Método reescrito para fazer o parser da descrição do vínculo
     * @param type $args
     * @param type $forceMode 
     */
    public function addToTable($args, $forceMode = FALSE)
    {
        $item = $args->GRepetitiveField;
        if ( $item == 'group' )
        {
            $args = $this->groupParse($args);
        }
        
        ($forceMode) ? parent::forceAddToTable($args) : parent::addToTable($args);
    }

    public function forceAddToTable($args)
    {
        $this->addToTable($args, TRUE);
    }
    
    /**
     * Método que trata os dados da repetitive de vínculos
     */
    public function groupParse($data)
    {
        if (is_array($data))
        {
            $arr = array();
            foreach ($data as $val)
            {
                $arr[] = $this->groupParse($val);
            }

            return $arr;
        }
        else if (is_object($data))
        {
            $link = $this->busBond->listBond();
            
            if ( is_array($link) )
            {
                foreach( $link as $key => $values )
                {
                    if ( $values[0] == $data->linkId )
                    {
                        $data->description = $values[1];
                        break;
                    }
                }
            }

            return $data;
        }
    }
    
}
?>