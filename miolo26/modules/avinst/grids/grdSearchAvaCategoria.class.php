<?php

/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 * 
 * Este arquivo é parte do programa Sagu.
 * 
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * @author Nataniel I. da Silva [nataniel@solis.coop.br]
 *
 * @version: $Id$
 *
 * @since
 * Class created on 09/06/2015
 *
 **/

class grdSearchAvaCategoria extends AGrid
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaCategoria.class.php', 'avinst');
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        
        $columns[] = new MGridColumn('Código da categoria', 'right', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Descrição', 'left', true, NULL, true, NULL, true);
        $columns[] = new MGridColumn('Tipo', 'left', true, NULL, true, NULL, true);
        
        $primaryKeys = array('categoriaId'=>'%0%', );
        $url = $MIOLO->getActionUrl($module, $action);
        
        parent::__construct(__CLASS__, NULL, $columns, $url);
        
        $args = array('event'=>'editButton:click', 'function'=>'edit');
        $hrefUpdate = $MIOLO->getActionURL($module, $action, '%0%', $args);
        
        $args = array(MUtil::getDefaultEvent()=>'deleteButton:click', 'function'=>'search');
        $hrefDelete = $MIOLO->getActionURL($module, $action, '%0%', $args);
        
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);
    }
}


?>
