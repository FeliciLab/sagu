<?php
class HandlerRelcliente extends Handler
{
    public function init()
    {
        parent::init();
        $this->manager->trace(__METHOD__);
        
        // Adiciona classes necessárias.
        $this->manager->uses( 'classes/BString.class.php','base');
        $this->manager->uses( 'classes/bBaseDeDados.class.php','base');
        $this->manager->uses( 'classes/bCatalogo.class.php','base');
        $this->manager->uses( 'classes/bForm.class.php','base');
        $this->manager->uses( 'classes/bFormCadastro.class.php','base');
        $this->manager->uses( 'classes/bFormBusca.class.php','base');
        $this->manager->uses( 'classes/bTipo.class.php','base');
        $this->manager->uses( 'classes/bBarraDeFerramentas.class.php','base');
        $this->manager->uses( 'classes/bJavascript.class.php','base');
        $this->manager->uses( 'classes/bInfoColuna.class.php','base');
        $this->manager->uses( 'classes/bBooleano.class.php','base');
        $this->manager->uses( 'classes/bEscolha.class.php','base');
        $this->manager->uses( 'classes/bUtil.class.php','base');
        $this->manager->uses( 'classes/bMainMenu.class.php','base');
        $this->manager->uses( 'classes/bStatusBar.class.php','base');

        // Adiciona biblioteca javascript do módulo Base.
        $this->manager->page->addScript('base.js','base');
        $this->manager->page->addScript('bMainMenu.js','base');
        
        // Inicializa as constantes.
        $this->inicializarConstantes();
    }
    
    /**
     * Inicializa as constantes necessários para o correto funcionamento do módulo. 
     */
    private function inicializarConstantes()
    {
        // Define a base de dados.
        define(DB_NAME, 'relcliente');

        // Constantes para o correto funcionamento do módulo.
        define(DB_TRUE, 't');
        define(DB_FALSE, 'f');
        define(FUNCAO_BUSCAR, 'buscar');
        define(FUNCAO_EDITAR, 'editar');
        define(FUNCAO_INSERIR, 'inserir');
        define(FUNCAO_REMOVER, 'remover');
        define(FUNCAO_EXPLORAR, 'explorar');
        define(MODULO, 'base');

        // Constantes para tamanho de campo.
        define(T_CODIGO, 10);
        define(T_INTEIRO, 10);
        define(T_DESCRICAO, 25);
        define(T_VERTICAL_TEXTO, 4);
        define(T_HORIZONTAL_TEXTO, 50);
    }
}
?>
