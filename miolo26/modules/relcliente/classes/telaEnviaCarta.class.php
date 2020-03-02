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
class telaEnviaCarta extends MDialog
{
    /**
     * Método ajax para montar o popup para geração de carta.
     * 
     * @param stdClass Parametros do ajax.
     */
    public function __construct($personid)
    {
        $MIOLO = MIOLO::getInstance();
        
        $campos[] = new MDiv('divGeraCarta');
        $campos[] = self::criaTable($personid);

            $botoes = array();

            $imagem = $MIOLO->getUI()->getImageTheme(NULL, 'botao_salvar.png');
            $botoes = new MButton('enviaCarta', _M('Gerar Carta'),  MUtil::getAjaxAction('enviarCarta', $args), $imagem);
            $campos[] = MUtil::centralizedDiv($botoes);

        // Mostra o Popup em tela.
        parent::__construct('popupGeraCarta', _M('Gerar Carta'), $campos);
        
        $this->show();
    }
    
    /**
     * Obtém tabela com pessoas que receberão a carta
     * 
     * @param int $personid,
     * @return MTableRaw,
     */
    public static function criaTable($personid)
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
        $tableRaw = new MTableRaw(_M('Pessoas selecionadas', $module), $tipoInadimplentes, array('Pessoa', 'email'));
        $tableRaw->addStyle('width', '500px');
        $tableRaw->addStyle('heigth', '500px');

        return $tableRaw;

    }
}
?>