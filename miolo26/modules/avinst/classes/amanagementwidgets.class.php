<?php

$MIOLO->uses('classes/awidget.class.php', 'avinst');
$MIOLO->uses('types/avaWidget.class.php', 'avinst');
$MIOLO->uses('classes/middleware/middlewarePools.class.php', 'avinst');


class AManagementWidgets
{
    const LIST_ARRAY_DESCRITIVO = 1; 
    const LIST_ARRAY_SEQUENCIAL = 2;

    // Widgets a serem devolvidos
    private $widgets;
    // Parâmetros padrão para os widgets
    private $params;
    // Código do perfil ao qual deve-se gerar widgets
    private $refPerfil;
    // Conjunto de chamadas para o middleware
    private $pools;
    
    //
    // Classe construtora para verificar os widgets disponíveis e exibir na tela, conforme permissões do usuário
    //
    public function __construct($refAvaliacao, $refPerfil, $params)
    {
        $this->params = $params;
        $this->params->refPerfil = $refPerfil;
        $this->refPerfil = $refPerfil;
        $this->refAvaliacao = $refAvaliacao;
        $this->widgets = $this->listWidgets(self::LIST_ARRAY_DESCRITIVO);
        $this->pools = array();
    }

//
// ------------------------------- Área para manipulação de arquivos ---------------------------------------------------
// 

    //
    //  Retorna se existe a classe para o elemento passado via parâmetro
    //
    public static function existsWidgetClass($idWidget)
    {
        $MIOLO = MIOLO::getInstance();
        $dir = $MIOLO->getAbsolutePath('classes/widgets/', 'avinst');
        $file = $idWidget.'.class.php';
        if (is_file($dir.$file))
        {
            $MIOLO->uses("classes/widgets/$idWidget.class.php", 'avinst');
            return class_exists($idWidget);
        }
    }
    
    //
    // Lista os widgets encontrados, se passado código da avaliação, retorna somente os widgets da avaliação em questão
    //
    public function listWidgetFiles($listType = self::LIST_ARRAY_DESCRITIVO)
    {
        $MIOLO = MIOLO::getInstance();
        $dir = $MIOLO->getAbsolutePath('classes/widgets/', 'avinst');
        // Se existir o diretório de widgets
        if (is_dir($dir))
        {
            // Pega lista de arquivos
            if ($dirWidgets = opendir($dir))
            {
                // Lista todos
                while (($file = readdir($dirWidgets)) !== false)
                {
                    if (stripos($file, '.class.php')>0)
                    {
                        $widgetName = str_replace('.class.php', '', $file);                    
                        $widgets[] = $this->getWidget($widgetName);
                    }
                }
                closedir($dirWidgets);
            }
        }
        return $widgets;
    }
    
    // 
    // Verifica se o widget existe e, caso exista,
    // obtém as propriedades dele
    //
    public function getWidget($widgetName, $listType = self::LIST_ARRAY_DESCRITIVO)
    {
        $MIOLO = MIOLO::getInstance();
        $dir = $MIOLO->getAbsolutePath('classes/widgets/', 'avinst');
        $file = $widgetName.'.class.php';
        // Se o arquivo existir
        if (file_exists($dir.$file))
        {
            // Obtém o nome da classe
            if ($widgetName.'.class.php' == $file)
            {
                // Tenta instanciar
                $MIOLO->uses('classes/widgets/'.$file, 'avinst');
                if (class_exists($widgetName))
                {
                    $instance = new $widgetName($params);
                    // E obtém as propriedades
                    $properties = $instance->getProperties();
                    if ($listType == self::LIST_ARRAY_DESCRITIVO)
                    {
                        $widget = $properties;
                    }
                    elseif ($listType == self::LIST_ARRAY_SEQUENCIAL)
                    {
                        if (is_array($properties))
                        {
                            foreach ($properties as $property)
                            {
                                $arrayProperties[] = $property;
                            }
                            $widget = $arrayProperties;
                            unset($arrayProperties);
                        }
                    }
                    return $widget;
                }
            }
        }
        return false;
    }

//
// ------------------------------- Fim da manipulação das chamadas com arquivos -----------------------------------------
//

//
// ----------------------------- Manipulação das classes e instâncias dos widgets ---------------------------------------
//

    //
    // Lista os widgets que estão na base de dados, para o perfil e avaliação em específico
    //
    public function listWidgets()
    {
        $filter = new stdClass();
        $filter->refPerfil = $this->refPerfil;
        $filter->refAvaliacao = $this->refAvaliacao;
        
        $avaWidget = new avaWidget($filter);
        $widgets = $avaWidget->checkWidgets($filter);
        $widgetsInfo = array();
        if (count($widgets)>=1)
        {
            $avaAvaliacaoWidget = new avaAvaliacaoWidget();
            foreach ($widgets as $widget)
            {
                // Pega o widget
                $widgetData = $this->getWidget($widget[0]);
                // Se ele retornar, adiciona à lista de widgets
                if ($widgetData != false)
                {
                    // Pega as configurações do widget
                    $avaAvaliacaoWidget->__set('refAvaliacao',$this->refAvaliacao);
                    $avaAvaliacaoWidget->__set('refWidget',$widgetData['idWidget']);
                    $widgetConfs = $avaAvaliacaoWidget->search(ADatabase::RETURN_TYPE);
                    $widgetData['opcoes'] = unserialize($widgetConfs[0]->__get('opcoes'));
                    $widgetsInfo[$widgetData['idWidget']] = $widgetData;
                }
            }
        }                    
        return $widgetsInfo;
    }

    //
    // Indica se existem widgets carregados
    //
    public function hasWidgets()
    {
        if (is_array($this->widgets))
        {
            return true;
        }
        return false;
    }
    
    //
    // Retorna apenas um widget, o passado via parâmetro
    //
    public function returnWidget($widgetName, $listType)
    {
        if (is_array($this->widgets))
        {
            foreach ($this->widgets as $widget)
            {
                if ($widget['idWidget'] == $widgetName)
                {
                    $class = $widget['idWidget'];
                }
            }
            if (strlen($class)>0)
            {
                // Instancia o widget
                $class = $widget['idWidget'];
                $widgetObj[$widget['idWidget']] = new $class($this->params, $listType);

                // Verifica se o widget tem pool (necessário para o Adianti)
                $wPools = $widgetObj[$widget['idWidget']]->getPools();
                if ($wPools)
                {
                    // Se existir, verifica se já não tem um pool registrado
                    if (!isset($this->pools[$widgetObj[$widget['idWidget']]->getMiddlewareName()]))
                    {
                        // Se não tiver, registra
                        $this->pools[$widgetObj[$widget['idWidget']]->getMiddlewareName()] = new middlewarePools($widgetObj[$widget['idWidget']]->getMiddlewareName());
                    }
                    $this->pools[$widgetObj[$widget['idWidget']]->getMiddlewareName()]->addPool($widgetObj[$widget['idWidget']]->getName(), $wPools);
                }

                // Verifica se existem pools
                if (count($this->pools)>0)
                {
                    // Se existir, percorre cada um e executa a chamada
                    foreach ($this->pools as $middleware => $pool)
                    {
                        if (!is_array($poolReturn))
                        {
                            $poolReturn = array();
                        }
                        $poolReturn = array_merge($poolReturn, $this->pools[$middleware]->processPool());
                    }
                }
                $widgets = array();
                // Agora, percorre novamente, setando os retornos aos widgets correspondentes
                if (isset($poolReturn[$widget['idWidget']]))
                {
                    $widgetObj[$widget['idWidget']]->setPools($poolReturn[$widget['idWidget']]);
                }
                $widgets[] = $widgetObj[$widget['idWidget']]->returnWidget($this->params);
                return $widgets;
            }
        }
    }
    
    //
    // Retorna os widgets
    //
    public function returnWidgets()
    {
        if (is_array($this->widgets))
        {
            // Percorre e instancia widget por widget
            foreach ($this->widgets as $widget)
            {
                // Instancia o widget
                $class = $widget['idWidget'];
                $widgetObj[$widget['idWidget']] = new $class($this->params);
                
                // Verifica se o widget tem pool (necessário para o Adianti)
                $wPools = $widgetObj[$widget['idWidget']]->getPools();
                if ($wPools)
                {
                    // Se existir, verifica se já não tem um pool registrado
                    if (!isset($this->pools[$widgetObj[$widget['idWidget']]->getMiddlewareName()]))
                    {
                        // Se não tiver, registra
                        $this->pools[$widgetObj[$widget['idWidget']]->getMiddlewareName()] = new middlewarePools($widgetObj[$widget['idWidget']]->getMiddlewareName());
                    }
                    $this->pools[$widgetObj[$widget['idWidget']]->getMiddlewareName()]->addPool($widgetObj[$widget['idWidget']]->getName(), $wPools);
                }
            }
            
            // Verifica se existem pools
            if (count($this->pools)>0)
            {
                // Se existir, percorre cada um e executa a chamada
                foreach ($this->pools as $middleware => $pool)
                {
                    if (!is_array($poolReturn))
                    {
                        $poolReturn = array();
                    }
                    $poolReturn = array_merge($poolReturn, $this->pools[$middleware]->processPool());
                }
            }
            $widgets = array();
            // Agora, percorre novamente, setando os retornos aos widgets correspondentes
            foreach ($this->widgets as $widget)
            {
                if (isset($poolReturn[$widget['idWidget']]))
                {
                    $widgetObj[$widget['idWidget']]->setPools($poolReturn[$widget['idWidget']]);
                }
                $widgets[] = $widgetObj[$widget['idWidget']]->returnWidget($this->params);
            }
            return $widgets;
        }
        return false;
    }
//
// ------------------------------ Manipulação das classes e instâncias dos widgets --------------------------
//    
}
?>