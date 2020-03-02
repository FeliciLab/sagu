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
class GrdFavorite extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busSearchFormat;
    public $busGenericSearch;

    public function __construct($data)
    {
        $this->MIOLO            = MIOLO::getInstance();
        $this->module           = MIOLO::getCurrentModule();
        $this->action           = MIOLO::getCurrentAction();
        $this->busSearchFormat  = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busGenericSearch = $this->MIOLO->getBusiness($this->module, 'BusGenericSearch2');

        $columns = array(
            new MGridColumn(_M('Pessoa', $this->module),            MGrid::ALIGN_RIGHT,   null, null, false, null, true),
            new MGridColumn(_M('Dados', $this->module),              MGrid::ALIGN_LEFT,    null, null, true, null, true),
            new MGridColumn(_M('Data de entrada', $this->module),     MGrid::ALIGN_CENTER,  null, null, true, null, true, MSort::MASK_DATETIME_BR)
        );

        parent::__construct($data, $columns );
        $this->setShowHeaders(false);

        $this->addActionIcon( _M('Excluir', $this->module), GUtil::getImageTheme('table-delete.png'), GUtil::getAjax('deleteFavoriteConfirm', '%1%'));

        $this->setIsScrollable();
        //Se preferência estiver como falso, não mostra botão CSV
        if (CSV_MYLIBRARY == 'f')
        {
            $this->setCSV(false);
        }

        $this->setRowMethod($this, 'checkValues');

        //Add detail button
        $this->addActionIcon(_M('Detalhes', $this->module), GUtil::getImageTheme('config-16x16.png') , GUtil::getAjax('openMaterialDetail', '%1%'));
    }


    public function checkValues($i, $row, $actions, $columns)
    {
    	//pega lista de campos necessários para o formato
        $fieldsList = $this->busSearchFormat->getVariablesFromSearchFormat( FAVORITES_SEARCH_FORMAT_ID, array('search'));
        //tira $ dos campos e adiciona na busca
        if ( is_array($fieldsList) )
        {
            foreach ($fieldsList as $line => $info)
            {
                $tag = str_replace('$','', $info);
                $this->busGenericSearch->addSearchTagField($tag);
            }
        }
        
        //adicionar somente o control number a busca
        $this->busGenericSearch->controlNumber = '';
        $this->busGenericSearch->addControlNumber( $this->data[$i][1] );
        //pega os dados do controlNumber desta linha
        $data = $this->busGenericSearch->getWorkSearch();
        //busca os texto ja formatado
        $temdata = $this->busSearchFormat->getFormatedString($this->data[$i][1], FAVORITES_SEARCH_FORMAT_ID);
        $columns[1]->control[$i]->setValue($temdata);
        $data = new GDate($columns[2]->control[$i]->value);
        $columns[2]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));
    }
    
}
?>