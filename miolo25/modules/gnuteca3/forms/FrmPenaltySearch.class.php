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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 23/09/2008
 *
 **/
class FrmPenaltySearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('Penalty', array('personId'),array('personId'));
        parent::__construct();
    }

    public function mainFields()
    {
        
        $fields[] = new MTextField('penaltyIdS', $this->penaltyIdS->value, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'person');
        $fields[] = new MTextField('observationS', $this->observationS->value, _M('Observação', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('internalObservationS', $this->internalObservationS->value, _M('Observação interna', $this->module), FIELD_DESCRIPTION_SIZE);

        $begin[] = new MLabel(_M('Data da penalidade', $this->module) . ':');
        $begin[] = new MCalendarField('beginBeginPenaltyDateS');
        $begin[] = new MCalendarField('endBeginPenaltyDateS');
        $fields[] = new GContainer('hctDates', $begin);

        $end[] = new MLabel(_M('Data final de penalidade', $this->module) . ':');
        $end[] = new MCalendarField('beginEndPenaltyDateS');
        $end[] = new MCalendarField('endEndPenaltyDateS');
        $fields[] = new GContainer('hctDates', $end );

        $fields[] = new MTextField('operatorS', null, _M('Operador', $this->module), 30);

        $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $busLibraryUnit->filterOperator = TRUE;
        $busLibraryUnit->labelAllLibrary = TRUE;
        $fields[] = new GSelection('libraryUnitIdS',   null, _M('Unidade de biblioteca', $this->module), $busLibraryUnit->listLibraryUnit(), null, null, null, TRUE);
        
        $fields[] = new MCheckBox('onlyActive', DB_TRUE, _M('Somente ativos','gnuteca3') );

        $this->setFields( $fields );
        
        $validators[]   = new MIntegerValidator('penaltyIdS');
        $validators[] = new MIntegerValidator('personIdS', _M('Pessoa', $this->module));
        $validators[] = new MDateDMYValidator('beginBeginDateValidateS');
        $validators[] = new MDateDMYValidator('beginEndDateValidateS');
        
        $this->setValidators( $validators );
    }
}
?>