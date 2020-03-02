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
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 21/10/2008
 *
 **/
class FrmMyPenalty extends GSubForm
{
    public $MIOLO;
    public $module;
    public $busAthenticate;
    public $busLibraryUnit;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();

        $this->business = $this->MIOLO->getBusiness( $this->module, 'BusMyPenalty');
        $this->busAthenticate = $this->MIOLO->getBusiness( $this->module, 'BusAuthenticate');
        $this->busLibraryUnit = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnit');

        $this->gridName = 'GrdMyPenalty';
        $this->gridSearchMethod = 'searchPenalty';

        parent::__construct( _M('Histórico de penalidade', $this->module) );
    }

    public function createFields()
    {
        GForm::setFocus('observationS',false);
        $this->busLibraryUnit->onlyWithAccess  = true;
        $fields[] = new GSelection('libraryUnitIdS', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnitForSearch(false, true) , null,null, null, true);
        $fields[] = new MTextField('observationS', null, _M('Observação',$this->module), FIELD_DESCRIPTION_SIZE);
        $lblDate = new MLabel(_M('Data da penalidade', $this->module) . ':');
        $beginBeginPenaltyDateS = new MCalendarField('beginBeginPenaltyDateS' );
        $endBeginPenaltyDateS = new MCalendarField('endBeginPenaltyDateS');
        $fields[] = new GContainer('hctDates', array($lblDate, $beginBeginPenaltyDateS, $endBeginPenaltyDateS));
        $validators[] = new MDateDMYValidator('beginBeginPenaltyDateS');

        $lblDate = new MLabel(_M('Data final da penalidade', $this->module) . ':');
        $beginEndPenaltyDateS = new MCalendarField('beginEndPenaltyDateS', $this->beginEndPenaltyDateS->value, null, FIELD_DATE_SIZE);
        $endEndPenaltyDateS = new MCalendarField('endEndPenaltyDateS', $this->endEndPenaltyDateS->value, null, FIELD_DATE_SIZE);
        $fields[] = new GContainer('hctDates', array($lblDate, $beginEndPenaltyDateS, $endEndPenaltyDateS));
        $validators[] = new MDateDMYValidator('beginEndPenaltyDateS');
        
        $this->setFields( array( Gutil::alinhaForm($fields)) , true );
    }

    public function getData()
    {
        $data = parent::getData();
        $data->personIdS = BusinessGnuteca3BusAuthenticate::getUserCode();

        return $data;
    }
}
?>