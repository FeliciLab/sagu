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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @since
 * Creation date 09/02/2012
 *
 **/
include_once '../classes/GUnitTest.class.php';

class TestWebservicematerial extends GUnitTest 
{
    protected function setUp() 
    {
        parent::setUp();
    }
    
    public function teste1()
    {
        $this->assertTrue( true);
        //$this->exibe("Conversão de formato banco para usuários - OK");
        
        $class = "gnuteca3WebServicesTesting";

        $MIOLO = MIOLO::getInstance();
        
        // Obtém URL do MIOLO.
        $url = $MIOLO->getConf('home.url');

        // Parâmetros do SOAP.
        $clientOptions["location"] = "$url/webservices.php?module=gnuteca3&action=main&class=gnuteca3WebServicesMaterial";
        $clientOptions["uri"] = "$url";
        $clientOptions["encoding"] = "UTF-8";
        
        try
        {
            $client = new SoapClient(NULL, $clientOptions);
            $result = $client->getSimilarControlnumberFromWork( '1', base64_encode('123456'), array(2000) );
        }
        catch ( Exception $e)
        {
            die( 'Erro='.$e->getMessage() ."\n" );
            return false;
        }
        
        $this->exibe($result);
        
        return true;
    }
}
?> 
