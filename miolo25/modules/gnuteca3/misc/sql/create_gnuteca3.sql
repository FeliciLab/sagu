
\echo "Voce precisa entrar no diretório onde os arquivos .sql estão!"

\c template1
\echo \n
\echo "Criando Base Gnuteca3"
CREATE DATABASE gnuteca3 WITH TEMPLATE = template1 ENCODING = 'UTF8';

ALTER DATABASE gnuteca3 OWNER TO postgres;

\echo "Conectando na base Gnuteca3";
\connect gnuteca3

\i dump_gnuteca3.sql
