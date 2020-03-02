<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 *  Teste unitário da classe GDate
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Creation date 2010/10/07
 *
 **/
include_once '../classes/GUnitTest.class.php';
$MIOLO->uses('classes/GDate.class.php', 'gnuteca3');

class TestGDate extends GUnitTest 
{
    protected function setUp() 
    {
        parent::setUp();
    }
    
    public function teste1()
    {
        //conversão db para user 
        $data = new GDate('2010-10-09');
        $dataDB = $data->getDate(GDate::MASK_DATE_USER);
        $this->assertTrue($dataDB == '09/10/2010');
        $this->exibe("Conversão de formato banco para usuários - OK");
        
        //conversão user para db
        $data = new GDate('09/10/2010');
        $dataDB = $data->getDate(GDate::MASK_DATE_DB);
        $this->assertTrue($dataDB == '2010-10-09');
        $this->exibe("Conversão de formato usuário para banco - OK");
        
        $data = '2010-02-28 14:30';
        $data = new GDate($data);

        //testa obter dia
        $this->assertTrue($data->getDay() == 28);
        $this->exibe("Testando o dia - OK");
        
        //testa obter mês
        $this->assertTrue($data->getMonth() == 02);
        $this->exibe("Testando o mês - OK");
        
        //testa obter ano
        $this->assertTrue($data->getYear() == 2010);
        $this->exibe("Testando o ano - OK");

        //testa obter hora
        $this->assertTrue($data->getHour() == 14);
        $this->exibe("Testando a hora - OK");
        
        //testa obter minuto
        $this->assertTrue($data->getMinute() == 30);
        $this->exibe("Testando o minuto - OK");
        
        //testa obter segundos
        $this->assertTrue($data->getSecond() == 0);
        $this->exibe("Testando os segundos- OK");
        
        //testa soma de dias
        $data->addDay(2);
        $this->exibe("Data: " . $data->getDate());
        $this->assertTrue($data->getDay() == 02);
        $this->exibe("Somar 2 dias - OK");
        
        //testa soma de anos
        $data->addyear(2);
        $this->exibe("Data: " . $data->getDate());
        $this->assertTrue($data->getYear() == 2012);
        $this->exibe("Somar 2 anos - OK");
        
        //testa diferença entre datas
        $this->exibe("Data 1: ". $data->getDate());
        $data2 = new GDate('10/10/2010 08:30');
        $this->exibe("Data 2: " . $data2->getDate()); 
        $diff = $data->diffDates($data2, GDate::ROUND_AUTOMATIC);
        $this->exibe('Diferença de segundos: ' . $diff->seconds); 
        $this->exibe('Diferença de minutos: ' . $diff->minutes); 
        $this->exibe('Diferença de horas: ' . $diff->hours); 
        $this->exibe('Diferença de dias: ' . $diff->days);  
        $this->exibe('Diferença de meses: ' . $diff->months);  
        $this->exibe('Diferença de anos: ' . $diff->years);  
        $this->exibe("Testando diferença de dias - OK");
        
        //comparando datas
        $this->exibe("Testando método compare()");
        $this->exibe("Data1: 09/10/2010");
        $this->exibe("Data2: 11/10/2010");
        $data1 = new GDate('09/10/2010');
        $data2 = new GDate('11/10/2010');
        $this->assertTrue($data1->compare($data2, '<') == true);
        $this->exibe("Data1 é menor que Data2 - OK");
        $this->assertTrue($data2->compare($data1, '>') == true);
        $this->exibe("Data2 é maior que Data1- OK");
        
        //obter a data atual
        $date = GDate::now() ;
        $this->exibe("Data: " . $date->getDate());
        $this->exibe("Testando obter data atual - OK");
        
    	return true;
    }
}


?> 
