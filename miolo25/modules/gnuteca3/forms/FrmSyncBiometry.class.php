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
 * @author Luis Augusto Weber Mercado
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Luis Augusto Weber Mercado
 *
 * @since
 * Class created on 12/03/2014
 *
 * */

class FrmSyncBiometry extends GForm
{
    public $MIOLO;
    public $module;


    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        
        $this->setTransaction('gtcSyncBiometry');
        
        parent::__construct();
    }
    
    public function mainFields()
    {
        parent::mainFields();
        
        $lFields = array();
        $lFields[] = new MDiv('', '<font color="orange"><h3><p align="center">Sincronizar Biometrias</p></h3></font>');
        $lFields[] = new MDiv('', '<h4>Esta operação é responsável pela sincronização dos usuários cadastrados assim como suas digitais entre uma estação de trabalho e outra.</h4>'
                                . '<h4>Na tela abaixo, deverão ser mostradas apenas as atualizações nos arquivos de usuários cadastrados.</h4>'
                                . 'Qualquer mensagem ou tela diferente deve ser considerado como um erro. Para corrigí-los, verifique se o servidor HTTP está rodando e o seu computador está conectado a internet.</p>');
        
        $lFields[] = $btBiometricAccess = new MButton('btnSyncBio', _M('Sincronizar Biometrias', $this->module), ':syncBiometry', GUtil::getImageTheme('materialMovement-16x16.png'));
        $lFields[] = new MDiv('','<h4>O resultado aparecerá no campo abaixo: </h4>');
        $lFields[] = $divIframeBioMessage = new MDiv('divIframeBioMessage');
        
        $btBiometricAccess->addStyle('margin-top', '20px');
        $btBiometricAccess->addStyle('margin-left', '465px');
        $btBiometricAccess->addStyle('margin-bottom', '40px');
        $divIframeBioMessage->addStyle('border', '2px solid #c0c0c0');
        $divIframeBioMessage->addStyle('height','200px');
        $divIframeBioMessage->addStyle('background','#fff');
        
        $this->setFields($lFields);
        
    }
    
    public function syncBiometry() {
        $this->setResponse('<iframe style="width: 100%; height: 100%; border: none;" src="'. BIOMETRIC_URL .'sincronizar.php" scrolling="Auto"></iframe>', 'divIframeBioMessage');
        
    }
    
}