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
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 08/10/2008
 *
 **/

class BusinessGnuteca3BusCataloge extends GBusiness
{
    /**
     * Attributes
     */
    public  $MIOLO, $function;
    private $session;

    private $businessMarcTagListing;
    /**  @var BusinessGnuteca3BusSpreadsheet  */
    private $businessSpreadsheet;
    private $businessControlFieldDetail;
    private $businessMaterialControl;
    private $businessExemplaryControl;
    private $businessKardexControl;
    private $businessExemplaryStatus;
    private $businessMaterial;
    private $businessMaterialType;
    private $businessLibraryUnit;
    private $businessMaterialGender;
    private $businessMaterialHistory;
    private $businessExemplaryFutureStatusDefined;
    private $businessRulesToCompleteFieldsMarc;
    private $businessLinkOfFieldsBetweenSpreadsheets;
    private $businessTag;
    private $businessDictionary;
    private $businessPreCatalogue;
    private $businessSearchableField;
    private $businessPrefixSuffix;
    private $businessSeparator;

    private $leaderFieds,
            $baseSpreadSheet,
            $spreadsheetCategory,
            $defaultValues,
            $requiredFields,
            $repeatFieldRequired,
            $historyRevision,
            $specialLink,
            $tab,
            $etiqueta,
            $subcampo,
            $hiddenDefaultFields,
            $objetMaterialHistory;

    public  $spreadSheet,
            $controlNumber,
            $entryDate,
            $lastChange,
            $marcLeader,
            $leaderString,
            $loadFields,
            $duplicateMaterial,
            $cutterComplement,
            $workNumber,
            $controlNumberFather,
            $preCatalogue   = false,
            $normalSave     = false,
            $preCatSave     = false,
            $cameFromPreCat = false,
            $localValidator,
            $level;

    /**
     * Constructor Method
     */
    function __construct()
    {
        parent::__construct();

        $this->MIOLO        = MIOLO::getInstance();
        $this->module       = MIOLO::getCurrentModule();
        $this->function     = MIOLO::_REQUEST('function');
        $this->session      = new MSession();

        // LOCAL CLASS VARS
        $this->loadFields           = false;
        $this->duplicateMaterial    = false;
        $this->historyRevision      = null;
        $this->workNumber           = null;
        $this->specialLink          = false;
        $this->controlNumberFather  = false;
        $this->preCatalogue         = false;
        $this->cameFromPreCat       = false;

        // LOCAL BUSINESS INSTANCE
        $this->businessMarcTagListing               = $this->MIOLO->getBusiness($this->module, 'BusMarcTagListing');
        $this->businessMarcTagListingOption         = $this->MIOLO->getBusiness($this->module, 'BusMarcTagListingOption');
        $this->businessSpreadsheet                  = $this->MIOLO->getBusiness($this->module, 'BusSpreadsheet');
        $this->businessTag                          = $this->MIOLO->getBusiness($this->module, 'BusTag');
        $this->businessControlFieldDetail           = $this->MIOLO->getBusiness($this->module, 'BusControlFieldDetail');
        $this->businessMaterialControl              = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');
        $this->businessExemplaryControl             = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->businessKardexControl                = $this->MIOLO->getBusiness($this->module, 'BusKardexControl');
        $this->businessExemplaryStatus              = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->businessMaterial                     = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->businessLibraryUnit                  = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->businessMaterialGender               = $this->MIOLO->getBusiness($this->module, 'BusMaterialGender');
        $this->businessMaterialType                 = $this->MIOLO->getBusiness($this->module, 'BusMaterialType');
        $this->businessMaterialHistory              = $this->MIOLO->getBusiness($this->module, 'BusMaterialHistory');
        $this->businessRulesToCompleteFieldsMarc    = $this->MIOLO->getBusiness($this->module, 'BusRulesToCompleteFieldsMarc');
        $this->businessPreCatalogue                 = $this->MIOLO->getBusiness($this->module, 'BusPreCatalogue');
        $this->businessDictionary                   = $this->MIOLO->getBusiness($this->module, 'BusDictionary');
        $this->businessPrefixSuffix                 = $this->MIOLO->getBusiness($this->module, 'BusPrefixSuffix');
        $this->businessSearchableField              = $this->MIOLO->getBusiness($this->module, 'BusSearchableField');
        $this->businessSeparator                    = $this->MIOLO->getBusiness($this->module, 'BusSeparator');

        $this->businessExemplaryFutureStatusDefined     = $this->MIOLO->getBusiness($this->module, 'BusExemplaryFutureStatusDefined');
        $this->businessLinkOfFieldsBetweenSpreadsheets  = $this->MIOLO->getBusiness($this->module, 'BusLinkOfFieldsBetweenSpreadsheets');
    }


    /**
     * Retorna um objecto com informações necessaria para montar o form leader marc
     *
     * @return object
     */
    public function getLeaderFields()
    {
        // Busca os campos na base
        $marcLeaderTags = $this->businessControlFieldDetail->getControlFieldDetail("000", "#", "LR", "t");

        // carrega a string leader
        $this->loadLeaderString();

        // percorre os campos montando o objecto
        foreach($marcLeaderTags as $i => $content)
        {
            $index = "{$content->fieldid}-{$content->beginposition}";

            $value = $content->defaultvalue;
            
            if(($this->loadFields || $this->specialLink) && strlen($this->leaderString))
            {
                $value = substr($this->leaderString, $content->beginposition, $content->lenght);
            }

            $this->leaderFields[$index]->name           = $index;
            $this->leaderFields[$index]->fieldName      = $index;
            $this->leaderFields[$index]->label          = $content->description;
            $this->leaderFields[$index]->value          = array();
            $this->leaderFields[$index]->formValue      = array();
            $this->leaderFields[$index]->baseValue      = null;
            $this->leaderFields[$index]->defaultValue[0]->$index    = $value;
            $this->leaderFields[$index]->loadValue[0]->$index       = $value;
            $this->leaderFields[$index]->type           = FIELD_TYPE_TEXT;
            $this->leaderFields[$index]->lenght         = $content->lenght;
            $this->leaderFields[$index]->beginposition  = $content->beginposition;

            $tagListing = $this->businessMarcTagListing->getMarcLeaderTags($content->marctaglistid);

            if($tagListing && isset($tagListing->options))
            {
                $this->leaderFields[$index]->type = FIELD_TYPE_COMBO;

                foreach($tagListing->options as $ops)
                {
                    $this->leaderFields[$index]->fieldContent->options[$ops->option] = $ops->description;
                }
            }
        }

        $this->saveContent();
        return $this->leaderFields;
    }


    /**
     * Carrega a String leader
     *
     */
    function loadLeaderString()
    {
       if(!is_null($this->controlNumber) && $this->loadFields)
        {
            if(!$this->preCatalogue)
            {
                $this->leaderString = $this->businessMaterial->getContentTag($this->controlNumber, MARC_LEADER_TAG);
            }
            else
            {
                $this->leaderString = $this->businessPreCatalogue->getContentTag($this->controlNumber, MARC_LEADER_TAG);
            }
        }

        $this->specialLink = false;

        switch ($this->function)
        {
            case 'repeatMaterial':
                $this->leaderString = $this->getLeaderStringFromSession();
                $this->specialLink  = true;
            break;

            case 'newBook'      :
                $menus = $this->businessSpreadsheet->getMenus('BK', '4');
                $this->leaderString = $menus[0]->menuoption;
                $this->specialLink  = true;
            break;

            case 'newPeriodic'  :
                $menus = $this->businessSpreadsheet->getMenus('SE', '4');
                $this->leaderString = $menus[0]->menuoption;
            break;

            case 'newColection' :
                $menus = $this->businessSpreadsheet->getMenus('BK', '#');
                $this->leaderString = $menus[0]->menuoption;
                $this->specialLink  = true;
            break;

            case 'addFasciculo' :
            case 'addChildren'  :
            case 'dinamicMenu'  :
                $this->leaderString = str_replace("*", "#", MIOLO::_REQUEST("leaderString"));
                $this->specialLink  = true;
            break;
        }
    }



    /**
     * Monta o Objeto Spreadsheet com todas informações necessarias para montar e trabalhar a planilha de catalogação
     *
     */
    function getSpreadsheet()
    {
        $this->spreadsheetCategory  = $this->getSpreadsheetCategory();
        $this->level                = $this->leaderFields[  LEADER_TAG_ENCODING_LEVEL  ]->value[0];
        $this->spreadSheet      = null;
        $this->baseSpreadSheet  = $this->businessSpreadsheet->getSpreadsheet($this->spreadsheetCategory, $this->level);

        if ( !$this->baseSpreadSheet )
        {
            throw new Exception( _M('Categoria da planilha "@1" e nível "@2" não está no padrão.', $this->module, $this->spreadsheetCategory, $this->level)  );
        }

        if ( $this->loadFields && !$this->controlNumberFather )
        {
            $this->controlNumberFather = $this->businessMaterialControl->getControlNumberFather($this->controlNumber);
        }

        //Monta os array de campos com validação na catalogação
        //TODO bastante processamento em php, pode ser simplificado
        $this->requiredFields       = $this->getSpreadsheetFieldsValidator();
        $this->repeatFieldRequired  = $this->getSpreadsheetRepeatFieldsValidators();

        // ADICIONA A TAB COM OS CAMPOS 008
        $this->getTags008();

        $fields = explode("\n", $this->baseSpreadSheet->field);

        $this->tab = 1;

        //Percore os campos criando um array a ser utilizado como filtro na busca dos objetos tags
        $campos = array();
        
        foreach($fields as $content)
        {
            list($tabName, $c) = explode("=", $content);

            $aCampos = explode(",", $c);

            foreach ($aCampos as $aCampo)
            {
                $tag = explode('.', $aCampo);
                
                if ($tag[0])
                {
                    if ($tag[1])
                    {
                        //Caso tenha subcampo é adicionado ao array. Também adiciona o campo principal no array
                        $campos[$tag[0]]['#']  = '#';
                        $campos[$tag[0]][]  = $tag[1];
                    }
                    else
                    {
                        //Somente o campo. Este caso irá trazer todos os subcampos, inclusível o principal
                        $campos[$tag[0]]  = null;
                    }
                }

            }
        }

        //Verifica se os campos são lookups para dar preferência ao lookup. O lookup anula os selections
        $lookups = explode("\n", CATALOGUE_LOOKUP_FIELDS);
        $novoLokups = array();
        
        foreach( $lookups as $lookup )
        {
            $look = explode('=', $lookup);
            $novoLookups[] = $look[0];
        }
        
        //Busca as tags da planilha no formato objeto
        $tags = $this->businessTag->getTags($campos);
        //Busca todos os dicionários e monta um array que será utilizado no tratamento dos campos
        $dictionaries = $this->businessDictionary->searchDictionary(true);

        if ($dictionaries)
        {
            foreach ($dictionaries as $dictionary)
            {
                $dictTags = explode(',', $dictionary->tags);

                if ($dictTags)
                {
                    foreach ($dictTags as $dictTag)
                    {
                        if (!$myDictionaries[$dictTag]) //pega sempre o primeiro dicionário encontrado
                        {
                            $myDictionaries[$dictTag] = $dictionary;
                        }
                    }
                }
            }
        }

        //Busca todas as regras para completar campos marc e monta um array que será utilizado no tratamento dos campos
        $this->businessRulesToCompleteFieldsMarc->category = $this->spreadsheetCategory;
        $rules = $this->businessRulesToCompleteFieldsMarc->searchRulesToCompleteFieldsMarc(true);

        if ($rules)
        {
            foreach ($rules as $rule)
            {
                if (!$myRules[trim($rule->originField)]) //pega sempre o primeiro dicionário encontrado
                {
                    $myRules[trim($rule->originField)] = $rule;
                }
            }
        }

        //Percore o objeto de tags para montar uma array facilitador e setar algumas propriedades nos campos
        if ($tags)
        {
            foreach ($tags as $tag)
            {
                $myTags[$tag->fieldId][$tag->subfieldId ] = $tag;

                if ( !array_search($tag->fieldId . '.' . $tag->subfieldId, $novoLookups) )
                {
                    //Verifica se o campo deve buscar informações na base. Caso sim, são setadas as opções no objeto para montar um selection
                    $opts = $this->businessMaterial->optionsTable($tag->fieldId . '.' . $tag->subfieldId);
                    if ($opts)
                    {
                        $myTags[$tag->fieldId][$tag->subfieldId]->options = $opts;
                        //Seta este atributo para não sobreescrever caso haja opções na listagem dos campos marc
                        $myTags[$tag->fieldId][$tag->subfieldId]->optionsTable = true;
                    }
                }

                //Verifica se o campo possui um dicionário associado. Caso sim, seta a informação no objeto
                if ($myDictionaries[$tag->fieldId . '.' . $tag->subfieldId])
                {
                    $myTags[$tag->fieldId][$tag->subfieldId]->hasDictionary = $myDictionaries[$tag->fieldId . '.' . $tag->subfieldId];
                }

                //Verifica se o campo possui regra para preenchimento automático. Caso sim, seta a informação no objeto
                if ($myRules[$tag->fieldId . '.' . $tag->subfieldId])
                {
                    $myTags[$tag->fieldId][$tag->subfieldId]->rulesToComplete = $myRules[$tag->fieldId . '.' . $tag->subfieldId];
                }
            }
        }

        //Busca todos os prefixo e sufixos para setar no objeto das tags
        $prefixSuffix = $this->businessPrefixSuffix->searchPrefixSuffix(true);

        if (is_array($prefixSuffix))
        {
            foreach ($prefixSuffix as $preSuf)
            {
                //Somente seta no objeto se a tag existe. Pois nesse array podem conter tag que não fazem parte da atual planilha
                if ($myTags[$preSuf->fieldId][$preSuf->subFieldId]->fieldId)
                {
                    if ($preSuf->type == BusinessGnuteca3BusPrefixSuffix::TYPE_PREFIX)
                    {
                        $myTags[$preSuf->fieldId][$preSuf->subFieldId]->prefix[$preSuf->prefixSuffixId] = $preSuf->content;
                    }
                    elseif ($preSuf->type == BusinessGnuteca3BusPrefixSuffix::TYPE_SUFFIX)
                    {
                        $myTags[$preSuf->fieldId][$preSuf->subFieldId]->suffix[$preSuf->prefixSuffixId] = $preSuf->content;
                    }
                }
            }
        }

        //Busca todos os separadores para setar no objeto das tags
        $separators = $this->businessSeparator->searchSeparator(true);

        if (is_array($separators))
        {
            foreach ($separators as $separator)
            {
                //Somente seta no objeto se a tag existe. Pois nesse array podem conter tag que não fazem parte da atual planilha
                if ($myTags[$separator->fieldId][$separator->subFieldId]->fieldId)
                {
                    $myTags[$separator->fieldId][$separator->subFieldId]->separator[$separator->separatorId] = $separator->content;
                }
            }
        }

        //Busca todos as listagens para setar no objeto das tags
        $tagListings = $this->businessMarcTagListingOption->getOnlyTagOptions(true);

        if ( is_array($tagListings) )
        {
            foreach ($tagListings as $tagListing)
            {
                if ( ereg("[0-9]{3}\.[a-z0-9]{1}", $tagListing->marcTagListingId) ) //verifica se é um campo marc válido
                {
                    list($fieldId, $subfieldId) = explode('.', $tagListing->marcTagListingId);

                    //Somente seta no objeto se a tag existe. Pois nesse array podem conter tag que não fazem parte da atual planilha
                    if ( $myTags[$fieldId][$subfieldId]->fieldId )
                    {
                        unset($tagListing->marcTagListingId);
                        
                        if( (!$myTags[$tag->fieldId][$tag->subfieldId]->optionsTable) && ( !array_search($tagListing->marcTagListingId, $novoLookups) )) //testa se a tag está entre os campos lookups ou vem da base de dados. Caso não, busca as opções das listagens marc
                        {
                            $myTags[$fieldId][$subfieldId]->options[] = $tagListing;
                        }
                    }
                }
            }
        }

        //só entra na atualização
        if ( ($this->controlNumber && $this->function == 'update' || $this->function == 'duplicate') )
        {
            //listas todas as tags modificadas (histórico) essa flag é utilizada para mostrar o botão de histórico.
            $history = $this->businessMaterialHistory->listModifiedTagsForMaterial( $this->controlNumber );

            if ( is_array( $history ) )
            {
                foreach ( $history as $line => $info )
                {
                    $myTags[$info[0]][$info[1]]->history = true;
                }
            }

            //seleciona bussines para buscar os dados
            $objectMaterialManipule = $this->businessMaterial;
            //caso da pré-catalogação
            if ( $this->preCatalogue )
            {
                $objectMaterialManipule = $this->businessPreCatalogue;
            }

            //busca valores da gtcMaterial e define nos objetos das tags
            $objectMaterialManipule->clean();
            $objectMaterialManipule->controlNumber = $this->controlNumber;
            $values = $objectMaterialManipule->searchMaterial("line");

            if ( is_array( $values ) )
            {
                foreach ( $values as $line => $value )
                {
                    $myTags[$value[1]][$value[2]]->materialValue[] = $value;
                    //coloca todas as informações no campo principal, para utilizar em outros casos
                    $myTags[$value[1]]['#']->materialValue[] = $value;
                }
            }
        }

        //Monta a lista de todos os indicadores para ser utilizada a seguir
        $myIndicators = $this->businessMarcTagListing->getAllIndicators();

        // CRIA UM ARRAY COM OS VALORES DEFAULT DE CADA CAMPO
        $defaultValues = $this->getSpreadsheetDefaultValues( $this->baseSpreadSheet , $myTags);

        // PERCORRE AS TABS para montar as planilhas
        foreach($fields as $content)
        {
            list($tabName, $c) = explode("=", $content);
            $campos = explode(",", $c);

            $this->spreadSheet[$this->tab]['tabName'] = $tabName;

            // PERCORRE OS CAMPOS DE UMA DETERMINADA TAB
            foreach ( $campos as $tags )
            {
                list($this->etiqueta, $this->subcampo) = explode(".", $tags);
                $this->subcampo = trim(($this->subcampo) ? $this->subcampo : '*');
                $this->etiqueta = trim($this->etiqueta);
                $this->marcTag  = "{$this->etiqueta}.{$this->subcampo}";

                if(!strlen($this->etiqueta))
                {
                    continue;
                }

                //Acrecenta o campos
                if(!isset($this->spreadSheet[$this->tab][$this->etiqueta]->name) && $myTags[$this->etiqueta]['#']->fieldId)
                {
                    $this->addMarcFieldHeaderObject($myTags[$this->etiqueta]['#']);
                }

                //Acrescenta o indicador do campo procurando na listagem gerada anteriormente
                unset($ind);

                if ($myIndicators[$this->etiqueta . '-I1'])
                {
                    $ind[$this->etiqueta . '-I1'] = $myIndicators[$this->etiqueta . '-I1'];   
                }

                if ($myIndicators[$this->etiqueta . '-I2'])
                {
                    $ind[$this->etiqueta . '-I2'] = $myIndicators[$this->etiqueta . '-I2'];
                }
                
                if ($ind)
                {
                    $this->addTagIndicators($ind,$myTags[$this->etiqueta][$this->subcampo]);
                }

                //Acrescenta o subcampo
                if($this->subcampo != '*' && $myTags[$this->etiqueta][$this->subcampo]->fieldId)
                {
                    $this->setTagVals($myTags[$this->etiqueta][$this->subcampo]);
                }
                //Acrescenta todos os subcampos de um campo. Isso ocorre quando é informado somente o campo na definição da planilha
                else
                {
                    if ( is_array( $myTags[$this->etiqueta] ) )
                    {
                        foreach ($myTags[$this->etiqueta] as $subCampo=>$tag)
                        {
                            if($subCampo != "#")
                            {
                                $this->subcampo = $subCampo;

                                if ($myTags[$this->etiqueta][$subCampo]->fieldId)
                                {
                                    $this->setTagVals($myTags[$this->etiqueta][$subCampo]);
                                }
                            }
                        }
                    }
                }
            }

            $this->tab++;
        }

        $this->saveContent();
        return $this->spreadSheet;
    }



    /**
     * Seta os valores do cabeçalho de uma tab
     *
     * @param object $tag
     */
    function addMarcFieldHeaderObject($tag)
    {
        $this->spreadSheet[$this->tab][$this->etiqueta]->name               = $tag->description;
        $this->spreadSheet[$this->tab][$this->etiqueta]->isRepetitive       = $tag->isRepetitive;
        $this->spreadSheet[$this->tab][$this->etiqueta]->hasSubfield        = $tag->hasSubfield;
        $this->spreadSheet[$this->tab][$this->etiqueta]->help               = $tag->help;
        $this->spreadSheet[$this->tab][$this->etiqueta]->baseGroupDisplay   = 'none';
        $this->spreadSheet[$this->tab][$this->etiqueta]->baseValue          = array();
        $this->spreadSheet[$this->tab][$this->etiqueta]->defaultValue       = $this->defaultValues[$this->etiqueta];
        $this->spreadSheet[$this->tab][$this->etiqueta]->loadValue          = null;

        // BUSCA CONTEUDO DOS CAMPOS
        if($this->loadFields || $this->duplicateMaterial)
        {
            $this->spreadSheetLoadFields($tag);
        }

        // LINK DE CONTEUDOS ENTRE PLANILHAS
        if($this->controlNumberFather)
        {
            $this->businessLinkOfFieldsBetweenSpreadsheets->clean();
            $getLinks = $this->businessLinkOfFieldsBetweenSpreadsheets->getLinksByControlNumberFather($this->controlNumberFather, $this->spreadsheetCategory, $this->level);

            if($getLinks)
            {
                $loadValues = null;
                foreach ($getLinks as $i => $values)
                {
                    list($values->fieldId,      $values->subfieldId)    = explode(".", $values->tag);
                    list($values->fieldIdSon,   $values->subfieldIdSon) = explode(".", $values->tagSon);
                    
                    $content = $this->businessMaterial->getContent($this->controlNumberFather, $values->fieldId, $values->subfieldId, null, false, true);
                  
                    if ( $content )
                    {
                        $dateField = ereg("{$values->fieldId}.{$values->subfieldId}", CATALOGUE_DATE_FIELDS);

                        foreach($content as $ii => $c)
                        {
                            if($dateField)
                            {
                                $c[0] = GDate::construct($c[0])->getDate(GDate::MASK_DATE_USER);
                            }

                            eval("\$loadValues['{$values->fieldIdSon}'][$ii]->spreeadsheetField_{$values->fieldIdSon}_{$values->subfieldIdSon} = \$c[0];");
                        }
                    }
                                      
                }

                if(!is_null($loadValues[$this->etiqueta]))
                {
                    $this->spreadSheet[$this->tab][$this->etiqueta]->loadValue = $loadValues[$this->etiqueta];
                }
            }
        }
    }





    /**
     * Pelo que entendi busca os valores dos campos repetitivos
     *
     */
    function spreadSheetLoadFields($tag)
    {
        $objectMaterialManipule = $this->businessMaterial;

        //PRE-CATALOGAÇÃO
        if($this->preCatalogue)
        {
            $objectMaterialManipule = $this->businessPreCatalogue;
        }

        //caso for duplicação desconsidera as etiquetas abaixo
        if($this->duplicateMaterial)
        {
            if($this->etiqueta          == MARC_EXEMPLARY_FIELD) return; //exemplares
            if("{$tag->tag}"            == MARC_WORK_NUMBER_TAG) return; //obra
            if("{$this->etiqueta}.a"    == MARC_WORK_NUMBER_TAG) return;
            if($this->etiqueta          == MARC_KARDEX_FIELD) return; //campos kardex
        }

        $objectMaterialManipule->clean();
        $objectMaterialManipule->controlNumber    = $this->controlNumber;
        $objectMaterialManipule->fieldid          = $this->etiqueta;

        //TODO otimizar faz bastante select
        //$values = $objectMaterialManipule->searchMaterial("line");
        $values = $tag->materialValue;

        if($values)
        {
            $this->setValuesOnObject($values);
        }

        if($this->preCatalogue || $this->duplicateMaterial || $this->etiqueta != MARC_EXEMPLARY_FIELD)
        {
            return;
        }

        $this->spreadSheet[$this->tab][$this->etiqueta]->loadValue == null;
        $exemplares = $this->businessExemplaryControl->getExemplaryOfMaterial($this->controlNumber);
        
        if($exemplares)
        {
            foreach ($exemplares as $i => $v)
            {
                $this->businessMaterial->clean();
                $this->businessMaterial->controlNumber      = $this->controlNumber;
                $this->businessMaterial->fieldid            = MARC_EXEMPLARY_FIELD;
                $this->businessMaterial->line               = $v->line;
                $values = $this->businessMaterial->searchMaterial();

                if ( $values )
                {
                    $this->setValuesOnObject($values, $v->originalLibraryUnitId);
                }
            }
        }
    }


    public function setValuesOnObject($values, $originalLibraryUnitId = null)
    {
        foreach ($values as $lxx => $vxx)
        {
            $fn = "spreeadsheetField_{$vxx[1]}_{$vxx[2]}";
            $suffixName     = "suffix_$fn";
            $prefixName     = "prefix_$fn";
            $separatorName  = "separator_$fn";
            if($this->function != 'duplicate')
            {
                $this->spreadSheet[$this->tab][$this->etiqueta]->baseValue[$vxx[3]] = $vxx[6];
            }

            if(ereg("{$vxx[1]}.{$vxx[2]}", CATALOGUE_DATE_FIELDS))
            {
                
                $vxx[6] = GDate::construct($vxx[6])->getDate(GDate::MASK_DATE_USER);
            }

            $this->spreadSheet[$this->tab][$this->etiqueta]->loadValue[$vxx[3]]->$fn            = $vxx[6];
            $this->spreadSheet[$this->tab][$this->etiqueta]->loadValue[$vxx[3]]->$suffixName    = $vxx[9];
            $this->spreadSheet[$this->tab][$this->etiqueta]->loadValue[$vxx[3]]->$prefixName    = $vxx[8];
            $this->spreadSheet[$this->tab][$this->etiqueta]->loadValue[$vxx[3]]->$separatorName = $vxx[10];

            if(strlen($originalLibraryUnitId))
            {
                if($this->function != 'duplicate')
                {
                    $this->spreadSheet[$this->tab][$this->etiqueta]->baseValue[$vxx[3]]  = $originalLibraryUnitId;
                }

                $fn = "spreeadsheetField_". MARC_EXEMPLARY_FIELD ."_". MARC_EXEMPLARY_ORIGINAL_LIBRARY_UNIT_ID_SUBFIELD ;
                $this->spreadSheet[$this->tab][$this->etiqueta]->loadValue[$vxx[3]]->$fn = $originalLibraryUnitId;
            }
        }
    }




    /**
     * Carrega campos com valores da bse
     *
     * @param object $tag
     */
    function loadMarcFieldsValues(&$tag)
    {

        if ( $this->duplicateMaterial )
        {
            if($this->etiqueta          == MARC_EXEMPLARY_FIELD) return;
            if("{$tag->tag}"            == MARC_WORK_NUMBER_TAG) return;
            if("{$this->etiqueta}.a"    == MARC_WORK_NUMBER_TAG) return;
        }

        $fn = $tag->fieldName;
        $values = $tag->materialValue;

        if ( $this->loadFields && $tag->isRepetitive == DB_FALSE )
        {
            if ( !$tag->baseValue )
            {
                $values = $values[0][6];

                if ( strlen($values) > 0 )
                {
                    if ( $this->function != 'duplicate' )
                    {
                        $tag->baseValue[0]      = $values;
                    }

                    if ( ereg( $tag->tag, CATALOGUE_DATE_FIELDS) )
                    {
                        $values = GDate::construct($values)->getDate(GDate::MASK_DATE_USER);
                    }

                    $tag->loadValue[0]->$fn = $values;
                }
            }

            $s = $tag->materialValue[0][9];
            $p = $tag->materialValue[0][8];
            $se = $tag->materialValue[0][10];

            $tag->loadSuffix    = strlen($s)  ? array($s) : array();
            $tag->loadPrefix    = strlen($p)  ? array($p) : array();
            $tag->loadSeparator = strlen($se) ? array($se): array();
        }
        elseif ( $this->loadFields && !$tag->baseValue && $tag->isRepetitive == DB_TRUE  )
        {
            if ( $values )
            {
                foreach ( $values as $linha => $v )
                {
                    if ( $this->function != 'duplicate' )
                    {
                        $tag->baseValue[$v[3]]      = $v[6];
                    }

                    if ( ereg($tag->tag, CATALOGUE_DATE_FIELDS) )
                    {
                        $v[6] = GDate::construct($v[6])->getDate(GDate::MASK_DATE_USER);
                    }

                    $tag->loadValue[$v[3]]->$fn = $v[6];
                    $tag->loadSuffix[$v[3]]     = $v[9];
                    $tag->loadPrefix[$v[3]]     = $v[8];
                    $tag->loadSeparator[$v[3]]  = $v[10];
                }
            }
        }
    }

    /**
     * Obtem etiquetas 008 MARC_FIXED_DATA_FIELD
     *
     * @return unknown
     */
    function getTags008()
    {
        //FIXME: adicionado temporariamente pra ter os valores padrão da tag 008
        $this->getSpreadsheetDefaultValues($this->baseSpreadSheet); 
        
        $tags008 = $this->businessControlFieldDetail->getControlFieldDetail(MARC_FIXED_DATA_FIELD, "a", $this->spreadsheetCategory, DB_TRUE);
        
        if ( !$tags008 )
        {
            return false;
        }

        $baseContent = null;

        if($this->loadFields && $this->controlNumber)
        {
            $baseContent =  (!$this->preCatalogue) ? $this->businessMaterial->getContent($this->controlNumber, MARC_FIXED_DATA_FIELD) : $this->businessPreCatalogue->getContent($this->controlNumber, MARC_FIXED_DATA_FIELD);
            
            if($this->function != 'duplicate')
            {
                $tag->baseValue[0]  = $baseContent;
            }
            
            $tag->isActive      = DB_FALSE;
            $this->tab          = MARC_FIXED_DATA_FIELD;
            list($this->etiqueta, $this->subcampo) = explode(".", MARC_FIXED_DATA_TAG);
            $this->setTagVals($tag);
        }
        else
        {
            $this->businessLinkOfFieldsBetweenSpreadsheets->categorySon = $this->spreadsheetCategory;
            $this->businessLinkOfFieldsBetweenSpreadsheets->levelSon = $this->level;
            $this->businessLinkOfFieldsBetweenSpreadsheets->tagSon = '008.a';
            $link = $this->businessLinkOfFieldsBetweenSpreadsheets->searchLinkOfFieldsBetweenSpreadsheets();
            
            // Obtém conteúdo do pai.
            if ( strlen($this->controlNumberFather) && is_array($link) )
            {
                $baseContent = $this->businessMaterial->getContent($this->controlNumberFather, '008', 'a', null, false, true);
                $baseContent = $baseContent[0][0];
            }
        }
        
        $options = $this->businessMarcTagListing->getTagsOptions('008-%');

        foreach($tags008 as $i => $content)
        {
            $tag        = null;
            $index      = MARC_FIXED_DATA_FIELD."-{$content->beginposition}-{$this->spreadsheetCategory}";
            $fieldName  = "spreeadsheetField_". MARC_FIXED_DATA_FIELD ."_{$content->beginposition}-{$this->spreadsheetCategory}";

            $tag->description                   = $content->description;
            $tag->name                          = $index;
            $tag->fieldName                     = $fieldName;
            $tag->isActive                      = $content->isactive;
            $tag->options                       = null;
            $tag->fieldContent->lenght          = $content->lenght;
            $tag->fieldContent->beginposition   = $content->beginposition;

            $value = null;

            // Adiciona valor quando for duplicar ou adicionar filho. Não adiciona valor no campo data.
            if ( strlen( $baseContent) && !( $content->beginposition == '0' && ($this->function == 'duplicate' || $this->function == 'addChildren' ) ))
            {
                $value = substr($baseContent, $content->beginposition, $content->lenght);
                
                //FIXME: rever o código abaixo. Retira os "#" das datas na tag 008, o "#" é definido na preferência MARC_SPACE
                if ( in_array($content->beginposition, array(0, 7, 11, 15, 31)) )
                {
                    $value = str_replace(MARC_SPACE, "", $value); //substitui # por nada, requerido nas datas
                }
            }
            else
            {
                $value = ($this->defaultValues[MARC_FIXED_DATA_FIELD][$i] == 'null') ? '' : $this->defaultValues[MARC_FIXED_DATA_FIELD][$i];
            }

            $tag->defaultValue[0]->$fieldName   = $value;
            $tag->loadValue[0]  ->$fieldName    = $value;

            //no caso de um registro duplicato não deve preencher o valor da base, para que a comparação de um novo registro aconteça no salvar
            if($this->function != 'duplicate')
            {
                $tag->baseValue = ($this->loadFields && $this->controlNumber) ? array($tag->defaultValue[0]->$fieldName) : array();
            }

            $tag->formValue = array();

            if($content->marctaglistid)
            {
                $tag->options   = $options[$content->marctaglistid]->options;              
            }

            $this->tab          = MARC_FIXED_DATA_FIELD;
            $this->etiqueta     = MARC_FIXED_DATA_FIELD;
            $this->subcampo     = "{$content->beginposition}-{$this->spreadsheetCategory}";

            $this->setTagVals( $tag );
        }
    }




    /**
     * Busca valores necessários para o objeto de tag
     *
     * @param stdClass $tag com os dados da etiqueta
     */
    function setTagVals( $tag )
    {
        //FIXME qual o motivo desta condição
        if(!$this->etiqueta || !$this->subcampo || !$this->tab)
        {
            return;
        }

        // DEFINE VARS
        $tag->fieldName = (isset($tag->fieldName) && strlen($tag->fieldName)) ? $tag->fieldName : "spreeadsheetField_{$this->etiqueta}_{$this->subcampo}";
        $fn             = $tag->fieldName;
        $tag->tag       = "{$this->etiqueta}.{$this->subcampo}";

        if ( !$tag->defaultValue && isset( $this->defaultValues[$this->etiqueta] ) )
        {
            $tag->defaultValue = $this->defaultValues[$this->etiqueta];
        }

        // CARREGA OS CAMPOS COM VALORES DA BASE
        $this->loadMarcFieldsValues($tag);

        // SETA OS VALORES NO OBJETO SPREADSHEET
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->name             = $tag->tag;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->fieldName        = $tag->fieldName;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->label            = $tag->description;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->tag              = $tag->tag;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->isRepetitive     = $tag->isRepetitive;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->hasSubfield      = $tag->hasSubfield;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->help             = $tag->help;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->formValue        = array();
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->baseValue        = ($this->function != 'duplicate') ? $tag->baseValue : array();
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->defaultValue     = $tag->defaultValue;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->loadValue        = $tag->loadValue;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->fieldContent     = $tag->fieldContent;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->isActive         = $tag->isActive;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->hasDictionary    = $tag->hasDictionary;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->readOnly         = $tag->readOnly;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->prefix           = $tag->prefix;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->suffix           = $tag->suffix;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->separator        = $tag->separator;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->history          = $tag->history;

        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->loadSuffix       = is_null($tag->loadSuffix)       ? array() : $tag->loadSuffix;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->loadPrefix       = is_null($tag->loadPrefix)       ? array() : $tag->loadPrefix;
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->loadSeparator    = is_null($tag->loadSeparator)    ? array() : $tag->loadSeparator;

        if(isset($this->requiredFields[$tag->tag]))
        {
            $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->workValidator = $this->requiredFields[$tag->tag];
        }
        if(isset($this->repeatFieldRequired[$tag->tag]))
        {
            $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->repeatFieldValidator = $this->repeatFieldRequired[$tag->tag];
        }

        // REGRAS DE PREENCHIMENTO AUTOMATICO DE CAMPOS
        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->rulesToComplete = $tag->rulesToComplete;

        if($this->controlNumberFather)
        {
            $this->businessLinkOfFieldsBetweenSpreadsheets->clean();
            $this->businessLinkOfFieldsBetweenSpreadsheets->type = 2;
            $this->businessLinkOfFieldsBetweenSpreadsheets->tagSon      = "{$this->etiqueta}.{$this->subcampo}";
            $getLinks = $this->businessLinkOfFieldsBetweenSpreadsheets->getLinksByControlNumberFather($this->controlNumberFather, $this->spreadsheetCategory, $this->leaderFields[  LEADER_TAG_ENCODING_LEVEL ]->value[0]);

            if(isset($getLinks[0]))
            {
                $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->referenceCopy  = true;
                $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->readOnly       = true;
            }
        }

        // VERIFICA SE TEM OPTIONS E PREPARA O CAMPO PARA UM MSELECTION
        if ( isset($tag->options) && is_array($tag->options ) )
        {
            $optValue = null;

            foreach ( $tag->options as $ops )
            {
                if ( is_null($optValue) && strlen( str_replace("-", "", $ops->option) ) )
                {
                    $optValue = str_replace("-", "", $ops->option);
                }

                $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->fieldContent->options[str_replace("-", "", $ops->option)] = $ops->description;
            }
        }

        $this->defineMarcFieldType( $tag );
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $tag
     */
    function defineMarcFieldType($tag)
    {
        $lookUpsFields  = null;
        $lookUps        = explode("\n", CATALOGUE_LOOKUP_FIELDS);
        if(is_array($lookUps))
        {
            foreach ($lookUps as $values)
            {
                list($i,$l)         = explode("=", $values);
                list($name, $desc)  = explode(":", $l);
                $lookUpsFields[$i]->name = $name;
                $lookUpsFields[$i]->desc = $desc;
            }
        }

        $dateFields         = explode(",", CATALOGUE_DATE_FIELDS);
        $multiLineFields    = explode(",", CATALOGUE_MULTILINE_FIELDS);

        $type = FIELD_TYPE_TEXT;

        if(is_array($dateFields) && array_search($tag->tag, $dateFields) !== false)
        {
            $type = FIELD_TYPE_DATE;
        }
        elseif(is_array($multiLineFields) && array_search($tag->tag, $multiLineFields) !== false)
        {
            $type = FIELD_TYPE_MULTILINE;
        }
        elseif(isset($lookUpsFields[$tag->tag]))
        {
            $type = FIELD_TYPE_LOOKUP;
            $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->lookUp->Name = $lookUpsFields[$tag->tag]->name;
            $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->lookUp->Desc = $lookUpsFields[$tag->tag]->desc;
        }
        elseif(isset($tag->hasDictionary->tags))
        {
            $type = FIELD_TYPE_DICTIONARY;
        }
        elseif(!is_null($tag->options) && is_array($tag->options))
        {
            $type = FIELD_TYPE_COMBO;
        }
        elseif ($tag->tag == MARC_PERIODIC_INFORMATIONS)
        {
            $type = MARC_PERIODIC_INFORMATIONS;
        }

        $this->spreadSheet[$this->tab][$this->etiqueta]->subFields[$this->subcampo]->type = $type;
    }

    /**
     * define os parametros na classe Catalogue
     *
     * @param stdClass $values
     */
    function setFormValueLeaderFields($values)
    {
        $this->getContent();

        foreach($values as $index => $value)
        {
            if(isset($this->leaderFields[$index]))
            {
                $this->leaderFields[$index]->value[0]       = $value;
                $this->leaderFields[$index]->formValue[0]   = $value;
            }
        }

        $this->saveContent(); //salva os dados na sessão
    }



    /**
     * Retorna a string lider
     *
     * @return a string lider
     */
    function getLeaderString()
    {
        $marcLeaderTags = $this->businessControlFieldDetail->getControlFieldDetail("000", "#", "LR");
        $string = "";

        //TODO otimizar faz 12 selects
        foreach($marcLeaderTags as $i => $content)
        {
            $empty = $this->businessControlFieldDetail->getControlFieldDetailEmptyValue("000", "#", $content->beginposition);
            $empty = isset($empty[0]->emptyValue) ? $empty[0]->emptyValue : '';

            $index = "{$content->fieldid}-{$content->beginposition}";
            $v = (isset($this->leaderFields[$index])) ? $this->leaderFields[$index]->value[0] : $empty;

            if(strlen($v) == $content->lenght)
            {
                $string.= $v;
            }
            else
            {
                $string.= GUtil::strPad($v, $content->lenght , MARC_SPACE);
            }
        }

        $this->leaderString = $string;
        $this->setLeaderInSession($string);
        return $string;
    }



    /**
     * retorna o tipo do material
     *
     * @return o tipo do material
     *
     */
    function getSpreadsheetCategory( $type = null, $level = null )
    {
        $type   = is_null($type)    ? $this->leaderFields[  LEADER_TAG_MATERIAL_TYPE      ]->value[0] : $type;
        $level  = is_null($level)   ? $this->leaderFields[  LEADER_TAG_BIBLIOGRAPY_LEVEL  ]->value[0] : $level;

        $map = array
        (
            'BK' => 'a:cdm,t',
            'BA' => 'a:a',
            'SE' => 'a:s',
            'SA' => 'a:b',
            'MU' => 'c,d,i,j',
            'MP' => 'e,f',
            'VM' => 'g,k,o,r',
            'CF' => 'm',
            'MX' => 'p'
        );

        foreach ( $map as $k => $d )
        {
            $tipos = explode(',',$d);

            foreach ( $tipos as $t )
            {
                $a = explode(':',$t);

                if ( $a[0] != $type )
                {
                    continue;
                }

                if ( count($a) == 2 && ! strchr($a[1], $level) )
                {
                    continue;
                }

                return $k;
            }
        }

        // todos materiais não reconhecidos serão tradadas como categoria 'BK'
        return 'BK';
    }


    /**
     * Obtem os valores padrão para a planilha;
     *
     * @param stdClass o objeto da planilha, que deve ser alterado em alguns casos
     * @param array de stdClass de tags
     */
    function getSpreadsheetDefaultValues( $spreadsheet , $myTags = null )
    {
        $this->defaultValues = null;
        $GFunction = new GFunction();

        if(!ereg(MARC_EXEMPLARY_EXEMPLARY_STATUS_TAG, $spreadsheet->defaultValue))
        {
            $spreadsheet->defaultValue.= "\n". MARC_EXEMPLARY_EXEMPLARY_STATUS_TAG           ."=". DEFAULT_EXEMPLARY_STATUS_PROCESSANDO;
        }
        if(!ereg(MARC_EXEMPLARY_EXEMPLARY_STATUS_FUTURE_TAG, $spreadsheet->defaultValue))
        {
            $spreadsheet->defaultValue.= "\n". MARC_EXEMPLARY_EXEMPLARY_STATUS_FUTURE_TAG    ."=". DEFAULT_EXEMPLARY_STATUS_DISPONIVEL;
        }

        //baseado nos campos com valor padrão, passa por todos obtendo o valor padrão
        if ( isset($spreadsheet->defaultValue) && strlen( $spreadsheet->defaultValue ) )
        {
            $explode = explode("\n", $spreadsheet->defaultValue);

            foreach($explode as $i => $v)
            {
                //pula caso não tenha informações
                if ( !strlen( $v ) )
                {
                    continue;
                }

                list($tag, $val) = explode("=", $v);
                list($eti, $subf) = explode(".", $tag);

                //caso a tag seja 008 ou MARC_FIXED_DATA_FIELD
                if($tag == MARC_FIXED_DATA_FIELD)
                {
                    $values = explode(",", $val);

                    if(!is_array($values))
                    {
                        continue;
                    }

                    foreach($values as $cont)
                    {
                        $this->defaultValues[$eti][] = $GFunction->interpret($cont);
                    }
                    
                    continue;
                }

                if(ereg("-", $tag))
                {
                    list($eti, $subf) =  explode("-", $tag);
                }

                if(ereg(",", $val))
                {
                    $val = explode(",", $val);
                }

                if(!is_array($val))
                {
                    $val = array($val);
                }

                foreach ( $val as $i2 => $v2 )
                {
                    $v2 = $GFunction->interpret($v2);
                    $spreeadsheetFieldName = "spreeadsheetField_{$eti}_{$subf}";
                    $this->defaultValues[$eti][$i2]->$spreeadsheetFieldName = $v2;

                    if ( ( $myTags[$eti][$subf]->isRepetitive == DB_TRUE ) )
                    {
                        $this->hiddenDefaultFields[$eti][$subf] = $v2;
                    }
                }

            }
        }

        // LINK DE CONTEUDOS ENTRE PLANILHAS, pega dados do pai
        if ( $this->controlNumberFather )
        {
            //TODO otimizar dados do pai
            $this->businessLinkOfFieldsBetweenSpreadsheets->clean();
            
            //Somente na atualização não deve trazer os conteúdos do campo pai  .
            if ( $this->function == 'update' )
            {
                $referenceType = 2; //Tipo de referencia é 2 = Referencia, campos filhos que são cópias não devem ser sobreescritos.
            }
                
            $getLinks = $this->businessLinkOfFieldsBetweenSpreadsheets->getLinksByControlNumberFather($this->controlNumberFather, $this->spreadsheetCategory, $this->level, $referenceType);

            if ( $getLinks )
            {
                foreach ($getLinks as $i => $values)
                {
                    list($values->fieldId,      $values->subfieldId)    = explode(".", $values->tag);
                    list($values->fieldIdSon,   $values->subfieldIdSon) = explode(".", $values->tagSon);

                    $content = $this->businessMaterial->getContent($this->controlNumberFather, $values->fieldId, $values->subfieldId, null, false, true);

                    if ( $content )
                    {
                        foreach($content as $ii => $c)
                        {
                            eval("\$this->defaultValues['{$values->fieldIdSon}'][$ii]->spreeadsheetField_{$values->fieldIdSon}_{$values->subfieldIdSon} = \$c[0];");
                        }
                    }
                }
            }
        }

        return $this->defaultValues;
    }


    /**
     * Enter description here...
     *
     */
    function makeSpreadsheetFieldsValidator($qual = 'required')
    {
        if(!isset($this->baseSpreadSheet->$qual) || !strlen($this->baseSpreadSheet->$qual))
        {
            return false;
        }

        $explode = explode("\n", $this->baseSpreadSheet->$qual);

        foreach($explode as $line)
        {
            if(!strlen($line))
            {
                continue;
            }

            list($field,$validatorsLine) = explode("=", $line);

            if(!strlen($field) || !strlen($validatorsLine))
            {
                continue;
            }

            $validators = explode(",", $validatorsLine);
            $val = null;

            foreach($validators as $v)
            {
                if(in_array($v, array('required', 'unique', 'date', 'readonly','url')))
                {
                    $val[] = $v;
                }
            }

            if(is_null($val))
            {
                continue;
            }

            switch($qual)
            {
                case 'required':
                    $this->requiredFields[$field]       = $val;
                break;
                case 'repeatFieldRequired':
                    $this->repeatFieldRequired[$field] = $val;
                break;
            }
        }

        switch($qual)
        {
            case 'required':            return $this->requiredFields;
            case 'repeatFieldRequired': return $this->repeatFieldRequired;
        }
    }

    /**
     * Enter description here...
     *
     */
    function getSpreadsheetRepeatFieldsValidators($field = null)
    {
        $this->repeatFieldRequired = $this->makeSpreadsheetFieldsValidator("repeatFieldRequired");

        if(is_null($field))
        {
            return $this->repeatFieldRequired;
        }

        return $this->repeatFieldRequired[$field];
    }

    /**
     * Enter description here...
     *
     */
    function getSpreadsheetFieldsValidator($field = null)
    {
        $this->requiredFields = $this->makeSpreadsheetFieldsValidator("required");

        if ( is_null($field) )
        {
            return $this->requiredFields;
        }

        return $this->requiredFields[$field];
    }

    /**
     *
     * @param stdClass $indicadores
     * @param array $tag array com os dados da tag
     */
    public function addTagIndicators($indicadores, $tag)
    {
        $cont = null;

        if ( $this->loadFields && $this->controlNumber )
        {
            //TODO otmizado
            //$cont = $this->businessMaterial->getContent($this->controlNumber, $this->etiqueta, null, null, true);
            
            if ( $tag->materialValue[0] )
            {
                $cont->indicator1 = $tag->materialValue[0][4].'';
                $cont->indicator2 = $tag->materialValue[0][5].'';
            }
        }

        foreach ( $indicadores as $i => $v )
        {
            $fieldName = "spreeadsheetField_". str_replace("-", "_", $i);

            //Explode o indicador para garantir que o indice dele está sendo lido corretamente.
            $indicatorIndex = explode('-', $i);
            //TODO Essa expressão regular que deixa somente os numeros da string deveria estar no GString.
            $indicatorIndex = preg_replace("/[^0-9]/", '', $indicatorIndex[1]);

            $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->name          = $i;
            $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->tag = ($indicatorIndex == '1') ? "IND1" : "IND2";
            $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->label         = $v->name;
            $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->fieldName     = $fieldName;
            $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->type          = FIELD_TYPE_COMBO;
            $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->formValue     = array();

            if ( $cont )
            {
                if ( ($this->function != 'duplicate') && ( !$this->preCatalogue ) )
                {
                    $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->baseValue[0] = ($indicatorIndex == '1') ? $cont->indicator1 : $cont->indicator2;
                }
                
                $n = $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->fieldName;
                $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->defaultValue[0]->$n   = ($indicatorIndex == '1') ? $cont->indicator1 : $cont->indicator2;
                $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->loadValue[0]->$n      = ($indicatorIndex == '1') ? $cont->indicator1 : $cont->indicator2;
            }

            if ( isset($this->defaultValues[$this->etiqueta]) && !$cont )
            {
                $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->defaultValue = $this->defaultValues[$this->etiqueta];
            }

            if ( isset( $v->options ) )
            {
                $optValue = null;
                foreach($v->options as $ops)
                {
                    if(is_null($optValue) && strlen($ops->option))
                    {
                        $optValue = $ops->option;
                    }
                    $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->fieldContent->options[$ops->option] = $ops->description;
                }

                if(SET_FIRST_OPTION_OF_THE_INDICATOR_AS_DEFAULT == DB_TRUE && !is_null($optValue) && !strlen($this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->defaultValue[0]->{$fieldName}))
                {
                    $this->spreadSheet[$this->tab][$this->etiqueta]->indicadores[$i]->defaultValue[0]->{$fieldName} = $optValue;
                }
            }
        }

    }


    /**
     * Salva a spreadsheet na base
     */
    function save()
    {
        $this->getContent();

        $this->normalSave = true;
        $this->preCatSave = false;
        $preCatalogue     = false;

        if ( $this->preCatalogue )
        {
            $this->businessPreCatalogue->clean();
            $this->businessPreCatalogue->controlNumber = $this->controlNumber;
            $this->businessPreCatalogue->deleteMaterial(false); //para não remover a capa
            $this->controlNumber = false;
            $preCatalogue        = true;
        }

        $this->preCatalogue = false;
        $this->saveContent();

        // INSERE NA GTCMATERIALCONTROL
        if( !$this->controlNumber )
        {
            $this->businessMaterialControl->clean();

            $this->controlNumber = $this->businessMaterialControl->getNextControlNumber();
            $this->historyRevision = $this->businessMaterialHistory->getNextRevision($this->controlNumber);

            if(!strlen($this->entryDate))
            {
                $this->entryDate = GDate::now()->getDate(GDate::MASK_DATE_USER);
            }

            $this->businessMaterialControl->controlNumber       = $this->controlNumber;
            $this->businessMaterialControl->entranceDate        = GDate::construct($this->entryDate)->getDate(GDate::MASK_DATE_DB);
            $this->businessMaterialControl->lastChangeDate      = GDate::now()->getDate(GDate::MASK_DATE_DB);
            $this->businessMaterialControl->lastChangeOperator  = GOperator::getOperatorId();
            $this->businessMaterialControl->materialGenderId    = null;
            $this->businessMaterialControl->materialTypeId      = null;
            $this->businessMaterialControl->category            = $this->spreadsheetCategory;
            $this->businessMaterialControl->level               = $this->level;

            if(!$this->businessMaterialControl->insertMaterialControl())
            {
                $this->addError(_M("Não é possível inserir o material!", $this->module));
                return false;
            }

            $this->businessMaterial->clean();
            $this->businessMaterial->controlNumber  = $this->controlNumber;
            list($this->businessMaterial->fieldid, $this->businessMaterial->subfieldid) = explode(".", MARC_CONTROL_NUMBER_TAG);
            $this->businessMaterial->indicator1     = '';
            $this->businessMaterial->indicator2     = '';
            $this->businessMaterial->content        = $this->controlNumber;
            $this->businessMaterial->line           = 0;
            $this->businessMaterial->insertMaterial();
            
            //grava histórico do campo 001.a (número de controle)
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]                  = new stdClass();
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->controlNumber   = $this->controlNumber;
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->revisionNumber  = $this->historyRevision;
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->operator        = GOperator::getOperatorId();
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->data            = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->chancesType     = BusinessGnuteca3BusMaterialHistory::CHANGETYPE_INSERT; //default como update. Apos é tratado pelas funções
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->fieldId         = $this->businessMaterial->fieldid;
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->subFieldId      = $this->businessMaterial->subfieldid;
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->previousLine    = $this->businessMaterial->line;
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->currentLine     = $this->businessMaterial->line;
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->previousContent = null;
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->currentContent  = $this->businessMaterial->content;
            $this->objetMaterialHistory[MARC_CONTROL_NUMBER_TAG][0]->execute         = true;
        }
        else
        {
             $this->historyRevision = $this->businessMaterialHistory->getNextRevision($this->controlNumber);
        }

        if($this->function == "update" && !is_null($this->controlNumber))
        {
            $this->businessMaterialControl->setLastChangeDate(GDate::now()->getDate(GDate::MASK_DATE_DB), $this->controlNumber);
            $this->businessMaterialControl->setLastChangeOperator(GOperator::getOperatorId(), $this->controlNumber);
        }

        // INSERE A TAG 000.a = LEADER STRING
        $this->insertOrUpdateLeaderMarcString();

        //Estas tags são gravadas independentemente de terem valores definidos pelo usuário
        $tagsException = array( MARC_WORK_NUMBER_TAG);
        // PERCORRE TODA PLANILHA CHECANDO OS CAMPOS ALTERADOS
        foreach($this->spreadSheet as $tab => $spEti)
        {
            foreach($spEti as $etiqueta => $objEti)
            {
                if(!$objEti->subFields)
                {
                    continue;
                }

                switch ($etiqueta)
                {
                    // campos dos exemplares
                    case MARC_EXEMPLARY_FIELD:
                        $this->updateExemplaries($objEti, $preCatalogue);
                    break;


                    case MARC_KARDEX_FIELD  :
                        $this->updateKardex($objEti, $preCatalogue);
                    break;
                }

                // TRABALHA CONTEUDO GTCMATERIAL
                foreach($objEti->subFields as $subfield => $object)
                {
                    if ( strlen($subfield) == 1 ) //Identifica se é um subcampo. Pode ocorrer casos como o do 008 e 001 que o conteúdo é quebrado em caracteres
                    {
                        // HISTORICO DO MATERIAL
                        $tag = $etiqueta . "." . $subfield;

                       //faz a fusão entre o formValue e baseValue
                        $keysForm = array_keys($object->formValue);
                        $keysBase = array_keys($object->baseValue? $object->baseValue : array() );
                        $diff = array_diff($keysBase ? $keysBase : array(), $keysForm);

                        if ( is_array($diff) )
                        {
                            foreach ($diff as $key=>$value)
                            {
                                $object->formValue[$value] = '';
                            }
                        }

                        foreach ( $object->formValue as $linha => $valor )
                        {
                            if ( strlen($object->baseValue[$linha]) > 0 || strlen($object->formValue[$linha]) || in_array($etiqueta, $tagsException) ) //ignora as tags que não são gravadas na gtcmaterial
                            {
                                $this->objetMaterialHistory[$tag][$linha] = new stdClass();
                                $this->objetMaterialHistory[$tag][$linha]->controlNumber        = $this->controlNumber;
                                $this->objetMaterialHistory[$tag][$linha]->revisionNumber       = $this->historyRevision;
                                $this->objetMaterialHistory[$tag][$linha]->operator             = GOperator::getOperatorId();
                                $this->objetMaterialHistory[$tag][$linha]->data                 = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
                                $this->objetMaterialHistory[$tag][$linha]->chancesType          = BusinessGnuteca3BusMaterialHistory::CHANGETYPE_UPDATE; //default como update. Apos é tratado pelas funções
                                $this->objetMaterialHistory[$tag][$linha]->fieldId              = $etiqueta;
                                $this->objetMaterialHistory[$tag][$linha]->subFieldId           = $subfield;
                                $this->objetMaterialHistory[$tag][$linha]->previousLine         = $linha;
                                $this->objetMaterialHistory[$tag][$linha]->currentLine          = $linha;

                                //adiciona o conteúdo
                                $this->objetMaterialHistory[$tag][$linha]->previousContent      = $object->baseValue[$linha];
                                $this->objetMaterialHistory[$tag][$linha]->currentContent       = $object->formValue[$linha];

                                //adiciona os indicadores
                                $this->objetMaterialHistory[$tag][$linha]->previousIndicator1   = $objEti->indicadores[$etiqueta . '-I1']->baseValue[0];
                                $this->objetMaterialHistory[$tag][$linha]->currentIndicator1    = $objEti->indicadores[$etiqueta . '-I1']->formValue[0];

                                $this->objetMaterialHistory[$tag][$linha]->previousIndicator2   = $objEti->indicadores[$etiqueta . '-I2']->baseValue[0];
                                $this->objetMaterialHistory[$tag][$linha]->currentIndicator2    = $objEti->indicadores[$etiqueta . '-I2']->formValue[0];

                                //adiciona os prefixos
                                $this->objetMaterialHistory[$tag][$linha]->previousprefixid = $object->loadPrefix[$linha]; //prefixo anterior 
                                $this->objetMaterialHistory[$tag][$linha]->currentprefixid = $object->formPrefix[$linha]; //atual

                                //adiciona os sufixos
                                $this->objetMaterialHistory[$tag][$linha]->previoussuffixid = $object->loadSuffix[$linha]; //sufixo anterior
                                $this->objetMaterialHistory[$tag][$linha]->currentsuffixid = $object->formSuffix[$linha]; //atual

                                //adiciona os separadores
                                $this->objetMaterialHistory[$tag][$linha]->previousseparatorid = $object->loadSeparator[$linha]; //separador anterior
                                $this->objetMaterialHistory[$tag][$linha]->currentseparatorid = $object->formSeparator[$linha]; //atual

                                //Por padrão não executa. As operações a seguir decidirão se executa ou não
                                $this->objetMaterialHistory[$tag][$linha]->execute = false;
                            }
                        }

                        //Passar da pré-catalogação não precisa gravar novamente MARC_WORK_NUMBER_TAG, pois já foi gravado acima
                        if ( ($preCatalogue) )
                        {
                            $object->baseValue      = array();
                            $object->baseValue[0]   = "";
                            $object->loadPrefix     = array();
                            $object->loadSuffix     = array();
                            $object->loadSeparator  = array();
                        }

                        // VERIFICA SE EXISTE DIFEREÇA ENTRE OS ARRAYS PARA CAMPOS DO MATERIAL
                        $isMaterialPhysicalTypeTag = ("{$etiqueta}.{$subfield}" == MARC_MATERIAL_PHYSICAL_TYPE_TAG) ? TRUE : FALSE; //Verificacao referente ao ticket #6115 (nao estava salvando)
                        
                        if( !GUtil::compareArray($object->formValue, $object->baseValue) || ($isMaterialPhysicalTypeTag) || in_array($etiqueta, $tagsException))
                        {
                            $this->insertAndUpdateFieldContent($object, $etiqueta, $subfield, $objEti->indicadores);
                            $this->deleteFieldContent($object, $etiqueta, $subfield, $objEti->indicadores);
                        }

                        //ATUALIZA PREFIXOS, é importante força a inserção na duplicação
                        if( !GUtil::compareArray($object->formPrefix, $object->loadPrefix, true) || $this->duplicateMaterial )
                        {
                            if ( count( $object->loadPrefix) > count( $object->formPrefix ) || $this->duplicateMaterial ) 
                            {
                                foreach($object->loadPrefix as $line => $prefixId)
                                {
                                    if ($object->loadPrefix[$line] != $object->formPrefix[$line] || $this->duplicateMaterial )
                                    {
                                        $this->businessMaterial->updateMaterialPrefix($this->controlNumber, $object->tag, $object->formPrefix[$line], $line);
                                        $this->objetMaterialHistory[$tag][$line]->execute = true;
                                    }
                                }
                            }
                            
                            foreach($object->formPrefix as $line => $prefixId)
                            {
                                if ($object->loadPrefix[$line] != $object->formPrefix[$line] || $this->duplicateMaterial)
                                {
                                    $this->businessMaterial->updateMaterialPrefix($this->controlNumber, $object->tag, $prefixId, $line);
                                    $this->objetMaterialHistory[$tag][$line]->execute = true;
                                }
                            }
                            
                            $object->loadPrefix = $object->formPrefix;
                        }
                        
                        //ATUALIZA SUFFIXOS
                        if( !GUtil::compareArray($object->formSuffix, $object->loadSuffix, true) || $this->duplicateMaterial)
                        {
                            if(count($object->loadSuffix) > count($object->formSuffix) || $this->duplicateMaterial)
                            {
                                foreach($object->loadSuffix as $line => $suffixId)
                                {
                                    if ($object->loadSuffix[$line] != $object->formSuffix[$line] || $this->duplicateMaterial)
                                    {
                                        $this->businessMaterial->updateMaterialSuffix($this->controlNumber, $object->tag, $object->formSuffix[$line], $line);
                                        $this->objetMaterialHistory[$tag][$line]->execute = true;
                                    }
                                }
                            }
                            
                            foreach($object->formSuffix as $line => $suffixId)
                            {
                                if ($object->loadSuffix[$line] != $object->formSuffix[$line] || $this->duplicateMaterial)
                                {
                                    $this->businessMaterial->updateMaterialSuffix($this->controlNumber, $object->tag, $suffixId, $line);
                                    $this->objetMaterialHistory[$tag][$line]->execute = true;
                                }
                            }
                            
                            $object->loadSuffix = $object->formSuffix;
                        }
                        //ATUALIZA SEPARATOR
                        if( !GUtil::compareArray($object->formSeparator, $object->loadSeparator, true) || $this->duplicateMaterial)
                        {
                            if(count($object->loadSeparator) > count($object->formSeparator) || $this->duplicateMaterial )
                            {
                                foreach($object->loadSeparator as $line => $suffixId)
                                {
                                    if ($object->loadSeparator[$line] != $object->formSeparator[$line] || $this->duplicateMaterial )
                                    {
                                        $this->businessMaterial->updateMaterialSeparator($this->controlNumber, $object->tag, $object->formSeparator[$line], $line);
                                        $this->objetMaterialHistory[$tag][$line]->execute = true;
                                    }
                                }
                            }
                            
                            foreach($object->formSeparator as $line => $separatorId)
                            {
                                if ($object->loadSeparator[$line] != $object->formSeparator[$line])
                                {
                                    $this->businessMaterial->updateMaterialSeparator($this->controlNumber, $object->tag, $separatorId, $line);
                                    $this->objetMaterialHistory[$tag][$line]->execute = true;
                                }
                            }
                            
                            $object->loadSeparator = $object->formSeparator;
                        }
                    }
                }

                // ATUALIZA OS INDICADORES
                if(isset($objEti->indicadores) && $this->controlNumber)
                {
                    if ( $this->updateIndicators($objEti, $etiqueta) )
                    {
                        $updateHistoryIndicators[$etiqueta] = true;
                    }
                }
            }
        }

        //Executa os históricos ativos
        if (count($this->objetMaterialHistory) > 0)
        {
            foreach ($this->objetMaterialHistory as $tag=>$tagMaterialHistory)
            {
                $aTag = explode('.', $tag);

                foreach ($tagMaterialHistory as $line=>$objectMaterialHistory)
                {
                    if ( ($objectMaterialHistory->execute || $updateHistoryIndicators[$aTag[0]]) )
                    {
                        if ($objectMaterialHistory->controlNumber)
                        {
                            $this->businessMaterialHistory->clean();

                            //Retira o valor base quando for da pré-catalogação para a catalogação
                            if ($preCatalogue)
                            {
                                $objectMaterialHistory->previousContent      = '';

                                //adiciona os indicadores
                                $objectMaterialHistory->previousIndicator1   = '';
                                $objectMaterialHistory->previousIndicator2   = '';

                                //adiciona os prefixos
                                $objectMaterialHistory->previousprefixid = '';

                                //adiciona os sufixos
                                $objectMaterialHistory->previoussuffixid = '';

                                //adiciona os separadores
                                $objectMaterialHistory->previousseparatorid = '';
                            }

                            $this->businessMaterialHistory->setData($objectMaterialHistory);

                            $this->businessMaterialHistory->insertMaterialHistory();
                        }
                    }
                }
            }
        }

        //atualiza campos unidos "245a+245.b"
        $this->updateSearchContent();
        $this->cameFromPreCat = false;
        $this->synchronizeFatherAndSon();
        $this->saveContent();
        
        //salva no servidor Z3950
        if ( $this->controlNumber && defined( 'Z3950_SERVER_URL' ) && Z3950_SERVER_URL )
        {
            $this->MIOLO->getClass('gnuteca3', 'GZ3950');
       
            $z3950 = new GZ3950( Z3950_SERVER_URL, Z3950_SERVER_USER, Z3950_SERVER_PASSWORD );
       
            try
            {
                $ok = $z3950->insertOrUpdate( $this->controlNumber );
            }
            catch ( Exception $e)
            {
                
            }
        }
        
        return $this->controlNumber;
    }



    /**
     * Este método atualiza os campos que tem uma uniao (baseado na gtcSearcheableField)
     * O Objetivo é atualizar apenas o campo searchField
     * FIXME essa função precisa de uma correção,
     * pois ela é chamada somente no save e isso não é sincronizado com o resto do conteúdo
     */
    private function updateSearchContent()
    {
        $camposUnidos = $this->businessSearchableField->getUnionFields();

        if(!$camposUnidos)
        {
            return false;
        }

        $campos = array();
        
        foreach ($camposUnidos as $content)
        {
            $f = explode(",", $content->field);
            
            foreach ($f as $value)
            {
                if(!ereg("\+", $value))
                {
                    continue;
                }

                $campos[] = $value;
            }
        }

        if(!count($campos))
        {
            return false;
        }

        $campos = array_unique($campos);
        
        foreach ($campos as $value)
        {
            $tags = explode("+", $value);

            foreach ($tags as $tag)
            {
                $object = $this->getValuesFromSpreadsheetField($tag);

                if ( $object ) 
                {
                    foreach ($object as $line => $conteudo)
                    {
                        $str[$value][$tag][$line]       =   $conteudo;
                        $str[$value]['classifaction']  .=   " !ereg('$tag', MARC_CLASSIFICATION_TAG) && ";
                    }
                }
            }
        }

        foreach($str as $value => $tags)
        {
            eval("\$classification = (". substr($tags['classifaction'], 0, -3) . ");");
            
            if(!$classification)
            {
                unset($str[$value]);
                continue;
            }
            
            unset($str[$value]['classifaction']);
            unset($tags['classifaction']);

            $conteudo = array();
            
            foreach ($tags as $tag => $content)
            {
                foreach ($content as $line => $cont)
                {
                    $conteudo[$line] .= " $cont";
                }
            }

            foreach ($tags as $tag => $content)
            {
                foreach ($content as $line => $cont)
                {
                    $this->businessMaterial->clean();
                    $this->businessMaterial->controlNumber = $this->controlNumber;
                    list($this->businessMaterial->fieldid, $this->businessMaterial->subfieldid) = explode(".", $tag);
                    $this->businessMaterial->line          = $line;
                    $this->businessMaterial->searchContent = trim($conteudo[$line]);
                    $this->businessMaterial->updateMaterialSearchContent();
                }
            }
        }
    }


    /**
     * Este método retorna o formValue de uma determinada tag, ou seja, retorna o conteudo preenchido no form;
     *
     * @param char(5) $tag
     * @return array
     */
    private function getValuesFromSpreadsheetField($tag)
    {
        foreach($this->spreadSheet as $tab => $spEti)
        {
            foreach($spEti as $etiqueta => $objEti)
            {
                if ( is_array($objEti->subFields ))
                {
                    foreach($objEti->subFields as $subfield => $object)
                    {
                        if($tag == "{$etiqueta}.{$subfield}")
                        {
                            if(strlen($object->formValue[0]))
                            {
                                return $object->formValue;
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Enter description here...
     *
     * @return unknown
     */
    function savePreCatalogue()
    {
        $this->getContent();

        $this->normalSave       = false;
        $this->preCatSave       = true;
        $this->preCatalogue     = true;
        $this->cameFromPreCat   = true;

        $this->saveContent();

        if(!$this->controlNumber)
        {
            $this->controlNumber = $this->businessPreCatalogue->getNextControlNumber();

            $this->businessPreCatalogue->clean();
            $this->businessPreCatalogue->controlNumber  = $this->controlNumber;
            list($this->businessPreCatalogue->fieldid, $this->businessPreCatalogue->subfieldid) = explode(".", MARC_CONTROL_NUMBER_TAG);
            $this->businessPreCatalogue->indicator1     = '';
            $this->businessPreCatalogue->indicator2     = '';
            $this->businessPreCatalogue->content        = $this->controlNumber;
            $this->businessPreCatalogue->line           = 0;
            $this->businessPreCatalogue->searchContent  = $this->controlNumber;

            $this->businessPreCatalogue->insertMaterial();
        }

        // INSERE A TAG 000.a = LEADER STRING
        $this->insertOrUpdateLeaderMarcString();

        // PERCORRE TODA PLANILHA CHECANDO OS CAMPOS ALTERADOS
        foreach($this->spreadSheet as $tab => $spEti)
        {
            foreach($spEti as $etiqueta => $objEti)
            {
                if(!$objEti->subFields)
                {
                    continue;
                }

                if($etiqueta == MARC_FIXED_DATA_FIELD)
                {
                    list($e,$s) = explode(".",MARC_FIXED_DATA_TAG);
                    $this->insertAndUpdateFieldContent($objEti->subFields[$s], $etiqueta, $s, $objEti->indicadores);
                }
                else
                {
                    foreach($objEti->subFields as $subfield => $object)
                    {
                        // VERIFICA SE EXISTE DIFEREÇA ENTRE OS ARRAYS PARA CAMPOS DO MATERIAL
                        if( !GUtil::compareArray($object->formValue, $object->baseValue) )
                        {
                            $this->insertAndUpdateFieldContent($object, $etiqueta, $subfield, $objEti->indicadores);
                            $this->deleteFieldContent($object, $etiqueta, $subfield, $objEti->indicadores);



                        }
                        //ATUALIZA PREFIXOS
                        if( !GUtil::compareArray($object->formPrefix, $object->loadPrefix, true))
                        {
                            if(count($object->loadPrefix) > count($object->formPrefix))
                            {
                                foreach($object->loadPrefix as $line => $prefixId)
                                {
                                    $this->businessPreCatalogue->updateMaterialPrefix($this->controlNumber, $object->tag, $object->formPrefix[$line], $line);
                                }
                            }
                            foreach($object->formPrefix as $line => $prefixId)
                            {
                                $this->businessPreCatalogue->updateMaterialPrefix($this->controlNumber, $object->tag, $prefixId, $line);
                            }
                            $object->loadPrefix = $object->formPrefix;
                        }
                        //ATUALIZA SUFFIXOS
                        if( !GUtil::compareArray($object->formSuffix, $object->loadSuffix, true))
                        {
                            if(count($object->loadSuffix) > count($object->formSuffix))
                            {
                                foreach($object->loadSuffix as $line => $suffixId)
                                {
                                    $this->businessPreCatalogue->updateMaterialSuffix($this->controlNumber, $object->tag, $object->formSuffix[$line], $line);
                                }
                            }
                            foreach($object->formSuffix as $line => $suffixId)
                            {
                                $this->businessPreCatalogue->updateMaterialSuffix($this->controlNumber, $object->tag, $suffixId, $line);
                            }
                            $object->loadSuffix = $object->formSuffix;
                        }
                        //ATUALIZA SEPARATOR
                        if( !GUtil::compareArray($object->formSeparator, $object->loadSeparator, true))
                        {
                            if(count($object->loadSeparator) > count($object->formSeparator))
                            {
                                foreach($object->loadSeparator as $line => $suffixId)
                                {
                                    $this->businessPreCatalogue->updateMaterialSeparator($this->controlNumber, $object->tag, $object->formSeparator[$line], $line);
                                }
                            }
                            foreach($object->formSeparator as $line => $separatorId)
                            {
                                $this->businessPreCatalogue->updateMaterialSeparator($this->controlNumber, $object->tag, $separatorId, $line);
                            }
                            $object->loadSeparator = $object->formSeparator;
                        }
                    }
                }

                // ATUALIZA OS INDICADORES
                if(isset($objEti->indicadores) && $this->controlNumber)
                {
                    $this->updateIndicators($objEti, $etiqueta);
                }
            }
        }

        $this->saveContent();
        return $this->controlNumber;
    }


    /**
     * Faz o insert dos dados na gtcMaterial, ou gtcPreCatalogue
     */
    function insertAndUpdateFieldContent(&$object, $etiqueta, $subfield, $indicadores)
    {
        //escolhe bussiness de inserção
        $objectMaterialManipule = $this->businessMaterial;
        $tag = $etiqueta . "." . $subfield;

        //pode ser a pré-catalogação, conforme o caso
        if ( $this->preCatalogue )
        {
            $objectMaterialManipule = $this->businessPreCatalogue;
        }

        foreach ($object->formValue as $linha => $valor)
        {
            // SETA ALGUNS DETALHES DA GTCMATERIALCONTROL
            if(!$this->preCatalogue)
            {
                switch($tag)
                {
                    // ATUALIZA O TIPO DO MATERIAL NA GTCMATERIALCONTROL
                    case MARC_MATERIAL_GENDER_TAG:
                        $op = $this->businessMaterialControl->updateMaterialGender($this->controlNumber, $valor);
                    break;

                    case MARC_MATERIAL_TYPE_TAG:
                        $op = $this->businessMaterialControl->updateMaterialType($this->controlNumber, $valor);
                    break;

                    case MARC_MATERIAL_PHYSICAL_TYPE_TAG:
                        $op = $this->businessMaterialControl->updateMaterialPhysicalType($this->controlNumber, $valor);
                    break;

                    case MARC_ANALITIC_ENTRACE_TAG:
                        if($linha == 0)
                        {
                            $op = $this->businessMaterialControl->setControlNumberFather($this->controlNumber, $valor);
                        }
                    break;

                    // GERA O NUMERO DA OBRA CASO ESTE ESTEJA EM BRANCO
                    case MARC_WORK_NUMBER_TAG :
                        if(!strlen($valor))
                        {
                           $valor = $this->businessMaterial->getNextWorkNumber();
                           $this->objetMaterialHistory[$tag][$linha]->currentContent = $object->formValue[$linha] = $valor;
                        }
                        $this->workNumber->value         = $valor;
                        $this->workNumber->fieldName     = $object->fieldName;
                    break;

                    case MARC_FIXED_DATA_FIELD :
                    break;
                }
            }

            $insert =   (    count($object->formValue)  &&   strlen($valor)  )   &&
                        (   !count($object->baseValue)  ||  !strlen($object->baseValue[$linha])  );

            if($this->cameFromPreCat && $this->normalSave)
            {
                $insert = true;
            }

            $update =   (    count($object->formValue)  &&   strlen($valor)  )   &&
                        (    count($object->baseValue)  &&   $object->baseValue[$linha] !== $valor  )   &&
                             strlen($this->controlNumber);

            if(!$insert && !$update)
            {
                continue;
            }

            $objectMaterialManipule->clean();

            $objectMaterialManipule->controlNumber = $this->controlNumber;
            $objectMaterialManipule->subfieldid = $subfield;
            $objectMaterialManipule->fieldid = $etiqueta;
            $objectMaterialManipule->indicator1 = '';
            $objectMaterialManipule->indicator2 = '';

            $objectMaterialManipule->content = $valor;
            $objectMaterialManipule->line = $linha;
            $objectMaterialManipule->complement = $this->cutterComplement;

            $op = false;

            if($insert)
            {
                $op = $objectMaterialManipule->insertMaterial();

                if ( $op && !$this->preCatalogue )
                {
                    $this->objetMaterialHistory[$tag][$linha]->chancesType = BusinessGnuteca3BusMaterialHistory::CHANGETYPE_INSERT;
                }
            }
            elseif($update)
            {
                $op = $objectMaterialManipule->updateMaterialContent();

                if ( $op && !$this->preCatalogue )
                {
                    $this->objetMaterialHistory[$tag][$linha]->chancesType = BusinessGnuteca3BusMaterialHistory::CHANGETYPE_UPDATE;
                }
            }

            $object->baseValue[$linha] = $object->formValue[$linha];
            //determina para executar histórico e atualiza base value
            if ( $op && !$this->preCatalogue )
            {
                $this->objetMaterialHistory[$tag][$linha]->execute = true;
            }
        }
    }

    /**
     * Verifica e deleta um determinado conteudo da gtcmaterial
     *
     * @param object $object
     * @param string $etiqueta
     * @param char $subfield
     */
    function deleteFieldContent($object, $etiqueta, $subfield)
    {
        $objectMaterialManipule = $this->businessMaterial;
        $tag = $etiqueta . "." . $subfield;

        //PRE-CATALOGAÇÃO
        if($this->preCatalogue)
        {
            $objectMaterialManipule = $this->businessPreCatalogue;
        }

        //CATALOGAÇÃO NORMAL
        foreach($object->baseValue as $linha => $valor)
        {
            $delete = (   !count($object->formValue)  ||  !strlen($object->formValue[$linha])  )
                            && strlen($this->controlNumber);

            if(!$delete)
            {
                continue;
            }

            $objectMaterialManipule->clean();

            $objectMaterialManipule->controlNumber  = $this->controlNumber;
            $objectMaterialManipule->subfieldid     = $subfield;
            $objectMaterialManipule->fieldid        = $etiqueta;
            $objectMaterialManipule->indicator1     = '';
            $objectMaterialManipule->indicator2     = '';
            $objectMaterialManipule->content        = $valor;
            $objectMaterialManipule->line           = $linha;

            //GRAVA HISTORICO
            if(!$this->preCatalogue)
            {
                $this->objetMaterialHistory[$tag][$linha]->chancesType = BusinessGnuteca3BusMaterialHistory::CHANGETYPE_DELETE;
            }

            $op = false;

            if($delete)
            {
                $op = $objectMaterialManipule->deleteMaterial();
            }

            if($op)
            {
                unset($object->baseValue[$linha]);

                if(!$this->preCatalogue)
                {
                    $this->objetMaterialHistory[$tag][$linha]->execute = true;
                }
            }
        }
    }



    /**
     *
     */
    function updateIndicators($objEti, $etiqueta)
    {
        $objectMaterialManipule = $this->businessMaterial;

        //PRE-CATALOGAÇÃO
        if($this->preCatalogue)
        {
            $objectMaterialManipule = $this->businessPreCatalogue;
        }

        $objectMaterialManipule->clean();
        $objectMaterialManipule->controlNumber  = $this->controlNumber;
        $objectMaterialManipule->fieldid        = $etiqueta;

        $up = false;

        foreach($objEti->indicadores as $i => $objInd)
        {
            if( !GUtil::compareArray($objInd->formValue, $objInd->baseValue))
            {
                $up = true;
                $objInd->baseValue = $objInd->formValue;
            }
            if(ereg("-I1", $i))
            {
                $objectMaterialManipule->indicator1     = $objInd->formValue[0];
            }
            elseif(ereg("-I2", $i))
            {
                $objectMaterialManipule->indicator2     = $objInd->formValue[0];
            }
        }

        if($up)
        {
            return $objectMaterialManipule->updateMaterialIndicator();
        }
    }



    /**
     * Enter description here...
     *
     * @param array  $formExemplares
     * @param array  $baseExemplares
     * @param object reference $object
     */
    function updateExemplaries(&$objEti, $preCatalogueToMaterial = false)
    {
        $formExemplares = array();
        $baseExemplares = array();

        foreach($objEti->subFields as $subfield => $object)
        {
            foreach ($object->formValue as $linha1 => $valor1)
            {
                if(strlen($valor1))
                {
                    $formExemplares[$linha1][$subfield] = $valor1;
                }
            }

            if(!$preCatalogueToMaterial)
            {
                if ( is_array($object->baseValue ) )
                {
                    foreach ($object->baseValue as $linha2 => $valor2)
                    {
                        $baseExemplares[$linha2][$subfield] = $valor2;
                    }
                }
            }

        }

        if(GUtil::compareArray($formExemplares, $baseExemplares))
        {
            return;
        }

        //DELETE Exemplaries
        foreach($baseExemplares as $linha => $subfields)
        {
            $delete = (!isset($formExemplares[$linha]) || $subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD] !== $formExemplares[$linha][MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD]);

            if($delete)
            {
                $op = $this->businessExemplaryControl->deleteExemplaryControl($subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD], $this->controlNumber);

                if($op)
                {
                    if($this->getFutureStatusItem($subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD]))
                    {
                        $this->businessExemplaryFutureStatusDefined->deleteExemplaryFutureStatusDefined($this->getFutureStatusItem($subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD]));
                        $this->cleanFutureStatusItem($subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD]);
                    }
                    unset($baseExemplares[$linha]);
                }
            }
        }


        //UPDATE OR INSERT Exemplaries
        foreach($formExemplares as $linha => $subfields)
        {
            $insert = (!isset($baseExemplares[$linha]));
            //É para fazer update
            $update = (  isset($baseExemplares[$linha]) && //SE o exemplar estiver na base 
                         ($subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD] !== $baseExemplares[$linha][MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD]) || //E SE O código do exemplar for diferente do existente na base 
                         !GUtil::compareArray($baseExemplares[$linha], $formExemplares[$linha]) // OU SE os exempĺares da base forem diferentes dos definidos no form
                      );
            
            if( !$insert && !$update || !strlen($subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD]))
            {
                continue;
            }

            $this->businessExemplaryControl->clean();

            $this->businessExemplaryControl->controlNumber          = $this->controlNumber;
            $this->businessExemplaryControl->itemNumber             = $subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD];
            $this->businessExemplaryControl->libraryUnitId          = $subfields[MARC_EXEMPLARY_LIBRARY_UNIT_ID_SUBFIELD];
            $this->businessExemplaryControl->acquisitionType        = $subfields[MARC_EXEMPLARY_ACQUISITION_TYPE_SUBFIELD];
            $this->businessExemplaryControl->exemplaryStatusId      = $subfields[MARC_EXEMPLARY_EXEMPLARY_STATUS_SUBFIELD];
            $this->businessExemplaryControl->materialGenderId       = $subfields[MARC_EXEMPLARY_MATERIAL_GENDER_SUBFIELD];
            $this->businessExemplaryControl->observation            = $subfields[MARC_EXEMPLARY_OBSERVATION_SUBFIELD];
            $this->businessExemplaryControl->entranceDate           = GDate::construct( $subfields[MARC_EXEMPLARY_ENTRACE_DATE_SUBFIELD] )->getDate( GDate::MASK_DATE_USER );
            $this->businessExemplaryControl->lowDate                = GDate::construct( $subfields[MARC_EXEMPLARY_LOW_DATE_SUBFIELD] )->getDate( GDate::MASK_DATE_USER );
            $this->businessExemplaryControl->line                   = $linha;
            $this->businessExemplaryControl->materialPhysicalTypeId = $subfields[MARC_EXEMPLARY_MATERIAL_PHYSICAL_TYPE_SUBFIELD];
            $this->businessExemplaryControl->materialTypeId         = $subfields[MARC_EXEMPLARY_MATERIAL_TYPE_SUBFIELD];

            $this->businessExemplaryControl->originalLibraryUnitId  = $subfields[MARC_EXEMPLARY_LIBRARY_UNIT_ID_SUBFIELD];

            $this->businessExemplaryFutureStatusDefined->clean();
            
            if ( $subfields[MARC_EXEMPLARY_EXEMPLARY_STATUS_FUTURE_SUBFIELD] )
            {
	            $this->businessExemplaryFutureStatusDefined->exemplaryStatusId  = $subfields[MARC_EXEMPLARY_EXEMPLARY_STATUS_FUTURE_SUBFIELD];
	            $this->businessExemplaryFutureStatusDefined->itemNumber = $subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD];
	            $this->businessExemplaryFutureStatusDefined->applied = DB_FALSE;
	            $this->businessExemplaryFutureStatusDefined->date = GDate::now()->getDate( GDate::MASK_TIMESTAMP_DB ); //FIXME avaliar passagem em português
	            $this->businessExemplaryFutureStatusDefined->operator = GOperator::getOperatorId();
            }
            
            $op = false;

            if ( $insert )
            {
            	$this->businessExemplaryControl->futureStatusId = $subfields[MARC_EXEMPLARY_EXEMPLARY_STATUS_FUTURE_SUBFIELD];
                $op = $this->businessExemplaryControl->insertExemplaryControl();

                if ( $this->businessExemplaryFutureStatusDefined->exemplaryStatusId )
                {
                    $this->businessExemplaryFutureStatusDefined->insertExemplaryFutureStatusDefined();
                }
                
                $this->businessExemplaryFutureStatusDefined->clean();
                $this->businessExemplaryFutureStatusDefined->exemplaryFutureStatusIdS   = $subfields[MARC_EXEMPLARY_EXEMPLARY_STATUS_FUTURE_SUBFIELD];
                $this->businessExemplaryFutureStatusDefined->itemNumberS                = $subfields[MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD];
                $this->businessExemplaryFutureStatusDefined->dateS                      = $this->businessExemplaryFutureStatusDefined->date;
                $obj = $this->businessExemplaryFutureStatusDefined->searchExemplaryFutureStatusDefined(true);

                $this->futureStatusItem($this->businessExemplaryControl->itemNumber, $obj[0]->exemplaryFutureStatusDefinedId);
            }
            elseif($update)
            {
                $op = $this->businessExemplaryControl->updateExemplaryControl( $baseExemplares[$linha][MARC_EXEMPLARY_ITEM_NUMBER_SUBFIELD] );
                $this->businessExemplaryFutureStatusDefined->clean();
            }

        }
    }


    /**
     * Enter description here...
     *
     */
    function updateKardex(&$objEti, $preCatalogueToMaterial = false)
    {
        $formExemplares = array();
        $baseExemplares = array();

        foreach($objEti->subFields as $subfield => $object)
        {
            foreach ($object->formValue as $linha => $valor)
            {
                if(strlen($valor))
                {
                    $formExemplares[$linha][$subfield] = $valor;
                }
            }
            if(!$preCatalogueToMaterial)
            {
                if ( is_array( $object->baseValue ))
                {
                    foreach ($object->baseValue as $linha1 => $valor1)
                    {
                        $baseExemplares[$linha1][$subfield] = $valor1;
                    }
                }
            }
        }

        if( GUtil::compareArray ($formExemplares, $baseExemplares))
        {
            return;
        }

        //DELETE kardex
        foreach($baseExemplares as $linha => $subfields)
        {
            $delete = (!isset($formExemplares[$linha]) || $subfields[MARC_KARDEX_SUBSCRIBER_ID_SUBFIELD] != $formExemplares[$linha][MARC_KARDEX_SUBSCRIBER_ID_SUBFIELD]);

            if($delete)
            {
                $op = $this->businessKardexControl->deleteKardexControl($this->controlNumber, $subfields[MARC_KARDEX_SUBSCRIBER_ID_SUBFIELD], $linha);

                if($op)
                {
                    unset($baseExemplares[$linha]);
                }
            }
        }

        //UPDATE OR INSERT kardex
        foreach($formExemplares as $linha => $subfields)
        {
            $insert = (!isset($baseExemplares[$linha]));
            $update = (  isset($baseExemplares[$linha]) &&
                         $subfields[MARC_KARDEX_SUBSCRIBER_ID_SUBFIELD] == $baseExemplares[$linha][MARC_KARDEX_SUBSCRIBER_ID_SUBFIELD] ||
                         !GUtil::compareArray($baseExemplares[$linha], $formExemplares[$linha])
                      );

            if(!$insert && !$update)
            {
                continue;
            }

            $this->businessKardexControl->clean();
            $this->businessKardexControl->controlNumber             = $this->controlNumber;
            $this->businessKardexControl->codigoDeAssinante         = $subfields[MARC_KARDEX_SUBSCRIBER_ID_SUBFIELD];
            $this->businessKardexControl->libraryUnitId             = $subfields[MARC_KARDEX_LIBRARY_UNIT_ID_SUBFIELD];
            $this->businessKardexControl->acquisitionType           = $subfields[MARC_KARDEX_ACQUISITION_TYPE_SUBFIELD];
            $this->businessKardexControl->vencimentoDaAssinatura    = GDate::construct($subfields[MARC_KARDEX_SIGNATURE_END_SUBFIELD])->getDate(GDate::MASK_DATE_DB);
            $this->businessKardexControl->dataDaAssinatura          = GDate::construct($subfields[MARC_KARDEX_SIGNATURE_DATE_SUBFIELD])->getDate(GDate::MASK_DATE_DB);
            $this->businessKardexControl->entranceDate              = GDate::construct($subfields[MARC_KARDEX_ENTRACE_DATE_SUBFIELD])->getDate(GDate::MASK_DATE_DB);
            $this->businessKardexControl->line                      = $linha;

            $op = false;

            if($insert)
            {
                $op = $this->businessKardexControl->insertKardexControl();
            }
            elseif($update)
            {
                $op = $this->businessKardexControl->updateKardexControl();
            }

            if($op)
            {
                $object->baseValue[$linha] = $object->formValue[$linha];
            }
        }
    }


    /**
     * Enter description here...
     *
     */
    function insertOrUpdateLeaderMarcString()
    {
        $objectMaterialManipule = $this->businessMaterial;

        //PRE-CATALOGAÇÃO
        if($this->preCatalogue)
        {
            $objectMaterialManipule = $this->businessPreCatalogue;
        }

        $objectMaterialManipule->clean();

        $objectMaterialManipule->controlNumber  = $this->controlNumber;
        list($objectMaterialManipule->fieldid, $objectMaterialManipule->subfieldid) = explode(".", MARC_LEADER_TAG);
        $objectMaterialManipule->indicator1     = '';
        $objectMaterialManipule->indicator2     = '';
        $objectMaterialManipule->content        = $this->leaderString;
        $objectMaterialManipule->searchContent  = $this->leaderString;
        $objectMaterialManipule->line           = 0;

        //objeto do histórico
        $this->objetMaterialHistory['000']['a'] = new stdClass();
        $this->objetMaterialHistory['000']['a']->controlNumber        = $this->controlNumber;
        $this->objetMaterialHistory['000']['a']->revisionNumber       = $this->historyRevision;
        $this->objetMaterialHistory['000']['a']->operator             = GOperator::getOperatorId();
        $this->objetMaterialHistory['000']['a']->data                 = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $this->objetMaterialHistory['000']['a']->fieldId              = $objectMaterialManipule->fieldid;
        $this->objetMaterialHistory['000']['a']->subFieldId           = $objectMaterialManipule->subfieldid;
        $this->objetMaterialHistory['000']['a']->previousLine         = 0;
        $this->objetMaterialHistory['000']['a']->currentLine          = 0;

        $content = $objectMaterialManipule->getContent($this->controlNumber, $objectMaterialManipule->fieldid, $objectMaterialManipule->subfieldid);

        $doOperation = false; //define se foi feita alguma operação

        if( !$content )
        {
            $objectMaterialManipule->insertMaterial();

            //adiciona o conteúdo
            $this->objetMaterialHistory['000']['a']->previousContent      = null;
            $this->objetMaterialHistory['000']['a']->currentContent       = $this->leaderString;
            $this->objetMaterialHistory['000']['a']->chancesType = BusinessGnuteca3BusMaterialHistory::CHANGETYPE_INSERT;
            $this->objetMaterialHistory['000']['a']->execute = true;
            
            $doOperation = true;
        }
        //só faz update caso o dado seja diferente do que esteja no banco
        else if ( $content != $this->leaderString )
        {
            $objectMaterialManipule->updateMaterialContent();
            
            //atualiza conteúdo
            $this->objetMaterialHistory['000']['a']->previousContent      = $content;
            $this->objetMaterialHistory['000']['a']->currentContent       = $this->leaderString;
            $this->objetMaterialHistory['000']['a']->chancesType = BusinessGnuteca3BusMaterialHistory::CHANGETYPE_UPDATE;
            $this->objetMaterialHistory['000']['a']->execute = true;
        
            $doOperation = true;
        }

        //se foi feito insert ou update (operacao), define a nova categoria e nível
        if ( !$this->preCatalogue && $doOperation)
        {
            $this->businessMaterialControl->clean();
            $this->businessMaterialControl->setCategory($this->spreadsheetCategory, $this->controlNumber);
            $this->businessMaterialControl->clean();
            $this->businessMaterialControl->setLevel($this->level, $this->controlNumber);
        }
    }



    /**
     * Seta o valor do form no Object spreadsheet
     */
    function setFormValues($formValues)
    {
        $this->getContent();

        $this->controlNumber    = $formValues->controlNumber;
        $this->entryDate        = $formValues->entryDate;
        $this->lastChange       = $formValues->lastChange;
        $this->leaderString     = $formValues->marcLeader;

        $tags       = array();
        $repeatF    = array();
        $suffix     = array();
        $prexif     = array();
        $separator  = array();
        $repeatF_prexif     = array();
        $repeatF_suffix     = array();
        $repeatF_separator  = array();

        // passa por todo o post filtrando dados necesários para inserção
        // a pratica utilizada para filtragem dos dados é considerando o nome (id) do campo, o que pode não ser uma prática muito segura ...
        foreach ( $formValues as $fieldName => $fieldValue )
        {
            //condição de exclusão de campos/dados
            if((
                    !ereg("spreeadsheetField_", $fieldName) &&
                    !ereg("gtcRepeatField_",    $fieldName) &&
                    !ereg("prefix_",            $fieldName) &&
                    !ereg("suffix_",            $fieldName) &&
                    !ereg("separator_",         $fieldName)
                ) ||
                (ereg("_sel", $fieldName) || ereg("_Filter", $fieldName))
              )
            {
                continue;
            }

            $x[] = $fieldName;

            //Verifica se é um indicador ou uma tag
            $isIndicador = ereg("_I[12]{1}", $fieldName);

            //vampos normais sem indicador
            if(!$isIndicador && ereg("^spreeadsheetField_", $fieldName))
            {
                if (ereg('_defaultValue', $fieldName))
                {
                    $fieldNameDefault = str_replace('_defaultValue', '', $fieldName);
                    
                    if (count($formValues->$fieldNameDefault) > 0)
                    {
                        continue;
                    }
                    else
                    {
                        $tag = str_replace(array("spreeadsheetField_", "_"), array("", "."), $fieldName);
                        list($etiqueta, $subcampo) = explode(".", $tag);
                        $tags[$etiqueta][$subcampo] = $fieldValue;
                    }
                }              
                else
                {
                    $tag = str_replace(array("spreeadsheetField_", "_"), array("", "."), $fieldName);
                    list($etiqueta, $subcampo) = explode(".", $tag);
                    $tags[$etiqueta][$subcampo] = $fieldValue;
                }

                if("{$etiqueta}.{$subcampo}" == MARC_CUTTER_TAG)
                {
                    $this->cutterComplement = $fieldValue;
                }
            }
            //prefixos
            elseif(!$isIndicador && ereg("^prefix_", $fieldName))
            {
                $tag = str_replace(array("prefix_spreeadsheetField_", "_"), array("", "."), $fieldName);
                list($etiqueta, $subcampo) = explode(".", $tag);
                //inclui numa variável de prefixos
                $prexif[$etiqueta][$subcampo] = $fieldValue;
            }
            //sufixos
            elseif(!$isIndicador && ereg("^suffix_",  $fieldName))
            {
                $tag = str_replace(array("suffix_spreeadsheetField_", "_"), array("", "."), $fieldName);
                list($etiqueta, $subcampo) = explode(".", $tag);
                $suffix[$etiqueta][$subcampo] = $fieldValue;
            }
            //separador
            elseif(!$isIndicador && ereg("^separator_", $fieldName))
            {
                $tag = str_replace(array("separator_spreeadsheetField_", "_"), array("", "."), $fieldName);
                list($etiqueta, $subcampo) = explode(".", $tag);
                //inclui numa variável de separador
                $separator[$etiqueta][$subcampo] = $fieldValue;
            }
            //caso for indicador e campo normal
            elseif($isIndicador && ereg("^spreeadsheetField_", $fieldName))
            {
                $tag = str_replace(array("spreeadsheetField_", "_"), array("", "-"), $fieldName);
                list($etiqueta, $subcampo) = explode("-", $tag);
                $tags[$etiqueta][$tag] = $fieldValue;
            }
            //campos repetitivo
            elseif (! $isIndicador && ereg( "^gtcRepeatField_", $fieldName ) && is_array( $fieldValue ) )
            {
                $repetitiveTag = str_replace("gtcRepeatField_",'',$fieldName);

                foreach($fieldValue as $linha => $subCampos)
                {
                    foreach ( $subCampos as $subNames => $value )
                    {
                        //caso venha dados extras do campo repetitivo, seleciona somente os dados da tag especifica
                        $isForThisTag = stripos($subNames, $repetitiveTag) > 0 ;

                        if ( $isForThisTag )
                        {
                            if ( ereg("^prefix_", $subNames ) )
                            {
                                $tag = str_replace(array("prefix_spreeadsheetField_", "_"), array("", "."), $subNames);
                                list($etiqueta, $subcampo) = explode(".", $tag);
                                $repeatF_prexif[$etiqueta][$subcampo][$linha] = $value;
                            }
                            elseif ( ereg("^suffix_", $subNames))
                            {
                                $tag = str_replace(array("suffix_spreeadsheetField_", "_"), array("", "."), $subNames);
                                list($etiqueta, $subcampo) = explode(".", $tag);
                                $repeatF_suffix[$etiqueta][$subcampo][$linha] = $value;
                            }
                            elseif ( ereg("^separator_", $subNames))
                            {
                                $tag = str_replace(array("separator_spreeadsheetField_", "_"), array("", "."), $subNames);
                                list($etiqueta, $subcampo) = explode(".", $tag);
                                $repeatF_separator[$etiqueta][$subcampo][$linha] = $value;
                            }
                            else
                            {
                                //podem vir dados dos filtros de dicionários e acabar por se misturar
                                $isFilter = stripos($subNames, '_Filter_') > 0 ;
                                
                                if ( !$isFilter )
                                {
                                    $tag = str_replace(array("spreeadsheetField_", "_"), array("", "-"), $subNames);
                                    list($etiqueta, $subcampo) = explode("-", $tag);
                                    $repeatF[$etiqueta][$subcampo][$linha] = $value;
                                }

                            }
                        }
                    }
                }
            }
        }

        foreach($formValues as $fieldName => $fieldValue)
        {
            if(!ereg("^especialField_", $fieldName))
            {
                continue;
            }
            
            if(ereg("^especialField_". MARC_PERIODIC_INFORMATIONS, $fieldName))
            {
                $tag = str_replace("especialField_". str_replace(".", "_", MARC_PERIODIC_INFORMATIONS) ."_spreeadsheetField_", "", $fieldName);
                $tag = str_replace("_", ".", $tag);

                $corretValue = "";
                
                if($this->getLevel() == "#")
                {
                    if ($fieldValue[0] && !$fieldValue[1])
                        {
                            $fieldValue[0] = 'Vol. ' . $fieldValue[0];
                        }
                        else if ($fieldValue[0] && $fieldValue[1])
                        {
                            $fieldValue[0] = 'Vol. ' . $fieldValue[0] . ', ';
                        }

                        if ($fieldValue[1])
                        {
                            $fieldValue[1] = 'no. ' . $fieldValue[1];
                        }

                        if (!$fieldValue[2])
                        {
                            $fieldValue[2] = ' - ';
                        }
                        else if (($fieldValue[0] || $fieldValue[1]) || (($fieldValue[2] && !$fieldValue[0] && !$fieldValue[1])))
                        {
                            $fieldValue[2] = ' ' . str_replace('-', '' , $fieldValue[2] ) . '-' ;
                        }

                        if ($fieldValue[3] && !$fieldValue[4] && !$fieldValue[5])
                        {
                            $fieldValue[3] = 'v. ' . $fieldValue[3];
                        }
                        else if ($fieldValue[3])
                        {
                            $fieldValue[3] = 'v. ' . $fieldValue[3] . ', ';
                        }

                        if ($fieldValue[4] && !$fieldValue[5])
                        {
                        $fieldValue[4] = 'no. ' . $fieldValue[4]; 
                        }
                        else if ($fieldValue[4])
                        {
                            $fieldValue[4] = 'no. ' . $fieldValue[4] . ' ';
                        }
                }
                else
                {                
                    if (($fieldValue[0] && !$fieldValue[1]) && ($fieldValue[0] && !$fieldValue[2]))
                    {
                        $fieldValue[0] = 'v. ' . $fieldValue[0];
                    }
                    else if (($fieldValue[0] && $fieldValue[1]) || ($fieldValue[0] && $fieldValue[2]))
                    {
                        $fieldValue[0] = 'v. ' . $fieldValue[0] . ', ';
                    }
                    
                    if ($fieldValue[1] && !$fieldValue[2])
                    {
                        $fieldValue[1] = 'n. ' . $fieldValue[1];
                    }
                    
                    if ($fieldValue[1] && $fieldValue[2])
                    {
                        $fieldValue[1] = 'n. ' . $fieldValue[1] . ', ';
                    }
                }
                    
                    foreach ($fieldValue as $i => $v)
                    {
                        if(!strlen($v))
                        {
                            continue;
                        }
                        
                        switch ($i)
                        {
                            case 0 : $corretValue.= "$v"; break;
                            case 1 : $corretValue.= "$v"; break;
                            case 2 : $corretValue.= "$v"; break;
                            case 3 : $corretValue.= "$v"; break;
                            case 4 : $corretValue.= "$v"; break;
                            case 5 : $corretValue.= "$v"; break;
                        }
                    }
                
                list($etiqueta, $subcampo) = explode(".", $tag);
                $tags[$etiqueta][$subcampo] = $corretValue;
            }
        }


        foreach($this->spreadSheet as $tab => $spEti)
        {
            foreach($spEti as $etiqueta => $objEti)
            {
                if(isset($objEti->indicadores))
                {
                    foreach($objEti->indicadores as $indicador => $objects)
                    {
                        $this->spreadSheet[$tab][$etiqueta]->indicadores[$indicador]->formValue[0] = $tags[$etiqueta][$indicador];
                    }
                }
                if ( is_array($objEti->subFields ))
                {
                    foreach($objEti->subFields as $subfield => $objects)
                    {
                        if($objects->isRepetitive == 't' && $objEti->isRepetitive == 't')
                        {
                            $fValues        = is_array($repeatF[$etiqueta][$subfield])          ? $repeatF[$etiqueta][$subfield]        : array($repeatF[$etiqueta][$subfield]);
                            $prefixValue    = is_null($repeatF_prexif[$etiqueta][$subfield])    ? array() : (is_array($repeatF_prexif[$etiqueta][$subfield])   ? $repeatF_prexif[$etiqueta][$subfield] : array($repeatF_prexif[$etiqueta][$subfield]));
                            $suffixValue    = is_null($repeatF_suffix[$etiqueta][$subfield])    ? array() : (is_array($repeatF_suffix[$etiqueta][$subfield])   ? $repeatF_suffix[$etiqueta][$subfield] : array($repeatF_suffix[$etiqueta][$subfield]));
                            $separatorVal   = is_null($repeatF_separator[$etiqueta][$subfield]) ? array() : (is_array($repeatF_separator[$etiqueta][$subfield])? $repeatF_separator[$etiqueta][$subfield] : array($repeatF_separator[$etiqueta][$subfield]));
                        }
                        else
                        {
                            $fValues        = is_array($tags[$etiqueta][$subfield])     ? $tags[$etiqueta][$subfield]   : array($tags[$etiqueta][$subfield]);
                            $prefixValue    = is_null($prexif[$etiqueta][$subfield])    ? array() : (is_array($prexif[$etiqueta][$subfield])   ? $prexif[$etiqueta][$subfield] : array($prexif[$etiqueta][$subfield]));
                            $suffixValue    = is_null($suffix[$etiqueta][$subfield])    ? array() : (is_array($suffix[$etiqueta][$subfield])   ? $suffix[$etiqueta][$subfield] : array($suffix[$etiqueta][$subfield]));
                            $separatorVal   = is_null($separator[$etiqueta][$subfield]) ? array() : (is_array($separator[$etiqueta][$subfield])? $separator[$etiqueta][$subfield] : array($separator[$etiqueta][$subfield]));
                        }

                        $this->spreadSheet[$tab][$etiqueta]->subFields[$subfield]->formValue    = $fValues;
                        $this->spreadSheet[$tab][$etiqueta]->subFields[$subfield]->formPrefix   = $prefixValue;
                        $this->spreadSheet[$tab][$etiqueta]->subFields[$subfield]->formSuffix   = $suffixValue;
                        $this->spreadSheet[$tab][$etiqueta]->subFields[$subfield]->formSeparator= $separatorVal;
                    }
                }
            }
        }

        if(isset($this->spreadSheet[MARC_FIXED_DATA_FIELD][MARC_FIXED_DATA_FIELD]->subFields))
        {
            $string = "";
            foreach($this->spreadSheet[MARC_FIXED_DATA_FIELD][MARC_FIXED_DATA_FIELD]->subFields as $subfield => $object)
            {
                $begin = $subfield[0];

                if(strlen($object->formValue[0]) > $object->fieldContent->lenght)
                {
                    $object->formValue[0] = substr($object->formValue[0], 0, $object->fieldContent->lenght);
                }
                
                $string.= GUtil::strPad($object->formValue[0], $object->fieldContent->lenght , MARC_SPACE);
            }
            
            list($e,$s) = explode(".",MARC_FIXED_DATA_TAG);
            $this->spreadSheet[MARC_FIXED_DATA_FIELD][MARC_FIXED_DATA_FIELD]->subFields[$s]->formValue[0] = $string;
        }
        
        $this->saveContent();

        return $this->spreadSheet;
    }

    /**
     * Função que valida os dados enviados pelo formulário, chamada antes do salvamento.
     * FIXME isso deveria ser feito no formulário
     *
     * @param stdClass $args objeto do post
     * @return array de errors
     */
    function validatorSpreadsheetFields($args)
    { 
        //TODO Esta validação da catalogação tem que ser integrada corretamente com os GValidators.
        $this->getContent();

        if(!$this->localValidator)
        {
            return true;
        }

        $this->_errors = null;

        $ok = true;

        foreach ($this->localValidator as $objValidator)
        {
            $fieldName      = $objValidator->fieldName;
            $fieldlabel     = $objValidator->label;
            $isRepetitive   = $objValidator->isRepetitive;
            $hasRepetitive  = $objValidator->hasRepetitive;
            $repetitiveName = "gtcRepeatField_{$objValidator->fieldId}";
            $specialField   = "especialField_{$fieldName}";
            
            foreach ($objValidator->workValidator as $typeValidator)
            {
                if($isRepetitive == DB_TRUE && $hasRepetitive)
                {
                    if($typeValidator == 'required')
                    {
                        if(!isset($args->$repetitiveName) || !count($args->$repetitiveName))
                        {
                            $ok = false;
                            $this->addError(_M('O campo @1 é necessário.', $this->module, strlen($objValidator->label) ? "{$objValidator->tag} - {$objValidator->label}" : $objValidator->tag), $objValidator->fieldName);
                            continue;
                        }
                        else
                        {
                            foreach ($args->$repetitiveName as $repeatFields)
                            {
                                if(!strlen($repeatFields->{$objValidator->fieldName}))
                                {
                                    $ok = false;
                                    $this->addError(_M('O campo @1 é necessário.', $this->module, strlen($objValidator->label) ? "{$objValidator->tag} - {$objValidator->label}" : $objValidator->tag), $objValidator->fieldName);
                                    continue;
                                }
                            }
                        }
                    }
                    if($typeValidator == 'unique')
                    {
                        if(is_array($args->$repetitiveName))
                        {
                            $duplicateInArray = false;
                            foreach ($args->$repetitiveName as $i => $repeatFields)
                            {
                                if(strlen($repeatFields->{$objValidator->fieldName}) && !$duplicateInArray)
                                {
                                    $this->businessMaterial->clean();
                                    list($this->businessMaterial->fieldid, $this->businessMaterial->subfieldid) = explode(".", $objValidator->tag);
                                    $this->businessMaterial->exactContent       = $repeatFields->{$objValidator->fieldName};
                                    $this->businessMaterial->controlNumberDiff  = is_numeric($args->controlNumber) ? $args->controlNumber : null;

                                    foreach($args->$repetitiveName as $i2 => $repeatFields2)
                                    {
                                        if($i != $i2 && $repeatFields2->{$objValidator->fieldName} == $repeatFields->{$objValidator->fieldName})
                                        {
                                            $duplicateInArray = true;
                                            break;
                                        }
                                    }

                                    if($duplicateInArray || $this->businessMaterial->getMaterial())
                                    {
                                        $ok = false;
                                        $this->addError(_M("O campo @1 é único", $this->module, (strlen($objValidator->label) ? "{$objValidator->tag} - {$objValidator->label}" : $objValidator->tag), $this->businessMaterial->exactContent) , $objValidator->fieldName);
                                        $this->addError(_M("Repita contéudo: @1.", $this->module, $this->businessMaterial->exactContent) , $objValidator->fieldName);
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                    if($typeValidator == 'date')
                    {
                        if(!isset($args->$repetitiveName) || !count($args->$repetitiveName))
                        {
                            continue;
                        }
                        else
                        {
                            foreach ($args->$repetitiveName as $repeatFields)
                            {
                                $mask = ereg("/", $repeatFields->{$objValidator->fieldName}) ? "dd/mm/yyyy" : null;
                                $mask = ereg("-", $repeatFields->{$objValidator->fieldName}) ? "yyyy-mm-dd" : $mask;

                                if(strlen($repeatFields->{$objValidator->fieldName}) && is_null(GDate::construct($repeatFields->{$objValidator->fieldName})->isValid()))
                                {
                                    $ok = false;
                    			    $this->addError(_M('O campo @1 é uma data inválida.', $this->module, strlen($objValidator->label) ? "{$objValidator->tag} - {$objValidator->label}" : $objValidator->tag) , $objValidator->fieldName);
                    			    continue;
                    			}
                            }
                        }
                    }
                    if($typeValidator == 'url')
                    {
                        if(!isset($args->$repetitiveName) || !count($args->$repetitiveName))
                        {
                            continue;
                        }
                        else
                        {
                            foreach ($args->$repetitiveName as $repeatFields)
                            {
                                //Se tiver um conteúdo no campo e ele não for uma url válida.
                                if( strlen($repeatFields->{$objValidator->fieldName}) && !filter_var($repeatFields->{$objValidator->fieldName}, FILTER_VALIDATE_URL) )
                                {
                                    $ok = false;
                    			    $this->addError(_M('O campo @1 é uma URL inválida.', $this->module, strlen($objValidator->label) ? "{$objValidator->tag} - {$objValidator->label}" : $objValidator->tag) , $objValidator->fieldName);
                    			    continue;
                    			}
                            }
                        }
                    }
                }
                else
                {
                    if($typeValidator == 'required')
                    {
                        if( (!isset($args->{$objValidator->fieldName}) || !strlen($args->{$objValidator->fieldName})) && (!isset($args->{$specialField}) || !strlen($args->{$specialField})))
                        {
                            $ok = false;
                            $this->addError(_M('O campo @1 é necessário.', $this->module, strlen($objValidator->label) ? "{$objValidator->tag} - {$objValidator->label}" : $objValidator->tag),$objValidator->fieldName);
                            continue;
                        }
                    }

                    if($typeValidator == 'unique')
                    {
                        if(strlen($args->{$objValidator->fieldName}))
                        {
                            $this->businessMaterial->clean();
                            list($this->businessMaterial->fieldid, $this->businessMaterial->subfieldid) = explode(".", $objValidator->tag);
                            $this->businessMaterial->exactContent       = $args->{$objValidator->fieldName};
                            $this->businessMaterial->controlNumberDiff  = is_numeric($args->controlNumber) ? $args->controlNumber : null;

                            if($this->businessMaterial->getMaterial())
                            {
                                $ok = false;
                                $this->addError(_M("O campo @1 é único", $this->module, strlen($objValidator->label) ? "{$objValidator->tag} - {$objValidator->label}" : $objValidator->tag), $objValidator->fieldName);
                                continue;
                            }
                        }
                    }
                    
                    if($typeValidator == 'date')
                    {
                        $mask = ereg("/", $args->{$objValidator->fieldName}) ? "dd/mm/yyyy" : null;
                        $mask = ereg("-", $args->{$objValidator->fieldName}) ? "yyyy-mm-dd" : $mask;
 
                        if( strlen($args->{$objValidator->fieldName}) && is_null(GDate::construct($args->{$objValidator->fieldName})->isValid()) )
                        {
                            $ok = false;
            			    $this->addError(_M('O campo @1 é uma data inválida.', $this->module, strlen($objValidator->label) ? "{$objValidator->tag} - {$objValidator->label}" : $objValidator->tag), $objValidator->fieldName);
            			    continue;
            			}
                    }
                    if($typeValidator == 'url')
                    {
                        //Se tiver um conteúdo no campo e ele não for uma url válida.
                        if( strlen($args->{$objValidator->fieldName}) && !filter_var($args->{$objValidator->fieldName}, FILTER_VALIDATE_URL) )
                        {
                            $ok = false;
                            $this->addError(_M('O campo @1 é uma URL inválida.', $this->module, strlen($objValidator->label) ? "{$objValidator->tag} - {$objValidator->label}" : $objValidator->tag) , $objValidator->fieldName);
                            continue;
                        }
                    }
                }
            }
        }

        return $ok;
    }

    /**
     * Função extendida do MBussiness para gerenciar a adição de erros.
     *
     * @param string $err mensagem de erro
     * @param string $fieldName id do campo
     */
    public function addError($err , $fieldName )
    {
        if ($err)
        {
            if (is_array($err))
            {
                //$this->_errors = array_merge($this->_errors, $err);
                foreach ( $err as $line => $info )
                {
                    $this->addError($info, $fieldName);
                }
            }
            else
            {
                $this->_errors[$fieldName] = $err;
            }
        }
    }

    /**
     * Retorna o valor dos campos de uma determinada tag
     *
     * @param string $tag
     * @return array
     */
    function getTagValue($tag, $ignoreEmptyValues = false)
    {
        list($fieldId, $subfieldId) = explode(".", $tag);
        $spreadsheet            = $this->getSpreadsheetFromSession();

        foreach ($spreadsheet as $fieldIdArray)
        {
            foreach ($fieldIdArray as $fieldIdx => $fieldObject)
            {
                if($fieldId != $fieldIdx)
                {
                    continue;
                }

                foreach ($fieldObject->subFields as $subfieldIdx => $content)
                {
                    if($subfieldId != $subfieldIdx)
                    {
                        continue;
                    }

                    if(!$ignoreEmptyValues)
                    {
                        return $content->formValue;
                    }

                    $result = false;
                    foreach ($content->formValue as $line => $values)
                    {
                        if(strlen($values))
                        {
                            $result[$line] = $values;
                        }
                    }
                    return $result;
                }
            }
        }
    }


    /**
     * Salva os campos/dados na Sessao
     */
    function saveContent()
    {
        $_SESSION['gtcCataloge']['leaderFields']    = $this->leaderFields;
        $_SESSION['gtcCataloge']['spreadSheet']     = $this->spreadSheet;
        $_SESSION['gtcCataloge']['controlNumber']   = $this->controlNumber;
        $_SESSION['gtcCataloge']['leaderString']    = $this->leaderString;
        $_SESSION['gtcCataloge']['loadFields']      = $this->loadFields;
        $_SESSION['gtcCataloge']['duplicate']       = $this->duplicateMaterial;
        $_SESSION['gtcCataloge']['controlFather']   = $this->controlNumberFather;
        $_SESSION['gtcCataloge']['preCatalogue']    = $this->preCatalogue;
        $_SESSION['gtcCataloge']['normalSave']      = $this->normalSave;
        $_SESSION['gtcCataloge']['preCatSave']      = $this->preCatSave;
        $_SESSION['gtcCataloge']['cameFromPreCat']  = $this->cameFromPreCat;
        $_SESSION['gtcCataloge']['requiredFields']  = $this->requiredFields;
        $_SESSION['gtcCataloge']['localValidator']  = $this->localValidator;
        $_SESSION['gtcCataloge']['hiddenDefaultFields']  = $this->hiddenDefaultFields;
        $_SESSION['gtcCataloge']['repeatFieldRequired']  = $this->repeatFieldRequired;
        $_SESSION['gtcCataloge']['category']        = $this->spreadsheetCategory;
        $_SESSION['gtcCataloge']['level']           = $this->level;
    }


    /**
     * Limpa os campos
     */
    function cleanContent()
    {
        $_SESSION['gtcCataloge'] = null;
    }


    /**
     * carrega os dados da sessao
     */
    function getContent()
    {
        $contentSave                    = $_SESSION['gtcCataloge'];

        $this->leaderFields             = $contentSave['leaderFields'];
        $this->spreadSheet              = $contentSave['spreadSheet'];
        $this->controlNumber            = $contentSave['controlNumber'];
        $this->leaderString             = $contentSave['leaderString'];
        $this->loadFields               = $contentSave['loadFields'];
        $this->duplicateMaterial        = $contentSave['duplicate'];
        $this->controlNumberFather      = $contentSave['controlFather'];
        $this->preCatalogue             = $contentSave['preCatalogue'];
        $this->preCatSave               = $contentSave['preCatSave'];
        $this->normalSave               = $contentSave['normalSave'];
        $this->cameFromPreCat           = $contentSave['cameFromPreCat'];
        $this->hiddenDefaultFields      = $contentSave['hiddenDefaultFields'];
        $this->repeatFieldRequired      = $contentSave['repeatFieldRequired'];
        $this->requiredFields           = $contentSave['repeatFieldRequired'];
        $this->localValidator           = $contentSave['localValidator'];
        $this->spreadsheetCategory      = $contentSave['category'];
        $this->level                    = $contentSave['level'];

        return $contentSave;
    }


    /**
     * Enter description here...
     *
     */
    function getControlNumberFromSession()
    {
        return $_SESSION['gtcCataloge']['controlNumber'];
    }


    /**
     * carrega os dados da sessao
     */
    function getSpreadsheetFromSession()
    {
        $contentSave = $_SESSION['gtcCataloge'];
        return $contentSave['spreadSheet'];
    }


    /**
     * carrega os dados da sessao
     */
    function getLeaderStringFromSession()
    {
        $contentSave = $_SESSION['gtcCataloge'];
        return $contentSave['leaderString'];
    }

    /**
     * carrega os dados da sessao
     */
    function setLeaderInSession($leader)
    {
        $_SESSION['gtcCataloge']['leaderString'] = $leader;
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $item
     * @param unknown_type $futureStatusId
     */
    function futureStatusItem($item, $futureStatusId)
    {
        $_SESSION['gtcCataloge']['futureStatusItem'][$item] = $futureStatusId;
    }


    /**
     * Enter description here...
     *
     */
    function cleanFutureStatusItem($item = null)
    {
        if(!is_null($item))
        {
            unset($_SESSION['gtcCataloge']['futureStatusItem'][$item]);
            return;
        }

        unset($_SESSION['gtcCataloge']['futureStatusItem']);
    }


    /**
     * Enter description here...
     *
     */
    function getFutureStatusItem($item)
    {
        return $_SESSION['gtcCataloge']['futureStatusItem'][$item];
    }


    function getHiddenDefaultFields()
    {
        return $_SESSION['gtcCataloge']['hiddenDefaultFields'];
    }


    /**
     *
     */
    function setLoadFields($status = true)
    {
        $this->loadFields = $status;
        $_SESSION['gtcCataloge']['loadFields'] = $this->loadFields;
    }


    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getCategory()
    {
        return $_SESSION['gtcCataloge']['category'];
    }


    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getLevel()
    {
        return $_SESSION['gtcCataloge']['level'];
    }


    /**
     * Enter description here...
     *
     * @param unknown_type $status
     */
    function setDuplicateMaterial($status = true)
    {
        $this->duplicateMaterial    = $status;
        $this->loadFields           = $status;
    }


    /**
     * Enter description here...
     *
     * @return unknown
     */
    function getWorkNumberValue()
    {
        return $this->workNumber->value;
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    function getWorkNumberFieldName()
    {
        return $this->workNumber->fieldName;
    }



    /**
     * Enter description here...
     *
     * @param unknown_type $controlNumber
     */
    function synchronizeFatherAndSon()
    {
        $this->businessMaterial->synchronizeFatherAndSon($this->controlNumber);
    }


    /**
     * Testa se existe item number duplicado
     * @param (array) com os itens
     * @param número de controle
     * @return boolean
     */
    public function verifyRepeatItemNumber($itens, $controlNumberItens=null)
    {
    	$tombos = array();
        foreach( $itens as $exemplary )
        {
            $exemplaryId =  $exemplary->spreeadsheetField_949_a;
            $controlNumber = $this->businessExemplaryControl->getControlNumber($exemplaryId);
            //testa se existe na base
            if ( (strlen($controlNumber) > 0) && ($controlNumber != $controlNumberItens) )
            {
                $tombos[] = $exemplaryId;
            }
        }

        if ( count($tombos) > 0 )
        {
        	$tombo = implode(', ', $tombos);
        	$this->addError(_M('Os seguintes exemplares já existem no sistema: @1', $this->module, $tombo));
        	return false;
        }

        return true;
    }
    
    /**
     * Método invocado quando é realizado clone do business.
     * 
     * Ao fazer clone do objeto, atributos que são objetos não funciona o clone, portanto todos os atributos que são objetos devem ter o clone feito manualmente, como o declarado abaixo.
     */
    public function __clone() 
    {
        if ( is_array($this->spreadSheet) )
        {
            foreach ( $this->spreadSheet as $z => $fields )
            {
                if ( is_array($fields) )
                {
                    foreach ( $fields as $i => $field )
                    {
                        if ( is_object($field) )
                        {
                            $field = clone $field;
                            $this->spreadSheet[$z][$i] = $field;
                            
                            if ( is_array($field->subFields) )
                            {
                                foreach ( $field->subFields as $j => $subfield )
                                {
                                    if ( is_object($subfield) )
                                    {
                                        $this->spreadSheet[$z][$i]->subFields[$j] = clone($subfield);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
