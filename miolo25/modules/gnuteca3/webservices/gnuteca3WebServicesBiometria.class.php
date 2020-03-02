<?php
/**
 * <--- Copyright 2005-2014 de Solis - Cooperativa de Soluções Livres Ltda. e
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
 * Classe Business para BusBiometry
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 25/02/2014
 * 
 **/

include("GnutecaWebServices.class.php");

class gnuteca3WebServicesBiometria extends GWebServices 
{
    public $busBiometry;
    public $busBiometrySession;
    
    public function __construct() {
        parent::__construct();
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $this->busBiometry = $MIOLO->getBusiness($module, 'BusBiometry');
        $this->busBiometrySession = $MIOLO->getBusiness($module, 'BusBiometrySession');
        
    }
    
    public function setIdenty($sessionId, $return)
    {
        
        $this->busBiometrySession->sessionId = $sessionId; 
        $this->busBiometrySession->return = $return;

        $this->busBiometrySession->updateBiometrySession();
        
    }
    
    public function getCapture($capture)
    {
        if(BIOMETRIC_PERSON_REGISTER == DB_TRUE)
        {
            if(strlen($capture) > 0)
            {
                $opts = explode(";", $capture);

                $pId = $opts[0];
                $bHash = $opts[1];

                if($pId != NULL && $bHash != NULL)
                {
                    //Coloca o ID para a identificação da pessoa
                    $this->busBiometry->personId = $pId; 
                    //Coloca o objeto BASE64 para a criação da lista para identificação
                    $this->busBiometry->biometry = $bHash;
                    //Cria o md5 do objeto, que será útil na sincronização
                    $this->busBiometry->key =  md5($bHash);

                    //Deleta a pessoa, caso a mesma já exista na base
                    $this->busBiometry->deleteBiometry($pId);
                    //Insere a pessoa novamente
                    $this->busBiometry->insertBiometry();
                }
            }
            
            $r = "OK";
            
        }
        else
        {            
            $email = new GMail();
                
            $email->setAddress(EMAIL_ADMIN);
            $email->setSubject("[Gnuteca] Aviso");
            $email->setContent("O host de endereço IP " . $this->clientIP . " tentou realizar o cadastro biométrico sem autorização.");
                
            $email->send();
            
        }
            

        return $r;
    }
    
    public function getPessoas()
    {
        $var = $this->busBiometry->searchBiometry(TRUE);
        /*foreach($var as $v)
        {
        }*/
        
        return $var;
        
    }
    
    public function goSync($lista)
    {
        //Variável que contem todas as informações cadastradas;
        $lBd = $this->busBiometry->searchBiometry(TRUE);
        
        
        //Monta objeto com a lista dos IDs que o usuário já possui
        foreach($lista as $l)
        {
            //Transforma em objeto para se tornar acessível
            $p = (object) $l;
            //Aloca valor do id para variável a ser consultada
            $idL = $p->id;
            //Aloca key md5 para variável com o valor a ser consultado
            $keyL = $p->key;

            if($this->busBiometry->verificaIntegridade($idL, $keyL))
            {
                //Adiciona ID na lista que o usuário possui de identificação
                $ids[] = $p->id;
            }else
            {
            }
        }
        
        // Verificar se todos os dados da base, estão no local do cliente
        foreach($lBd as $dado)
        {
            if(in_array($dado->personId, $ids))
            {
                //Se esta ok, aqui ele não tomará nenhuma ação
                
            }else
            {
                //Irá montar o objeto, e adiciona a um array que será retornado do webservice
                $list[] = $dado;
            }
        }
        
        return $list;
    }
    
}
?>
