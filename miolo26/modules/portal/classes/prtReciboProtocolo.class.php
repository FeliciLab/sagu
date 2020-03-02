<?php

$MIOLO->uses('classes/GString.class.php', $module);
$MIOLO->uses('types/PtcSubject.class', 'protocol');

class prtReciboProtocolo
{
    
    // 48 -> Baseado no arquivo do gnuteca
    const RECIBO_MAX_LENGHT = 48;
    
    private $solicitacaoId;
    private $personId;
    private $fileName;
    
    public function __construct($solicitacaoId, $personId)
    {
        $this->solicitacaoId = $solicitacaoId;
        $this->personId = $personId;
    }
    
    public function gerarRecibo()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $busPhysicalPerson = new BusinessBasicBusPhysicalPerson();
        $busRequestStatus = new BusinessProtocolBusRequestStatus();
        $busSector = new BusinessBasicBusSector();
        $busRequest = new BusinessProtocolBusRequestPtc();
        $busSubject = new BusinessProtocolBusSubject();
        $busDispatch = new BusinessProtocolBusDispatch();

        $person = $busPhysicalPerson->getPhysicalPerson($this->personId);
        $requestData = $busRequest->getRequest($this->solicitacaoId);
        $requestPerson = $busPhysicalPerson->getPhysicalPerson($requestData->personId);
        $subject = $busSubject->getSubject($requestData->subjectId);
        $status = $busRequestStatus->getStatusDescription($requestData->statusId);
        $sector = $busSector->getSector($requestData->sectorId);
        $currentSector = $busSector->getSector($requestData->currentSectorId ? $requestData->currentSectorId : $requestData->sectorId);
        $date = $requestData->dateTime;
        $dispatch = $busDispatch->searchDispatch($this->solicitacaoId);
        
        //PhysicalPersonEmployee
        $businessPhysicalPersonEmployee = new BusinessBasicBusPhysicalPersonEmployee();
        $personEmployee = $businessPhysicalPersonEmployee->getPersonByMioloUserName($requestData->userName);
        
        $txtInfo = $this->gerarCabecalho();
        $txtInfo .= $this->quebrarLinha();
        $txtInfo .= $this->escreverLinha("Solicitante: {$requestData->personId} - {$requestPerson->name}");
        $txtInfo .= $this->escreverLinha("Assunto: {$subject->description}");
        $txtInfo .= $this->escreverLinha("Número da Solicitação: {$requestData->number}");
        $txtInfo .= $this->escreverLinha("Status: $status");
        $txtInfo .= $this->escreverLinha("Registrado por: {$personEmployee->personId} - {$personEmployee->name}");
        $txtInfo .= $this->escreverLinha("Setor de origem: {$sector->description}");
        $txtInfo .= $this->escreverLinha("Setor atual: {$currentSector->description}");
        $txtInfo .= $this->escreverLinha("Início: $date");
        $txtInfo .= $this->escreverLinha("Descrição: {$requestData->description}");
        
        if ( count($dispatch) > 0 )
        {
            $txtInfo .= $this->escreverLinha("Parecer: {$dispatch[0][5]}");
        }
        
        // HARDCODE
        if ( $requestData->statusId == 1 && $subject->taxValue > 0 )
        {
            $txtInfo .= $this->escreverLinha("Taxa: {$subject->taxValue}");
            $txtInfo .= $this->escreverLinha("Obs.: A solicitação só será válida após");
            $txtInfo .= $this->escreverLinha("o pagamento da taxa.");
        }

        $txtInfo .= $this->gerarRodape();
        
        $this->fileName = $MIOLO->getConf('home.html') . "/files/tmp/protocolo_" . $this->solicitacaoId . '.txt';
        $this->fileName = str_replace('/miolo20/', '/miolo26/', $this->fileName);
        $file = fopen($this->fileName, 'w+');
        fwrite($file, $txtInfo);
        fclose($file);

        $txtInfo = str_replace(array(chr(124), '-SOLICITACAO DE PROTOCOLO-', '--', '+'), '', $txtInfo);
        
        return $txtInfo;
    }
    
    public function obterArquivo()
    {
        return $this->fileName;
    }
    
    private function gerarCabecalho()
    {
        $string = NULL;
        
        $metade = (self::RECIBO_MAX_LENGHT/2) - 12;
        $string .= chr(43);
        for ( $i = 0; $i < $metade-1; $i++)
        {
            $string .= chr(45);
        }
        
        $string .= 'SOLICITACAO DE PROTOCOLO';
        
        for ( $i = strlen($string); $i < self::RECIBO_MAX_LENGHT-1; $i++)
        {
            $string .= chr(45);
        }
        $string .= chr(43);
        
        return $string;
    }
    
    private function gerarRodape()
    {
        $string = chr(43);
        for ( $i = 1; $i < self::RECIBO_MAX_LENGHT-1; $i++)
        {
            $string .= chr(45);
        }
        $string .= chr(43);
        
        return $string;
    }
    
    private function escreverLinha($linha)
    {
        $gString = new GString($linha);
        $gString = $gString->unaccent();
        $gString = $gString->toUpper();
        $linha = $gString->getString();

        // Se a linha for maior do que 44, quebra por espaços, para poder
        // dividir as palavras corretamente
        if ( strlen($linha) > (self::RECIBO_MAX_LENGHT - 4) )
        {
            $quebra = split(" ", $linha);
            $texto = "";

            foreach ( $quebra as $position => $pedaco )
            {                
                if ( strlen($texto . " " . $pedaco) <= (self::RECIBO_MAX_LENGHT - 4) )
                {
                    $texto .= " " . $pedaco;
                }
                else
                {
                    $string .= $this->escreverLinha(trim($texto));
                    $texto = $pedaco;
                }
            }
            
            $string .= $this->escreverLinha(trim($texto));
        }
        else
        {
            $string = chr(124) . chr(32);
            $string .= $linha;
            
            for ( $i = strlen($string); $i < self::RECIBO_MAX_LENGHT-1; $i++)
            {
                $string .= chr(32);
            }

            $string .= chr(124) . $this->quebrarLinha();
        }
        
        return $string;
    }
    
    private function quebrarLinha()
    {
        return chr(13);
    }
    
    private function obterPareceres()
    {
        
    }
}

?>
