CREATE OR REPLACE function cr_fin_receitas_despesas_anual(IN ano text) 
RETURNS TABLE(centro_custo text, "Jan" numeric, "Fev" numeric, "Mar" numeric, "Abr" numeric, "Mai" numeric, "Jun" numeric, "Jul" numeric, "Ago" numeric, "Set" numeric, "Out" numeric, "Nov" numeric, "Dez" numeric)
AS
 $BODY$ 
DECLARE
v_sql TEXT;
BEGIN
    v_sql := '
    select * from crosstab(''
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''1\''' as Jan, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/01/01\''')::date, ((''''||$1||''''::text||\'''/01/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''2\''' as fev, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/02/01\''')::date, ((''''||$1||''''::text||\'''/02/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''3\''' as mar, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/03/01\''')::date, ((''''||$1||''''::text||\'''/03/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''4\''' as abr, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/04/01\''')::date, ((''''||$1||''''::text||\'''/04/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''5\''' as mai, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/05/01\''')::date, ((''''||$1||''''::text||\'''/05/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''6\''' as jun, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/06/01\''')::date, ((''''||$1||''''::text||\'''/06/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''7\''' as jul, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/07/01\''')::date, ((''''||$1||''''::text||\'''/07/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''8\''' as ago, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/08/01\''')::date, ((''''||$1||''''::text||\'''/08/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''9\''' as set, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/09/01\''')::date, ((''''||$1||''''::text||\'''/09/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''10\''' as out, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/10/01\''')::date, ((''''||$1||''''::text||\'''/10/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''11\''' as nov, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/11/01\''')::date, ((''''||$1||''''::text||\'''/11/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)
    union all 
    SELECT cod_centro_de_custo||\'''-\'''||centro_de_custo, \'''12\''' as dez, saldo as valor
    FROM cr_fin_balancete_por_centro(1, (''''||$1||''''::text||\'''/12/01\''')::date, ((''''||$1||''''::text||\'''/12/01\''')::date + interval \'''1 month\''' - interval \'''1 day\''')::date)''
    , ''select m from generate_series(1,12) m''
    ) as (
    cc text,
    "Jan" numeric,
    "Fev" numeric,
    "Mar" numeric,
    "Abr" numeric,
    "Mai" numeric,
    "Jun" numeric,
    "Jul" numeric,
    "Ago" numeric,
    "Set" numeric,
    "Out" numeric,
    "Nov" numeric,
    "Dez" numeric
    )'; 
    RETURN QUERY EXECUTE v_sql;
END;
$BODY$
LANGUAGE 'plpgsql';
