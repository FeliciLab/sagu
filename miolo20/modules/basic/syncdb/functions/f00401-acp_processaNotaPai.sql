--Criada primeiramente zerada, para que não ocorram erros por utilizar recursividade.
CREATE OR REPLACE FUNCTION acp_processaNotaPai(p_matriculaId INT, p_componenteDeAvaliacaoId INT)
RETURNS BOOLEAN AS
$BODY$
BEGIN
    RETURN TRUE;
END;
$BODY$
LANGUAGE plpgsql;
--

CREATE OR REPLACE FUNCTION acp_processaNotaPai(p_matriculaId INT, p_componenteDeAvaliacaoId INT)
RETURNS BOOLEAN AS
$BODY$
DECLARE
    v_componentePai acpComponenteDeAvaliacao;
    v_componenteNotaPai acpComponenteDeAvaliacaoNota;
    v_notaPai NUMERIC := 0;
    v_avaliacaoNotaPai AcpAvaliacao;

    v_componentesFilhos RECORD;
    v_componenteNotaFilho acpComponenteDeAvaliacaoNota;
    v_notaFilho NUMERIC;

    v_quantidadeDeFilhos INT := 0;
    v_somaPeso INT := 0;
BEGIN
    --Obtém componente de avaliação pai.
    SELECT INTO v_componentePai
	        CDA.*
           FROM acpRelacionamentoDeComponentes RDC
     INNER JOIN acpComponenteDeAvaliacao CDA
	     ON CDA.componenteDeAvaliacaoId = RDC.componenteDeAvaliacaoPai
          WHERE RDC.componenteDeAvaliacaoFilho = p_componenteDeAvaliacaoId;

    --Obtém a configuração de nota, do componente de avaliação pai.
    SELECT INTO v_componenteNotaPai
	        CDAN.*
           FROM acpComponenteDeAvaliacaoNota CDAN
          WHERE CDAN.componenteDeAvaliacaoId = v_componentePai.componenteDeAvaliacaoId;
          
    --Obtém a quantidade de filhos do componente de avaliação pai.
    SELECT INTO v_quantidadeDeFilhos
                COUNT(CDA.*)
           FROM acpRelacionamentoDeComponentes RDC
     INNER JOIN acpComponenteDeAvaliacao CDA
             ON CDA.componenteDeAvaliacaoId = RDC.componenteDeAvaliacaoFilho
          WHERE RDC.componenteDeAvaliacaoPai = v_componentePai.componenteDeAvaliacaoId
            AND CDA.classeDeComponente <> 'R'; --Exclui recuperações.

    --Verifica se o componente de avaliacao pai possui filhos.
    IF ( v_quantidadeDeFilhos > 0 )
    THEN
        --Percorre os componentes de avaliação filhos.
        FOR v_componentesFilhos IN
            (SELECT CDA.*
               FROM acpRelacionamentoDeComponentes RDC
         INNER JOIN acpComponenteDeAvaliacao CDA
                 ON CDA.componenteDeAvaliacaoId = RDC.componenteDeAvaliacaoFilho
              WHERE RDC.componenteDeAvaliacaoPai = v_componentePai.componenteDeAvaliacaoId
                AND CDA.classeDeComponente <> 'R') --Exclui recuperações.
        LOOP
            v_notaFilho := 0;

            --Obtém a configuração de nota, do componente de avaliação filho.
            SELECT INTO v_componenteNotaFilho
                        CDAN.*
                   FROM acpComponenteDeAvaliacaoNota CDAN
                  WHERE CDAN.componenteDeAvaliacaoId = v_componentesFilhos.componenteDeAvaliacaoId;

            --Obtém a nota da matrícula, no componente de avaliação filho.
            SELECT INTO v_notaFilho
                        A.nota
                   FROM acpAvaliacao A
                  WHERE A.matriculaId = p_matriculaId
                    AND A.componenteDeAvaliacaoId = v_componentesFilhos.componenteDeAvaliacaoId;

            --Aplica regras de forma de cálculo para a nota.
            --Soma ou média (sim, para média também, mais adiante é dado continuação do cálculo para média).
            IF ( (v_componenteNotaPai.formaDeCalculo = 'S') OR 
                 (v_componenteNotaPai.formaDeCalculo = 'M') )
            THEN
                SELECT INTO v_notaPai 
                            ROUND((v_notaPai + v_notaFilho), v_componenteNotaPai.grauDePrecisao);

            --Média ponderada.
            ELSEIF ( v_componenteNotaPai.formaDeCalculo = 'P' )
            THEN
                SELECT INTO v_notaPai 
                            ROUND(((v_notaPai + v_notaFilho) * COALESCE(v_componenteNotaFilho.peso, 1)), v_componenteNotaPai.grauDePrecisao);

                v_somaPeso := v_somaPeso + COALESCE(v_componenteNotaFilho.peso, 1);

            --Substituir se é maior.
            ELSEIF ( v_componenteNotaPai.formaDeCalculo = 'A' )
            THEN
                SELECT INTO v_notaPai
                            ROUND((CASE WHEN ( v_notaFilho > COALESCE(A.nota, 0) )
                                        THEN
                                             v_notaFilho
                                        ELSE
                                             COALESCE(A.nota, 0)
                                   END), v_componenteNotaPai.grauDePrecisao)
                       FROM acpAvaliacao A
                      WHERE A.matriculaId = p_matriculaId
                        AND A.componenteDeAvaliacaoId = p_componenteDeAvaliacaoId;

            --Substituir sempre.
            ELSEIF ( v_componenteNotaPai.formaDeCalculo = 'E' )
            THEN
                v_notaPai := v_notaFilho;
            END IF;
        END LOOP;

        --Finaliza cálculo da Média.
        IF ( v_componenteNotaPai.formaDeCalculo = 'M' )
        THEN
            SELECT INTO v_notaPai
                        ROUND((v_notaPai / v_quantidadeDeFilhos), v_componenteNotaPai.grauDePrecisao);

        --Finaliza cálculo da Média ponderada.
        ELSEIF ( v_componenteNotaPai.formaDeCalculo = 'P' )
        THEN
            SELECT INTO v_notaPai
                        ROUND((v_notaPai / v_somaPeso), v_componenteNotaPai.grauDePrecisao);
        END IF;

        --Obtém a avaliação da matrícula para a nota pai, caso exista.
        SELECT INTO v_avaliacaoNotaPai
                    *
               FROM acpAvaliacao
              WHERE matriculaId = p_matriculaId
                AND componenteDeAvaliacaoId = v_componentePai.componenteDeAvaliacaoId;

         --Salvar a nota pai.
         IF ( v_avaliacaoNotaPai.avaliacaoId IS NOT NULL )
         THEN
             UPDATE acpAvaliacao
                SET datalancamento = NOW()::DATE,
                    nota = (CASE WHEN ( v_notaPai >= 0 )
                                 THEN
                                      v_notaPai
                                 ELSE
                                      NULL
                            END)
              WHERE matriculaId = p_matriculaId
                AND componenteDeAvaliacaoId = v_componentePai.componenteDeAvaliacaoId;
         ELSE
             INSERT INTO acpAvaliacao
                         (matriculaId,
                          componenteDeAvaliacaoId,
                          datalancamento,
                          nota)
                  VALUES (p_matriculaId,
                          v_componentePai.componenteDeAvaliacaoId,
                          NOW()::DATE,
                          (CASE WHEN ( v_notaPai >= 0 )
                                THEN
                                     v_notaPai
                                ELSE
                                     NULL
                            END));
         END IF;

        --Processar a função para o componente pai.
        PERFORM acp_processaNotaPai(p_matriculaId, v_componentePai.componenteDeAvaliacaoId);
    END IF;

    RETURN TRUE;
END;
$BODY$
LANGUAGE plpgsql;
