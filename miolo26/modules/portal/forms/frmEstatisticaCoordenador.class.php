<?php

$MIOLO->uses('forms/frmMobile.class.php', $module);
$MIOLO->uses('classes/prtTableRaw.class.php', $module);
class frmEstatisticaCoordenador extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Estatísticas', MIOLO::getCurrentModule()));
    }

    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        $image = $ui->getImageTheme($module, 'stats.png');
        
        $fields[] = new MDiv();
        $label = new MLabel(_M('Selecione abaixo o gráfico desejado'));
        $label->addStyle('font-weight', 'bold');
        $label->addStyle('color', 'navy');
        $label->addStyle('margin-top', '8px');
        $label->addStyle('margin-left', '10px');
        $fields[] = MUtil::centralizedDiv(array($label));
        $fields[] = new MDiv();
        
        $graficos = array();
        $graficos['estadoContratual'] = _M('Número de matrículas, renovações, trancamentos, reingressos e transferências por período.');
        $graficos['estadoMatriculas'] = _M('Total de alunos aprovados, reprovados e reprovados por falta no período.');
        $graficos['mediaAlunosDisciplinasSemestre'] = _M('Média de alunos por disciplina e semestre do curso.');
        $graficos['numeroAlunosSemestre'] = _M('Número de alunos por semestre do curso.');
        
        if ( SAGU::getParameter('BASIC', 'MODULE_FINANCE_INSTALLED') == 'YES' )
        {
            $graficos['numeroInadimplentesSemestre'] = _M('Número de inadimplentes por semestre do curso.');
            $graficos['inadimplenciaPeriodo'] = _M('Percentual de inadimplências por período.');
            $graficos['inadimplenciaAtual'] = _M('Inadimplência atual, em valor e número de alunos.');
        }
        
        $graficos['numeroInscritosProcessoSeletivo'] = _M('Número de inscritos por processo seletivo.');

        $url = $MIOLO->GetActionURL('portal', 'main:estatistica') . '&grafico=';
        
        $selection = new MSelection('grafico', NULL, NULL, $graficos);
        $selection->addAttribute('onchange', "location.replace('{$url}' + dojo.byId('grafico').value);");
        $fields[] = $selection;
        
        $fields[] = new MDiv();
        
        $fields[] = new MDiv();
        
        // se foi selecionado algum relatorio, ira vir no request
        if ( isset($_REQUEST['grafico']) )
        {
            $args = new stdClass();
            $args->grafico = MIOLO::_REQUEST('grafico');

            $fields = array_merge($fields, $this->informarParametros($args));
        }
        
        $fields[] = new MDiv();
        
        $fields[] = new MDiv();
        $fields[] = $divGrafico = new MDiv('divGrafico');
        $divGrafico->setWidth('100%');
        $fields[] = new MDiv();
        
        parent::addFields($fields);
    }
    
    public function informarParametros($args)
    {
        $MIOLO = MIOLO::getInstance();
        $busPeriodo = $MIOLO->getBusiness('academic', 'BusPeriod');
        $busCourseCoordinator = $MIOLO->getBusiness('academic', 'BusCourseCoordinator');
        $busCourse = $MIOLO->getBusiness('academic', 'BusCourse');
        
        $porPeriodo = array('estadoContratual', 'estadoMatriculas', 'mediaAlunosDisciplinasSemestre', 'numeroAlunosSemestre', 'numeroInadimplentesSemestre', 'inadimplenciaPeriodo');
        $porCurso = array('estadoContratual', 'estadoMatriculas', 'mediaAlunosDisciplinasSemestre', 'numeroAlunosSemestre', 'numeroInadimplentesSemestre', 'inadimplenciaPeriodo', 'inadimplenciaAtual');
        $porDataInicioFim = array('numeroInscritosProcessoSeletivo');
        
        if ( in_array($args->grafico, $porPeriodo) )
        {
            $label = new MLabel(_M('Selecione o período: '));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('margin-top', '8px');
            $label->addStyle('margin-left', '10px');
            
            $periodos = $busCourseCoordinator->obterPeriodosDoCoordenador($this->personid);
            if ( !$periodos || prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_GESTOR )
            {
                $periodos = $busPeriodo->listPeriod('periodid DESC');
            }            
            $selection = new MSelection('periodo', NULL, NULL, $periodos);
            $fields[] = MUtil::centralizedDiv(array($label, $selection));
        }
        
        if ( in_array($args->grafico, $porCurso) )
        {
            $label = new MLabel(_M('Selecione o curso: '));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('margin-top', '8px');
            $label->addStyle('margin-left', '10px');
            
            if ( prtUsuario::obterTipoDeAcesso() == prtUsuario::USUARIO_GESTOR )
            {
                $cursos = $busCourse->listCourse();
            }
            else
            {
                $cursos = $busCourseCoordinator->obterCursosDoCoordenador($this->personid, true);
            }
            $selection = new MSelection('curso', NULL, NULL, $cursos);
            $fields[] = MUtil::centralizedDiv(array($label, $selection));
        }
        
        if ( in_array($args->grafico, $porDataInicioFim) )
        {
            $label = new MLabel(_M('Data de início: '));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('margin-top', '8px');
            $label->addStyle('margin-left', '10px');
            
            $dtInicio = new MCalendarField('dataInicio');
            $fields[] = MUtil::centralizedDiv(array($label, $dtInicio));
            
            $label = new MLabel(_M('Data de fim: '));
            $label->addStyle('font-weight', 'bold');
            $label->addStyle('color', 'navy');
            $label->addStyle('margin-top', '8px');
            $label->addStyle('margin-left', '10px');
            
            $dtFim = new MCalendarField('dataFim');
            $fields[] = MUtil::centralizedDiv(array($label, $dtFim));
        }
        
        $fields[] = new MDiv();
        $button = new MButton('btnGerarGrafico', _M('Gerar o gráfico'));
        $fields[] = MUtil::centralizedDiv(array($button));
        
        return $fields;
    }
    
    public function btnGerarGrafico_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
        $busCourseCoordinator = $MIOLO->getBusiness('academic', 'BusCourseCoordinator');
        $disciplinas = new PrtDisciplinas();
        $args->unidade = $this->unitid;
        
        if ( $this->validate($args) )
        {
            $grafico = $disciplinas->obterGrafico($args);
            if ( !$grafico )
            {
                $this->setResponse(MUtil::centralizedDiv(array(new MLabel(_M('Não foram encontrados dados para gerar o gráfico.'), 'red'))), 'divGrafico');
            }
            else
            {
                $grafico->setWidth('90%');
                $div = new MDiv('divGrafico', MUtil::centralizedDiv(array($grafico)));
                $div->setWidth('100%');

                $this->setResponse($div, 'divGrafico');
            }
        }
        else
        {
            $this->setNullResponseDiv();
        }        
    }
    
    public function validate($args)
    {
        $valid = true;
        
        if ( $args->grafico == 'numeroInscritosProcessoSeletivo' )
        {
            if ( !$args->dataInicio )
            {
                new MMessageWarning(_M('Por favor, informe a data de início.'));
               $valid = false;
            }
            elseif ( !$args->dataFim )
            {
                new MMessageWarning(_M('Por favor, informe a data de fim.'));
               $valid = false;
            }
        }
        elseif ( $args->grafico == 'inadimplenciaAtual' )
        {
            if ( !$args->curso )
            {
                new MMessageWarning(_M('Por favor, selecione o curso.'));
                $valid = false;
            }
        }
        else
        {
            if ( !$args->grafico )
            {
               new MMessageWarning(_M('Por favor, selecione o gráfico desejado.'));
               $valid = false;
            }
            elseif ( !$args->periodo )
            {
                new MMessageWarning(_M('Por favor, selecione o período.'));
                $valid = false;
            }
            elseif ( !$args->curso )
            {
                new MMessageWarning(_M('Por favor, selecione o curso.'));
                $valid = false;
            }
        }
        
        return $valid;
    }

}

?>
