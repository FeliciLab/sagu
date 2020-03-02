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
 * Classe que mostra os detalhes de um material (passando um array de GMaterialItem)
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 16/02/2010
 *
 **/

$MIOLO->getClass( $module, 'GMaterialItem' );

//controlador de eventos
$possibleEvents = array('changeSearchFormat', 'importMaterial');

$event = GUtil::getAjaxFunction();

if ( in_array( $event, $possibleEvents))
{
    $gMaterialDetailOther = new GMaterialDetailOther();

    if ( method_exists($gMaterialDetailOther, $event) )
    {
        $gMaterialDetailOther->$event();
    }
}

class GMaterialDetailOther extends MDiv
{
    public $MIOLO;
    protected $data;
    protected $tagsForIgnore;
    public $module;
    protected $googleControlNumber;
    protected $controlNumber;
    public $busPurchaseRequest;

    public function __construct($data)
    {
        $this->module = MIOLO::getCurrentModule();
        $this->MIOLO = MIOLO::getInstance();
        $this->busPurchaseRequest = $this->MIOLO->getBusiness($this->module, 'BusPurchaseRequest');
        
        parent::__construct('materialDetailOther', null, 'GMaterialDetailOther');

        //caso não tenha dados pega da sessão
        if ( !$data )
        {
            $data = $this->getData();
        }
        else
        {
            $this->setTagsForIgnore( null ); //caso passe data limpa os dados da sessão
        }

        $this->setData($data);
    }

    public function generate()
    {
        $tabControl = new GTabControl('tabControlDetail');
        $tabDetail[] = $this->getFormatedData( MIOLO::_REQUEST('searchFormat') ? MIOLO::_REQUEST('searchFormat')  : Z3950_SEARCH_FORMAT_ID );

        $tabControl->addTab( 'tabDetail', _M('Informações detalhadas', $this->module) , $tabDetail );

        if ( $this->googleControlNumber && MUtil::getBooleanValue( GB_INTEGRATION )  )
        {
            $tabGoogle[] = new GoogleBookViewer('viewerCanvas',$this->googleControlNumber, 700,350);
            $tabControl->addTab( 'tabGoogleBook', _M('Visualizar o livro', $this->module) , $tabGoogle );
        }

        $fields[]   = $tabControl;

        if ( $this->getControlNumber() ) //Se tiver número de controle
        {
            $url = 'main:search:simpleSearch&controlNumber='.$this->getControlNumber();
            $buttons[]  = new MDiv( '', new GRealLinkButton('btnSeeInGnuteca', _M('Ver no gnuteca', 'gnuteca3'), $url, GUtil::getImageTheme('gnuteca3-16x16.png')) ) ;
        }
        else //Se não tiver numero de controle 
        {
            $myData['subForm'] = 'PurchaseRequest'; //Define SubForm
            $gMaterialItems = $this->getData();
            $purchaseFields = $this->busPurchaseRequest->parseFieldsPurchaseRequest();

            foreach ( $purchaseFields as $purchaseField ) //Para cada tag do formulário de sugestão de material
            {
                $purcheaseSearchFields[] = $purchaseField->id; //Campos que serão usados para verificar qual informação será enviada ao formulário de sugestão de compra de materiais.
            }

            foreach ( $gMaterialItems as $gMaterialItem ) //Para cada tag material
            {
                if ( in_array("{$gMaterialItem->fieldid}_{$gMaterialItem->subfieldid}", $purcheaseSearchFields) ) //Verifica se o campo é para ser passado para o formulário de sugestão de compra.
                {
                    $myData["dinamic{$gMaterialItem->fieldid}_{$gMaterialItem->subfieldid}"] = base64_encode($gMaterialItem->content); //Adiciona tag e com conteúdo codificado para base 64 por causa de problemas com o miolo no aninhamento de " e ' que estava conflitando..
                }
            }

            if ( BusinessGnuteca3BusAuthenticate::getUserCode() ) //Se tiver usuario logado
            {
                $ajax = GUtil::getAjax('subForm',$myData) . GUtil::getCloseAction();
                $buttons[]  = new MDiv('',new MButton('bntPurchaseRequest', _M('Sugerir material', 'gnuteca3'), $ajax , GUtil::getImageTheme('button_insert.png')) ); //Mostra botão de sugestão de material
            }

        }

        if ( ! $this->getControlNumber() && GOperator::isLogged() && GPerms::checkAccess('gtcPreCatalogue', null, false) ) //operador precisa estar logado e deve ter permissão de pré-catalogação
        {
            $buttons[]  = new MDiv('',new MButton('bntImportMaterial', _M('Importar Material', 'gnuteca3'), GUtil::getAjax('importMaterial'), GUtil::getImageTheme('catalogue-16x16.png')) );
        }

        $buttons[]  = new MDiv( '',GForm::getCloseButton() );
        $fields[]   = new GContainer('materialDetailOtherButtons', $buttons );

        $this->setInner( $fields );

        return parent::generate();
    }

    public function setTagsForIgnore($tagsForIgnore)
    {
        $this->tagsForIgnore = $tagsForIgnore;
        $_SESSION['MaterialDetailOther']['TagsForIgnore'] = $tagsForIgnore;
    }


    public function getTagsForIgnore()
    {
        return $this->tagsForIgnore ? $this->tagsForIgnore : $_SESSION['MaterialDetailOther']['TagsForIgnore'];
    }


    public function setGoogleControlNumber( $googleControlNumber )
    {
        $this->googleControlNumber = $googleControlNumber;
        $_SESSION['MaterialDetailOther']['googleControlNumber'] = $googleControlNumber;
    }


    public function getGoogleControlNumber()
    {
        return $this->googleControlNumber ? $this->googleControlNumber : $_SESSION['MaterialDetailOther']['googleControlNumber'];
    }


    public function setControlNumber( $controlNumber )
    {
        $this->controlNumber = $ControlNumber;
        $_SESSION['MaterialDetailOther']['controlNumber'] = $controlNumber;
    }
    

    public function getControlNumber()
    {
        return $this->controlNumber ? $this->controlNumber : $_SESSION['MaterialDetailOther']['controlNumber'];
    }

    /**
     * Trata os campos Marc
     *
     * 900=901.* = pega todas as tags da etiqueta 901 (901.a,901.b)
     * 900=90* = pega as tags de 901.* a 909.*
     * 900=9** = pega todas as tags da etiqueta 900
     *
     * @param $ignore as tags para ignorar
     */
    private function parseIgnoreTags($ignore=null)
    {
        $busTag = $this->manager->getBusiness('gnuteca3', 'BusTag');

        if ( !$ignore )
        {
        	return null;
        }

        $completo   = array();
        $lines      = explode(",", $ignore);

        foreach ($lines as $i=>$val)
        {
        	$broken = explode('.', $val);
        	$etiqueta = $broken[0];
        	$sub = $broken[1];

            if ($sub)
            {
            	if ( $sub == '*' )
            	{
            		//procura as tags
            		$tags = $busTag->getTag($etiqueta, '#');
            		$tags = $tags->tag;
                    
                    if ( is_array($tags) )
                    {
	            		foreach ($tags as $k=>$tag )
	            		{
	            			$completo[] = $tag->fieldId . '.' . $tag->subfieldId;
	            		}
                    }
            	}
            	else
            	{
            		$completo[] = $val;
            	}

            }
            else
            {
            	$etiqueta = str_replace('*', '', $etiqueta);
            	$fieldsId = $busTag->searchFieldId($etiqueta);

                if ( is_array($fieldsId) )
                {
                	foreach ($fieldsId as $j=>$fieldId)
                	{
	                	$tags = $busTag->getTag($fieldId->fieldid, '#');
	                    $tags = $tags->tag;
	                    if ( is_array($tags) )
	                    {
	                        foreach ($tags as $k=>$tag )
	                        {
	                            $completo[] = $tag->fieldId . '.' . $tag->subfieldId;
	                        }
	                    }
                	}
                }
            }
        }

        return $completo;
    }

    /**
     * Retorna a tableRaw formatada, passando o formato de pesquisa
     *
     * @param <integer> $searchFormat
     * @return MTableRaw
     */
    public function getFormatedData($searchFormat)
    {
        if ( !$searchFormat )
        {
            return false;
        }

        $content    = GMaterialItem::getFormatedData($this->data, $searchFormat);
        $content    = preg_split('/<([ \/]{0,}?[bB][rR][ \/]{0,}?)>/U', $content);

        // converte para o formato da MTableRaw
        if ( is_array( $content ) )
        {
            $detailData = null;

            foreach ($content as $line => $info)
            {
                //FIXME o replace é uma pog porque da problema no miolo (mtableraw) não deveria ser necessário
                $detailData[$line] = array( str_replace('"',"'",strlen($info) ? $info : '--'));
            }

            $tableDetail = new MTableRaw( null, $detailData , null, null);
            $tableDetail->setAlternate(true);
        }

        return $tableDetail;
    }

    public function setData($data)
    {
        if ( $this->checkData($data) )
        {
            $this->data = $data;

            foreach ( $data as $line => $info)
            {
                $data[$line] = $info->toStdClass();
            }

            $_SESSION['MaterialDetailOther']['data'] = $data;
        }
    }

    public function getData()
    {
        if ( !$this->data)
        {
            $data = $_SESSION['MaterialDetailOther']['data'];

            if ( is_array($data))
            {
                //transforma em GMaterialItem os stdClass que vem da sessão
                foreach ( $data as $line => $info)
                {
                    $data[$line] = GMaterialItem::fromStdClass( $info );
                }

                $this->data = $data;
            }
        }

        return $this->data;
    }


    /**
     *  Verifica se todos os objetos do data são GMaterialItem
     *
     * @param <array> $data array of GMaterialItem
     * @return <boolean> true se todos os objetos do data são GMaterialItem
     */
    protected function checkData($data)
    {
        if ( $data && is_array($data) )
        {
            foreach ( $data as $line => $info)
            {
                if ( !$info instanceof GMaterialItem )
                {
                    return false;
                }
            }
        }

        return true;
    }


    /**
     * Faz a importação do último material aberto nos detalhes.
     *
     */
    public function importMaterial()
    {
        $controlNumber = $this->getControlNumber();

        if ( $controlNumber )
        {
            GForm::information( _M('Não é necessário importar este livro, ele já esta catalogado sobre número de controle @1.', $this->module, $controlNumber ) );
            return false;
        }

        $busCatalogue   = $this->manager->getBusiness($this->module, 'BusPreCatalogue');
        $data           = $this->getData();
        $tagsForIgnore  = $this->parseIgnoreTags( $this->getTagsForIgnore() );

        if ( is_array( $data ))
        {
            $controlNumber = $busCatalogue->getNextControlNumber();

            foreach ( $data as $l => $materialItem )
            {
                $tag = $materialItem->fieldid.'.'. $materialItem->subfieldid;

                if ( $materialItem->check() && !in_array( $tag , $tagsForIgnore ) )
                {
                    $materialItem->controlNumber = $controlNumber;
                    $busCatalogue->line = 0;
                    $busCatalogue->setData($materialItem); //define os dados
                    $ok = $busCatalogue->insertMaterial(); //insere na pré-catalogação
                }
            }
        }

        if ( $ok )
        {
            GForm::information( _M("Registro inserido com sucesso na pré-catalogação.<br> Número de controle da pré-catalogação: $controlNumber", $this->module) );
        }
        else
        {
            GForm::error( _M("Não foi possível importar os dados para a pré-catalogação.", $this->module) );
        }
    }

}

?>
