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
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
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
 * Class created on 31/jul/08
 *
 **/
class FrmMarcTagListing extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('MarcTagListing', array('description'), 'marcTagListingId', array('description'));
        parent::__construct();
        
        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('MarcTagListingOptions');
        }
    }

    /**
     * Create Default Fileds for Search Form
     *
     * @return void
     */
    public function mainFields($sender)
    {
        $fields[] = new MTextField("marcTagListingId",   $this->marcTagListingId->value, _M("Código",          $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("description_",       $this->description_->value,     _M("Descrição",   $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MSeparator();

        $fields[] = $marcTagListingOptions = new GRepetitiveField('MarcTagListingOptions', _M('Opções de listagem para campos Marc', $this->module));

        $tableFields[] = new MTextField('option',       null, _M('Opção', $this->module),          FIELD_ID_SIZE);
        $tableFields[] = new MTextField('description',  null, _M('Descrição', $this->module ),    FIELD_DESCRIPTION_SIZE);

        $marcTagListingOptions->setFields($tableFields);

        $repetitiveFieldsValidators[] = new MRequiredValidator      ('option',      _M('Opção', $this->module));
        $repetitiveFieldsValidators[] = new GnutecaUniqueValidator  ('option',      _M('Opção', $this->module));
        $repetitiveFieldsValidators[] = new MRequiredValidator      ('description', _M('Descrição', $this->module ));
        $repetitiveFieldsValidators[] = new GnutecaUniqueValidator  ('description', _M('Descrição', $this->module ));

        $marcTagListingOptions->setValidators($repetitiveFieldsValidators);

        $columns = array
        (
            new MGridColumn( _M('Opção',       $this->module), 'left', true, "20%", true, 'option'      ),
            new MGridColumn( _M('Descrição',  $this->module), 'left', true, "64%", true, 'description' ),
        );

        $marcTagListingOptions->setColumns($columns);

        $this->setFields( $fields );

        if( $this->function == 'update' )
        {
            $this->marcTagListingId->setReadOnly(true);
        }
        
        $validators[] = new MRequiredValidator("marcTagListingId", _M("Código", $this->module) );
        $validators[] = new MRequiredValidator("description_", _M("Descrição", $this->module) );
        $validators[] = new MRequiredValidator("MarcTagListingOptions");
        
        $this->setValidators($validators);
    }

    /**
     * Metodo chamado ao clicar no botao btnSearch
     */
    public function tbBtnSave_click($sender)
    {
    	$data = $this->getData();
        $data->marc_options = GRepetitiveField::getData('MarcTagListingOptions');
        parent::tbBtnSave_click($sender, $data);
    }

    /**
     * carrega os dados do form com o conteudo do banco
     */
    public function loadFields()
    {
        $marcTagListingId = MIOLO::_REQUEST('marcTagListingId');
        $this->business->getMarcTagListing($marcTagListingId);
        $this->setData( $this->business );
        $marcOptions = $this->business->getMarcTagListingOptions($marcTagListingId);
        GRepetitiveField::setData($marcOptions,'MarcTagListingOptions');
    }
}
?>