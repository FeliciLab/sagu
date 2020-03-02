<pre>
<?php
ini_set("soap.wsdl_cache_enabled", 0);

	/*   SOAP Client com Gnuteca em PHP  -  por: Tcharles Silva
	  *-------------------------------------------
	  * Três jeitos de começar um WebServices
	  * 1- Instanciando com o modo não WSDL [OK]
	  * 2- Iniciando com o modo WSDL + Opções  [OK] 
	  * 3- Iniciando Somente com o modo WSDL
	  * Atribua o valor à variavel $sc correspondente à operação que deseja:
	  * No campo $wsdl, preencha se irá utilizar WSDL
	  *
	 */
	$sc = 1	; // 1, 2 ou 3
	
	//$wsdl = "https://www.univates.br/gnutecadevel/modules/gnuteca3/files/wsdl/gnuteca3WebServicesAutomacao.wsdl";
	//$wsdl = "http://sagu.tcharles/miolo25/html/file.php?folder=wsdl&file=gnuteca3WebServicesAutomacao.wsdl";
	//$wsdl = "http://sistemacomercial.solis.com.br/gnuteca_trunk/miolo25/webservices/wsdl/gnuteca3WebServicesAutomacao.wsdl";
	//$wsdl =  "http://gnuteca3automacao.solis.com.br/miolo25/html/file.php?folder=wsdl&file=gnuteca3WebServicesAutomacao.wsdl";
	//$wsdl = "http://gnuteca3automacao.solis.com.br/miolo25/modules/gnuteca3/webservices/wsdl/gnuteca3WebServicesAutomacao.wsdl";
	
	
	//Mostrar todas as funções do WSDL?
	$mostrar = false;
	
	//Mostrar informações do cliente?
	$cl = false;
	
//	https://www.univates.br/gnutecadevel/webservices.php?module=gnuteca3&action=main&class=

	/*
	 * Nos campos abaixo:
	 * $url = Corresponde ao endereço onde esta o seu index. Exemplo: http://localhost/saguteste/miolo25/html
	 * $class = Corresponde a classe que será o server
	 * Formando a URL: http://localhost/saguteste/miolo25/html/webservices.php?module=gnuteca3&action=main&class=gnuteca3WebServicesAutomacao
	*/
	
	// Lista de URLS
	
	    //$url = "http://gnuteca3automacao.solis.com.br/miolo25/html";
	    //$url = "http://localhost/sagu_trunk/miolo25/html/";
	    //$url = "http://www.sistemacomercial.solis.com.br/gnuteca_trunk/miolo25/html/";
	    $url = "http://www.univates.br/gnutecaprojeto/";
        
        
        
        // Classe utilizada
        
	    $class = "gnuteca3WebServicesAutomacao";
	
	
/*
    [!] IMPORTANTE LEIA COM ATENÇÃO:
    
    LINHA PARA SER ADICIONADO AO WSDL LOCAL
		
	    <wsdlsoap:address location="http://localhost/saguteste/miolo25/html/webservices.php?module=gnuteca3&amp;action=main&amp;class=gnuteca3WebServicesAutomacao"/>
*/	
	
	$clientOptions["location"] = "$url/webservices.php?module=gnuteca3&action=main&class=$class";
	$clientOptions["uri"] = "$url";
	$clientOptions["encoding"] = "UTF-8";


	try
	{
		echo "<center><h2>SoapClient Gnuteca - v 1.0</h2>";
		if($sc == 1)
		{
			echo "<h3>Modo no-WSDL</h3>";
			$client = new SoapClient(NULL, $clientOptions);
		}
		else if($sc == 2)
		{
			echo "<h3>Modo WSDL + Options</h3>";
			$client = new SoapClient($wsdl, $clientOptions);
		}
		else if($sc == 3)
		{
			echo "<h3>Modo WSDL</h3>";
			$client = new SoapClient($wsdl);
		}
		
		if($mostrar)
		{
			//Mostra todas as funçoes do WSDL
			$functions = $client->__getFunctions();
			echo "<center><h1>FUNCTIONS DO WEBSERVICE C/ WSDL</h1></center>";
			var_dump($functions);
		}
		
		if($cl)
		{
			//Exibe informações da variável $cliente
			echo "<center><br><br><h1>DETALHES DO CLIENTE DO WEBSERVICE</h1>";
			var_dump($client);
		}
		
		//---------------------------------------------------------------------------------------
		echo "<br>----------------------------------------";
		
		
		####################################################################
		##                   INICIO DOS WEBSERVICES
		####################################################################
		##
		##      ABAIXO, ESTÃO TODAS AS CHAMADAS AOS WEBSERVICES
		##      CASO NÃO QUEIRA UTILIZAR, APENAS COMENTE A LINHA
		## 
		## 		PS: Mantenha esse script organizado ;). 
		##
		## 						     Tcharles Silva
		####################################################################
		
		
/*
	[#] Testes com WebService de Login [#]
			    
	    Informações:
			
		Aqui informações
*/
		//Parametros
		
		
		
		//Chamada ao webServices
			
		    //$result[] = $client->login('10', '123');
		    //$result[] = $client->login('10', '10');
		    //$result[] = $client->login('1', '123');
		    //$result[] = $client->login('10', '123');
		    //$result[] = $client->login('45', '123');
		
			
			
/*
	[#] Testes com WebService de PatronInfo [#]
			    
	    Informações:
			
		PatronID como personID: , 
		PatronID como cartão: 
			
		PatronPwd: Solis2014, 123
*/
		//Parametros
		
		    $language = "000";
		    $dateTime = "13/08/2014 20:42:27";
		    $summary = "";
		    $instID = "1";
		    $patronID = "000000009D581951";
		    $patronPwd = "";
		    $termID = "sr300-1";
		    $termPwd = "";
		    $startItem = "0";
		    $endItem = "0";
		
		
		//Chamada ao webServices
		
		    $result[] = $client->patronInfo($language, $dateTime, $summary, $instID, $patronID, $patronPwd, $termID, $termPwd, $startItem, $endItem);
			
			
			
			
			
/*
	[#] Testes com WebService de Status [#]
		    
	    Informações:
				
				
			    
*/
		//Parametros
		
		
		
		//Chamada ao webServices
		
		    //$result[] = $client->status(10, 0, 40, 2.00);
		    //$result[] = $client->status('10', '0', '40', '2.00');
		    //$result[] = $client->status('45', '0');
		    //$result[] = $client->status('72', '1');
		    //$result[] = $client->status('175', '1');
		    //$result[] = $client->status('10', '1');
		    //$result[] = $client->status('10', '2');
		    //$result[] = $client->status('11', '2');
			
			
			
			
			
/*
	[#] Testes com WebService de Renew [#]
			    
	    Informações:
				
				
			    
*/
		//Parametros
		
		
		$thdAllowed = 'false';
		$noBlock= 'false';
		$dateTime= '29/08/2014 14:22:00';
		$nbDueDate= '29/08/2014 14:22:00';
		$instID= '1'; 
		$patronID= '345814';
		$patronPwd= '';
		$itemID= '00002525';
		$titleID= ' Ajax com Java';
		$termID= '10';
		$termPwd= '123';
		$itemProp = '';
		
		
		//Chamada ao webServices
		
		    //$result[] = $client->renew($thdAllowed, $noBlock, $dateTime, $nbDueDate, $instID, $patronID, $patronPwd, $itemID, $titleID, $termID, $termPwd, $itemProp);
		    //$result[] = $client->renew(false, false, "02/12/2013 16:00:00", "02/12/2013 16:00:00", '1', "100000006", "1234", "00118475", "", "45", "");
		    //$result[] = $client->renew(false, false, "02/12/2013 16:00:00", "02/12/2013 16:00:00", '1', "100000006", "1234", "00118475", "", "5", "");
		    //$result[] = $client->renew(false, false, "02/12/2013 16:00:00", "02/12/2013 16:00:00", '1', "tcharles", "123", "00118478", "", "45", "");
			
			
			
			
			
			
			
/*
	[#] Testes com WebService de Checkout [#]
			    
	    Informações:
				
				
			    
*/
		//Parâmetros::
		
		    $renewalPolicy = "1";
		    $noBlock = "";
		    $dateTime = "07/07/2014 20:59:57";
		    $nbDueDate = "null/null/null null:null:null";
		    $instID = "1";
		    $patronID = "";
		    $itemID = "00002525";
		    $termID = "10";
		    $termPwd = "123";
		    $itemProp = "";
		    $patronPwd = "";
		    $cancel = "";

		
		//Chamadas dos webServices
		//*
		    //$result[] = $client->checkout($renewalPolicy, $noBlock, $dateTime, $nbDueDate, $instID, $patronID, $itemID, $termID, $termPwd, $itemProp, $patronPwd, $cancel);
		 //*/
			
			
			
			
/*
	[#] Testes com WebService de Checkin [#]
			    
	    Informações:
				
				
			    
*/
		//Parametros
		
		$noBlock = "false";
		$dateTime = "26/07/2014 11:30:21";
		$returnDate = "26/07/2014 11:30:21";
		$currentLocation = "";
		$instID = "1";
		$itemID = "00002525";
		$termID = "10";
		$termPwd = "123";
		$itemProp = "";
		$cancel = "";
		
		//Chamada ao webServices
		//*
		    //$result[] = $client->checkin($noBlock, $dateTime, $returnDate, $currentLocation, $instID, $itemID, $termID, $termPwd, $itemProp, $cancel);
		//*/
			
			
			
/*
	[#] Testes com WebService de endPatronSession [#]
			    
	    Informações:
		
*/
		//Parametros
		
		
		
		//Chamada ao webServices
		
		    //$result[] = $client->endPatronSession('05/12/2013 11:30:00', '1', "100000000", "45", "e");
		    //$result[] = $client->endPatronSession('05/12/2013 11:30:00', '1', "c", "5", "e");
			
#FIM DO EndPatronSession
			
			
			
			
			

			
/*
	[#] Testes com WebService de renewAll [#]
			    
	    Informações:
				
				
			    
*/
		//Parametros
		
		
		
		//Chamada ao webServices
		
		    //$result[] = $client->renewAll('06-12-2013 13:26:30', '1', '100000006', '123', '45', '123');
		    //$result[] = $client->renewAll('06-12-2013 13:26:30', '1', 'tcharles', '123', '45', '123');

#FIM DO RENEWALL
		
		
		
		
		
		
		
		
/*
	[#] Testes com WebService de itemInformation [#]
			    
	    Informações:
		
*/
		//Parametros
		
		    $dateTime = "21/05/2014 12:39:16";
		    $instID = "1";
		    $itemID = "00002525";
		    $termID = "10";
		
		//Chamada ao webService
		    
		    //$result[] = $client->itemInformation($dateTime, $instID, $itemID, $termID, "");
		    
#FIM DO ITEMINFORMATION




			
/*
	[#] Testes com WebService de patronStatus [#]
			    
	    Informações:
		   
*/
		//Parametros
		
		
		
		//Chamada ao webService
		
		    //$result[] = $client->patronStatus("10", "10", "02/12/2013 16:00:00", "1", "10000006", "123", "123");
			
			
			
			
			
			
	foreach($result as $r)
	{
	    $var22 = explode("||", $r);
	    var_dump($var22);
			    
	    echo $r . "<br><br>";
	}	    
    }
    catch ( Exception $e )
    {	
	//Caso não consiga, mostra a mensagem de falha
	echo '<br><br><br>';
	echo "Erro ao acessar webservice: <h2>" . $e->getMessage() . "</h2>";
    }
	
    echo "<h1><br><br></h1>----------------------------------------<br>";

    if(is_null($result))
    {
	echo "<h3>RESULT EM BRANCO!</h3>\n";
    }
    else{
	echo "RESULT:: OK! ";
    }
    if(is_null($client))
    {
	echo "Cliente é nulo!";
    }
    else{
	echo "\nCLIENT:: OK! <br>";
    }
    
    echo "<br>Fim do script\n";
?>
</pre>
[<a href=".">Voltar</a>]
