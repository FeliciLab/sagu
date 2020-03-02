#!/bin/bash

listdescendants ()
{
    local children=$(ps -o pid= --ppid "${1}")

    for pid in ${children}
    do
        listdescendants "${pid}"
    done
    echo "${children}"
}

# Se foi informado o parâmetro 'c', cancela o processo atual que está rodando
if [ "${1}" = "c" ]
then
    kill $(listdescendants ${2})

    exit 0
fi

CAMINHO_INSTALADOR=/var/www/instalador

# Se possui o caminho do instalador
if [ ! -d ${CAMINHO_INSTALADOR} ]
then
    echo "[erro] O caminho do instalador informado não existe!"

    exit 1
fi

# Se o caminho definido no instalador é o mesmo da instalação que está chamando
# esse script
CAMINHO_INSTALACAO=$(grep "dir.dest=.*" ${CAMINHO_INSTALADOR}/properties/build-dir.properties | grep -o "/.*")

CAMINHO_ATUAL=$(cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd)

# Faz o grep que verifica se o caminho da instalação definido no instalador é o
# mesmo que o script que está rodando
GREP_CAMINHO_INSTALACAO_EM_CORRENTE=$(echo ${CAMINHO_ATUAL}"/" | grep ${CAMINHO_INSTALACAO}"/")

if [ -z "${GREP_CAMINHO_INSTALACAO_EM_CORRENTE}" ]
then
    echo "[erro] O caminho da instalação definido no instalador não corresponde ao instalação que está executando-o!"

    exit 1
fi

# Realiza o grep a procura do tipo do deploy
GREP_MODO_DEPLOY=$(grep "^sagu.deploy.version=.*" ${CAMINHO_INSTALADOR}/properties/build-deploy.properties | grep -o "[^=]*$")

if [ "${GREP_MODO_DEPLOY}" != "auto" ]
then
    echo "[erro] O atualizador está configurado com uma versão específica, o que impede a atualização por esta interface. O parâmetro sagu.deploy.version do atualizador deve estar como: auto"

    exit 1
fi

# Se foi informado o parâmetro 'v', realiza a verificação e não tenta atualizar
if [ "${1}" = "v" ]
then
    exit 0
fi

# Acessa o diretório do instalador
cd ${CAMINHO_INSTALADOR}

# Ínicio do processo de atualização
sudo ./deploy.sh

# Futuramente rodar o syncdb
