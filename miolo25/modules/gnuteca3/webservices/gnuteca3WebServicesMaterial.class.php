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
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 24/02/2010
 *
 **/

include("GnutecaWebServices.class.php");

class gnuteca3WebServicesMaterial extends GWebServices
{
    /**
     * Attributes
     */
    public $busSearchFormat;



    /**
     * Contructor method
     */
    public function __construct()
    {
        parent::__construct();

        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
    }
    
    /**
     * Obtem uma string formatada com a informacao do material 
     *
     * @param (int) $clientId
     * @param (String) $clientPassword base64 encoded
     * @param (int) $controlNumber
     * @param (int) $searchFormatId
     * @param (String) $returnType
     * @param (String) $returnAsObject Se for para retornar em array com o conteudo do formato de pesquisa e o ano (260.c) em outra posicao do mesmo array
     * 
     * @return (mixed) $content
     */
    public function getMaterialInformation($clientId, $clientPassword, $controlNumber, $searchFormatId = null, $returnType = null, $returnAsObject = false)
    {
        // CHECA ACESSO AO METHODO
        parent::__setClient($clientId, $clientPassword);
        if(!parent::__checkMethod('getMaterialInformation', false))
        {
            return parent::__getErrorStr();
        }
        
        //Busca o formato padrao de retorno, caso nao seja passado
        if (!$searchFormatId && (WEB_SERVICE_MATERIAL_DEFAULT_SEARCH_FORMAT_ID != 'WEB_SERVICE_MATERIAL_DEFAULT_SEARCH_FORMAT_ID'))
        {
            $searchFormatId = (int) WEB_SERVICE_MATERIAL_DEFAULT_SEARCH_FORMAT_ID;
        }

        $content = $this->busSearchFormat->getFormatedString($controlNumber, $searchFormatId, 'search', $returnAsObject);

        return $this->returnType($content, $returnType);
    }
    
    /**
     * Obtém informações da obra a partir do(s) número(s) de controle.
     * 
     * @param string $clientId
     * @param string $clientPassword com base64_encode
     * @param array $controlNumber array simples
     * @return array de array com stdClass dentro
     */
    public function getExemplarFromWork( $clientId, $clientPassword, $controlNumber)
    {
        parent::__setClient($clientId, $clientPassword);
        
        if(!parent::__checkMethod('getMaterialInformation', false))
        {
            return parent::__getErrorStr();
        }

         $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        

        if ( !is_array($controlNumber ) )
        {
            $controlNumber = array( $controlNumber );
        }
        
        $search = array();
        foreach ( $controlNumber as $line => $number )
        {
            $search[] = intval($number);
        }

        return $busMaterial->getMaterialFromControlNumbers($search);
    }
    
    /**
     * Obtém numeros de controle semelhantes a classificacao do numero de controle
     * passado por parametro.
     * 
     * @param string $clientId
     * @param string $clientPassword com base64_encode
     * @param array $controlNumber array simples
     * @return array de array com stdClass dentro
     */
    public function getSimilarControlnumberFromWork( $clientId, $clientPassword, $controlNumber)
    {
        parent::__setClient($clientId, $clientPassword);
        
        if(!parent::__checkMethod('getMaterialInformation', false))
        {
            return parent::__getErrorStr();
        }

         $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        

        if ( !is_array($controlNumber ) )
        {
            $controlNumber = array( $controlNumber );
        }
        
        $search = array();
        foreach ( $controlNumber as $line => $number )
        {
            $search[] = intval($number);
        }

        return $busMaterial->getSimilarControlNumber($search);
    }    
}
?>
