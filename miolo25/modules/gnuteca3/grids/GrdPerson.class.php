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
 * @author Moises Heberle [moises@solis.coop.br]
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
 * Class created on 07/08/2008
 *
 **/
class GrdPerson extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;

    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        
        //Verificação para colocar ou não campo login, nos resultados da grid ao pesquisar por pessoa
        $loginVisible = false;
        if(GSipCirculation::usingSmartReader())
        {
            $loginVisible = true;
        }
        
        $columns = array(
            new MGridColumn(_M('Código', $this->module),         MGrid::ALIGN_RIGHT, null, null, true,  null, true),
            new MGridColumn(_M('Nome', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Cidade', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('CEP', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Tipo de Logradouro', $this->module),MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Logradouro', $this->module),MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Número', $this->module),MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Complemento', $this->module),MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('E-mail', $this->module),MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Bairro', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Senha', $this->module),MGrid::ALIGN_LEFT,  null, null, false,  null, true),
            new MGridColumn(_M('Login', $this->module),MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Base ldap', $this->module),MGrid::ALIGN_LEFT,  null, null, false,  null, true),
            new MGridColumn(_M('Grupo', $this->module),MGrid::ALIGN_LEFT,  null, null, false,  null, true),
            new MGridColumn(_M('Sexo', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  BusinessGnuteca3BusDomain::listForSelect('SEX', false, true), true)
        );

        parent::__construct($data, $columns);

        $args = array( 'function' => 'update','personId' => '%0%'        );//Make update action
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        
        $args = array( 'function' => 'delete','personId' => '%0%'  );//Make delete action

        $args['function'] = 'detail';

        $imgBond    = GUtil::getImageTheme('bond-16x16.png');
        $imgPenalty = GUtil::getImageTheme('penalty-16x16.png');

        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->addActionIcon(_M('Vínculo', $this->module), $imgBond, GUtil::getAjax('showBond', $args) );
        $this->addActionIcon(_M('Penalidade', $this->module), $imgPenalty, GUtil::getAjax('showPenalty', $args) );
        $this->addActionIcon( _M('Foto', $this->module), GUtil::getImageTheme('photo-16x16.png'), GUtil::getAjax('showPhoto',"%0%"));
        $this->setIsScrollable();
        
    }
}
?>
