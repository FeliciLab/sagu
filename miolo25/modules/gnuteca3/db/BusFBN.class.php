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
 * gtcTask business
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 *
 * @since
 * Class created on 11/01/2011
 *
 **/
$MIOLO = MIOLO::getInstance();
$MIOLO->getClass('gnuteca3', 'gMarc21Record');
$MIOLO->getClass('gnuteca3', 'GMarc21');
$MIOLO->getClass('gnuteca3', 'GMaterialItem');

class BusinessGnuteca3BusFBN extends GBusiness
{
    const USE_TODOS     = 'kw_livre';
    const USE_AUTOR     = 'kw_autores';
    const USE_TITULO    = 'kw_titulos';
    const USE_ASSUNTO   = 'kw_assuntos';

    public $arg; //=iracema
    public $use = 'kw_livre';
    public $columnsTitle;
    public $searchFormat;

    public function parseUrl( $url )
    {
        $explode = explode( '=' , $url );
        return $explode[ count($explode)-1 ];
    }

    public function searchFBN( $returnSearchFormat = true )
    {
        if ( !$this->arg)
        {
            return false;
        }

        /*&rn=paginação*/
        $arg = htmlspecialchars(urlencode(utf8_decode($this->arg)));
        $url = "http://bndigital.bn.br/scripts/odwp032k.dll?t=xs&pr=fbn_dig_pr&db=fbn_dig&use={$this->use}&disp=list&sort=off&ss=new&arg={$arg}";
        $content = file_get_contents($url);

        if ( !$content )
        {
            throw new Exception ( _M("Impossível obter conteúdo da biblioteca nacional. Tente novamente mais tarde!",'gnuteca3') );
        }

        if ( $content )
        {
            try
            {
                $doc = new DOMDocument();
                $doc->strictErrorChecking = FALSE;
                $ok = $doc->loadHTML( $content );
                $xml = simplexml_import_dom($doc);
            }
            catch ( Exception $e )
            {
                throw new Exception( _M('Impossível importar conteúdo da biblioteca nacional. Conteúdo corrompido!','gnuteca3') );
            }

            //encontra o link com os dados marc
            $marcLink = $xml->body->center[1]->table->tr->form->td->a[6];
            
            if ( $marcLink )
            {
                $marcLink = utf8_decode('http://bndigital.bn.br'.$marcLink->attributes()->href);
                $marcPage = file_get_contents($marcLink); //obtem a página
                $_SESSION['FBNMARCContent'] = $marcPage;
            }

            $searchResult = $xml->body->center[0]->table->tr->form->td[1]->font;
            
            /*O código abaixo foi feito desta maneira pois o resultado da pesquisa biblioteca nacional difere quando a pesquisa retorna somente 1 registro. Assim,
            quando a busca retornar 1 registro, obtémos o marc do resultado, e tratamos em um array de resultado, ficando transparente para o resto do processo.
             Formato do array:    [0] => Doc
                                  [1] => Suporte
                                  [2] => Autor
                                  [3] => Título
                                  [4] => Data
                                  [5] => Link
             */
            
            //parse de resultado com 1 registro
            if ( strpos($searchResult, '1 de 1') )
            {
                $resultData = $this->searchFBNMarc(); //obtém objeto marc da pesquisa
                
                $result = array();
                if ( is_array($resultData) )
                {
                    foreach( $resultData[0] as $key => $data )
                    {
                        //obtém o autor
                        if ( $data->fieldid == '100' && $data->subfieldid == 'a') //obtém autor
                        {
                            $result[2][2] = $data->content;
                        }
                        elseif ( $data->fieldid == '100' && $data->subfieldid == 'b') //obtém complemento do autor
                        {
                            $result[2][2] .= $data->content;
                        }
                        elseif ( $data->fieldid == '245' && $data->subfieldid == 'a' ) //obtém o título
                        {
                            $result[2][3] = $data->content;
                        }
                        elseif ( $data->fieldid == '245' && $data->subfieldid == 'b' ) //obtém o subtítulo
                        {
                            $result[2][3] .= $data->content;
                        }
                        elseif ( $data->fieldid == '100' && $data->subfieldid == 'd') //data de publicação
                        {
                            $result[2][4] = $data->content;
                        }
                        elseif ( $data->fieldid == '856' && $data->subfieldid == 'u') //link
                        {
                            $result[2][5][] = $data->content;
                        }
                    }
                }
            }
            else //parse de resultado com n registros
            {
                $result = $this->treatMoreResults($xml->body->center[2]->table->tr);
            }

        }

        //$result fica sendo um array linear
        $this->columnsTitle = $result[1];

        unset( $result[1] );

        if ( $this->searchFormat && $returnSearchFormat)
        {
            $MIOLO              = MIOLO::getInstance();
            $busSearchFormat    = $MIOLO->getBusiness( 'gnuteca3', 'BusSearchFormat');
            $format             = $busSearchFormat->getSearchFormat( $this->searchFormat );
            $gFunction          = new GFunction();

            $gFunction->setVariable('$LN', "\n");

            foreach( $result as $line => $info )
            {
                $gFunction->setVariable( '$'.MARC_AUTHOR_TAG , $info[2] );
                $gFunction->setVariable( '$'.MARC_TITLE_TAG , $info[3] );
                $gFunction->setVariable( '$'.MARC_PUBLICATION_DATE_TAG , $info[4] );

                $content    = $gFunction->interpret( $format->searchPresentationFormat[0]->searchFormat, true  );
                $content    = str_replace("\n", '<br>', $content);

                $return[$line][0] = $content;
                $return[$line][1] = $info[5]; //link
                $return[$line][2] = $line-2; //pra compensar a retirada da coluna dos títulos
            }

            //isso foi feito para ajustar o array ( começava do 2)
            return array_values( $return );
        }

        return array_values( $result );
    }

    /**
     * Trata os registros de uma pesquisa com mais de 1 resultado
     * @param xml $content com os resultado da busca biblioteca nacional
     * @return array de dados tratados em suas devidas posições 
     */
    private function treatMoreResults($content)
    {
        $cont = 0;
        foreach ( $content as $line => $tr)
        {
            $cont++;

            foreach ( $tr as $l =>$td)
            {
                if ( $td->a[1]) //link
                {
                    $atr1 = $td->a[0]->attributes();
                    $atr2 = $td->a[1]->attributes();
                    $atr3 = null;

                    if ( $td->a[2] )
                    {
                        $atr3 = $td->a[2]->attributes();
                    }

                    $lData = array();

                    $lData[] = $this->parseUrl( ''.$atr1->href );

                    if ( $atr2->href )
                    {
                        $lData[] = $this->parseUrl( ''.$atr2->href );
                    }

                    if ( $atr3->href )
                    {
                        $lData[] = $this->parseUrl( ''.$atr3->href );
                    }
                }
                //caso seja os cabeçalhos
                else if ( $td->font->b )
                {
                    $lData = ''.$td->font->b;
                }
                else if ( $td->b ) //contador
                {
                    $lData = ''.$td->b ;
                }
                else if ( $td->a ) //link
                {
                    $atr = $td->a->attributes()  ;
                    $lData = $this->parseUrl( ''.$atr->href );
                }
                else if ( $td[0] )
                {
                    $lData = ''.$td[0];
                }

                $result[$cont][] = $lData;
            }
        }
        
        return $result;
    }
    
    
    public function getFilterList()
    {
        return array(
            self::USE_TODOS     => _M('Todos os campos',  $this->module) ,
            self::USE_TITULO    => _M('Título', $this->module) ,
            self::USE_AUTOR     => _M('Autor',  $this->module) ,
            self::USE_ASSUNTO   => _M('Assunto',  $this->module)
        );
    }

    public function searchFBNMarc()
    {
        //$url = "http://bndigital.bn.br/scripts/odwp032k.dll?t=nav&pr=fbn_dig_pr&db=fbn_dig&use=cs0&rn=1&disp=tags&sort=off&ss=22459684&arg=";
        //$content = file_get_contents($url);

        $content = $_SESSION['FBNMARCContent'];
        
        //$content = file_get_contents( BusinessGnuteca3Busfile::getAbsoluteFilePath('tmp', 'fbnMarc', 'html') );

        if ( $content )
        {
            $doc = new DOMDocument();
            $doc->strictErrorChecking = FALSE;
            $ok = $doc->loadHTML( $content );
            $xml = simplexml_import_dom($doc);
            
            foreach ( $xml->body->center[2]->table->tr as $line => $register)
            {
                $str = $register->td[1]->asXml();
                $str = str_replace('<td>',"#-!@#", $str); //troca td por espaços
                $str = str_replace('</tr>',"\n", $str); //troca fim de tr por nova linha
                $str = str_replace("\n#-!@#","#-!@#", $str); // troca linha nova e espaço por espaço
                $str = strip_tags($str);

                $html .= $str."---\n";
            }

            $objectMarc21 = new GMarc21($html, "\n", "|", "_", "---"); //faz a quebra dos registros
            $records = $objectMarc21->getRecords();

            $listRecords = array();
            
            //obtém as tags de cada material
            if ( is_array($records) )
            {
                foreach ( $records as $i => $record )
                {
                    $listRecords[] = $record->getTags();
                }
            }
            
            return $listRecords;
        }
    }
}
?>