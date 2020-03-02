<?php
class MHtmlArea extends MMultiLineField
{
    public function __construct($name = '', $value = '', $label = '', $size = 20, $rows = 1, $cols = 20, $hint = '',
                         $validator = null)
    {
        parent::__construct($name, $value, $label, $size, $rows, $cols, $hint, $validator);
//        $this->page->addScript('htmlarea/config.js');
//        $this->page->addScript('htmlarea/htmlarea.js');
        $url = $this->manager->getConf("home.url")."/scripts/xinha/";
        $this->page->addJsCode("var _editor_url=\"$url\";\nvar _editor_lang=\"en\";");
        $this->page->addScriptURL($url."/XinhaCore.js");

        $init = "
    xinha_init_{$this->name} = function()
    {
      xinha_plugins_{$this->name} =
      [
       'CharacterMap',
       'ContextMenu',
       'ListType',
       'SpellChecker',
       'Stylist',
       'SuperClean',
       'TableOperations'
      ];
             // THIS BIT OF JAVASCRIPT LOADS THE PLUGINS, NO TOUCHING  :)
             if(!Xinha.loadPlugins(xinha_plugins_{$this->name}, xinha_init_{$this->name})) return;

      xinha_editors_{$this->name} =
      [
        '{$this->name}'
      ];

       xinha_config_{$this->name} = new Xinha.Config();

      xinha_editors_{$this->name} = Xinha.makeEditors(xinha_editors_{$this->name}, xinha_config_{$this->name}, xinha_plugins_{$this->name});

      Xinha.startEditors(xinha_editors_{$this->name});
    }
";            

        $this->page->addJsCode($init);
        $this->formMode = 0;
        $this->manager->getTheme()->setLayout('HtmlArea');
    }

    public function generate()
    {
//        $code = "<script type=\"text/javascript\" defer=\"1\">HTMLArea.replace(\"{$this->id}\");</script>";
        $btnEdit = new MButton($this->name."_edit",'Edit',"xinha_init_{$this->name}()");
        $html = $btnEdit->generate();
        return parent::generate() . $html;
    }
}