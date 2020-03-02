<?php

/**
 * Sistema de widgets para o sistema da avaliação
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2011/11/13
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2008 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */

$MIOLO = MIOLO::getInstance();

class AWidget extends MDiv
{   
    protected $elementName;
    protected $middlewareName;
    protected $middlewareData;
    protected $parameters;
    public $linha;
    public $coluna;
    public $profileConstraint;

    //
    // Estes três atributos definem as informaçoes do widget
    //
    protected $version;
    protected $description;

    
    public function __construct($name)
    {
        parent::__construct($name);
        // Variáveis de identificação do widget
    }
    
    //
    // Classe responsável por retornar as chamadas para o middleware
    // Deve ser implementada pela classe filha, retornando as 
    // informações para a awidgetcontrol
    //
    public function getMiddlewareCalls()
    {
        return false;
    }
        
    //
    // Retorna as propriedades do elemento, em forma de array;
    //
    public function getProperties()
    {
        $properties['idWidget'] = get_class($this);
        $properties['name'] = $this->name;
        $properties['version'] = $this->version;
        $properties['description'] = $this->description;
        $properties['type'] = $this->type;
        return $properties;
    }
    
    public function __get($element)
    {
        return $this->$element;
    }
    
    //
    // Adiciona o tipo de middleware utilizado (útil quando utilizado pools)
    //
    public function setMiddlewareName($middlewareName)
    {
        $this->middlewareName = $middlewareName;
    }
    
    //
    // Retorna o nome do middleware (útil quando utilizado pools)
    // 
    public function getMiddlewareName()
    {
        return $this->middlewareName;
    }
    
    //
    // Retorna o nome do objeto
    //
    public function getName()
    {
        return $this->name;
    }
    
    //
    // Adiciona os dados de retorno do pool para ser utilizado no getWidget
    //
    public function setMiddlewareData($data)
    {
        $this->middlewareData = $data;
    }

    //
    // Retorna os valores carregados nos dados do pool (caso processado)
    //
    public function getMiddlewareData()
    {
        return $this->middlewareData;
    }
}
?>