<?php
require dirname(__FILE__) . '/../../basic/classes/sconsole.php';

SDatabase::beginTransaction();

// para remover TODOS registros inseridos do pedagogico, utilizar:
// BEGIN; TRUNCATE acpperfilcurso CASCADE; TRUNCATE acpmatrizcurriculargrupo cascade; TRUNCATE acpmodelodeavaliacao cascade; TRUNCATE prccondicaodepagamento CASCADE; ROLLBACK;
// cuidado: utilizar com cautela !!

try
{
    inserirTudo();
    
    $matricula = new AcpMatricula();
    $inscricao = new AcpInscricao();
    
//    $novaInsc = new AcpInscricao( $inscricao->getLastInsertId() );
    
//    testaTitulos($inscricao, $novaInsc);

    $novaInsc = new AcpInscricao( $inscricao->getLastInsertId() );
    
//    $oldMat = $matricula->getLastInsertId();
    
//    $novaInsc->alterarParaInscrito();
//    $matricula->inserirDaInscricao($inscricao->getLastInsertId());
    
//    if ( $oldMat == $matricula->getLastInsertId() )
//    {
//        throw new Exception(_M('Nao foi inserido uma nova matricula! Verifique o teste.'));
//    }
    
    $novaMat = new AcpMatricula( $matricula->getLastInsertId() );
//    $novaMat->confirmarMatricula();
    
    consoleOutput("Status inscricao (cod. {$novaInsc->getLastInsertId()}): " . $novaInsc->situacao);
    consoleOutput("Status matricula (cod. {$novaMat->getLastInsertId()}): " . $novaMat->situacao);
}
catch (Exception $e)
{
    consoleOutput($e);
}

SDatabase::commit();
//SDatabase::rollback();

function inserirTudo()
{
    $modeloDeAvaliacao = insereModeloAvaliacao();
    
    $perfil = new AcpPerfilCurso();
    $perfil->descricao = 'Meu Perfil';
    $perfil->formadeoferta = AcpPerfilCurso::OFERTA_TURMA;
    $perfil->ativo = DB_TRUE;
    $perfil->organizacao = AcpPerfilCurso::ORGANIZACAO_CONTINUO;
    $perfil->formacursarcomponentescurriculares = AcpPerfilCurso::FORMA_CURSAR_COMPONENTES_SEQUENCIAL;
    $perfil->permiteinscricaoporgrupo = DB_TRUE;
//    $perfil->permiteinscricaoporgrupo = DB_FALSE;
    $perfil->modelodeavaliacaogeral = $modeloDeAvaliacao->getLastInsertId();
    $perfil->save();
    
    $regras = new AcpRegrasMatriculaPerfilCurso();
    $regras->perfilcursoid = $perfil->getLastInsertId();
    $regras->tipoinscricao = AcpRegrasMatriculaPerfilCurso::TIPO_INSCRICAO_ATENDIMENTO;
//    $regras->formadeconfirmacaoinscricao = AcpRegrasMatriculaPerfilCurso::CONFIRMACAO_INSCRICAO_PGTO_TAXA;
    $regras->formadeconfirmacaoinscricao = AcpRegrasMatriculaPerfilCurso::CONFIRMACAO_INSCRICAO_NENHUM;
    $regras->tipomatricula = AcpRegrasMatriculaPerfilCurso::TIPO_MATRICULA_ATENDIMENTO;
    $regras->formadeconfirmacaomatricula = AcpRegrasMatriculaPerfilCurso::CONFIRMACAO_MATRICULA_PGTO_TAXA;
//    $regras->formadeconfirmacaomatricula = AcpRegrasMatriculaPerfilCurso::CONFIRMACAO_MATRICULA_NENHUM;
    $regras->save();
    
    $curso = new AcpCurso();
    $curso->perfilcursoid = $perfil->getLastInsertId();
    $curso->nome = 'Meu curso';
    $curso->codigo = 'MC';
    $curso->situacao = AcpCurso::SITUACAO_ATIVO;
    $curso->modalidade = AcpCurso::MODALIDADE_PRESENCIAL;
    $curso->percentualcargahorariadistancia = 10;
    $curso->numeroformalvagas = 5;
    $curso->datainicio = SAGU::getDateNow();
    $curso->datafim = SAGU::addIntervalInDate(SAGU::getDateNow(), 'd', 30);
//    $curso->gratuito = DB_FALSE;
    $curso->gratuito = DB_TRUE;
    $curso->save();

    $ocorrenciaCurso = new AcpOcorrenciaCurso();
    $ocorrenciaCurso->unitId = 1;
    $ocorrenciaCurso->turnId = 1;
    $ocorrenciaCurso->situacao = AcpOcorrenciaCurso::SITUACAO_ATIVO;
    $ocorrenciaCurso->cursoid = $curso->getLastInsertId();
    $ocorrenciaCurso->save();

    $grade = new AcpGradeHorario();
    $grade->descricao = 'Minha grade';
    $grade->ativo = DB_TRUE;
    $grade->save();
    
    $horario = new AcpHorario();
    $horario->gradehorarioid = $grade->getLastInsertId();
    $horario->horainicio = '08:00';
    $horario->horafim = '10:00';
    $horario->diasemana = 1;
    $horario->minutosfrequencia = '120';
    $horario->save();
    $horarios[] = $horario;
    
    $horario = new AcpHorario();
    $horario->gradehorarioid = $grade->getLastInsertId();
    $horario->horainicio = '18:00';
    $horario->horafim = '21:00';
    $horario->diasemana = 1;
    $horario->minutosfrequencia = '120';
    $horario->save();
    $horarios[] = $horario;
    
    $ofertaCurso = new AcpOfertaCurso();
    $ofertaCurso->ocorrenciacursoid = $ocorrenciaCurso->getLastInsertId();
    $ofertaCurso->descricao = 'Minha oferta curso';
    $ofertaCurso->datainicialoferta = SAGU::getDateNow();
    $ofertaCurso->datafinaloferta = SAGU::addIntervalInDate(SAGU::getDateNow(), 'd', 30);
    $ofertaCurso->situacao = AcpOfertaCurso::SITUACAO_ATIVO;
    $ofertaCurso->gradehorarioid = $grade->getLastInsertId();
    $ofertaCurso->taxainscricao = DB_TRUE;
//    $ofertaCurso->taxainscricao = DB_FALSE;
    $ofertaCurso->save();
    
    $ofertaTurma = new AcpOfertaTurma();
    $ofertaTurma->codigo = 'TUR01';
    $ofertaTurma->descricao = 'Oferta turma 01';
    $ofertaTurma->ofertacursoid = $ofertaCurso->getLastInsertId();
    $ofertaTurma->habilitada = DB_TRUE;
    $ofertaTurma->gradehorarioid = $grade->getLastInsertId();
    $ofertaTurma->save();
    
    $matrizcurricular = new AcpMatrizCurricular();
    $matrizcurricular->cursoid = $curso->getLastInsertId();
    $matrizcurricular->descricao = 'Minha matriz';
    $matrizcurricular->series = 0;
    $matrizcurricular->situacao = AcpMatrizCurricular::SITUACAO_ATIVO;
    $matrizcurricular->datainicial = SAGU::getDateNow();
    $matrizcurricular->save();
    
//    if ( $perfil->permiteinscricaoporgrupo == DB_TRUE )
    {
        $matrizcurriculargrupo = new AcpMatrizCurricularGrupo();
        $matrizcurriculargrupo->matrizcurricularid = $matrizcurricular->getLastInsertId();
        $matrizcurriculargrupo->descricao = 'Meu grupo matriz';
        $matrizcurriculargrupo->ordem = 1;
        $matrizcurriculargrupo->save();
    }
    
    $matrizcurriculargrupo2 = new AcpMatrizCurricularGrupo();
    $matrizcurriculargrupo2->matrizcurricularid = $matrizcurricular->getLastInsertId();
    $matrizcurriculargrupo2->descricao = 'Meu segundo grupo';
    $matrizcurriculargrupo2->ordem = 2;
    $matrizcurriculargrupo2->save();
    
    $componenteCurricular = new AcpComponenteCurricular();
    $componenteCurricular->matrizcurriculargrupoid = $matrizcurriculargrupo ? $matrizcurriculargrupo->getLastInsertId() : null;
    $componenteCurricular->nome = 'Meu componente';
    $componenteCurricular->codigo = 'MEUCOMP';
    $componenteCurricular->descricao = 'Componente do Moises';
    $componenteCurricular->conteudo = 'Conteudo deste componente';
    $componenteCurricular->tipocomponentecurricularid = 1; // 1 = DISCIPLINAS
    $componenteCurricular->save();
    
    $ccMatriz = new AcpComponenteCurricularMatriz();
    $ccMatriz->matrizcurriculargrupoid = $matrizcurriculargrupo ? $matrizcurriculargrupo->getLastInsertId() : null;
    $ccMatriz->componentecurricularid = $componenteCurricular->getLastInsertId();
    $ccMatriz->obrigatorio = DB_FALSE;
    $ccMatriz->situacao = DB_TRUE; // = ATIVO
    $ccMatriz->save();
    
    $ofertaCC = new AcpOfertaComponenteCurricular();
    $ofertaCC->ofertaTurmaId = $ofertaTurma->getLastInsertId();
    $ofertaCC->dataInicio = SAGU::getDateNow();
    $ofertaCC->componenteCurricularMatrizId = $ccMatriz->getLastInsertId();
    $ofertaCC->save();
    
    foreach ( $horarios as $hor )
    {
        $hor instanceof AcpHorario;
        
        $ocorrenciaHorario = new AcpOcorrenciaHorarioOferta();
        $ocorrenciaHorario->dataaula = SAGU::getDateNow();
        $ocorrenciaHorario->possuifrequencia = DB_TRUE;
        $ocorrenciaHorario->cancelada = DB_FALSE;
        $ocorrenciaHorario->professorid = 224;
        $ocorrenciaHorario->repete = DB_FALSE;
        $ocorrenciaHorario->ofertacomponentecurricularid = $ofertaCC->getLastInsertId();
        $ocorrenciaHorario->horarioid = $hor->getPkeyValue();
        $ocorrenciaHorario->save();
    }
    
    $busBankAccount = new BusinessFinanceBusBankAccount();
    $costCenter = new BusinessAccountancyBusCostCenter();
    
    $busDefaultOperation = new BusinessFinanceBusDefaultOperations();

    $operationIdMonthy = $busDefaultOperation->getDefaultOperation('monthlyfeeoperation');
    $operationIdEnroll = $busDefaultOperation->getDefaultOperation('enrollOperation');
    $operationIdRenewal = $busDefaultOperation->getDefaultOperation('renewalOperation');
    
    $condicao = new PrcCondicaoDePagamento();
    $condicao->descricao = 'Condicao pgto moi';
    $condicao->exigeentrada = DB_FALSE;
    $condicao->numerodeparcelas = 2;
    $condicao->save();
    
    // preco curso inscricao
    $precoCursoInsc = new PrcPrecoCurso();
    $precoCursoInsc->ofertaturmaid = $ofertaTurma->getLastInsertId();
    $precoCursoInsc->ocorrenciacursoid = $ocorrenciaCurso->getLastInsertId();
    $precoCursoInsc->diasvencimentoentrada = 10;
    $precoCursoInsc->tipo = PrcPrecoCurso::TIPO_INSCRICAO;
    $precoCursoInsc->costcenterid = $costCenter->getLastInsertId();
    $precoCursoInsc->bankaccountid = $busBankAccount->getLastInsertId();
//    $precoCurso->operationid = SAGU::getParameter('BASIC', 'BANK_PAYMENT_OPERATION_ID');
    $precoCursoInsc->operationid = $operationIdEnroll;
    $precoCursoInsc->datainicial = SAGU::getDateNow();
    $precoCursoInsc->valoravista = 5;
    $precoCursoInsc->save();
    
    $precoCondInsc = new PrcPrecoCondicao();
    $precoCondInsc->condicaodepagamentoid = $condicao->getLastInsertId();
    $precoCondInsc->precocursoid = $precoCursoInsc->getLastInsertId();
    $precoCondInsc->valorparcela = 100;
    $precoCondInsc->valortotal = 200;
    $precoCondInsc->save();
    
    $precoCondInscId = $precoCondInsc->getLastInsertId();
    
    // preco curso matricula
    $precoCursoMat = new PrcPrecoCurso();
    $precoCursoMat->ofertaturmaid = $ofertaTurma->getLastInsertId();
    $precoCursoMat->ocorrenciacursoid = $ocorrenciaCurso->getLastInsertId();
    $precoCursoMat->diasvencimentoentrada = 10;
    $precoCursoMat->tipo = PrcPrecoCurso::TIPO_MATRICULA;
    $precoCursoMat->costcenterid = $costCenter->getLastInsertId();
    $precoCursoMat->bankaccountid = $busBankAccount->getLastInsertId();
//    $precoCurso->operationid = SAGU::getParameter('BASIC', 'BANK_PAYMENT_OPERATION_ID');
    $precoCursoMat->operationid = $operationIdEnroll;
    $precoCursoMat->save();
    
    $precoCondMat = new PrcPrecoCondicao();
    $precoCondMat->condicaodepagamentoid = $condicao->getLastInsertId();
    $precoCondMat->precocursoid = $precoCursoMat->getLastInsertId();
    $precoCondMat->valorparcela = 100;
    $precoCondMat->valortotal = 200;
    $precoCondMat->save();
    
    $precoCondMatId = $precoCondMat->getLastInsertId();
    
    $diavenc = new PrcDiaDeVencimento();
    $diavenc->dia = 5;
    $diavenc->save();

    $inscricao = new AcpInscricao();
    $inscricao->ofertaturmaid = $ofertaTurma->getLastInsertId();
    $inscricao->ofertacursoid = $ofertaCurso->getLastInsertId();
    $inscricao->personid = 3826;
    $inscricao->situacao = AcpInscricao::SITUACAO_PENDENTE;
    $inscricao->datasituacao = SAGU::getDateNow();
    $inscricao->origem = AcpInscricao::ORIGEM_ADMINISTRATIVA;
    $inscricao->precocursoid = $precoCursoInsc->getLastInsertId();
    $inscricao->precocondicaoinscricaoid = $precoCondInscId;
    $inscricao->precocondicaomatriculaid = $precoCondMatId;
    $inscricao->diadevencimentoid = $diavenc->getLastInsertId();
    $inscricao->save();
    
    $inscricaoturmagrupo = new AcpInscricaoTurmaGrupo();
    $inscricaoturmagrupo->inscricaoid = $inscricao->getLastInsertId();
    $inscricaoturmagrupo->ofertaturmaid = $ofertaTurma->getLastInsertId();
    $inscricaoturmagrupo->matrizcurriculargrupoid = $matrizcurriculargrupo ? $matrizcurriculargrupo->getLastInsertId() : null;
    $inscricaoturmagrupo->salvarTurmaGrupo();
}

function testaTitulos(AcpInscricao $inscricao, AcpInscricao $novaInsc)
{
    $matricula = new AcpMatricula();
    
    $tituloInscricao = new PrcTituloInscricao();
    $tituloInscricao->gerarTitulos($inscricao->getLastInsertId(), PrcTituloInscricao::TIPO_INSCRICAO);

    $rows = $tituloInscricao->findMany( $tituloInscricao->msql()->addEqualCondition('PrcTituloInscricao.inscricaoid', $inscricao->getLastInsertId()) );

    consoleOutput('Total titulos (antes): ' . count($rows));
    $novaInsc->alterarParaInscrito();

    $rows = $tituloInscricao->findMany( $tituloInscricao->msql()->addEqualCondition('PrcTituloInscricao.inscricaoid', $inscricao->getLastInsertId()) );

    consoleOutput('Total titulos (depois): ' . count($rows));

    foreach ( $rows as $row )
    {
        consoleOutput("Invoice: " . $row->invoiceId);

        $busInvoice = new BusinessFinanceBusInvoice();
        $busInvoice->closeInvoice($row->invoiceId, $row->value);
    }
}

function insereModeloAvaliacao()
{
    $modelo = new AcpModeloDeAvaliacao();
    $modelo->descricao = 'Modelito';
    $modelo->tipoDeDados = AcpModeloDeAvaliacao::TIPO_NOTA;
    $modelo->aplicacao = AcpModeloDeAvaliacao::APLICACAO_CURSO;
    $modelo->ativo = DB_TRUE;
    $modelo->save();
    
    for ($i=1; $i <= 3; $i++)
    {
        $componente = new AcpComponenteDeAvaliacao();
        $componente->modeloDeAvaliacaoId = $modelo->getLastInsertId();
        $componente->ordem = $i;
        $componente->descricao = 'N' . $i;
        $componente->detalhes = 'Nota ' . $i;
        $componente->classeDeComponente = AcpComponenteDeAvaliacao::CLASSE_NORMAL;
        $componente->permiteAlteracoes = DB_TRUE;
        $componente->save();

        $componenteNota = new AcpComponenteDeAvaliacaoNota();
        $componenteNota->componenteDeAvaliacao = new AcpComponenteDeAvaliacao($componente->getLastInsertId());
        $componenteNota->formaDeCalculo = AcpComponenteDeAvaliacaoNota::FORMA_CALCULO_VALOR;
        $componenteNota->peso = 1;
        $componenteNota->grauDePrecisao = 1;
        $componenteNota->valorMinimo = 1;
        $componenteNota->valorMaximo = 10;
        $componenteNota->valorMinimoAprovacao = 5;
        $componenteNota->save();
    }
    
    return $modelo;
}

?>