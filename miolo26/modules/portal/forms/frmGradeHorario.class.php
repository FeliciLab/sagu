<?php

/**
 * Formulário de exibição da grade de horários.
 *
 * @author Bruno E. Fuhr [bruno@solis.com.br]
 * @since 20/11/2013
 * @version 3.9
 *
 * \b Maintainers: \n
 * Jader O. Fiegenbaum [jader@solis.com.br]
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2013 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

// Estouro de memória, ticket #38072
ini_set('memory_limit', '10240M');
ini_set('max_execution_time', '0');

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('db/BusLearningPeriod.class', 'academic');
$MIOLO->uses('db/BusGroup.class', 'academic');
$MIOLO->uses('db/BusEnroll.class', 'academic');
$MIOLO->uses('types/AcpOcorrenciaHorarioOferta.class', 'pedagogico');
$MIOLO->uses('db/BusPerson.class', 'basic');

class frmGradeHorario extends frmMobile
{
    /**
     * @var string
     */
    private $minDate;
    
    /**
     * @var string
     */
    private $maxDate;
    
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Grade de horários', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
        {
            $label = new MLabel(_M('Data de início: '));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('width', '150px');
            
            $dtInicio = new MCalendarMobileField('dataInicio','');
            $fields[] = $ctn = new MHContainer('div',array($label, $dtInicio));
            $ctn->addStyle('margin-left', '38%');
            $ctn->addStyle('width', '100%');
            
            $label = new MLabel(_M('Data de fim: '));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('width', '150px');
            
            $dtFim = new MCalendarMobileField('dataFim','');
            $fields[] = $ctn = new MHContainer('div',array($label, $dtFim));
            $ctn->addStyle('margin-left', '38%');
            $ctn->addStyle('width', '100%');
            
            $label = new MLabel('<label> </label>');
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('width', '150px');
            
            $fields[] = new MDiv();
            $button = new MButton('btnGerarGrade', _M('Buscar'));
            $fields[] = $ctn = new MHContainer('div',array($label, $button));
            $ctn->addStyle('margin-left', '38%');
            $ctn->addStyle('width', '100%');
        }
        
        $table = $this->generateTable();
        
        if ( $table )
        {
            $fields[] = new MDiv('divGrade', $table);
        }
        else
        {
            $fields[] = MMessage::getStaticMessage('msgError', _M('Não há informações a exibir, pois não existem horários cadastrados para as disciplinas selecionadas. Por favor, certifique-se de que os horários para as disciplinas estejam configurados.'), MMessage::TYPE_WARNING);
        }

        parent::addFields($fields);
    }
    
    /**
     * 
     * @return MTableRaw
     */
    public function generateTable($args)
    {
        $learningPeriod = new BusinessAcademicBusLearningPeriod();
        $busGroup = new BusinessAcademicBusGroup();
        $busEnroll = new BusinessAcademicBusEnroll();
        
        $disciplinas = array();
        if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
        {
            $disciplinasDoProfessor = $busGroup->obterDisciplinasDoProfessor($this->personid);
            foreach ( $disciplinasDoProfessor as $disciplinaDoProfessor )
            {
                $disciplinas[$disciplinaDoProfessor[7]] = $disciplinaDoProfessor[8];
            }
        }
        else
        {
            $disciplinasDaPessoa = $busGroup->obterDisciplinasDaPessoaPorPeriodo($this->personid, $learningPeriod->obterPeriodoAtual());
            foreach ( $disciplinasDaPessoa as $disciplinaDaPessoa )
            {
                $enroll = $busEnroll->getEnroll($disciplinaDaPessoa[0]);
                $curriculumId = $enroll->curriculumId;

                $disciplinas[$disciplinaDaPessoa[1]] = $curriculumId;
            }
        }
        
        $disciplinasAcademico = $this->getSchedulesArray($disciplinas, $args->dataInicio, $args->dataFim);
        if( count($disciplinasAcademico) > 0 )
        {
            $table = $this->generateSchedulesArray($disciplinasAcademico);
            $table->setWidth('100%');
        }
        
        $disciplinasPedagogico = $this->getSchedulesArrayPedagogico($disciplinas, $args->dataInicio, $args->dataFim);
        if( count($disciplinasPedagogico) > 0 )
        {
            $table = $this->generateSchedulesArrayPedagogico($disciplinasPedagogico);
            $table->setWidth('100%');
        }
        
        return $table;
    }
    
    public function btnGerarGrade_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
                
        if(!MIOLO::_REQUEST('dataInicio') && !MIOLO::_REQUEST('dataFim'))
        {
            $args->dataInicio = '';
            $args->dataFim = '';
            $this->minDate = '';
            $this->maxDate = '';
            
            $MIOLO->page->addJsCode("document.getElementById('dataInicio').value = ''");
            $MIOLO->page->addJsCode("document.getElementById('dataFim').value = ''");
            
            $table = $this->generateTable($args);
            $this->setResponse($table, 'divGrade');  
        }
        else
        {
            if(strlen(MIOLO::_REQUEST('dataInicio')) == 0)
            {
                $args->dataInicio = '';
            }
            if(strlen(MIOLO::_REQUEST('dataFim')) == 0)
            {
                $args->dataFim = '';
            }
            $this->minDate = $args->dataInicio;
            $this->maxDate = $args->dataFim;
            
            $MIOLO->page->addJsCode("document.getElementById('dataInicio').value = ''");
            $MIOLO->page->addJsCode("document.getElementById('dataFim').value = ''");
                     
            $table = $this->generateTable($args);
            $this->setResponse($table, 'divGrade');
        }
        
        $args->dataInicio = '';
        $args->dataFim = '';
        $this->minDate = '';
        $this->maxDate = '';
        $_REQUEST['dataInicio']  = '';
        $_REQUEST['dataFim']  = '';
        
    }
        
    
    public function generateSchedulesArray($schedulesArray)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = 'academic';
        
        if ( count($schedulesArray) > 0 )
        {
            $busGroup = new BusinessAcademicBusGroup();

            $weekDays = array();
            $turns    = array();

            foreach ( $schedulesArray as $weekdayId => $schedulesData )
            {
                if ( ! array_key_exists($weekdayId, $weekDays) )
                {
                    $bString = new BString($schedulesData->description, 'ISO-8859-1');
                    $weekDays[$weekdayId] = $bString->getString();
                }
                
                if ( count($schedulesData->turns) > 0 )
                {
                    foreach ( $schedulesData->turns as $turnId => $turnData )
                    {
                        if ( ! array_key_exists($turnId, $turns) )
                        {
                            $turns[$turnId] = $turnData->description;
                        }
                    }
                }
            }
            
            $columns = array();
            $data = array();
            
            $j = 1;
            
            if ( (count($weekDays) > 0) && (count($turns) > 0) )
            {
                $columns[0] = '&nbsp';
                
                foreach ( $weekDays as $weekDayId => $weekDayDescription )
                {
                    foreach ( $turns as $turnId => $turnDescription )
                    {
                        $scheduleData = $schedulesArray[$weekDayId]->turns[$turnId];
                   
                        if ( (isset($scheduleData)) && (count($scheduleData->schedules) > 0) )
                        {
                            // tenta extrair a data (occurrenceDate) do array
                            $scheduleDate = current((array)$scheduleData->schedules)->occurrenceDate;
                            
                            // exibe a (data) apos o dia da semana, ex.:
                            // Quinta-feira (19/02/2015)
                            if ( strlen($scheduleDate) > 0 )
                            {
                                $weekDayDescription .= ' (' . $scheduleDate . ')';
                            }
                            
                            $columns[$weekDayId] = '<center><b>' . $weekDayDescription . '</b></center>';
                            $data[$turnId][0] = '<center><b>' . $turnDescription . '</b></center>';
                            
                            $text = array();
                            $counter = 0;
                            
                            foreach ( $scheduleData->schedules as $scheduleId => $turnSchedule )
                            {
                                $groupData = $busGroup->getGroup($turnSchedule->groupId);
                                
                                $text[$counter] .= '<center><b>' . $turnSchedule->beginHour . '-' . $turnSchedule->endHour . '</b></center><center>' . $groupData->curriculumCurricularComponentName . '</center>';

                                $filters->scheduleId = $scheduleId;
                                
                                if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
                                {
                                    $professors = $turnSchedule->professors;

                                    if ( count($professors) > 0 )
                                    {
                                        foreach ( $professors as $professorData )
                                        {
                                            $text[$counter] .= '<center><i>' . $professorData . '</i></center>';
                                        }
                                    }
                                    else
                                    {
                                        $text[$counter] .= '<center><i>' . _M('Sem professor definido', $module) . '</i></center>';
                                    }
                                }
                                
                                $place = '';
                                if ( strlen($turnSchedule->place) > 0 )
                                {
                                    $place = '<center>' . $turnSchedule->place;
                                    
                                    if ( strlen($turnSchedule->unit) > 0 )
                                    {
                                        $place .= ' - ' . $turnSchedule->unit;
                                    }
                                    
                                    $place .= '</center>';
                                }
                                elseif ( strlen($turnSchedule->unit) > 0 )
                                {
                                    $place = '<center>' . $turnSchedule->unit . '</center>';
                                }
                                
                                if ( strlen($place) > 0 )
                                {
                                    $text[$counter] .= $place;
                                }
                                
                                $counter++;
                            }
                            
                            sort($text);
                            $data[$turnId][$weekDayId] = implode('<br>', $text);
                        }
                    }
                }
            }
             
            $data2 = array();
            $data3 = array();
            if ( count($data) > 0 )
            {
                foreach ( $data as $dataKey => $arrayRow )
                {
                    foreach ( $arrayRow as $key => $value )
                    {
                        foreach ( $data as $dataKey2 => $arrayRow2 )
                        {
                            foreach ( $arrayRow2 as $key2 => $value2 )
                            {
                                if ( ! array_key_exists($key2, $arrayRow) )
                                {
                                    $arrayRow[$key2] = '&nbsp';
                                }
                                
                                if ( ! array_key_exists($key, $arrayRow2) )
                                {
                                    $arrayRow2[$key] = '&nbsp';
                                }
                            }
                        }
                        
                        $data2[$dataKey2] = $arrayRow2;
                    }
                    
                    $data2[$dataKey] = $arrayRow;
                }
                
                ksort($data2);
                $j = 0;
                
                foreach ( $data2 as $dataRow )
                {
                    $i = 0;
                    ksort($dataRow);
                                        
                    foreach ( $dataRow as $dataElement )
                    {
                        $data3[$j][$i] = $dataElement;
                        $i++;
                    }

                    $j++;
                }
            }
            
            if ( count($columns) > 0 )
            {
                $i = 0;
                $columns2 = array();
                
                foreach ( $columns as $column ) 
                {
                    $columns2[$i] = $column;
                    $i++;
                }
            }
            
        }

        if ( count($columns2) > 0 && count($data3) > 0 )
        {
	        $schedulesTable = new MTableRaw(_M('Horário do período '.$this->minDate.'::'.$this->maxDate, $module), $data3, $columns2);
	        $schedulesTable->setAlternate(true);
        }
        else
        {
        	$data3 = array(_M('Ops! Nenhum resultado encontrado'));
        	$schedulesTable = new MTableRaw(_M('Horário do período '.$this->minDate.'::'.$this->maxDate, $module), $data3);
        }
        
        return $schedulesTable;
    }
    
    public function getSchedulesArray($groups, $beginDate = null, $endDate = null)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = 'academic';

        $busSchedule = new BusinessAcademicBusSchedule();

        $tmp = array();
        
        if ( count($groups) > 0 )
        {
            foreach ( $groups as $groupId => $curriculumId )
            {
                $schedulesData = $busSchedule->getGroupScheduleDataByDate($groupId, $beginDate, $endDate);
                
                if ( count($schedulesData) > 0 )
                {
                    foreach ( $schedulesData as $occurrenceDate => $scheduleData )
                    {
                        $tmp[$scheduleData->weekday->id]->description = $scheduleData->weekday->description;
                    
                        foreach ( $scheduleData->units as $unitId => $unit )
                        {
                            foreach ( $unit->turns as $turnId => $turn )
                            {
                                $tmp[$scheduleData->weekday->id]->turns[$turnId]->description = $turn->description;
                            
                                foreach ( $turn->times as $timeId => $time )
                                {
                                    $tmp[$scheduleData->weekday->id]->turns[$turnId]->schedules[$timeId]->beginHour = $time->beginHour;
                                    $tmp[$scheduleData->weekday->id]->turns[$turnId]->schedules[$timeId]->endHour = $time->endHour;
                                    $tmp[$scheduleData->weekday->id]->turns[$turnId]->schedules[$timeId]->groupId = $groupId;
                                    $tmp[$scheduleData->weekday->id]->turns[$turnId]->schedules[$timeId]->unit = $unit->description;
                                    $tmp[$scheduleData->weekday->id]->turns[$turnId]->schedules[$timeId]->professors = $time->professors;
                                    $tmp[$scheduleData->weekday->id]->turns[$turnId]->schedules[$timeId]->occurrenceDate = $occurrenceDate;
                                }
                            }

                            ksort($tmp[$scheduleData->weekday->id]->turns);
                        }
                    }
                }
            }

            ksort($tmp);
        }
        
        // Pega a data mínima e máxima
        foreach ( $tmp as $dia )
        {
            foreach ( $dia->turns as $turno )
            {
                foreach ( $turno->schedules as $horario )
                {
                    if ( SAGU::compareTimestamp($horario->occurrenceDate, '>', $this->maxDate, SAGU::getParameter('BASIC', 'MASK_DATE')) || 
                         !(strlen($this->maxDate) > 0) )
                    {
                        $this->maxDate = $horario->occurrenceDate;
                    }
                    
                    if ( SAGU::compareTimestamp($horario->occurrenceDate, '<', $this->minDate, SAGU::getParameter('BASIC', 'MASK_DATE')) || 
                         !(strlen($this->minDate) > 0) )
                    {
                        $this->minDate = $horario->occurrenceDate;
                    }
                }
            }
        }
        
        return $tmp;   
    }
    
    public function generateSchedulesArrayPedagogico($schedulesArray)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = 'academic';

        if ( count($schedulesArray) > 0 )
        {

            $weekDays = array();
            $turns    = array();

            foreach ( $schedulesArray as $weekdayId => $schedulesData )
            {
                if ( ! array_key_exists($weekdayId, $weekDays) )
                {
                    $schedulesData->description = BString::validaCaracteres($schedulesData->description, false);
                    $bString = new BString($schedulesData->description, 'ISO-8859-1');
                    $weekDays[$weekdayId] = $bString->getString();
                }
                
                if ( count($schedulesData->turns) > 0 )
                {
                    foreach ( $schedulesData->turns as $turnId => $turnData )
                    {
                        if ( ! array_key_exists($turnId, $turns) )
                        {
                            $turns[$turnId] = $turnData->description;
                        }
                    }
                }
            }
        
            $columns = array();
            $data = array();
            
            $j = 1;
            
            if ( (count($weekDays) > 0) && (count($turns) > 0) )
            {
                $columns[0] = '&nbsp';
                
                foreach ( $weekDays as $weekDayId => $weekDayDescription )
                {
                    foreach ( $turns as $turnId => $turnDescription )
                    {
                        $scheduleData = $schedulesArray[$weekDayId]->turns[$turnId];
                        if ( (isset($scheduleData)) && (count($scheduleData->schedules) > 0) )
                        {
                            $columns[$weekDayId] = '<center><b>' . $weekDayDescription . '</b></center>';
                            $data[$turnId][0] = '<center><b>' . $turnDescription . '</b></center>';
                            
                            $text = array();
                            $counter = 0;
                            
                            foreach ( $scheduleData->schedules as $scheduleId => $turnSchedule )
                            {
                                
                                $text[$counter] .= '<center><b>'. $turnSchedule->data. ' ' . $turnSchedule->beginHour . '-' . $turnSchedule->endHour . '</b></center><center>' . $turnSchedule->descricao . '</center>';
                                if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_ALUNO )
                                {
                                    if ( strlen($turnSchedule->professors) > 0 )
                                    {
                                        $text[$counter] .= '<center><i>' . $turnSchedule->professors . '</i></center>';
                                    }
                                    else
                                    {
                                        $text[$counter] .= '<center><i>' . _M('Sem professor definido', $module) . '</i></center>';
                                    }
                                }
                                
                                $place = '';
                                if ( strlen($turnSchedule->place) > 0 )
                                {
                                    $place = '<center>' . $turnSchedule->place;
                                    
                                    if ( strlen($turnSchedule->unit) > 0 )
                                    {
                                        $place .= ' - ' . $turnSchedule->unit;
                                    }
                                    
                                    $place .= '</center>';
                                }
                                elseif ( strlen($turnSchedule->unit) > 0 )
                                {
                                    $place = '<center>' . $turnSchedule->unit . '</center>';
                                }
                                
                                if ( strlen($turnSchedule->assunto) > 0 )
                                {
                                    // Parâmetros passado para montar o assunto de cada horário
                                    $args = array(strip_tags($turnSchedule->data) . '&&' .
                                                  strip_tags($turnSchedule->ocorrenciaHorarios). '&&' . 
                                                  strip_tags($turnSchedule->professors)
                                                 );
                                    
                                    $link = new MLink('link',null,null,_M('Ver assunto'));    
                                    $link->addAttribute('onClick', MUtil::getAjaxAction('infoDisciplinaPedagogico', (array) $args));
                                    $place .= '<center>'.$link->generate().'</center>';
                                }
                                
                                if ( strlen($place) > 0 )
                                {
                                    $text[$counter] .= $place;
                                }
                                
                                $counter++;
                            }

                            $data[$turnId][$weekDayId] = implode('<br>', $text);
                        }
                    }
                }
            }
             
            $data2 = array();
            $data3 = array();
            if ( count($data) > 0 )
            {
                foreach ( $data as $dataKey => $arrayRow )
                {
                    foreach ( $arrayRow as $key => $value )
                    {
                        foreach ( $data as $dataKey2 => $arrayRow2 )
                        {
                            foreach ( $arrayRow2 as $key2 => $value2 )
                            {
                                if ( ! array_key_exists($key2, $arrayRow) )
                                {
                                    $arrayRow[$key2] = '&nbsp';
                                }
                                
                                if ( ! array_key_exists($key, $arrayRow2) )
                                {
                                    $arrayRow2[$key] = '&nbsp';
                                }
                            }
                        }
                        
                        $data2[$dataKey2] = $arrayRow2;
                    }
                    
                    $data2[$dataKey] = $arrayRow;
                }
                
                ksort($data2);
                $j = 0;

                foreach ( $data2 as $dataRow )
                {
                    $i = 0;
                    ksort($dataRow);
                                        
                    foreach ( $dataRow as $dataElement )
                    {
                        $data3[$j][$i] = $dataElement;
                        $i++;
                    }

                    $j++;
                }
            }
            
            if ( count($columns) > 0 )
            {
                $i = 0;
                $columns2 = array();
                
                foreach ( $columns as $column ) 
                {
                    $columns2[$i] = $column;
                    $i++;
                }
            }
        }

        if ( count($columns2) > 0 && count($data3) > 0 )
        {
            $schedulesTable = new MTableRaw(_M('Horário do período entre '.$this->minDate.' e '.$this->maxDate, $module), $data3, $columns2);
            $schedulesTable->setAlternate(true);
        }
        else
        {
            $data3 = array(_M('Ops! Nenhum resultado encontrado'));
            $schedulesTable = new MTableRaw(_M('Horário do período '.$this->minDate.'::'.$this->maxDate, $module), $data3);
        }
        
        return $schedulesTable;
    }
    
    public function getSchedulesArrayPedagogico($disciplinasPedagogico = null,$beginDate = null, $endDate = null)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = 'portal';

        //Busca turmas pedagógico e equivale com periodos do academico
        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        $prtDisciplinasPedagogico = new PrtDisciplinasPedagogico();
        
        $tmp = array();
        
        if( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_PROFESSOR )
        {
            $gradedehorarios = $prtDisciplinasPedagogico->obterGradeDeHorariosDoProfessor($this->personid);
        }
        else
        {
            $gradedehorarios = $prtDisciplinasPedagogico->obterGradeDeHorariosDoAluno($this->personid, NULL, $beginDate, $endDate);
        }

        foreach ($gradedehorarios as $cod =>$data)
        {
            $ofertacomponentecurricular = new AcpOfertaComponenteCurricular($data[0]);
            $ocorrenciacurso = $ofertacomponentecurricular->ofertaturma->ofertacurso->ocorrenciacurso;
            
            $tmp[$data[2]]->description = $data[3];
            $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->description = $ocorrenciacurso->turn->description;
            
            $sala = '';
            if ( strlen($data[10]) > 0 )
            {
                $sala = ' (' . $data[10] . ') ';
            }
            
            if( strlen($tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->beginHour) > 0 ||
                strlen($tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->endHour) > 0 )
            {
                $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->endHour .= '</br>' . $data[4] . '-' . $data[5] . $sala;
            }
            else
            {
                $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->beginHour = '</br>' . $data[4];
                $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->endHour = $data[5] . $sala;
            }
            
            $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->ofertacomponentecurricularid = $data[0];
            $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->descricao = $data[1];
            $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->data = '<font size="3">' . $data[9] . '</font>';
            $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->unit = $data[8];
            $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->professors = $data[6];
            
            $assunto = $data[11];
            
            $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->ocorrenciaHorarios .= $data[4] . '-' . $data[5] . '&';
            $tmp[$data[2]]->turns[$ocorrenciacurso->turnId]->schedules[$data[0].$data[9]]->assunto .= $assunto;
            
            ksort($tmp[$data[2]]->turns);
            
            // Formatado a data no padrão yyyy-mm-dd para poder ordenar e pegar a maior e menor
            $dataFormatada = SAGU::formatDate($data[9], 'yyyy-mm-dd');
            if ( !in_array($dataFormatada, $dataS) )
            {
                $dataS[] = $dataFormatada;
            }
        }

        $this->minDate = !$beginDate ? date('d/m/Y', strtotime(min($dataS))) : $beginDate;
        $this->maxDate = !$endDate ? date('d/m/Y', strtotime(max($dataS))) : $endDate;
        
        return $tmp;   
    }
    
    public function infoDisciplinaPedagogico($args)
    {
        $args = rawurldecode($args);
        $args = explode('&&', substr($args, 2));
        
        list ( $data, $ocorrenciaHorarios, $professor ) = $args;
        $ocorrenciaHorarios = explode('&', $ocorrenciaHorarios);
        
        $acpHorario = new AcpOcorrenciaHorarioOferta();
        $busPerson = new BusinessBasicBusPerson();

        $dlgFields[] =  new MText('txtInfo','<center><strong>' . $this->diasemana($data) . ' (' . $data . ')</strong></center>', '');
        $dlgFields[] =  new MText('assuntoLabel','<center><strong>Assuntos:<br></strong></center>');
        
        foreach ( $ocorrenciaHorarios as $key => $ocorrenciaHorario )
        {
            $filter = new stdClass();
            $filter->name = str_replace('&', '', $professor);
            $person = $busPerson->searchPerson($filter);
            list ( $inicio, $fim ) =  explode('-', $ocorrenciaHorario);

            $assunto = $acpHorario->procurarAssuntoAulaHorario($data, $inicio, $fim, prtUsuario::obterInscricaoAtiva(), $person[0][0]);
            $assunto = strlen($assunto[0][0]) > 0 ? $assunto[0][0] : '-';

            $dlgFields[] =  new MText('horario' . $key, '<center><strong>' . $ocorrenciaHorario . ':<br></strong>' . $assunto . '</center>', '');
        }
        
        $dlgFields[] = MUtil::centralizedDiv(array(new MButton('btnFecharDialogo', _M('Fechar'), 'javascript:validaBotao();')));
        $div = new MDiv('div', $dlgFields);
        $div->addStyle('width', '500px');

        $dialog = new MDialog('popupVizualizarSolicitacao','Ver assunto',array($div));
        $dialog->show();
       
        //Motivo funcao, todos os MDialog atualizam a pagina quando coloca algum botao fechar, por ser um tela que demora para
        //atualizar, MDialog não fechar corretamente, função força o click fechar da tela
        $js = "
            (function validaBotao()
            {
                if( document.getElementById('btnFecharDialogo') !== null ) 
                {
                    document.getElementById('btnFecharDialogo').onclick = function()
                    {
                        document.querySelector('span.dijitDialogCloseIcon').click();
                        
                        this.parentNode.removeChild(this);

                        return false;
                    };
                }
                else
                {
                    setTimeout(validaBotao, 100);
                }
            })();
        ";
        
        MIOLO::getInstance()->page->addJsCode($js);
    }
    
    public function diasemana($data)
    {  // Traz o dia da semana para qualquer data informada
        $dia =  substr($data,0,2);
        $mes =  substr($data,3,2);
        $ano =  substr($data,6,4);
        $diasemana = date("w", mktime(0,0,0,$mes,$dia,$ano) );

        switch($diasemana)
        {  
            case"0": $diasemana = 'Domingo';	   break;  
            case"1": $diasemana = 'Segunda-Feira'; break;  
            case"2": $diasemana = 'Terça-Feira';   break;  
            case"3": $diasemana = 'Quarta-Feira';  break;  
            case"4": $diasemana = 'Quinta-Feira';  break;  
            case"5": $diasemana = 'Sexta-Feira';   break;  
            case"6": $diasemana = 'Sábado';		break;  
        }
        return $diasemana;
    }
}
?>
