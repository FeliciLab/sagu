FROM nimmis/apache-php5:latest
MAINTAINER Victor Magalhães <victor.magalhaes@esp.ce.gov.br>

#RUN apt-get update && \
#    apt-get install -y  software-properties-common && \
#    add-apt-repository ppa:webupd8team/java -y && \
#    apt-get update && \
#    echo oracle-java7-installer shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections && \
#    apt-get install -y oracle-java8-installer && \
#    apt-get clean

RUN a2enmod rewrite

ADD docker-config/ /etc/apache2/sites-available

RUN service apache2 stop

RUN service apache2 start
