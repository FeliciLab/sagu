#!/bin/bash

PIPE=/tmp/flog

# funcao para excluir o pipe criado no temp
control_c()
{
  rm -f $PIPE
  exit $?
}

# intercepa o control-c do teclado
trap control_c SIGINT

while true
do
    if [ ! -p "$PIPE" ]
    then
        mkfifo "$PIPE" -m 0777
        if [ "$?" -ne 0 ]
        then
            echo "Could not create named pipe."
            exit 1
        fi
    fi

    cat "$PIPE" | sed "1i########## $(date) ##########"
done

