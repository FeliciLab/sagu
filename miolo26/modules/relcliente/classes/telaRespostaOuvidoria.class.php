<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Sagu.
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
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 28/09/2012
 *
 **/

$MIOLO->uses('tipos/rccMensagemOuvidoria.class.php', 'relcliente');
$MIOLO->uses('forms/frmRccMensagemOuvidoriaRespondenteBusca.class.php', 'relcliente');
$MIOLO->uses('classes/rccEmail.class.php', 'relcliente');
class telaRespostaOuvidoria extends MDialog
{
    /**
     * Método ajax para montar o popup de resposta de mensagem de ouvidoria.
     * 
     * @param stdClass Parametros do ajax.
     */
    public function __construct($mensagemDeOuvidoriaId)
    {
        $MIOLO = MIOLO::getInstance();
        
        // Verifica se já existe algum registro.
        $tipoRespostaOuvidoria = bTipo::instanciarTipo('rccRespostaOuvidoria', 'relcliente');
        $filtro = new stdClass();
        $filtro->mensagemouvidoriaid = $mensagemDeOuvidoriaId;
        $solicitacaoDeResposta = $tipoRespostaOuvidoria->buscar($filtro);
        
        $campos = array();

        $campos[] = self::obterInformacoesDoContato($mensagemDeOuvidoriaId);
        $somenteLeitura = FALSE;
        $dataResposta = $dataPrevista = date("d/m/y H:m:s");
        
        // Obtém os valores da solicitação de resposta já incluída na base de dados.
        if ( is_array($solicitacaoDeResposta) )
        {
            $respostaOuvidoriaId = $solicitacaoDeResposta[0]->respostaouvidoriaid;
            $tipoRespostaOuvidoria->popularPorMensagemOuvidoriaId($mensagemDeOuvidoriaId);
        
            $campos[] = $respostaId = new MTextField('respostaouvidoriaid', $respostaOuvidoriaId);
            $respostaId->addStyle('display', 'none');
            
            if ( strlen($solicitacaoDeResposta[0]->resposta) )
            {
                $dataPrevista = $tipoRespostaOuvidoria->datahoraprevista;
                $dataResposta = $tipoRespostaOuvidoria->datahoradaresposta;
                $respondente = $tipoRespostaOuvidoria->respondente;
                $resposta = $tipoRespostaOuvidoria->resposta;
                $origemDeContato = $tipoRespostaOuvidoria->origemdecontatoid;
                $somenteLeitura = TRUE;
            }
        }
        
        $campos[] = new MDiv('divMensagemDialogResposta');
        
        // Se n�o encontrou respondente.
        if ( is_null($respondente) )
        {
            $busPhysicalPerson = new BusinessBasicBusPhysicalPerson();
            
            $user   = $MIOLO->getLogin();
            $person = $busPhysicalPerson->getPhysicalPersonByMioloUserName($user->id);
            
            $respondente[0]->personid = $person->personId;
            
            // Se o usu�rio n�o possui pessoa f�sica associada.
            if ( is_null($respondente[0]->personid) )
            {
                $campos[] = $pessoa = new bEscolha('personid', 'basperson', 'relcliente', '', _M('Respondente'), FALSE, 'personid,name');
                $pessoa->addAttribute("style", "clear:both; width:90px");
            }
        }

        $campos[] = $dataHoraPrevista = new MTextField('datahoraprevista', $dataPrevista);
        $dataHoraPrevista->addStyle('display', 'none');
        
        $campos[] = $campoDataResposta = new MTimestampField('datahoradaresposta', $dataResposta, _M('Data da resposta'), $somenteLeitura);

        $tipoOrigemOuvidoria = bTipo::instanciarTipo('rccOrigemDeContato', 'relcliente');
        $origem = $tipoOrigemOuvidoria->buscarParaSelection();
        $campos[] = $origem = new MSelection('origemdecontatoid', $origemDeContato, _M('Origem'), $origem);
        $origem->setReadOnly($somenteLeitura);
        $campos[] = $respostaCampo = new MMUltiLineField('resposta', $resposta, _M('Resposta'), NULL, T_VERTICAL_TEXTO, T_HORIZONTAL_TEXTO);
        $respostaCampo->setReadOnly($somenteLeitura);

        // Se for somente leitura, não cria os botões.
        if ( !$somenteLeitura )
        {
            $botoes = array();
            
            $imagem = $MIOLO->getUI()->getImageTheme(NULL, 'botao_salvar.png');
            $botoes = new MButton('salvarSolicitacao', _M('Salvar'),  MUtil::getAjaxAction('responderMensagemOuvidoria', array($mensagemDeOuvidoriaId, $respondente[0]->personid)), $imagem);
            $campos[] = MUtil::centralizedDiv($botoes);
        }

        // Mostra o Popup em tela.
        parent::__construct('popupResponderMensagem', _M('Responder mensagem'), $campos);
        
        $this->show();
        
        $datahora = explode(' ', $dataResposta);
        
        $refreshEvent = "miolo.getElementById('datahoradarespostaDate').value = '{$datahora[0]}';";
        $refreshEvent .= "miolo.getElementById('datahoradarespostaTime').value = '{$datahora[1]}';";
        $refreshEvent .= "miolo.getElementById('datahoradaresposta').value = miolo.getElementById('datahoradarespostaDate').value + ' ' + miolo.getElementById('datahoradarespostaTime').value.replace('T', '');";
        $refreshEvent .= "miolo.getElementById('datahoradaresposta').value = miolo.getElementById('datahoradaresposta').value.trim();";
        $refreshEvent .= "miolo.getElementById('datahoradarespostaTime').value = miolo.getElementById('datahoradarespostaTime').value.replace('T', '');";
        
        $this->page->onLoad($refreshEvent);
    }
    
    /**
     * Método ajax para salvar a resposta de mensagem.
     * 
     * @param int $mensagemDeOuvidoriaId Código da mensagem de ouvidoria.
     */
    public static function responderMensagemOuvidoria($argumentos)
    {
        $event = explode( '&', $argumentos->__mainForm__EVENTARGUMENT);
        $eventOuvidoriaId = explode('=', $event[0]);
        $mensagemDeOuvidoriaId = $eventOuvidoriaId[1];
        
        $eventPersonId = explode('=', $event[1]);
        $personid = $eventPersonId[1];
        $argumentos->respondente = ($personid) ? $personid : MForm::getFormValue('personid');
        
        $mensagemValidacao = array();
        
        // Corrige a data e hora da resposta.
        if ( strlen($argumentos->datahoradarespostaDate) && strlen($argumentos->datahoradarespostaTime) )
        {
            $argumentos->datahoradaresposta = $argumentos->datahoradarespostaDate . ' ' . substr($argumentos->datahoradarespostaTime, 1); 
        }
        
        // Valida data e hora da resposta.
        if ( strlen($argumentos->datahoradaresposta) == 0 )
        {
            $validado = FALSE;
            $mensagemValidacao[] = _M('É necessário preencher o a data e hora da resposta'); 
        }
        
        // Valida resposta.
        if ( strlen($argumentos->resposta) == 0 )
        {
            $validado = FALSE;
            $mensagemValidacao[] = _M('É necessário preencher a resposta.');
        }
        
        // Valida respondente.
        if ( strlen($argumentos->respondente) == 0 )
        {
            $mensagemValidacao[] = _M('É necessário preencher o respondente.');
        }
        
        // Valida origem de contato.
        if ( strlen($argumentos->origemdecontatoid) == 0 )
        {
            $mensagemValidacao[] = _M('É necessário preencher a origem.');
        }
        
        // Salva a resposta caso tenha passado na validação.
        if ( count($mensagemValidacao) == 0 )
        {
            $tipoRespostaOuvidoria = bTipo::instanciarTipo('rccRespostaOuvidoria', 'relcliente');
            
            $argumentos->mensagemouvidoriaid = $mensagemDeOuvidoriaId;
            $argumentos->datahoradasolicitacao = date("d/m/Y H:m:s");
            $tipoRespostaOuvidoria->definir($argumentos);

            // Verifica se é o registro será inserido ou editado.
            if ( strlen($argumentos->respostaouvidoriaid) )
            {
                $salvou = $tipoRespostaOuvidoria->editar();
            }
            else
            {
                $salvou = $tipoRespostaOuvidoria->inserir();
            }
            
            // Verica se registro foi salvo.
            if ( $salvou )
            {
                // Envia e-mail para solicitante.
                if ( $tipoRespostaOuvidoria->origemdecontatoid == rccMensagemOuvidoria::ORIGEM_MENSAGEM_OUVIDORIA_EMAIL )
                {
                    // Instância tipo de mensagem de ouvidoria.
                    $tipoMensagemOuvidoria = bTipo::instanciarTipo('rccMensagemOuvidoria', 'relcliente');
                    $tipoMensagemOuvidoria->mensagemouvidoriaid = $mensagemDeOuvidoriaId;
                    $tipoMensagemOuvidoria->popular();
                    
                    // Envia o e-mail.
                    $email = new rccEmail();
                    $email->definirEndereco($tipoMensagemOuvidoria->email);
                    $email->definirAssunto("Resposta da ouvidoria");
                    $email->definirConteudo($tipoRespostaOuvidoria->resposta);
                    $enviar = $email->enviar();
                }
                
                new MMessageSuccess(_M('A mensagem foi respondida com sucesso.'));
                
                // Fecha a caixa de dialogo.
                MDialog::close('popupResponderMensagem');
            }
            else
            {
                new MMessageError(_M('Ocorreu um erro ao solicitar a resposta'));
            }
        }
        else
        {
            new MMessage(implode("<br/>", $mensagemValidacao), MMessage::TYPE_WARNING, true, 'divMensagemDialogResposta');
        }
    }
    
    /**
     * Obtém mensagem informativa com informações do contato.
     * 
     * @param int $mensagemOuvidoriaId Código da mensagem de ouvidoria.
     * @return MMessage Mensagem fo tipo informação. 
     */
    public static function obterInformacoesDoContato($mensagemOuvidoriaId)
    {
        $tipoMensagemOuvidoria = bTipo::instanciarTipo('rccMensagemOuvidoria', 'relcliente');
        $tipoMensagemOuvidoria->mensagemouvidoriaid = $mensagemOuvidoriaId;
        $tipoMensagemOuvidoria->popular();
        
        $tipoAssuntoDeContato = bTipo::instanciarTipo('rccassuntodecontato');
        $tipoAssuntoDeContato->assuntodecontatoid = $tipoMensagemOuvidoria->assuntodecontatoid;
        $tipoAssuntoDeContato->popular();
        
        $conteudo = "<b>Nome:</b> {$tipoMensagemOuvidoria->nome}
                     <br/><b>Telefone: </b> {$tipoMensagemOuvidoria->telefone}
                     <br/><b>E-mail: </b> {$tipoMensagemOuvidoria->email}
                     <br/><b>Matrícula: </b> {$tipoMensagemOuvidoria->matricula}
                     <br/><b>Vínculo: </b> {$tipoMensagemOuvidoria->vinculo}
                     <br/><b>Assunto: </b> {$tipoAssuntoDeContato->descricao}
                     <br/><b>Mensagem: </b> {$tipoMensagemOuvidoria->mensagem}";
         
        return MMessage::getStaticMessage('', $conteudo, MMessage::TYPE_INFORMATION);
    }
}

// Event handler.
$args = MUtil::getAjaxActionArgs();
$event = MIOLO::_REQUEST("{$MIOLO->page->getFormId()}__EVENTTARGETVALUE");

if ( $event == 'responderMensagemOuvidoria' )
{
    telaRespostaOuvidoria::responderMensagemOuvidoria($args);
}

?>