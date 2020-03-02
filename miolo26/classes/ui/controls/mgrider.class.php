<?php

/**
 * MSubDetail class
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @since
 * Creation date 2012/09/11
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 * 
 * Based on http://boriscy.github.com/grider/#table1
 * 
 */
$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('m_grider.js');

class MGrider extends MTableRaw
{
    /**
     * controles utilizados pelo componente
     * @var array 
     */
    protected $controls;

    /**
     * Dados utilizados pelo componente
     * @var array 
     */
    protected $data;

    /**
     * Javascript adicional do Botão de adição de item
     * @var string
     */
    protected $addAction;

    /**
     * Javascript adicional do botão de remoção de item
     * 
     * @var string
     */
    protected $delAction;

    /**
     * Define se mostra ou não contagem de linhas
     * 
     * @var boolean
     */
    protected $countRow;

    /**
     * Texto para aparecer na coluna de contagem de linhas
     * @var string
     */
    protected $countRowText;

    /**
     * Se é para mostrar botão de adição
     * @var boolean
     */
    protected $addRow = true;

    /**
     * Se é para mostrar botão de remoção
     * @var boolean
     */
    protected $delRow = true;
    
    
    static $i = 1;

    const STATUS_ADD = 'add';
    const STATUS_EDIT = 'edit';
    const STATUS_REMOVE = 'remove';

    public function __construct($title = '', $controls=NULL, $colTitle = NULL, $name = '', $data = null)
    {
        if ( !$name )
        {
            throw new Exception(_M('Name not informed in new MGrider.', 'miolo'));
        }

        parent::__construct($title, null, $colTitle, $name);

        //configurações padrão
        $this->setCountRow(false);
        $this->setAlternate(false);
        $this->setCountRowText('Nº');
        $this->setClass("mGrider");
        
        // Remover as colunas header da tabela
        foreach($controls as $key => $control)
        {
            $this->setHeadClass($key, 'mGriderHead');
        }
        // Remover a coluna header 'Excluir'
        $this->setHeadClass(count($controls), 'mGriderHead');
        
        $this->setControls($controls);

        if ( $data )
        {
            $this->setData($data);
        }
    }

    /**
     * Define os controles iniciais do componente
     * 
     * @param array $controls 
     */
    public function setControls($controls)
    {
        $this->controls = $controls;
    }

    /**
     * Obtem os controles atuais do componente
     * 
     * @return array 
     */
    public function getControls()
    {
        return $this->controls;
    }

    /**
     * Define se mostra ou não contagem de linhas
     * 
     * @param boolean $countRow 
     */
    public function setCountRow($countRow)
    {
        $this->countRow = $countRow;
    }

    /**
     * Retorna se mostra ou não contagem de linhas
     * 
     * @param boolean $countRow 
     */
    public function getCountRow()
    {
        return $this->countRow;
    }

    /**
     * Define o conteudo do botão de adição de item
     * 
     * @param string $rowText 
     */
    public function setAddAction($javascript)
    {
        $this->addAction = $javascript;
    }

    /**
     * Obtem conteúdo de botão de adição de item
     * 
     * @return string
     */
    public function getAddAction()
    {
        return $this->addAction;
    }

    /**
     * Define o conteudo do botão de remoção de item
     * 
     * @param string $rowText 
     */
    public function setDelAction($javascript)
    {
        $this->delAction = $javascript;
    }

    /**
     * Obtem conteúdo de botão de remoção de item
     * 
     * @return string
     */
    public function getDelAction()
    {
        return $this->delAction;
    }

    /**
     * Texto para aparecer na coluna de contagem
     * 
     * @param string $countRowText 
     */
    public function setCountRowText($countRowText)
    {
        $this->countRowText = $countRowText;
    }

    /**
     * Obtem texto para aparecer na coluna de contagem
     * 
     * @return string $countRowText 
     */
    public function getCountRowText($countRowText)
    {
        return $this->countRowText;
    }

    /**
     * Mostra/esconde botão de adição
     * 
     * @param type $addRow 
     */
    public function setAddRow($addRow)
    {
        $this->addRow = $addRow;
    }

    /**
     * Se botão de adição está ativado
     * 
     * @return boolean
     */
    public function getAddRow()
    {
        return $this->addRow;
    }

    /**
     * Mostra/Esconde botão de remoção
     * @param boolean $delRow 
     */
    public function setDelRow($delRow)
    {
        $this->delRow = $delRow;
    }

    /**
     * Informa se o botão de remoção está ativado
     * 
     * @return boolean
     */
    public function getDelRow()
    {
        return $this->delRow;
    }

    public function generate()
    {
        $controls = $this->controls;

        if ( !$controls )
        {
            throw new Exception(_M("É necessário definir algum controle para o ideal funcionamento do mGrider."));
        }

        //caso não tenha data, cria um simplificado para funcionar corretamente
        if ( !is_array($this->data) || count($this->data) == 0 )
        {
            $this->data[] = new stdClass();
        }

        //caso não exista colunas, cria as padrões baseadas no label dos campos
        if ( !$this->colTitle )
        {
            foreach ( $controls as $line => $control )
            {
                $this->colTitle[] = $control->label ? $control->label : $control->name;
            }
        }

        //passa pelas informações montando campos iniciais
        if ( is_array($controls) )
        {
            $controls[] = $status = new MTextField('status', 'original');
            $status->setClass('griderStatus');

            //guarda os nomes originais
            foreach ( $controls as $line => $control )
            {
                $originalName[] = $control->name;
            }

            $tmpControls = $controls;
            $controls = null;

            //é necessário fazer isso, caso contrário a tableRaw não consegue lidar corretamente com as informações
            $this->data = array_values($this->data);

            foreach ( $this->data as $line => $dataItem )
            {
                //esconde coluna de 
                $this->setCellAttribute($line, count($this->colTitle), 'style', 'display:none;');
                $controls[$line] = $tmpControls;

                //define se é ou não para esconder a linha
                if ( is_array($dataItem) && $dataItem['status'] == self::STATUS_REMOVE
                        || ( is_object($dataItem) && $dataItem->status == self::STATUS_REMOVE ) )
                {
                    $this->setRowAttribute($line, 'style', 'display:none;');
                }

                foreach ( $tmpControls as $item => $control )
                {
                    if ($tmpControls[$item] instanceOf bEscolha )
                    {
                        $innerControls = $tmpControls[$item]->getControls();
                        $innerControls[0]->id = $innerControls[0]->id.'['.$line.']';
                        $innerControls[0]->name = $innerControls[0]->name.'['.$line.']';
                        $innerControls[1]->id = $innerControls[1]->id.'['.$line.']';
                        $innerControls[1]->name = $innerControls[1]->name.'['.$line.']';
                    }
                    
                    //clona controle para funcionar a definição de valores
                    $controls[$line][$item] = clone $tmpControls[$item];
                    $myOriginalName = $originalName[$item];

                    $controls[$line][$item]->name = $this->name . '[' . $line . '][' . $myOriginalName . ']'; //corrige name
                    $controls[$line][$item]->id = $this->name . '[' . $line . '][' . $myOriginalName . ']'; //corrige id
                    
                    $value = '';
                    
                    //caso for objeto, caso so Type
                    if ( ($myOriginalName != null) && (is_object($this->data[$line])) )
                    {
                        $value = $this->data[$line]->$myOriginalName;
                    }
                    else if ( is_array($this->data[$line]) ) //caso for objeto, caso do post
                    {
                        $value = $this->data[$line][$myOriginalName];
                    }

                    //adiciona suporte a checkbox
                    if ( $controls[$line][$item] instanceof MCheckBox )
                    {
                        $checked = isset($value);
                        $controls[$line][$item]->checked = $checked;
                    }
                    else
                    {
                        $controls[$line][$item]->value = $value; //define valor
                    }

                    //define os campos internos como somente leitura caso o grider esteja definido como
                    if ( $this->readonly == true && method_exists($control, 'setReadOnly') )
                    {
                        $controls[$line][$item]->setReadOnly(true);
                    }
                }
            }

            $this->array = $controls;
        }

        //se for somente leitura passa a ser uma tabela normal
        if ( $this->readonly == false )
        {
            if ( $this->delRow )
            {
                $this->colTitle[] = _M('Delete', 'miolo');
            }

            $addImg = $this->manager->getUI()->getImage(NULL, 'button_add.png');
            $delImg = $this->manager->getUI()->getImage(NULL, 'button_cancel.png');
            $showRowCount = $this->countRow == true ? 'true' : 'false';
            $showDelRow = $this->delRow == true ? 'true' : 'false';
            $showAddRow = $this->addRow == true ? 'true' : 'false';

            /**
             * Observação:
             * O código abaixo passa as informação para o componente javascript.
             * Infelizmente não consegui utilizar o miolo para gerar os botões, gerava erro de javascript.
             */
            //monta grider caso não tenha sido montado
            $this->manager->page->onload("if ( !$('#addRow{$this->name}')[0] )
            {
                var {$this->name}Mount = $('#{$this->name}').grider(
                    {
                        countRow: $showRowCount,
                        countRowAdd: true,
                        countRowText: '{$this->countRowText}',
                        addRow: {$showAddRow},
                        delRow: {$showDelRow},
                        addRowText: '<a id=\'addRow{$this->name}\' class=\'addButton\' onclick=\'{$this->addAction}; return false;\'><button class=\'mButton\'><img src=\'$addImg\'/>&nbspAdicionar</button></a>',
                        delRowText: '<td><a onclick=\'{$this->delAction} ; return false;\' class=\'delete\'><button class=\'mGriderButtonExcluir\'><img src=\'$delImg\'/></button></a></td>',
                    }
                )
            };");
        }

        //TODO soma, média formula,

        return parent::generate();
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData($class = 'stdClass')
    {
        $data = MIOLO::_REQUEST($this->name);

        if ( $class && $data )
        {
            foreach ( $data as $line => $itemData )
            {
                $infoObject = new $class;

                foreach ( $itemData as $item => $info )
                {
                    $infoObject->$item = $info;
                }

                $data[$line] = $infoObject;
            }
        }

        return $data;
    }

    /**
     * Retorna uma ação ajax preparada para o mGrider
     * 
     * @param string $event evento ajax a ser chamado
     * @return string
     */
    public function getAjaxAction($event)
    {
        return "miolo.doAjax('$event',this.id,'{$this->manager->page->getFormId()}');";
    }

    /**
     * Separa por partes um id/name composto do mGrider
     * 
     * @param string $args
     * @return stdClass
     */
    public static function explodeName($args)
    {
        $explode = explode('[', $args);

        $name = new stdClass();
        $name->grider = $explode[0];
        $name->index = str_replace(']', '', $explode[1]);
        $name->field = str_replace(']', '', $explode[2]);

        return $name;
    }
}

?>