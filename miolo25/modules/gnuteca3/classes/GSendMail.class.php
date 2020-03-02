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
 * Class
 *
 * @author Luiz Gilberto Gregory Filho [luz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop]
 *
 * @since
 * Class created on 08/09/2009
 *
 **/

class GSendMail
{
	public  $MIOLO, $module;
	public  $busLibraryUnitConfig;
	public  $busExemplaryControl;
	public  $busLibraryUnit;
	public  $busLoanBetweenLibrary;
	public  $mailTo;
	private $mail;

	function __construct()
	{
		$this->MIOLO    = MIOLO::getInstance();
		$this->module   = MIOLO::getCurrentModule();

		$this->busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');
		$this->busExemplaryControl  = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
		$this->busLibraryUnit       = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
		$this->busLoanBetweenLibrary= $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibrary');
		$this->MIOLO->getClass($this->module, 'GMail');
		$this->MIOLO->getClass($this->module, 'GFunction');
	}



	/**
	 * Este método encaminha um email para o administrador informando que uma requisição foi cancelada.
	 */
	public function sendMailToAdminCancelRequestChangeExemplaryStatus( $requestChangeExemplaryStatusId )
	{
		$busRequestChangeExemplaryStatus    = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatus' );
		$libraryUnit                        = $busRequestChangeExemplaryStatus->getLibraryUnit($requestChangeExemplaryStatusId);

		$gf = new GFunction();
		$gf->setVariable('$LN', "\n");
		$gf->setVariable('$SP', " ");
		$gf->setVariable('$REQUEST_ID', $requestChangeExemplaryStatusId);

		$to         = $this->getAdminEmailTo($libraryUnit->libraryUnitId, "EMAIL_ADMIN_REQUEST_CHANGE_EXEMPLARY_STATUS");
		$subject    = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnit->libraryUnitId, 'EMAIL_CANCEL_SUBJECT_REQUEST_CHANGE_EXEMPLARY_STATUS');
		$content    = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnit->libraryUnitId, 'EMAIL_CANCEL_CONTENT_REQUEST_CHANGE_EXEMPLARY_STATUS');

		$mail = new GMail();
		$mail->setAddress($to);
		$mail->setSubject($subject);
		$mail->setContent($gf->interpret($content));
		$mail->send();
	}




	/**
	 * Email que comunica o administrado da biblioteca sobre uma nova requisição de emprestimo entre biblioteca
	 *
	 * @param integer $loanBetweenLibraryId
	 * @return boolean
	 */
	public function sendMailLoanBetweenLibraryRequest($loanBetweenLibraryId)
	{
		return $this->sendMailLoanBetweenLibrary($loanBetweenLibraryId, "request");
	}


	/**
	 * Email que comunica o administrado da biblioteca sobre uma requisição de emprestimo entre biblioteca que foi cancelada
	 *
	 * @param integer $loanBetweenLibraryId
	 * @return boolean
	 */
	public function sendMailLoanBetweenLibraryCancel($loanBetweenLibraryId)
	{
		return $this->sendMailLoanBetweenLibrary($loanBetweenLibraryId, "cancel", null);
	}


	/**
	 * Email que comunica o administrado da biblioteca sobre uma requisição de emprestimo entre biblioteca que foi cancelada
	 *
	 * @param integer $loanBetweenLibraryId
	 * @return boolean
	 */
	public function sendMailLoanBetweenLibraryReturnMaterial($loanBetweenLibraryId, $exemplaries)
	{
		return $this->sendMailLoanBetweenLibrary($loanBetweenLibraryId, "return", $exemplaries);
	}


	/**
	 * Email que comunica o administrado da biblioteca sobre uma requisição de emprestimo entre biblioteca que foi cancelada
	 *
	 * @param integer $loanBetweenLibraryId
	 * @return boolean
	 */
	public function sendMailLoanBetweenLibraryApproveMaterial($loanBetweenLibraryId, $exemplaries)
	{
		return $this->sendMailLoanBetweenLibrary($loanBetweenLibraryId, "approve", $exemplaries);
	}



	/**
	 * Email que comunica o administrado da biblioteca sobre uma requisição de emprestimo entre biblioteca que foi cancelada
	 *
	 * @param integer $loanBetweenLibraryId
	 * @return boolean
	 */
	public function sendMailLoanBetweenLibraryDisapproveMaterial($loanBetweenLibraryId)
	{
		return $this->sendMailLoanBetweenLibrary($loanBetweenLibraryId, "disapprove");
	}



	/**
	 * Envia um email para o administrador da biblioteca sobre emprestimos entre bibliotecas
	 *
	 * @param integer $loanBetweenLibraryId
	 * @return boolean
	 */
	public function sendMailLoanBetweenLibrary($loanBetweenLibraryId, $type = "request", $auxArgs = null)
	{
		$busLoanBetweenLibrary  = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibrary');
		$busExemplaryControl    = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
		$busLibraryUnit         = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');

		$loanBetweenLibrary     = $busLoanBetweenLibrary->getLoanBetweenLibrary($loanBetweenLibraryId);

		if(!$loanBetweenLibrary || !$loanBetweenLibrary->libraryComposition)
		{
			return false;
		}

		$composition = $loanBetweenLibrary->libraryComposition;

		//Group by library
		$libraryItems = array();
		foreach ($composition as $ex)
		{
			$add = false;

			switch ($type)
			{
				case "return"   :
				case "approve"  :
					if(in_array($ex->itemNumber, $auxArgs))
					{
						$add = true;
					}
					break;

				default:
					$add = true;
					break;
			}

			if($add)
			{
				$libraryUnitId = $this->busExemplaryControl->getExemplaryControl($ex->itemNumber);
                //Se reprovar solicitação, enviar só um aviso
				if ($type == "disapprove")
                {
                    $libraryItems[1][] = $ex;
                }
                else
                {
                    $libraryItems[$libraryUnitId->originalLibraryUnitId][] = $ex;
                }
			}
		}

		foreach ($libraryItems as $libraryUnitId => $exemplaryes)
		{
			$materials = array();
			foreach ($exemplaryes as $ex)
			{
				$info = $busExemplaryControl->getExemplaryControl($ex->itemNumber, TRUE);
				$materials[$ex->itemNumber] = _M('Número do exemplar', $this->module)       . ": $ex->itemNumber\n";
				$materials[$ex->itemNumber].= _M('Título', $this->module)             . ": $info->title\n";
				$materials[$ex->itemNumber].= _M('Autor', $this->module)            . ": $info->author\n";
				$materials[$ex->itemNumber].= _M('Classificação', $this->module)    . ": $info->classification $info->cutter\n";
			}

			//$to      = $this->getAdminEmailTo($libraryUnitid, 'EMAIL_ADMIN_LOAN_BETWEEN_LIBRARY');
			//Pega a biblioteca de acordo com a função executada
			$libraryUnitIdRequest = $this->busLoanBetweenLibrary->getLibraryId($loanBetweenLibraryId);
		    if ( ($type == 'approve') || ($type == 'disapprove') )
            {
                $libraryUnitIdRequest = $libraryUnitId;
            	$libraryUnitId = $this->busLoanBetweenLibrary->getLibraryId($loanBetweenLibraryId);
            }
			$to      = $this->getEmailTo($loanBetweenLibraryId, $libraryUnitId, $type, $exemplaryes);

			$subject = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitIdRequest, 'EMAIL_LOANBETWEENLIBRARY_SUBJECT');

			$action = "";

			switch ($type)
			{
				case 'cancel'       :
					$content    = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitIdRequest, 'EMAIL_LOANBETWEENLIBRARY_CANCEL_CONTENT');
					$status     = _M('Cancelado', $this->module);
					break;

				case 'request'      :
					$content    = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitIdRequest, 'EMAIL_LOANBETWEENLIBRARY_REQUEST_CONTENT');
					$status     = _M('Solicitado', $this->module);
					break;

				case 'return'       :
					$content    = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitIdRequest, 'EMAIL_LOANBETWEENLIBRARY_RETURNMATERIAL_CONTENT');
					$status     = _M('Devolução do material', $this->module);
					break;

				case 'approve'      :
					$content    = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitIdRequest, 'EMAIL_LOANBETWEENLIBRARY_CONFIRMLOAN_CONTENT');
					$status     = _M('Aprovar material',    $this->module);
					$action     = _M('aprovou',            $this->module);
					break;

				case 'disapprove'   :
					$content    = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitIdRequest, 'EMAIL_LOANBETWEENLIBRARY_CONFIRMLOAN_CONTENT');
					$status     = _M('Reprovar material', $this->module);
					$action     = _M('não aprovou',        $this->module);
					break;
			}


			$library = $busLibraryUnit->getLibraryUnit($libraryUnitIdRequest);

			$gf = new GFunction();
			$gf->setVariable('$LN', "\n");
			$gf->setVariable('$STATUS',                     $status);
			$gf->setVariable('$ACTION',                     $action);
			$gf->setVariable('$LIBRARY_UNIT_DESCRIPTION',   $library->libraryName);
			$gf->setVariable('$MATERIALS',                  implode("\n", $materials));
			$gf->setVariable('$LOAN_DATE',                  GDate::construct($loanBetweenLibrary->loanDate)->getDate(GDate::MASK_DATE_USER));
			$gf->setVariable('$RETURN_FORECAST_DATE',       GDate::construct($loanBetweenLibrary->returnForecastDate)->getDate(GDate::MASK_DATE_USER));

			$mail = new GMail();
			$mail->setSubject($gf->interpret($subject));
			$mail->setContent($gf->interpret($content));
			$mail->setAddress($to);
			$mail->send();
		}
	}


	/**
	 * Pega e-mail do empréstimo entre bibliotecas de acordo com as ações
	 */
	public function getEmailTo($loanBetweenLibraryId, $libraryUnitId, $type)
	{
		if ( ($type == 'request') || ($type == 'return') || ($type == 'cancel') )
		{
			$emailLibrary  = $this->busLibraryUnit->getLibraryUnit1($libraryUnitId);
			return $emailLibrary->email;
		}
		elseif ( ($type == 'approve') || ($type == 'disapprove'))
		{
			$libraryUnitId = $this->busLoanBetweenLibrary->getLibraryId($loanBetweenLibraryId);
			$emailLibrary  = $this->busLibraryUnit->getLibraryUnit1($libraryUnitId);
			return $emailLibrary->email;
		}
		else
		{
			return EMAIL_ADMIN_LOAN_BETWEEN_LIBRARY;
		}
	}



	/**
	 * Envia um email comunicando o usuario que sua reserva foi atendida
	 *
	 * @param integer $reserveId
	 */
	public function sendMailToUserReserveAnswered($reserveObj, $personName, $personMail)
	{
		$busReserve             = $this->MIOLO->getBusiness($this->module, 'BusReserve' );
		$busReserveComposition  = $this->MIOLO->getBusiness($this->module, 'BusReserveComposition' );
		$busExemplaryControl    = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
		$busMaterial            = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
		$busLibraryUnit         = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');

		//Executa o processo de envio do aviso
		$reserveId = $reserveObj->reserveId;
		$busReserveComposition->reserveId = $reserveObj->reserveId;
		$rc = $busReserveComposition->getReserveComposition();

		$itemNumber = $rc[0]->itemNumber;
		$title      = $busMaterial->getContentByItemNumber($itemNumber, MARC_TITLE_TAG);

		$subject = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_RESERVE_ANSWERED_SUBJECT');
		$content = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_RESERVE_ANSWERED_CONTENT');


		$gf = new GFunction();
		$gf->setVariable('$USER_NAME',                  $personName);
		$gf->setVariable('$CODE',                       $reserveId);
		$gf->setVariable('$MATERIAL_TITLE',             $title);
		$gf->setVariable('$RESERVE_CODE',               $reserveId);
		$gf->setVariable('$LIBRARY_UNIT_DESCRIPTION',   $busLibraryUnit->getLibraryUnit($reserveObj->libraryUnitId)->libraryName);
		$gf->setVariable('$RESERVE_WITHDRAWAL_DATE',    GDate::construct($reserveObj->limitDate)->getDate(GDate::MASK_DATE_USER));

		$mail = new GMail();
		$mail->setSubject($gf->interpret( $subject ));
		$mail->setContent($gf->interpret( $content ));
		$mail->setAddress($personMail);

		return $mail->send();
	}


    /**
     * Envia um email para o administrador com o resultado dos comunicados enviados para os alunos.
     *
     * @param array $result
     */
    public function sendMailToAdminResultOfReserveAnswered($result, $libraryUnitId)
    {
        $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');

        $mountNewResult = array();
        
        if ( count($result) > 0 )
        {
            foreach ($result as $reserveId => $content)
            {
                $boo = $content[2];
                $content[2] = GUtil::getYesNo($content[2]);
                $mountNewResult[$boo][$reserveId] = $content;
            }
    
            $columns = array
            (
                _M("Código da reserva",        $this->module),
                _M("Pessoa",            $this->module),
                _M("Foi comunicada",  $this->module),
                _M("Mensagem",    $this->module),
                _M("Número do exemplar",       $this->module),
                _M("E-mail pessoal",       $this->module),
                _M("Título",             $this->module),
            );
    
            $mailContent = $this->mountTable(_M("E-mails não enviados"),            $columns, $mountNewResult[DB_FALSE], _M("Todos os e-mails foram enviados com sucesso!", $this->module), true);
            $mailContent.= $this->mountTable(_M("E-mails enviados com sucesso"),  $columns, $mountNewResult[DB_TRUE],  _M("Todos os e-mails falharam!", $this->module), true);
        }
        else 
        {
            $mailContent = _M('Não há comunicações de reserva.', $this->module);           
        }
        
        $to      = $this->getAdminEmailTo($libraryUnitId, 'EMAIL_ADMIN_RESERVE');
        
        $subject = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_RESERVE_ANSWERED_ADMIN_RESULT_SUBJECT');
        $content = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_RESERVE_ANSWERED_ADMIN_RESULT_CONTENT');

        $gf   = new GFunction();
        $gf->setVariable('$LN',         "\n");
        $content = $gf->interpret($content);
        $content = str_replace('$CONTENT', $mailContent, $content);
        $content = str_replace('<td >', '<td>', $content);

        $mail = new GMail();
        $mail->setSubject($subject);
        $mail->setContent($content);
        $mail->setAddress($to);
        return $mail->send();

    }


	/**
	 * Envia email para o fornecedor agradecendo e/ou solicitando materiais.
	 *
	 * @param integer $interchangeId
	 * @param text $formatedContent
	 */
	public function sendMailToSupplierInterchengeReceipt($interchangeId, $materials, $formatedContent = null)
	{
		$busInterchange             = $this->MIOLO->getBusiness($this->module, 'BusInterchange');
		$busSupplierTypeAndLocation = $this->MIOLO->getBusiness($this->module, 'BusSupplierTypeAndLocation');
		$interchange                = $busInterchange->getInterchange($interchangeId, "object");

		$supplier = $busSupplierTypeAndLocation->getSupplierTypeAndLocationValueForm( $interchange->supplierId, $interchange->type );
		$mailTo   = $supplier->email;
		if (!$mailTo)
		{
			return 1;
		}
		$this->mailTo = $mailTo;

		// AS LINHAS ABAIXO ESTAO COMENTADOS POR OS ITENS GRAVAM O CONTROL NUMBER, COM ESTE FICA RUIM BUSCAR A LIBRARY_UNIT DO MATERIAL.. QUE PODE TER VARIAS... O IDEAS SERIA GRAVAR O ITEMNUMBER
		//$item = $busInterchange->getInterchangeItem($interchangeId);
		//$content = !is_null($formatedContent) ? $formatedContent: $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'INTERCHANGE_MAIL_RECEIPT_CONTENT');
		//$subject = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'INTERCHANGE_MAIL_RECEIPT_SUBJECT');

		$content = !is_null($formatedContent) ? $formatedContent: INTERCHANGE_MAIL_RECEIPT_CONTENT;
		$subject = INTERCHANGE_MAIL_RECEIPT_SUBJECT;

		//Obtem o conteudo do email
		$gf = new GFunction();
		$gf->setVariable('$CONTACT_NAME',   $supplier->contact);
		$gf->setVariable('$MATERIALS',      $materials);

		//Envia e-mail
		$mail = new GMail();
		$mail->setAddress($mailTo);
		$mail->setSubject($subject);
		$mail->setContent($gf->interpret($content));

		if (!$mail->send())
		{
			return 2;
		}

		// sucess
		return 0;
	}


	/**
	 * Encaminha um email para o solicitante informando sobre o término da requisição
	 *
	 * @param object $requestObject
	 */
    public function informaSolicitanteTerminoRequisicao($person, $subject, $content)
    {
        //Envia e-mail
        $mail = new GMail();
        $mail->setAddress($person->email);
        $mail->setSubject($subject);
        $mail->setContent($content);
        return $mail->send();
    }


	/**
	 * Este metodo informa o usuario que sua reserva foi cancelada.
	 *
	 * @param integer $reserveId
         * @param string $mailSubject Assunto caso o operador queira mandar uma mensagem personalizada. 
         * @param string $mailContent Conteudo caso operador queira mandar uma mensagem personalizada.
	 * @return boolean
	 */
	public function sendMailToUserInformingReserveCancel($reserveId, $mailSubject = null, $mailContent = null)
	{
		$busPerson  = $this->MIOLO->getBusiness($this->module, 'BusPerson');
		$busReserve = $this->MIOLO->getBusiness($this->module, 'BusReserve');

		$reserve = $busReserve->getReserves(null, null, $reserveId);

		if(!$reserve)
		{
			return false;
		}

		$reserve    = $reserve[0];
		$person     = $busPerson->getBasicPersonInformations($reserve->personId);

		if(!strlen($person->email))
		{
			return false;
		}

		$busMaterial    = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
		$busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
                //Verifica se vai utilizar conteudo/assunto personalizado ou padrao vindo da preferencia.
		$subject        = (is_null($mailSubject)) ? $this->busLibraryUnitConfig->getValueLibraryUnitConfig($reserve->libraryUnitId, 'EMAIL_CANCEL_RESERVE_COMUNICA_SOLICITANTE_SUBJECT'):$mailSubject;
		$content        = (is_null($mailContent)) ? $this->busLibraryUnitConfig->getValueLibraryUnitConfig($reserve->libraryUnitId, 'EMAIL_CANCEL_RESERVE_COMUNICA_SOLICITANTE_CONTENT'): $mailContent;

		//Obtem o conteudo do email
		$gf = new GFunction();
		$gf->setVariable('$USER_NAME',                  $person->name);
		$gf->setVariable('$MATERIAL_TITLE',             $busMaterial->getMaterialTitleByItemNumber($reserve->itemNumber));
		$gf->setVariable('$ITEM_NUMBER',                $reserve->itemNumber);
		$gf->setVariable('$LIBRARY_UNIT_DESCRIPTION',   $busLibraryUnit->getLibraryUnit($reserve->libraryUnitId)->libraryName);
		$content = $gf->interpret($content);

		//Envia e-mail
		$mail = new GMail();
		$mail->setAddress($person->email);
		$mail->setSubject($subject);
		$mail->setContent($content);
		return $mail->send();
	}


	/**
	 * Este metodo cria uma tabela simples para enviar por email.
	 *
	 * @param unknown_type $firstLineText
	 * @param unknown_type $columns
	 * @param unknown_type $content
	 * @param unknown_type $emptyMsg
	 * @return unknown
	 */
    public function mountTable($firstLineText, $columns, $content, $emptyMsg = "", $displayTotalLines = false)
    {
        $line = $colummn = 0;

        $tableRaw = new MSimpleTable(null, "cellspacing=0 cellpadding=2 border=1");

        if(!is_null($firstLineText))
        {
            $tableRaw->setCell($line++, 0, $firstLineText, 'colspan="100" align="center"');
        }

        if(!is_array($content) || !count($content))
        {
            $tableRaw->setCell($line++, 0, $emptyMsg, 'colspan="100" align="center"');
        }
        else
        {
            foreach ($columns as $text)
            {
                $tableRaw->setCell($line, $colummn++, $text, 'align="center"');
            }

            foreach ($content as $celContent)
            {
                $line++;
                foreach ($celContent as $index => $txt)
                {
                    $txt = strlen($txt) ? $txt : ' - ';
                    $tableRaw->setCell($line, $index, $txt);
                }
            }

            if($displayTotalLines)
            {
                $tableRaw->setCell(++$line, 0, _M("Total") . ": ". count($content) ."  ", "colspan=100 align=right");
            }
        }

        return str_replace(array("\n", "\t", "\r"), "", $tableRaw->generate());
    }


	/**
	 * Esta função retorna o email do administrador para cada tipo de operação.
	 *
	 * @param id da biblioteca $libraryUnitid
	 * @param operation $type
	 * @return email string
	 */
	private function getAdminEmailTo($libraryUnitId, $type = null)
	{
		if(!is_null($type))
		{
			// BUSCA EMAIL ESPECIFICO DA UNIDADE
			$to = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, $type);

			if(strlen($to))
			{
				return $to;
			}
		}

		// RETORNA EMAIL PADRAO
		return $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_ADMIN');
	}
	
	
    /**
     * Envia e-mail para usuário informando sobre devolução
     * @param (array) $loan
     * @param varchar $subject
     * @param varchar $content
     */
    public function sendMailToUserCommunicateReturn($loan, $subject, $content)
    {
        $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $libraryUnitDescription = $busLibraryUnit->getLibraryName($loan->libraryUnitId);

        $tags = array('$USER_NAME'                => $loan->personName,
                      '$MATERIAL_TITLE'           => $loan->title,
                      '$ITEM_NUMBER'              => $loan->itemNumber,
                      '$RETURN_FORECAST_DATE'     => GDate::construct($loan->returnForecastDate)->getDate(GDate::MASK_DATE_USER),
                      '$LIBRARY_UNIT_DESCRIPTION' => $libraryUnitDescription,
                      '$LN'                       => EMAIL_LINE_BREAK);

        $mail = new GMail();
        $mail->setSubject($subject);
        $mail->setContent(strtr($content, $tags));
        $mail->setAddress($loan->personEmail);
        return $mail->send();
    }

    
    /**
     * Envia um email para o administrador com o resultado dos comunicados de devolução enviados para os alunos.
     *
     * @param array $result
     * @param code of library
     */
    public function sendMailToAdminResultOfCommunicateReturn($result, $libraryUnitId)
    {
        $mountNewResult = array();
        if ( count($result) )
        {
            foreach ($result as $reserveId => $content)
            {
                $boo = $content[2];
                $content[2] = GUtil::getYesNo($content[2]);
                $mountNewResult[$boo][$reserveId] = $content;
            }
    
            $columns = array
            (
                _M("Código do empréstimo",        $this->module),
                _M("Pessoa",            $this->module),
                _M("Foi comunicada",  $this->module),
                _M("Mensagem",    $this->module),
                _M("Número do exemplar",       $this->module),
                _M("E-mail pessoal",       $this->module),
                _M("Título",             $this->module),
            );
            
            $mailContent = $this->mountTable(_M("E-mails não enviados"),            $columns, $mountNewResult[DB_FALSE], _M("Todos os e-mails foram enviados com sucesso!", $this->module), true);
            $mailContent.= $this->mountTable(_M("E-mails enviados com sucesso"),  $columns, $mountNewResult[DB_TRUE],  _M("Todos os e-mails falharam!", $this->module), true);
        }
        else 
        {
            $mailContent = _M('Não há devoluções a serem comunicadas.', $this->module);
        }
        
        $to      = $this->getAdminEmailTo($libraryUnitId, 'EMAIL_ADMIN_DEVOLUTION');
        $subject = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_ADMIN_DEVOLUTION_RESULT_SUBJECT');
        $content = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_ADMIN_DEVOLUTION_RESULT_CONTENT');

        $gf   = new GFunction();
        $gf->setVariable('$LN',         "\n");
        $content = $gf->interpret($content);
        $content = str_replace('$CONTENT', $mailContent, $content);
        $content = $this->fixHtmlTable($content); //Tratamento hardcode para nao zoar html no leitor de e-mail.
        
        $mail = new GMail();
        $mail->setSubject($subject);
        $mail->setContent($content);
        $mail->setAddress($to);

        return $mail->send();
    }
    
    
    /**
     * Envia e-mail para usuário informando sobre empréstimo atrasado
     * @param (array) $loan
     * @param varchar $subject
     * @param varchar $content
     */
    public function sendMailToUserCommunicateDelayedLoan($loan, $subject, $content)
    {
        $busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $libraryUnitDescription = $busLibraryUnit->getLibraryName($loan->libraryUnitId);
        
        $tags = array('$USER_NAME'                => $loan->personName,
                      '$ITEM_NUMBER'              => $loan->itemNumber,
                      '$MATERIAL_TITLE'           => $loan->title,
                      '$RETURN_DATE'              => GDate::construct($loan->returnForecastDate)->getDate(GDate::MASK_DATE_USER),
                      '$LIBRARY_UNIT_DESCRIPTION' => $libraryUnitDescription,
                      '$LN'                       => EMAIL_LINE_BREAK);
        
        $mail = new GMail();  
        $mail->setSubject($subject);
        $mail->setContent(strtr($content, $tags));
        $mail->setAddress($loan->personEmail);
        
        return $mail->send();
    }
    
    
    /**
     * Envia um email para o administrador com o resultado dos comunicados de empréstimos atrasados enviados para os alunos.
     *
     * @param array $result
     * @param code of library
     */
    public function sendMailToAdminResultOfCommunicateDelayedLoan($result, $libraryUnitId)
    {
        $mountNewResult = array();
        if ( count($result) > 0 )
        {
            foreach ($result as $reserveId => $content)
            {
                $boo = $content[2];
                $content[2] = GUtil::getYesNo($content[2]);
                $mountNewResult[$boo][$reserveId] = $content;
            }
    
            $columns = array
            (
                _M("Código do empréstimo",        $this->module),
                _M("Pessoa",            $this->module),
                _M("Foi comunicada",  $this->module),
                _M("Mensagem",    $this->module),
                _M("Número do exemplar",       $this->module),
                _M("E-mail pessoal",       $this->module),
                _M("Título",             $this->module),
            );
            
            $mailContent = $this->mountTable(_M("E-mails não enviados"),            $columns, $mountNewResult[DB_FALSE], _M("Todos os e-mails foram enviados com sucesso!", $this->module), true);
            $mailContent.= $this->mountTable(_M("E-mails enviados com sucesso"),  $columns, $mountNewResult[DB_TRUE],  _M("Todos os e-mails falharam!", $this->module), true);
        }
        else 
        {
            $mailContent = _M('Não há notificações de empréstimos atrasados.', $this->module); 
        }

        $to      = $this->getAdminEmailTo($libraryUnitId, 'EMAIL_ADMIN_DELAYED_LOAN');
        $subject = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_ADMIN_DELAYED_LOAN_RESULT_SUBJECT');
        $content = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_ADMIN_DELAYED_LOAN_RESULT_CONTENT');
        $content = $this->fixHtmlTable($content); //Tratamento hardcode para nao zoar html no leitor de e-mail.
        
        $gf   = new GFunction();
        $gf->setVariable('$LN',         "\n");
        $content = $gf->interpret($content);
        $content = str_replace('$CONTENT', $mailContent, $content);

        $mail = new GMail();
        $mail->setSubject($subject);
        $mail->setContent($content);
        $mail->setAddress($to);

        return $mail->send();
    }
    
    
    /**
     * Envia um e-mail para o usuário informando as novas aquisições
     * @param (array) $personInformations
     * @param varchar $subject
     * @param varchar $content
     */
    public function sendMailToUserNotifyAcquisition($personInformations, $subject, $content)
    {
         $mail = new GMail();
         $mail->setSubject($subject);
         $mail->setContent($content);
         $mail->setAddress($personInformations->email);
         return $mail->send();
    }
    
    
    /**
     * Envia um email para o administrador com o resultado das notificações de aquisições
     *
     * @param array $result
     * @param code of library
     */
    public function sendMailToAdminResultOfNotifyAcquisition($result, $libraryUnitId)
    {
        $busPreference = $this->MIOLO->getBusiness($this->module, 'BusPreference');
        $mountNewResult = array();

        if ( count($result) > 0 )
        {
            foreach ($result as $reserveId => $content)
            {
                $boo = $content[2];
                $content[2] = GUtil::getYesNo($content[2]);
                $mountNewResult[$boo][$reserveId] = $content;
            }
    
            $columns = array
            (
                _M("Código da pessoa",       $this->module),
                _M("Nome",              $this->module),
                _M("Foi comunicada",  $this->module),
                _M("Mensagem",    $this->module),
                _M("E-mail pessoal",       $this->module),
            );
            
            $mailContent = $this->mountTable(_M("E-mails não enviados"),            $columns, $mountNewResult[DB_FALSE], _M("Todos os e-mails foram enviados com sucesso!", $this->module), true);
            $mailContent.= $this->mountTable(_M("E-mails enviados com sucesso"),  $columns, $mountNewResult[DB_TRUE],  _M("Todos os e-mails falharam!", $this->module), true);
        }
        else 
        {
            $mailContent = _M('Não há notificações de aquisição.', $this->module);
        }
        $to = $this->getAdminEmailTo($libraryUnitId, 'EMAIL_ADMIN_NOTIFY_ACQUISITION');
        $content = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_ADMIN_NOTIFY_ACQUISITION_RESULT_CONTENT');
        $subject = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_ADMIN_NOTIFY_ACQUISITION_RESULT_SUBJECT');

        $gf   = new GFunction();
        $gf->setVariable('$LN',         "\n");
        $content = $gf->interpret($content);
        $content = str_replace('$CONTENT', $mailContent, $content);
        $content = $this->fixHtmlTable($content); //Tratamento hardcode para nao zoar html no leitor de e-mail.
        
        $mail = new GMail();
        $mail->setSubject($subject);
        $mail->setContent($content);
        $mail->setAddress($to);

        return $mail->send();
    }
    
    
    /**
     * Envia um email para o administrador com o resultado das notificações de término de requisição
     *
     * @param array $result
     * @param code of library
     */
    public function sendMailToAdminResultOfNotifyEndRequest($result, $libraryUnitId)
    {
        $busPreference = $this->MIOLO->getBusiness($this->module, 'BusPreference');
        $mountNewResult = array();

        if ( count($result) > 0 )
        {
            foreach ($result as $reserveId => $content)
            {
                $boo = $content[2];
                $content[2] = GUtil::getYesNo($content[2]);
                $mountNewResult[$boo][$reserveId] = $content;
            }
    
            $columns = array
            (
                _M("Código requisição",     $this->module),
                _M("Pessoa",           $this->module),
                _M("Foi comunicada", $this->module),
                _M("Mensagem",   $this->module),
                _M("E-mail pessoal",      $this->module),
                _M("Encerramento",          $this->module)
            );
            
            $mailContent = $this->mountTable(_M("E-mails não enviados"),            $columns, $mountNewResult[DB_FALSE], _M("Todos os e-mails foram enviados com sucesso!", $this->module), true);
            $mailContent.= $this->mountTable(_M("E-mails enviados com sucesso"),  $columns, $mountNewResult[DB_TRUE],  _M("Todos os e-mails falharam!", $this->module), true);
        }
        else 
        {
            $mailContent = _M('Não há notificações de aquisição.', $this->module);
        }
        $to = $this->getAdminEmailTo($libraryUnitId, 'EMAIL_ADMIN_NOTIFY_END_REQUEST');
        $content = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_ADMIN_NOTIFY_END_REQUEST_RESULT_CONTENT');
        $subject = $this->busLibraryUnitConfig->getValueLibraryUnitConfig($libraryUnitId, 'EMAIL_ADMIN_NOTIFY_END_REQUEST_RESULT_SUBJECT');

        $gf   = new GFunction();
        $gf->setVariable('$LN',         "\n");
        $content = $gf->interpret($content);
        $content = str_replace('$CONTENT', $mailContent, $content);
        $content = $this->fixHtmlTable($content); //Tratamento hardcode para nao zoar html no leitor de e-mail.
        
        $mail = new GMail();
        $mail->setSubject($subject);
        $mail->setContent($content);
        $mail->setAddress($to);

        return $mail->send();
    }
    

    /**
     * Este metodo informa personalizadamente ao usuario que sua reserva foi cancelada.
     *
     * @param integer $reserveId
     * @param string $mailSubject Assunto para o operador mandar uma mensagem personalizada. 
     * @param string $mailContent Conteudo para o operador mandar uma mensagem personalizada.
     * @return boolean
     */
    public function sendPersonalMailToUserInformingReserveCancel($reserveId, $mailSubject, $mailContent)
    {
        return $this->sendMailToUserInformingReserveCancel($reserveId, $mailSubject, $mailContent);
    }        

/**
 * Metodo criado para corrigir problemas como o relatado no ticket #18343
 * A geracao de tabelas do miolo apresenta problemas com espaço, novas linhas dentro do html
 * que acabam gerando problemas nos leitores de e-mail que respeitam duramente
 * o padrao html da w3c.
 * 
 * @param type $content conteudo gerado nos envios de e-mail.
 * @return string (html com correçoes que evitam de zoar a tabela).
 */
    public function fixHtmlTable($content)
    {
        //Tratamento hardcode para evitar de dar problema na leitura de html efetuada pelo leitor de e-mail.
        $content = str_replace(array('<td >', '< td >','< td>'), '<td>', $content); 
        $content = str_replace(array('</td >', '</ td >','</ td>'), '</td>', $content); 
        $content = str_replace("\n", '', $content); 
        $content = str_replace('< /', '</', $content); 
        $content = str_replace('< ', '<', $content); 
        $content = str_replace(' >', '>', $content); 
        
        return $content;
    }

}

?>
