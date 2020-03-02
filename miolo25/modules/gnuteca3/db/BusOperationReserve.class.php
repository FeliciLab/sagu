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
 * Class to manage to operation reserve of gnuteca.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 28/10/2008
 *
 * */
class BusinessGnuteca3BusOperationReserve extends GMessages
{
    public $MIOLO;
    public $module;
    public $busPerson;
    public $busBond;
    public $busPenalty;
    public $busLibraryUnit;
    public $busReserve;
    public $busReserveComposition;
    public $busReserveStatus;
    public $busReserveType;
    public $busAuthenticate;
    public $busExemplaryControl;
    public $busPolicy;
    public $busSearchFormat;
    public $busGeneralPolicy;
    public $busExemplaryStatus;
    public $busRight;
    public $busHoliday;
    public $busLibraryUnitConfig;
    public $busLoan;
    public $busMaterial;
    public $person;
    public $reserveType;
    public $session;
    protected $exemplary;
    protected $gridData;
    public $mail;

    /**
     * Class construct
     */
    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busPerson = $this->MIOLO->getBusiness($this->module, 'BusPerson');
        $this->busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busPenalty = $this->MIOLO->getBusiness($this->module, 'BusPenalty');
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busReserve = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $this->busReserveStatus = $this->MIOLO->getBusiness($this->module, 'BusReserveStatus');
        $this->busReserveComposition = $this->MIOLO->getBusiness($this->module, 'BusReserveComposition');
        $this->busReserveType = $this->MIOLO->getBusiness($this->module, 'BusReserveType');
        $this->busAuthenticate = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busPolicy = $this->MIOLO->getBusiness($this->module, 'BusPolicy');
        $this->busGeneralPolicy = $this->MIOLO->getBusiness($this->module, 'BusGeneralPolicy');
        $this->busExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busRight = $this->MIOLO->getBusiness($this->module, 'BusRight');
        $this->busHoliday = $this->MIOLO->getBusiness($this->module, 'BusHoliday');
        $this->busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');
        $this->busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busRulesForMaterialMovement = $this->MIOLO->getBusiness($this->module, 'BusRulesForMaterialMovement');

        $this->MIOLO->getClass('gnuteca3', 'GSendMail');

        $this->sendMail = new GSendMail();
        $this->session = new MSession('operationReserve');
    }

    public function setReserveType($reserveTypeId)
    {
        $this->session->set('reserveType', $this->busReserveType->getReserveType($reserveTypeId));
    }

    public function getReserveType()
    {
        return $this->session->get('reserveType');
    }

    public function blockReserveProcess($block)
    {
        $this->session->set('blockReserveProcess', $block);
    }

    public function getBlockReserveProcess()
    {
        return $this->session->get('blockReserveProcess');
    }

    public function unsetBlockReserveProcess()
    {
        $this->session->unregister('blockReserveProcess');
    }

    /**
     * Define the Library Unit of this operation (and verify if exists)
     *
     * @param integer $libraryUnitId the id of the library unit
     * @return boolean if is seted or not
     */
    public function setLibraryUnit($libraryUnitId)
    {
        //permite passar o objeto de outra operação, evitando assim, select no banco, que já foram feitos
        if ( !is_object($libraryUnitId) )
        {
            $libraryUnit = $this->busLibraryUnit->getLibraryUnit($libraryUnitId, true);
        }
        else
        {
            $libraryUnit = $libraryUnit;
        }

        if ( $libraryUnit->libraryUnitId )
        {
            $this->libraryUnitId = $libraryUnitId;
            $this->libraryUnit = $libraryUnit;
            $this->session->set('libraryUnit2', $libraryUnit);
            $this->session->set('libraryUnitId2', $libraryUnitId);
            return true;
        }
        else
        {
            $libraryUnit = $libraryUnitId;
	
            if (!$libraryUnit)
	
            {

                $this->addError(_M('Selecione uma unidade válida.', MIOLO::getCurrentModule()));

            }
            return false;
        }
    }

    public function getLibraryUnit($object = FALSE)
    {
        if ( !$object )
        {
            $libraryUnit = $this->libraryUnitId;
            if ( !$libraryUnit )
            {
                $libraryUnit = $this->session->get('libraryUnitId2');
            }
        }
        else
        {
            $libraryUnit = $this->libraryUnit;
            if ( !$libraryUnitId )
            {
                $libraryUnit = $this->session->get('libraryUnit2');
            }
        }
        return $libraryUnit;
    }

    public function setPerson($personId)
    {
        $this->person = $this->busPerson->getPerson($personId, true);

        if ( !$this->person->personId )
        {
            $this->addError(_M('Usuário não existe.', $this->module));
            return false;
        }

        $this->person->link = $this->busBond->getPersonLink($this->person->personId);

        if ( !$this->person->link->linkId )
        {
            $this->addError(_M('Usuário sem vínculo.', $this->module));
            return false;
        }

        $libraryUnitId = $this->getLibraryUnit();

        if ( !$libraryUnitId )
        {
            $this->addError(_M('Selecione uma unidade válida.', $this->module));
            return false;
        }

        $access = $this->busPerson->checkAccessLibraryUnit($personId, $libraryUnitId);
        if ( !$access )
        {

            $this->addError(_M('Usuário não tem acesso a biblioteca @1.', $this->module, $this->getLibraryUnit(TRUE)->libraryName));
            return false;
        }
        else
        {
            $this->person->penalty = $this->busPenalty->getPenaltyOfAssociation($libraryUnitId, $this->person->personId);

            //lista reservas pela unidade selecionada
            $requestedReserves = $this->busReserve->getReservesOfAssociation($this->getLibraryUnit(), $this->person->personId, array( ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_REPORTED, ID_RESERVESTATUS_ANSWERED ), true);

            if ( $requestedReserves )
            {
                $this->person->reserve = $requestedReserves;
            }

            $this->session->set('person', $this->person);

            $policy = $this->busPolicy->getLibraryUnitPolicy($libraryUnitId, $this->person->link->linkId, true, null, $this->person->personId);

            if ( !$policy )
            {
                $this->addError(_M('Política não encontrada.', $this->module), null, 100);
                return true;
            }
            else
            {
                return $policy;
            }
        }
    }

    public function getPerson()
    {
        return $this->session->get('person');
    }

    public function clearPerson()
    {
        return $this->session->unregister('person');
    }

    public function personAuthenticate($password)
    {
        $person = $this->getPerson();
        return $this->busAuthenticate->authenticate($person->personId, $password);
    }
    
    /**
     * Esta função adiciona todos os exemplares de um material a partir do número de um exemplar.
     * Ela descobre o número de controle do exemplar e repassa para a funcao addMaterial, ela por
     * sua vez fará todo trabalho.
     * 
     * @param type $itemNumber Número de tombo do exempĺar que será reservado
     * @param type $filter
     * @param type $confirmAvailable
     * @return boolean 
     */
    public function addMaterialByExemplar($itemNumber, $filter = null, $confirmAvailable = null)
    {
        //Descobre o número de controle do exemplar.
        $controlNumber = $this->busExemplaryControl->getControlNumber($itemNumber);

        //Se não tiver número de controle no exemplar
        if ( !$controlNumber )
        {
            //Não deixa prosseguir
            $this->addError(_M('Exemplar não existe.', $this->module));
            return false;
        }
        //Adiciona todos exemplares do número de controle
        return $this->addMaterial($controlNumber, $filter, $confirmAvailable);
    }

    /**
     * Adiciona todos os itemNumber do material selecionado a listagem,
     * o parametro filter serve justamente para nï¿½o adicionar todos, vocï¿½s pode filtrar
     * por um campo marc (tag), e somente os exemplares que estiver dentro da condiï¿½ï¿½o serï¿½o adicionados
     *
     * Exemplo de uso de filter:
     *
     *  $filter[MARC_EXEMPLARY_VOLUME_TAG] = $volume;
     *
     * Filtra o campo marc de volume (por exemplo 949.v) pelo valor que vier na variï¿½vel volume.
     * Repere que deve existir na variï¿½vel $exemplary (o exemplar em si) os dados da tag.
     *
     * TODO por algum motivo alguém deletou a programação que filtra os itemNumber .... ($filter)
     *
     *
     * @param integer $controlNumber
     * @param array  $filter
     * @param boolean $confirmAvailable define que o usuário já aceitou reservar materiais disponívei
     * @return unknown
     */
    public function addMaterial($controlNumber, $filter = null, $confirmAvailable = null)
    {
        $libraryUnit = $this->getLibraryUnit(true);
        $exemplary = $this->busExemplaryControl->getExemplaryOfMaterial($controlNumber, $libraryUnit->libraryUnitId, true); //o true faz pegar os dados extras

        if ( !$exemplary )
        {
            $this->addError(_M('Nenhum exemplar encontrado para esta obra.', $this->module));
            return false;
        }

        if ( is_array($exemplary) )
        {
            $count = 0; //Conta quantos exemplares foram adicionados com sucesso
            foreach ( $exemplary as $line => $info )
            {
                if ( !$this->getBlockReserveProcess() )
                {
                    //Verifica os exemplares que devem ser ignorados pelos usuários
                    //FIXME isso fere o MVC por favor corrigir
                    if ( GMaterialDetail::checkDisplayExemplary($info->exemplaryStatusId) )
                    {
                        //se tiver exemplares no nível inicial, ou seja disponível
                        if ( $info->exemplaryStatusId == DEFAULT_EXEMPLARY_STATUS_DISPONIVEL )
                        {
                            if ( !$confirmAvailable )
                            {
                                //$this->clearItemsReserve();
                                return 'available_confirm'; //pede pro usuário confirmar, mandando essa mensagem
                            }
                            else
                            {
                                $ok = $this->addItemNumber($info->itemNumber, TRUE);
                            }
                        }
                        else
                        {
                            $ok = $this->addItemNumber($info->itemNumber, TRUE);
                        }
                    }

                    if ( $ok )
                    {
                        $count++;
                    }
                }
                else
                {
                    //Se algum exemplar bloquear a operação, este script limpa toda a reserva
                    $this->clearItemsReserve();
                    $this->clean('2'); //Limpa todas as mensagens de sucesso
                    $count = 0;
                    break;
                }
            }

            if ( $count > 0 )
            {
                return true;
            }
        }

        $this->addError(_M('Nenhum exemplar adicionado.', $this->module), null, 101);
        return false;
    }

    public function addItemNumber($itemNumber, $initialStatusConfirmed = NULL)
    {
        //bloqueia a adição de itens  caso esteja com o processo bloqueado
        if ( $this->getBlockReserveProcess() )
        {
            $this->addError(_M('Processo de reserva bloqueado.', $this->module), null, 105);
            return false;
        }

        $exemplary = $this->busExemplaryControl->getExemplaryControl($itemNumber, true);
        $person = $this->getPerson();
        $libraryUnit = $this->getLibraryUnit(true);
        $reserveType = $this->getReserveType();

        if ( !$exemplary->itemNumber )
        {
            $this->addError(_M('Exemplar não existe.', $this->module), $reserveMSG);
            return false;
        }

        $exemplary->searchData = $this->busSearchFormat->getFormatedString($exemplary->controlNumber, MATERIAL_MOVIMENT_SEARCH_FORMAT_ID, 'search');

        $reserveMSG['itemNumber'] = $itemNumber;
        $reserveMSG['searchData'] = $exemplary->searchData;

        if ( $exemplary->libraryUnitId != $this->getLibraryUnit() )
        {
            $this->addError(_M('O exemplar não pertence a unidade.', $this->module), $reserveMSG);
            return false;
        }

        $exemplary->materialGenderId = $this->busExemplaryControl->getMaterialGender($itemNumber); //really need this??

        $policy = $this->busPolicy->getPolicy($libraryUnit->privilegeGroupId, $person->link->linkId, $exemplary->materialGenderId, true);

        if ( !$policy->privilegeGroupId )
        {
            $this->addError(_M('Políticas não encontradas.', $this->module), $reserveMSG, 102);
            return false;
        }

        if ( $this->person->penalty )
        {
            $right = $this->busRight->hasRight($libraryUnit->libraryUnitId, $person->link->linkId, $exemplary->materialGenderId, ID_OPERATION_LOAN_PENALTY);
            if ( !$right )
            {
                $this->addError(_M("O usuário tem penalidades em aberto. Isso impede a reserva do item <b>$itemNumber</b>.", $this->module), $reserveMSG);
                $this->blockReserveProcess(true);
                return false;
            }
        }

        //Conta quantas reservas solicitadas o usuário tem
        $contReserve = 0;
        $contReserveInitial = 0;

        //percorre reservas já gravadas no banco
        foreach ( $person->reserve as $reserve )
        {
            if ( $reserve->reserveTypeId == ID_RESERVETYPE_LOCAL || $reserve->reserveTypeId == ID_RESERVETYPE_WEB )
            {
                //somente soma se for do genero de material do exemplar
                if ( is_array($reserveComposition = $reserve->reserveComposition) )
                {
                    foreach ( $reserveComposition as $line => $composition )
                    {
                        if ( $composition->materialGenderId == $exemplary->materialGenderId )
                        {
                            $contReserve += 1;
                            break;
                        }
                    }
                }
            }
            else
            {
                if ( is_array($reserveComposition = $reserve->reserveComposition) )
                {
                    foreach ( $reserveComposition as $line => $composition )
                    {
                        if ( $composition->materialGenderId == $exemplary->materialGenderId )
                        {
                            $contReserveInitial += 1;
                            break;
                        }
                    }
                }
            }
        }

        //Conta quantas reservas solicitadas o usuário tem
        $totalContReserve = 0;
        $totalContReserveInitial = 0;

        //para reserva gerais
        foreach ( $person->reserve as $reserve )
        {
            if ( $reserve->reserveTypeId == ID_RESERVETYPE_LOCAL || $reserve->reserveTypeId == ID_RESERVETYPE_WEB )
            {
                $totalContReserve += 1;
            }
            else
            {
                $totalContReserveInitial += 1;
            }
        }

        $addedExemplaries = $this->getExemplarys();

        //percorre exemplares adicionados na operação procurando exemplares em nivel inicial, adiciona na contagem de iniciais
        if ( (is_array($addedExemplaries)) && ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED ) )
        {
            foreach ( $addedExemplaries as $line => $addedExemplary )
            {
                if ( $addedExemplary->currentStatus->level == 1 )
                {
                    $contReserveInitial += 1;

                    if ( $reserve->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED )
                    {
                        $totalContReserveInitial += 1;
                    }
                }
                else
                {
                    $contReserve += 1;

                    if ( $reserve->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED )
                    {
                        $totalContReserve += 1;
                    }
                }
            }
        }

        //Verifica limit de reserva inicial
        if ( ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED ) || ( $reserveType->reserveTypeId == ID_RESERVETYPE_WEB_ANSWERED ) || ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL_INITIAL_STATUS) )
        {
            if ( $contReserveInitial >= $policy->reserveLimitInInitialLevel )
            {
                $this->addError(_M('Chegou ao limite de reservas no nível inicial.', $this->module), $reserveMSG);
                $this->blockReserveProcess(true);
                return false;
            }
        }
        else
        {

            if ( $contReserve >= $policy->reserveLimit )
            {
                $this->addError(_M('Chegou ao limite de reservas.', $this->module), $reserveMSG);
                $this->blockReserveProcess(true);
                return false;
            }
        }

        /**
         * Aqui aplica limite de reservas, baseado na contagem de reservas em estados inicias e demais reservas
         *
         * Note que os limites gerais (em inicial ou não) se somam, o total geral real de reservas é a soma dos dois
         *
         */
        $generalPolicy = $this->busGeneralPolicy->getGeneralPolicy($libraryUnit->privilegeGroupId, $person->link->linkId);
        //limite geral de reservas
        if ( ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL ) || ( $reserveType->reserveTypeId == ID_RESERVETYPE_WEB ) )
        {
            if ( is_numeric($generalPolicy->reserveGeneralLimit) )
            {
                if ( $totalContReserve >= $generalPolicy->reserveGeneralLimit )
                {
                    $this->addError(_M('Chegou ao limite geral de reservas.', $this->module), $reserveMSG);
                    $this->blockReserveProcess(true);
                    return false;
                }
            }
        }
        else
        {
            //limite geral de reservas no nivel inicial
            if ( is_numeric($generalPolicy->reserveGeneralLimitIninitialLevel) ) //inicial
            {
                if ( $totalContReserveInitial >= $generalPolicy->reserveGeneralLimitIninitialLevel )
                {
                    $this->addError(_M('Chegou ao limite geral de reservas em nível inicial.', $this->module), $reserveMSG);
                    $this->blockReserveProcess(true);
                    return false;
                }
            }
        }

        $exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus($exemplary->exemplaryStatusId, true);

        $executeReserve = MUtil::getBooleanValue($exemplaryStatus->executeReserve);
        $executeReserveInInitialLevel = $exemplaryStatus->level == 1 && MUtil::getBooleanValue($exemplaryStatus->executeReserveInInitialLevel);

        if ( !$executeReserve )
        {
            $this->addError(_M('Não é possível reservar exemplares no estado <b>@1</b>.', $this->module, $exemplaryStatus->description), $reserveMSG);
            return false;
        }

        //Nao permite atender reservas que ja foram atendidas
        if ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED )
        {
            if ( $this->busReserve->hasReserve($exemplary->itemNumber, array( ID_RESERVESTATUS_ANSWERED ), $person->personId) )
            {
                $this->addError(_M('A reserva para este exemplar já foi atendida.', $this->module), $reserveMSG);
                return false;
            }
        }

        //Nível inicial
        if ( $exemplaryStatus->level == 1 )
        {
            if ( !$executeReserveInInitialLevel )
            {
                $this->addError(_M('Não é permitido reservar este exemplar em estado inicial. Estado: <b>@1</b>.', $this->module, $exemplaryStatus->description), $reserveMSG);
                return false;
            }

            //compara o tipo de reserva para determinar a operação
            if ( ($reserveType->reserveTypeId == ID_RESERVETYPE_WEB) || ($reserveType->reserveTypeId == ID_RESERVETYPE_WEB_ANSWERED) )
            {
                $operation = ID_OPERATION_WEB_RESERVE_IN_INITIAL_STATUS;
            }
            else
            {
                $operation = ID_OPERATION_LOCAL_RESERVE_IN_INITIAL_STATUS;
            }

            $right = $this->busRight->hasRight($libraryUnit->libraryUnitId, $person->link->linkId, $exemplary->materialGenderId, $operation);

            if ( !$right )
            {
                $this->addError(_M('Não tem direitos para reserva em estado "@1".', $this->module, $exemplaryStatus->description), $reserveMSG);
                $this->blockReserveProcess(true);
                return false;
            }
            else
            {
                $exemplary->reserveLevel = 1;
            }

            // solicita confirmação ao usuário para material disponível
            if ( ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL || $reserveType->reserveTypeId == ID_RESERVETYPE_WEB ) && (!$initialStatusConfirmed ) )
            {
                return 'initial_confirm';
            }

            //Troca o tipo de reserva de local para local em estado inicial
            if ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL )
            {
                $this->setReserveType(ID_RESERVETYPE_LOCAL_INITIAL_STATUS);
            }
        }
        //Nível de transição
        else if ( $exemplaryStatus->level == 2 )
        {
            //Se for reserva que atende direto, não permite reserva dos materiais em nível de transição
            if ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED )
            {
                $this->addError(_M('Não pode atender reservas para materiais em transição.', $this->module), $reserveMSG);
                return false;
            }

            //compara o tipo de reserva para determinar a operação
            if ( $reserveType->reserveTypeId == ID_RESERVETYPE_WEB )
            {
                $operation = ID_OPERATION_WEB_RESERVE;
            }
            else
            {
                $operation = ID_OPERATION_LOCAL_RESERVE;
            }

            $right = $this->busRight->hasRight($libraryUnit->libraryUnitId, $person->link->linkId, $exemplary->materialGenderId, $operation);
            if ( !$right )
            {
                $this->addError(_M('Sem direitos para reservar.', $this->module), $reserveMSG);
                return false;
            }
            else
            {
                $exemplary->reserveLevel = 2;
            }
        }

        //Verifica se este exemplar ja possui um emprestimo pelo usuario
        $loans = $this->busLoan->getLoansOpenOfAssociation($libraryUnit->libraryUnitId, $person->personId);

        //iteração que verifica se o exemplar já está retirado para o usuário.
        if ( $loans )
        {
            foreach ( $loans as $loan )
            {
                if ( $loan->itemNumber == $exemplary->itemNumber )
                {
                    $this->addError(_M('Usuário já possui empréstimo para este exemplar.', $this->module), $reserveMSG);
                    $this->blockReserveProcess(true);
                    return false;
                }
            }
        }

        // Verifica se este exemplar ja possui uma reserva pelo usuario (para nao permitir duplicar)
        // verifica as reservas solicitadas e comunidadas e atendidas
        if ( $this->busReserve->hasReserve($exemplary->itemNumber, array( ID_RESERVESTATUS_REQUESTED, ID_RESERVESTATUS_REPORTED, ID_RESERVESTATUS_ANSWERED ), $person->personId) )
        {
            $this->addError(_M('Usuário já possui reserva para este material.', $this->module), $reserveMSG);
            $this->blockReserveProcess(true);
            return false;
        }

        $this->_addItemNumber($exemplary);
        $this->addInformation(_M('Exemplar adicionado com sucesso.', $this->module), $reserveMSG, 104);

        return true;
    }

    private function _addItemNumber($exemplary)
    {
        $exemplaryes = $this->session->get('reserveItems');
        $exemplaryes[$exemplary->itemNumber] = $exemplary;
        $this->session->set('reserveItems', $exemplaryes);
    }

    public function deleteItemNumber($itemNumber)
    {
        $items = $this->getExemplarys();

        if ( is_array($items) )
        {
            foreach ( $items as $line => $info )
            {
                if ( $info->itemNumber != $itemNumber )
                {
                    $temp[$line] = $info;
                }
            }
        }
        $this->session->unregister('reserveItems');
        $this->session->set('reserveItems', $temp);
    }

    public function clearItemsReserve()
    {
        $this->session->unregister('reserveItems');
    }

    public function clear()
    {
        $this->session->unregister('reserveType');
        $this->clearItemsReserve();
        $this->clearPerson();
        $this->session->unregister('libraryUnit2');
        $this->session->unregister('libraryUnitId2');
    }

    function getExemplarys()
    {
        $items = $this->session->get('reserveItems');
        return $items;
    }

    function finalize()
    {
        if ( $this->getBlockReserveProcess() )
        {
            $this->addError(_M('Processo de reserva bloqueado.', $this->module), null, 105);
            $this->blockReserveProcess(false); //desbloqueia o processo ao finalizar
            return false;
        }

        $this->blockReserveProcess(false); //desbloqueia o processo ao finalizar

        $exemplary = $this->getExemplarys();
        $person = $this->getPerson();
        $reserveType = $this->getReserveType();
        $libraryUnit = $this->getLibraryUnit(true);

        if ( $exemplary )
        {
            /* O processo de de reserva quando por ANSWERED (Atendida) deve:
              Para cada item:
              a) inserir a reserva
              b) inserir a composição
              c) trocar o estado para reservado (basConfig)
             */
            if ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED )
            {
                foreach ( $exemplary as $line => $info )
                {
                    $this->busReserve->reserveTypeId = $reserveType->reserveTypeId;
                    $this->busReserve->personId = $person->personId;
                    $this->busReserve->libraryUnitId = $libraryUnit->libraryUnitId;
                    $this->busReserve->requestedDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
                    $policy = $this->busPolicy->getPolicy($libraryUnit->privilegeGroupId, $person->link->linkId, $info->materialGenderId, true);
                    //Se reserva inicial
                    if ( ($reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED) || ($reserveType->reserveTypeId == ID_RESERVETYPE_WEB_ANSWERED) )
                    {
                        //Se estiver acessando do módulo Circulação de material
                        //if (MIOLO::_REQUEST('option') == '[F6] Exemplar')
                        if ( $reserveType->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED )
                        {
                            $this->busReserve->reserveStatusId = ID_RESERVESTATUS_REPORTED;
                            $date = $this->calculateLimitDate($policy->daysOfWaitForReserveInInitialLevel);
                        }
                        else
                        {
                            $this->busReserve->reserveStatusId = ID_RESERVESTATUS_REQUESTED;
                        }
                    }
                    else
                    {
                        $this->busReserve->reserveStatusId = ID_RESERVESTATUS_ANSWERED;
                        $date = $this->calculateLimitDate($policy->daysOfWaitForReserve);
                    }

                    $this->busReserve->limitDate = $date;

                    $reserveComposition = null;
                    $this->busReserve->reserveComposition = null;
                    $reserveComposition->itemNumber = $info->itemNumber;
                    $reserveComposition->isConfirmed = DB_TRUE;
                    $this->busReserve->reserveComposition[] = $reserveComposition;
                    $this->busReserve->insertReserve();     //insere reserva com composições

                    $this->busExemplaryControl->changeStatus($info->itemNumber, DEFAULT_EXEMPLARY_STATUS_RESERVADO, GOperator::getOperatorId()); //troca o estado
                }
                $this->clearMessages();
                $this->addInformation(_M('Reservas atendidas com sucesso.', MIOLO::getCurrentModule()));
                return true;
            }
            else
            {
                /*
                 *  Caso seja solicitada
                 *  a) Incluir uma única reserva com várias composições
                 *  b) não deve efetuar troca de estado
                 */
                $this->busReserve->reserveStatusId = ID_RESERVESTATUS_REQUESTED;
                $this->busReserve->reserveTypeId = $reserveType->reserveTypeId;
                $this->busReserve->personId = $person->personId;
                $this->busReserve->libraryUnitId = $libraryUnit->libraryUnitId;
                $this->busReserve->requestedDate = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);

                foreach ( $exemplary as $line => $info )
                {
                    unset($reserveComposition);
                    $reserveComposition->itemNumber = $info->itemNumber;
                    $reserveComposition->isConfirmed = DB_FALSE;
                    $this->busReserve->reserveComposition[] = $reserveComposition;
                }

                //$this->busReserve = new BusinessGnuteca3BusReserve(); //para o Zen
                $this->busReserve->insertReserve();

                $this->clearMessages();
                $this->addInformation(_M('Solicitação de reserva efetuada com sucesso.', MIOLO::getCurrentModule()));

                return true;
            }
        }
        else
        {
            $this->addError(_M('Não foi possível efetuar a reserva. ', MIOLO::getCurrentModule()));
            return false;
        }
    }

    /**
     * Este método verifica se há alguma reserva na fila de espera e atende a reserva
     *
     * @param string int $itemNumber
     * @return boolean
     */
    function meetReserve($itemNumber, $exemplary = null)
    {
        $module = MIOLO::getCurrentModule();
        $reserveMSG['itemNumber'] = $itemNumber;

        $exemplaryStatus = $this->busExemplaryControl->getExemplaryControl($itemNumber);
        
            if ( !$exemplary )
            {
                $exemplary = $this->busExemplaryControl->getExemplaryControl($itemNumber);
            }

            //se exemplar não existir informa ao usuário
            if ( !$exemplary->itemNumber )
            {
                $this->addError(_M('Exemplar não existe.', $this->module), $reserveMSG);
                return false;
            }

            //aproveita objeto do estado do exemplar, caso exista
            if ( is_object($exemplary->exemplaryStatus) )
            {
                $exemplaryStatus = $exemplary->exemplaryStatus;
            }
            else
            {
                $exemplaryStatus = $this->busExemplaryStatus->getExemplaryStatus($exemplary->exemplaryStatusId, true);
            }

            //verifica se o estado permite atender reservas
            if ( !MUtil::getBooleanValue($exemplaryStatus->meetReserve) )
            {
                $this->addError(_M('O estado <b>@1</b> não permite atender reservas.', $this->module, $exemplaryStatus->description), $reserveMSG);
                return false;
            }

            // verifica se já ha alguma reserva confirmada, se tiver informa que o exemplar já esta reservado
            $reserve = $this->busReserve->getReservesOfExemplary($itemNumber, array( ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED ), true);
            
            if ( $reserve )
            {
                $this->addError(_M('Exemplar já reservado.', $this->module), $reserveMSG);
                return false;
            }

            // verifica se há reservas na fila
            $reserve = $this->busReserve->getReservesOfExemplary($itemNumber, array( ID_RESERVESTATUS_REQUESTED ), false);

            if ( !$reserve )
            {
                $this->addError(_M('Sem reservas para atender.', $this->module));
                return false;
            }
            
            // verifica se há um estado futuro
            $futureStatusId = $this->busRulesForMaterialMovement->getFutureStatus($itemNumber, $exemplary->exemplaryStatusId, ID_OPERATION_MEET_RESERVE, 1);

            if ( !$futureStatusId )
            {
                $this->addError(_M('Não foi possível localizar um <b>estado futuro</b> para o exemplar. @1', $this->module, $itemNumber));
                return false;
            }

            $busOperationRequestChangeExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusOperationRequestChangeExemplaryStatus');

            foreach ( $reserve as $reserv )
            {
                $busOperationRequestChangeExemplaryStatus->setPersonId($reserv->personId);
                $vinculo = $busOperationRequestChangeExemplaryStatus->getPersonLink();

                if ( $vinculo )
                {
                    $reserveA = $reserv;
                    break;
                }
                else
                {
                    $this->cancelReserve($reserv->reserveId);
                }
            }

            //retorna false se não tiver reserva pra atender
            if ( !$reserveA )
            {
                return false;
            }

            //$reserve = $reserve[0];

            $libraryUnit = $this->getLibraryUnit(true);
            $link = $this->busBond->getPersonLink($reserveA->personId);
            $policy = $this->busPolicy->getPolicy($libraryUnit->privilegeGroupId, $link->linkId, $exemplary->materialGenderId, true);

            if ( !$policy->privilegeGroupId )
            {
                $this->addError(_M('Sem políticas.', $this->module), $reserveMSG, 103);
                return false;
            }

            //Se reserva inicial
            if ( ($reserveA->reserveTypeId == ID_RESERVETYPE_LOCAL_INITIAL_STATUS) || ($reserveA->reserveTypeId == ID_RESERVETYPE_LOCAL_ANSWERED) || ($reserveA->reserveTypeId == ID_RESERVETYPE_WEB_ANSWERED) )
            {
                $date = $this->calculateLimitDate($policy->daysOfWaitForReserveInInitialLevel);
            }
            else
            {
                $date = $this->calculateLimitDate($policy->daysOfWaitForReserve);
            }

            $this->busReserve->changeReserveStatus($reserveA->reserveId, ID_RESERVESTATUS_ANSWERED, null, $date); // operator

            $this->busReserveComposition->reserveId = $reserveA->reserveId;
            $this->busReserveComposition->itemNumber = $itemNumber;
            $this->busReserveComposition->isConfirmed = DB_TRUE;
            $this->busReserveComposition->updateComposition();

            $this->busExemplaryControl->changeStatus($itemNumber, $futureStatusId, GOperator::getOperatorId(), $exemplary); //operator

            if ( !MUtil::getBooleanValue(SUPRESS_RETURN_MESSAGE) )
            {
                $this->addInformation(_M('Reserva do exemplar @1 atendida para o usuário @2', $module, $itemNumber, $reserveA->personId), $reserveMSG);
            }

            return true;
    }

    /**
     * Calcula data limite para reserva
     *
     * @param string $days dias
     * @return string data
     */
    public function calculateLimitDate($days)
    {
        return $this->busHoliday->checkHolidayBetweenDate(GDate::now(), $days, $this->getLibraryUnit());
    }

    /**
     * Comunica usuarios com reservas atendidas
     *
     * Metodo reformulado.
     *
     * @return boolean
     */
    function comunicateReserveAnswered()
    {
        $libraryUnitId = $this->libraryUnit->libraryUnitId;
        $reserves = $this->busReserve->getReservesLibrary(ID_RESERVESTATUS_ANSWERED, true, $libraryUnitId, 'object');

        if ( !$reserves )
        {
            return true;
        }

        $reserveMensage = array( );

        foreach ( $reserves as $line => $reserve )
        {
            $person = $this->busPerson->getPerson($reserve->personId);

            $reserveMensage[$reserve->reserveId][0] = $reserve->reserveId;
            $reserveMensage[$reserve->reserveId][1] = "{$reserve->personId} - {$person->personName}";
            $reserveMensage[$reserve->reserveId][4] = $reserve->itemNumber;
            $reserveMensage[$reserve->reserveId][5] = $person->email;
            $reserveMensage[$reserve->reserveId][6] = $this->busMaterial->getContentByItemNumber($reserve->itemNumber, MARC_TITLE_TAG);

            if ( !strlen($person->email) )
            {
                $reserveMensage[$reserve->reserveId][2] = DB_FALSE;
                $reserveMensage[$reserve->reserveId][3] = _M("E-mail da pessoa está em branco", $this->module);
                continue;
            }

            //testa se e-mail é no formato conta@domínio.extensão
            if ( !preg_match("/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+\.([a-zA-Z]{2,4})$/", $person->email) )
            {
                $reserveMensage[$reserve->reserveId][2] = DB_FALSE;
                $reserveMensage[$reserve->reserveId][3] = _M("Person mail is invalid.", $this->module);
                continue;
            }

            //verifica se a pessoa tem vínculo válido
            $link = $this->busBond->getPersonLink($reserve->personId);

            if ( !$link )
            {
                $reserveMensage[$reserve->reserveId][2] = DB_FALSE;
                $reserveMensage[$reserve->reserveId][3] = _M("Vínculo da pessoa é inválido.", $this->module);
                continue;
            }

            //verifica o genero do material
            $materialGenderId = $this->busExemplaryControl->getMaterialGender($reserve->itemNumber);

            if ( !$materialGenderId )
            {
                $reserveMensage[$reserve->reserveId][2] = DB_FALSE;
                $reserveMensage[$reserve->reserveId][3] = _M("Gênero do material é inválido", $this->module);
                continue;
            }

            //obtem políticas
            $policy = $this->busPolicy->getPolicy($this->libraryUnit->privilegeGroupId, $link->linkId, $materialGenderId);

            if ( !$policy->privilegeGroupId )
            {
                $reserveMensage[$reserve->reserveId][2] = DB_FALSE;
                $reserveMensage[$reserve->reserveId][3] = _M("Grupo de privilégio é inválido. Código do vínculo: @1; Código do gênero do material: @2;", $this->module, $link->linkId, $materialGenderId);
                continue;
            }

            //calcula data limite baseado no tipo de reserva
            switch ( $reserve->reserveTypeId )
            {
                case ID_RESERVETYPE_LOCAL_INITIAL_STATUS :
                case ID_RESERVETYPE_WEB_ANSWERED :
                    $date = $this->calculateLimitDate($policy->daysOfWaitForReserveInInitialLevel);
                    break;

                default :
                    $date = $this->calculateLimitDate($policy->daysOfWaitForReserve);
                    break;
            }

            $reserve->limitDate = $date;
            $this->busReserve->updateLimitDate($reserve->reserveId, $date);

            if ( $this->sendMail->sendMailToUserReserveAnswered($reserve, $person->personName, $person->email) )
            {
                $this->busReserve->changeReserveStatus($reserve->reserveId, ID_RESERVESTATUS_REPORTED, null);
                $reserveMensage[$reserve->reserveId][2] = DB_TRUE;
                $reserveMensage[$reserve->reserveId][3] = _M("Sucesso!", $this->module);
            }
            else
            {
                $reserveMensage[$reserve->reserveId][2] = DB_FALSE;
                $reserveMensage[$reserve->reserveId][3] = _M("Falha no envio do e-mail", $this->module);
            }
        }

        $this->sendMail->sendMailToAdminResultOfReserveAnswered($reserveMensage, $libraryUnitId);

        // MONTA GRID COM OS DADOS DOS ENVIOS DE EMAIL.
        foreach ( $reserveMensage as $content )
        {
            $this->addGridData($content);
        }
    }

    /**
     * Comunica reserva 
     * @param item number
     * @param object libraryUnit
     * @return boolean
     */
    public function communicateReserve($itemNumber, $libraryUnit)
    {
        if ( !$itemNumber )
        {
            return false;
        }

        $reserve = $this->busReserve->getReservesOfExemplary($itemNumber, ID_RESERVESTATUS_ANSWERED);
        $reserve = $reserve[0];

        if ( !$reserve )
        {
            return false;
        }

        $person = $this->busPerson->getPerson($reserve->personId);
        $link = $this->busBond->getPersonLink($reserve->personId);

        if ( !$link )
        {
            return false;
        }

        $materialGenderId = $this->busExemplaryControl->getMaterialGender($reserve->itemNumber);

        if ( !$materialGenderId )
        {
            return false;
        }

        $policy = $this->busPolicy->getPolicy($libraryUnit->privilegeGroupId, $link->linkId, $materialGenderId);

        if ( !$policy->privilegeGroupId )
        {
            return false;
        }

        switch ( $reserve->reserveTypeId )
        {
            case ID_RESERVETYPE_LOCAL_INITIAL_STATUS:
            case ID_RESERVETYPE_WEB_ANSWERED:
                $date = $this->calculateLimitDate($policy->daysOfWaitForReserveInInitialLevel);
                break;
            default:
                $date = $this->calculateLimitDate($policy->daysOfWaitForReserve);
                break;
        }

        $reserve->limitDate = $date;
        $this->busReserve->updateLimitDate($reserve->reserveId, $date);

        //envia e-mail e troca estado
        if ( $this->sendMail->sendMailToUserReserveAnswered($reserve, $person->personName, $person->email) )
        {
            $this->busReserve->changeReserveStatus($reserve->reserveId, ID_RESERVESTATUS_REPORTED, null);
        }
        else
        {
            return false;
        }

        return true;
    }

    /**
     * Cancela uma reserva, atendendo a próxima caso seja necessário.
     *
     * @param reserveId código da reserv
     * @param estado do cancelamento default ID_RESERVESTATUS_CANCELLED, mas poderia ID_RESERVESTATUS_UNSUCCESSFUL //vencida
     *
     */
    function cancelReserve($reserveId, $newReserveStatusId = ID_RESERVESTATUS_CANCELLED)
    {
        //evita problemas que poderia cancelar várias reservas
        if ( !$reserveId )
        {
            $this->addError(_M('Impossível encontrar sua reserva.', MIOLO::getCurrentModule()));
            return;
        }

        //obtem a reserva para verificar o seu estado atual
        $reserve = $this->busReserve->getReserve($reserveId, true);
        //estado atual da reserva
        $reserveStatusId = $reserve->reserveStatusId;
        $compositions = $reserve->reserveComposition;

        //localiza o material confirmado
        if ( is_array($compositions) )
        {
            foreach ( $compositions as $line => $composition )
            {
                if ( MUtil::getBooleanValue($composition->isConfirmed) )
                {
                    $selectedComposition = $compositions[$line];
                }
            }
        }

        //troca o estado da reserva , trocando para cancelado ou vencido, de acordo com o caso
        $this->busReserve->changeReserveStatus($reserveId, $newReserveStatusId, null);

        //Só precisa mudar o estado do exemplar ou atender , se a reserva estiver no estado de Atendida ou Comunicada
        if ( ($reserveStatusId == ID_RESERVESTATUS_REPORTED) || ($reserveStatusId == ID_RESERVESTATUS_ANSWERED) )
        {
            $itemNumber = $selectedComposition->itemNumber;
            //busca relação de reservas solicitadas
            $reserves = $this->busReserve->getReservesOfExemplary($itemNumber, ID_RESERVESTATUS_REQUESTED, false);
            //estado atual do material
            $currentState = $selectedComposition->exemplaryStatusId;

            if ( ($reserves) && $currentState != DEFAULT_EXEMPLARY_STATUS_EMPRESTADO )
            {
                //não pode retornar o resultado do meetReseve, pois ele pode retornar falso, pois pode não ter nada para atender,
                //mas mesmo assim, esta operação retornou sucesso.
                $this->meetReserve($itemNumber);
            }
            else
            {
                $futureStatusId = $this->busRulesForMaterialMovement->getFutureStatus($itemNumber, $currentState, ID_OPERATION_CANCEL_RESERVE, 1);

                if ( !$futureStatusId )
                {
                    $this->addError(_M('Sem estado futuro.', $this->module));
                }
                else
                {
                    $result = $this->busExemplaryControl->changeStatus($itemNumber, $futureStatusId, GOperator::getOperatorId());
                    return $futureStatusId;
                }
            }
        }

        return true;
    }

    /**
     * Cancela a reserva para um itemNumber que esteja confirmado em uma 
     * reserva (atendida|comunicada|solicitada)
     *
     * @param stdClass $itemNumber
     * @return boolean
     */
    public function cancelReserveByItemNumber($exemplary, $args = false)
    {
        //Filtro que obtem todas reservas em estado aberto para o exemplar.
        $reserveStatusId = array(ID_RESERVESTATUS_ANSWERED,ID_RESERVESTATUS_REPORTED,ID_RESERVESTATUS_REQUESTED);//Atendida,Comunicada,Solicitada

        //Obtem reserva que esteja (atendiada|comunicada|solicita)
        $exemplaryReserves = $this->busReserve->getReservesOfExemplary($exemplary->itemNumber, $reserveStatusId,null);        

        //Se tiver reservas abertas.
        if ( is_array($exemplaryReserves) )
        {
            //Itera pelas reservas abertas.
            foreach ( $exemplaryReserves as $reserve )
            {
                $cancelReserve = true;
                //Todas reservas que nao forem solicitadas e nao estiverem confirmadas devem ser ignoradas.
                if ( $reserve->reserveStatusId != ID_RESERVESTATUS_REQUESTED && !MUtil::getBooleanValue($reserve->isConfirmed))
                {
                    //Passa para o proximo exemplar.
                    continue;
                }
                //Obtem a reserva
                $this->busReserve = MIOLO::getInstance()->getBusiness('gnuteca3', 'BusReserve');
                $reserveObject = $this->busReserve->getReserve($reserve->reserveId);
                $composition = $reserveObject->reserveComposition;
                
                //Verifica se tem composiçao a reserva
                if ( is_array($composition) )
                {
                    //Itera pela composiçao da reserva
                    foreach ( $composition as $exemplaryC )
                    {
                        //se for status de baixa
                        $exemplarStatus = $this->busExemplaryStatus->getExemplaryStatus($exemplaryC->exemplaryStatusId, true);

                        //Se o estado do exemaplar nao for de baixa, vai para o proximo exemplar da composicao && reserva nao estiver como SOLICITADA
                        //Reserva solicitada sempre e cancelada independendo se o exemplar esta confirmado ou nao.
                        if ( !MUtil::getBooleanValue($exemplarStatus->isLowStatus) )
                        {
                            $cancelReserve = false;
                            break;
                        }
                    }

                    if ( $cancelReserve )
                    {
                        //cancela a reserva
                        $this->cancelReserve($reserveObject->reserveId);
                        
                        if ( $args->observationSendMail )
                        {
                            // ENVIA EMAIL PARA OS USUARIO QUE TIVERAM SUAS RESERVAS CANCELADAS
                            $this->sendMail->sendPersonalMailToUserInformingReserveCancel($reserveObject->reserveId, EMAIL_CANCEL_RESERVE_COMUNICA_SOLICITANTE_SUBJECT, $args->observationMail);
                        }
                    }
                    else
                    {
                        //Se a reserva nao foi cancelada deve ser desatendida.
                        $this->busReserve->neglectReserve($reserve->reserveId, false);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Reorganiza a lita de reserva
     *
     * @param $date data
     * @param $libraryUnitId unidade da biblioteca
     * @return nothing
     */
    function reorganizeQueueReserve($date, $libraryUnitId)
    {
        //pega a data atual
        $now = GDate::now();
        $date = new GDate($date);

        //compara com a data passada
        $diff = $now->diffDates($date);
        $diff = $diff->days;

        if ( $diff < 0 )
        {
            $this->addError(_M('Data excede dia de hoje', $this->module));
            return;
        }

        $reserveStatusId = array( ID_RESERVESTATUS_ANSWERED, ID_RESERVESTATUS_REPORTED );
        $this->busReserve->libraryUnitId = $libraryUnitId;

        //pega a descrição do estado da reserva para o qual esta sendo trocado: default: Vencida
        $statusReserve = $this->busReserveStatus->getReserveStatus(ID_RESERVESTATUS_UNSUCCESSFUL)->description;

        //buscas as reservas confirmadas no estado atendida e comunicada
        $reserves = $this->busReserve->getReserves($reserveStatusId, true);

        //se não tiver reservas retorna erro de "Sem reservas para organizar"
        if ( !$reserves )
        {
            $this->addError(_M('Nenhuma reserva para organizar.', $this->module));
            return;
        }

        //para cada reserva, executa processo de cancelar
        foreach ( $reserves as $line => $info )
        {
            //verifica se a data limite é menor que a data do campo $date
            $limitDate = new GDate($info->limitDate);
            $diff = $date->diffDates($limitDate);
            $diff = $diff->days;

            if ( $diff > 0 )
            {
                $this->setLibraryUnit($info->libraryUnitId);
                //chama o processo de cancelar a reserva, mas forçando o estado para Vencida
                $this->cancelReserve($info->reserveId, ID_RESERVESTATUS_UNSUCCESSFUL);

                //Data for add to grid. Formatação do 1º null (Data) e do 2º null (pessoa) são feitos no GridReserveQueue
                $this->addGridData(array( $info->reserveId, $info->itemNumber, $info->controlNumber, null, $info->personId, $info->name, $info->limitDate, $statusReserve ));
            }
        }
    }

    /**
     * Adiciona dados a relação de dados para ir para a grid
     *
     * @param array $gridData
     */
    public function addGridData($gridData)
    {
        $this->gridData[] = $gridData;
    }

    public function getGridData()
    {
        return $this->gridData;
    }
}
?>

