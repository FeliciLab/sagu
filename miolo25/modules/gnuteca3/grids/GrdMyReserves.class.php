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
 * @author Luiz G. Gregory Filho [luiz@solis.coop.br]
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
 * Class created on 01/ago/2008
 *
 **/

class GrdMyReserves extends GGrid
{
    function __construct($data)
    {
        $columns = array
        (
            new MGridColumn( _M('Código', $this->module), MGrid::ALIGN_CENTER,   null, null, true, null, true ),
            new MGridColumn( _M('Dados', $this->module), MGrid::ALIGN_LEFT,     null, null, true, null, true ),
            new MGridColumn( _M('Autor', $this->module), MGrid::ALIGN_LEFT,     null, null, false, null, true ),
            new MGridColumn( _M('Data da requisição', $this->module), MGrid::ALIGN_CENTER,   null, null, true, null, true, MSort::MASK_DATETIME_BR ),
            new MGridColumn( _M('Data limite', $this->module), MGrid::ALIGN_CENTER,   null, null, true, null, true, MSort::MASK_DATETIME_BR ),
            new MGridColumn( _M('Estado', $this->module), MGrid::ALIGN_LEFT,     null, null, true, null, true ),
            new MGridColumn( _M('Posição', $this->module), MGrid::ALIGN_CENTER,   null, null, true, null, true ),
            new MGridColumn( _M('Data prevista da devolução', $this->module), MGrid::ALIGN_CENTER,   null, null, true, null, true ),
            new MGridColumn( _M('Biblioteca', $this->module), MGrid::ALIGN_LEFT,     null, null, true, null, true ),
        );

        parent::__construct($data , $columns);
        $this->setShowHeaders(false);
        $this->setIsScrollable(true);

        $this->addActionIcon( _M('Cancelar', $this->module), GUtil::getImageTheme('cancel-16x16.png'), "javascript:".GUtil::getAjax('cancel','%0%'));

        //Add favorite button
        $href = $this->MIOLO->getActionURL($this->module, $this->MIOLO->getCurrentAction(), null, $args);
        $this->addActionIcon(_M('Favoritos', $this->module), GUtil::getImageTheme('favorites-16x16.png'), "javascript:".GUtil::getAjax('favorites','%0%'));

        //Add detail button
        $href = $this->MIOLO->getActionURL($this->module, $this->MIOLO->getCurrentAction(), null, $args);
        $this->addActionIcon(_M('Detalhes', $this->module), GUtil::getImageTheme('config-16x16.png'), "javascript:".GUtil::getAjax('showDetail','%0%'));
        
        //Se preferência estiver como falso, não mostra botão CSV
        if (CSV_MYLIBRARY == 'f')
        {
            $this->setCSV(false);
        }
    }
    
}
?>