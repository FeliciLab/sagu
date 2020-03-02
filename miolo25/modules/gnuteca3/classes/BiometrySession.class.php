<?php
/**
 * <--- Copyright 2005-2013 de Solis - Cooperativa de Soluções Livres Ltda. e
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
 * Classe para administrar sessão
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 28/11/2013
 * 
 **/

class BiometrySession
{
  /**
   * Abre uma nova sessão para autenticação/cadastro de impressão digital.
   *
   * @return int Retorna o número da sessão aberta.
   */
   public static function openSession()
   {
       $MIOLO = MIOLO::getInstance();
       $busBiometrySession = $MIOLO->getBusiness('gnuteca3', 'BusBiometrySession');

       $idsession = $busBiometrySession->insertBiometrySession();
              
       return $idsession;
   }

  /**
   * Obtém o retorno da operação que está sendo feita com o leitor biométrico.
   *
   * @param (int) $sessionId Número da sessão.
   * @return boolean Retorna true caso tenha realizado a operação com sucesso. Retorna false caso contrário.
   */
   public static function getReturn($sessionId)
   {
       $return = null;
       $count = 0;
       $MIOLO = MIOLO::getInstance();
       $busBiometrySession = $MIOLO->getBusiness('gnuteca3', 'BusBiometrySession');
       
       while (($return == null) && ($count < 20)) // Enquanto não tiver nada na coluna return e o timer ser menor que 20.
       {    
           
         $return = $busBiometrySession->getBiometrySession($sessionId);
            
         if ( $return )
         {
            $return = $return->return; // Pega o que há no campo return da tabela.
            
         }
            
         sleep(1); // Aguardar 1 segundo.
         $count++;
         
       }
       
       return $return;
       
   }

  /**
   * Abre uma nova sessão para autenticação/cadastro de impressão digital.
   *
   * @param (int) $sessionId Número da sessão que está sendo fechada.
   * @return int Retorna true caso tenha realizado a operação com sucesso. Retorna false caso contrário.
   */
   public static function closeSession($sessionId, $return)
   {
       $MIOLO = MIOLO::getInstance();
       $busBiometrySession = $MIOLO->getBusiness('gnuteca3', 'BusBiometrySession');
       $busBiometrySession->sessionId = $sessionId;
       $busBiometrySession->return = $return;

       return $busBiometrySession->updateBiometrySession();
   }

}
?>
