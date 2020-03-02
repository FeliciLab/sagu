<?php

/**
 * Formulário para inserir, editar e remover registros da tabela ava_avaliacao.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 * 
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 18/11/2011
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class frmAvaAvaliacao extends AManagementForm
{
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaPerfil.class.php', 'avinst');
        $MIOLO->uses('types/avaPerfilWidget.class.php', 'avinst');
        $MIOLO->uses('types/avaCategoria.class.php', 'avinst');
        $MIOLO->uses('classes/autil.class.php', 'avinst');
        $this->target = 'avaAvaliacao';
        parent::__construct(null);
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        parent::createFields();
        $module = MIOLO::getCurrentModule();
        if ( MIOLO::_REQUEST('function')  ==  'edit' )
        {
            $fields[] = new MTextField('idAvaliacao', '', 'Código da avaliação', 10, null, null, true);
            $validators[] = new MIntegerValidator('idAvaliacao', '', 'required');
            
            $avaPerfil = new avaPerfil();
            $perfis = $avaPerfil->search(ADatabase::RETURN_TYPE);
        }
        
        if (Avinst::isFirstAccessToForm())
        {
            $avaPerfil = new avaPerfil();
            $perfis = $avaPerfil->search(ADatabase::RETURN_TYPE);
            
            foreach ($perfis as $perfil)
            {
                MSubDetail::clearData('avaliacaoPerfilWidgets'.$perfil->idPerfil);
                $MIOLO->getSession()->setValue('avaliacaoPerfilWidgets'.$perfil->idPerfil, null);
            }
            
            MSubDetail::clearData('subCategorias');
        }
        
        $fields[] = new MTextField('nome', '', 'Nome', 60);
        $fields[] = new MMultiLineField('descritivo', null, 'Descritivo', 70, 4, 70);
        $fields[] = new MCalendarMobileField('dtInicio', '', 'Data inicial', 10);
        $fields[] = new MCalendarMobileField('dtFim', '', 'Data final', 10);
        $fields[] = new MCalendarMobileField('dtFimRelatorio', '', 'Data final para relatórios', 10);
        $fields[] = new MSelection('tipoProcesso', avaAvaliacao::AVALIACAO_TIPO_PROCESSO_PONTUAL, 'Tipo de processo', avaAvaliacao::obtemTiposProcesso());
        
        $fieldsCategorias[] = $categorias = new MSelection('categorias', null, 'Categorias', avaCategoria::listarCategorias());
        $categorias->addAttribute('onchange', MUtil::getAjaxAction('obtemCategoria', $args));
        $validatorsSubdetail[] = new MRequiredValidator('categorias', 'Categorias');
        
        $fieldsCategorias[] = $categoriaDescricao = new MTextField('categoriaDescricao');
        $categoriaDescricao->addBoxStyle('display', 'none');
        
        $fieldsCategorias[] = $categoriaTipo = new MTextField('categoriaTipo');
        $categoriaTipo->addBoxStyle('display', 'none');

        $columns[] = new MGridColumn('Código categoria', 'right', false, 0, false, 'categorias', false);
        $columns[] = new MGridColumn('Descrição', 'left', false, 0, true, 'categoriaDescricao', false);
        $columns[] = new MGridColumn('Tipo', 'left', false, 0, true, 'categoriaTipo', false);
        
        $fields[] = $subDetail = new MSubDetail('subCategorias', 'Categorias', $columns, $fieldsCategorias, array('remove'));
        $subDetail->setValidators($validatorsSubdetail);
        
        $validators[] = new MRequiredValidator('nome');
        $validators[] = new MRequiredValidator('dtInicio');
        $validators[] = new MRequiredValidator('tipoProcesso');
        
        $avaPerfil = new avaPerfil();
        $perfis = $avaPerfil->search(ADatabase::RETURN_TYPE);
        
        foreach ($perfis as $perfil)
        {
            // Subdetail dos widgets
            $filter = new stdClass();
            $filter->refPerfil = $perfil->idPerfil;
            $avaPerfilWidget = new avaPerfilWidget($filter);
            $perfilWidgets = $avaPerfilWidget->search(ADatabase::RETURN_TYPE);
            
            unset($selection);
            if (is_object($perfilWidgets[0]))
            {
                foreach ($perfilWidgets as $key => $perfilWidget)
                {
                    $filter = new stdClass();
                    $filter->idWidget = $perfilWidget->refWidget;
                    $avaWidget = new avaWidget($filter, true);
                    
                    $selection[$perfilWidget->idPerfilWidget] = $avaWidget->nome;
                }
                $sdFields['id'] = new MTextField('idAvaliacaoPerfilWidget');
                $sdFields['id']->addStyle('display', 'none');
                $sdFields[] = new MSelection('refPerfilWidget', null, 'Componente', $selection);
                $sdFields[] = new MTextField('largura', null, 'Largura', 20);
                $sdFields[] = new MTextField('altura', null, 'Altura', 20);
                $sdFields[] = new MTextField('linha', null, 'Linha', 20);
                $sdFields[] = new MTextField('coluna', null, 'Coluna', 20);
        
                $sdColumns[] = new MGridColumn('Código', 'left', true, null, false, 'idAvaliacaoPerfilWidget');
                $sdColumns[] = new MGridColumn('Componente', 'left', true, null, true, 'refPerfilWidget');
                $sdColumns[] = new MGridColumn('Largura', 'left', true, null, true, 'largura');
                $sdColumns[] = new MGridColumn('Altura', 'left', true, null, true, 'altura');
                $sdColumns[] = new MGridColumn('Linha', 'left', true, null, true, 'linha');
                $sdColumns[] = new MGridColumn('Coluna', 'left', true, null, true, 'coluna');
        
                $fields[] = new MSubDetail('avaliacaoPerfilWidgets'.$perfil->idPerfil, _M('Componentes habilitados para o perfil "'.$perfil->descricao.'"'), $sdColumns, $sdFields);
                unset($sdFields);
                unset($sdColumns);
            }
        }
        $fields[] = $this->getButtons();
        $this->setValidators($validators);
        $this->addFields($fields);      
    }
    
    /**
     * Obtém as informações referentes a uma categoria
     * @param args $args
     */
    public function obtemCategoria($args)
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( $args->subCategorias_categorias )
        {
            $data = new stdClass();
            $data->categoriaId = $args->subCategorias_categorias;
            
            $categoria = new avaCategoria($data, true);
            
            $jscode = " document.getElementById('subCategorias_categoriaDescricao').value = '{$categoria->descricao}'; 
                        document.getElementById('subCategorias_categoriaTipo').value = '{$categoria->tipo}';";
            
            $MIOLO->page->onLoad($jscode);
        }
    }
    
    /**
     * Evento de adição a subdetail
     * @param args $data
     */
    public static function addToTable($data)
    {
        $varificaSubDetail = array(); 
        
        foreach ( $_SESSION['main:avaAvaliacao:subCategorias']->contentData as $key => $dataSubDetail )
        {
            if ( in_array($dataSubDetail->subCategorias_categorias, $varificaSubDetail) )
            {
                unset($_SESSION['main:avaAvaliacao:subCategorias']->contentData[$key]);
                
                $categoria = $dataSubDetail->subCategorias_categoriaDescricao . '/' . $dataSubDetail->subCategorias_categoriaTipo;
                
                new MMessageWarning(_M("A categoria {$categoria} já foi adicionada."), false);
                return;
            }
            else
            {
                $varificaSubDetail[] = $dataSubDetail->subCategorias_categorias;
            }
        }
        
        parent::addToTable($data);
    }
    
    public function saveButton_click()
    {
        if ( $this->validate() )
        {
            $data = $this->getData();

            if ( strlen($data->dtFim) == 0 || AUtil::compararDatas($data->dtInicio, $data->dtFim) )
            {
                parent::saveButton_click();
            }
            else
            {
                new MMessageWarning(_M('A data final não pode ser anterior à data inicial'));
            }
        }
        else
        {
            new MMessageWarning('Verifique os dados informados');
            return;
        }
    }

        //
    //
    //
    public function getData()
    {
        $data = parent::getData();
        
        $avaPerfil = new avaPerfil();
        $perfis = $avaPerfil->search(ADatabase::RETURN_TYPE);
        
        $gridsData = array();
        foreach ($perfis as $perfil)
        {
            $gridData = MSubDetail::getData('avaliacaoPerfilWidgets'.$perfil->idPerfil);
            if (is_array($gridData))
            {
                $gridsData = array_merge($gridsData, $gridData);
            }
        }
        $data->avaliacaoPerfilWidgets = $gridsData;
        
        foreach ( MSubDetail::getData('subCategorias') as $categorias )
        {
            if ( $categorias->dataStatus != 'remove' )
            {
                $categoria[] = $categorias->categorias;
            }
        }
        
        $data->categorias = $categoria;
        
        return $data;
    }
    
    //
    //
    //
    public function editButton_click()
    {
        if (MUtil::isFirstAccessToForm())
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();
            $action = MIOLO::getCurrentAction();
            $type = new $this->target();
            $type->__set($type->getPrimaryKeyAttribute(), MUtil::getAjaxActionArgs()->item);
            $type->populate();

            // Pega os atributos(public,protected) de acordo com o nome do type (target)
            $reflectionClass = new ReflectionClass($this->target);
            foreach ($reflectionClass->getProperties() as $attribute)
            {                
                if ($attribute->name == 'avaliacaoPerfilWidgets')
                {
                    if (is_array($type->{$attribute->name}))
                    {
                        $subData = array();
                        foreach ($type->{$attribute->name} as $attributeData)
                        {
                            if (!is_array($subData[$attributeData->perfilWidget->refPerfil]))
                            {
                                $subData[$attributeData->perfilWidget->refPerfil] = array();
                            }
                            $subData[$attributeData->perfilWidget->refPerfil][] = $attributeData;
                        }
                    }
                    if (is_array($subData))
                    {
                        foreach ($subData as $sub => $data)
                        {
                            MSubDetail::setData($data, 'avaliacaoPerfilWidgets'.$sub);
                        }
                    }
                }
                else if ( $attribute->name == 'categorias' )
                {
                    unset($data);
                    $data = avaCategoriaAvaliacao::obtemCategoriasDaAvaliacao($type->idAvaliacao);
                    
                    MSubDetail::setData($data, 'subCategorias');
                }
                else
                {
                    $this->{$attribute->name}->setValue($type->__get($attribute->name));
                }
            }
        }
    }
}
?>
