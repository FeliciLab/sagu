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
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 18/11/2008
 *
 **/
class BusinessGnuteca3BusChangeMaterial extends GBusiness
{
    public  $MIOLO,
            $numberType,
            $number,
            $controlNumber,
            $expressionS;
    public  $businessGenericSearch2,
            $businessExemplaryControl,
            $businessmaterialControl,
            $businessMaterial,
            $busFile,
            $busSearchableField;            
    
    protected $count;
    
    private $busMaterialHistory;

    function __construct()
    {
        parent::__construct();

        $this->businessGenericSearch2   = $this->MIOLO->getBusiness( $this->module, 'BusGenericSearch2');
        $this->businessExemplaryControl = $this->MIOLO->getBusiness( $this->module, 'BusExemplaryControl');
        $this->businessMaterialControl  = $this->MIOLO->getBusiness( $this->module, 'BusMaterialControl');
        $this->businessMaterial         = $this->MIOLO->getBusiness( $this->module, 'BusMaterial');
        $this->businessMaterial         = $this->MIOLO->getBusiness( $this->module, 'BusMaterial');
        $this->busFile                  = $this->MIOLO->getBusiness( $this->module, 'BusFile');
        $this->busSearchableField       = $this->MIOLO->getBusiness( $this->module, 'BusSearchableField');
        $this->busMaterialHistory       = $this->MIOLO->getBusiness( $this->module, 'BusMaterialHistory');
    }

    public function getCount()
    {
        return $this->count;
    }
    
    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     */
    public function searchMaterial()
    {
        $this->businessGenericSearch2->clean();
        
        if(!strlen($this->expressionS) && !strlen($this->number))
        {
            $this->expressionS = '%';
        }
        
        $exp = $this->busSearchableField->parseExpression( $this->expressionS );
        $this->businessGenericSearch2->addMaterialWhereByExpression($exp);

        //Adiciona condição quando é informado o número de controle ou o número do tombo
        if(strlen($this->number))
        {
            switch ($this->numberType)
            {
                case "cn" :
                    $this->businessGenericSearch2->addControlNumber($this->number);
                break;

                case "in" :
                    $cn = $this->businessExemplaryControl->getControlNumber($this->number);
                    if($cn)
                    {
                        $this->businessGenericSearch2->addControlNumber($cn);
                    }
                break;

                case "wn" :
                    $cn = $this->businessMaterial->getControlNumberByWorkNumber($this->number);

                    if($cn)
                    {
                        $this->businessGenericSearch2->addControlNumber($cn);
                    }
                break;
            }
        }

        //forca o pagina a ser a primeira quando o usario apertou no botao btnSearch
        if ( GUtil::getAjaxEventArgs() == '' ? true : false )
       	{
       	    $_REQUEST['pn_page'] = 1;
            $_REQUEST['gridCount'] = null;
            $firstTime = true;
       	}        

        // Adicionado porque a busca estava retornando valores da última busca efetuada.	
        $_SESSION['materialSearchResult'] = NULL;
        
        $data = $this->businessGenericSearch2->getWorkSearch( null, $firstTime );

        if ( $this->businessGenericSearch2->getCount() )
        {
            $this->count = $this->businessGenericSearch2->getCount();
        }
        else
        {
            $this->count = $_REQUEST['gridCount'];
        }        

        return $data;
    }

    function deleteMaterial($controlNumber)
    {
        $this->controlNumber = $controlNumber ? $controlNumber : MIOLO::_REQUEST("controlNumber");
        
        if ( ! $this->controlNumber )
        {
            throw new Exception( _M("Impossível deletar material sem número de controle",'gnuteca3') );
        }
        
        //deleta no servidor Z3950, tem que ser feito primeiro em função de de poder montar o arquivo ISO
        if ( defined( 'Z3950_SERVER_URL' ) && Z3950_SERVER_URL )
        {
            $this->MIOLO->getClass('gnuteca3', 'GZ3950');
       
            $z3950 = new GZ3950( Z3950_SERVER_URL, Z3950_SERVER_USER, Z3950_SERVER_PASSWORD );
       
            try
            {
                $ok = $z3950->delete( $this->controlNumber );
            }
            catch ( Exception $e)
            {
               
            }
        }
        
        //registra exclusão em histórico
        $ok[] = $this->busMaterialHistory->insertMaterialHistoryForDeleteMaterial($this->controlNumber);
        
        $this->businessMaterial->controlNumber = $this->controlNumber;
        $ok[] = $this->businessMaterial->deleteMaterial();
        $ok[] = $this->businessMaterialControl->deleteMaterialControl($this->controlNumber);
        $ok[] = $this->businessExemplaryControl->deleteAllExemplariesByMaterialControl($this->controlNumber);
       
        $this->busFile->folder= 'cover'; //escolhe a pasta certa para selecionar a imagem
        $this->busFile->fileName = $this->controlNumber;
        $file = $this->busFile->searchFile(true);

        $file= $file[0];

        if ( $file->absolute )
        {
            $this->busFile->deleteFile( $file->absolute );
        }
        
        // Atualiza conteúdo da tabela de pesquisa.
        $arguments = new stdClass();
        $arguments->controlNumber = $this->controlNumber;
        $this->MIOLO->getClass('gnuteca3', 'backgroundTasks/GBackgroundTask');
        GBackgroundTask::executeBackgroundTask('updateSearchTable', $arguments);
        
        return array_search(false, $ok) == false;
    }
}
?>