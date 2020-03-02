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
 * Class created on 10/12/2012
 *
 **/

$MIOLO->uses('forms/frmDinamico.class.php', 'base');
//$MIOLO->uses('forms/frmRccInteresse.class.php', 'relcliente');

//TODO: unset no campo datahora. Mas antes deve ser corrigido o base
class frmRccInteressePortal extends frmDinamico
{
    public function __construct($parametros) {
        parent::__construct(array(
            'modulo' => 'relcliente',
            'tipo' => 'rccInteresse'
        ), 'Interesse');
    }
    
    public function definirCampos() 
    {

        parent::definirCampos(FALSE, FALSE);
        
        // Esconde banner.
        bJavascript::esconderElemento('m-container-top');
        
        // Esconde migalha.
        bJavascript::esconderElemento('__mainForm_bottom');

        //$campos[] = $personid = new MIntegerField('personid', '', _M('Código de aluno'), 20, _M('Preencha este campo com seu código de aluno, apenas se for aluno da instituição'));
        //$personid->addAttribute('onchange', MUtil::getAjaxAction('personidChange'));
        
        $campos[] = new MTextField('nome', '', _M('Nome'), 40);
        $campos[] = new MTextField('email', '', _M('Email'), 40);
        $campos[] = new MTextField('telefone', '', _M('Telefone'), 40);
        $campos[] = $cpf = new MTextField('cpf', '', _M('CPF'), 40);
        $cpf->addMask('###.###.###-##');
        $campos[] = new MMultiLineField('observacao', '', _M('Observação'), 40, 8, 50);
        
        $campos[] = new MFormContainer('divCurso');
        
        // cria capcha
        $campos[] = new MDiv('divSeparator');
        $campos[] = new MCaptchaField('captcha', _M('Digite o texto ao lado para confirmar'));
       
        // Limpa campo de capcha
        $this->page->onload(MCaptchaField::getRefreshCode('captcha') . bJavascript::definirValor('captcha', ''));
       
        $validadores[] = new MRequiredValidator('nome');
        $validadores[] = new MRequiredValidator('email');
        $validadores[] = new MEmailValidator('email');
        $validadores[] = new MPhoneValidator('telefone');
        $validadores[] = new MRequiredValidator('cpf');
        $validadores[] = new MCPFValidator('cpf');
        $validadores[] = new MRequiredValidator('observacao');
        
        $campos[] = MUtil::centralizedDiv(array(new MDiv('btnNovo')), 'divBtnNovo');

        $this->addFields($campos);
        $this->setValidators($validadores);

    }
    
    public function personidChange($args)
    {
        if ( strlen($args->personid) > 0 )
        {
            $busAluno = new BusinessBasicBusPhysicalPersonStudent();
            if (is_numeric($args->personid) )
            {
                $aluno = $busAluno->getPhysicalPersonStudent($args->personid);
            }
            
            bJavascript::definirValor('nome', $aluno->name);
            bJavascript::definirValor('email', $aluno->email);
            bJavascript::definirValor('telefone', strlen($aluno->residentialPhone) > 0 ? $aluno->residentialPhone : $aluno->cellPhone);
            bJavascript::definirValor('cpf', $aluno->personCpf);
            
            if ( strlen($aluno->personId) > 0 )
            {
                $busContract = new BusinessAcademicBusContract();
                $contratos = $busContract->listContracts($aluno->personId);
                
                if ( count($contratos) > 0 )
                {
                    $selCurso = new MSelection('contrato', $contratos[0][0], _M('Curso'), $contratos, FALSE, _M('Informe seu curso'), '', FALSE);
                    $this->setResponse(new MFormContainer('divCurso', array($selCurso)), 'divCurso');
                }
                else
                {
                    $this->setResponse(new MFormContainer('divCurso'), 'divCurso');
                }
            }
        }
        else
        {
            bJavascript::definirValor('nome', '');
            bJavascript::definirValor('email', '');
            bJavascript::definirValor('telefone', '');
            bJavascript::definirValor('cpf', '');
            
            $this->setResponse(new MFormContainer('divCurso'), 'divCurso');
        }
        
        $this->setNullResponseDiv();
    }
    
    public function getData()
    {
        $dados = parent::getData();
        $dados->mensagem = strip_tags($dados->mensagem);
        
        return $dados;
    }
    
    protected function obterBotoesPadrao()
    {
        return parent::obterBotoesPadrao(FALSE); 
    }
    
    public function botaoSalvar_click()
    {
        $dados = $this->getData();
        if ( strlen($dados->contrato) > 0 )
        {
            $busContract = new BusinessAcademicBusContract();
            $contrato = $busContract->getContract($dados->contrato);
            $dados->curso = $contrato->courseName;
        }
        
        if ( MCaptchaField::validate($dados->captcha) )
        {
            if ( $this->validate() )
            {
                parent::botaoSalvar_click();

                new MMessageSuccess(_M('Mensagem enviada com sucesso.'));
                $btn = new MButton('btnNovo', _M('Nova mensagem'));

                $this->setResponse(new MDiv('divBtnNovo', $btn), 'divBtnNovo');
            }
        }
        else
        {
            new MMessageWarning(_M('O código de verificação não é válido.'));
            
            $this->addJsCode(MCaptchaField::getRefreshCode('captcha'));
        }
        
    }
    
    public function btnNovo_click()
    {
        $MIOLO = MIOLO::getInstance();
        $url = $MIOLO->getCurrentURL();
        
        $this->page->redirect($url);
    }
}

?>