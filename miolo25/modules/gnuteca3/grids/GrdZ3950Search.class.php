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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 05/05/2009
 *
 **/
class GrdZ3950Search extends GGrid
{
    public $busSearchFormat, $format;

    public function __construct($data)
    {
    	global $MIOLO, $module, $action;

    	$this->busSearchFormat = $MIOLO->getBusiness($module, 'BusSearchFormat');

        $columns = array(
            new MGridColumn(_M('Dados',              $module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Dados serializados',    $module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('gridLIne',          $module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
        );

        parent::__construct($data, $columns);

        $this->format = $this->busSearchFormat->getSearchFormat( MIOLO::_REQUEST('searchFormat') ? MIOLO::_REQUEST('searchFormat') : Z3950_SEARCH_FORMAT_ID  ,true);

        $this->setRowMethod($this, 'checkValues');
        $this->setIsScrollable();
        $this->addActionIcon( _M('Detalhes',  $this->module),  GUtil::getImageTheme('detail.png'),  GUtil::getAjax('detail', '#2#'));
    }


    public function checkValues($i, $row, $actions, $columns)
    {
        $content = unserialize( $columns[1]->control[$i]->getValue() );
        $content2 = "";

        $GFunction = new GFunction();
        $GFunction->setVariable('$LN', "###BREAKLINE###" );
    	$GFunction->setVariable('$SP','&nbsp;' );

        foreach ($content as $tag => $registros)
        {
            $subfields = $registros->subfields;

            foreach ($subfields as $subf => $subContent)
            {
                $contentFormated = "";

                foreach ($subContent as $lineSub => $cont)
                {
                    if(strlen($contentFormated))
                    {
                        $contentFormated.= "\n";
                    }

                    $contentFormated.= "$cont->content";
                }

                $GFunction->setVariable("\${$tag}.{$subf}", $contentFormated);
                $content2.= "\${$tag}.{$subf} = $contentFormated <br>";
            }
        }

        $content = $GFunction->interpret($this->format->searchPresentationFormat[0]->searchFormat, true);
        $content = str_replace("###BREAKLINE###", "<br/>", $content );
        $columns[0]->control[$i]->setValue( $content );
    }
}
?>