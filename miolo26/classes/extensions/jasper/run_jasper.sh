#!/bin/sh

  # Attempt to use the bundled VM if none specified
  if [ "$JAVA_HOME" = "" ]; then
	JAVA_HOME=.
  fi

  JAVA_PATH=$JAVA_HOME/jre/bin

# Change to the script' working directory, should be the Protege root directory 
#cd $(dirname $0)
#cd /home/ematos/public_html/miolo/classes/extensions/jasper
cd $1

# ------------------- Where is JARs? ------------------- 

JARS=lib/classes12.jar:lib/jasperreports-3.0.0.jar:lib/commons-collections-2.1.jar:lib/commons-logging-1.1.jar:lib/itext-1.3.1.jar
MAIN_CLASS=MioloJasper.jar

# ------------------- JVM Options ------------------- 
MAXIMUM_MEMORY=-Xmx100M
OPTIONS=$MAXIMUM_MEMORY

# Run Jasper
$JAVA_PATH/java $OPTIONS -jar $MAIN_CLASS $2


echo Finished
