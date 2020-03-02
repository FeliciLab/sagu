<?php 

/*
 *	Script que é chamado pela crontab para iniciar o processo que envia emails
 *  em background para os agendamentos de emails. 
 * 
 */

// Instancia o MIOLO na variável $MIOLO
include_once 'miolo25.php';
$module = MIOLO::getCurrentModule();
$MIOLO->uses('types/avaAvaliacao.class.php', 'avinst');
$MIOLO->uses('types/avaTotalizadores.class.php', 'avinst');
$MIOLO->uses('classes/agranularity.class.php', 'avinst');

echo " == PROCESSO DE ATUALIZAÇÃO DE TOTALIZADORES DO SISTEMA AVINST == \n";
echo "Início do processo às ".date('d/m/Y G:i')."\n";
//
// Obtém todas as avaliações abertas no momento de atualização...
//
try
{
    echo "Obtendo avaliações abertas: ";
    $avaAvaliacao = new avaAvaliacao();
    $avaliacoes = $avaAvaliacao->getAvaliacoesAbertas(ADatabase::RETURN_TYPE);
    if (is_object($avaliacoes[0]))
    {
        $totAvaliacoes = count($avaliacoes);
        echo $totAvaliacoes." aberta(s) encontrada(s)\n";
        // Das avaliações abertas
        foreach ($avaliacoes as $avaliacao)
        {
            echo "Processando totalizadores da avaliacao ".$avaliacao->idAvaliacao." - ".$avaliacao->nome."\n";
            unset($granularidades);
            // Obtém os formulários
            //echo "Obtendo formulários da avaliação ".$avaliacao->idAvaliacao."\n";
            echo "Analisando granularidades...\n";
            $formularios = $avaliacao->getFormularios();
            if (is_object($formularios[0]))
            {
                // Dos formulários
                foreach ($formularios as $formulario)
                {
                    //echo "Analisando blocos do formulario ".$formulario->idFormulario." - ".$formulario->nome."\n";
                    // Obtém-se os blocos
                    $formulario->populate();
                    if (is_array($formulario->blocos))
                    {
                        //echo "Percorrendo blocos\n";
                        // Dos blocos
                        foreach ($formulario->blocos as $bloco)
                        {
                            //echo "Analisando bloco ".$bloco->idBloco."\n";
                            // Obtém-se as granularidades
                            $granularidade = $bloco->getGranularidade();
                            if ($granularidade->tipo == AGranularity::GRANULARITY_RETURN_ARRAY_OF_OBJECTS)
                            {
                                //echo "Bloco válido para estatísticas";
                                if (!isset($granularidades[$granularidade->idGranularidade]))
                                {
                                    // Alimenta um array para inserir os dados     
                                    $granularidades[$granularidade->idGranularidade] = $granularidade;
                                }
                            }
                        }
                    }
                    else
                    {
                        //echo "Blocos não existentes para o formulário ".$formulario->idFormulario."...";
                    }
                }
            }
            // Depois, verifica se existem granularidades a atualizar e atualiza...
            if (count($granularidades)>0)
            {
                echo "Há um total de ".(count($granularidades))." granularidade(s) válida(s) para o formulário ".$formulario->idFormulario." a processar para a avaliação, iniciando processamento da(s) granularidade(s)...\n";
                foreach ($granularidades as $idGranularidade => $granularidade)
                {
                    echo " => Processando granularidade ".$idGranularidade." :: ";
                    $data = new stdClass();
                    $idAvaliacao = $avaliacao->idAvaliacao;
                    $idGranularidade = $idGranularidade;
                    $avaTotalizadores = new avaTotalizadores();
                    // Atualiza aqui...
                    $status = $avaTotalizadores->atualizaTotalizadores($idAvaliacao, $idGranularidade);
                    if ($status->status == false)
                    {
                        echo "Não processada, motivo: ".$status->error."\n";
                    }
                    else
                    {
                        echo "Processada: ".$status->total." totalizador(es) atualizado(s)\n";
                    }
                }
            }
        }
    }    
    else
    {
        echo "Não foram encontradas avaliações abertas no presente momento, processo de atualização de totalizadores encerrado\n\n";
    }
}
catch (Exception $e)
{
    echo "Exceção de código, processo abortado, por favor, verifique o sistema\n\n";
}
echo "Fim do processo às ".date('d/m/Y G:i')."\n";
?>