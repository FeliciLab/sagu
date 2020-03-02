<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/09/11
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
$MIOLO->uses('forms/frmMobile.class.php', $module);
class frmPreferenciasProfessor extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Preferências', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
		$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
		$ui = $MIOLO->getUI();
        $fields = array();
        
        $MIOLO->uses('types/PrtPreferenciaProfessor.class.php', $module);
        $preferenciaProfessor = new PrtPreferenciaProfessor($this->personid);
        
        $chks[] = $this->checkbox('notifmsgrecebida', 'Notificação por e-mail para mensagens recebidas', 'Define se o professor receberá ou não um e-mail quando receber uma nova mensagem', $preferenciaProfessor->notifmsgrecebida);
        $chks[] = $this->checkbox('notifpostagemmural', 'Notificação por e-mail para postagens no mural', 'Define se o professor receberá ou não um e-mail quando uma nova postagem for feita em seu mural', $preferenciaProfessor->notifpostagemmural);        
        $chks[] = $this->checkbox('notifconviteatividade', 'Notificação por e-mail para convites para atividades', 'Define se o professor receberá ou não um e-mail quando for adicionado como participante de uma atividade', $preferenciaProfessor->notifconviteatividade);
        $chks[] = $this->checkbox('notifsolicitacaoreposicao', 'Notificação por e-mail para avaliação de solicitação de reposição', 'Define se o professor receberá ou não um e-mail quando tiver uma solicitação de reposição de aula avaliada pelo coordenador', $preferenciaProfessor->notifsolicitacaoreposicao);        
        $chks[] = $this->checkbox('notiffinalizacaodisciplina', 'Notificação por e-mail para finalização de disciplina', 'Define se o professor receberá ou não um e-mail quando uma disciplina for finalizada pelo professor', $preferenciaProfessor->notiffinalizacaodisciplina);
        $sections[] = new jCollapsibleSection(_M('Notificações por E-mail'), $chks);
        
        $chks2[] = $this->slider('numeropostagensmural', 'Número de postagens exibidas no mural', 'Define quantas postagens serão exibidas no mural por vez', $preferenciaProfessor->numeropostagensmural);
        $sections[] = new jCollapsibleSection(_M('Mural'), $chks2);
        
        $fields[] = new jCollapsible('col_pref', $sections);
        
        $this->page->onLoad('$("input[type=\'checkbox\']").checkboxradio("refresh");');
        
        $fields[] = new MDiv();
        $fields[] = MUtil::centralizedDiv(new MButton('btnSalvar', 'Salvar', MUtil::getAjaxAction('salvar')));

        parent::addFields($fields);
    }
    
    public function salvar()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('types/PrtPreferenciaProfessor.class.php', $module);
        
        $args = $this->getAjaxData();

        $preferenciaProfessor = new PrtPreferenciaProfessor($this->personid);
        
        if ( $preferenciaProfessor->salvar($args) )
        {
            new MMessageSuccess(_M('Preferências salvas com sucesso.'));
        }
        else
        {
            new MMessageError(_M('Não foi possível salvar as preferências.'));
        }
        
        $this->setResponse(NULL, 'responseDiv');
    }

}

?>
