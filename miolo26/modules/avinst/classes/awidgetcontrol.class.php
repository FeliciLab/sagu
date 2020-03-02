<?php

$MIOLO->uses('classes/awidget.class.php', 'avinst');
$MIOLO->uses('types/avaWidget.class.php', 'avinst');
$MIOLO->uses('classes/middleware/middleware.class.php', 'avinst');
$MIOLO->uses('types/avaAvaliacaoPerfilWidget.class.php', 'avinst');


class AWidgetControl
{
    const LIST_ARRAY_DESCRITIVO = 1; 
    const LIST_ARRAY_SEQUENCIAL = 2;

    // Conjunto de chamadas para o middleware
    private $middlewares;
    // Código do perfil ao qual deve-se gerar widgets
    private $refPerfil;
    // Código da avaliação ao qual deve-se gerar widgets
    private $refAvaliacao;    
    // Widgets a serem devolvidos    
    private $widgets;
    // Parâmetros padrão para os widgets
    private $params;
    
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
        $this->middlewares = array();
    }

//
// ------------------------------- Área para manipulação de arquivos ---------------------------------------------------
// 

    //
    //  Retorna se existe a classe para o elemento passado via parâmetro
    //
    public static function existsWidgetClass($widget)
    {
        $MIOLO = MIOLO::getInstance();
        $dir = $MIOLO->getAbsolutePath('classes/widgets/', 'avinst');
        $file = $widget.'.class.php';
        if (is_file($dir.$file))
        {
            $MIOLO->uses("classes/widgets/$widget.class.php", 'avinst');
            if (class_exists($widget))
            {
                return true;
            }
        }
        return false;
    }
    
    
    // 
    // Verifica se o widget existe e, caso exista, obtém as propriedades dele
    //
    public function getWidgetClassProperties($widget, $listType = self::LIST_ARRAY_DESCRITIVO)
    {
        $MIOLO = MIOLO::getInstance();
        $dir = $MIOLO->getAbsolutePath('classes/widgets/', 'avinst');
        $file = $widget.'.class.php';
        // Se o arquivo existir
        if (file_exists($dir.$file))
        {
            // Obtém o nome da classe
            if ($widget.'.class.php' == $file)
            {
                // Tenta instanciar
                $MIOLO->uses('classes/widgets/'.$file, 'avinst');
                if (class_exists($widget))
                {
                    $instance = new $widget($params);
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
            $dirWidgets = opendir($dir);
            if ($dirWidgets)
            {
                // Lista todos
                while (($file = readdir($dirWidgets)) !== false)
                {
                    if (stripos($file, '.class.php')>0)
                    {
                        $widgetName = str_replace('.class.php', '', $file);                    
                        $widgets[] = $this->getWidgetClassProperties($widgetName);
                    }
                }
                closedir($dirWidgets);
            }
        }
        return $widgets;
    }

//
// ------------------------------- Fim da manipulação das chamadas com arquivos -----------------------------------------
//

//
// ----------------------------- Manipulação das classes e instâncias dos widgets ---------------------------------------
//

    //
    // Indica se existem widgets carregados
    //
    public function hasWidgets()
    {
        if (is_object($this->widgets[0]))
        {
            return true;
        }
        return false;
    }
    
    //
    // Lista os widgets que estão na base de dados, para o perfil e avaliação em específico
    //
    public function listWidgets()
    {
        $filter = new stdClass();
        $filter->refPerfil = $this->refPerfil;
        $filter->refAvaliacao = $this->refAvaliacao;
        //
        // Alterar para retornar um vetor de avaAvaliacaoPerfilWidget contendo um avawidget que conterá um avaPerfilWidget
        //
        $avaAvaliacaoPerfilWidget = new avaAvaliacaoPerfilWidget($filter);
        $widgetsInfo = $avaAvaliacaoPerfilWidget->getWidgetsByEvaluation($filter->refPerfil);
        return $widgetsInfo;
    }

    //
    // Retorna os widgets
    //
    public function getContent($widget = null)
    {
        if (is_array($this->widgets))
        {
            if (is_object($this->widgets[0]))
            {
                $widgetObj = array();
                // Percorre e instancia widget por widget
                foreach ($this->widgets as $avaliacaoPerfilWidget)
                {
                    $class = $avaliacaoPerfilWidget->perfilWidget->widget->idWidget;

                    if ($this->existsWidgetClass($class))
                    {
                        if (isset($widget))
                        {
                            if ($class == $widget)
                            {
                                $flagAdd = true;
                            }
                            else
                            {
                                $flagAdd = false;
                            }
                        }
                        else
                        {
                            $flagAdd = true;
                        }
                        
                        if ($flagAdd == true)
                        {
                            // Instancia o widget
                            $this->params->largura = $avaliacaoPerfilWidget->largura;
                            $this->params->altura = $avaliacaoPerfilWidget->altura;
                            $widgetObj[$class] = new $class($this->params);
                            
                            if (isset($this->params->profileConstraint) && isset($widgetObj[$class]->profileConstraint))
                            {
                                if ($this->params->profileConstraint == $widgetObj[$class]->profileConstraint)
                                {
                                    $flagAdd = true;
                                }
                                else
                                {
                                    $flagAdd = false;
                                }
                            }
                            
                            if ($flagAdd == true)
                            {
                                $widgetObj[$class]->linha = $avaliacaoPerfilWidget->linha;
                                $widgetObj[$class]->coluna = $avaliacaoPerfilWdiget->coluna;
                                // Verifica se o widget tem chamadas a executar
                                $calls = $widgetObj[$class]->getMiddlewareCalls();

                                if ($calls)
                                {
                                    // Se existir, verifica se já não tem uma chamada registrada
                                    if (!isset($this->middlewares[$widgetObj[$class]->getMiddlewareName()]))
                                    {
                                        // Se não tiver, registra
                                        $this->middlewares[$widgetObj[$class]->getMiddlewareName()] = middleware::newFromClass($widgetObj[$class]->getMiddlewareName());
                                    }
                                    $this->middlewares[$widgetObj[$class]->getMiddlewareName()]->addCalls($widgetObj[$class]->getName(), $calls);
                                }
                            }
                            else
                            {
                                unset($widgetObj[$class]);
                            }
                        }
                    }
                }
                unset($class);
                unset($avaliacaoPerfilWidget);
                // Verifica se existem chamadas registradas pendentes a executar
                if (count($this->middlewares)>0)
                {
                    // Se existir, percorre cada um e executa a chamada
                    foreach ($this->middlewares as $middleware)
                    {
                        if (!is_array($callReturn))
                        {
                            $callReturn = array();
                        }
                        $callReturn = array_merge($callReturn, $middleware->fetchCalls());
                    }
                }
                // Agora, percorre novamente, setando os retornos aos widgets correspondentes
                
                foreach ($this->widgets as $avaliacaoPerfilWidget)
                {
                    $class = $avaliacaoPerfilWidget->perfilWidget->widget->idWidget;
                    
                    if (isset($widgetObj[$class]))
                    {
                        $widgetObj[$class]->setMiddlewareData($callReturn);
                    }
                }
                return $widgetObj;
            }
            return false;
        }
    }
//
// ------------------------------ Manipulação das classes e instâncias dos widgets --------------------------
//
}
?>