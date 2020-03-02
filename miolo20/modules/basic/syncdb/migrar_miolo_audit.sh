#!/bin/bash

if [ "$1" = "-help" ]; then
    echo "------------------------------------------------------------------------------------------------------------------------------------------
* Para fazer a migração de dados das tabelas miolo_audit e miolo_audit_detail, é preciso rodar o seguinte script.sh que se encontra em:
/var/www/<nome do sagu>/miolo20/modules/basic/syncdb. *
 
* Executar o seguinte comando: ./migrar_miolo_audit.sh '-Upostgres -hlocalhost base_de_dados' '-Upostgres -hlocalhost -d base_de_dados_auditoria' *

** Sintaxe do primeiro parâmetro(recebe os dados da base que será feito o backup): -Upostgres(usuário do postgres) -hlocalhost(host de acesso onde encontra-se a base de dados ex. localhost, 192.168.1.100) base_de_dados(o nome da base de dados utilizada pelo sagu) ** 

** Sintaxe segundo parâmetro(recebe os dados da base que será restaurado o backup): -Upostgres(usuário do postgres) -hlocalhost(host de acesso onde encontra-se a base de dados a ser restaurado o dump ex. localhost, 192.168.1.100) -d (é utilizado o menos d, devido a função que irá restaurar os dados na nova base de auditoria - pg_restore) base_de_dados_auditoria(o nome da base de dados utilizada pelo sagu para auditoria - este nome é o nome da base atual do sagu concatenada com '_auditoria') **

** Certifique-se que há espaço em disco tanto na máquina que irá ser feito o backup(irá armazenar um arquivo temporário de dump) quanto na base a ser restaurado. **

** Em uma base de dados muito grande, o processo pode levar mais que 30 minutos. Certifique-se de ter tempo hábil para executar o processo. **
----------------------------------------------------------------------------------------------------------------------------------------------------" 
    exit 1;
fi 

if [ "$1" = "" ] || [ "$2" = "" ] || [ "$3" != "" ]; then
    echo "--> Usar: $0 '<configuração da base do sagu>' '<configuração da base de auditoria>'"  
    exit 1;
fi 

echo "--> Executando o backup das tabelas de auditoria..."
pg_dump $1 -Fc --data-only -t miolo_audit -t miolo_audit_detail -f /tmp/dados_importacao_auditoria.dump


if [ "$?" != 0 ]; then
    echo "--> Falhou. "
    exit 1;
fi

echo "--> Backup realizado com sucesso!"

echo "--> Importando os dados para a nova base..."

pg_restore -d $2 --disable-triggers /tmp/dados_importacao_auditoria.dump 

if [ "$?" != 0 ]; then
    echo "--> Falhou."
    exit 1;
fi

echo "--> Dados importados com sucesso!"

echo "--> Excluindo dados do sagu..."

psql $1 -c 'DELETE FROM miolo_audit_detail';

psql $1 -c 'DELETE FROM miolo_audit';

if [ "$?" != 0 ]; then
    echo "--> Falhou."
    exit 1;
fi

echo "--> Dados excluídos com sucesso!"

