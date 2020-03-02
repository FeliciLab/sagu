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
 * Class created on 28/11/2008
 *
 **/
class FrmSearchFormatSearch extends GForm
{
    public $MIOLO;
    public $module;
    public $busSearchFormatAccess;

    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
        $this->busSearchFormatAccess = $this->MIOLO->getBusiness($this->module, 'BusSearchFormatAccess');
        $this->setAllFunctions('SearchFormat', array('searchFormatIdS','descriptionS','isRestrictedS'),array('searchFormatId'));

        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MIntegerField('searchFormatIdS', null , _M('Código',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('descriptionS', null, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('isRestrictedS',null,_M('É restrita', $this->module), GUtil::listYesNo() );;

        $this->setFields($fields);
    }

    /**
     * Mostra os grupos que podem acessar essa grid
     */
    public function showGroup()
    {
        $searchFormatId = MIOLO::_REQUEST('searchFormatId');
        $this->busSearchFormatAccess->searchFormatId = $searchFormatId;
        $search = $this->busSearchFormatAccess->searchSearchFormatAccess(TRUE);

        if ($search)
        {
            //Cria o array com os dados
            $tbData = array();

            foreach ($search as $v)
            {
                $tbData[] = array( $v->linkIdDescription);
            }

	        $tb = new MTableRaw('', $tbData, array( _M('Grupo', $this->module) ) );
	        $tb->zebra = TRUE;

            $this->injectContent( $tb, true, _M('Grupos com acesso', $this->module));
        }
        else
        {
            $this->information( _M('Formato de pesquisa "@1" não é restringido por nenhum grupo.', $this->module, $searchFormatId) );
        }
    }
}
?>