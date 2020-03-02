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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
class FrmLibraryUnit extends GForm
{
    public $MIOLO;
	public $module;
    
    /** @var BusinessGnuteca3BusLibraryUnit */
    public $business;
    
    private $busBond,
            $busWeekDay;

    public function __construct()
    {
        $this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
        $this->setAllFunctions('LibraryUnit', null, array('libraryUnitId'), array('libraryName'));
        $this->setGetFunction('getLibraryUnit1');
        
        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busWeekDay = $this->MIOLO->getBusiness($this->module, 'BusWeekDay');
        
        parent::__construct();
        
        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('libraryUnitIsClosed');
            GRepetitiveField::clearData('group');
        }
    }

    public function mainFields()
    {
        if ( $this->function != 'insert' )
        {
            $fields[] = new MTextField('libraryUnitId', '', _M('Código', $this->module), FIELD_ID_SIZE,null, null, true);
        }

        $fields[]     = new MTextField('libraryName', $this->libraryName->value, _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE);
        $validators[] = new MRequiredValidator('libraryName');

        $businessPrivilegeGroup = $this->MIOLO->getBusiness($this->module, 'BusPrivilegeGroup');
        $businessLibraryGroup   = $this->MIOLO->getBusiness($this->module, 'BusLibraryGroup');
        $privilegeOptions = $businessPrivilegeGroup->listPrivilegeGroup();
        
        $fields[] = new GRadioButtonGroup('isRestricted', _M('É restrita', $this->module), GUtil::listYesNo(1), DB_FALSE );
        $fields[] = new GRadioButtonGroup('acceptPurchaseRequest', _M('Aceitar solicitações de compra', $this->module), GUtil::listYesNo(1), DB_FALSE );
        $fields[] = new MTextField('city', $this->city->value, _M('Cidade',$this->module), FIELD_DESCRIPTION_SIZE);;
        $fields[] = new MTextField('zipCode', $this->zipCode->value, _M('CEP',$this->module), FIELD_ID_SIZE);;
        $fields[] = new MTextField('location', $this->location->value, _M('Local',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('number', $this->number->value, _M('Número',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('complement', $this->complement->value, _M('Complemento',$this->module), FIELD_DESCRIPTION_SIZE);;
        $fields[] = new MTextField('email', $this->email->value, _M('E-mail',$this->module), FIELD_DESCRIPTION_SIZE);;
        $fields[] = new MTextField('url', $this->url->value, _M('URL',$this->module), FIELD_DESCRIPTION_SIZE);;
        $fields[] = new GSelection('privilegeGroupId', $this->privilegeGroupId->value, _M('Grupo de privilégio',$this->module), $privilegeOptions ,false, '','' , false );;
        $fields[] = new GSelection('libraryGroupId', $this->libraryGroupId->value, _M('Grupo de biblioteca',$this->module), $businessLibraryGroup->listLibraryGroup());;
        $fields[] = new MTextField('level', null, _M('Nível', $this->module), FIELD_ID_SIZE);;
        $fields[] = new MMultiLineField('observation', null, _M('Observação', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);

        $validators[] = new MCepValidator('zipCode');
        $validators[] = new MEmailValidator('email');
        $validators[] = new MRequiredValidator('privilegeGroupId');
        $validators[] = new MIntegerValidator('level');

        //dias fechado
        $fields[] = $libraryUnitIsClosed = new GRepetitiveField('libraryUnitIsClosed', _M('Dias em que a biblioteca está fechada', $this->module) );
        $fldLibraryUnitIsClosed[] = new GSelection('weekDayId', null, _M('Dia da semana',$this->module) .':', $this->busWeekDay->listWeekDay());

        $libraryUnitIsClosed->setFields($fldLibraryUnitIsClosed);

        $tableClosedValidators[] = new MIntegerValidator('weekDayId',_M('Dia da semana', $this->module),'required', _M('Por favor selecione corretamente o dia da semana.', $this->module) );
        $tableClosedValidators[] = new GnutecaUniqueValidator('weekDayId', _M('Dia da semana', $this->module), 'required');
        $libraryUnitIsClosed->setValidators( $tableClosedValidators );

        $columns[] = new MGridColumn( _M('Código do dia da semana', $this->module), 'left', true, '', false, 'weekDayId');
        $columns[] = new MGridColumn( _M('Dia da semana', $this->module),      'left', true, '', true, 'weekDescription');

        $libraryUnitIsClosed->setColumns($columns);

        //grupos
        $gColumns[] = new MGridColumn( _M('Código',        $this->module), 'left', true, null, true, 'linkId' );
        $gColumns[] = new MGridColumn( _M('Descrição', $this->module), 'left', true, null, true, 'description' );
        $fldGroup[]    = new MHiddenField('description');
        $fldGroup[] = new GSelection('linkId', '', _M('Código do vínculo', $this->module), $this->busBond->listBond(true));
        
        $fields[]   = $group  = new GRepetitiveField('group', _M('Liberar acesso para os grupos', $this->module), $gColumns, $fldGroup, array('edit', 'remove'));
        $gValids[]  = new GnutecaUniqueValidator('linkId', _M('Código do vínculo', $this->module));
        $gValids[]  = new MIntegerValidator('linkId', _M('Código do vínculo', $this->module), 'required');
        $group->setValidators($gValids);

        $this->setFields($fields);
        $this->setValidators($validators);
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
        elseif ( $item == 'libraryUnitIsClosed' )
        {
            $args = $this->libraryUnitIsClosedParse($args);
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
    
    /**
     *  Faz o parse da descrição do dia da semana
     * 
     * @param object $data com dados que estão sendo inseridos no GnutecaRepetitiField
     * @return object $data com a relação de id e descrição do dia da semana 
     */
    public function libraryUnitIsClosedParse($data)
    {
        $weekDay = $this->busWeekDay->listWeekDay();

        if ( is_array($weekDay) )
        {
            //percorre a lista de dias da semana
            foreach( $weekDay as $key => $value )
            {
                //obtém a descrição do dia da semana
                if ( $data->weekDayId == $value[0] )
                {
                    $data->weekDescription = $value[1];
                    break;
                }
            }
        }

        return $data;
    }
    
    
    /**
     *  Método reescrito para fazer o parser da descrição do vínculo na repetitive de grupos
     */
    public function loadFields()
    {
        $this->business->getLibraryUnit1( MIOLO::_REQUEST('libraryUnitId') );
        $this->setData($this->business);
        GRepetitiveField::setData($this->business->libraryUnitIsClosed, 'libraryUnitIsClosed');
        GRepetitiveField::setData($this->groupParse($this->business->group), 'group');
    }
}
?>
