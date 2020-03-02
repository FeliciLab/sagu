<?php

/**
 * Formulário para definir os tipos de granularidades.
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

class frmAvaDefinirTipoDeGranularidade extends AProcessForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $this->target = 'avaGranularidade';
        parent::__construct('Definir tipo de granularidade');
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
        
        $html = "<b>Atenção:</b> Este processo tem o objetivo de popular o campo \"tipo_granularidade\". Ele foi criado para facilitar a geração de relatórios. Nas versões a partir da 3.16 o campo já é populado no formulário de questões. <br> A definição do tipo de granularidade é baseada na <b>descrição</b> da granularidade.";
        
        $fields['mensagem'] = new MDiv('', $html);
        $fields['mensagem']->addStyle('text-align', 'center');
        $fields['mensagem']->addStyle('background-color', 'yellow');
        $fields['mensagem']->addStyle('padding', '10px');
        $fields['mensagem']->addStyle('border-width', '1px');
        $fields['mensagem']->addStyle('border-style', 'groove');

        $granularidade = $this->obterGranularidadesComOTipo();
        $fields['grid'] = $MIOLO->getUI()->getGrid($module, 'grdAvaDefinirTipoDeGranularidade');
        $fields['grid']->setData($granularidade);
        
        $url = $MIOLO->getCurrentURL();
        $botaoAtualizar = new MButton('reloadDefinirTipo', _M('Atualizar lista de granularidades', $module), $url);
        $botaoAplicar = new MButton('salvar', _M('Salvar', $module));
        
        $fields['botoes'] = new MDiv('', array($botaoAtualizar, $botaoAplicar));
        $fields['botoes']->addStyle('text-align', 'center');
        
        $this->addFields($fields);
    }

    /**
     * Replica o formulário.
     */
    public function salvar_click()
    {
        $module = MIOLO::getCurrentModule();
        
        $granularidade = $this->obterGranularidadesComOTipo();
        $updates = array();
        
        if ( !is_array($granularidade) )
        {
            new MMessageWarning('Não existem granularidades para serem definidas');
        }
        else
        {
            foreach ( $granularidade as $granularidade )
            {
                $updates[] = avaGranularidade::updateTipoDeGranularidade($granularidade[0], $granularidade[3] );
            }
        }
        
        if ( in_array(FALSE, $updates) )
        {
            new MMessageError(_m('Uma ou mais granularidades não foram definidas!', $module));
        }
        else
        {
            new MMessageSuccess(_M('Tipos de granularidades definidas com sucesso!', $module), false);
            $this->page->redirect(MIOLO::getCurrentURL());
        }
    }
    
    /**
     * Obtém as granularidades.
     * 
     * @return array Vetor com as granularidades.
     */
    private function obterGranularidadesComOTipo()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses( "types/$this->target.class.php", $module );
        $type = new $this->target();
        
        $granularidades = $type->search();
        
        $novaGranularidades = array();
        
        if ( is_array($granularidades) )
        {
            foreach ( $granularidades as $i => $granularidade )
            {
                $novaGranularidades[$i][] = $granularidade[0];
                $novaGranularidades[$i][] = $granularidade[1];
                $novaGranularidades[$i][] = $this->obtemDescricaoTipoGranularidade($granularidade[5]);
                $novaGranularidades[$i][] = $this->obtemNovaGranularidade($granularidade[1]);
                $novaGranularidades[$i][] = $this->obtemDescricaoTipoGranularidade($this->obtemNovaGranularidade($granularidade[1]));
            }
        }
        
        return $novaGranularidades;
    }
    
    /**
     * Obtém a descrição do tipo de granularidade.
     * 
     * @param int $tipoDeGranularidade Códigodo tipo de granularidade.
     * @return String Descrição do tipo de granularidade.
     */
    private function obtemDescricaoTipoGranularidade($tipoDeGranularidade)
    {
        $descricoes = AGranularity::getGranularityTypes();
        
        return $descricoes[$tipoDeGranularidade];
    }
    
    
    /**
     * Obtém o código do tipo de granularidade a partir da descrição da granularidade.
     * 
     * @param String $descricaoGranularidae Descrição da granularidade.
     * @return int Código do tipo de granularidade.
     */
    private function obtemNovaGranularidade($descricaoGranularidae)
    {
        $tipoGranularidade = AGranularity::GRANULARITY_TYPE_OUTRO;
        
        if ( stripos($descricaoGranularidae, 'sem granularidade') !== FALSE )
        {
            $tipoGranularidade = AGranularity::GRANULARITY_TYPE_SEM_GRANULARIDADE;
        }
        elseif  ( stripos($descricaoGranularidae, 'por curso') !== FALSE )
        {
            $tipoGranularidade = AGranularity::GRANULARITY_TYPE_POR_CURSO;
        }
        elseif  ( stripos($descricaoGranularidae, 'por disciplina') !== FALSE )
        {
            $tipoGranularidade = AGranularity::GRANULARITY_TYPE_POR_DISCIPLINA;
        }
        elseif  ( stripos($descricaoGranularidae, 'por setor') !== FALSE )
        {
            $tipoGranularidade = AGranularity::GRANULARITY_TYPE_POR_SETOR;
        }
        
        return $tipoGranularidade;
    }
    
    
}


?>