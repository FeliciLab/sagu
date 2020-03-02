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
 * DictionaryContent form
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
 * Class created on 03/12/2008
 *
 **/
class FrmDictionaryContent extends GForm
{
	public $MIOLO;
	public $module;
	public $busDictionary;
    public $_dictionaryRelatedContent;
    public $busDictionaryRelatedContent;


    public function __construct()
    {
    	$this->MIOLO  = MIOLO::getInstance();
    	$this->module = MIOLO::getCurrentModule();
    	$this->busDictionary = $this->MIOLO->getBusiness($this->module, 'BusDictionary');
        $this->busDictionaryRelatedContent = $this->MIOLO->getBusiness($this->module, 'BusDictionaryRelatedContent');

        $this->function = MIOLO::_REQUEST('function');

        if ( ($this->function == 'search') || ($this->function == '') )
        {
            $this->setAllFunctions('DictionaryContent', array('dictionaryId'),array('dictionaryContentId'));
        }
        else
        {
            
            
            $busDictionaryContent = $this->MIOLO->getBusiness($this->module, 'BusDictionaryContent');
            $dictionaryContent = $busDictionaryContent->getDictionaryContent(MIOLO::_REQUEST('dictionaryContentId'));
            $dictionaryId = $dictionaryContent->dictionaryId;
            $dictionary = $this->busDictionary->getDictionary($dictionaryId);
        
            if ( MUtil::getBooleanValue($dictionary->readOnly) )
            {
                $this->information(_M("Este dicionário é somente leitura!", $this->module), $this->MIOLO->getActionURL($this->module, MIOLO::getCurrentAction()));
            }
            
            $this->setAllFunctions('DictionaryContent', array('dictionaryId'), 'dictionaryContentId', array('dictionaryId'));
        }

        parent::__construct();

        if ( $this->function == 'insert' && $this->getEvent() == 'tbBtnNew:click' )
        {
            $this->_dictionaryRelatedContent->clearData();
        }
    }


    public function mainFields()
    {        
    	$tag = MIOLO::_REQUEST('openerRelated');
    	$tag = str_replace('_', '.',substr($tag, -5));
    	$dictionaryId = '';

    	if ( strlen($tag) > 0 )
    	{
	    	$dictionary = $this->busDictionary->checkExistsDictionaryForTag($tag);
	    	$dictionaryId = $dictionary->dictionaryId;
	    	$this->setFocus('dictionaryContent');
    	}
    	
        if ( ( $this->function == 'search' ) || ( $this->function == '' ) )
        {
            $dictionaryContentIdS = new MTextField('dictionaryContentIdS', null, _M('Código', $this->module), FIELD_ID_SIZE);
            $fields[] = $dictionaryContentIdS;
        }

        if ( $this->function == 'update' )
        {
            $dictionaryContentId = new MTextField('dictionaryContentId', null,   _M('Código', $this->module), FIELD_ID_SIZE);
            $fields[] = $dictionaryContentId;
            $dictionaryContentId->setReadOnly(TRUE);
            $validators[] = new MRequiredValidator('dictionaryContentId');
        }

        if ( ( $this->function == 'search' ) || ( $this->function == '' ) )
        {
            $fields[] = new GSelection('dictionaryId', $dictionaryId, _M('Dicionário', $this->module), $this->busDictionary->listDictionary());
        }
        else
        {
            // Não lista dicionários somente leitura.            
            $this->busDictionary->readOnly = 'f';
            $this->busDictionary->description = null;
            $this->busDictionary->tags = null;
            $fields[] = new GSelection('dictionaryId', $dictionaryId, _M('Dicionário', $this->module), $this->busDictionary->searchDictionary());
        }
        $fields[] = new MTextField('dictionaryContent', null, _M('Conteúdo', $this->module), FIELD_DESCRIPTION_SIZE);

        if ( $this->function == 'update' )
        {
            $fields[] = new MCheckBox('updateMaterialContent', 'updateMaterialContent', _M('Atualizar o conteúdo dos materiais relacionados', $this->module), false );
        }

        $fields[] = new MSeparator('<br/>');

        if ( ($this->function == 'insert') || ($this->function == 'update') )
        {
            if  ( ($this->primeiroAcessoAoForm()) && (strlen($tag) == 0) )
            {
                $this->setFocus('dictionaryId');
            }

            $validators[] = new MRequiredValidator('dictionaryContent');
            $validators[] = new MRequiredValidator('dictionaryId');

	        $flds[] = new MTextField('relatedContent', null, _M('Conteúdo relacionado', $this->module), FIELD_DESCRIPTION_SIZE);
	        $dictionaryRelatedContent = new MTextField('dictionaryRelatedContentId');
            $dictionaryRelatedContent->setVisibility(FALSE);
            $flds[] = $dictionaryRelatedContent;
	        $columns[] = new MGridColumn(_M('Conteúdo relacionado', $this->module),                 MGrid::ALIGN_LEFT, true, null, true,  'relatedContent');
	        $columns[] = new MGridColumn(_M('Código do conteúdo do dicionário relatado', $this->module), MGrid::ALIGN_LEFT, true, null, false, 'dictionaryRelatedContentId');
	        $valids[] = new MRequiredValidator('relatedContent');
	        $this->_dictionaryRelatedContent = new GRepetitiveField('dictionaryRelatedContent', _M('Conteúdo relacionado', $this->module), $columns, $flds);
	        $this->_dictionaryRelatedContent->setValidators($valids);
	        $fields[] = $this->_dictionaryRelatedContent;
        }

        $fields[] = new MHiddenField('relatedEdit');
        $this->setFields($fields);
        
        if ($validators)
        {
            $this->setValidators($validators);
        }
    }

    public function tbBtnDelete_click($sender = NULL)
    {
        $dictionaryContentId = MIOLO::_REQUEST('dictionaryContentId');
        if ( strlen($dictionaryContentId) == 0 )
        {
            $dictionaryContentIdArray = array();
            foreach($sender->selectGrdDictionaryContent as $line)
            {
                $dictionaryContentIdArray = explode('=', $line);
            }
            
            $dictionaryContentId = $dictionaryContentIdArray[1];
        }
        
        $busDictionaryContent = $this->MIOLO->getBusiness($this->module, 'BusDictionaryContent');
        $dictionaryContent = $busDictionaryContent->getDictionaryContent($dictionaryContentId);
        $dictionaryId = $dictionaryContent->dictionaryId ? $dictionaryContent->dictionaryId : $sender->dictionaryId;
        $dictionary = $this->busDictionary->getDictionary($dictionaryId);

        if ( MUtil::getBooleanValue($dictionary->readOnly) )
        {
            $this->information(_M("Este dicionário é somente leitura!", $this->module), $this->MIOLO->getActionURL($this->module, MIOLO::getCurrentAction()));
        }
        else
        {
            parent::tbBtnDelete_click($sender);
        }
    }

    public function tbBtnSave_click($sender = NULL)
    {
        $this->mainFields();
        
        $data = $this->getData();
        $data->dictionaryRelatedContent = $this->_dictionaryRelatedContent->getData();

        //Tratamento especifico para quando formulario veio da lookup, na Catalogacao
        $errors = $this->gValidator->validate( $data );

        if (MIOLO::_REQUEST('from') == 'lookup' && !$errors)
        {
            $this->business->setData( $data );
            $insert             = call_user_func(array($this->business, $this->insertFunction)); //true/false
            $codigo             = $this->business->dictionaryContentId;
            $dictionaryContent  = $this->business->getDictionaryContent( $codigo );
            $value              = addslashes( $dictionaryContent->dictionaryContent );

            $this->page->onload("
                id = '".MIOLO::_REQUEST('openerRelated')."';
                opener.dojo.byId(id).value = '{$value}';
                self.close();
            ");

            $this->setResponse( null, 'divResponse');
            return;
        }

        $id = MIOLO::_REQUEST('dictionaryContentId');

        $olderContent   = $_SESSION['olderdictionaryContent'];
        $currentContent = MIOLO::_REQUEST('dictionaryContent');
        
        if ( $olderContent != $currentContent && MIOLO::_REQUEST('updateMaterialContent')   )
        {            
            $args['olderContent']           = urlencode($olderContent);
            $args['currentContent']         = urlencode($currentContent);
            $args['dictionaryContentId']    = MIOLO::_REQUEST('dictionaryContentId');
            $args['dictionaryId']           = MIOLO::_REQUEST('dictionaryId');

            $count = $this->business->getUpdateMaterialContentCount( MIOLO::_REQUEST('dictionaryId') , $olderContent);

            $this->question( _M("Deseja atualizar os @1 materiais selecionados de '@2' para '@3'.", $this->module, $count,  $olderContent, $currentContent) , GUtil::getAjax('updateMaterialContent', $args), GUtil::getCloseAction(true));
        }
        else
        {
            parent::tbBtnSave_click($sender, $data);
        }
    }

    public function updateMaterialContent()
    {
        $this->business->updateMaterialContent( MIOLO::_REQUEST('dictionaryId') , urldecode(MIOLO::_REQUEST('olderContent')) , urldecode(MIOLO::_REQUEST('currentContent')));
        parent::tbBtnSave_click($sender, $data);
    }

    public function loadFields()
    {
        $this->business->getDictionaryContent( MIOLO::_REQUEST('dictionaryContentId') );
        $this->setData( $this->business );
        $this->_dictionaryRelatedContent->setData($this->business->dictionaryRelatedContent);
        $_SESSION['olderdictionaryContent'] = $this->dictionaryContent->value;
    }

    public function showRelatedContent()
    {
        $dictionaryContentId = MIOLO::_REQUEST('dictionaryContentId');
        $this->busDictionaryRelatedContent->dictionaryContentId = $dictionaryContentId;
        $search = $this->busDictionaryRelatedContent->searchDictionaryRelatedContent(TRUE);
        
        if ($search)
        {
            //Cria o array com os dados
            $tbData = array();
            
            foreach ($search as $v)
            {
                $tbData[] = array( $v->relatedContent );
            }

            //Cria as colunas
            $tbColumns = array( _M('Conteúdo relacionado', $this->module));

	        $tb = new MTableRaw('', $tbData, $tbColumns);
	        $tb->zebra = TRUE;
        }
        else
        {
            $tb = new MLabel(_M('Nenhum registro encontrado.', $this->module));
        }

        $this->injectContent($tb, true, _M('Conteúdo relacionado', $this->module));
    }


    public function forceAddToTable($args)
    {
        //Verifica se foi uma edicao
        if (MUtil::getBooleanValue($args->relatedEdit))
        {
            $args->dictionaryRelatedContentId = null;
        }
        parent::forceAddToTable($args);
        $this->page->onLoad("document.getElementById('relatedEdit').value = 'f'");
    }


    public function editFromTable($args)
    {
        parent::editFromTable($args);
        $this->page->onLoad("document.getElementById('relatedEdit').value = 't'");
    }
    
    //Retorna o modo do formulário busca ou inserção
    public function getFormMode()
    {
        if ( !$this->function || $this->function == 'search' )
        {
            return search;
        }

        return 'manage';
    }
}
?>
