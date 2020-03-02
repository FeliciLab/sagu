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
 * Library Unit search form
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
class FrmLibraryUnitSearch extends GForm
{
    /** @var BusinessGnuteca3BusLibraryUnit */
    public $business;

    public function __construct()
    {
        $this->setAllFunctions('LibraryUnit', array('libraryUnitIdS','libraryNameS'), array('libraryUnitId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $businessLibraryGroup     = $this->MIOLO->getBusiness( $this->module, 'BusLibraryGroup');
        $businessPrivilegeGroup   = $this->MIOLO->getBusiness( $this->module, 'BusPrivilegeGroup');

        $fields[] = new MIntegerField('libraryUnitIdS', null, _M('Código',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('libraryNameS', null, _M('Nome',$this->module),FIELD_DESCRIPTION_SIZE );
        $fields[] = new GSelection('isRestrictedS',null, _M('É restrita', $this->module),GUtil::listYesNo() );
        $fields[] = new MTextField('cityS', null, _M('Cidade',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('emailS', null, _M('Email',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('privilegeGroupIdS', null, _M('Grupo de privilégio',$this->module), $businessPrivilegeGroup->listPrivilegeGroup());;
        $fields[] = new GSelection('libraryGroupIdS', null, _M('Grupo de biblioteca',$this->module), $businessLibraryGroup->listLibraryGroup());
        $fields[] = new MIntegerField('levelS', null, _M('Nível',$this->module), FIELD_ID_SIZE);

        $this->business->filterOperator = TRUE;
        $this->business->labelAllLibrary = TRUE;
        $listLibraryUnit = $this->business->listLibraryUnit();
        $fields[] = new GSelection('libraryUnitIdSelect', null, _M('Unidade de biblioteca', $this->module), $listLibraryUnit, null, null, null, TRUE);

        $this->setFields( $fields );
    }

    /*
     * Mostra os dias em que a unidade de biblioteca está fechada
     */
    public function showDays()
    {
        $busLibraryUnitIsClosed = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnitIsClosed');
        $libraryName = $this->business->getLibraryName( MIOLO::_REQUEST('libraryUnitId') );
        $busLibraryUnitIsClosed->libraryUnitIdS = MIOLO::_REQUEST('libraryUnitId');
        $search = $busLibraryUnitIsClosed->searchLibraryUnitIsClosed(TRUE);

        if ( $search )
        {
	        for ( $i=0; $i < count($search); $i++)
	        {
	            $tbData[] = array( $search[$i]->weekDescription );
	        }

	        $tb = new MTableRaw('', $tbData, array( _M('Dia da semana', $this->module) ) );
	        $tb->setAlternate(true);
            $this->injectContent( $tb, true, _M('Dias fechados para ', $this->module) . MIOLO::_REQUEST('libraryUnitId') . ' - '. $libraryName  );
        }
        else
        {
        	GForm::information( _M('A unidade @1 não está fechada em nenhum dia.', $this->module, MIOLO::_REQUEST('libraryUnitId') . ' - '. $libraryName ) );
        }
    }

    /**
     * Mostra grupos que tem acesso a esta unidade
     */
    public function showGroups()
    {
        $libraryName = $this->business->getLibraryName( MIOLO::_REQUEST('libraryUnitId') );
        $busLibraryUnitAccess = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnitAccess');
        $busLibraryUnitAccess->libraryUnitIdS = MIOLO::_REQUEST('libraryUnitId');
        $search = $busLibraryUnitAccess->searchLibraryUnitAccess(TRUE);

        if ($search)
        {
	        for ($i=0; $i < count($search); $i++)
	        {
	            $tbData[] = array( $search[$i]->description );
	        }

	        $tb = new MTableRaw('', $tbData, array( _M('Grupo', $this->module) ) );
	        $tb->setAlternate(TRUE);
            $this->injectContent( $tb, true, _M('Grupos para ', $this->module) . MIOLO::_REQUEST('libraryUnitId') .' - ' . $libraryName );
        }
        else
        {
        	GForm::information( _M('Nenhum grupo encontrado para @1.', $this->module, MIOLO::_REQUEST('libraryUnitId') .' - '. $libraryName) );
        }
    }
}
?>