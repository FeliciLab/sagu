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
 * Spreadsheet form
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 26/09/2008
 *
 **/
class FrmSpreadsheet extends GForm
{
    public $MIOLO;
    public $module;
    public $busMarcTagListingOption;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->busMarcTagListingOption = $this->MIOLO->getBusiness($this->module, 'BusMarcTagListingOption');
       
        if (!$_REQUEST['level'])
        {
            $_REQUEST['level'] = '#';
        }
        $this->setAllFunctions('Spreadsheet', null, array('category', 'level'), array('category', 'level'));
        parent::__construct();
    }

    public function mainFields()
    {
        if ($this->function == 'update')
        {
            $fields[] = new MTextField('category', $this->category->value, _M('Categoria', $this->module),FIELD_ID_SIZE,null, null, true);
            $fields[] = new MTextField('level', $this->level->value, _M('Nível', $this->module),FIELD_ID_SIZE, null, null, true);
        }
        else if ($this->function == 'insert')
        {
            $listCategory = $this->busMarcTagListingOption->listMarcTagListingOption('CATEGORY');
            $fields[] = new GSelection('category', $this->category->value, _M('Categoria', $this->module), $listCategory, true);
            $validators[] = new MRequiredValidator('category');

            $listLevel = $this->busMarcTagListingOption->listMarcTagListingOption('LEVEL');
            $fields[] = new GSelection('level', $this->level->value, _M('Nível', $this->module), $listLevel, true);
            $validators[] = new MRequiredValidator('level');
        }

        $fields[] = new MTextField('menuName',    null, _M('Nome',   $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('menuLevel',   null, _M('Ordem do menu',   $this->module), FIELD_ID_SIZE);        
        $fields[] = new MMultiLIneField('field', $this->field->value, _M('Campo', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);

        $validators[] = new MRequiredValidator('field');

        // REQUIRED BOX
        $requiredLabel  = new MLabel( _M('Validador da obra', $this->module) . ':' );
        $requiredLabel  ->setWidth(FIELD_LABEL_SIZE);
        $required       = new MMultiLIneField   ('required', $this->required->value, null, null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[]       = new GContainer       ("requiredContainer", array($requiredLabel, $required));

        $repeatFieldRequiredLabel   = new MLabel( _M('Validador de campos repetitivos', $this->module) . ':' );
        $repeatFieldRequiredLabel   ->setWidth(FIELD_LABEL_SIZE);
        $repeatFieldRequired        = new MMultiLIneField   ('repeatFieldRequired', $this->repeatFieldRequired->value, null, null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[]                   = new GContainer       ("repeatFieldRequiredContainer", array($repeatFieldRequiredLabel, $repeatFieldRequired));

        // DEFAULT VALUE BOX
        $defaultValueLabel  = new MLabel( _M('Valor padrão', $this->module) . ':' );
        $defaultValueLabel  ->setWidth(FIELD_LABEL_SIZE);
        $defaultValue       = new MMultiLIneField   ('defaultValue', $this->defaultValue->value, null, null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[]           = new GContainer       ("defaultValueContainer", array($defaultValueLabel, $defaultValue));

        // MENU BASE GROUP
        $lblMenuName = new MLabel( _M('Nome do menu',   $this->module) . ':' );
        $lblMenuLevel = new MLabel( _M('Nível',       $this->module) . ':' );
        $lblMenuOption = new MLabel( _M('Opção',      $this->module) . ':' );

        $lblMenuName->setWidth(FIELD_LABEL_SIZE);
        $lblMenuLevel->setWidth(FIELD_LABEL_SIZE);
        $lblMenuOption->setWidth(FIELD_LABEL_SIZE);

        $this->setFields($fields);
        $this->setValidators($validators);

    }
    
    /**
     * Método reescrito para validar os valores do campo "campo" do formulario.
     */
    public function tbBtnSave_click()
    {
        $needed = array('949.1','949.3','949.c','949.d');
        
        $data = $this->getData();
        $fields = $data->field;
        
        // Valida somente para as planilhas que já tiverem aba de exemplares.
        if ( stripos($fields, '949') )
        {
            $lines = explode("\n", $fields);

            $find = array();

            // Percorre as linhas obtendo os campo necessários.
            foreach ( $lines as $line )
            {
                $val = explode('=', $line);
                $values = explode(',', $val[1]);

                foreach( $values as $value )
                {
                    if ( in_array($value, $needed) )
                    {
                        $find[] = $value;
                    }
                }
            }

            // Compara os arrays.
            $diff = array_diff($needed, $find );

            // Caso tenha diferença entre os arrays, quer dizer que estão faltando alguns campos.
            if ( count($diff) > 0 )
            {
                $this->error(_M('É necessário inserir o(s) campo(s) "@1"', $this->module, implode(',', $diff)));
            
                return false;
            }
        }
        
        parent::tbBtnSave_click(); 
        
    }
}
?>