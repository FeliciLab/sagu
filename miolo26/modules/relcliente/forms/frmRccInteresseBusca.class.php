<?php

$MIOLO->uses('tipos/rccInteresse.class.php', 'relcliente');
class frmRccInteresseBusca extends bFormBusca
{
    
    public function __construct()
    {
        parent::__construct(_M('Interesse'), array('modulo' => 'relcliente', 'funcao' => 'buscar', 'tipo' => 'rccInteresse'));
    }
    
    public function definirCampos()
    {
        parent::definirCampos();
        
        $filtros = array();
    
        $filtros[] = new MTextField('nome', '', _M('Nome'), 60);
        $this->adicionarFiltros($filtros);
        
        $colunas = array();
        $colunas[] = new MGridColumn(_M('Código', $this->modulo), 'center', FALSE, '5%');
        $colunas[] = new MGridColumn(_M('Código da pessoa', $this->modulo), 'center', FALSE, '5%');
        $colunas[] = new MGridColumn(_M('Data', $this->modulo), 'center', FALSE, '5%');
        $colunas[] = new MGridColumn(_M('Nome', $this->modulo), 'left', FALSE, '15%');
        $colunas[] = new MGridColumn(_M('Telefone', $this->modulo), 'center', FALSE, '10%');
        $colunas[] = new MGridColumn(_M('Email', $this->modulo), 'center', FALSE, '15%');
        $colunas[] = new MGridColumn(_M('CPF', $this->modulo), 'center', FALSE, '10%');
        $colunas[] = new MGridColumn(_M('Observação', $this->modulo), 'center', FALSE, '40%');

        $this->criarGrid($colunas);
        
    }
    
}
