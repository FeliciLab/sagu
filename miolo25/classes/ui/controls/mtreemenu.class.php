<?php

class MTreeMenu extends MControl
{
    public static $order = 0;
    private $nOrder;
    private $template;
    private $action;
    private $target;
    private $items;
    private $jsItems;
    private $arrayItems;
    private $selectEvent = "";

    /**
     * @var boolean Whether to show the tree already expanded on load.
     */
    private $expanded = false;

    /**
     * @var boolean Whether to open the tree on clicking the node.
     */
    private $openOnClick = false;

    /**
     * MTreeMenu constructor.
     *
     * @param string $name Component id.
     * @param integer $template Template number.
     * @param string $action Action.
     * @param string $target Link target.
     */
    public function __construct($name='', $template='', $action='', $target='_blank')
    {
        parent::__construct($name);
        $page = $this->page;
        $this->items = NULL;
        $this->template = $template;
        $page->addDojoRequire("dojo.data.ItemFileReadStore");
        $page->addDojoRequire("dijit.Tree");

        $this->action = $action;
        $this->target = $target;
        $this->selectEvent = '';
        $this->nOrder = MTreeMenu::$order++;
    }

    private function getJsItems($items)
    {
        if ( $items != NULL )
        {
            foreach ( $items as $it )
            {
                $i .= ($i != '' ? ',' : '') . "{description:'{$it[1]}',";
                $i .= "id: '{$this->formId}_{$this->name}_$it[0]'";

                if ( count($this->items[(int) $it[0]]) )
                {
                    $i .= ", children: [" . $this->getJsItems($this->items[(int) $it[0]]) . "]";
                }
                $i .= "}";
            }

            return $i;
        }
    }

    public function setItemsFromArray($array, $key='3', $data='0,1,2')
    {
        $this->arrayItems = array( );
        foreach ( $array as $a )
        {
            $this->arrayItems[$a[0]] = $a;
        }

        $o = new MTreeArray($array, $key, $data);
        $this->items = $o->tree;
        $this->jsItems = "identifier: 'id', label: 'description', items: [" . $this->getJsItems($this->items['root']) . "]";
    }

    public function setItemsFromResult($result, $basename, $key='0', $data='1')
    {
        // for while, only for bi-dimensional results
        // column 0 - key used to group data
        // column 1 - data
        $o = new MTreeArray($result, $key, $data);
        $this->items['root'][] = array( 0, $basename, '' );
        $i = 0;
        foreach ( $o->tree as $key => $tree )
        {
            $this->items[0][] = array( ++$i, $key, '' );
            $j = $i;
            foreach ( $tree as $t )
            {
                $this->items[$j][] = array( ++$i, $t[0], '' );
            }
        }
        $this->jsItems = "identifier: 'id', label: 'description', items: [" . $this->getJsItems($this->items['root']) . "]";
    }

    /**
     * @param boolean $expanded Set if the tree must be expanded on load.
     */
    public function setExpanded($expanded)
    {
        $this->expanded = $expanded;
    }

    /**
     * @return boolean Get if the tree will be expanded on load.
     */
    public function getExpanded()
    {
        return $this->expanded;
    }

    /**
     * @param boolean $openOnClick Set if the tree must be opened on clicking the node.
     */
    public function setOpenOnClick($openOnClick)
    {
        $this->openOnClick = $openOnClick;
    }

    /**
     * @return boolean Get if the tree will be opened on clicking the node.
     */
    public function getOpenOnClick()
    {
        return $this->openOnClick;
    }

    /**
     * Expand the tree to the given path.
     *
     * @param array $path Path to expand. E.g. array('root', 'nodeId', 'subNodeId')
     */
    public function expandPath($path)
    {
        foreach ( $path as $i => $node )
        {
            $path[$i] = "{$this->formId}_{$this->name}_$node";
        }

        $path = "['{$this->formId}_{$this->name}_Root', '" . implode("', '", $path) . "']";

        $js = "{$this->formId}_{$this->name}.attr('path', $path);";

        $this->page->onload($js);
    }

    public function getItems()
    {
        return $this->arrayItems;
    }

    public function setSelectEvent($jsCode)
    {
        $this->selectEvent .= $jsCode;
    }

    public function setEventHandler($eventHandler='')
    {
        $form = $this->page->getFormId();
        if ( $eventHandler == '' )
        {
            $eventHandler = $this->name . '_click';
        }
        $this->selectEvent .= "miolo.doPostBack('{$eventHandler}', item.id,'{$form}');\n";
    }

    public function getIconClass()
    {
        $code = "function {$this->formId}_{$this->name}_getIconClass(item,opened) {\n" .
                "    var cls = (!item || this.model.mayHaveChildren(item)) ? opened ? 'dijitFolderOpened':'dijitFolderClosed' : 'dijitLeaf';\n" .
                "    return cls + '$this->template';\n}\n";
        return $code;
    }

    public function getOnClick()
    {
        $code = "function {$this->formId}_{$this->name}_onClick(item,node) {\n" .
                $this->selectEvent .
                "\n}\n";
        return $code;
    }

    public function generateInner()
    {
        $tree = $this->nOrder;
        $page = $this->page;
        $code = "{$this->formId}_{$this->name}_Store = new dojo.data.ItemFileReadStore({jsId: '{$this->formId}_{$this->name}_Store', data: { $this->jsItems }});\n";
        $code .= "{$this->formId}_{$this->name}_Model = new dijit.tree.ForestStoreModel({jsId: '{$this->formId}_{$this->name}_Model', rootId: '{$this->formId}_{$this->name}_Root', rootLabel: 'BaseControl', store: {$this->formId}_{$this->name}_Store});\n";
        $code .= $this->getIconClass();
        $code .= $this->getOnClick();
        $this->page->addJsCode($code);

        $openOnClick = $this->openOnClick ? 'true' : 'false';
        $autoExpand = $this->expanded ? 'true' : 'false';

        $this->inner = <<<HTML
<div dojoType="dijit.Tree" autoExpand="$autoExpand" jsId="{$this->formId}_{$this->name}" openOnClick="$openOnClick" 
    model="{$this->formId}_{$this->name}_Model" onClick="{$this->formId}_{$this->name}_onClick" showRoot="false"
    getIconClass="{$this->formId}_{$this->name}_getIconClass"></div>
HTML;
    }
}

?>