<?php

/**
 * <--- Copyright 2011-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Fermilab é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Formulário de gerenciamento de mensagem de ouvidoria.
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 10/09/2012
 */

$MIOLO->uses('forms/frmDinamico.class.php', 'base');
$MIOLO->uses('classes/rccEmail.class.php', 'relcliente');
class frmRccMensagemOuvidoria extends frmDinamico
{
    /**
     * Método reescrito para tratar os campos. 
     */
    public function definirCampos()
    {
        // Obtém os campos e validadores do formulário.
        $camposEValidadores = $this->gerarCampos();
        $campos = $camposEValidadores[0];
        $validadores = $camposEValidadores[1];
        $areaAdministrativa = true;
        
        if ( $areaAdministrativa )
        {
            $data = date('d/m/Y H:m:s');
            // Tira os campos "está cancelado" e "motivo cancelamento".
            unset($campos['estacancelada']);
            unset($campos['motivocancelamento']);
            $campos['datahora'] = new MTimestampField('datahora', $data, 'Data');

            // Chama o definirCampos do pai para definir a Toolbar.
            parent::definirCampos(FALSE);
            
            $campos[] = $estaCancelada = new MTextField('estacancelada', DB_FALSE);
            $estaCancelada->addStyle('display', 'none');
            
            $validadores[] = new MPhoneValidator('telefone', 'telefone');
            $validadores[] = new MEmailValidator('email', 'email');
        }   
    
        $this->addFields($campos);
        $this->setValidators($validadores);
    }
    
    /**
     * Método reescrito para testar se foi inserido um email ou telefone.
     */
    public function botaoSalvar_click()
    {
        $dados = $this->getData();
        
        if ( (strlen($dados->email) == 0) && (strlen($dados->telefone) == 0) )
        {
            
            $campos = array();
            $campos[] = new MDiv('divMensagemDialog');
            $divText = new MTextLabel('information', 'A falta de e-mail ou telefone impossibilita a resposta da instituição.<br> Deseja seguir mesmo assim?');
            $botoes = array();
            
            $salvar = $this->manager->getUI()->getImageTheme(NULL, 'botao_salvar.png');
            $cancelar = $this->manager->getUI()->getImageTheme(NULL, 'botao_cancelar.png');
            $botoes[] = new MButton('salvarMensagem', _M('Salvar'),  MUtil::getAjaxAction('salvarMensagem'), $salvar);
            $botoes[] = new MButton('cancelarMensagem', _M('Canela'),  MUtil::getAjaxAction('cancelarMensagem'), $cancelar);
            $campos[] = $divText;
            $campos[] = MUtil::centralizedDiv($botoes);

            // Mostra o Popup em tela.
            $caixaDialogo = new MDialog('popupconfirma', _M('Confirmação'), $campos);
            $caixaDialogo->show();
        }
        else
        {
            parent::botaoSalvar_click();
        }
    }
    
    public function salvarMensagem()
    {
        parent::botaoSalvar_click();
        MDialog::close('popupconfirma');
    }
    
    public function cancelarMensagem()
    {
        MDialog::close('popupconfirma');
        new MMessageWarning(_M('É necessário preecher pelo menos e-mail ou telefone.'));
    }
}

?>