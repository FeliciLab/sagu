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
 * Class created on 11/01/2013
 *
 **/

$MIOLO->uses('classes/rccEmail.class.php', 'relcliente');
$MIOLO->uses('tipos/rccInadimplentes.class.php', 'relcliente');
class telaEnviaEmail extends MDialog
{
    /**
     * Método ajax para montar o popup para envio de e-mail.
     * 
     * @param stdClass Parametros do ajax.
     */
    public function __construct($personid)
    {
        $MIOLO = MIOLO::getInstance();
        
        // Mostra o Popup em tela.
        if(!self::emailexiste(self::obterPessoa($personid)))
        {
            $campos[] = new MDiv('divResponderMensagem');
            $tabela = new MTableRaw(_M('Email inválido', $module), null, null);
            $campos[] = MUtil::centralizedDiv($tabela);
            
            parent::__construct('popupResponderMensagem', _M('Enviar e-mail'), $campos);
        }
        else
        {
             $campos[] = new MDiv('divResponderMensagem');
             $campos[] = self::criaTable(self::obterPessoa($personid));
          
             $botoes = array();

             $imagem = $MIOLO->getUI()->getImageTheme(NULL, 'botao_salvar.png');
             $botoes = new MButton('enviaEmail', _M('Enviar e-mail'),  MUtil::getAjaxAction('enviarEmail', $args), $imagem);
             $campos[] = MUtil::centralizedDiv($botoes);
     
             parent::__construct('popupResponderMensagem', _M('Enviar e-mail'), $campos);
        
        }
            $this->show();
    }
    
    /**
     * Obtem a tabela de pessoas e e-mails
     * 
     * @param int $personid,
     * @return MTableRaw,
     */
    public static function criaTable($tipoInadimplentes)
    {
        $tableRaw = new MTableRaw(_M('Pessoas selecionadas', $module), $tipoInadimplentes, array('Pessoa', 'email'));
        $tableRaw->addStyle('width', '500px');
        $tableRaw->addStyle('heigth', '500px');

        return $tableRaw;

    }
    public static function obterPessoa($personid)
    {
     $a;
        if (array_keys($personid) > 1)
        {
            foreach (array_keys($personid) as $key)
            {
                $a++;
                $filtros->personid = $personid[$key];
                $aux = rccInadimplentes::getPersonInfo($filtros);
                $tipoInadimplentes[$key] = $aux;
            }
        }
        else
        {
            $filtros->personid = $personid;
            $tipoInadimplentes[] = rccInadimplentes::getPersonInfo($filtros);
        }
        return $tipoInadimplentes;
    }
    
    public static function emailexiste($tipoInadimplentes)
    { 
        similar_text($tipoInadimplentes[0][1],'@naoinformado.com.br',$percent);
        if($percent > 60)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}
?>