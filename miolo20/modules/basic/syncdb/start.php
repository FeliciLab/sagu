<?php
$MIOLO = MIOLO::getInstance();

global $messagesG;
$messagesG = array();

function addMessage( $message )
{
    global $messagesG;
    $messagesG[] = $message;
}


addMessage('Executando scripts iniciais');

// Desativa escape automatico de strings
$dataBase = $MIOLO->getConf('db.academic.name');
sDataBase::getInstance()->execute("ALTER DATABASE \"$dataBase\" SET standard_conforming_strings = off");

// Atualiza permissao antiga FrmEnrollContract para FrmEnrollAlt1
$subSelect = "(SELECT idtransaction FROM miolo_transaction WHERE m_transaction='FrmEnrollAlt1')";
$subSelTransaction = "(SELECT idtransaction FROM miolo_transaction WHERE m_transaction='FrmEnrollContract')";
sDataBase::getInstance()->execute("UPDATE miolo_access SET idtransaction = {$subSelect} WHERE {$subSelect} IS NOT NULL AND idtransaction = {$subSelTransaction}");


//Garantindo da miolo_audit
sDataBase::getInstance()->execute(file_get_contents($MIOLO->getConf('home.miolo').'/modules/basic/syncdb/functions/f00025-miolo_audit_cache_primary_key.sql'));
sDataBase::getInstance()->execute(file_get_contents($MIOLO->getConf('home.miolo').'/modules/basic/syncdb/functions/f00026-miolo_audit_it.sql'));

sDataBase::getInstance()->execute("
--
-- ftomasini - Cria Tabela de diferenças caso ela não exista
--
CREATE OR REPLACE FUNCTION CriaTabela() RETURNS integer AS \$\$
DECLARE
foiCriada INTEGER :=0;
tabela RECORD;
BEGIN

SELECT INTO tabela tablename FROM pg_tables where tablename='dbchanges' and schemaname = ANY (current_schemas(true));

IF tabela.tablename IS NULL
THEN
    CREATE TABLE dbChanges
        (changeId SERIAL,
         change TEXT NOT NULL,
         applied BOOLEAN NOT NULL DEFAULT 'f',
         applicationVersion INTEGER NOT NULL,
         orderchange integer,
         error text,
         applieddate TIMESTAMP);

foiCriada = 1;

END IF;
RETURN foiCriada;
END;
\$\$ LANGUAGE plpgsql;

--
-- ftomasini - Cria tabela se não existe
--
SELECT CriaTabela();


CREATE OR REPLACE FUNCTION applyChanges(p_changes TEXT, p_changeid integer)
RETURNS BOOLEAN AS \$body\$
BEGIN
    RAISE NOTICE 'Executando Changeid % : Comando %', p_changeid, p_changes;
    EXECUTE p_changes;

    RETURN TRUE;
    EXCEPTION WHEN OTHERS
    THEN
        UPDATE dbchanges set error = SQLERRM, applieddate = now() WHERE changeid = p_changeid;

        RETURN FALSE;
    END;

\$body\$ language plpgsql;



CREATE OR REPLACE FUNCTION syncDataBase(p_applicationversion integer)
RETURNS BOOLEAN AS \$body\$
DECLARE
    line RECORD;
    result_change BOOLEAN;
BEGIN
    --Percorre comandos que n�o foram executados e aplica na base
    FOR line IN  SELECT * 
                   FROM dbchanges 
                  WHERE applied IS false 
                    AND applicationversion <= p_applicationversion 
                    AND error IS NULL
               ORDER BY applicationversion,orderchange
    LOOP
        SELECT INTO result_change applyChanges(line.change, line.changeid);

        IF result_change IS TRUE
        THEN
            EXECUTE 'UPDATE dbchanges SET applied = true, error = null, applieddate = now() WHERE changeId = '|| line.changeid || '';
        END IF;

    END LOOP;

    RETURN True;
END;
\$body\$ language plpgsql;

CREATE OR REPLACE FUNCTION criarBaseDeDadosMioloAuditExterna(p_stringConf TEXT, p_database TEXT)
RETURNS boolean
AS \$\$
/***************************************************************
  NAME: criarBaseDeDadosMioloAudit
  PURPOSE: Cria base de dados utilizando o db_link
  PARAMETERS: 
  p_stringConf - Configura��o do host de acesso (host, user, password, port)
  p_database - nome da base a ser criada                
****************************************************************/
DECLARE
        
BEGIN

    BEGIN
	PERFORM (SELECT dblink_exec(''|| p_stringConf::TEXT ||'', 'CREATE DATABASE '|| p_database::TEXT ||';'));    
    EXCEPTION
         WHEN others THEN
             RAISE NOTICE 'N�O FOI POSS�VEL CRIAR A BASE DE DADOS PARA A MIOLO AUDIT CONFIGURADA NO PARAMETRO (MIOLO_AUDIT_DATABASE). Erro: % ', SQLERRM;
	     RETURN FALSE;
    END;
	 
    RETURN TRUE;
END;
\$\$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION miolo_audit_upgrade(p_query TEXT)
RETURNS boolean
AS \$\$
/***************************************************************
  NAME: miolo_audit_upgrade
  PURPOSE: Executa um comando sql na base externa da miolo audit.
  PARAMETERS: p_query - String que recebe sql.
                
****************************************************************/
DECLARE
    --Valor configurado do par�metro (host)
    v_hostExterno TEXT;
    --Recebe o nome da base utilizada pelo sagu
    v_database TEXT;
    -- Recebe string de conex�o com o banco externo (dblink)
    v_conn TEXT;
    
BEGIN

    v_hostExterno := GETPARAMETER('BASIC', 'MIOLO_AUDIT_DATABASE');

    --Busca a base de dados atual usada pelo sagu
    SELECT INTO v_database current_database();

    --Monta o host externo para conectar ao dblink
    v_database := ' dbname=' || v_database || '_auditoria ';
    v_conn := v_database || v_hostExterno;
    
    IF LENGTH(v_hostExterno)>0 THEN

       BEGIN
       
       PERFORM (SELECT dblink_exec(''|| v_conn::TEXT ||'', '' || p_query || ''));
       EXCEPTION
           WHEN others THEN
                 --RAISE EXCEPTION 'N�O FOI POSS�VEL EXECUTAR ESSA QUERY NO BANCO CONFIGURADO PRA MIOLO_ADIT.'
                 RETURN FALSE;
       END;

    END IF;

    RETURN TRUE;
	 
END;
\$\$ LANGUAGE plpgsql;


"
);

// Rodar no inicio do syncDb para evitar problemas no Menu, pois ele � chamado provavelmente antes da leitura dos CHANGES.xml, o que causaria erro se estivesse la.
if ( !SDatabase::existeColunaDaTabela(null, 'basreport', 'hasgrouping') )
{
    sDataBase::getInstance()->execute("ALTER TABLE basReport ADD hasgrouping BOOLEAN DEFAULT FALSE");
}

// No arquivo views.sql nao funcionava...
// Deve remover algumas views ANTES de rodar o sync do functions.sql, pois sao dependentes
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS cmn_grupo CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS cmn_pessoas CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS cmn_vinculo CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS pessoas CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS rptpessoa CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS tra.requestcurrentdata CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS user_sagu CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS rptHorarios CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS user_sagu CASCADE");
sDataBase::getInstance()->execute("DROP FUNCTION IF EXISTS synchronizePasswordOfPerson() CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS rptContrato CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS receita CASCADE");
sDataBase::getInstance()->execute("DROP VIEW IF EXISTS lancamentos_financeiros CASCADE");
sDataBase::getInstance()->execute("DROP FUNCTION IF EXISTS getcontractclassid(integer) CASCADE");

// Seta datestyle sempre que o syncdb for executado
$dataBase = $MIOLO->getConf('db.academic.name');
sDataBase::getInstance()->execute("ALTER DATABASE \"$dataBase\" SET DateStyle TO 'ISO, DMY'");

//Cria��o da base de dados para miolo_audit
$confMioloAudit = SAGU::getParameter('BASIC', 'MIOLO_AUDIT_DATABASE');

$database = $MIOLO->getConf('db.basic.name');
$database = $database.'_auditoria';

//Cria��o das tabelas miolo_audit e miolo_audit_detail
$sql1 = estruturaMioloAudit();
$sql2 = alterTableMioloAudit();
$sql3 = estruturaMioloAuditDetail();
$sql4 = indexTabelasAuditoria();

//Verifica se par�metro est� configurado, caso contr�rio, cria 
// uma base de dados utilizando configura��es do miolo.conf
if( !(strlen($confMioloAudit) > 0) )
{
    $password = $MIOLO->getConf('db.basic.password');
    $port = $MIOLO->getConf('db.basic.port');
    $host = $MIOLO->getConf('db.basic.host');
    $user = $MIOLO->getConf('db.basic.user');
    
    //Tenta conectar com a base, utilizando configura��es do miolo.conf
    $stringConf = "host=$host dbname=$database port=$port user=$user password=$password";
    $con = pg_connect($stringConf);
    
    //Se n�o conseguiu conex�o, cria a base
    if(!$con)
    {
       $base = sDataBase::getInstance()->execute("CREATE DATABASE {$database};"); 
              
       //Se a base foi criada, insere a string de configura��o no par�metro
       if($base)
       {   
           //String de configura��o sem o nome do banco de dados
           $string = "host=$host port=$port user=$user password=$password"; 
           SDatabase::getInstance()->execute("UPDATE basconfig SET value = '{$string}' WHERE parameter = 'MIOLO_AUDIT_DATABASE' AND moduleconfig = 'BASIC'");
               
           SDatabase::getInstance()->execute("SELECT miolo_audit_upgrade('$sql1')");
           SDatabase::getInstance()->execute("SELECT miolo_audit_upgrade('$sql2')");
           SDatabase::getInstance()->execute("SELECT miolo_audit_upgrade('$sql3')");
           SDatabase::getInstance()->execute("SELECT miolo_audit_upgrade('$sql4')");
       }
    }
}
else
{
    //Host de configura��o de acesso passando a base como postgres
    $stringConf = $confMioloAudit." dbname=postgres";
    
    //Tenta conectar com a base, utilizando configura��es do par�metro
    $stringConfConnect = $confMioloAudit." dbname=".$database;    
    $con = pg_connect($stringConfConnect);
    
    //Se n�o conseguiu conectar, cria uma nova
    if(!$con)
    {
        SDatabase::getInstance()->execute("SELECT criarBaseDeDadosMioloAuditExterna('{$stringConf}','{$database}')");
            
        SDatabase::getInstance()->execute("SELECT miolo_audit_upgrade('$sql1')");
        SDatabase::getInstance()->execute("SELECT miolo_audit_upgrade('$sql2')");
        SDatabase::getInstance()->execute("SELECT miolo_audit_upgrade('$sql3')");
        SDatabase::getInstance()->execute("SELECT miolo_audit_upgrade('$sql4')");
    }
    
}

function estruturaMioloAudit()
{
    $sql = "CREATE TABLE miolo_audit (
                audit_id integer NOT NULL,
                schema_name text NOT NULL,
                table_name text NOT NULL,
                user_name text,
                action_timestamp_utc timestamp without time zone DEFAULT timezone('UTC'::text, now()) NOT NULL,
                action text NOT NULL,
                query text,
                CONSTRAINT miolo_audit_action_check CHECK ((action = ANY (ARRAY['INSERT'::text, 'DELETE'::text, 'UPDATE'::text])))
            )";
    $sql = str_replace("'", "''", $sql);
    
    return $sql;
}

function alterTableMioloAudit()
{
    $sql = "ALTER TABLE public.miolo_audit OWNER TO postgres;

            CREATE SEQUENCE miolo_audit_audit_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;

            ALTER TABLE public.miolo_audit_audit_id_seq OWNER TO postgres;

            ALTER SEQUENCE miolo_audit_audit_id_seq OWNED BY miolo_audit.audit_id;

            ALTER TABLE ONLY miolo_audit ALTER COLUMN audit_id SET DEFAULT nextval('miolo_audit_audit_id_seq'::regclass);

            ALTER TABLE ONLY miolo_audit
                ADD CONSTRAINT miolo_audit_pkey PRIMARY KEY (audit_id);";
    
    $sql = str_replace("'", "''", $sql);
                
    return $sql;
}
  

function estruturaMioloAuditDetail()
{
    $sql = "CREATE TABLE miolo_audit_detail (
                audit_id integer NOT NULL,
                column_name text NOT NULL,
                original_value text,
                new_value text,
                is_pkey boolean DEFAULT false NOT NULL
            );

            ALTER TABLE public.miolo_audit_detail OWNER TO postgres;

            ALTER TABLE ONLY miolo_audit_detail
                ADD CONSTRAINT miolo_audit_fk FOREIGN KEY (audit_id) REFERENCES miolo_audit(audit_id);"; 
       
    return $sql;
}

function indexTabelasAuditoria()
{
    $sql = "CREATE INDEX audit_id_idx ON miolo_audit(audit_id);
            CREATE INDEX action_timestamp_utc_idx ON miolo_audit(action_timestamp_utc);
            CREATE INDEX table_name_idx ON miolo_audit(table_name);
            CREATE INDEX user_name_idx ON miolo_audit(user_name);
            CREATE INDEX audit_id_detail_idx ON miolo_audit_detail(audit_id);";
    
    return $sql;
}

//Exclui os registros da basaccess que tenham mais que 90 dias
sDataBase::getInstance()->execute(" ALTER TABLE basaccess DISABLE TRIGGER ALL; ");
sDataBase::getInstance()->execute(" DELETE FROM basaccess WHERE  datetime < (now()::TIMESTAMP - interval '90 days')::TIMESTAMP; ");
sDataBase::getInstance()->execute(" ALTER TABLE basaccess ENABLE TRIGGER ALL; ");

//Garantindo a criação e atualização dos 
sDataBase::getInstance()->execute(file_get_contents($MIOLO->getConf('home.miolo').'/modules/basic/syncdb/functions/f00311-updateSequences.sql'));

sDataBase::getInstance()->execute(" SELECT * FROM updateSequences();");
?>
