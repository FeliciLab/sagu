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
 * This file manage operations for material
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 * Luiz Gregory Filho   [luiz@solis.coop.br]
 * Moises Heberle       [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 01/08/2008
 *
 **/
class BusinessGnuteca3BusOperationMaterial extends GMessages
{
    public $action;
    public $gridData;
    public $busClassificationArea;
    public $busExemplaryControl;
    public $busMaterial;
    public $busMaterialControl;
    public $busInterestsArea;
    public $busLibraryUnit;
    public $busLibraryUnitConfig;
    public $busPerson;
    public $busPolicy;
    public $busPersonConfig;
    public $busPreference;
    public $busEmailControlNotifyAquisition;


    public function __construct()
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        parent::__construct();
        $MIOLO->getClass($module, 'GMail');

        $this->busClassificationArea            = $MIOLO->getBusiness($module, 'BusClassificationArea');
        $this->busExemplaryControl              = $MIOLO->getBusiness($module, 'BusExemplaryControl');
        $this->busMaterial                      = $MIOLO->getBusiness($module, 'BusMaterial');
        $this->busMaterialControl               = $MIOLO->getBusiness($module, 'BusMaterialControl');
        $this->busInterestsArea                 = $MIOLO->getBusiness($module, 'BusInterestsArea');
        $this->busLibraryUnit                   = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $this->busLibraryUnitConfig             = $MIOLO->getBusiness($module, 'BusLibraryUnitConfig');
        $this->busPreference                    = $MIOLO->getBusiness($module, 'BusPreference');
        $this->busPerson                        = $MIOLO->getBusiness($module, 'BusPerson');
        $this->busPolicy                        = $MIOLO->getBusiness($module, 'BusPolicy');
        $this->busPersonConfig                  = $MIOLO->getBusiness($module, 'BusPersonConfig');
        $this->busEmailControlNotifyAquisition  = $MIOLO->getBusiness($module, 'BusEmailControlNotifyAquisition');
        $this->sendMail = new GSendMail();
    }

    private function getBookForNotifyAquisitions($date, $libraryUnitId)
    {
    	$date = new GDate($date);
    	$date->addDay(-USER_NOTIFY_AQUISITION); //normalmente 15 dias
    	$date = $date->getDate(GDate::MASK_DATE_USER);
        
        $controlNumbers = $this->busMaterialControl->getControlNumbersByDate($date, $libraryUnitId);

        if ( empty( $controlNumbers[0] ) )
        {
            return false;
        }

        $areasDeClassificacao = $this->busClassificationArea->searchClassificationArea(true);

        if ( !$areasDeClassificacao )
        {
            return false;
        }

        $materials = null;

        foreach ($areasDeClassificacao as $areaObject)
        {
            $class          = $areaObject->classification;
            $ignoreClass    = $areaObject->ignoreClassification;

            $ok = $this->busMaterial->getControlNumberRelativeClassification($class, $ignoreClass, $controlNumbers, TRUE);

            if(!$ok)
            {
                continue;
            }

            $materials[$areaObject->classificationAreaId]->areaName     = $areaObject->areaName;
            $materials[$areaObject->classificationAreaId]->class        = $areaObject->classification;
            $materials[$areaObject->classificationAreaId]->areaId       = $areaObject->classificationAreaId;
            $materials[$areaObject->classificationAreaId]->ignoreClass  = $areaObject->ignoreClassification;

            $books = null;

            foreach ($ok as $materialObject)
            {
                $title      = $this->busMaterial->getContentTag($materialObject[0], MARC_TITLE_TAG);
                $author     = $this->busMaterial->getContentTag($materialObject[0], MARC_AUTHOR_TAG);
                $entranceDate = $this->busMaterialControl->getEntraceDate($materialObject[0]); //pega a data de entrada
                $libraries  = $this->busExemplaryControl->getLibrariesOfMaterial($materialObject[0], $libraryUnitId);

                if ( strlen($entranceDate) > 0 )
                {
                	$books[$materialObject[0]]->entranceDate = $entranceDate;
                }    
                
                if ( (!strlen($title) && !strlen($author)) || !$libraries )
                {
                    continue;
                }

                $books[$materialObject[0]]->title   = strlen($title)    ? $title    : " - ";
                $books[$materialObject[0]]->author  = strlen($author)   ? $author   : " - ";

                $classification = explode(",", MARC_CLASSIFICATION_TAG);
                $classification = $classification[0];

                $area = $this->busMaterial->getContentTag($materialObject[0], $classification);

                if ($area)
                {
                    $books[$materialObject[0]]->callNumber.= "{$area}<br>";
                }

                $vol = $this->busMaterial->getContentTag($materialObject[0], MARC_EXEMPLARY_VOLUME_TAG);

                if ( strlen($vol) > 0 )
                {
                	$books[$materialObject[0]]->vol = $vol;
                }

                $edit = $this->busMaterial->getContentTag($materialObject[0], MARC_EDITION_TAG);

                if ( strlen($edit) > 0 )
                {
                	$books[$materialObject[0]]->edit = $edit;
                }

                $cutter = $this->busMaterial->getContentTag($materialObject[0], MARC_CUTTER_TAG);

                if ( $cutter )
                {
                     $books[$materialObject[0]]->callNumber.= $cutter;
                }

                $books[$materialObject[0]]->libraries = $libraries;
            }

            $materials[$areaObject->classificationAreaId]->books = $books;
        }

        return $materials;
    }


    private function getPersonInterese($areas)
    {
        $personInterests = array();
        foreach ($areas as $classificationAreaId => $bookObject)
        {
            $this->busInterestsArea->classificationAreaId   = $classificationAreaId;
            $areaInterese = $this->busInterestsArea->searchInterestsAreaLink(true);

            foreach($areaInterese as $interesanteAreaObject )
            {
                if(!strlen($interesanteAreaObject->personId) || !strlen($interesanteAreaObject->classificationAreaId))
                {
                    continue;
                }

                $person = $this->busPerson->getPerson($interesanteAreaObject->personId);
                if (!$person->bond)
                {
                	continue;
                }

                $userNotify = MUtil::getBooleanValue($this->busPersonConfig->getValuePersonConfig($interesanteAreaObject->personId, 'USER_SEND_NOTIFY_AQUISITION'));
                if ($userNotify)
                {
                    $personInterests[$interesanteAreaObject->personId][$interesanteAreaObject->classificationAreaId] = $interesanteAreaObject->classificationAreaId;
                }
            }
        }

        return $personInterests;
    }


    /**
     * Obtem o conteúdo do material formato para email de aquisições
     *
     * FIXME tranformar em formato de pesquisa
     *
     * @param array $books
     * @return string
     */
    private function makeMaterialsContent($books)
    {
        $booksContent = "";

        foreach ($books as $controlNumber => $bookObject)
        {
            $content = "";
            
        	$libContent = array();

        	foreach ($bookObject->libraries as $lib)
            {
                $libContent[$lib->libraryUnitId]= $lib->libraryName;
            }

            $libContent = implode(", ", $libContent);
            $bookObject->callNumber = str_replace('<br>', ' ', $bookObject->callNumber);
            
            $opts = array('controlNumber' => $controlNumber,
                          'searchFormat' => SIMPLE_SEARCH_SEARCH_FORMAT_ID); 
            $url = $this->MIOLO->getActionURL($this->module, 'main:search:simpleSearch', null, $opts);
            $link = new MLink(null,"<br>". _M('Clique aqui para acessar o material', $this->module), $url);
        	
        	$content .= "<b>". _M('Título', $this->module).": </b>{$bookObject->title}<br>\r";
            $content .= "<b>". _M('Autor', $this->module). ": </b>{$bookObject->author}<br>\r";
        	$content .= "<b>". _M('Classificação', $this->module).": </b><font color = '#0000FF'>{$bookObject->callNumber}</font><br>\r";
            
        	if ( $bookObject->vol )
        	{
        		$content .= "<b>". _M('Volume', $this->module). ": </b><font color = '#0000FF'>{$bookObject->vol}</font><br>\r";
        	}
        	
        	if ( $bookObject->edit )
        	{
        		$content .= "<b>". _M('Edição', $this->module). ": </b><font color = '#0000FF'>{$bookObject->edit}</font><br>\r";
        	}

        	$content .= "<b>". _M('Biblioteca', $this->module).": </b>{$libContent}\r";
            $content .= $link->generate()."<br><br>\r";             
            $content = str_replace('URL_GNUTECA', URL_GNUTECA, $content);
            
            $booksContent .= $content;
        }

        return $booksContent;
    }



    /**
     * Este método encaminha um email notificando os usuarios sobre as novas aquisições da biblioteca
     *
     * @param date string $date
     * @param integer $libraryUnitId
     * @return boolean
     */
    public function notifyAcquisition($endDate, $libraryUnitId)
    {
        $busMyLibrary = $this->MIOLO->getBusiness('gnuteca3', 'BusMyLibrary');
        
    	if ($libraryUnitId)
    	{
    	   $libraryUnitId = is_array($libraryUnitId) ? $libraryUnitId : array($libraryUnitId);
    	}
    	
        $materials = $this->getBookForNotifyAquisitions($endDate, $libraryUnitId);

        if ( empty($materials) )
        {
            return false;
        }

        $personInterests = $this->getPersonInterese($materials);
        
        if (!count($personInterests) > 0)
        {
            $this->addError(_M('Nenhuma pessoa interessada nas aquisições', $this->module));
            return false;
        }

        $personInterestsMessage = array();
        $newPersonInterestMessage = array();

        foreach ($personInterests as $personId => $classArea) //laço por pessoa
        {
            $error = false;
            
            //Verifica se já foi enviado aviso para usuário
            $checkSendMail = $this->busEmailControlNotifyAquisition->checkSendMail($personId);

            if (!$checkSendMail)
            {
                continue;
            }
            
            //pega última data de envio do user
            $lastSentOfUser = $this->busEmailControlNotifyAquisition->getLastSent($personId);

            //transforma em data
            if ( strlen($lastSentOfUser) > 0 )
            {
            	$lastSentOfUser = new GDate($lastSentOfUser);
            }
            else 
            {
                unset($lastSentOfUser);	
            }

            $personInformations = $this->busPerson->getBasicPersonInformations($personId);

            //mensagem
            $personInterestsMessage[$personId][0] = $personId;
            $personInterestsMessage[$personId][1] = $personInformations->name;
            $personInterestsMessage[$personId][4] = $personInformations->email;
                 
            //testa se pessoa tem e-mail
            if(!strlen($personInformations->email))
            {
                $personInterestsMessage[$personId][2] = DB_FALSE;
                $personInterestsMessage[$personId][3] = _M("E-mail da pessoa está em branco", $this->module);

                $error = true;
            }
                
            $contentMailPerson = "";

            $librariesOfPerson = array();

            foreach ($classArea as $classAreaId)
            {
                $areaName   = $materials[$classAreaId]->areaName;
                $books      = $materials[$classAreaId]->books;

                //pega as unidades onde o usuário será avisado e pega somente os materiais que ainda não foram avisados para o usuário
                if ( is_array($books) )
                {
                	$newBooks = array();

                    foreach($books as $i=>$book)
                    {
                    	//testa se já usuário recebeu aviso deste material
                        if ( $lastSentOfUser )
                        {
	                    	$entranceDate = $book->entranceDate;
                                $entranceDate = new GDate($entranceDate);
	                    	if ( $entranceDate->compare($lastSentOfUser, '>=') )
	                    	{
	                    		$newBooks[$i] = $book;
	                    	}
	                    	else 
	                    	{
	                            continue;                    		
	                    	}
                        }
                        else 
                        {
                        	$newBooks[$i] = $book;
                        }
                        
                        $libraries = $book->libraries;
                        if ( is_array($libraries) )
                        {
                            foreach($libraries as $library)
                            {
                                $librariesOfPerson[$library->libraryUnitId] = $personId;
                            }
                        }
                    }
                }
                   
                //só seta a área de tiver materiais na área
                if ( count($newBooks) > 0 )
                {
	                $contentMailPerson.= "<br><br>";
	                $contentMailPerson.= "\r<font size='3'><b>". _M("Área",$this->module) . ": $areaName</b></font>\r <br><br>";
	                $contentMailPerson.= $this->makeMaterialsContent($newBooks);
                }
            }
                
            //se não tiver conteúdo, aborta o processo
            if ( strlen($contentMailPerson) == 0 )
            {
            	unset($personInterestsMessage[$personId]);
            	continue;
            }

            $content = EMAIL_NOTIFY_ACQUISITION_CONTENT;
            $subject = EMAIL_NOTIFY_ACQUISITION_SUBJECT;
            
            $gf = new GFunction();
            $gf->setVariable('$USER_NAME', $personInformations->name);
            
            $dateX = new GDate($endDate);
            $dateX->addDay(-USER_NOTIFY_AQUISITION);

            if ( ($lastSentOfUser) && ($lastSentOfUser->compare($dateX, '>=')) )
            {
                $gf->setVariable('$DATE_AQUISITIONS', $lastSentOfUser->getDate(GDate::MASK_DATE_USER));
            }
            else 
            {
                $gf->setVariable('$DATE_AQUISITIONS', $dateX->getDate(GDate::MASK_DATE_USER));
            }
               
            $content = $gf->interpret( $content );
            $content = "<html>\r<body>\r" . str_replace('$ACQUISITIONS', $contentMailPerson, $content). "\r</body>\r</html>";

            //testa se e-mail é no formato conta@domínio.extensão
            if (!$error && !preg_match("/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+\.([a-zA-Z]{2,4})$/", $personInformations->email))
            {
                $personInterestsMessage[$personId][2] = DB_FALSE;
                $personInterestsMessage[$personId][3] = _M("Email da pessoa é inválido.", $this->module);
                $error = true;
            }
                
            if ( !$error )
            {
                if (!$this->sendMail->sendMailToUserNotifyAcquisition($personInformations, $subject, $content) )
                {
                    $personInterestsMessage[$personId][2] = DB_FALSE;
                    $personInterestsMessage[$personId][3] = _M("Falha no envio do e-mail", $this->module);
                }
                else
                {
                    //grava registro na minha biblioteca
                    $busMyLibrary->myLibraryId = null;
                    $busMyLibrary->personId = $personId;
                    $busMyLibrary->date = GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER);
                    $busMyLibrary->message = stripslashes($busMyLibrary->getNewAcquisitionMessage());
                    $busMyLibrary->visible = DB_TRUE;
                    $busMyLibrary->insertMyLibrary();

                    $this->busEmailControlNotifyAquisition->updateLastDate($personId);
                    $personInterestsMessage[$personId][2] = DB_TRUE;
                    $personInterestsMessage[$personId][3] = _M("Sucesso!", $this->module);
                }
            }
            
            if ( is_array($librariesOfPerson) )
            {
            	foreach($librariesOfPerson as $libraryUnitId=>$personId)
            	{
            		$newPersonInterestMessage[$libraryUnitId][$personId] = $personInterestsMessage[$personId];
            	}
            }
        }

        foreach( $newPersonInterestMessage as $libraryUnitId => $persons )
        {
            $this->sendMail->sendMailToAdminResultOfNotifyAcquisition($persons, $libraryUnitId);
        }
            
        // MONTA GRID COM OS DADOS DOS ENVIOS DE EMAIL.
        foreach ($personInterestsMessage as $content)
        {
            $this->addGridData($content);
        }
        
        return true;
    }


    public function addGridData($data)
    {
        $this->gridData[] = $data;
    }


    public function getGridData()
    {
        return $this->gridData;
    }
}
?>
