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
 * Class created on 05/11/2008
 *
 **/
$MIOLO->uses( 'forms/FrmBarCode.class.php', $module );
class FrmBackOfBook extends FrmBarCode
{
    public $MIOLO;
    public $module;
    public $business;
    public $busExemplaryControl;
    public $busLibraryUnit;

    const OPTION_CONTROL_NUMBER = 1;
    const OPTION_ITEM_NUMBER    = 2;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->business            = $this->MIOLO->getBusiness($this->module, 'BusFormatBackOfBook');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busLibraryUnit      = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');

        //Define internal format options
        define('OPTION_ILABEL_NO',       1);
        define('OPTION_ILABEL_YES_SAME', 2);
        define('OPTION_ILABEL_YES_DIFF', 3);
        define('OPTION_ILABEL_ONLY',     4);

        define('OPTION_BARCODE_NO', 1);
        define('OPTION_BARCODE_YES_SAME', 2);
        define('OPTION_BARCODE_YES_DIFF', 3);
        
        $this->forceCreateFields = true;
        $this->setTransaction('gtcBackOfBook');
        parent::__construct( _M('Lombada', $this->module) );

        if (GForm::primeiroAcessoAoForm())
        {
            GRepetitiveField::clearData('codes');
        }

        //força formulário de busca
        $this->setSearchFunction('test');
    }

    public function mainFields()
    {
        $lbl = new MLabel( _M('Intervalo', $this->module) . ':' );
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $interval   = new MRadioButtonGroup('interval', null, $this->getIntervalList(), FrmBarCode::INTERVAL_CONTINUOUS, null, 'vertical');
        $form       = $this->manager->page->getFormId();
        $interval->addAttribute('onchange', "miolo.doAjax( (dojo.byId('interval_0').checked ? 'getContinuousFields' : 'getDiscreteFields') ,'','{$form}');");
        $fields[]   = new GContainer('hctInterval', array($lbl, $interval));

        $options = array(
            array(_M('Número do exemplar', $this->module), self::OPTION_ITEM_NUMBER),
            array(_M('Número de controle', $this->module), self::OPTION_CONTROL_NUMBER),
        );

        $fields[] = new GRadioButtonGroup('exemplarys', _M('Exemplares', $this->module), $options, self::OPTION_ITEM_NUMBER, null, 'vertical');
        $fields[] = $divInterval = new MDiv('divInterval', $this->getContinuousFields(TRUE));

        $fields[] = $divOptions = new MDiv('divOptions', $this->getOptionsFields(TRUE));

        $fields[] = new GSelection('formatBackOfBookId', 1, _M('Formato da lombada', $this->module), $this->business->listFormatBackOfBook());
        $fields[] = new MSeparator();
        $fields[] = $fontSize = new GSelection('fontSize', '10', _M('Tamanho da fonte', $this->module) , $this->getSizeList() , false, null, null, true);
        $fontSize->addStyle('width','60px');
        
        $fields[] = new MIntegerField('beginLabel', '1', _M('Etiqueta inicial', $this->module), 5);

        $labelLayout = new MLabel( _M('Modelo de etiqueta','gnuteca3'));
        $labelLayout->setWidth(FIELD_LABEL_SIZE);
        $labelLayoutId = new GLookupTextField ('labelLayout',  DEFAULT_BARCODE_LABEL_LAYOUT, '', FIELD_LOOKUPFIELD_SIZE, null, null, 'labelLayoutDescription, lines, columns, topMargin, leftMargin, verticalSpacing, horizontalSpacing, labelHeight, labelWidth, pageFormat', $this->module, 'LabelLayout');
        $labelLayoutId->setContext($this->module, $this->module, 'LabelLayout', 'filler', 'labelLayout,labelLayoutDescription,lines,columns,topMargin,leftMargin,verticalSpacing,horizontalSpacing,labelHeight,labelWidth,pageFormat', '', true);
        $labelLayoutDescription = new MTextField ('labelLayoutDescription', '', null, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $labelLayoutDescription->setReadOnly(true);
        $fields[] = new GContainer('labelLayoutContainer', array($labelLayout, $labelLayoutId, $labelLayoutDescription));
        $fields[] = $this->getLabelFields();

        $fields[] = new MButton('BtnPrint', _M('Gerar', $this->module) , GUtil::getAjax('tbBtnPrint_click'), GUtil::getImageTheme('print-16x16.png'));

        $this->forceFormContent = TRUE;
        $this->setFields($fields);
        $this->setLabelWidth(FIELD_LABEL_SIZE);
        $this->setShowPostButton(false);

        //desabilita botões da toolbar
        $this->_toolBar->disableButton( MToolBar::BUTTON_DELETE );
        $this->_toolBar->disableButton( MToolBar::BUTTON_NEW );
        $this->_toolBar->disableButton( MToolBar::BUTTON_RESET );
        $this->_toolBar->disableButton( MToolBar::BUTTON_SAVE );
        
        if ( $this->primeiroAcessoAoForm()  && ( $this->GetField('interval')->value == self::INTERVAL_DISCRETE ) ) 
        {
            $this->page->onload(  GUtil::getAjax('getDiscreteFields') );
        }

    }

    public function tbBtnPrint_click($data = null)
    {
        $beginCode     = $this->beginCode->value;
        $endCode       = $this->endCode->value;
        $interval      = $data->interval;
        $exemplarys    = $data->exemplarys;
        
        if ( ($interval == FrmBarCode::INTERVAL_CONTINUOUS) && ($exemplarys == self::OPTION_ITEM_NUMBER) )
        {
            $lengthFirst = strlen(trim($beginCode));

            //Se foi passada a quantidade de zeros para preencher o codigo
            if ($data->barCodeCharacter > 0 )
            {
                $lengthFirst = $data->barCodeCharacter;
            }
            
            /**
             * Nesta parte do código tive que incremantar e decremantar a variável $beginCode.
             * Motivo: no funcionamento do script por intervalo continuo, a variável $beginCode chegava
             * até a função for mas seu valor não passava por incremento chegando na funçao strPad
             * e não sendo tratada como deveria.
             * O incremento e decremento foi feito para a variável perder os zeros e ser tratada como
             * a variável $x.
             */
            $beginCode++;
            $beginCode--;
            for ( $x = $beginCode; $x <= $endCode; $x++ )
            {
                $itemNumber = GUtil::strPad($x, $lengthFirst, '0', STR_PAD_LEFT);
                $codes[$itemNumber] = $this->busExemplaryControl->getExemplaryControl( $itemNumber );
            }
        }

        if ( ($interval == FrmBarCode::INTERVAL_DISCRETE) && ($exemplarys == self::OPTION_ITEM_NUMBER) )
        {
            $codeList = GRepetitiveField::getData('codes');
            $codes    = null;

            if ($codeList)
            {
                foreach ($codeList as $key => $c)
                {
                    //Não adicionar exemplares excluídos
                    if (!$c->removeData)
                    {
                        $codes[$key] = $this->busExemplaryControl->getExemplaryControl($c->itemNumber);
                    }
                }
            }
        }

        if ( ($interval == FrmBarCode::INTERVAL_CONTINUOUS) && ($exemplarys == self::OPTION_CONTROL_NUMBER) )
        {
            $lengthFirst = strlen(trim($beginCode));

            for ($x=$beginCode; $x<=$endCode; $x++)
            {
                $itemNumber = GUtil::strPad($x, $lengthFirst, '0', STR_PAD_LEFT);
                $exemplary = $this->busExemplaryControl->getExemplaryOfMaterial($itemNumber);

                if ($exemplary)
                {
                    foreach ($exemplary as $ex)
                    {
                        $codes[ $ex->itemNumber ] = $ex;
                    }
                }
            }
        }

        if ( ($interval == FrmBarCode::INTERVAL_DISCRETE) && ($exemplarys == self::OPTION_CONTROL_NUMBER) )
        {
            $codeList = GRepetitiveField::getData('codes');
            $codes    = null;
            if ($codeList)
            {
                foreach ($codeList as $c)
                {
                    //Não adicionar números de controle excluídos
                    if (!$c->removeData)
                    {
                        $exemplary = $this->busExemplaryControl->getExemplaryOfMaterial($c->itemNumber);

                        if ($exemplary)
                        {
                            foreach ($exemplary as $ex)
                            {
                                $codes[ $ex->itemNumber ] = $ex;
                            }
                        }
                    }
                }
            }
        }
        
        $this->MIOLO->getClass($this->module, 'GPDFLabel');
        $this->MIOLO->getClass($this->module, 'report/rptBackOfBook');

        $report = new rptBackOfBook($data, $codes);
        $report->showDownloadInfo();
    }
    
    public function getOptionsFields($return = FALSE,$args = NULL)
    {

        $options = array(
            OPTION_BARCODE_NO       => _M('Não', $this->module),
            OPTION_BARCODE_YES_SAME => _M('Sim, na mesma etiqueta', $this->module),
            OPTION_BARCODE_YES_DIFF => _M('Sim, em etiqueta diferente', $this->module)
        );        

        $barCodeType = new GSelection('barCodeType', 1, null, $options,false,'','',true);
        $barCodeType->setValue($args->barCodeType);

        $barCodeType->addAttribute('onchange',GUtil::getAjax('changeBackOfBookOptions'));

        $options = array(
            OPTION_ILABEL_NO       => _M('Não', $this->module),
            OPTION_ILABEL_YES_SAME => _M('Sim, na mesma etiqueta', $this->module),
            OPTION_ILABEL_YES_DIFF => _M('Sim, em etiqueta diferente', $this->module),
            OPTION_ILABEL_ONLY     => _M('Somente etiqueta interna', $this->module)
        );

        $internalLabel = new GSelection('internalLabel', 1, null, $options,false,'','',true);
        $internalLabel->setValue($args->internalLabel);
        $internalLabel->addAttribute('onchange',GUtil::getAjax('changeBackOfBookOptions'));          

        if ( !is_null($args) )
        {
            if ( !$args->enableBarcode )
            {
                $barCodeType->setClass('mReadOnly');
                $barCodeType->setAttribute('disabled'); 
                $barCodeType->setEnabled($args->enableBarcode);
            }
            else if ( $barCodeType->getValue() != OPTION_BARCODE_NO )
            {
                $options = array(
                    FrmBarCode::TYPE_CODABAR => _M('Codabar', $this->module),
                    FrmBarCode::TYPE_BARCODE128 => _M('Código de barras 128', $this->module)
                );                
                $barCodeStyle = new GSelection('barCodeStyle', 1, null, $options,false,'','',true);
                $barCodeStyle = new MDiv('barCodeStyle',array(new MLabel(_M('Tipo da barra', $this->module) .":" ), $barCodeStyle ));

                $characters[] = array('0', 'Não fixos');
                $characters[] = array('6', '6');
                $characters[] = array('8', '8');
                $characters[] = array('10', '10');
                $characters[] = array('12', '12');                

                $barCodeCharacter = $characters = new GSelection('barCodeCharacter', 1, null, $characters , null, null, null, true);
                $barCodeCharacter->addStyle('width','80px');
                $barCodeCharacter = new MDiv('barCodeCharacter',array(new MLabel(_M('Caracteres', $this->module) .":" ), $barCodeCharacter ));     
                
                $barCodeText = new MTextField('barCodeText', null,null, FIELD_DESCRIPTION_SIZE, new MDiv('divHint',$this->getFormatTypeHint(rptBarCode::BARCODE_FORMAT_EXEMPLARY)) );
                $barCodeText = new MDiv('barCodeText',array(new MLabel(_M('Texto', $this->module) .":" ), $barCodeText ));                               
            }

            if ( !$args->enableInternalLabel )
            {
                $internalLabel->setClass('mReadOnly');
                $internalLabel->setAttribute('disabled'); 
                $internalLabel->setEnabled($args->enableBarcode);
            }
        }

        //se e para deixar visivel o campo de codigo de barras
        if ( defined('BACK_OF_BOOK_BARCODE_TEMP') && MUtil::getBooleanValue(BACK_OF_BOOK_BARCODE_TEMP) == DB_TRUE)
        {
            $fields[] = new MDiv('barCodeType',array(new MLabel(_M('Código de barras', $this->module) . ":"  ), $barCodeType ));
            $fields[] = $barCodeStyle;
            $fields[] = $barCodeText;
            $fields[] = $barCodeCharacter;
            $fields[] = new MDiv('internalLabel',array(new MLabel(_M('Etiqueta interna', $this->module) .":" ), $internalLabel ));
        }
        else //somente mostra campo da etiqueta interna
        {
            $internalLabel->addAttribute('onchange','');
            $fields[] = new MDiv('internalLabel',array(new MLabel(_M('Etiqueta interna', $this->module) .":" ), $internalLabel ));
        }        
        
        $fields = new MFormContainer('optionFields',$fields);
        
        if ( $return )
        {
            return $fields;
        }
        
        $this->setResponse($fields, 'divOptions');
    }    
    
    public function changeBackOfBookOptions($args)
    {
        //Se veio valor null, aplica o valor de Nao
       $args->barCodeType = is_null($args->barCodeType)? OPTION_BARCODE_NO:$args->barCodeType;
       $args->internalLabel = is_null($args->internalLabel)? OPTION_ILABEL_NO:$args->internalLabel;
        
       //Se tiver seleciona um tipo de codigo de barra.
       if ($args->barCodeType != OPTION_BARCODE_NO)
       {
           $args->enableInternalLabel = false;
           $args->enableBarcode = true;
       }
       else if( $args->internalLabel != OPTION_ILABEL_NO )
       {
           $args->enableInternalLabel = true;
           $args->enableBarcode = false;           
       }
       else
       {
           $args->enableInternalLabel = true;
           $args->enableBarcode = true;                      
       }


       $this->getOptionsFields(FALSE, $args);
    }
}
?>