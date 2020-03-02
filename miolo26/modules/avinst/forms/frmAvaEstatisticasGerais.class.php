<?php

/**
 * Formulário para inserir, editar e remover registros da tabela ava_avaliacao.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 * 
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 18/11/2011
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class frmAvaEstatisticasGerais extends AForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/amanagementwidgets.class.php', 'avinst');
        $MIOLO->uses('types/avaAvaliacao.class.php', 'avinst');
        parent::__construct('Painel administrativo');
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        $fields[] = MMessage::getMessageContainer();
        $fields[] = MPopup::getPopupContainer();
        $avaAvaliacao = new avaAvaliacao();
        $avaliacoesAbertas = $avaAvaliacao->getAvaliacoesAbertas(ADatabase::RETURN_OBJECT);
        // Obtém as avaliações abertas
        if (is_array($avaliacoesAbertas))
        {
            // Para cada avaliação aberta, coloca as informações gerais e os widgets dela
            foreach ($avaliacoesAbertas as $avaliacaoAberta)
            {
                // Para cada
                $fieldsI1[] = new MDiv('periodo'.$avaliacaoAberta->idAvaliacao, 'O período da avaliação vai de '.$avaliacaoAberta->dtInicio.' à '.$avaliacaoAberta->dtFim.'.');
                $maisInfo = new MDiv('maisInfo_'.$avaliacaoAberta->idAvaliacao, 'Mais informações...');
                $maisInfo->setClass('avinstLinkPanel');
                $args['idAvaliacao'] = $avaliacaoAberta->idAvaliacao;
                $maisInfo->addAttribute('onClick', MUtil::getAjaxAction('maisInfoAvaliacao', $args));
                $fieldsI1[] = $maisInfo;
                $fieldsI[] = new MHcontainer('avaliacaoInfo_'.$avaliacaoAberta->idAvaliacao, $fieldsI1);
                $fieldsI[] = new MDiv('avaliacaoMaisInfo_'.$avaliacaoAberta->idAvaliacao, null);
                
                $formularios = $avaliacaoAberta->getFormularios();
                if (is_array($formularios))
                {
                    foreach ($formularios as $formulario)
                    {
                        $params = new stdClass();
                        $params->idAvaliacao = $avaliacaoAberta->idAvaliacao;
                        $params->idFormulario = $formulario->idFormulario;
                        $widgets = new AManagementWidgets($params);
                        
                        if ($widgets->hasWidgets())
                        {
                            $fieldsC[] = new MHContainer('hct'.$params->idAvaliacao, $widgets->returnWidgets());
                        }
                        else
                        {
                            $fieldsC[] = new MDiv('divAviso'.$params->idAvaliacao, 'Não há estatísticas administrativas habilitadas para esta avaliação');
                        }
                        $fieldsI[] = new MPanel('panel'.$avaliacaoAberta->idAvaliacao.$formulario->idFormulario, 'Formulário '.$formulario->nome, $fieldsC);
                        unset($fieldsC);
                    }
                    $fields[] = new MBaseGroup('avaliacao'.$avaliacaoAberta->idAvaliacao, $avaliacaoAberta->nome, $fieldsI, 'vertical');
                    unset($fieldsI);
                }
            }
        }
        $this->addFields($fields);
        $this->setShowPostButton( FALSE );
    }

    //
    // Coloca mais informações para a avaliação repassada como argumento
    //
    public function maisInfoAvaliacao()
    {
        $idAvaliacao = MUtil::getAjaxActionArgs()->idAvaliacao;
        
        //
        // TODO: Implementar informações acerca da avaliação aqui
        //
        $textoTemporario = 'Informações da avaliação '.$idAvaliacao;
        $this->setResponse($textoTemporario, 'avaliacaoMaisInfo_'.$idAvaliacao);
    }
}
?>