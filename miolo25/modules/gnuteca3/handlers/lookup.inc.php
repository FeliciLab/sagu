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
 * Class
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/
class GLookup extends MLookup
{
    /* Faz o lookup abrir fechado a primeira vez, colocando limit 0*/
    public function setGrid( MSQL $sql, $columns, $title = 'Pesquisa', $pageLength = 15 , $indexColumn = 0)
    {
        $MIOLO = MIOLO::getInstance();

        //faz com que o select result em nada, no primeiro acesso
        if ( !$MIOLO->page->isPostBack() )
        {
            $order = implode(', ', $sql->orderBy);
            $sql->orderBy = '';
            $sql->setOrderBy(  $order  . ' LIMIT 0' );
        }

        $query = &$this->GetQuery( 'gnuteca3', $sql );
        $this->setQueryGrid( $query, $columns, $title, $pageLength, $indexColumn );
       
        if ( !$MIOLO->page->isPostBack() )
        {
            $this->grid->emptyMsg = '';
        }
        else
        {
            $this->grid->emptyMsg = _M('Nenhum registro encontrado.', 'gnuteca3');
        }
    }

    /*
     * Função modificado do miolo para definir o foco no primeiro campo do related do lookup
     */
    function setQueryGrid($query, $columns, $title = 'Pesquisa', $pageLength = 15, $indexColumn = 0)
    {
        parent::setQueryGrid($query, $columns, $title, $pageLength, $indexColumn);
        
        //$this->grid->setIsScrollable(true);

        $related = explode( ',',$this->related);
        $focus = $related[0];

        for ($i = 0; $i < count($query->result[0]); $i++)
        {
            $args .= ($i ? '|' : '') . "#$i#";
        }

        $this->grid->setActionDefault("{$this->formName}.deliver('$this->formName', {$indexColumn}, '$args'); gnuteca.setFocus('{$focus}',false);");
    }
}

$MIOLO->uses('/db/lookup.class.php','gnuteca3');
$MIOLO->history->pop();
$lookup = new GLookup($module);

$businessClass      = "Business{$lookup->module}Lookup";
$autoCompleteMethod = "AutoComplete{$lookup->item}";
$searchMethod       = "Lookup{$lookup->item}";

$object = new $businessClass();
$lookupMethod = ($lookup->autocomplete && method_exists($object, $autoCompleteMethod)) ? $autoCompleteMethod : $searchMethod;
$object->$lookupMethod($lookup);

//faz a paginação da grid do lookup funcionar
if ( $lookup->grid->pn )
{
    $lookup->grid->pn->setPageNumber( MIOLO::_REQUEST('pn_page' ) ); 
}

$lookup->setContent();
?>