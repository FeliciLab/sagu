<?php

/**
 * Formulário para popular as opções das questões.
 *
 * @author Jader Fiegenbaum [jader@solis.com.br]
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.com.br]
 *
 * @since
 * Creation date 09/07/2014
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 20141 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

$MIOLO->uses('classes/agranularity.class.php', 'avinst');

class frmAvaPopularOpcoesDeQuestoes extends AProcessForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaQuestoes';
        parent::__construct('Popular opções de questões');
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $this->toolbar->hideButtons(MToolBar::BUTTON_SAVE);
        $this->toolbar->hideButtons(MToolBar::BUTTON_RESET);
        $module = MIOLO::getCurrentModule();
        $MIOLO = MIOLO::getInstance();
        
        $fields = array();
        
        $html = "<b>Atenção:</b> Este processo tem o objetivo de popular as opções das questões na base de dados. Nas versões a partir da 3.16 as opções já são armazenadas automaticamente ao salvar uma questão. <br> O processo obtém todas questões armazenadas, faz o \"unserialize\" das opções e armazena em uma tabela de opções.";
        
        $fields['mensagem'] = new MDiv('', $html);
        $fields['mensagem']->addStyle('text-align', 'center');
        $fields['mensagem']->addStyle('background-color', 'yellow');
        $fields['mensagem']->addStyle('padding', '10px');
        $fields['mensagem']->addStyle('border-width', '1px');
        $fields['mensagem']->addStyle('border-style', 'groove');

        $botaoAtualizar = new MButton('popular', _M('Popular opções', $module));
        
        $fields['botoes'] = new MDiv('', array($botaoAtualizar));
        $fields['botoes']->addStyle('text-align', 'center');
        
        $this->addFields($fields);
    }

    /**
     * Popula opções das questões.
     */
    public function popular_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses( "types/$this->target.class.php", $module );
        $type = new $this->target();
        
        $questoes = $type->search(ADatabase::RETURN_TYPE);
        $contadorDeQuestoesAtualizadas = 0;
        
        $retorno = array();
        
        if ( is_array($questoes) )
        {
            foreach ( $questoes as $questao )
            {
                $opcoes = unserialize($questao->opcoes);
                
                if ( $questao->tipo == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_MULTI_RESPOSTA ||  $questao->tipo == ADynamicFields::TIPO_QUESTAO_MULTIPLA_ESCOLHA_SELECAO )
                {
                    $opcoes = $opcoes->opcoes;
                }
                
                if ( is_array($opcoes) )
                {
                    $questao->opcoesUnserialize = $opcoes;
                    $retorno[] = $questao->update();
                    $contadorDeQuestoesAtualizadas++;
                }
            }
        }
        
        if ( in_array(FALSE, $updates) )
        {
            new MMessageError(_m('Não foi possível popular as opções das questões.!', $module));
        }
        else
        {
            new MMessageSuccess(_M('Foram populadas as opções de @1 questões com sucesso!', $module, $contadorDeQuestoesAtualizadas));
        }
        
    }
    
}

?>