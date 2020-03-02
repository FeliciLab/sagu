<?php

define('PN_PAGE', 'pn_page');

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
 *  @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * 
 * Uma implementação de controles de navegação de páginas para grids.
 * Código baseado no MGridNavigator do miolo, mas convertido para ajax.
 * 
 */
class GGridNavigator extends MDiv
{
    /**
     * Número de registros por página
     * @var integer
     */
    public $pageLength;

    /**
     * Número da página atual
     * @var integer
     */
    public $pageNumber;

    /**
     * total de registros
     * @var integer
     */
    public $rowCount;

    /**
     * total de páginas, dado calculado
     * @var integer
     */
    public $pageCount;
    //public $showPageNo = false;
    /**
     * Objeto da grid a qual o paginador pertence
     * @var GGrid
     */
    public $grid;

    /**
     * @var string Máscara de ordenação. 
     */
    private $orderMask;

    /**
     *
     * @param integer $length  Number of records per page
     * @param integer $total   Number total of records
     * @param GGrid The grid which contains this component
     */
    public function __construct($length = 20, $total = 0, $grid = NULL)
    {
        parent::__construct();
        $this->pageLength = $length;
        $this->setRowCount($total);
        $this->grid = $grid;
        //da prioridade para o $_POST, caso não tiver, pega do $_GET

        $pag = MIOLO::_REQUEST('pn_page', 'POST') ? MIOLO::_REQUEST('pn_page', 'POST') : MIOLO::_REQUEST('pn_page', 'GET');

        //segurança para funcionar no FrmAdminReport
        if ( !$pag )
        {
            $pag = MIOLO::_REQUEST('pn_page', 'REQUEST');
        }

        $this->setPageNumber($pag);

        // Seta a máscara de ordenação.
        $this->orderMask = MIOLO::_REQUEST('orderMask');
    }

    /**
     * Define o total de registros
     * @param integer $rowCount
     */
    public function setRowCount($rowCount)
    {
        $this->rowCount = $rowCount;
        $this->pageCount = ($this->pageLength > 0) ? (int) (($this->rowCount + $this->pageLength - 1) / $this->pageLength) : 1;
    }

    /**
     * Define a página atual
     * @param integer $num
     */
    public function setPageNumber($num)
    {
        $this->pageNumber = (int) ($num ? $num : 1);
    }

    /**
     * Define a página atual
     * @param integer $pageNumber
     */
    public function setCurrentPage($pageNumber)
    {
        $this->setPageNumber($pageNumber);
    }

    /**
     * Método público obter a máscara de ordenação.
     * 
     * @return string Máscara de ordenação.
     */
    public function getOrderMask()
    {
        return $this->orderMask;
    }

    /**
     * Método público para setar a máscara de ordenação.
     * 
     * @param string $orderMask Máscara de ordenação.
     */
    public function setOrderMask($orderMask)
    {
        $this->orderMask = $orderMask;
    }

    /**
     * Retorna o primeiro registro visível após a paginação.
     *
     * @return integer
     */
    public function getFirstPaginedRegister()
    {
        return ($this->getPageNumber() * $this->pageLength ) - $this->pageLength;
    }

    public function setGridParameters($pageLength, $rowCount, $action, $grid)
    {
        $this->pageLength = $pageLength;
        $this->setRowCount($rowCount);
        $this->grid = $grid;
        $this->setIndexes();
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * Retorna a página atual
     * @return integer a página atual
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Retorna total de páginas
     * @return integer total de páginas
     */
    public function getPageCount()
    {
        return $this->pageCount;
    }

    /**
     * Monta os links do paginador
     *
     * @param boolean $showPage
     * @param integer $limit
     * @return MLinkButton
     */
    public function getPageLink($showPage = true, $limit = 10)
    {
        $pageCount = $this->getPageCount(); //quantidade de páginas
        $pageNumber = $this->getPageNumber(); //página atual
        $pageLinks = array( ); //array de links
        //cria array para passar por ajax
        $ajaxArgs = new stdClass();
        $ajaxArgs->gridName = $this->grid->name;
        $ajaxArgs->orderMask = $this->orderMask;

        $p = 0;

        //aqui ele entra caso não tenha retornado dados na pesquisa
        if ( !$this->getRowCount() ) //quantidade de linhas
        {
            $pageLinks[$p] = new MLabel('&nbsp;&nbsp;&nbsp;');
            $pageLinks[$p++]->setClass('mGridNavigatorText');
        }
        else
        {
            if ( $showPage )
            {
                $pageLinks[$p] = new MText('', '&nbsp;Página:&nbsp;');
                $pageLinks[$p++]->setClass('mPagenavigatorText');
            }

            if ( $pageNumber <= $limit )
            {
                $o = 1;
            }
            else
            {
                $o = ceil(( ($pageNumber - 1) / $limit) * $limit);
                $ajaxArgs->pn_page = $o - 9; //volta algumas páginas
                $pageLinks[$p] = new MLinkButton('', '...', GUtil::getAjax(searchFunction, $ajaxArgs));
                $pageLinks[$p++]->setClass('mGridNavigatorLink');
            }

            //passa por todas páginas montando o array de links
            for ( $i = 0; ($i < $limit) && ($o <= $pageCount); $i++, $o++ )
            {
                $ajaxArgs->pn_page = $o;

                //se NÃO for a página selecionada
                if ( $o != $pageNumber )
                {
                    $pageLinks[$p] = new MLinkButton('', $o, GUtil::getAjax('searchFunction', $ajaxArgs));
                    $pageLinks[$p]->setClass('mGridNavigatorLink');
                }
                else //página atual, não precisa de link
                {
                    $pageLinks[$p] = new Label($o);
                    $pageLinks[$p]->setClass('mGridNavigatorSelected');
                }

                $p++; //aumenta contador para a próxima página
            }

            //deve ir para uma a mais do que a máxima atual
            if ( $o < $pageCount )
            {
                $ajaxArgs->pn_page = $ajaxArgs->pn_page + 1;
                $pageLinks[$p++] = new MLabel('');
                $pageLinks[$p] = new MLinkButton('', '...', GUtil::getAjax('searchFunction', $ajaxArgs));
                $pageLinks[$p++]->setClass('mGridNavigatorLink');
            }
        }

        return $pageLinks;
    }

    /**
     * Retorna um span para botões de primeiro/próximo/anterior/último
     *
     * @param string $id
     * @param string $label
     * @param integer $pageNumber
     * @param string $imageUrl
     * @return MSpan
     */
    protected function getSpanForPage($id, $label, $pageNumber, $imageUrl)
    {
        //desabilita os links conforme a situação
        if ( $pageNumber == $this->getPageNumber() || $pageNumber > $this->getPageCount() || $pageNumber < 1 )
        {
            $btn = new MImage($id, $label, str_replace('_x', '', $imageUrl), array( 'border' => '0' ));
        }
        else
        {
            $ajaxArgs = new stdClass();
            $ajaxArgs->gridName = $this->grid->name;
            $ajaxArgs->pn_page = $pageNumber;
            $ajaxArgs->orderMask = $this->orderMask;
            
            $btn = new MImageButton($id, $label, GUtil::getAjax('searchFunction', $ajaxArgs), $imageUrl);
        }

        return new MSpan('', $btn, 'mGridNavigatorImage');
    }

    public function generate()
    {
        if ( !$this->getRowCount() )
        {
            $range = _M('Nenhum dado', 'gnuteca3');
        }
        else
        {
            $range = $this->getPageLink(false);
        }

        $array[0] = $this->getSpanForPage('_gnFirst', _M('Primeira', 'gnuteca3'), 1, GUtil::getImageTheme('but_pg_primeira_x.gif'));
        $array[1] = $this->getSpanForPage('_gnPrev', _M('Anterior', 'gnuteca3'), $this->getPageNumber() - 1, GUtil::getImageTheme('but_pg_anterior_x.gif'));
        $array[2] = new MSpan('', $range, 'mGridNavigatorRange');
        $array[3] = $this->getSpanForPage('_gnNext', _M('Próxima', 'gnuteca3'), $this->getPageNumber() + 1, GUtil::getImageTheme('but_pg_proxima_x.gif'));
        $array[4] = $this->getSpanForPage('_gnLast', _M('Última', 'gnuteca3'), $this->getPageCount(), GUtil::getImageTheme('but_pg_ultima_x.gif'));

        $this->setInner($array);
        $this->setClass('mGridNavigator');
        return parent::generate();
    }
}
?>