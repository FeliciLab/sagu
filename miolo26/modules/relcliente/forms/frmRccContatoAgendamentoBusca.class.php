<?php

/**
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 07/11/2012
 */
$MIOLO->uses('classes/telaRegistroContato.class.php', 'relcliente');
$MIOLO->uses('classes/telaPessoa.class.php', 'relcliente');
$MIOLO->uses('forms/frmDinamicoBusca.class.php', 'base');
$MIOLO->uses('tipos/buscaDinamica.class.php', 'base');

class frmRccContatoAgendamentoBusca extends bFormBusca
{
    protected $colunas;
    
    public function __construct($parametros, $titulo=NULL)
    {   
        // Obtém as colunas da tabela.
        $this->colunas = buscaDinamica::buscarDadosDasColunas($parametros['modulo'], 'rccContato');
        
        parent::__construct(_M('Agendamento', MIOLO::getCurrentModule()), $parametros);
    }

    /**
     * Método reescrito para definir os campos da busca dinâmica.
     */
    public function definirCampos()
    {
        parent::definirCampos();
        
        $botoes = array();   
        
        $pessoaLabel = new MText('pessoaLabel', _M('Pessoa:'));
        $pessoa      = new bEscolha('personid', 'basperson', 'relcliente', '', null, FALSE, 'personid,name');
        $botoes[]  = new MHContainer('pessoaHC', array($pessoaLabel, $pessoa));
        
        $checkBox[] = new MCheckBox('checkRecebidas', 't', null, false, 'Contatos sem retorno');
        $checkBox[] = new MCheckBox('checkEnviados', 't', null, false, 'Contatos feitos');
        $checkBox[] = new MCheckBox('checkRespondidos', 't', null, false, 'Contatos respondidos');
        $checkBox[] = new MCheckBox('checkTodos', 't', null, false, 'Todos');

        $checkGroup = new MCheckBoxGroup('checkgroup', 'Mostrar Contatos:', $checkBox, $hint, 'vertical', 'solid');
        $checkGroup->addAttribute("style", "padding: 10px");
        
        $group[] = $checkGroup;
        $botoes[] = $div = new MDiv('', $group);  

        $botoes[] = $div = new MDiv('', new MButton('buscaTodos', 'Todos'));
        $div->addStyle('display', 'inline');
        $botoes[] = $div = new MDiv('', new MButton('buscaSemana', 'Semana'));
        $div->addStyle('display', 'inline');
        $botoes[] = $div = new MDiv('', new MButton('buscaHoje', 'Hoje'));
        $div->addStyle('display', 'inline');
        $botoes[] = $div = new MDiv('', new MButton('buscaAtrasados', 'Atrasados'));
        $div->addStyle('display', 'inline');

        $campos = array();
        $campos[] = new MRowContainer('', $botoes);
        
        $this->adicionarFiltros($campos);
        
        $colunas[] = new MGridColumn(_M('Código do Contato', $this->modulo));
        $colunas[] = new MGridColumn(_M('Código da Pessoa', $this->modulo));
        $colunas[] = new MGridColumn(_M('Nome', $this->modulo));
        $colunas[] = new MGridColumn(_M('Tipo de Contato', $this->modulo));
        $colunas[] = new MGridColumn(_M('Data e Hora', $this->modulo));
        
        $this->criarGrid($colunas, TRUE);
        
        // Remove opções do menu de contexto.
        $this->menu->removeItemByLabel(_M('Editar'));
        $this->menu->removeItemByLabel(_M('Remover'));
        $this->menu->removeItemByLabel(_M('Explorar'));

        // Não há método para esta opção do menu, ela vem do bFormBusca e gera o Explorar com base no frmRccContato
        $this->menu->addCustomItem(_M('Informações do Contato'), $this->manager->getUI()->getAjax('bfExplorar:click'), MContextMenu::ICON_VIEW);
        $this->menu->addCustomItem(_M('Confirmar Contato'), $this->manager->getUI()->getAjax('bfRegistrarContato:click'), MContextMenu::ICON_WORKFLOW);
        $this->menu->addCustomItem(_M('Informações da Pessoa'), $this->manager->getUI()->getAjax('bfInfoPessoa:click'), MContextMenu::ICON_VIEW);
        
        //$this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_INSERIR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_EDITAR);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_REMOVER);
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_SALVAR);
    }
    
    
    /**
     * Método reescrito para definir os botões padrões.
     */    
    protected function obterBotoes()
    {
        return NULL;
    }
    
    /**
     * Este método busca os contato agendados do dia em questão até domingo.
     */
    public function buscaSemana_click($args)
    {   
        $this->definirCampos();
        $filtros = $this->getData();
        $date = getDate();        

        if ( is_array($this->colunas) )
        {
              $date = getDate();
              
              if ($date[weekday] == 'Sunday') {
                  $pdomingo = strtotime("+7 day"); 
              }
              
              if ($date[weekday] == 'Monday') {
                  $pdomingo = strtotime("+6 day"); 
              }
              
              if ($date[weekday] == 'Tuesday') {
                  $pdomingo = strtotime("+5 day"); 

              }
              
              if ($date[weekday] == 'Wednesday') {
                  $pdomingo = strtotime("+5 day"); 
                  
              }
              
              if ($date[weekday] == 'Thursday') {
                  $pdomingo = strtotime("+3 day"); 
                 
              }
              
              if ($date[weekday] == 'Friday') {
                  $pdomingo = strtotime("+2 day"); 
                  
              }
              
              if ($date[weekday] == 'Saturday') {
                  $pdomingo = strtotime("+1 day"); 
                  
              }
              
              $domingo = date("d/m/Y G:i:s", $pdomingo);              
              $hoje = date("d/m/Y G:i:s");              
              
              $filtros->datahoraprevista = $hoje;
              $filtros->domingo = $domingo;
              $filtros->semana = 1;
              $filtros->checkRecebidas = $args->checkRecebidas;
              $filtros->checkEnviados = $args->checkEnviados;
              $filtros->checkRespondidos = $args->checkRespondidos;
              $filtros->checkTodos = $args->checkTodos;
              $filtros->personid = $args->personid;
              
              $sqlSemana = $this->tipo->obterConsulta($filtros);

            //$this->grid->setQuery($sqlSemana, $this->modulo);
            $this->grid->setQuery($sqlSemana, $this->modulo);

            
            // Tira a checagem nos checkbox da grid.
            $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
        }
        
        $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);

    }
    
    public function buscaHoje_click($args)
    {      
        $this->definirCampos();
        $filtros =  $this->getData();
        
        if ( is_array($this->colunas) )
        {
              $date = getDate();
              $hoje .= $date[year] . '-';
              
              if (strlen($date[mon]) < 2)
              {
                $hoje .='0'.$date[mon] . '-';
              }
              else
              {
                $hoje .= $date[mon]; 
              }
              
                            
              if (strlen($date[mday]) < 2)
              {
                $hoje .='0'.$date[mday];
              }
              else
              {
                $hoje .= $date[mday]; 
              }
              
              $filtros->datahoraprevista = $hoje;
              $filtros->hoje = 1;
              $filtros->checkRecebidas = $args->checkRecebidas;
              $filtros->checkEnviados = $args->checkEnviados;
              $filtros->checkRespondidos = $args->checkRespondidos;
              $filtros->checkTodos = $args->checkTodos;
              $filtros->personid = $args->personid;

              $sqlSemana = $this->tipo->obterConsulta($filtros);

            //$this->grid->setQuery($sqlSemana, $this->modulo);
            $this->grid->setQuery($sqlSemana, $this->modulo);

            
            // Tira a checagem nos checkbox da grid.
            $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
        }
        
        $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);
    }
    
    public function buscaTodos_click($args)
    {   
        $this->definirCampos();
        $filtros =  $this->getData();

        if ( is_array($this->colunas) )
        {

            $filtros->todos = 1;
            $filtros->checkRecebidas = $args->checkRecebidas;
            $filtros->checkEnviados = $args->checkEnviados;
            $filtros->checkRespondidos = $args->checkRespondidos;
            $filtros->checkTodos = $args->checkTodos;
            $filtros->personid = $args->personid;
              
            $sqlSemana = $this->tipo->obterConsulta($filtros);

            $this->grid->setQuery($sqlSemana, $this->modulo);
//            $this->grid->setQuery($this->tipo->obterConsulta($filtros), $this->modulo);

            
            // Tira a checagem nos checkbox da grid.
            $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
        }
        
        $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);

    }
    
    public function buscaAtrasados_click($args)
    {
        $this->definirCampos();
        $filtros =  $this->getData();

        if ( $filtros->checkEnviados == 't' || $filtros->checkRespondidos == 't' )
        {
            new MMessageWarning(_M('Contatos feitos e respondidos não podem estar atrasados.'));
        }        
        else
        {
            if ( is_array($this->colunas) )
            {
                  $date = getDate();
                  $hoje .= $date[year] . '-';
                  $hoje .= $date[mon] . '-';
                  $hoje .= $date[mday]. ' 23:59:59';


                  $filtros->datahoraprevista = $hoje;
                  $filtros->atrasados = 1;
                  $filtros->checkRecebidas = $args->checkRecebidas;
                  $filtros->checkEnviados = $args->checkEnviados;
                  $filtros->checkRespondidos = $args->checkRespondidos;
                  $filtros->checkTodos = $args->checkTodos;
                  $filtros->personid = $args->personid;

                  $sqlSemana = $this->tipo->obterConsulta($filtros);

                //$this->grid->setQuery($sqlSemana, $this->modulo);
                $this->grid->setQuery($sqlSemana, $this->modulo);


                // Tira a checagem nos checkbox da grid.
                $this->page->onload("mspecialgrid.uncheckAll('bSearchGrid')");
            }

            $this->setResponse(array( $this->grid, $this->menu ), self::GRID_DIV);
        }
    }
    
    public function bfInfoPessoa_click($args)
    {

        $selecionados = $args->selectlabSearchGrid;
        $numSelecionados = count($selecionados);
        
        if ( $numSelecionados > 1 )
        {
            new MMessageWarning(_M('Você deve selecionar apenas um registro.'));
        }
        elseif ( $numSelecionados == 0 )
        {
            new MMessageWarning(_M('Você deve selecionar um registro.'));
        }
        else
        {
            $chave = array_keys($args->selectlabSearchGrid);
            $selecionado = explode("|", $args->selectlabSearchGrid[$chave[0]]);
            $contatoid = substr($selecionado[1], 0, strlen($selecionado) -1);
            $rccContato = bTipo::instanciarTipo('rcccontato', 'relcliente');
            
            $filtros = new stdClass();
            $filtros->contatoid = $contatoid;
            $personid = $rccContato->buscar($filtros, 'pessoa');

            new telaPessoa($personid[0]->pessoa);
            
        }
    }
    
    public function bfRegistrarContato_click($args)
    {
        $chave = array_keys($args->selectlabSearchGrid);
        $selecionado = explode("|", $args->selectlabSearchGrid[$chave[0]]);
        $contato = substr($selecionado[1], 0, strlen($selecionado) -1);
        $tipo = bTipo::instanciarTipo('rccContato');
        $filtros = new stdClass();
        $filtros->contatoid = $contato;
        $personid = $tipo->buscar($filtros); 
        
        new telaRegistroContato($personid[0]->pessoa);
         
    }

    /**
    * Método para registar o contato na tabela rcccontato
    * Este método é chamado na popup telaRegistroContato
    */
    public function registraContatoEmail($personId, $mensagem)
    {
        $MIOLO = MIOLO::getInstance();
        
        $contato = new stdClass();
        $contato->pessoa = $personId;
        $contato->mensagem = strip_tags($mensagem);
        $contato->datahoradocontato = date('d/m/y H:m:s');
        // origemdecontatoid HARDCODE?
        $contato->origemdecontatoid = 3;
        // assuntodecontato HARDCODE?
        $contato->assuntodecontato = 4;
        // tipodecontatoid HARDCODE?
        $contato->tipodecontatoid = 5;
        $contato->operador = $MIOLO->getLogin()->id;
        
        $tipoContato = new bTipo('rcccontato');
        $tipoContato->definir($contato);
        
        return $tipoContato->inserir();
    }
    
    public function registraContato($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
         
        $valido = true;
        if ( !$args->origem )
        {
            $valido = false;
            new MMessage(_M('Selecione o campo \'Origem\'.'), MMessage::TYPE_WARNING, true, 'divRegistroContatoAlert');
        }
        else
        {
            $contato = new stdClass();
            
            //separa contatoid para registro do rcccontatocontatoid na tabela rccContato
            $chave = array_keys($args->selectlabSearchGrid);
            $selecionado = explode("|", $args->selectlabSearchGrid[$chave[0]]);
            $contato->rcccontatocontatoid = substr($selecionado[1], 0, strlen($selecionado) -1);        
                   
            $contato->pessoa = $args->personid;
            $contato->datahoradocontato = date('d/m/Y H:m:s');
            $contato->origemdecontatoid = $args->origem;
            // assuntodecontato HARDCODE?
            $contato->assuntodecontato = 4;
            // tipodecontatoid HARDCODE?
            $contato->tipodecontatoid = 5;
            $contato->operador = $MIOLO->getLogin()->id;

            if ( MUtil::getBooleanValue($args->agendar) )
            {
                if ( strlen($args->orientacao) > 0 && strlen($args->datahoraprevista) > 0 )
                {
                    $contato->orientacao = $args->orientacao;
                    $contato->datahoraprevista = $args->datahoraprevista;
                }
                else
                {
                    $valido = false;
                    new MMessage(_M('Os campos \'Data\' e \'Orientação\' são obrigatórios.'), MMessage::TYPE_WARNING, true, 'divRegistroContatoAlert');
                }
            }
            else
            {
                if ( strlen($args->mensagem) > 0 )
                {
                    $contato->mensagem = $args->mensagem;
                }
                else
                {
                    $valido = false;
                    new MMessage(_M('O campo \'Mensagem\' é obrigatório.'), MMessage::TYPE_WARNING, true, 'divRegistroContatoAlert');
                }
            }

            if ( $valido )
            {
                $rccContato = bTipo::instanciarTipo('rcccontato');
                $rccContato->definir($contato); 

                if ( $rccContato->inserir() )
                {
                    new MMessageSuccess(_M('Contato registrado com sucesso.'));                    
                }
                else
                {
                    new MMessageError(_M('Houve um erro ao registrar o contato.'));
                }
            }
            
            MDialog::close('popupRegistroContato');
        }
    }
    
    /**
     * Ação AJAX do evento change da checkbox 'Agendar' do diálogo de registro de contato.
     * 
     * @param type $args
     */
    public function agendarClick($args)
    {
        if ( MUtil::getBooleanValue($args->agendar) )
        {
            $dataPrevista = date("d/m/Y H:m:s");
            $campo['data'] = new MTimestampField('datahoraprevista', $dataPrevista, 'Data');
            $this->setResponse(new MFormContainer('dataDiv', $campo), 'dataDiv');
            unset($campo);
            
            $this->setResponse(null, 'respostaDiv');
            
            $campo['orientacao'] = new MMUltiLineField('orientacao', null, _M('Orientação'), NULL, T_VERTICAL_TEXTO, T_HORIZONTAL_TEXTO);
            $this->setResponse(new MFormContainer('orientacaoDiv', $campo), 'orientacaoDiv');
        }
        else
        {
            $this->setResponse(null, 'dataDiv');
            $this->setResponse(null, 'orientacaoDiv');
            
            $campo[] = new MMUltiLineField('mensagem', $resposta, _M('Mensagem'), NULL, T_VERTICAL_TEXTO, T_HORIZONTAL_TEXTO);
            $this->setResponse(new MFormContainer('respostaDiv', $campo), 'respostaDiv');
        }
    }
    
     /**
     * Método reescrito para obter a pessoa que está logada.
     * 
     * @return stdClass Objeto com o código do usuário logado.
     */
    public function getData()
    {
        $MIOLO = MIOLO::getInstance();
        $dados = $this->getAjaxData();        
        
        // Percorre os valores do FormData ajustando os valores cujo indice termine com '_'.
        foreach ( $dados as $indice => $dado )
        {
            if ( substr($indice, strlen($indice) -1, 1) == '_' )
            {
                // Retira o "-" no final do indice.
                $novoIndice = substr($indice, 0, strlen($indice) -1);
                $dados->$novoIndice = $dado;
            }
        }
        
        // Suporte ao componente MSubDetail.
        foreach ( $this->fields as $campo )
        {
            if ( $campo instanceof MSubDetail )
            {
                $idSubDetail = $campo->getName();
                $dados->$idSubDetail = MSubDetail::getData($idSubDetail);
            }
            
            if ( $campo instanceof MCheckBox )
            {
                $dados->{$campo->name} = $dados->{$campo->name} == DB_TRUE ? DB_TRUE : DB_FALSE;
            }
        }
        $dados->operador =$MIOLO->getLogin()->id;
        $dados->admin = $MIOLO->getLogin()->isAdmin;
        
        
        return $dados;
    }
}

?>