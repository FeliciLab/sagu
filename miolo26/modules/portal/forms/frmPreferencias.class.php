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
$MIOLO->uses('classes/prtUsuario.class.php', $module);

class frmPreferencias extends frmMobile
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
        
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
        {
            $MIOLO->uses('types/PrtPreferenciaAluno.class.php', $module);
            $preferenciaAluno = new PrtPreferenciaAluno($this->personid);

            $chks[] = $this->checkbox('notifmsgrecebida', 'Notificação por e-mail para mensagens recebidas', 'Define se o aluno receberá ou não um e-mail quando receber uma nova mensagem', $preferenciaAluno->notifmsgrecebida);
            $chks[] = $this->checkbox('notifpostagemmural', 'Notificação por e-mail para postagens no mural', 'Define se o aluno receberá ou não um e-mail quando uma nova postagem for feita em seu mural', $preferenciaAluno->notifpostagemmural);
            $chks[] = $this->checkbox('notifregistrofrequencia', 'Notificação por e-mail para registro de frequência', 'Define se o aluno receberá ou não um e-mail quando sua frequência for registrada', $preferenciaAluno->notifregistrofrequencia);
            $chks[] = $this->checkbox('notifregistronota', 'Notificação por e-mail para registro de nota', 'Define se o aluno receberá ou não um e-mail quando sua nota for registrada', $preferenciaAluno->notifregistronota);
            $chks[] = $this->checkbox('notiffinalizacaodisciplina', 'Notificação por e-mail para finalização de disciplina', 'Define se o aluno receberá ou não um e-mail quando uma disciplina for finalizada pelo professor', $preferenciaAluno->notiffinalizacaodisciplina);
            $sections[] = new jCollapsibleSection(_M('Notificações por E-mail'), $chks);

            //$chks2[] = $this->slider('numeropostagensmural', 'Número de postagens exibidas no mural', 'Define quantas postagens serão exibidas no mural por vez', $preferenciaAluno->numeropostagensmural);
            $fldsNumeroPostagens[] = new MLabel(_M('Número de postagens exibidas no mural'));
            $fldsNumeroPostagens[] = new MIntegerField('numeropostagensmural', $preferenciaAluno->numeropostagensmural);
            $sections[] = new jCollapsibleSection(_M('Mural'), $fldsNumeroPostagens);

            $fields[] = new jCollapsible('col_pref', $sections);

            $this->page->onLoad('$("input[type=\'checkbox\']").checkboxradio("refresh");');
        }
        elseif ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            $MIOLO->uses('types/PrtPreferenciasCoordenador.class.php', $module);
            $preferenciaAluno = new PrtPreferenciasCoordenador($this->personid);
            
            $filtros = new stdClass();
            $filtros->personid = $this->personid;
            $preferencias = $preferenciaAluno->buscar($filtros);
            $preferencias = $preferencias[0];
            
            $label = new MLabel(_M('Ser informado por email ao receber nova mensagem?'));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('margin-top', '8px');
            $label->addStyle('margin-left', '10px');
            $sel = new MSelection('notificacaonovamensagem', $preferencias->notificacaonovamensagem, NULL, array('t' => 'SIM', 'f' => 'NÃO'));
            $fields[] = new MHContainer('contEmail', array($label, $sel));
            
            $label = new MLabel(_M('Ser notificado por email quando adicionado como participante em uma atividade?'));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('margin-top', '8px');
            $label->addStyle('margin-left', '10px');
            $sel = new MSelection('notificacaoconviteatividade', $preferencias->notificacaoconviteatividade, NULL, array('t' => 'SIM', 'f' => 'NÃO'));
            $fields[] = new MHContainer('contEmail', array($label, $sel));
            
            $label = new MLabel(_M('Ser notificado por email quando tiver uma nova solicitação de reposição de aula a ser avaliada?'));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('margin-top', '8px');
            $label->addStyle('margin-left', '10px');
            $sel = new MSelection('notificacaosolicitacaoreposicao', $preferencias->notificacaosolicitacaoreposicao, NULL, array('t' => 'SIM', 'f' => 'NÃO'));
            $fields[] = new MHContainer('contEmail', array($label, $sel));
        }
        
        $fields[] = new MDiv();
        $fields[] = MUtil::centralizedDiv(new MButton('btnSalvar', 'Salvar', MUtil::getAjaxAction('salvar')));

	parent::addFields($fields);
    }
    
    public function salvar()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('types/PrtPreferenciaAluno.class.php', $module);
        $MIOLO->uses('types/PrtPreferenciasCoordenador.class.php', $module);
        
        $args = $this->getAjaxData();
        $objPreferencia = NULL;
        
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_COORDENADOR )
        {
            $objPreferencia = new PrtPreferenciasCoordenador($this->personid);
        }
        else
        {
            $objPreferencia = new PrtPreferenciaAluno($this->personid);
        }
        
        if ( $objPreferencia->salvar($args) )
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
