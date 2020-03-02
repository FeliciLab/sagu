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
$MIOLO->uses('forms/frmMensagens.class.php', $module);

class frmMensagensProfessor extends frmMensagens
{
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Mensagens', MIOLO::getCurrentModule()));
    }

    public function paraDestinatario()
    {
        $options[] = array(self::PARA_TODOS,'Todos os alunos de uma disciplina');
        $options[] = array(self::PARA_ALUNO,'Um aluno de uma disciplina');
        $options[] = array(self::PARA_COORDENADOR,'Coordenador de curso');
        
        return $options;
    }
    
    public function comboDisciplinas()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $disciplinas = new PrtDisciplinas();
        
        $periodos = $disciplinas->obterPeriodosProfessor($this->personid);
        
        $data_atual = new DateTime();
        
        foreach($periodos as $periodo)
        {
            $data_inicio = DateTime::createFromFormat('d/m/Y',$periodo->learningPeriodBeginDate);
            $data_fim = DateTime::createFromFormat('d/m/Y',$periodo->learningPeriodEndDate);
            
            #FIXME: ver a melhor forma para exibir somente as disciplinas do periodo letivo vigente
            //if($data_atual > $data_inicio && $data_atual < $data_fim)
            {   
                $options[] = array($periodo->groupId,$periodo->curricularComponentName);
                
            }
        }
        
        return new MSelection('disciplina', null, _M('Disciplina'), $options);
    }

}

?>
