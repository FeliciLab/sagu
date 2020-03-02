<?php

/**
 * Description of bottomBar
 *
 * @author jonas
 */
class Cielo extends MDiv
{
    private $urlapf;
    private $numeroCartao;
    private $mesValidade;
    private $anoValidade;
    private $codigoSeguranca;
    private $quantidadeParcelas;
    private $valorDocumento;
    private $cieloAccount;
    
    public function getCieloAccount()
    {
        return SAGU::getParameter('FINANCE', 'CIELO_ACCOUNT');
    }

    public function setCieloAccount($cieloAccount)
    {
        $this->cieloAccount = $cieloAccount;
    }

    public function getUrlapf()
    {
        return $this->urlapf;
    }

    public function setUrlapf($urlapf)
    {
        $this->urlapf = $urlapf;
        $this->setQuantidadeParcelas(1);
    }

    public function getNumeroCartao()
    {
        return $this->numeroCartao;
    }

    public function setNumeroCartao($numeroCartao)
    {
        $this->numeroCartao = $numeroCartao;
    }

    public function getMesValidade()
    {
        return $this->mesValidade;
    }

    public function setMesValidade($mesValidade)
    {
        $this->mesValidade = $mesValidade;
    }

    public function getAnoValidade()
    {
        return $this->anoValidade;
    }

    public function setAnoValidade($anoValidade)
    {
        $this->anoValidade = $anoValidade;
    }

    public function getCodigoSeguranca()
    {
        return $this->codigoSeguranca;
    }

    public function setCodigoSeguranca($codigoSeguranca)
    {
        $this->codigoSeguranca = $codigoSeguranca;
    }

    public function getQuantidadeParcelas()
    {
        return $this->quantidadeParcelas;
    }

    public function setQuantidadeParcelas($quantidadeParcelas)
    {
        $this->quantidadeParcelas = $quantidadeParcelas;
    }

    public function getValorDocumento()
    {
        return $this->valorDocumento;
    }

    public function setValorDocumento($valorDocumento)
    {
        $this->valorDocumento = $valorDocumento;
    }

    public function __construct()
    {
        $this->setUrlapf('https://www.aprovafacil.com/cgi-bin/APFW/'.$this->getCieloAccount().'/APC');
    }
    
    public function efetuarTransacao()
    {        
        $parametros = 'NumeroCartao=' . $this->getNumeroCartao() .
                      '&MesValidade=' . $this->getMesValidade() .
                      '&AnoValidade=' . $this->getAnoValidade() .
                      '&CodigoSeguranca=' . $this->getCodigoSeguranca() .
                      '&QuantidadeParcelas=' . $this->getQuantidadeParcelas() .
                      '&ValorDocumento=' . $this->getValorDocumento();
        
        $ch = curl_init($this->urlapf);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parametros);
        $resultadotransacao = explode(chr(10), curl_exec($ch));
        curl_close($ch);

        if (substr($resultadotransacao[2], 0, 4) == 'True') 
        {
            $Transacao = substr($resultadotransacao[11], 0, 14);
            $CodigoAutorizacao = substr($resultadotransacao[8], 0, 6);

            // Transação Aprovada deve ser confirmada após salvar os dados no seu banco de dados
            $urlapf = 'https://www.aprovafacil.com/cgi-bin/APFW/'.$this->getCieloAccount().'/CAP?Transacao=' . $Transacao;

            $ch = curl_init($urlapf);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $resultadotransacao = curl_exec($ch);
            curl_close($ch);
            
            return true;

        }
        else 
        {
            echo false;
        }
    }
}

?>
