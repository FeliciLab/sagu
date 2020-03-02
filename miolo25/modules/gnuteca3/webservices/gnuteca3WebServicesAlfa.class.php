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
 * Classe WebService para GnutecaAutomação
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.com.br]
 *
 *
 * @since
 * Class created on 04/11/2013
 * 
 **/

include("GnutecaWebServices.class.php");
$MIOLO->getClass('gnuteca3', 'GTask');

class gnuteca3WebServicesAlfa extends GWebServices
{

    /* Atributos */
    public $busPerson;
    public $busAuthenticate;
    
    /* Instanciar atributos */
    public function __construct() {
        parent::__construct();
        
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        //Instancia os atributos com o objeto Bus$Parametro respectivamente
        $this->busPerson = $MIOLO->getBusiness($module, 'BusPerson');
        $this->busAuthenticate = $MIOLO->getBusiness($module, 'BusAuthenticate');
    }
    
    /* Método para atualizar cartão do usuário
     * 
     * Parâmetros:
     *      $clientId [Autenticar no webservice]
     *      $clientPassword [Autenticar no webservice]
     *      $personId [Código da pessoa]
     *      $pwd [Senha da pessoa]
     *      $pwdWebS [Senha da funcionalidade. Preferência: PWD_UPDATEBYWEBSERVICE
     *       
     * Criado em: 06/08/2014
     * Por: Tcharles Silva
     */
    public function updatePersonInformation($clientId, $clientPassword, $personId)
    {
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod("updatePersonInformation", false))
        {
            return parent::__getErrorStr();
        }
        
        //Verifica se foram passados os parâmetros necessários
        if( !empty($personId))
        {
            /* Se atutenticar, chamar tarefas de importar pessoa e importar
             * vínculo, colocando em ambas como parâmetro, o personId. */

            //Obtem preferência IMPORTPERSON_TASK
            if(strlen(IMPORTPERSON_TASK) > 0)
            {
                $personOK = GTask::executeTaskId(IMPORTPERSON_TASK, $personId);
            }

            //Obtem preferência IMPORTLINK_TASK
            if(strlen(IMPORTLINK_TASK) > 0)
            {
                $linkOK = GTask::executeTaskId(IMPORTLINK_TASK, $personId);
            }      

            if($personOK)
            {
                if($linkOK)
                {
                    return 'true';
                }
                return '1';
            }
            else
            {
                return '2';
            }
        }
        else
        {
            return 'false';
        }
    }
}
