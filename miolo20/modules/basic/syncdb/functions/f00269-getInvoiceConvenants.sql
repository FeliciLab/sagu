CREATE OR REPLACE FUNCTION getinvoiceconvenants(p_invoiceid integer, p_date date) 
RETURNS SETOF record AS
$BODY$
/*************************************************************************************
  NAME: getInvoiceConvenants
  PURPOSE: Obtém os convênios a serem aplicados em um título
  DESCRIPTION: vide "PURPOSE".


  REVISIONS:
  Ver       Date       Author            Description
  --------- ---------- ----------------- ------------------------------------
  1.0       07/12/2010 Leovan            1. FUNÇÃO criada.
  1.1       16/04/2012 Moises            1. Alterado calculo de convenios
  1.2       04/06/2012 Jonas Diel        1. Altera calculo de convenios no balance
  1.3       07/11/2012 Jonas Diel        1. Adicionada regra da data de ocorrencia
                                            para finais de semana (ENABLE_BUSINESS_USER = 1)
  1.4       23/11/2012 Jonas Diel        1. Ajustes para calcular convênios somente
                                         para lançamentos com opção 
                                         'Considerar em descontos' habilitada
  1.5       26/09/2014 Jonas Diel        1. Renomeada função para getinvoicecategorizedconvenants
                                          e antigo nome utilizado em nova função que realizará todas
                                          as chamadas.
  1.6       30/09/2014 ftomasini         1. Suporte a incentivos acumulativos
                                            e não acumulativos
                                            Quando convênio não acumulativo representa
                                            um valor maior do que todos acumulativos é 
                                            concedido apenas este, caso contrário 
                                            concede os convênios acumulativos.                                           
**************************************************************************************/
DECLARE
  v_convenant record;
  v_total_acumulativo numeric;
  v_valor_maior_convenio numeric;
  v_convenantid integer;
  v_convenio record;
BEGIN
v_total_acumulativo:=0;
v_valor_maior_convenio:=0;

  --Percorre todos convenios ACUMULATIVOS e soma o valor total
  FOR v_convenant IN SELECT * FROM getinvoicecategorizedconvenants(p_invoiceid, p_date) AS convenant(convenantid integer, description text, value numeric, ispercent boolean, convenantoperation int, acumulativo boolean, todasdisciplinas boolean, contractid integer, learningperiodid integer, operationid integer)
  LOOP
      IF v_convenant.acumulativo IS TRUE
      THEN
          v_total_acumulativo:= v_total_acumulativo + v_convenant.value;
      END IF;
  END LOOP;

  --Percorre todos os convênios NÃO ACUMULATIVOS e obtém o valor do maior
  FOR v_convenant IN SELECT * FROM getinvoicecategorizedconvenants(p_invoiceid, p_date) AS convenant(convenantid integer, description text, value numeric, ispercent boolean, convenantoperation int, acumulativo boolean, todasdisciplinas boolean, contractid integer, learningperiodid integer, operationid integer)
  LOOP
      IF v_convenant.acumulativo IS FALSE
      THEN
          IF v_valor_maior_convenio <= v_convenant.value
          THEN
              v_valor_maior_convenio:= v_convenant.value;
              v_convenantid := v_convenant.convenantid;
          END IF;
      END IF;
  END LOOP;

  --Retorna se o valor acumulativo é maior que o valor não acumulativo
  IF  v_valor_maior_convenio >= v_total_acumulativo THEN

      --Percorre todos os convênios não acumulativos e obtém o valor do maior
      FOR v_convenant IN SELECT * FROM getinvoicecategorizedconvenants(p_invoiceid, p_date) AS convenant(convenantid integer, description text, value numeric, ispercent boolean, convenantoperation int, acumulativo boolean, todasdisciplinas boolean, contractid integer, learningperiodid integer, operationid integer)
      LOOP
          IF v_convenant.acumulativo IS FALSE
          THEN
            IF v_valor_maior_convenio <= v_convenant.value
                THEN
                    v_valor_maior_convenio := v_convenant.value;
                    v_convenio := v_convenant;
            END IF;

            IF v_convenantid = v_convenant.convenantid 
            THEN
                RETURN NEXT v_convenant;
            END IF;
          END IF;

      END LOOP;
  ELSE
      --Percorre todos convenios acumulativos e soma o valor total
      FOR v_convenant IN SELECT * FROM getinvoicecategorizedconvenants(p_invoiceid, p_date) AS convenant(convenantid integer, description text, value numeric, ispercent boolean, convenantoperation int, acumulativo boolean, todasdisciplinas boolean, contractid integer, learningperiodid integer, operationid integer)
      LOOP
          IF v_convenant.acumulativo IS TRUE
          THEN
              RETURN NEXT v_convenant;
          END IF;
      END LOOP;
  END IF;

  RETURN;

END;
$BODY$
  LANGUAGE 'plpgsql';
