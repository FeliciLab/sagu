<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Solu��es Livres Ltda.
 *
 * Este arquivo � parte do programa Sagu.
 *
 * O Sagu � um software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * dentro dos termos da Licen�a P�blica Geral GNU como publicada pela Funda��o
 * do Software Livre (FSF); na vers�o 2 da Licen�a.
 *
 * Este programa � distribu�do na esperan�a que possa ser �til, mas SEM
 * NENHUMA GARANTIA; sem uma garantia impl�cita de ADEQUA��O a qualquer MERCADO
 * ou APLICA��O EM PARTICULAR. Veja a Licen�a P�blica Geral GNU/GPL em
 * portugu�s para maiores detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU, sob o t�tulo
 * "LICENCA.txt", junto com este programa, se n�o, acesse o Portal do Software
 * P�blico Brasileiro no endere�o www.softwarepublico.gov.br ou escreva para a
 * Funda��o do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * Usuario portal
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * @since
 * Class created on 28/09/2012
 *
 */

$MIOLO->uses('classes/prtCommonForm.class.php', $module);

class PrtDisciplinasPedagogico
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/SType.class', 'basic');
        $MIOLO->uses('types/AcpOfertaComponenteCurricular.class', 'pedagogico');
        $MIOLO->uses('types/AcpOfertaTurma.class', 'pedagogico');
        $MIOLO->uses('types/AcpOfertaCurso.class', 'pedagogico');
        $MIOLO->uses('types/AcpOcorrenciaCurso.class', 'pedagogico');
        $MIOLO->uses('types/AcpCurso.class', 'pedagogico');
        $MIOLO->uses('types/AcpPerfilCurso.class', 'pedagogico');
        $MIOLO->uses('types/AcpRegrasMatriculaPerfilCurso.class', 'pedagogico');
        $MIOLO->uses('types/AcpGradeHorario.class', 'pedagogico');
        $MIOLO->uses('types/AcpComponenteCurricularDisciplina.class', 'pedagogico');
        $MIOLO->uses('types/AcpComponenteCurricularTrabalhoConclusao.class', 'pedagogico');
        $MIOLO->uses('types/AcpTipoComponenteCurricular.class', 'pedagogico');
        $MIOLO->uses('types/AcpCursoDocente.class', 'pedagogico');
        $MIOLO->uses('types/AcpCamposAdicionaisCurso.class', 'pedagogico');
        $MIOLO->uses('types/AcpPerfilCursoCamposAdicionais.class', 'pedagogico');
        $MIOLO->uses('types/AcpPerfilCursoComponenteCurricular.class', 'pedagogico');
        $MIOLO->uses('types/AcpTipoDocumento.class', 'pedagogico');
        $MIOLO->uses('types/AcpComponenteCurricular.class', 'pedagogico');
        $MIOLO->uses('types/AcpMatricula.class', 'pedagogico');
        $MIOLO->uses('types/AcpInscricao.class', 'pedagogico');
        $MIOLO->uses('types/AcpInscricaoTurmaGrupo.class', 'pedagogico');
        $MIOLO->uses('types/AcpFrequencia.class', 'pedagogico');
        $MIOLO->uses('types/AcpOcorrenciaHorarioOferta.class', 'pedagogico');
        $MIOLO->uses('types/AcpModeloDeAvaliacao.class', 'pedagogico');
        $MIOLO->uses('types/AcpComponenteDeAvaliacao.class', 'pedagogico');
        $MIOLO->uses('types/AcpComponenteDeAvaliacaoNota.class', 'pedagogico');
        $MIOLO->uses('types/AcpComponenteDeAvaliacaoConceito.class', 'pedagogico');
        $MIOLO->uses('types/AcpControleDeFrequencia.class', 'pedagogico');
        $MIOLO->uses('types/AcpAvaliacao.class', 'pedagogico');
        $MIOLO->uses('types/AcpRelacionamentoDeComponentes.class', 'pedagogico');
        $MIOLO->uses('types/AcpComponenteDeAvaliacaoNotaRecuperacao.class', 'pedagogico');        
        $MIOLO->uses('types/AcpConceitosDeAvaliacao.class', 'pedagogico');
        $MIOLO->uses('types/AcpComponenteCurricularMatriz.class', 'pedagogico');
        $MIOLO->uses('types/AcpMatrizCurricularGrupo.class', 'pedagogico');
        $MIOLO->uses('types/AcpCalendarioAcademicoEvento.class', 'pedagogico');
    }
        
    /**
     * Lista as Turmas do professor
     * @param type $professorId
     * @return type
     * 
     */
    public function obterTurmasProfessor($professorId)
    {        
        $msql = new MSQL();
        $msql->setTables('acpOcorrenciaHorarioOferta A
                        INNER JOIN acpofertacomponentecurricular C
                                ON A.ofertacomponentecurricularid = C.ofertacomponentecurricularid
                        INNER JOIN acpofertaturma D
                                ON D.ofertaturmaid = C.ofertaturmaid
                        INNER JOIN acpofertacurso E
                                ON D.ofertacursoid = E.ofertacursoid');

        $msql->setColumns('DISTINCT  D.ofertaturmaid,D.descricao, D.descricao || \' - \' || E.descricao as turma_curso');
        $msql->setWhere("A.professorid = $professorId AND A.cancelada IS FALSE AND D.habilitada IS TRUE");
        $msql->setOrderBy('D.descricao');

        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
    public function obterTurmasAluno($personId)
    {        
        $msql = new MSQL();
        $msql->setTables('acpmatricula A
                        INNER JOIN acpinscricaoturmagrupo C
                                ON A.inscricaoturmagrupoid = C.inscricaoturmagrupoid
                        INNER JOIN acpofertaturma D
                                ON D.ofertaturmaid = C.ofertaturmaid');

        $msql->setColumns('DISTINCT D.ofertaturmaid,D.descricao');
        $msql->setWhere("A.personid = $personId AND D.habilitada IS TRUE");
        $msql->setOrderBy('D.descricao');

        $inscricaoId = prtUsuario::obterInscricaoAtiva();

        if ( strlen($inscricaoId) > 0 )
        {
            $msql->setWhere('C.ofertaTurmaId = (SELECT ITG.ofertaTurmaId FROM acpinscricaoturmagrupo ITG WHERE ITG.inscricaoId = ? limit 1)', array($inscricaoId));
        }

        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
        public function obterDadosDasTurmasDoAluno($personId)
    {        
        $msql = new MSQL();
        $msql->setTables('acpmatricula A
                        INNER JOIN acpinscricaoturmagrupo C
                                ON A.inscricaoturmagrupoid = C.inscricaoturmagrupoid
                        INNER JOIN acpofertaturma D
                                ON D.ofertaturmaid = C.ofertaturmaid');

        $msql->setColumns('DISTINCT D.ofertaturmaid, D.descricao, D.ofertacursoid, D.datainicialoferta, D.datafinaloferta, D.datafinaloferta, A.matriculaId, A.ofertacomponentecurricularid');
        $msql->setWhere("A.personid = $personId AND D.habilitada IS TRUE");
        $msql->setOrderBy('D.descricao');

        $inscricaoId = prtUsuario::obterInscricaoAtiva();

        if ( strlen($inscricaoId) > 0 )
        {
            $msql->setWhere('C.ofertaTurmaId = (SELECT ITG.ofertaTurmaId FROM acpinscricaoturmagrupo ITG WHERE ITG.inscricaoId = ? LIMIT 1)', array($inscricaoId));
        }

        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
    public function obterDisciplinasDoProfessorNaTurma($professorId, $ofertaTurmaId)
    {
        $msql = new MSQL();
        $msql->setTables('acpOcorrenciaHorarioOferta A
                        INNER JOIN acpofertacomponentecurricular B
                                ON A.ofertacomponentecurricularid = B.ofertacomponentecurricularid
                        INNER JOIN acpComponenteCurricularMatriz C
                                ON C.componentecurricularMatrizId = B.componentecurricularMatrizId
                        INNER JOIN acpcomponentecurricular D
                                ON D.componentecurricularid = C.componentecurricularid');

        $msql->setColumns('DISTINCT B.ofertacomponentecurricularid');
        $msql->setWhere("A.professorid = $professorId AND A.cancelada IS FALSE AND B.ofertaturmaid = $ofertaTurmaId");
        
        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
    public function obterDisciplinasDoProfessorNaTurmaEad($professorId, $ofertaTurmaId)
    {
        $msql = new MSQL();
        $msql->setTables('acpdocentesead A
                        INNER JOIN acpofertacomponentecurricular B
                                ON A.ofertacomponentecurricularid = B.ofertacomponentecurricularid
                        INNER JOIN acpComponenteCurricularMatriz C
                                ON C.componentecurricularMatrizId = B.componentecurricularMatrizId
                        INNER JOIN acpcomponentecurricular D
                                ON D.componentecurricularid = C.componentecurricularid');

        $msql->setColumns('DISTINCT B.ofertacomponentecurricularid');
        $msql->setWhere("A.professorid = $professorId AND B.ofertaturmaid = $ofertaTurmaId");
        
        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
    public function obterDisciplinasAtivasDoProfessor($professorId)
    {
        $msql = new MSQL();
        $msql->setTables('acpOcorrenciaHorarioOferta A
                        INNER JOIN acpofertacomponentecurricular B
                                ON A.ofertacomponentecurricularid = B.ofertacomponentecurricularid
                        INNER JOIN acpComponenteCurricularMatriz C
                                ON C.componentecurricularMatrizId = B.componentecurricularMatrizId
                        INNER JOIN acpcomponentecurricular D
                                ON D.componentecurricularid = C.componentecurricularid');

        $msql->setColumns('DISTINCT B.ofertacomponentecurricularid');
        $msql->setWhere("A.professorid = $professorId AND A.cancelada IS FALSE AND B.datafechamento IS NULL");
        
        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
    public function obterDisciplinasDoAlunoNaTurma($personId, $ofertaTurmaId)
    {
        $msql = new MSQL();
        $msql->setTables('acpmatricula A
                        INNER JOIN acpinscricaoturmagrupo C
                                ON A.inscricaoturmagrupoid = C.inscricaoturmagrupoid
                        INNER JOIN acpofertaturma D
                                ON D.ofertaturmaid = C.ofertaturmaid
                        INNER JOIN acpofertacomponentecurricular OCC
                                ON OCC.ofertaturmaid = D.ofertaturmaid
        ');

        $msql->setColumns('DISTINCT OCC.ofertacomponentecurricularid');
        $msql->setWhere("A.personid = $personId AND C.ofertaturmaid = $ofertaTurmaId");
        
        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
    public function obterDisciplinasDoAlunoNaTurmaParaOPortal($personId, $ofertaTurmaId)
    {
        $sql = "SELECT DISTINCT OCC.ofertacomponentecurricularid,
                       (CASE getParameter('PORTAL', 'TITULO_DISCIPLINA_COM_DATA')::BOOLEAN
                             WHEN TRUE
                             THEN
                                  (CC.codigo || ' - ' || CC.nome || CASE WHEN OCC.datainicio IS NOT NULL THEN ' - ' || TO_CHAR(OCC.datainicio, getParameter('BASIC', 'MASK_DATE')) ELSE ' ' END)
                             ELSE
                                  (CC.codigo || ' - ' || CC.nome)
                        END) AS titulo
                  FROM acpmatricula A
            INNER JOIN acpinscricaoturmagrupo C
                    ON A.inscricaoturmagrupoid = C.inscricaoturmagrupoid
            INNER JOIN acpofertaturma D
                    ON D.ofertaturmaid = C.ofertaturmaid
            INNER JOIN acpofertacomponentecurricular OCC
                    ON OCC.ofertaturmaid = D.ofertaturmaid  
            INNER JOIN acpComponenteCurricularMatriz CCM
                    ON CCM.componenteCurricularMatrizId = OCC.componenteCurricularMatrizId
            INNER JOIN acpComponenteCurricular CC
                    ON CC.componenteCurricularId = CCM.componenteCurricularId
                 WHERE A.personid = ? 
                   AND C.ofertaturmaid = ?";

        $resultado = SDatabase::query($sql, array($personId, $ofertaTurmaId));
        
        return $resultado;
    }
    
    public function obterAlunosDaDisciplina($ofertacomponentecurricularid)
    {
        $msql = new MSQL();
        $msql->setTables('acpofertacomponentecurricular OCC '
                . 'LEFT JOIN acpofertaturma OT ON (OCC.ofertaturmaid = OT.ofertaturmaid) '
                . 'LEFT JOIN acpinscricaoturmagrupo ITG ON (OT.ofertaturmaid = ITG.ofertaturmaid) '
                . 'LEFT JOIN acpmatricula M ON (ITG.inscricaoturmagrupoid = M.inscricaoturmagrupoid)'
                . 'LEFT JOIN basphysicalperson P ON (M.personid = P.personid)');
        
        $msql->setColumns('DISTINCT M.personid, P.name');
        $msql->setWhere("OCC.ofertacomponentecurricularid = {$ofertacomponentecurricularid} AND M.personid IS NOT NULL");
        $msql->setOrderBy('P.name');
        
        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
    public function obterCursos()
    {
        $resultado = NULL;
        
        $msql = new MSQL();
        $msql->setTables('acpcoordenadores CO '
                . 'LEFT JOIN acpcurso CU ON (CO.cursoid = CU.cursoid)');

        $msql->setColumns('DISTINCT CO.cursoid, CU.nome');
        $msql->setWhere('CO.cursoid IS NOT NULL');
        $msql->setOrderBy('CU.nome');

        $resultado = bBaseDeDados::consultar($msql);
        return $resultado;
    }
    
    public static function obterGradeDeHorariosDoAluno($personid, $inscricaoid = NULL, $datainicio = NULL, $datafim = NULL)
    {
        $inscricaoid = prtUsuario::obterInscricaoAtiva();
                $where = '';
        if( strlen($datainicio) == 0 && strlen($datafim) == 0 )
        {
        }
        else
        {
        // comparacao de datas
        if ( strlen($datainicio) > 0 && strlen($datafim) > 0 )
        {
            $where = " AND (dataaula BETWEEN TO_DATE('{$datainicio}', getParameter('BASIC', 'MASK_DATE') ) AND TO_DATE('{$datafim}', getParameter('BASIC', 'MASK_DATE') )) ";
        }
        else
        {
            if(strlen($datainicio) > 0)
            {
                $where = " AND ( dataaula >= TO_DATE('{$datainicio}', getParameter('BASIC', 'MASK_DATE')))";
            }
            if(strlen($datafim) > 0)
            {
                $where .= " AND ( dataaula <= TO_DATE('{$datafim}', getParameter('BASIC', 'MASK_DATE')))";
            }
        }
        }
        
        
        $sql = "SELECT ofertacomponentecurricularid, 
                        disciplina, 
                        dia, 
                        diasemana, 
                        horainicio, 
                        horafim, 
                        professor,
                        unitid,
                        unidade, 
                        TO_CHAR(dataaula, 'dd/mm/yyyy') as data,
                        sala,
                        assunto
                    FROM
                    (SELECT 
                        ofertaComponente.ofertacomponentecurricularid,
                        componente.nome as disciplina,
                        (CASE WHEN
                            EXTRACT('DOW' FROM ocorrenciaHorario.dataaula) = 0 THEN 7
                            ELSE EXTRACT('DOW' FROM ocorrenciaHorario.dataaula) END) as dia,
                        obterdiaextenso(EXTRACT('DOW' FROM ocorrenciaHorario.dataaula)::INT) as diasemana,
                        TO_CHAR(horario.horainicio, 'HH24:MI') as horainicio,
                        TO_CHAR(horario.horafim, 'HH24:MI') as horafim,
                        pessoa.name as professor,
                        ocorrenciaHorario.dataaula,
                        unit.unitid as unitid,
                        unit.description as unidade,
                        IPR.description as sala,
                        ocorrenciaHorario.assunto as assunto
                    FROM   acpOcorrenciaHorarioOferta ocorrenciaHorario  
                    LEFT JOIN acpHorario horario ON horario.horarioid = ocorrenciaHorario.horarioid 
                    LEFT JOIN acpOfertaComponenteCurricular ofertaComponente ON ofertaComponente.ofertacomponentecurricularid = ocorrenciaHorario.ofertaComponenteCurricularId 
                    LEFT JOIN acpMatricula matricula ON matricula.ofertacomponentecurricularid = ofertaComponente.ofertacomponentecurricularid
                    LEFT JOIN ONLY basphysicalperson pessoa on pessoa.personid = ocorrenciaHorario.professorid
                    LEFT JOIN acpcomponentecurricularmatriz componentematriz ON componentematriz.componentecurricularmatrizid = ofertaComponente.componentecurricularmatrizid
                    LEFT JOIN acpcomponentecurricular componente on componente.componentecurricularid = componentematriz.componentecurricularid
                    LEFT JOIN basunit unit ON unit.unitid = ocorrenciaHorario.unitid
                    LEFT JOIN insphysicalresource IPR ON ocorrenciaHorario.physicalresourceid = IPR.physicalresourceid

                    WHERE   
                      ocorrenciaHorario.cancelada IS FALSE
                      AND matricula.personid = ?
                      AND matricula.situacao != 'C'
                      {$where}
                      AND matricula.inscricaoturmagrupoid = (SELECT inscricaoturmagrupoid FROM acpinscricaoturmagrupo WHERE inscricaoid = ? LIMIT 1)
                    ORDER BY 1,2,3) AS A
                    ORDER BY dia, dataaula, horainicio, horafim;";

                      $resultado = sDatabase::query($sql, array($personid, $inscricaoid));
        return $resultado;
    }
    
    public static function obterGradeDeHorariosDoProfessor($personid)
    {
        $sql = "SELECT ofertacomponentecurricularid, 
                        disciplina, 
                        dia, 
                        diasemana, 
                        horainicio, 
                        horafim, 
                        professor,
                        unitid,
                        unidade, 
                        TO_CHAR(dataaula, 'dd/mm/yyyy') as data,
                        sala,
                        assunto
                    FROM
                    (SELECT 
                        ofertaComponente.ofertacomponentecurricularid,
                        componente.nome as disciplina,
                        (CASE WHEN
                            EXTRACT('DOW' FROM ocorrenciaHorario.dataaula) = 0 THEN 7
                            ELSE EXTRACT('DOW' FROM ocorrenciaHorario.dataaula) END) as dia,
                        obterdiaextenso(EXTRACT('DOW' FROM ocorrenciaHorario.dataaula)::INT) as diasemana,
                        TO_CHAR(horario.horainicio, 'HH24:MI') as horainicio,
                        TO_CHAR(horario.horafim, 'HH24:MI') as horafim,
                        pessoa.name as professor,
                        ocorrenciaHorario.dataaula,
                        unit.unitid as unitid,
                        unit.description as unidade,
                        IPR.description as sala,
                        ocorrenciaHorario.assunto as assunto
                    FROM   acpOcorrenciaHorarioOferta ocorrenciaHorario  
                    LEFT JOIN acpHorario horario ON horario.horarioid = ocorrenciaHorario.horarioid 
                    LEFT JOIN acpOfertaComponenteCurricular ofertaComponente ON ofertaComponente.ofertacomponentecurricularid = ocorrenciaHorario.ofertaComponenteCurricularId 
                    LEFT JOIN ONLY basphysicalperson pessoa on pessoa.personid = ocorrenciaHorario.professorid
                    LEFT JOIN acpcomponentecurricularmatriz componentematriz ON componentematriz.componentecurricularmatrizid = ofertaComponente.componentecurricularmatrizid
                    LEFT JOIN acpcomponentecurricular componente on componente.componentecurricularid = componentematriz.componentecurricularid
                    LEFT JOIN basunit unit ON unit.unitid = ocorrenciaHorario.unitid
                    LEFT JOIN insphysicalresource IPR ON ocorrenciaHorario.physicalresourceid = IPR.physicalresourceid
                    WHERE   
                      ocorrenciaHorario.cancelada IS FALSE
                      AND pessoa.personid = ?
                      AND ocorrenciaHorario.cancelada IS FALSE
                    ORDER BY 1,2,3) AS A
                    ORDER BY dia, dataaula, horainicio, horafim;";
        
        $resultado = sDatabase::query($sql, array($personid));
        return $resultado;
    }
    
    public function obterMatriculaPelaOferta($personId, $ofertacomponentecurricularid)
    {
        $msql = new MSQL();
        $msql->setColumns('A.matriculaid');
        $msql->setTables(' acpmatricula A
                INNER JOIN acpofertacomponentecurricular OCC
                        ON OCC.ofertacomponentecurricularid = A.ofertacomponentecurricularid
        ');

        $msql->addEqualCondition('A.personid', $personId);
        $msql->addEqualCondition('A.ofertacomponentecurricularid', $ofertacomponentecurricularid);
        
        // Não buscar matrículas que ainda não estejam confirmadas e/ou canceladas
        $msql->addWhereNotIn('A.situacao', array(AcpMatricula::SITUACAO_INSCRICAO, AcpMatricula::SITUACAO_CANCELAMENTO));
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }

    public function obterNotaPedagogico($componenteDeAvaliacaoId, $matriculaId)
    {
        $matricula = new AcpMatricula($matriculaId);
        $modelodeavaliacao = AcpModeloDeAvaliacao::obterModeloDaOfertaDeComponenteCurricular($matricula->ofertacomponentecurricularid);
        $componentedeavaliacao = new AcpComponenteDeAvaliacao($componenteDeAvaliacaoId);
        $nota = new stdClass();
        $avaliacao = AcpAvaliacao::obterAvaliacao($matriculaId, $componenteDeAvaliacaoId);
        
        //Tipo conceito
        if ( $modelodeavaliacao->tipoDeDados == AcpModeloDeAvaliacao::TIPO_CONCEITO )
        {
            $notaatual = $avaliacao->conceitodeavaliacaoid;
            $componenteconceito = AcpComponenteDeAvaliacaoConceito::obterComponenteConceitoDoComponente($componenteDeAvaliacaoId);
            $conceitos = AcpConceitosDeAvaliacao::listarConceitosDoConjunto($componenteconceito->conjuntoDeConceitosId);
            
            $nota->valor = $conceitos[$notaatual];
            $nota->descricao = $componentedeavaliacao->descricao;
        }
        else //Tipo nota
        {
            $notaatual = $avaliacao->nota;

            $nota->valor = $notaatual;
            $nota->descricao = $componentedeavaliacao->descricao;
        }
        
        return $nota;
    }
    
    public function verificaFrequencia($matriculaid, $ocorrenciahorarioofertaid, $registraFalta=false)
    {
        $frequencia = AcpFrequencia::obterFrequencia($matriculaid, $ocorrenciahorarioofertaid);

        // Se não encontrou um registro de frequência para o aluno nesse dia, nesse horário, insere por padrão a presença.
        if ( !strlen($frequencia->frequenciaid) > 0 )
        {
            $frequencia = new AcpFrequencia();
            $frequencia->ocorrenciahorarioofertaid = $ocorrenciahorarioofertaid;
            $frequencia->matriculaid = $matriculaid;
            $frequencia->datalancamento = SAGU::getDateNow();
            $frequencia->frequencia = AcpFrequencia::FREQUENCIA_PRESENTE;            
            $frequencia->save();
        }
        elseif( $registraFalta )
        {
            $frequencia->frequencia = AcpFrequencia::FREQUENCIA_AUSENTE;
            $frequencia->save();
        }
    }
    
    public function salvarFrequencia($matriculaid, $ocorrenciahorarioofertaid, $valor = AcpFrequencia::FREQUENCIA_PRESENTE)
    {
        $frequencia = AcpFrequencia::obterFrequencia($matriculaid, $ocorrenciahorarioofertaid);  
        $frequencia->ocorrenciahorarioofertaid = $ocorrenciahorarioofertaid;
        $frequencia->matriculaid = $matriculaid;
        $frequencia->datalancamento = SAGU::getDateNow();
        $frequencia->frequencia = $valor;
        return $frequencia->save();
    }
       
    public function buscaFrequencias($matriculaId)
    {
        $msql = new MSQL();
        $msql->setColumns("OHO.dataaula,
                           array_to_string(array_agg(TO_CHAR(h.horainicio, 'hh24:mi') || ' - ' || TO_CHAR(h.horafim, 'hh24:mi') ORDER BY h.horainicio, h.horafim), ' / ') AS horarios,
                           array_to_string(array_agg(F.frequencia ORDER BY h.horainicio, h.horafim), ' ') AS status,
                           array_to_string(array_agg(F.justificativa ORDER BY h.horainicio, h.horafim), '||') AS justificativa");
        $msql->setTables('acpocorrenciahorariooferta OHO
               INNER JOIN acphorario H
                       ON H.horarioid = OHO.horarioid
               INNER JOIN acpofertacomponentecurricular OCC
                       ON OCC.ofertacomponentecurricularid = OHO.ofertacomponentecurricularid
               INNER JOIN acpofertaturma OT
                       ON OT.ofertaturmaid = OCC.ofertaturmaid
               INNER JOIN acpinscricaoturmagrupo ITG
                       ON ITG.ofertaturmaid = OT.ofertaturmaid
               INNER JOIN acpmatricula M
                       ON M.inscricaoturmagrupoid = ITG.inscricaoturmagrupoid
                      AND M.ofertacomponentecurricularid = OCC.ofertacomponentecurricularid
                LEFT JOIN acpfrequencia F
                       ON F.matriculaid = M.matriculaid
                      AND F.ocorrenciahorarioofertaid = OHO.ocorrenciahorarioofertaid
        ');
        $msql->setGroupBy('OHO.dataaula');
        $msql->setOrderBy('OHO.dataaula');
        $msql->addEqualCondition('M.matriculaid', $matriculaId);
        
        $resultado = SDatabase::queryAssociative($msql);

        return $resultado;
    }
    
    /**
     * Retorna no formato array associativo
     */
    public function obterCronograma($ofertacomponentecurricularid, $dataaula=null)
    {   
        $msql = new MSQL();
        $msql->setTables('acpocorrenciahorariooferta A');
        $msql->setColumns('A.ocorrenciahorarioofertaid, A.dataaula, B.horainicio, B.horafim, B.horarioid, A.conteudo');
        $msql->addLeftJoin('acphorario B','A.horarioid = B.horarioid');
        $msql->setWhere("A.ofertacomponentecurricularid = $ofertacomponentecurricularid");
        
        if ($dataaula)
        {
            $msql->setWhere("A.dataAula = datetodb(?)", array($dataaula));
        }
        
        $msql->setOrderBy('2, 3');
        
        $resultado = SDatabase::queryAssociative($msql);
        
        return $resultado;
    }
    
    /**
     * Obtém a última aula da disciplina
     * @param type $ofertacomponentecurricularid
     * @return type
     */
    public function obterUltimoDiaCronograma($ofertacomponentecurricularid)
    {
        $msql = new MSQL();
        $msql->setTables('acpocorrenciahorariooferta A');
        $msql->setColumns('distinct A.ocorrenciahorarioofertaid, A.dataaula');
        $msql->setWhere("A.ofertacomponentecurricularid = $ofertacomponentecurricularid");
        $msql->setOrderBy('A.dataaula DESC');
        $msql->setLimit(1);

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }

    /**
     * Obtém o conteúdo da aula
     */
    public function obterCronogramaDescricao($ofertacomponentecurricularid, $ocorrenciahorarioofertaid)
    {
        $msql = new MSQL();
        $msql->setTables('acpocorrenciahorariooferta A');
        $msql->setColumns('A.conteudo');
        $msql->setWhere("A.ofertacomponentecurricularid = $ofertacomponentecurricularid");
        
        if ( $ocorrenciahorarioofertaid )
        {
            $msql->addEqualCondition('A.ocorrenciahorarioofertaid', $ocorrenciahorarioofertaid);
        }

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }
    
    /**
     * Obtém o conteúdo da aula
     */
    public function obterCronogramaPelaData($ofertacomponentecurricularid, $dataaula, $personid = null)
    {
        $msql = new MSQL();
        $msql->setTables('acpocorrenciahorariooferta A');
        $msql->setColumns('A.conteudo');
        $msql->addEqualCondition('A.ofertacomponentecurricularid', $ofertacomponentecurricularid);
        $msql->setWhere('A.dataAula = datetodb(?)', array($dataaula));
        $msql->setWhere("A.conteudo IS NOT NULL");
        
        if ( $personid )
        {
            $msql->addEqualCondition('A.professorid', $personid);
        }

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }
    
    public function salvarCronograma($ofertacomponentecurricularid, $descricao, $dataaula)
    {
        $cronogramas = $this->obterCronograma($ofertacomponentecurricularid, $dataaula);
        
        foreach ( $cronogramas as $cron )
        {
            if ( strlen($cron['ocorrenciahorarioofertaid']) > 0 )
            {
                $sql = MSQL::updateTable('acpocorrenciahorariooferta', array('conteudo' => $descricao), array('ocorrenciahorarioofertaid' => $cron['ocorrenciahorarioofertaid']));
                bBaseDeDados::executar($sql);
            }
        }
    }
    
    /**
     * Retorna a data anterior a qual foi passada, caso exista.
     * 
     * @return string Data anterior
     */
    public function obterDiaAnterior($ofertacomponentecurricularid, $dataaula)
    {
        $msql = new MSQL();
        $msql->setTables('acpocorrenciahorariooferta A');
        $msql->setColumns('datetouser(A.dataaula)');
        $msql->setWhere("A.ofertacomponentecurricularid = ?", array($ofertacomponentecurricularid));
        $msql->setWhere("A.dataaula < datetodb(?)", array($dataaula));
        $msql->setOrderBy('A.dataaula DESC');

        $resultado = bBaseDeDados::consultar($msql);

        return $resultado[0][0];
    }
    
    public function obterDiasDeAula($ofertacomponentecurricularid, $professorid = null)
    {
        $msql = new MSQL();
        $msql->setTables('acpocorrenciahorariooferta A');
        $msql->setColumns('distinct A.ocorrenciahorarioofertaid, A.dataaula');
        $msql->setWhere("A.ofertacomponentecurricularid = $ofertacomponentecurricularid");
        $msql->setOrderBy('A.dataaula');

        if ( $professorid )
        {
            $msql->setWhere("A.professorid = $professorid");
        }
        
        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
    
    public function obterHorarioDeAula($ofertacomponentecurricularid, $ocorrenciahorarioofertaid=null)
    {
        $msql = new MSQL();
        $msql->setTables('acpocorrenciahorariooferta A');
        $msql->setColumns('A.ocorrenciahorarioofertaid, A.dataaula, B.horainicio, B.horafim, B.horarioid, A.conteudo');
        $msql->addLeftJoin('acphorario B','A.horarioid = B.horarioid');
        
        if( strlen($ofertacomponentecurricularid) > 0 )
        {
            $msql->addEqualCondition('A.ofertacomponentecurricularid', $ofertacomponentecurricularid);
        }
        if ( $ocorrenciahorarioofertaid )
        {
            $msql->addEqualCondition('A.ocorrenciahorarioofertaid', $ocorrenciahorarioofertaid);
        }
        
        $msql->setOrderBy('A.dataaula, B.horainicio');

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado;
    }
    
    public function obterHorariosPelaData($ofertacomponentecurricularid, $dataaula, $personid = null)
    {
        $msql = new MSQL();
        $msql->setTables('acpocorrenciahorariooferta A');
        $msql->setColumns('A.ocorrenciahorarioofertaid, A.dataaula, B.horainicio, B.horafim, B.horarioid, A.conteudo');
        $msql->addLeftJoin('acphorario B','A.horarioid = B.horarioid');
        
        if( strlen($ofertacomponentecurricularid) > 0 )
        {
            $msql->addEqualCondition('A.ofertacomponentecurricularid', $ofertacomponentecurricularid);
        }
        
        if ( $dataaula )
        {
            $msql->setWhere('A.dataAula = datetodb(?)', array($dataaula));
        }
        
        if ( $personid )
        {
            $msql->addEqualCondition('A.professorid', $personid);
        }
        
        $msql->setOrderBy('A.dataaula, B.horainicio');

        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado;
    }
    
    /**
     * @deprecated acabou ficando lento esta, favor utilizar funcao obterMatriculasOtimizado()
     * 
     * @return array
     */
    public function obterMatriculas($ofertacomponentecurricularid, $matriculasRetroativas = true)
    {
        return AcpMatricula::obterMatriculasNaOfertaDeComponenteCurricular($ofertacomponentecurricularid, $matriculasRetroativas);   
    }
    
    /**
     * @return array
     */
    public function obterMatriculasOtimizado($ofertacomponentecurricularid, $matriculasRetroativas = true)
    {
        $sql = new MSQL();
        $sql->setTables('acpmatricula m');
        $sql->setColumns('m.matriculaid,
                          pp.personid,
                          pp.name AS _pessoa,
                          m.frequencia');
        $sql->addLeftJoin('acpinscricaoturmagrupo itg', 'itg.inscricaoturmagrupoid = m.inscricaoturmagrupoid');
        $sql->addLeftJoin('acpinscricao i', 'i.inscricaoid = itg.inscricaoid');
        $sql->addLeftJoin('ONLY basphysicalperson pp', 'pp.personid = i.personid');
        $sql->addEqualCondition('m.ofertacomponentecurricularid', $ofertacomponentecurricularid);
        $sql->addWhereNotIn('m.situacao', array(AcpMatricula::SITUACAO_INSCRICAO, AcpMatricula::SITUACAO_CANCELAMENTO));
        $sql->setOrderBy('pp.name');
        
        if ( !$matriculasRetroativas )
        {
            $sql->setWhere('m.retroativa IS FALSE');
        }
        
        return SDatabase::queryAssociative($sql);
    }
        
    public function salvarNota($matriculaid, $componentedeavaliacaoid, $nota)
    {
        $avaliacao = AcpAvaliacao::obterAvaliacao($matriculaid, $componentedeavaliacaoid);
        
        if( !$avaliacao )
        {
            $avaliacao = new AcpAvaliacao();
            $avaliacao->componentedeavaliacaoid = $componentedeavaliacaoid;
            $avaliacao->matriculaid = $matriculaid;
        }
        $avaliacao->datalancamento = SAGU::getDateNow();
        $avaliacao->nota = strlen($nota) > 0 ? $nota : SType::NULL_VALUE;
        
        return $avaliacao->save();
    }
    
    public function salvarParecer($matriculaid, $parecer, $situacao)
    {
        $matricula = new AcpMatricula($matriculaid);
        $matricula->parecerfinal = $parecer;
        $matricula->situacao = $situacao;
        return $matricula->save();
    }
    
    public function salvarConceito($matriculaid, $componentedeavaliacaoid, $conceito)
    {
        $avaliacao = AcpAvaliacao::obterAvaliacao($matriculaid, $componentedeavaliacaoid);
        if( !strlen($avaliacao->avaliacaoid) > 0 )
        {
            $avaliacao = new AcpAvaliacao();
            $avaliacao->componentedeavaliacaoid = $componentedeavaliacaoid;
            $avaliacao->matriculaid = $matriculaid;
        }
        $avaliacao->datalancamento = SAGU::getDateNow();
        $avaliacao->conceitodeavaliacaoid = strlen($conceito) > 0 ? $conceito : SType::NULL_VALUE;
        
        return $avaliacao->save();
    }
    
    public function obterNotas($personId, $ofertaTurmaId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $notas = array();
        foreach ( $learningPeriodDegrees as $degree )
        {
            $filters = new stdClass();
            $filters->degreeId = $degree->degreeId;
            $filters->groupId = $groupId;

            $evaluations = $busEvaluation->searchEvaluation($filters);

            $subnotas = array();
            if ( count($evaluations) > 0 )
            {
                foreach ( $evaluations as $evaluation )
                {
                    $evaluationData = $busEvaluation->getEvaluation($evaluation[0]);
                    $evaluationGrade = $busEvaluationEnroll->getEvaluationEnrollCurrentGrade($evaluationData->evaluationId, $enrollId, $group->useConcept == DB_TRUE);
                    
                    $subnotas[$evaluationData->description] = $evaluationGrade;
                }
            }

            // Avaliação
            $degreeGrade = $busDegreeEnroll->getDegreeEnrollCurrentGrade($degree->degreeId, $enrollId, $group->useConcept == DB_TRUE);
            
            $notas[$degree->description]['nota'] = $degreeGrade;
            $notas[$degree->description]['degreeid'] = $degree->degreeId;
            $notas[$degree->description]['avaliacoes'] = $subnotas;
            $notas[$degree->description]['exame'] = $degree->isExam;
            $notas[$degree->description]['final'] = $degree->parentDegreeId ? false : true;
        }
        
        return $notas;
    }
    
    public function obterCoordenadoresDoCurso($cursoId)
    {
        $msql = new MSQL();
        $msql->setTables('acpcoordenadores C');
        $msql->setColumns('distinct C.personid');
        $msql->setWhere("C.cursoid = '{$cursoId}'");

        $resultado = bBaseDeDados::consultar($msql);

        return $resultado;
    }
}
?>