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
 * @author Luiz Gilberto Gregory FÂº [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 24/10/2008
 *
 **/


class MaterialSaveDiv extends MDiv
{
    public $MIOLO;
    public $module;
    public $function,
           $business,
           $busMaterial,
           $busKardexControl,
           $closeButton;


   /**
     * Class constructor
     **/
    function __construct($data)
    {
        $MIOLO    = MIOLO::getInstance();
        $module   = MIOLO::getCurrentModule();
        $function = MIOLO::_REQUEST('function');

        parent::__construct('materialSaveContent');

        $busCatalogue       = $MIOLO->getBusiness( $module, 'BusCataloge');
        $busMaterial        = $MIOLO->getBusiness( $module, 'BusMaterial');
        $busKardexControl   = $MIOLO->getBusiness( $module, 'BusKardexControl');

        $fields[] = new GContainer("buttonsContainer", $buttons);
        $fields[] = new MSeparator('<br>');

        list($cat, $lev) = explode("-", SPREADSHEET_CATEGORY_FASCICLE);

        if ( $busCatalogue->getCategory() == $cat && $busCatalogue->getLevel() == $lev )
        {
            $controlNumberFather    = $busCatalogue->getTagValue(MARC_ANALITIC_ENTRACE_TAG);
            $unidades               = $busCatalogue->getTagValue(MARC_EXEMPLARY_LIBRARY_UNIT_ID_TAG, true);

            if ( $unidades )
            {
                $kardex = $busKardexControl->getKardexOfMaterial($controlNumberFather[0], $unidades);
            }

            if(!$kardex)
            {
                return false;
            }

            if ( $kardex )
            {
                $columsn[] = _M("Código do Assinante",  $module);
                $columsn[] = _M("Library Unit",         $module);
                $columsn[] = _M("Aquisition type",      $module);
                $columsn[] = _M("Publication",          $module);

                foreach ($kardex as $index => $kardexContent)
                {
                    $content[$index][0] = $kardexContent->codigoDeAssinante;
                    $content[$index][1] = $busMaterial->relationOfFieldsWithTable(MARC_KARDEX_LIBRARY_UNIT_ID_TAG, $kardexContent->libraryUnitId, true);

                    $aquisitionType  = $busMaterial->getContentTag($controlNumberFather[0], MARC_KARDEX_ACQUISITION_TYPE_TAG, $kardexContent->line);
                    $publication     = $busMaterial->getContentTag($controlNumberFather[0], MARC_KARDEX_PUBLICATION_TAG, $kardexContent->line);

                    $content[$index][2] = $busMaterial->relationOfFieldsWithTable(MARC_KARDEX_ACQUISITION_TYPE_TAG, $aquisitionType, true);
                    $content[$index][3] = $busMaterial->relationOfFieldsWithTable(MARC_KARDEX_PUBLICATION_TAG, $publication, true);
                }

                $table = new MTableRaw("Collection Kardex", $content, $columsn);
                $table->addAttribute("style", "width:100%");

                $fields[] = new MDiv("publicacao", $table);
                $fields[] = new MSeparator('<br>');
            }
        }

        #info

        $table = new MTableRaw(_M("Campos", $module), null , array('Fields', 'Contents'), 'tableWorkInfo');
        $table->setAlternate(true);
        $table->addAttribute("Style", "width:100%");
        $table->setCellAttribute(0, 0, "width", "35%");
        $table->setCellAttribute(0, 1, "width", "64%");

        $spreadsheet    = $busCatalogue->getSpreadsheetFromSession();
        $controlNumber  = $busCatalogue->getControlNumberFromSession();

        $table->array[] = array(MARC_CONTROL_NUMBER_TAG ." - ". _M("Número de controle", $module) , $controlNumber);

        if($spreadsheet)
        {
            foreach($spreadsheet as $tab => $spEti)
            {
                foreach($spEti as $etiqueta => $objEti)
                {
                    if(!$objEti->subFields || $etiqueta == MARC_EXEMPLARY_FIELD || $etiqueta == MARC_KARDEX_FIELD)
                    {
                        continue;
                    }

                    foreach($objEti->subFields as $subfield => $object)
                    {
                        $value = null;

                        foreach($object->formValue as $linha => $valor)
                        {
                            if($etiqueta == MARC_FIXED_DATA_FIELD && "$etiqueta.$subfield" != MARC_FIXED_DATA_TAG)
                            {
                                continue;
                            }

                            if(strlen($valor))
                            {
                                if(isset($object->fieldContent) && isset($object->fieldContent->options))
                                {
                                    if(isset($object->fieldContent->options[$valor]) && strlen($object->fieldContent->options[$valor]))
                                    {
                                        $valor = $valor ." - ". $object->fieldContent->options[$valor];
                                    }
                                }

                                if(isset($object->prefix) && is_array($object->prefix) && isset($object->loadPrefix[0]) && strlen($object->loadPrefix[0]))
                                {
                                    $valor = "{$object->prefix[$object->loadPrefix[0]]}$valor";
                                }
                                if(isset($object->suffix) && is_array($object->suffix) && isset($object->loadSuffix[0]) && strlen($object->loadSuffix[0]))
                                {
                                    $valor.= "{$object->suffix[$object->loadSuffix[0]]}";
                                }

                                $d = new MDiv("", "$valor");
                                $value.= $d->generate();
                            }
                        }

                        if(!is_null($value))
                        {
                            $label = "{$object->name}" . (strlen($object->label) ? " - {$object->label}" : "" );
                            $table->array[] = array($label, $value);
                        }
                    }
                }
            }
        }

        $div = new MDiv("divWorkInfos", $table, null, array("style" => "/*max-height: 300px; overflow-y: auto;*/ border:1px solid ". CATALOGE_TAG_LABEL_COLOR));

        $imagePlus      = new MImage("iconWorkInfos", null, GUtil::getImageTheme('plus-8x8.png') );
        $imagePlus->addAttribute("style", "margin:2px 0 0 10px;cursor:pointer;");
        $label          = new MText  ('textWorkBox');
        $label->setValue(_M("Informações da obra", $module));
        $label->addAttribute('style', 'cursor: pointer; font-weight: bolder; color: '. CATALOGE_TAG_LABEL_COLOR);

        $containerH = new GContainer("containerLabelFieldsGroup", array(/*$imagePlus*/null, $label));
        //$containerH->addAttribute('onclick', "displayBox('divWorkInfos')");

        $containerTagGroup = new MBaseGroup ("bgWorkInfos", null, null, 'vertical');
        $containerTagGroup->setAttribute    ('style', "display: block; border:0; ");
        $containerTagGroup->setBorder       (0);
        $containerTagGroup->addControl      ($div);

        $workInfos = new MVContainer("workinfo", array($containerH, $containerTagGroup));


        #exemplary

        $spreadsheet    = $busCatalogue->getSpreadsheetFromSession();

        $c = 0;

        if($spreadsheet)
        {
            foreach($spreadsheet as $tab => $spEti)
            {
                foreach($spEti as $etiqueta => $objEti)
                {
                    if(!$objEti->subFields || ($etiqueta != MARC_EXEMPLARY_FIELD && $etiqueta != MARC_KARDEX_FIELD))
                    {
                        continue;
                    }

                    foreach($objEti->subFields as $subfield => $object)
                    {
                        $tableColumns[$c] = "{$object->tag}<br>{$object->label}";

                        foreach($object->formValue as $linha => $valor)
                        {
                            if(strlen($valor))
                            {
                                if(isset($object->fieldContent) && isset($object->fieldContent->options))
                                {
                                    if(isset($object->fieldContent->options[$valor]) && strlen($object->fieldContent->options[$valor]))
                                    {
                                        $valor = $valor ." - ". $object->fieldContent->options[$valor];
                                    }
                                }
                            }
                            else
                            {
                                $valor = ' ';
                            }

                            $d = new MDiv("", "$valor");
                            $tableArray[$linha][$c] = $d->generate();
                        }

                        $c++;
                    }
                }
            }
        }

        $table = new MTableRaw(_M("Exemplares", $module), null , $tableColumns, 'tableExemplaryInfo');
        $table->setAlternate(true);
        $table->addAttribute("Style", "width:100%");
        $table->array = $tableArray;

        $div = new MDiv("divExemplaryInfos", $table, null, array("style" => "/*max-height: 300px;display:block; overflow:auto;*/ border:1px solid ". CATALOGE_TAG_LABEL_COLOR));

        $imagePlus      = new MImage("iconExemplaryInfos", null, GUtil::getImageTheme('minus-8x8.png'));
        $imagePlus->addAttribute("style", "margin:2px 0 0 10px;cursor:pointer;");
        $label          = new MText  ('textExemplaryBox');
        $label->setValue(_M("Informações do exemplar", $module));
        $label->addAttribute('style', 'cursor: pointer; font-weight: bolder; color: '. CATALOGE_TAG_LABEL_COLOR);

        $containerH = new GContainer("containerLabelFieldsGroup", array(/*$imagePlus*/null, $label));
        //$containerH->addAttribute('onclick', "displayBox('divExemplaryInfos')");

        $containerTagGroup = new MBaseGroup ("bgExemplaryInfos", null, null, 'vertical');
        $containerTagGroup->setAttribute    ('style', "display: block; border:0; ");
        $containerTagGroup->setBorder       (0);
        $containerTagGroup->addControl      ($div);

        $exemplaryInfo = new MVContainer("exemplaryinfo", array($containerH, $containerTagGroup));

        $fields[] = new MDiv("geralContent", array( $workInfos, $exemplaryInfo ));

        $this->setInner($fields);

    }
}
?>