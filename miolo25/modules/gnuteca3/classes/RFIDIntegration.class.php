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
 *
  @ @author Tcharles Silva [tcharles@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Pereira da Silva[eduardo@solis.coop.br]
 * Lucas Gerhardt
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 10/01/2014
 *
 * */

class RFIDIntegration
{
    public $instance;

    public function __construct()
    {   
    }
    
    public static function getInstance()
    {
        if (self::$instance == NULL)
        {
            self::$instance = new RFIDIntegration();
        }
        return self::$instance;
    }
    
    /*
     * Executa diferentes comandos, dependendo da indicação do parâmetro
     * Criado em Janeiro/2014
     * Por: Tcharles Silva
     */
    public static function executeCommand($path, $command, $tag)
    {
        //Parametro $command, indica qual será o comando a ser executado
        
        //Pega valor do diretório identificado pelo cookie
        $termId = $_COOKIE[RFID_COOKIE];
        
        $arqInt = $path . "/" . $termId .  "/Req/Integracao.xml";
        $arqResp = $path . "/" . $termId . "/Resp/Retorno.xml";
        
        
        //Caso arquivo não exista, ou diretorio não exista, ou não tiver permissão para escrita, não realiza operação
        if(! file_exists($arqInt) || ! is_dir($path . "/" . $termId . "/Resp") || ! is_writable($arqInt))
        {
            return false;
        }       
        else
        {
            $sem = sem_get(ftok($arqInt, "W"), 1);
            
            switch($command)
            {
                case RFID_WRITE_TAG:
                    
                    //Status de proteção ativa
                    $status =1;
                    
                    //Solicita Semáforo
                    sem_acquire($sem);
                    
                    //Abre arquivo e coloca os conteúdos
                    $xml = simplexml_load_file($arqInt);
                    $xml->Comando = RFID_WRITE_TAG;
                    $xml->Protecao = $status;
                    $xml->Conteudo = $tag;
                    
                    //grava no arquivo
                    file_put_contents($arqInt, $xml->asXML());

                    //Aguarda um tempo para o software responder
                    sleep(3);
                    
                    //Mostra o retorno
                    $xml = simplexml_load_file($arqResp);
                    
                    if($xml->Retorno != 'SUCESS')
                    {
                        $err = $xml->Conteudo;
                    }
                    
                    sem_release($sem);
                    break;

                case RFID_READ_TAG: //Comando 2 - Leitura de etiqueta

                    //Utilizando semáforo para obter o acesso restrito ao arquivo
                    sem_acquire($sem);
                    
                    //Carrega arquivo
                    $xml = simplexml_load_file($arqInt);
                    
                    //Retira alguns conteúdos desnecessários
                    unset($xml->Protecao);
                    unset($xml->Conteudo);
                    
                    //escreve no arquivo
                    $xml->Comando = RFID_READ_TAG;
                    file_put_contents($arqInt, $xml->asXML());

                    //aguarda o software responder
                    sleep(3);

                    $xml = simplexml_load_file($arqResp);
                    
                    if($xml->Retorno != 'SUCESS')
                    {
                        $err = $xml->Conteudo;
                        
                    }else
                    {
                        $tagNumb = $xml->Conteudo;

                        $fileRemoved = RFIDIntegration::removeFile($arqResp);
                        if(!$fileRemoved)
                        {
                        }else
                        {
                        }
                        
                        return $tagNumb;
                    }
                    
                    //liberando o semáforo
                    sem_release($sem);
                    break;

                case RFID_REMOVE_BIT:

                    sem_acquire($sem);
                    
                    //carrega o arquivo
                    $xml = simplexml_load_file($arqInt);
                    
                    //desaloca alguns conteúdos desnecessários
                    unset($xml->Protecao);
                    unset($xml->Conteudo);
                    $xml->Comando = RFID_REMOVE_BIT;
                    //escreve o arquivo
                    file_put_contents($arqInt, $xml->asXML());
                    
                    //aguarda o software responder
                    sleep(3);

                    $xml = simplexml_load_file($arqResp);
                    if($xml->Retorno != 'SUCESS')
                    {
                        $err = $xml->Conteudo;
                    }
                    
                    sem_release($sem);
                    break;

                case RFID_ACTIVE_BIT:

                    sem_acquire($sem);
                    
                    //carrega o arquivo
                    $xml = simplexml_load_file($arqInt);
                    
                    //desaloca alguns conteúdos que não serão utilizados
                    unset($xml->Protecao);
                    unset($xml->Conteudo);
                    $xml->Comando = RFID_ACTIVE_BIT;
                    //grava o arquivo
                    file_put_contents($arqInt, $xml->asXML());
                    
                    //aguarda o software responder
                    sleep(3);

                    $xml = simplexml_load_file($arqResp);
                    if($xml->Retorno != 'SUCESS')
                    {
                        $err = $xml->Conteudo;
                    }
                    
                    sem_release($sem);
                    break;

                case RFID_STATUS_BIT:
                    
                    sem_acquire($sem);
                    //abre o arquivo
                    $xml = simplexml_load_file($arqInt);
                    
                    //desaloca algumas variáveis que não serão utilizadas
                    unset($xml->Protecao);
                    unset($xml->Conteudo);
                    $xml->Comando = RFID_STATUS_BIT;
                    
                    //escreve o arquivo
                    file_put_contents($arqInt, $xml->asXML());
                    
                    //aguarda o software responder
                    sleep(3);

                    $xml = simplexml_load_file($arqResp);
                    if($xml->Retorno != 'SUCESS')
                    {
                        $err = $xml->Conteudo;
                    }
                    sem_release($sem);
                    break;
                    
            }
            //Avalia se a operação foi realizada com sucesso
            $r = RFIDIntegration::avaliaResp($err);
            
            //Caso a resposta seja um array, é porque contém erro.            
            //Exclui o arquivo
            if(!is_array($r))
            {
                //Se entrou, não contém ERRORs
                if(file_exists($arqResp))
                {
                    $fileRemoved = RFIDIntegration::removeFile($arqResp);
                    if(!$fileRemoved)
                    {
                        return false;
                    }else
                    {
                        return true;
                    }
                }else
                {
                    return true;
                }
            }else
            {
                return $r;
            }
        }
    }

    public static function verifyStatus()
    {
        //Identifica o terminal do usuário
        $termId = $_COOKIE[RFID_COOKIE];
        
        //Identifica o arquivo de Status
        $fileStatus = PATH_RFID_INTEGRATION . "/" . $termId . "/Resp/Status.xml";
        
        //carrega o arquivo de status
        $xml = simplexml_load_file($fileStatus);
        
        if($xml->Status == '1')
        {
            return true;
        }else{
            return false;
        }
    }
    
    //Remove o arquivo
    public static function removeFile($file)
    {
        if(file_exists($file))
        {
            $var = unlink($file);
            if(!$var)
            {
                return false;
            }
            return true;
        }
    }
    
    //Avalia se o arquivo contem erros
    public function avaliaResp($err)
    {
        if($err)
        {
            //Pega valor do diretório identificado
            $termId = $_COOKIE[RFID_COOKIE];
            
            //Local do arquivo de resposta a ser excluído.
            $arqResp = PATH_RFID_INTEGRATION . "/" . $termId . "/Resp/Retorno.xml";
            

            //O leitor não foi encontrado
            if($err == 'ERR0000')
            {
                RFIDIntegration::removeFile($arqResp);
                
                $arr[] = "<b>[ERR0000]</b> Problema com o RFID. O leitor não foi encontrado.";
                return $arr;
                
            }
            
            //Mais de uma etiqueta encontrada
            if($err == 'ERR0001')
            {
                RFIDIntegration::removeFile($arqResp);
                
                $arr[] = "<b>[ERR0001]</b> Problema com o RFID. Mais de uma etiqueta encontrada.";
                return $arr;
                
            }

            //Nenhuma etiqueta encontrada 
            if($err == 'ERR0002')
            {
                RFIDIntegration::removeFile($arqResp);
                
                $arr[] = "<b>[ERR0002]</b> Problema com o RFID. Nenhuma etiqueta encontrada.";
                return $arr;
                
            }

            //Erro de comunicação com a chave de acesso
            if($err == 'ERR0003')
            {
                RFIDIntegration::removeFile($arqResp);
                
                $arr[] = "<b>[ERR0003]</b> Problema com o RFID. Erro de comunicação com a chave de acesso.";
                return $arr;
                
            }
        }else
        {
            return true;
        }
    }
}
?>
