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
 * Class Marc Tag Listing Form Search
 *
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 28/jul/08
 *
 **/
class FrmMarcTagListingSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('MarcTagListing', array('marcTagListingId', 'description'), array('marcTagListingId'));
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[] = new MTextField("marcTagListingIdS",  null, _M("Código", $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("descriptionS",       null, _M("Descrição", $this->module), FIELD_DESCRIPTION_SIZE);
        $this->setFields( $fields );
    }

    function showOptions()
    {
        $tbData = null;

        $marcTagListingId = MIOLO::_REQUEST('item');

        if($marcOptions = $this->business->getMarcTagListingOptions($marcTagListingId))
        {
            foreach($marcOptions as $content)
            {
                $tbData[] = array($content->option, $content->description);
            }
        }

        $tbColumns = array( _M('Opções',       $this->module), _M('Descrição',   $this->module) );
        
        $tb = new MTableRaw( null, $tbData, $tbColumns);
        $tb->zebra = TRUE;

        $tb->setCellAttribute(0, 0, "width", "80px");
        $tb->setCellAttribute(0, 1, "width", "470px");

        $this->injectContent( $tb, true, _M('Opções de listagem para campos Marc', $this->module) . " - " . $marcTagListingId );
    }
}
?>
