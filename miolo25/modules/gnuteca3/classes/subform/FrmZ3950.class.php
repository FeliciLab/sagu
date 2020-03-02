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
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 05/05/2009
 *
 **/
$MIOLO->getClass( $module, 'GMaterialItem');
$MIOLO->getClass( $module, 'GZ3950');
$MIOLO->getClass( $module, 'controls/GMaterialDetailOther');

class FrmZ3950 extends GSubForm
{
	public $manager;
	public $module;
    public $busZ3950Servers, $busSearchFormat;
    public $grid;
    public $format;

    public function __construct()
    {
    	$this->manager   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
        $this->busZ3950Servers  = $this->manager->getBusiness($this->module, 'BusZ3950Servers');
        $this->busSearchFormat  = $this->manager->getBusiness($this->module, 'BusSearchFormat');
        
        parent::__construct('Z3950');

        $this->getGrid();
    }

    /**
     * A checagem de acesso deste é via operador logado
     *
     * @return boolean
     */
    public function checkAcces()
    {
        return GOperator::isLogged();
    }

    /**
     * Este formulário não precisa de login do usuário
     *
     * @return boolean
     */
    public static function isUserLoginNeeded()
    {
        return false;
    }

    public function createFields()
    {
        GForm::setFocus('serverId',false);
        $relationOfMarcAndZ = GZ3950::getRelationOfMarc21AndZ3950(null, true );
        $busSearchFormat    = $this->manager->getBusiness($this->module, 'BusSearchFormat');
        $searchFormat       = $busSearchFormat->listSearchFormat(false, !GOperator::isLogged());

        $fields[] = new GSelection('serverId', null, _M('Servidor', $this->module), $this->busZ3950Servers->listZ3950Servers(), false, '', '', true);
        $fields[] = new GSelection('searchField', $relationOfMarcAndZ[0][1], _M('Filtro', $this->module), $relationOfMarcAndZ,false, false, false, true);
        $fields[] = new MTextField('description', null, _M('Termo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection( 'searchFormat', SIMPLE_SEARCH_SEARCH_FORMAT_ID, _M('Formato de pesquisa') , $searchFormat ,null,null,null,true);
        $fields[] = new MSeparator('<br/>');
        $fields[] = new MDiv( '',_M('Este serviço depende da conexão com o servidor selecionado.', $this->module) );
        
        $this->setFields( GUtil::alinhaForm($fields) , true );
    }

    public function searchFunctionSub($args)
    {
        $args = (Object) $_REQUEST;
        
        $server = $this->busZ3950Servers->getZ3950Servers( $args->serverId );
       
        $z3950 = new GZ3950( $server->host, $server->username, $server->password );
        
        if ( !strlen( $args->description ) )
        {
            throw new Exception( _M("O campo descrição é necessário!", $this->module) );
        }

        $z3950->addTagSearch( trim($args->searchField), trim($args->description), '@and' );
        
        //Realiza a busca de um material no Z3950, caso o intervalo $args->pn_page seja passado, ele deve ser explicitado como int pois do contrário a pesquisa tranca.
        $return = $z3950->search( $server->recordType, $server->sintax, ($args->pn_page ? intval($args->pn_page) : 1), 50 );
        $data = array();

        //dados devem ser passado de forma diferenciada para grid e para sessão
        foreach ( $return as $line => $content )
        {
            $data[$line] = array('', serialize($content), $line);
            $sessionData[$line] = serialize($content);
        }
        
        $_SESSION['z3950'] = $sessionData;
        
        $this->grid = $this->getGrid();
        $this->grid->setData( $data );
        $this->grid->setCount( $z3950->getCount() );
        
        if ( count( $data ) == 0 )
        {
            GForm::information( _M('Registros não encontrados!', 'gnuteca3' ) );
        }
        
        $this->setResponse($this->grid, 'divSearchSub');
    }

    /**
     * Retorna a grid
     *
     * @return unknown
     */
    public function getGrid()
    {
        $this->grid = $this->manager->getUI()->getGrid($this->module, "GrdZ3950Search");
        $this->grid->emptyMsg = '';
        $this->grid->setCSV(FALSE);

        return $this->grid;
    }

    /**
     * Mostra detalhes do material
     * 
     * @param stdClass $args
     */
    public function detail( $args )
    {
        $this->format   = $this->busSearchFormat->getSearchFormat( MIOLO::_REQUEST('searchFormat') ? MIOLO::_REQUEST('searchFormat') : Z3950_SEARCH_FORMAT_ID);
        $content        = unserialize( $_SESSION['z3950'][$args] );
        $count          = 0;
        
        //monta array de GMaterialItem
        foreach ($content as $tag => $registros)
        {
            $subfields = $registros->subfields;

            foreach ( $subfields as $subf => $subContent )
            {
                foreach ( $subContent as $lineSub => $cont )
                {
                    $gMaterialItem = new GMaterialItem();
                    $gMaterialItem->fieldid     = $tag;
                    $gMaterialItem->subfieldid  = $subf;
                    $gMaterialItem->setContent( $cont->content );
                    $gMaterialItem->indicator1 = $registros->ind1;
                    $gMaterialItem->indicator2 = $registros->ind2;
                    $gMaterialItem->line       = $materialLine[ $gMaterialItem->fieldid ][ $gMaterialItem->subfieldid ];

                    $data[$count] = $gMaterialItem;

                    $materialLine[ $gMaterialItem->fieldid ][ $gMaterialItem->subfieldid ] += 1;//controlador de line
                    $count++; //controlador de indice
               }
            }
        }

        $materialDetailOther = new GMaterialDetailOther($data);
        $materialDetailOther->setTagsForIgnore( Z3950_IGNORAR_TAGS );
        GForm::injectContent( $materialDetailOther , null, _M('Detalhes do material', $this->module));
    }
}
?>