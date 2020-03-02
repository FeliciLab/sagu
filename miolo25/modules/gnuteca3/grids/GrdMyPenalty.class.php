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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 01/08/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdMyPenalty extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();

        $columns = array(
            new MGridColumn(_M('Código', $this->module),              MGrid::ALIGN_CENTER,  null, null, true,  null, true),
            new MGridColumn(_M('Pessoa', $this->module),            MGrid::ALIGN_RIGHT,  null, null, false, null, true),
            new MGridColumn(_M('Observação', $this->module),       MGrid::ALIGN_LEFT,   null, null, true,  null, true),
            new MGridColumn(_M('Data da penalidade', $this->module),      MGrid::ALIGN_CENTER,  null, null, true,  null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Data final de penalidade', $this->module),  MGrid::ALIGN_CENTER,  null, null, true,  null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Unidade de biblioteca', $this->module),      MGrid::ALIGN_LEFT,   null, null, true,  null, true)
        );

        parent::__construct($data, $columns, $this->MIOLO->getCurrentURL(), LISTING_NREGS, 0, 'gridMyPenalty');
        $this->setIsScrollable();
        
        //Se preferÃªncia estiver como falso, nÃ£o mostra botÃ£o CSV
        if ( (CSV_MYLIBRARY == 'f') && (MIOLO::_REQUEST('action') != 'main:materialMovement') )
        {
            $this->setCSV(false);
        }
        $this->setRowMethod($this, 'checkValues');
    }

    public function checkValues($i, $row, $actions, $columns)
    {
        $data = new GDate($columns[3]->control[$i]->value);
        $columns[3]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));

        $data = new GDate($columns[4]->control[$i]->value);
        $columns[4]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));
    }
}
?>
