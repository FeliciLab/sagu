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

$MIOLO->getClass('gnuteca3', 'RFIDIntegration');

class RFID
{
    public function __construct()
    {
        $miolo = MIOLO::getInstance();
    }
    
    /*
     * Verifica o Status do equipamento
     * Criado em Janeiro/2014
     * Por: Tcharles Silva
     */
    public static function verifyStatus()
    {
        $status = RFIDIntegration::verifyStatus();
        if($status)
        {
            return true;
        }else
        {
            return false;
        }
    }

    /*
     * Verifica o bit anti furto do equipamento
     * Criado em Janeiro/2014
     * Por: Tcharles Silva
     */
    public static function verifyBitAgainstTheft()
    {
        $resp = RFIDIntegration::executeCommand(PATH_RFID_INTEGRATION, RFID_STATUS_BIT);
        return $resp;
    }

    /*
     * Remove o bit anti furto do equipamento
     * Criado em Janeiro/2014
     * Por: Tcharles Silva
     */
    public static function removeBitAgainstTheft()
    {
        $resp = RFIDIntegration::executeCommand(PATH_RFID_INTEGRATION, RFID_REMOVE_BIT);
        return $resp;
    }

    /*
     * Adiciona o bit anti furto do equipamento
     * Criado em Janeiro/2014
     * Por: Tcharles Silva
     */
    public static function addBitAgainstTheft()
    {
        $resp = RFIDIntegration::executeCommand(PATH_RFID_INTEGRATION, RFID_ACTIVE_BIT);
        return $resp;
    }

    /*
     * Lê etiqueta do material
     * Criado em Janeiro/2014
     * Por: Tcharles Silva
     */
    public static function readTag()
    {
        $resp = RFIDIntegration::executeCommand(PATH_RFID_INTEGRATION, RFID_READ_TAG);
        return $resp;
    }

    /*
     * Escreve etiqueta no material
     * Criado em Janeiro/2014
     * Por: Tcharles Silva
     */
    public static function writeTag($tag)
    {
        
        $resp = RFIDIntegration::executeCommand(PATH_RFID_INTEGRATION, RFID_WRITE_TAG, $tag);
        return $resp;
    }
  
    /*
     * Retorna quantidade de terminais.
     * Criado em Janeiro/2014
     * Por: Tcharles Silva
     */
    public static function getTerms()
    {
        $cont = RFID_TERM;
        $c = 1;
        
        while($c <= $cont)
        {
            $terms[] = "Terminal  $c";
            $c++;
        }
        
        return $terms;
    }
    
    /*
     * Lança excessão para o campo de identificação
     * Criado em Janeiro/2014
     * Por: Tcharles Silva
     */
    
    public static function exibeInfo()
    {
        throw new Exception("Preencha o campo identificação!");
    }
    
}
?>
