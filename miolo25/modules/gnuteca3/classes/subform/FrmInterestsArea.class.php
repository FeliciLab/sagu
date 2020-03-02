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
 * Class created on 21/10/2008
 *
 **/
class FrmInterestsArea extends GSubForm
{
	public $MIOLO;
	public $module;
    public $business;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->business       = $this->MIOLO->getBusiness($this->module, 'BusInterestsArea');
        parent::__construct( _M('Áreas de interesse', $this->module) );
       
    }

    public function createFields()
    {
        //Mensagem a ser mostrada no topo da tela
        $fields[] = new MDiv('divInterest', LABEL_INTEREST_AREA );
        $fields[] = new MDiv( self::DIV_SEARCH , $this->getGrid());
        $fields[] = new MButton('btnSave', _M('Salvar', $this->module), ':saveButton',GUtil::getImageTheme('save-16x16.png') );
        $this->setFields( $fields );
    }

    public function getGrid()
    {
        //Data
        $data = $this->business->mountInterestsArea( BusinessGnuteca3BusAuthenticate::getUserCode() );
        //Create array with person interests defined
        $selecteds = array();

        if ( count( $data ) )
        {
            foreach ($data as $key => $val)
            {
                if ( strlen( $val[2] ) > 0 )
                {
                    $selecteds[] = $key;
                }
            }
        }

        $grid = $this->MIOLO->getUI()->getGrid($this->module, 'GrdInterestsArea');
        $grid->setData($data);
        $grid->selecteds = $selecteds;

        return $grid;
    }
   
    public function saveButton()
    {
        $action = MIOLO::getCurrentAction();
        $this->business->personId = BusinessGnuteca3BusAuthenticate::getUserCode();
        $this->business->interestsArray = MIOLO::_REQUEST('selectGrdInterestsArea');
        $this->business->insertInterestsArea();
        GForm::information( MSG_RECORD_UPDATED );
    }
}
?>