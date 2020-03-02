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
 * Class created on 07/08/2008
 *
 * */
$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
$MIOLO->getClass('gnuteca3', 'GSipCirculation');

class FrmPersonSearch extends GForm
{

    public $MIOLO;
    public $module;
    public $busBond;
    public $busPenalty;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busPenalty = $this->MIOLO->getBusiness($this->module, 'BusPenalty');
        $this->setAllFunctions('Person', 'personId', 'personId');
        $this->setGrid('GrdPerson', 'personNameS');

        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MIntegerField('personIdS', '', _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('personNameS', '', _M('Nome', $this->module), FIELD_DESCRIPTION_SIZE);

        $cityIdLabel = new MLabel(_M('Código da cidade', $this->module) . ':');
        $cityId = new GLookupTextField('cityIdS', '', '', FIELD_LOOKUPFIELD_SIZE);
        $cityId->setReadOnly($read);
        $cityId->setContext($this->module, $this->module, 'City', 'filler', 'cityIdS,cityNameS', '', true);
        $cityId->baseModule = $this->module;
        $cityName = new MTextField('cityNameS', $cityName, NULL, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $cityName->setReadOnly(FALSE);
        
        $fields[] = new GContainer('cityIdContainerPerson', array($cityIdLabel, $cityId, $cityName));        
        $fields[] = new MTextField('emailS', '', _M('E-mail', $this->module), FIELD_DESCRIPTION_SIZE);

        $login = new MTextField('loginS', '', _M('Usuário/Login', $this->module), FIELD_DESCRIPTION_SIZE, null, null, $read);

        if (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN_BASE)
        {
            $fields[] = $login;
            $bases = BusinessGnuteca3BusAuthenticate::listMultipleLdap();
            $fields[] = new GSelection('baseLdapS', '', _M('Base', $this->module), $bases);
        }
        else if (MY_LIBRARY_AUTHENTICATE_TYPE == BusinessGnuteca3BusAuthenticate::TYPE_AUTHENTICATE_LOGIN || GSipCirculation::usingSmartReader())
        {
            $fields[] = $login;
        }

        $fields[] = new GSelection('activeBondS', '', _M('Vinculos ativos', $this->module), $this->busBond->listBond());
        //campo para selecionar o grupo
        $personGroups = BusinessGnuteca3BusDomain::listForSelect('PERSON_GROUP');

        if ($personGroups != null) //Se tiver a preferencia PERSON_GROUP
        {
            $fields[] = new GSelection('personGroupS', null, _M('Grupo', $this->module), $personGroups); //Mostra campo de person Group
        }

        //domínios do sexo
        $fields[] = new GSelectioN('sexS', null, _M('Sexo', $this->module), BusinessGnuteca3BusDomain::listForSelect('SEX'));

        $this->setFields($fields);

        $this->_toolBar->addRelation(_M("Envio de email em lote", 'gnuteca3'), GUtil::getImageTheme('email-16x16.png'), "javascript:" . Gutil::getAjax('sendMail'), 'gtcPersonSendMultipleEmail');
        $this->_toolBar->addRelation(_M('Grupo de usários', 'gnuteca3'), GUtil::getImageTheme('userGroup-16x16.png'), 'javascript:' . GUtil::getActionLink('main:configuration:userGroup'), 'gtcUserGroup');
        $this->_toolBar->addRelation(_M('Unir pessoas', 'gnuteca3'), GUtil::getImageTheme('userGroup-16x16.png'), 'javascript:' . GUtil::getAjax('unionPersons'), 'gtcUnionPerson');
    }

    public function sendMail()
    {
        $controls[] = $bond = new GSelection('bond', '', _M('Vínculo', 'gnuteca3'), $this->busBond->listBond(), true, '', 5, true);
        $bond->setMultiple(true);
        $controls[] = new MTextField('emailSubject', '', _M('Título', 'gnuteca3'), 80);
        $controls[] = new MCheckBox('individualSend', DB_TRUE, _M('Enviar e-mails separadamente', 'gnuteca3'), false);

        $fields[] = new MVContainer('', $controls, MControl::FORM_MODE_SHOW_SIDE);
        $fields[] = new MDiv('', '<br/><br/>'); //caso não colocar isso quebra o layout

        $fields[] = $editor = new MEditor('emailContent', '');

        $editor->disableElementsPath();

        $buttons[] = new MButton('btnSendMail', _M('Enviar', 'gnuteca3'), GUtil::getAjax('sendMailQuestion') . " meditor.remove('emailContent');", GUtil::getImageTheme('email-16x16.png'));
        $buttons[] = GForm::getCloseButton("meditor.remove('emailContent');");
        $fields[] = new MDiv('', $buttons);

        GForm::injectContent($fields, false, _M('Envio de email em lote', 'gnuteca3'));
    }

    public function sendMailQuestion($args)
    {
        if (!$args->bond || !$args->emailSubject || !$args->emailContent)
        {
            throw new Exception(_M('É necessário preencher todos os campos!', 'gnuteca3'));
        }

        $this->busBond->linkIdS = $args->bond;
        $this->busBond->allActive = true;
        $result = $this->busBond->searchBond(true);

        $total = $result ? count($result) : 0;

        if (!$total)
        {
            throw new Exception(_M('Sem pessoas ativas para envio de email para o grupo "@1"', 'gnuteca3', $args->bond[0]));
        }

        $params['bond'] = implode(',', $args->bond);
        $params['emailSubject'] = base64_encode($args->emailSubject);
        $params['emailContent'] = base64_encode($args->emailContent);
        $params['individualSend'] = $args->individualSend;

        GForm::question(_M('Esta ação enviará email para @1 pessoas. Confirma envio?', 'gnuteca3', $total), 'javascript:' . GUtil::getAjax('sendMailFinalize', $params));
    }

    public function sendMailFinalize($args)
    {
        $args = GUtil::decodeJsArgs($args);
        $args->bond = explode(',', $args->bond);
        $args->emailSubject = base64_decode($args->emailSubject);
        $args->emailContent = base64_decode($args->emailContent);
        $this->busBond->linkIdS = $args->bond;
        $this->busBond->allActive = true;
        $result = $this->busBond->searchBond(true);

        if (is_array($result))
        {
            if ($args->individualSend) //Se por para enviar e-mails separadamente
            {
                foreach ($result as $line => $person) //Para cada pessoa com vinculo ativo
                {
                    try
                    {
                        $mail = new GMail();
                        $mail->setSubject($args->emailSubject);
                        $mail->setContent($args->emailContent);
                        $mail->setIsHtml(true);
                        $mail->addAddress($person->email, $person->name); //Adiciona pessoa como destinatário oculto.

                        $ok = $mail->send(); //Envia o e-mail

                        if ($ok) //Se o e-mail foi enviado
                        {
                            $sucess[] = $ok; //adiciona aos e-mails de envio bem sucedido
                        }
                        else //Se o e-mail não foi enviado
                        {
                             $error[$person->email] = $mail->ErrorInfo; //adiciona aos e-mails de envio mal sucedidos
                        }
                    }
                    catch (Exception $e)
                    {
                        $error[$person->email] = $mail->ErrorInfo; //adiciona aos e-mails de envio mal sucedidos
                    }

                    if (!is_null($error[$person->email]))
                    {
                        $tbData[] = array($person->email, $error[$person->email]);
                    }

                    if (defined('EMAIL_SERVER_DELAY'))
                    {
                        sleep(EMAIL_SERVER_DELAY);
                    }
                }

                if ($sucess)
                {
                    $msgSuccess = _M('Foram enviados @1 emails com sucesso.', 'gnuteca3', count($sucess)) . '<br/>';
                }

                if ($error)
                {
                    $msgError = _M('Abaixo a listagem dos emails que tiveram problema de envio :', 'gnuteca3') . '<br/>';
                }

                $tbColumns = array(
                    _M('Email', $this->module),
                    _M('Erro', $this->module)
                );



                if (!is_null($tbData)) //Se houveram erros
                {
                    $fields[] = new MDiv('mailStatusSuccess', $msgSuccess . $msgError);
                    $fields[] = $tb = new MTableRaw('', $tbData, $tbColumns);
                    $tb->zebra = TRUE;
                    $this->injectContent($fields, TRUE, _M('Resultado do envio de e-mails', $this->module));
                }
                else //Se não houveram erros
                {
                    $this->information($msgSuccess);
                }
            }
            else //Se for para enviar os e-mails em lote
            {
                $mail = new GMail();
                $mail->setSubject($args->emailSubject);
                $mail->setContent($args->emailContent);
                $mail->setIsHtml(true);

                foreach ($result as $line => $person) //Para cada pessoa com vinculo ativo
                {
                    $mail->AddBCC($person->email, $person->name); //Adiciona pessoa como destinatário oculto.
                }

                if (!$mail->send()) //Verifica se o lote inteiro foi enviado.
                {
                    throw new Exception ( _M('Impossível enviar email em lote! Motivo:','gnuteca3') . ' ' .$mail->ErrorInfo );
                }

                $this->information(_M('Email em lote enviado com sucesso!', 'gnuteca3'));
            }
        }
        else
        {
            $this->information(_M('Não existem usuários vinculados aos grupos : @1', 'gnuteca3', implode(',', $this->busBond->linkIdS)));
        }
    }

    /**
     *  Mostra vínculos da pessoa
     */
    public function showBond()
    {
        $data = new StdClass();
        $data->personIdS = MIOLO::_REQUEST('personId');
        $this->busBond->setData($data);
        $search = $this->busBond->searchBond(TRUE);

        if ($search)
        {
            $date = new GDate();

            for ($i = 0; $i < count($search); $i++)
            {
                $date->setDate($search[$i]->dateValidate);
                $tbData[] = array(
                    $search[$i]->linkIdName,
                    strlen($search[$i]->dateValidate) ? $date->getDate(GDate::MASK_DATE_USER) : ''
                );
            }

            $fields[] = new MLabel(_M('Pessoa', $this->module) . ': ' . $data->personIdS);

            $tbColumns = array(
                _M('Grupo de usuário', $this->module),
                _M('Data de validade', $this->module)
            );

            $tb = new MTableRaw('', $tbData, $tbColumns);
            $tb->zebra = TRUE;
        }
        else
        {
            $tb = new MLabel(_M('Nenhum registro encontrado.', $this->module));
        }

        $this->injectContent($tb, true, _M('Vínculo', $this->module) . ' - ' . MIOLO::_REQUEST('personId'));
    }

    /**
     * Mostra penalidades da pessoa
     */
    public function showPenalty()
    {
        $data = new StdClass();
        $data->personIdS = MIOLO::_REQUEST('personId');
        $this->busPenalty->setData($data);
        $search = $this->busPenalty->searchPenalty(TRUE);

        if ($search)
        {
            $penaltyDate = new GDate();
            $penaltyEndDate = new GDate();

            foreach ($search as $v)
            {

                $penaltyDate->setDate($v->penaltyDate);
                $penaltyEndDate->setDate($v->penaltyEndDate);

                $tbData[] = array($v->observation,
                    $v->internalObservation,
                    $penaltyDate->getDate(GDate::MASK_DATE_USER),
                    $penaltyEndDate->getDate(GDate::MASK_DATE_USER),
                    $v->operator,
                    $v->libraryName);
            }

            $fields[] = new MLabel(_M('Pessoa', $this->module) . ': ' . $data->personIdS);

            $tbColumns = array(
                _M('Observação', $this->module),
                _M('Observação interna', $this->module),
                _M('Data da penalidade', $this->module),
                _M('Data final de penalidade', $this->module),
                _M('Operador', $this->module),
                _M('Unidade de biblioteca', $this->module),
            );
            $tb = new MTableRaw('', $tbData, $tbColumns);
            $tb->zebra = TRUE;
        }
        else
        {
            $tb = new MLabel(_M('Nenhum registro encontrado.', $this->module));
        }

        $this->injectContent($tb, true, _M('Penalidade', $this->module) . ' - ' . MIOLO::_REQUEST('personId'));
    }

    /**
     * Visualiza os arquivos possíveis e permite o download dos não conhecidos.
     *
     *
     * @param string $relative
     *
     */
    public function showPhoto($personId)
    {
        if (!$personId)
        {
            throw new Exception('É necessário informar um código de pessoa.', 'gnuteca3');
        }

        $this->injectContent(GUtil::getPersonPhoto($personId), true);
    }

    /**
     * Obtem as pessoas selecionadas na grid
     * @param type $args
     * @return Array $persons
     * @throws Exception 
     */
    public function getSelectePersons($args)
    {
        $persons = $args->selectGrdPerson;
        $persons = array_values($persons);

        if (count($persons) != 2)
        {
            throw new Exception(_M("É necessário selecionar duas pessoas.", 'gnuteca3'));
        }

        foreach ($persons as $line => $id)
        {
            $tmpPerson = explode('=', $persons[$line]);
            $persons[$line] = array($tmpPerson[1]);
            $myPerson = $this->business->getBasicPersonInformations($persons[$line][0]);
            $persons[$line][1] = $myPerson->name;
        }

        return $persons;
    }

    /**
     * Constrói a poup-up onde será selecionada a pessoa que ira permanecer no sistema 
     * @param type $args 
     * @return void
     */
    public function unionPersons($args)
    {
        $persons = $this->getSelectePersons($args);

        $fields = array();

        $fields[] = new MDiv('divDescription', 'Você esta prestes a unir as informações de duas pessoas selecionadas, onde a pessoa escolhida abaixo permanecerá no sistema e a outra será apagada. <em><b>Lembre-se esta operação é irreverssível!</b></em>', 'reportDescription');

        $fields[] = new GContainer('', array(new GSelection('stayPerson', null, _M('Selecione a pessoa que permanecerá no sistema: ', $this->module), $persons)));

        $buttons[] = new MButton('btnYes', _M('Confirmar'), GUtil::getAjax('confirmUnionPersons'), GUtil::getImageTheme('accept-16x16.png'));
        $buttons[] = $this->getCloseButton();

        $fields[] = new MDiv('buttons2', $buttons);

        $this->injectContent($fields);
    }

    /**
     * Obtém a confiemação da união das pessoas
     * @param type $args
     * @return void
     */
    public function confirmUnionPersons($args)
    {
        $persons = $this->getSelectePersons($args);
        $stayPerson = $args->stayPerson;

        if (!$stayPerson)
        {
            throw new Exception('Você deve selecionar a pessoa que permanecerá no sistema.');
        }

        foreach ($persons as $line => $person)
        {
            if ($person[0] != $stayPerson)
            {
                $outPerson = $person[0];
            }
        }

        $ok = $this->business->personUnion($stayPerson, $outPerson);
        $this->information('Pessoas unidas com sucesso!', Gutil::getAjax('searchFunction') . ' ' . GUtil::getCloseAction());
    }

}

?>