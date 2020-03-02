#!/bin/bash

if [ "$1" == "" ]
then
    echo "Usage: $0 <revision>"
    exit;
fi

REV=$1
TRUNK=https://svn.solis.coop.br/miolo/trunk/

echo "Updating local repository"
svn up

echo "Doing merge from trunk's revision $REV"
svn merge -c $REV $TRUNK . && \
svn ci -m "Applied trunk's revision $REV to 2.5 branch. $2"
echo "Done"

