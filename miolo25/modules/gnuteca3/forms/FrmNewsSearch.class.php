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
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 19/03/2009
 *
 **/
class FrmNewsSearch extends GForm
{
    public $MIOLO;
    public $module;
    public $action;
    public $busNewsAccess;
    public $businessLibraryUnit;

    public function __construct()
    {
        $this->setAllFunctions('News', array('newsIdS','placeS'),array('newsId'));
        $this->MIOLO                    = MIOLO::getInstance();
        $this->module                   = MIOLO::getCurrentModule();
        $this->action                   = MIOLO::getCurrentAction();
        $this->function                 = MIOLO::_REQUEST('function');
        $this->busNewsAccess            = $this->MIOLO->getBusiness($this->module, 'BusNewsAccess');
        $this->businessLibraryUnit      = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[]       = new MIntegerField('newsIdS', $this->newsIdS->value, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[]       = new GSelection('placeS', $this->typeS->value, _M('Lugar', $this->module), $this->business->listPlace(), null, null, null, FALSE);
        $fields[]       = new MTextField('title1S', $this->titleS->value, _M('Título',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]       = new MTextField('newsS', $this->newsS->value, _M('Notícias',$this->module), FIELD_DESCRIPTION_SIZE);
        $lblDate        = new MLabel(_M('Data', $this->module) . ':');
        $lblDate->setWidth(FIELD_LABEL_SIZE);
        $beginDateS     = new MCalendarField('beginDateS', $this->beginDateS->value, null, FIELD_DATE_SIZE);
        $endDateS       = new MCalendarField('endDateS', $this->endDateS->value, null, FIELD_DATE_SIZE);
        $fields[]       = new GContainer('hctDates', array($lblDate, $beginDateS, $endDateS));

        $lblDate             = new MLabel(_M('Data inicial', $this->module) . ':');
        $lblDate->setWidth(FIELD_LABEL_SIZE);
        $beginBeginDateS     = new MCalendarField('beginBeginDateS', $this->beginBeginDateS->value, null, FIELD_DATE_SIZE);
        $endBeginDateS       = new MCalendarField('endBeginDateS', $this->endBeginDateS->value, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('hctDates', array($lblDate, $beginBeginDateS, $endBeginDateS));

        $lblDate             = new MLabel(_M('Data final', $this->module) . ':');
        $lblDate->setWidth(FIELD_LABEL_SIZE);
        $beginEndDateS     = new MCalendarField('beginEndDateS', $this->beginEndDateS->value, null, FIELD_DATE_SIZE);
        $endEndDateS       = new MCalendarField('endEndDateS', $this->endEndDateS->value, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('hctDates', array($lblDate, $beginEndDateS, $endEndDateS));
        $fields[] = new MTextField('operatorS', $this->operatorS->value, _M('Operador', $this->module), FIELD_DESCRIPTION_SIZE);

        $lbl = new MLabel(_M('É restrita', $this->module) . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $isRestricted = new MRadioButtonGroup('isRestrictedS', null, GUtil::listYesNo(1), $isRestrictedValue, null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctIsRestricted', array($lbl, $isRestricted));

        $lbla = new MLabel(_M('Está ativo', $this->module) . ':');
        $lbla->setWidth(FIELD_LABEL_SIZE);
        $isActive = new MRadioButtonGroup('isActiveS', null, GUtil::listYesNo(1), $isActiveValue, null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new GContainer('hctIsActive', array($lbla, $isActive));
        $fields[] = new GSelection('libraryUnitIdS',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->businessLibraryUnit->listLibraryUnit());

        $this->setFields($fields);
    }

    public function showGroups()
    {
        $data->newsIdS = MIOLO::_REQUEST('newsId');
        $this->busNewsAccess->setData($data);
        $search = $this->busNewsAccess->searchNewsAccess(TRUE);

        if ($search)
        {
	        for ($i=0; $i < count($search); $i++)
	        {
	            $tbData[] = array(
	                $search[$i]->description
	            );
	        }
            
	        $fields[] = new MLabel(_M('Código da notícia', $this->module) . ': '. $data->newsIdS);
	        $tbColumns = array( _M('Grupo', $this->module));
	        $tb = new MTableRaw('', $tbData, $tbColumns);
	        $tb->zebra = TRUE;
        }
        else
        {
        	$tb = new MLabel(_M('Nenhum registro encontrado.', $this->module));
        }

        $this->injectContent($tb, true, _M('Grupos para notícia ', $this->module) . MIOLO::_REQUEST('newsId'));
    }
}
?>