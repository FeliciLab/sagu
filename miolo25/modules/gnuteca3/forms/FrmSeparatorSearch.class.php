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
 * Separator search form
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 22/05/2009
 *
 **/
class FrmSeparatorSearch extends GForm
{
    public $MIOLO;
    public $module;
    public $busSeparator;
    public $busCataloguingFormat;

    public function __construct()
    {
    	$this->MIOLO  = MIOLO::getInstance();
    	$this->module = MIOLO::getCurrentModule();
    	$this->busSeparator = $this->MIOLO->getBusiness($this->module, 'BusSeparator');
        $this->busCataloguingFormat = $this->MIOLO->getBusiness($this->module, 'BusCataloguingFormat');

        $this->setAllFunctions('Separator', array('separatorIdS','fieldIdS', 'subFieldIdS'), array('separatorId'));
        parent::__construct();
    }


    public function mainFields()
    {
        $cataloguingFormatId = $this->busCataloguingFormat->listCataloguingFormat();
        
        $fields[]   = new MTextField('separatorIdS', $this->separatorIdS->value, _M('Código', $this->module), 3);
        $fields[]   = new GSelection('cataloguingFormatIdS', $this->cataloguingFormatIdS->value, _M('Código do formato de catalogação', $this->module), $cataloguingFormatId);
        $fields[]   = new MTextField('fieldIdS', $this->fieldIdS->value, _M('Campo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]   = new MTextField('subFieldIdS', $this->subFieldIdS->value, _M('Subcampo',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]   = new MTextField('contentS', $this->contentS->value, _M('Conteúdo',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]   = new MTextField('fieldId2S', $this->fieldId2S->value, _M('Campo 2', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]   = new MTextField('subFieldId2S', $this->subFieldId2S->value, _M('Subcampo2',$this->module), FIELD_DESCRIPTION_SIZE);

        $this->setFields($fields);
    }
}
?>
