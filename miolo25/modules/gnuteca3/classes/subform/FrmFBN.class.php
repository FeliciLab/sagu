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
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 05/09/2010
 *
 **/
$MIOLO->getClass( $module , 'controls/GMaterialDetailOther');

class FrmFBN extends GSubForm
{
    /** @var BusinessGnuteca3BusFBN  */
    public $business;
    private $busAuthenticate;

    public function __construct()
    {
        $this->gridName = 'GrdFbnSearch';
        $this->gridSearchMethod = 'searchFBN';
        $MIOLO = MIOLO::getInstance();
        $this->business = $MIOLO->getBusiness('gnuteca3','BusFBN');
        $this->busAuthenticate = $MIOLO->getBusiness('gnuteca3', 'BusAuthenticate');

        parent::__construct( _M('Biblioteca nacional', 'gnuteca3') );

        GForm::setFocus('arg',false);
    }

    /**
     * A checagem de acesso deste formulário é via integração com biblioteca nacional
     *
     * @return boolean
     */
    public function checkAcces()
    {
        return MUtil::getBooleanValue( FBN_INTEGRATION );
    }

    /**
     * Este formulário não precisa de login do usuário
     *
     * @return boolean
     */
    public static function isUserLoginNeeded()
    {
        return false;
    }

    public function createFields()
    {
        $busSearchFormat    = $this->manager->getBusiness($this->module, 'BusSearchFormat');
        $searchFormat       = $busSearchFormat->listSearchFormat(false, !GOperator::isLogged());

        $fields[] = new GSelection('use', BusinessGnuteca3BusFBN::USE_TODOS, _M('Filtro',$this->module), $this->business->getFilterList() );
        $fields[] = new MTextField('arg', null, _M('Termo',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection( 'searchFormat', SIMPLE_SEARCH_SEARCH_FORMAT_ID, _M('Formato de pesquisa') , $searchFormat ,null,null,null,true);
        $fields[] = new MSeparator('<br/>');
        $fields[] = new MDiv('', _M('Este serviço depende da conexão com o site da Fundação Biblioteca Nacional.', $this->module) );
        $fields = GUtil::alinhaForm($fields);

        $this->setFields( $fields , true);
        $this->setValidators( $this->getValidators() );
    }

    public function getValidators()
    {
        $valids[] = new MRequiredValidator('use',_M('Filtro', $this->module) );
        $valids[] = new MRequiredValidator('arg',_M('Termo', $this->module) );
        return $valids;
    }

    /**
     * Monta os detalhes do material especifico
     *
     * @param integer $index
     */
    public function detail( $index )
    {
        $marc21 = $this->business->searchFBNMarc();
        
        if ( $index > 9 )
        {
            throw new Exception ( _M( 'Detalhes não disponíveis para este material, refine sua busca e tente novamente!','gnuteca3') );
        }

        $data   = $marc21[$index]; //$index é o indice que vem da grid
        $materialDetailOther = new GMaterialDetailOther($data);
        $materialDetailOther->setTagsForIgnore( Z3950_IGNORAR_TAGS );
        GForm::injectContent( $materialDetailOther , null, _M('Detalhes do material', $this->module));
    }
}
?>