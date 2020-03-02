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
 * Class created on 25/09/2008
 *
 **/
class FrmBarCode extends GForm
{
    public $MIOLO;
    public $module;
    public $action;
    public $function;
    public $busLibraryUnit;

    const INTERVAL_CONTINUOUS = 1;
    const INTERVAL_DISCRETE   = 2;
    const SIZE_SMALL          = '1';
    const SIZE_MEDIUM         = '1.5';
    const SIZE_BIG            = '2';
    const TYPE_CODABAR        = 'codabar';
    const TYPE_BARCODE128     = 'barcode128';

    function __construct($title=NULL)
    {

        $this->MIOLO = MIOLO::getInstance();
        $this->MIOLO->getClass('gnuteca3', 'GPDFLabel');
        $this->MIOLO->getClass('gnuteca3', 'report/rptBarCode');     
    	
        //Define os tipos de codigo de barra.
    	//Os valores devem bater com o nome das classes de geração das barras
        $this->forceCreateFields = true;
     
        if ( !$title )
        {
            $title = _M('Código de barras', $this->module);
            $this->setTransaction('gtcBarcode');
        }

        parent::__construct( $title );

       	if ( GForm::primeiroAcessoAoForm() )
       	{
       		GRepetitiveField::clearData('codes');
       	}
    
    }
    
    /**
     * Retorna relação de tipos de intervalo
     * 
     * @return array
     */
    public function getIntervalList()
    {
        return array(
            array(_M('Contínuo', $this->module), self::INTERVAL_CONTINUOUS),
            array(_M('Discreto', $this->module), self::INTERVAL_DISCRETE),
        );
    }
    
    
    /**
     * Retorna relação de tamanhos de fontes
     * 
     * @return array
     */
    public function getSizeList()
    {
        for ($i = 1 ; $i <= 30 ; $i++)
        {
            $sizeList[] = array($i,$i);
        }
        
        return $sizeList;
    }
    
    /**
     * Retorna lista com Tamanho das barras
     * 
     * @return array 
     */
    public function getBarcodeList()
    {
        return array(
            array(_M('Pequeno', $this->module),  self::SIZE_SMALL),
            array(_M('Médio', $this->module), self::SIZE_MEDIUM),
            array(_M('Maior', $this->module), self::SIZE_BIG)
        );
    }

    /**
     * Retorna tipo de barra
     * 
     * @return array
     */
    public function getBarcodeTypeList()
    {
        return array(
            array(_M('Codabar', $this->module), self::TYPE_CODABAR),
            array(_M('Código de barras 128', $this->module), self::TYPE_BARCODE128)
        );
    }
    
    /**
     * Retorna relação de caracteres
     * 
     * @return array
     */
    public function getCharactersList()
    {
        $characters[] = array('0', 'Não fixos');
        $characters[] = array('6', '6');
        $characters[] = array('8', '8');
        $characters[] = array('10', '10');
        $characters[] = array('12', '12');
        
        return $characters;
    }
    
    public function mainFields()
    {
        $lbl = new MLabel( _M('Intervalo', $this->module) . ':' );
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $interval = new MRadioButtonGroup('interval', null, $this->getIntervalList(), self::INTERVAL_CONTINUOUS, null, 'vertical');
        $form     = $this->manager->page->getFormId();
        $interval->addAttribute('onchange', "miolo.doAjax( (dojo.byId('interval_0') .checked ? 'getContinuousFields' : 'getDiscreteFields') ,'','{$form}');");
        $fields[] = $formatType = new GSelection('formatType', rptBarCode::BARCODE_FORMAT_EXEMPLARY, _M('Formato', $this->module), $this->getBarcodeFormatTypeList() , null, null, null, true);
        $formatType->addAttribute('onchange', 'javascript:' . GUtil::getAjax('formatTypeOnChange'));
        
        $fields[] = new GContainer('hctInterval', array($lbl, $interval));

        $fields[] = new MDiv('divInterval', $this->getContinuousFields(TRUE));

        $fields[] = $fontSize = new GSelection('fontSize', '10', _M('Tamanho da fonte', $this->module) , $this->getSizeList() , null, null, null, true);
        $fontSize->addStyle('width','60px');

        $fields[] = new MTextField('beginLabel', '1', _M('Etiqueta inicial', $this->module), 5);
        $fields[] = new MTextField('text', null, _M('Texto', $this->module), FIELD_DESCRIPTION_SIZE, new MDiv('divHint',$this->getFormatTypeHint(rptBarCode::BARCODE_FORMAT_EXEMPLARY)) );
        $fields[] = new GRadioButtonGroup('size', _M('Tamanho do código de barra','gnuteca3'), $this->getBarcodeList(), self::INTERVAL_CONTINUOUS);
        $type = new GRadioButtonGroup('type', _M('Tipo','gnuteca3'), $this->getBarcodeTypeList(), self::TYPE_CODABAR, _M('Se selecionado, você apenas conseguirá utilizar números inteiros.', 'gnuteca3'));
        //troca o label do code 128
        foreach ( $type->controls as $todosControles )
        {
            foreach ($todosControles as $a)
            {
                if ( $a instanceof MRadioButtonGroup )
                {
                    clog($a->controls);
                
                     foreach ($a->controls as $controles)
                     {
                          $controles[1]->hint = _M("Se selecionado, você conseguirá utilizar qualquer carácter.", 'gnuteca3');
                     }
                }
            }
        }
        $fields[] = $type;

        $fields[] = $characters = new GSelection('characters', $this->getFormValue('characters', '8'), _M('Caracteres', $this->module), $this->getCharactersList() , null, null, null, true);
        $characters->addStyle('width','80px');

        $fields[] = $logo = new MCheckBox( 'logo', DB_TRUE, _M("Adicionar logo da insituição"), false);
        
        Gutil::accessibility( $logo, null, _M("Determina a adiçao ou não do logo da instituição ao documento. Para modificar o logo Acesse Admnistração->Geral->Arquivo e modifique o arquivo images/logo.jpg") );
        
        $labelLayout = new MLabel( _M('Modelo da etiqueta:', $this->module) );
        $labelLayout->setWidth(FIELD_LABEL_SIZE);
        $labelLayoutId = new GLookupTextField ('labelLayout',  DEFAULT_BARCODE_LABEL_LAYOUT, '', FIELD_LOOKUPFIELD_SIZE, null, null, 'labelLayoutDescription, lines, columns, topMargin, leftMargin, verticalSpacing, horizontalSpacing, labelHeight, labelWidth, pageFormat', $this->module, 'LabelLayout');
        $labelLayoutId->setContext($this->module, $this->module, 'LabelLayout', 'filler', 'labelLayout,labelLayoutDescription,lines,columns,topMargin,leftMargin,verticalSpacing,horizontalSpacing,labelHeight,labelWidth,pageFormat', '', true);
        $labelLayoutDescription = new MTextField ('labelLayoutDescription', '', null, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $labelLayoutDescription->setReadOnly(true);
        $labelLayoutContainer = new GContainer('labelLayoutContainer', array ($labelLayout, $labelLayoutId, $labelLayoutDescription));
        $fields[] = $labelLayoutContainer;
        $fields[] = FrmBarCode::getLabelFields();

        $fields[] = new MButton('BtnPrint', _M('Gerar', $this->module) , GUtil::getAjax('tbBtnPrint_click'), GUtil::getImageTheme('print-16x16.png'));
        $fields[] = new MSeparator('');

        $this->forceFormContent = TRUE;
        $this->setFields($fields);
        $this->setLabelWidth(FIELD_LABEL_SIZE);
        $this->setShowPostButton(false);
        
        $this->_toolBar->disableButton( MToolBar::BUTTON_DELETE );
        $this->_toolBar->disableButton( MToolBar::BUTTON_NEW );
        $this->_toolBar->disableButton( MToolBar::BUTTON_RESET );
        $this->_toolBar->disableButton( MToolBar::BUTTON_SAVE );
        
        if ( $this->primeiroAcessoAoForm()  && ( $this->GetField('interval')->value == self::INTERVAL_DISCRETE ) ) 
        {
            $this->page->onload(  GUtil::getAjax('getDiscreteFields') );
        }
    }

    /**
     * Obtem os fields para modo contínuo
     *
     * @param unknown_type $return
     * @return unknown
     */
    public function getContinuousFields($return = FALSE)
    {
        $flds[] = new MTextField('beginCode', null, _M('Código inicial', $this->module), FIELD_ID_SIZE);
        $flds[] = new MTextField('endCode', null, _M('Código final', $this->module), FIELD_ID_SIZE);

        $hct = new MFormContainer('hctContinuous', $flds);
        
        if ( $return )
        {
        	return $hct;
        }
        
        $this->setResponse($hct, 'divInterval');
        $this->setFocus('beginCode');
    }

    /**
     * Obtem os fields para modo discreto
     */
    public function getDiscreteFields()
    {
        $flds[] = new MTextField('itemNumber', null, _M('Código', $this->module), FIELD_ID_SIZE);
        $cols[] = new MGridColumn(_M('Código', $this->module), MGrid::ALIGN_LEFT, true, null, true, 'itemNumber');
        $valids[] = new GnutecaUniqueValidator('itemNumber', _M('Código', $this->module), 'required');
        $interval = new GRepetitiveField('codes', _M('Itens', $this->module), $cols, $flds);
        $interval->setValidators($valids);
        $fields[] = $interval;
        $this->setResponse($fields, 'divInterval');
        $this->setFocus('itemNumber');
    }

    /**
     * Event triggered when user chooses Print on toolbar
     **/
    public function tbBtnPrint_click($sender=NULL)
    {
        $labelLayout->lines              = $this->getFormValue('lines');
        $labelLayout->columns            = $this->getFormValue('columns');
        $labelLayout->topMargin          = $this->getFormValue('topMargin');
        $labelLayout->leftMargin         = $this->getFormValue('leftMargin');
        $labelLayout->verticalSpacing    = $this->getFormValue('verticalSpacing');
        $labelLayout->horizontalSpacing  = $this->getFormValue('horizontalSpacing');
        $labelLayout->labelHeight        = $this->getFormValue('labelHeight');
        $labelLayout->labelWidth         = $this->getFormValue('labelWidth');
        $labelLayout->pageFormat         = $this->getFormValue('pageFormat');

        $data->fontSize   = $this->getFormValue('fontSize');
        $data->beginLabel = $this->getFormValue('beginLabel');
        $data->characters = $this->getFormValue('characters');
        $data->text       = $this->getFormValue('text');
        $data->size       = $this->getFormValue('size');
        $data->type       = $this->getFormValue('type');
        $data->logo       = MUtil::getBooleanValue( $this->getFormValue('logo') );
        $data->formatType = $this->getFormValue('formatType');
        
        //Fará a verificação para o tipo codabar
        if($data->type == self::TYPE_CODABAR)
        {
            //Realiza a verificação dos campos beginCode e endCode, para ver se não tem letras
            if ($this->getFormValue('interval') == self::INTERVAL_CONTINUOUS)
            {
                $beg = $this->beginCode->value;
                $end = $this->endCode->value;

                if(! is_numeric($beg) || !is_numeric($end))
                {
                    GPrompt::error("Você apenas pode colocar números em um código de barras do tipo 'codabar'.");
                    return;
                }
            }
            else
            {
                $cd = GRepetitiveField::getData('codes');
                foreach ($cd as $cod)
                {
                    if(! is_numeric($cod->itemNumber))
                    {
                        GPrompt::error("Você apenas pode colocar números em um código de barras do tipo 'codebar'.");
                        return;
                    }
                }
            }
        }
        
        
        if ($this->getFormValue('interval') == self::INTERVAL_CONTINUOUS)
        {
            $beginCode   = $this->beginCode->value;
            $endCode     = $this->endCode->value;
            
            for ($x=$beginCode; $x<=$endCode; $x++)
            {
                $codes[] = GUtil::strPad($x, $data->characters, '0', STR_PAD_LEFT);
            }
        }
        else //self::INTERVAL_DISCRETE
        {
            $_codes = GRepetitiveField::getData('codes');
            $codes  = null;
            if ($_codes)
            {
                foreach ($_codes as $code)
                {
                    //Não adicionar exemplares excluídos
                    if (!$code->removeData)
                    {
                        if ($code->itemNumber)
                        {
                            $codes[] = GUtil::strPad($code->itemNumber, $data->characters, '0', STR_PAD_LEFT);
                        }
                    }
                }
            }
        }
        
        $report = new rptBarCode($labelLayout, $data, $codes);
        $report->showDownloadInfo();
    }
    
    /**
     * Retorna o modo do formulário busca ou inserção
     * Faz com que não peça confirmação de edição
     *
     * @return string
     */
    public function getFormMode()
    {
        return 'search';
    }
    
    public function getLabelFields()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getClass( 'gnuteca3' ,'controls/GTree' );
        $module = 'gnuteca3';
        
        $fieldsD[] = new MTextField('lines', null, _M('Linhas', $module), 6,null, null, true);
        $fieldsD[] = new MTextField('columns', null, _M('Colunas', $module), 6,null, null, true);
        $fieldsD[] = new MTextField('topMargin', null, _M('Margem superior', $module), 6,null, null, true);
        $fieldsD[] = new MTextField('leftMargin', null, _M('Margem esquerda', $module), 6,null, null, true);
        $fieldsD[] = new MTextField('verticalSpacing', null, _M('Espaco Vertical', $module), 6,null, null, true);
        $fieldsD[] = new MTextField('horizontalSpacing', null, _M('Espaço horizontal', $module), 6,null, null, true);
        $fieldsD[] = new MTextField('labelHeight', null, _M('Altura', $module), 6,null, null, true);
        $fieldsD[] = new MTextField('labelWidth', null, _M('Largura', $module), 6,null, null, true);
        $fieldsD[] = new MTextField('pageFormat', null, _M('Formato da página', $module), 15,null, null, true);
        
        $fieldsD = new MFormContainer('abc', $fieldsD );
        $fieldsD = new MBaseGroup('baseFields', _M('Configuração da página', $module), array($fieldsD), 'vertical');
        
        $tree[0]->title = _M('Detalhes do modelo da etiqueta','gnuteca3');
        $tree[0]->content = $fieldsD;
       
        return new GTree('abc2', $tree);
    }
    

    /**
     * Retorna o tipo da formatação do código
     * 
     * @return array
     */
    public function getBarcodeFormatTypeList()
    {
        return array(
            array(rptBarCode::BARCODE_FORMAT_EXEMPLARY, _M('Exemplar', $this->module),),
            array(rptBarCode::BARCODE_FORMAT_PERSON, _M('Pessoa', $this->module)),
            array(rptBarCode::BARCODE_FORMAT_OTHER, _M('Outros', $this->module))
        );
    }
    
    /**
     * Gerenciador das chamadas ajax para o divHint
     * 
     * @param type $args 
     */
    public function formatTypeOnChange($args)
    {
        $this->setResponse($this->getFormatTypeHint($args->formatType), 'divHint');
    }
    
    /**
     * Função que retorna o texto da hint do campo id=text
     * 
     * @param int $formatType
     * @return string 
     */
    public function getFormatTypeHint($formatType = null)
    {

        $hintText[rptBarCode::BARCODE_FORMAT_EXEMPLARY] = _M( 'Pode-se utilizar campos marc e funções. Ex.: $245.a.','gnuteca3');
        $hintText[rptBarCode::BARCODE_FORMAT_PERSON] = _M( 'Pode-se utilizar atributos da pessoa Ex.: $personName,$personId.','gnuteca3');
        $hintText[rptBarCode::BARCODE_FORMAT_OTHER] = _M( 'O que for digitado neste campo será colocado exatamente igual na descrição do código.','gnuteca3');
        return $hintText[$formatType];
    }    
    
}
?>
