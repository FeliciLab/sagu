<?php
/**
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 23/12/2012
 *
 **/

$MIOLO->uses('classes/telaConfirmaEnvio.class.php', 'relcliente');
$MIOLO->uses('classes/telaConfeccaoEmail.class.php', 'relcliente');
$MIOLO->uses('classes/rccEmail.class.php', 'relcliente');
$MIOLO->uses('tipos/rccMalaDireta.class.php', 'relcliente');

class frmRccMalaDiretaBusca extends bFormCadastro
{
    public function __construct() 
    {   
        parent::__construct('Mala direta', 'relcliente');
    }
    
    public function definirCampos() 
    {
        
        parent::definirCampos(TRUE);
        
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_EDITAR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_REMOVER);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_BUSCAR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_SALVAR);
        
        $fields[] = MMessage::getMessageContainer();
        
        $campos = new MSelection('selectTipo', $value, 'Tipo', array('Aluno', 'Vinculo', 'Interesse'));
        $campos->setJsHint('Selecione o grupo para o qual deseja enviar um e-mail');
        $campos->addEvent('change', ':displayOrHideExamFields');
        
        $alunos = array();
        $alunos[] = new bEscolha('personid', 'basperson', 'relcliente', null, '', false, 'personid, name');
        
        $grider = new MGrider(_M('Alunos', 'example'), $alunos, null, 'myGrider');
        $grider->addStyle('margin', '10px 0 0 195px');
       
        
        $fields[] = new MDiv('divAlunos', $grider, null, "style=\"display:none\"");
        
        $label = new MLabel('Vínculo: ');          
        $label->addStyle('margin', '0 0 0 12.5%');
        $vinculo[] = $label;
        $vinculo[] = new MSelection('selectVinculo', $value, 'Vínculo', array('Professores', 'Alunos', 'Funcionarios', 'Todos'));
        $fields[] = new MDiv('divVinculo', $vinculo, null, "style=\"display:none\"");
                
        //TODO interesse deve vir de uma tabela de cursos que ainda não existe
        $label2 = new MLabel('Interesse: ');
        $label2->addStyle('margin', '0 0 0 11.9%');
        $interesse[] = $label2;
        $interesse[] = new MSelection('selectInteresse', $value, 'Interesse', rccMalaDireta::selectcurso($args));

        $fields[] = new MDiv('divInteresse', $interesse, null, "style=\"display:none\"");

        $btn = new MButton('btnEnviar', 'Enviar');
        $btn->addStyle('margin', '0 0 0 19%');

        $this->addField($campos);
        $this->addFields($fields);   
        $this->setButtons($btn);
    }
    
    protected function obterBotoesPadrao()
    {
        return parent::obterBotoesPadrao(FALSE, FALSE); 
    }
    
   /**
   * Método que define os campos que estão visiveis ou invisiveis,
   * não retorna, apenas da um display none ou block com AJAX
   * 
   * @param $args
   */
    public function displayOrHideExamFields($args) 
    {   
        //Esconde todos os campos caso --selecione-- esteja selecionado
        if ( $args->selectTipo == '' ) 
        {
            $display = 'none';
            $displayVin = 'none';
            $displayInt = 'none';
        } 
        else if ( $args->selectTipo == 0 ) 
        {
            $display = 'block';
            $displayVin = 'none';
            $displayInt = 'none';
        }
        else if ( $args->selectTipo == 1 )  
        {
            $display = 'none';
            $displayVin = 'block';
            $displayInt = 'none';
        }
        else
        {
            $display = 'none';
            $displayVin = 'none';
            $displayInt = 'block';
        }

        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onload("document.getElementById('divAlunos').style.display = '{$display}';");    
        $MIOLO->page->onload("document.getElementById('divVinculo').style.display = '{$displayVin}';");    
        $MIOLO->page->onload("document.getElementById('divInteresse').style.display = '{$displayInt}';");    

        $this->setResponse(NULL, 'responseDiv');
    }
    
    
    public function btnEnviar_click($args)
    {
        $emails = rccMalaDireta::getInfo($args);
        
        if ($emails == '')
        {
            new MMessageWarning('Nenhuma informação selecionada.');
        }
        else
        {
            new telaConfirmaEnvio($emails);
        }
    }
    
    public function confeccaoEmail($args)
    {        
            $emails = rccMalaDireta::getInfo($args);

            foreach( $emails as $email )
            {
                $params[] = $email[1];
            }
            
            new telaConfeccaoEmail(implode(',', $params));
    }
    
    public static function enviarEmail($argumentos)
    {       
        if ( strlen($argumentos->emails) == 0 )
        {
            new MMessageWarning(_M('É necessário preencher o campo e-mail.'));
        }
        else
        {        
            // Envia o e-mail.
            $email = new rccEmail(rccEmail::EMAIL_MALA_DIRETA);
            $email->adicionarDestinatario($argumentos->emails);
            $email->definirAssunto(_M("Mala direta"));
            $email->definirConteudo($argumentos->mensagem);
            $enviar = $email->enviar();
            
            if ( $enviar )
            {
                new MMessageSuccess(_M('A mensagem foi enviada com sucesso.'));
            }
            else
            {
                new MMessageError(_M('Não foi possível enviar a mensagem.'));
            }
        }
        
        // Fecha as caixas de dialogo.
        MDialog::close('popupConfirmaEnvio');
        MDialog::close('popupDesenvolvimentoEmail');
    }
    
}
?>