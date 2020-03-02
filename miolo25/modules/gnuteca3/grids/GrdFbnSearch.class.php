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
 * Grid
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 05/05/2009
 *
 **/
class GrdFbnSearch extends GGrid
{
    public $busSearchFormat;
    public $format;
    public $columnsTitle;

    public function __construct($data)
    {
    	global $MIOLO, $module, $action;
        $this->busSearchFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');
        $this->format = $this->busSearchFormat->getSearchFormat(Z3950_SEARCH_FORMAT_ID);

        $columns[] =  new MGridColumn( _M('Dados', $module), MGrid::ALIGN_LEFT,  null, null, true,  null, true);
        $columns[] =  new MGridColumn( _M('Links', $module), MGrid::ALIGN_LEFT,  null, null, true,  null, true);

        parent::__construct(null,$columns);
        
        $this->addActionIcon( _M('Detalhes',  $this->module),  GUtil::getImageTheme('detail.png'),  GUtil::getAjax('detail', '#2#'));
        $this->setRowMethod($this, 'myRowMethod');

        $this->setCSV(false);
    }

    public function myRowMethod($i, $row, $actions, $columns)
    {
        $links = $columns[1]->control[$i]->getValue();

        if ( !is_array($links))
        {
            $links = array($links);
        }

        foreach ($links as $line => $info)
        {
            $explode = explode('/' , $info);
            $explode = $explode[ count($explode)-1 ];
            $colValue .= "<a href='{$info}' target='_blank' >$explode</a><br/>";
        }

        $columns[1]->control[$i]->setValue( $colValue);

        //detalhes só estão ativos para os 10 primeiros itens da primeira página
        //limitação da biblioteca nacional
        if ( $i > 9 || ( MIOLO::_REQUEST('pn_page') && MIOLO::_REQUEST('pn_page') > 1 )  )
        {
            $this->actions[0]->disable();
        }
        else
        {
            $this->actions[0]->enable();
        }
    }
}
?>