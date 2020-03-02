#!/bin/bash

file=sagu.sql.tar.gz
if [ -z "$1" ]
then
    echo "No filename specified. Defaulting to $file."
else
    file=$1
fi

if [ ! -f "$file" ]
then
    echo "File $file not found."
    exit 1
fi

db=$(basename $file .sql.gz)

echo "Creating database $db"
createdb -hlocalhost -Upostgres -Eutf8 $db

if [ $? -ne 0 ]
then
    echo "Error creating database"
else
    echo "Creating database structure"
    cat $(dirname $0)/$file | gunzip | psql -Upostgres -hlocalhost $db
fi

echo "All done."

