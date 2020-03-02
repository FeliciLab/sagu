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
 * Tag form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 25/09/2008
 *
 **/
class FrmTag extends GForm
{
    /** @var BusinessGnuteca3BusTag  */
    public $business;

    function __construct()
    {
        $this->setAllFunctions('Tag', null, array('fieldId', 'subfieldId'), array('fieldId','subfieldId'));
        parent::__construct();

        if (($this->function == 'insert') && ($this->getEvent() == 'tbBtnNew:click'))
        {
            GRepetitiveField::clearData('tag');
        }
    }


    public function mainFields()
    {   
        $fields[] = $fieldId = new MTextField('fieldId', $this->fieldId->value, _M('Campo', $this->module), 3);
        $fields[] = $subfieldId = new MTextField('subfieldId', null, _M('Subcampo', $this->module), 1);

        if ($this->function == 'update')
        {
            $fieldId->setReadOnly(TRUE);
            $subfieldId->setReadOnly(TRUE);
        }

        $fields[] = new MTextField('description', null, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MMultiLIneField ('observation', null,  _M('Observação',   $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[] = new GRadioButtonGroup('isRepetitive', _M('Repetitivo',   $this->module), GUtil::listYesNo(1), DB_FALSE );
        $fields[] = new GRadioButtonGroup('hasSubfield', _M('Subcampos', $this->module), GUtil::listYesNo(1), DB_FALSE );
        $fields[] = new GRadioButtonGroup('isActive', _M('Ativo', $this->module), GUtil::listYesNo(1), DB_FALSE );
        $fields[] = new GRadioButtonGroup('inDemonstration', _M('Demonstração', $this->module) , GUtil::listYesNo(1), DB_FALSE );
        $fields[] = new GRadioButtonGroup('isObsolete', _M('Obsoleto', $this->module) , GUtil::listYesNo(1), DB_FALSE);
        $fields[] = $editor = new GEditor( 'helpX' , null, _M('Ajuda',$this->module) );
        $editor->setConfigValue('width', '625px');
        $this->setFields($fields);

        $validators[]   = new MRequiredValidator('fieldId', null, 3);
        $validators[]   = new MRequiredValidator('subfieldId', null, 1);
        $validators[]   = new MRequiredValidator('description', null, 100);

        $this->setValidators($validators);
    }

    public function tbBtnSave_click($sender=NULL)
    {
    	$data = $this->getData();
        $data->help = $data->helpX;
        
        parent::tbBtnSave_click($sender, $data);
    }
}
?>
