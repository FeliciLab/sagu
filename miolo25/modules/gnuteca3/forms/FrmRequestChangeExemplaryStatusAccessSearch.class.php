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
 * Class Request change exemplary status access Form Search
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 24/04/2009
 *
 **/
class FrmRequestChangeExemplaryStatusAccessSearch extends GForm
{
    public $busExemplaryStatus;
    public $busRequestChangeExemplaryStatusAccess;
    public $busBond;

    public function __construct()
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $this->busRequestChangeExemplaryStatusAccess = $MIOLO->getBusiness($module, 'BusUserGroup');
        $this->busBond = $MIOLO->getBusiness($module, 'BusBond');
        $this->busExemplaryStatus = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
        $this->setAllFunctions('RequestChangeExemplaryStatusAccess', array('basLinkId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new GSelection('basLinkIdS', $this->basLinkIdS->value, _M('Código do grupo de usuário', $this->module), $this->busBond->listBond(true));
        $fields[] = new GSelection('exemplaryStatusIdS', $this->exemplaryStatusIdS->value, _M('Estado do exemplar', $this->module), $this->busExemplaryStatus->listExemplaryStatus());

        $this->setFields( $fields );
    }
}
?>