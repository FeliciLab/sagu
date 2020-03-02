/**
 * Script desenvolvido para funcionar em conjunto com o Sagu,
 * por meio da aplicação Construtor de relatórios
 * 
 * @since Classe criada em 10/09/2014
 * @author Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
 * 
 */

/**
 * Classe ConstRel
 * Métodos para criação do relatório dinâmico da aplicação Construtor de relatórios do Sagu
 * 
 * @type Class
 */
var ConstRel = {
    
    /**
     * Contém todas as variáveis "globais"
     * 
     * @type Object
     */
    Vars: {
        loaded: false,
        saved: true,
        disableBeforeUnload: false,
        
        parametros: [],
        
        campo: [],
        selectedCampo: [],
        orderSelectedCampo: [],
        countCampo: 0,

        grupo: [],
        selectedGrupo: [],
        orderSelectedGrupo: [],
        countGrupo: 0,

        ordem: [],
        selectedOrdem: [],
        orderSelectedOrdem: [],
        countOrdem: 0,

        previousSelectedIndex: null,
        previousSelectedItem: null,
        isResultadoVisible: true,
        
        ajaxArgs: {
            selectedView: null,
            parametros: null,
            ordemCampo: null,
            ordemGrupo: null,
            ordemOrdem: null,
            countCampo: null,
            countGrupo: null,
            countOrdem: null,
            sentidoOrdem: null,
            filtrosCampo: null,
            parametrosValor: null,
            parametrosValorDefinitivo: null,
            operacoesCampo: null,
            apelidosCampo: null,
            apelidosParametros: null,
            camposEhFiltroUsuario: null,
            validado: null

        },

        Control: {
            operations: {
                numeric: {
                    SUM: "Soma",
                    AVG: "Média",
                    MAX: "Maior",
                    MIN: "Menor",
                    COUNT: "Contar"
                    //DISTINCTCOUNT: "Contar diferentes"

                },
                text: {
                    DISTINCTCOUNT: "Contar diferentes"

                },
                date: {
                    DISTINCTCOUNT: "Contar diferentes"

                }

            },

            fieldTypes: {
                integer: "integer",
                numeric: "numeric",
                bigint: "integer",
                double: "numeric",
                text: "text",
                character: "text",
                date: "date",
                boolean: "boolean"

            },

            input: {
                numeric: ["Apenas números", "[0-9]*"],
                double: ["Apenas números (números com casas decimais separados por '.')", "[0-9\.]*"],
                boolean: ["Apenas t (true) ou f (false)", "(([t]{1})|([f]{1})){1}"],
                date: ["Formato dd/mm/aaaa", "[0-9]{2}\/[0-9]{2}\/[0-9]{4}"],
                text: ["Palavra a ser procurada", "[^&$]*"],
                integer: ["Apenas números", "[0-9]*"]

            },
            
            colunasParametros: {
                "Parâmetro": "Nome do parâmetro",
                "Apelido": "Informe um nome amigável "
                         + "para os parâmetros, pois este irá aparecer "
                         + "(se não estiver com valor definido) na tela de geração do relatório",
                "Valor": "O valor a ser considerado para este parâmetro",
                "Filtro do usuário": "Marque os parâmetros que podem ser filtrados pelo usuário caso o relatório esteja habilitado"
                
            },
            
            colunasCampos: {
                "Habilitar": "Marque os campos que devem ser considerados na consulta",
                "Campo": "Nome do campo",
                "Apelido": "Informe um nome amigável para os campos",
                "Ordem": "Selecione a ordenação dos campos da consulta",
                "Filtro": "Determine um filtro específico para os campos",
                "Filtro do usuário": "Marque os campos que podem ser filtrados pelo usuário caso o relatório esteja habilitado"
                
            },
            
            colunasOrdem: {
                "Considerar": "Marque os itens que desejas considerar na ordenação",
                "Campo": "Nome do campo a ser considerado",
                "Preferência": "Define qual dos campos terá preferência na ordenação",
                "Sentido da ordenação": "Sentido da ordenação"
                
            },
            
            colunasGrupos: {
                "Considerar": "Marque os itens que desejas separar e realizar a contagem",
                "Campo": "Nome do campo a ser considerado",
                "Ordem": "Define qual dos campos terá seus dados agrupados preferencialmente"
                
            }

        }
        
    },
    
    /**
     * Contém a referência dos containers
     * 
     * @see Referência disponível após o método initElements() ser chamado
     * @type Object
     */
    Elements: {
        divParent: null,
        divCampos: null,
        divGrupos: null,
        divOrdem: null,
        divResultado: null,
        rbgTipo: null,
        rbgHabilitado: null
        
        
    },
    
    /**
     * Inicializa o objeto de elementos com as referências dos mesmos
     * 
     */
    initElements: function()
    {
        this.Elements.divParent = document.getElementById("divReportMaker");
        this.Elements.divParametros = document.getElementById("divRMParametros");
        this.Elements.divCampos = document.getElementById("divRMCampos");
        this.Elements.divGrupos = document.getElementById("divRMGrupos");
        this.Elements.divOrdem = document.getElementById("divRMOrdem");
        this.Elements.divResultado = document.getElementById("divResultado");
        this.Elements.rbgTipo = document.querySelectorAll("input[name='rbgModoRelatorio']");
        this.Elements.rbgHabilitado = document.querySelectorAll("input[name='rbgHabilitado']");
        
    },
    
    
    /**
     * Faz o setup da aplicação
     * 
     * @param {Object} data Campos da view com suas informações
     * @param {Object} savedData Informações salvas, ao ser informado, as mesmas
     * serão carregadas
     */
    setup: function(data, savedData)
    {
        showLoading();
        
        var reloadData = false;
        
        this.reset();
        
        this.Elements.divParent.style.display = "block";
        
        // Mostra/esconde a div com a tabela de parâmetros
        document.getElementById("divRMParametrosParent").style.display = data.parametros.length === 0 ? "none" : "block";
                
        this.Elements.divParametros.appendChild(this.getTabela(this.Vars.Control.colunasParametros));
        this.Elements.divCampos.appendChild(this.getTabela(this.Vars.Control.colunasCampos));
        this.Elements.divOrdem.appendChild(this.getTabela(this.Vars.Control.colunasOrdem));
        this.Elements.divGrupos.appendChild(this.getTabela(this.Vars.Control.colunasGrupos));
        
        this.Vars.ajaxArgs.selectedView = data.nomeView;
        
        for( var i = 0; i < data.parametros.length; i++ )
        {
            var row = this.createParameter(data.parametros[i], i);
            
            this.Elements.divParametros.firstChild.appendChild(row);
            
        }
                
        // Adiciona um listener para eventos de scroll, para fazer com que o cabeçalho acompanhe a rolagem.
        this.Elements.divParametros.addEventListener("scroll", ConstRel.handlerScroll);
        this.Elements.divCampos.addEventListener("scroll", ConstRel.handlerScroll);
        this.Elements.divOrdem.addEventListener("scroll", ConstRel.handlerScroll);
        this.Elements.divGrupos.addEventListener("scroll", ConstRel.handlerScroll);
        
        // Popula a view de campos
        for( var i = 0; i < data.campos.length; i++ )
        {
            var row = this.createField(data.campos[i], i);
                        
            // Tabela é a primeira filha da div
            this.Elements.divCampos.firstChild.appendChild(row);

        }
     
        // Listeners dos botões radio buttons das opções referentes ao habilitar/desabilitar
        for( var i = 0; i < this.Elements.rbgHabilitado.length; i++ )
        {
            this.Elements.rbgHabilitado[i].addEventListener("click", function()
            {
                ConstRel.Vars.ajaxArgs.habilitado = this.value;

            });

        }
     
        // Listeners do botão de radio buttons das opções referentes ao tipo de relatório
        for( var i = 0; i < this.Elements.rbgTipo.length; i++ )
        {
            this.Elements.rbgTipo[i].addEventListener("click", function()
            {
                ConstRel.Vars.ajaxArgs.sintetico = this.value === "1" ? true : false;

            });

        }
        
        // Se há alguma coisa salva
        if( typeof savedData !== "undefined" && savedData !== null )
        {
            this.reload(savedData);
            
            reloadData = true;

        }
        
        if( reloadData )
        {
            // Seleciona a combobox informada pelo usuário previamente
            var combo = document.getElementById("cbRMView");

            // Evitar uma chamada AJAX repetida
            for( var i = 0; i <  combo.options.length; i++ )
            {
                if( combo.options[i].value === data.nomeView )
                {
                    combo.options[i].selected = true;

                    break;

                }

            }
            
        }
        else
        {
            stopShowLoading();
            
            this.doSmoothScroll("divRMOpcoesRelatorio", 200, 40);
            
        }
        
    },
    
    /**
     * Reinicia todos os dados utilizados na aplicação
     * 
     */
    reset: function()
    {
        /* global this, Calendar */
        
        // Inicializa os elementos
        this.initElements();
        
        this.Vars.parametros = [];
        
        // Reset nas variáveis
        this.Vars.ordem = [];
        this.Vars.countOrdem = 0;
        this.Vars.selectedOrdem = [];
        this.Vars.orderSelectedOrdem = [];

        // Limpa a div de ordem
        this.clearElementContent(this.Elements.divOrdem);
        
        // Reset nas variáveis
        this.Vars.grupo = [];
        this.Vars.countGrupo = 0;
        this.Vars.selectedGrupo = [];
        this.Vars.orderSelectedGrupo = [];

        // Limpa a div de grupo
        this.clearElementContent(this.Elements.divGrupos);

        // Reset nas variáveis
        this.Vars.campo = [];
        this.Vars.countCampo = 0;
        this.Vars.selectedCampo = [];
        this.Vars.orderSelectedCampo = [];

        // Limpa a div de grupo
        this.clearElementContent(this.Elements.divParametros);

        // Limpa a div de campo
        this.clearElementContent(this.Elements.divCampos);
        
        this.clearElementContent(this.Elements.divResultado);
        
        this.Vars.ajaxArgs = {
            selectedView: null,
            parametros: null,
            ordemCampo: null,
            ordemGrupo: null,
            ordemOrdem: null,
            countCampo: null,
            countGrupo: null,
            countOrdem: null,
            sentidoOrdem: null,
            filtrosCampo: null,
            parametrosValor: null,
            parametrosValorDefinitivo: null,
            operacoesCampo: null,
            apelidosCampo: null,
            apelidosParametros: null,
            camposEhFiltroUsuario: null,
            validado: null

        };
        
    },
    
    /**
     * Método responsável por recarregar as informações
     *
     * @param {Object} data Dados a serem recarregados
     */
    reload: function(data)
    {
        var eventoClick = new Event("click"); // Usado para dar trigger nos elementos
        var eventoChange = new Event("change"); // Usado para dar trigger nos elementos
        var eventoInput = new Event("input"); // Usado para dar trigger nos elementos
        
        if( data.ordemCampo !== null )
        {
            for( var i = 0; i < data.ordemCampo.length; i++ )
            {
                var info = data.ordemCampo[i];
                
                var cb = document.getElementById("campo_cbCampo_" + info.id);
                 
                cb.checked = true;
                
                cb.dispatchEvent(eventoClick);
                
                var filtro = document.getElementById("campo_filterCampo_" + info.id);
                
                // Verifica os filtros salvos
                if( data.filtrosCampo !== null )
                {
                    if( info.tipo === "date" )
                    {
                        if( data.filtrosCampo[i] !== null )
                        {
                            filtro.value = data.filtrosCampo[i][0];
                            document.getElementById("campo_filterCampo_dt_" + info.id).value = data.filtrosCampo[i][1] !== null ? data.filtrosCampo[i][1] : "";
                        }

                    }
                    else if( info.tipo === "boolean" )
                    {
                        filtro = document.querySelector("input[name='campo_filterCampo_" + info.id + "'][value='" + (data.filtrosCampo[i] !== null ? data.filtrosCampo[i] : 'd') + "']");
                        filtro.checked = true;

                    }
                    else
                    {
                        filtro.value = data.filtrosCampo[i] !== null ? data.filtrosCampo[i] : "";

                    }
                                                            
                }
                
                if( typeof data.camposEhFiltroUsuario !== "undefined" && data.camposEhFiltroUsuario.length > 0 )
                {
                    document.getElementById("campo_cbValorDefinidoCampo_" + info.id).checked = data.camposEhFiltroUsuario[i];
                    
                }
                
                filtro.dispatchEvent(eventoChange);
                filtro.dispatchEvent(eventoInput);
                                
                // Se foi feita alguma operação
                if( data.operacoesCampo !== null )
                {
                    var co = document.getElementById("campo_coOperacoesCampo_" + info.id);
                    
                    if( co !== null )
                    {
                        for( var j = 0; j < co.length; j++ )
                        {
                            if( co.options[j].value === data.operacoesCampo[i] )
                            {
                                co.selectedIndex = j;

                            }

                        }

                        co.dispatchEvent(eventoChange);
                        
                    }

                }
                                
                // Verifica os apelidos
                if( typeof data.apelidosCampo !== "undefined" && data.apelidosCampo.length > 0 )
                {
                    document.getElementById("campo_aliasCampo_" + info.id).value = data.apelidosCampo[i];
                    
                }              
                
            }
            
        }
        
        // Campos de ordem
        if( data.ordemOrdem !== null )
        {
            for( var i = 0; i < data.ordemOrdem.length; i++ )
            {
                var info = data.ordemOrdem[i];
                
                var cb = document.getElementById("ordem_cbCampo_" + info.id);
                
                cb.checked = true;
                
                cb.dispatchEvent(eventoClick);
                
                var sentido = data.sentidoOrdem[i];
                
                if( sentido === "ASC" )
                {
                    var radio = document.getElementById("ordem_rbCampo_" + info.id + "_0");
                    
                    radio.checked = true;
                    radio.dispatchEvent(eventoClick);
                    
                }
                else
                {
                    var radio = document.getElementById("ordem_rbCampo_" + info.id + "_1");
                    
                    radio.checked = true;
                    radio.dispatchEvent(eventoClick);
                    
                }
                
            }
            
        }
        
        if( data.ordemGrupo !== null )
        {
            for( var i = 0; i < data.ordemGrupo.length; i++ )
            {
                var info = data.ordemGrupo[i];
                
                var cb = document.getElementById("grupo_cbCampo_" + info.id);
                
                cb.checked = true;
                
                cb.dispatchEvent(eventoClick);
                
            }
            
        }
        
        if( typeof data.parametros !== "undefined" && data.parametros !== null )
        {
            for( var i = 0; i < data.parametros.length; i++ )
            {
                // Verifica os filtros salvos
                if( data.parametrosValor[i] !== null )
                {
                    if( data.parametros[i].tipo === "boolean" )
                    {
                        var valorRadio = data.parametrosValor[i] === null ? data.parametros[i].valorDefault : data.parametrosValor[i];
                        var filtro = document.querySelector("input[name='parametro_valor_" + i + "'][value='" + valorRadio + "']");
                        filtro.checked = true;
                        
                        continue;
                        
                    }
                                        
                    var filtro = document.getElementById("parametro_valor_" + i);
                    
                    filtro.value = data.parametrosValor[i];
                    
                    filtro.dispatchEvent(eventoChange);
                                        
                }
                
                if( typeof data.parametrosValorDefinitivo !== "undefined" && data.parametrosValorDefinitivo.length > 0 )
                {
                    document.getElementById("parametro_cbValorDefinidoCampo_" + i).checked = data.parametrosValorDefinitivo[i];
                    
                }
                
                // Verifica os apelidos
                if( typeof data.apelidosParametros !== "undefined" && data.apelidosParametros.length > 0 )
                {
                    document.getElementById("parametro_aliasCampo_" + data.parametros[i].id).value = data.apelidosParametros[i];
                    
                }
                
            }
            
        }
        
        ConstRel.Vars.saved = true;
        ConstRel.Vars.loaded = true;
        
        stopShowLoading();
        
    },
    
    /**
     * Cria a tabela e sua estrutura base baseado no array de colunas informado
     * 
     * @param {Array} colunas Colunas a serem criadas
     * 
     * @returns {DOMElement} Tabela
     * 
     */
    getTabela: function(colunas)
    {
        var tabela = document.createElement("table");
        tabela.className = "repmaker-table";
                
        var tr = document.createElement("tr");
        var chaves = this.getKeysObjeto(colunas);
        
        for( var i = 0; i < chaves.length; i++ )
        {
            var th = document.createElement("th");
            var p = document.createElement("p");
            
            p.title = colunas[chaves[i]];
            p.className = "repmaker-hint";
            p.innerHTML = chaves[i];
                        
            th.appendChild(p);
            
            tr.appendChild(th);
            
        }
        
        tr.className = "repmaker-table-header";
        
        tabela.appendChild(tr);
        
        return tabela;
        
    },
    
    /**
     * Pega as keys de um dado objeto
     * 
     * @param {Object} objeto
     * @returns Array Lista com as chaves do objeto
     * 
     */
    getKeysObjeto: function(objeto)
    {
        var retorno = [];
        
        for( var key in objeto )
        {
            retorno[retorno.length] = key;
            
        }
        
        return retorno;
        
    },
    
    /**
     * Cria e retorna um campo para inserção de um valor do parâmetro
     * 
     * @param {Object} info Informação do parametro
     * @param {Integer} id Indentificador do parametro
     */
    createParameter: function(info, id)
    {
        var tipo = this.Vars.Control.fieldTypes[info.tipo.split(" ")[0]];
        var label = document.createElement("label");
        var input = document.createElement("input");
        var row = document.createElement("tr");
        var campos = [];
        var div = document.createElement("div");
        var btData = null;
        var tipoNativo = info.tipo.split(" ")[0] === "double" ? "double" : tipo;
        
        var parameter = {
            id: id,
            nome: info.nome,
            tipo: tipo,
            valorDefault: info.valorDefault
        };
        
        // Nome da coluna
        label.className = "repmaker-inline";
        label.innerHTML = info.nome.length > 32 ? info.nome.substr(0, 31) + "..." : info.nome;
        label.id = "parametro_label_" + id;
              
        if( tipo !== "boolean" )
        {
            // Input (filtros)
            input.placeholder = this.Vars.Control.input[tipoNativo][0];

            input.type = "text";
            input.className = "repmaker-input-text repmaker-inline repmaker-inline-right";
            input.id = input.name = "parametro_valor_" + id;

            input.required = parameter.valorDefault === null;

            input.pattern = this.Vars.Control.input[tipoNativo][1];
            
            var formataData = function(e)
            {
                MIOLO_Apply_Mask({field: input.id, mask: "99/99/9999"}, e);
            };
            input.addEventListener("keyup", formataData);

            if( tipo === "date" )
            {
                btData = this.getCampoData(parameter, "_parametro");

                (function ativaCalendario()
                {
                    // Aguarda o elemento ser adicionado ao DOM
                    if(document.getElementById(input.id))
                    {
                        Calendar.setup({inputField: input.id, ifFormat: "%d/%m/%Y", button: btData.id});

                        input.className = "repmaker-input-text repmaker-campo-data repmaker-inline";

                        ConstRel.corrigePosicaoCalendario(btData);

                    }
                    else
                    {
                        setTimeout(ativaCalendario, 200);

                    }

                })();

            }
            
            div.appendChild(input);
        
            if( btData )
            {
                div.appendChild(btData);

            }
        
        }
        else
        {
            div = this.getRadioButtonsBoolean(parameter, "parametro");
            
        }
        
        /*input.addEventListener("change", function()
        {
            //ConstRel.updateArgs();

        });*/

        campos[campos.length] = label;
        campos[campos.length] = this.getInputApelido(parameter, "parametro");
        campos[campos.length] = div;
        campos[campos.length] = this.getCheckBoxValorDefinido(parameter, "parametro");

        campos.forEach(function(item)
        {
            var td = document.createElement("td");
            
            td.appendChild(item);
            row.appendChild(td);
            
        });

        this.Vars.parametros[this.Vars.parametros.length] = parameter;

        return row;
        
    },
    
    /**
     * Cria e retorna um campo para div de seleção de campos
     * 
     * @param {Object} info Informação do campo
     * @param {Integer} id Identificador do campo
     */
    createField: function(info, id)
    {
        var tipo = this.Vars.Control.fieldTypes[info.tipo.split(" ")[0]];
        
        // Se não for de nem um tipo já previsto, será de texto.
        if( typeof tipo === "undefined" )
        {
            tipo = "text";
            
        }
                
        var field = {
            id: id,
            nome: info.nome,
            tipo: tipo

        };
        
        var row = document.createElement("tr");
        
        var campos = [];
        var checkBox, comboOperacoes = null;
        
        campos[campos.length] = checkBox = this.getCheckBox(field, "campo");
        campos[campos.length] = this.getLabelCampo(field);
        campos[campos.length] = this.getInputApelido(field, "campo");
        campos[campos.length] = this.getComboOrdem(field, "campo");
        campos[campos.length] = this.getFiltroCampo(field);
        campos[campos.length] = this.getCheckBoxValorDefinido(field, "campo");
        campos[campos.length] = comboOperacoes = this.getComboOperacoesCampo(field);
        
        campos.forEach(function(item)
        {
            var td = document.createElement("td");
            
            // Temporário
            if( item === comboOperacoes )
            {
                td.style.display = "none";
                
            }
            
            td.appendChild(item);
            row.appendChild(td);
            
        });
        
        this.Vars.campo[this.Vars.campo.length] = field;

        // Desabilida os campos
        this.changeChildsState(row, true, [checkBox.firstChild.id]);

        return row;
                
    },
    
    /**
     * Cria o combo responsável pela ordenção dos campos
     * 
     * @param {Object} info Informações do campo
     * @param {String} area campo, ordem ou grupo
     * 
     * @returns {DOMElement} Select
     * 
     */
    getComboOrdem: function(info, area)
    {
        var combo = document.createElement("select");
        
        // Combo da ordem
        combo.id = area + "_coCampo_" + info.id;
        combo.className = "m-combo repmaker-inline";
        combo.title = "Selecione a ordem de seleção dos campos";
    
        // Armazena o último item selecionado. Útil para o método changeOrdem.
        combo.addEventListener("focus", function()
        {
            ConstRel.Vars.previousSelectedIndex = this.selectedIndex;
            
        });
    
        var selected = "selected" + area.substr(0, 1).toUpperCase() + area.substr(1); 
    
        combo.addEventListener("change", function()
        {
            ConstRel.changeOrdem(area, this, selected, info);
                                    
        });
        
        return combo;
        
    },
    
    /**
     * Cria a checkbox responsável por fazer a seleção dos campos
     * 
     * @param {Object} info Informações do campo
     * @param {String} area campo, ordem ou grupo
     * 
     * @returns {DOMElement} Checkbox
     * 
     */
    getCheckBox: function(info, area)
    {
        var checkBox = document.createElement("input");
        var div = document.createElement("div");
        
        // CheckBox
        checkBox.type = "checkbox";
        checkBox.id = area + "_cbCampo_" + info.id;
        checkBox.name = area + "_cbCampo_" + info.id;
        checkBox.className = "repmaker-checkbox repmaker-inline";
        
        var count = "count" + area.substr(0, 1).toUpperCase() + area.substr(1);
        var selected = "selected" + area.substr(0, 1).toUpperCase() + area.substr(1);
        
        checkBox.addEventListener("click", function()
        {
            ConstRel.update(area, this, count, selected, info);
                                    
        });
        
        div.appendChild(checkBox);
        div.appendChild(this.getLabelCheckBox(info, area));
        
        return div;
        
    },
    
    /**
     * Cria a label do checkbox
     * 
     * @param {Object} info Informações do campo
     * @param {String} area campo, ordem ou grupo
     * 
     * @returns {DOMElement} Label
     * 
     */
    getLabelCheckBox: function(info, area)
    {
        var label = document.createElement("label");
        
        // Nome da coluna
        label.className = "repmaker-checkboxlabel repmaker-inline";
        label.htmlFor = area + "_cbCampo_" + info.id;
        label.id = area + "_cbCampoLabel_" + info.id;
        label.title = "Marque (clique) se desejas considerar o campo '" + info.nome + "'";
                
        return label;
        
    },
    
    /**
     * Cria a label do campo
     * 
     * @param {Object} info Informações do campo
     * 
     * @returns {DOMElement} Label
     * 
     */
    getLabelCampo: function(info)
    {
        var span = document.createElement("span");
        
        span.innerHTML = info.nome.length > 32 ? info.nome.substr(0, 31) + "..." : info.nome;
     
        return span;
        
    },
    
    /**
     * Cria o campo de inserção do apelido
     * 
     * @param {Object} info Informações do campo/parametro
     * @param {String} area campo ou parametro
     * 
     * @returns {DOMElement} Input
     * 
     */
    getInputApelido: function(info, area)
    {
        var input = document.createElement("input");
        var nome = area === "campo" ? area : "parâmetro";
        
        // Input
        input.id = area + "_aliasCampo_" + info.id;
        input.className = "repmaker-inline repmaker-input-text";
        input.type = "text";
        input.title = "Informe um apelido para o " + nome;
        input.placeholder = "Informe um apelido para o " + nome;
        input.pattern = this.Vars.Control.input["text"][1];
        
        input.addEventListener("change", function()
        {
            //ConstRel.updateArgs();

        });
    
        return input;
        
    },
    
    /**
     * Cria a checkbox responsável pela marcação se o valor do campo deve ser default
     * 
     * @param {Object} info Informações do campo
     * @param {Object} area campo ou parametro
     * 
     * @returns {DOMElement} Div
     * 
     */
    getCheckBoxValorDefinido: function(info, area)
    {
        var checkBox = document.createElement("input");
        var div = document.createElement("div");
        
        // CheckBox
        checkBox.type = "checkbox";
        checkBox.id = area + "_cbValorDefinidoCampo_" + info.id;
        checkBox.name = area + "_cbValorDefinidoCampo_" + info.id;
        checkBox.className = "repmaker-checkbox repmaker-inline";
                
        checkBox.addEventListener("click", function()
        {
            //ConstRel.updateArgs();
                                    
        });
        
        div.appendChild(checkBox);
        div.appendChild(this.getLabelCheckBoxValorDefinido(info, area));
        
        return div;
        
    },
    
    /**
     * Cria a label do checkbox de valor definido
     * 
     * @param {Object} info Informações do campo
     * @param {Object} area campo ou parametro
     * 
     * @returns {DOMElement} Label
     * 
     */
    getLabelCheckBoxValorDefinido: function(info, area)
    {
        var label = document.createElement("label");
        
        // Nome da coluna
        label.className = "repmaker-checkboxlabel repmaker-inline";
        label.htmlFor = area + "_cbValorDefinidoCampo_" + info.id;
        label.id = area + "_cbValorDefinidoCampoLabel_" + info.id;
        label.title = "Marque (clique) se deseja que o usuário possa filtrar por esse campo, se o relatório for habilitado";
                
        return label;
        
    },
        
    /**
     * Cria o filtro dos campo
     * 
     * @param {Object} info Informações do campo
     * 
     * @returns {DOMElement} Div contendo os filtros
     * 
     */
    getFiltroCampo: function(info)
    {
        if( info.tipo === "boolean" )
        {
            return this.getRadioButtonsBoolean(info, "campo");
            
        }
        
        var input = document.createElement("input");
        var div = document.createElement("div");
        var btdt = null;
        
        var placeh = info.tipo === "date" ? "De: " + this.Vars.Control.input[info.tipo][0] : this.Vars.Control.input[info.tipo][0];
        
        input.placeholder = placeh;
        
        input.pattern = this.Vars.Control.input[info.tipo][1];

        if( info.tipo === "date" )
        {
            btdt = this.getCampoData(info, 0);
            
        }

        input.type = "text";
        input.className = "repmaker-input-text repmaker-campo-data repmaker-inline";
        input.id = input.name = "campo_filterCampo_" + info.id;
        
        var mostraCampoAte = function()
        {
            var required = this.value !== "";
            var cmpData = document.getElementById("campo_filterCampo_dt_" + info.id);
            var cmpSelecData = document.querySelector("input#campo_filterCampo_dt_" + info.id + " + span.repmaker-selecionar-data");
                        
            if( info.tipo === "date" )
            {
                if( required )
                {
                    cmpData.style.display = "inline-block";
                    cmpSelecData.style.display = "inline-block";


                }
                else
                {
                    cmpData.style.display = "none";
                    cmpData.value = "";
                    cmpSelecData.style.display = "none";

                }
                
            }
                                                
        };
                
        var formataData = function(e)
        {
            MIOLO_Apply_Mask({field: this.id, mask: "99/99/9999"}, e);
        };
                
        // Para teste se deve ou não o campo de data "até" aparecer
        input.addEventListener("input", mostraCampoAte);
        input.addEventListener("change", mostraCampoAte);
        
        if( info.tipo === "date" )
        {
            input.addEventListener("keyup", formataData);
        }
        
        div.appendChild(input);
        
        if( btdt )
        {
            div.appendChild(btdt);
                        
        }
        
        // Se estamos tratanto uma data, adicionar um outro campo
        if( info.tipo === "date" )
        {
            var btdt2 = this.getCampoData(info, 1);
                        
            var dtInput = document.createElement("input");
            dtInput.type = "text";
            dtInput.id = input.name = "campo_filterCampo_dt_" + info.id;
            dtInput.placeholder = "Até: " + this.Vars.Control.input[info.tipo][0];
            dtInput.pattern = this.Vars.Control.input[info.tipo][1];
            dtInput.style.visibility = "none";
            
            dtInput.addEventListener("change", function()
            {
                // ConstRel.updateArgs();

            });
            
            dtInput.addEventListener("keyup", formataData);
            
            div.appendChild(dtInput);
                        
            if( btdt2 )
            {
                div.appendChild(btdt2);
                
                (function ativaCalendario()
                {
                    if(document.getElementById(input.id))
                    {
                        Calendar.setup({inputField: input.id, ifFormat: "%d/%m/%Y", button: btdt.id});
                        Calendar.setup({inputField: dtInput.id, ifFormat: "%d/%m/%Y", button: btdt2.id});
                        
                        input.className = "repmaker-input-text repmaker-campo-data repmaker-inline";
                        dtInput.className = "repmaker-input-text repmaker-inline";
                        
                        ConstRel.corrigePosicaoCalendario(btdt);
                        ConstRel.corrigePosicaoCalendario(btdt2);
                        
                    }
                    else
                    {
                        setTimeout(ativaCalendario, 200);
                        
                    }
                    
                })();

            }
            
            dtInput.style.display = "none";
            btdt2.style.display = "none";
                                    
        }
                
        div.className = "repmaker-container-filtro";
        
        return div;
        
    },
    
    /**
     * Gera o botão responsável pela chamada do calendário
     *
     * @param {Object} info Informações do campo
     * @param {String} index Adicional ao nome (útil quando há mais de um campo
     * de filtro de data)
     * @returns {DOMElement} Span
     * 
     */
    getCampoData: function(info, index)
    {
        var span = document.createElement("span");
        
        span.id = "selecionaData_filtro_" + info.id + index;
        span.className = "repmaker-selecionar-data";
                
        return span;
        
    },
    
    /**
     * Cria os radio buttons para o filtro do tipo boolean
     * 
     * @param {Object} info Informações do campo
     * @param {Object} area campo ou parametro
     * 
     * @returns {DOMElement} Div
     */
    getRadioButtonsBoolean: function(info, area)
    {
        var inputRb1 = document.createElement("input");
        var inputRb2 = document.createElement("input");
        var inputRb3 = document.createElement("input");
        var spanRb1 = document.createElement("span");
        var spanRb2 = document.createElement("span");
        var spanRb3 = document.createElement("span");
        var div = document.createElement("div");
        var temValorDefault = typeof info.valorDefault !== "undefined" && info.valorDefault !== null;
        var pref = area === "campo" ? area + "_filterCampo_" : area + "_valor_";
        
        inputRb1.type = "radio";
        inputRb1.name = pref + info.id;
        inputRb1.id = pref + info.id + "_0";
        inputRb1.className = "repmaker-inline repmaker-inline-right";
        spanRb1.className = "repmaker-inline repmaker-inline-right";
        
        inputRb1.value = "t";
        inputRb1.checked = !temValorDefault && area === "parametro";
        spanRb1.innerHTML = "Verdadeiro";
        
        inputRb2.type = "radio";
        inputRb2.name = pref + info.id;
        inputRb2.id = pref + info.id + "_1";
        inputRb2.className = "repmaker-inline repmaker-inline-right";
        spanRb2.className = "repmaker-inline repmaker-inline-right";
        
        inputRb2.value = "f";
        spanRb2.innerHTML = "Falso";
                
        inputRb3.type = "radio";
        inputRb3.name = pref + info.id;
        inputRb3.id = pref + info.id + "_d";
        inputRb3.className = "repmaker-inline repmaker-inline-right";
        spanRb3.className = "repmaker-inline repmaker-inline-right";
        
        inputRb3.value = "d";
        spanRb3.innerHTML =  temValorDefault ? "Padrão" : "Ambos";
                
        div.appendChild(inputRb1);
        div.appendChild(spanRb1);
        div.appendChild(inputRb2);
        div.appendChild(spanRb2);
        
        if( temValorDefault || area === "campo" )
        {
            inputRb3.checked = true;
            div.appendChild(inputRb3);
            div.appendChild(spanRb3);
            
        }
                
        return div;
        
    },
    
    /**
     * Corrige a posição do calendário na tela, pois este não considera o scroll
     * do elemento pai
     * 
     * @param {DOMElement} elemento Botão que tem o evento ligado ao calendário
     */
    corrigePosicaoCalendario: function(elemento)
    {
        var clique = elemento.onclick;
        
        elemento.addEventListener("click", function()
        {
            clique();
           
            var calendarios = document.querySelectorAll("div.calendar");
            
            for( var i = 0; i < (calendarios.length - 1); i++ )
            {
                document.body.removeChild(calendarios[i]);
                                
            }
            
            calendarios = document.querySelectorAll("div.calendar");
            
            var div = this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode;
            var divInner = this.parentNode.parentNode.parentNode.parentNode.parentNode;
            var top = div.offsetTop - window.pageYOffset;
                             
            top = top - divInner.offsetTop;
            top = top - divInner.scrollTop;
            top = top + this.offsetTop;
            
            top = top < 0 ? top * (-1) : top;
            
            calendarios[0].style.position = "fixed";
            calendarios[0].style.top = top + "px";
            
        });
        
    },
    
    /**
     * Cria a combo com as operações
     * 
     * @param {Object} info Informações do campo
     * 
     * @returns {DOMElement} Select
     * 
     */
    getComboOperacoesCampo: function(info)
    {
        var comboOperations = document.createElement("select");
        
        // Combo com as operações
        comboOperations.id = "campo_coOperacoesCampo_" + info.id;
        comboOperations.className = "repmaker-inline m-combo repmaker-inline-right";
        comboOperations.title = "Selecione a operação a ser relizada relativa ao campo";
        
        // Desabilitado por enquanto
        comboOperations.style.display = "none";
                
        var firstOption = document.createElement("option");

        firstOption.innerHTML = "-- Operação --";

        comboOperations.appendChild(firstOption);

        for( var operacao in this.Vars.Control.operations[info.tipo] )
        {
            var option = document.createElement("option");

            option.value = operacao;
            option.innerHTML = this.Vars.Control.operations[info.tipo][operacao];

            comboOperations.appendChild(option);


        }

        comboOperations.addEventListener("change", function()
        {
            // Caso seja escolhida uma operação, não é possível agrupar por ela
            var groupElement = document.getElementById("grupo_cbCampo_" + info.id);

            if( this.selectedIndex !== 0 )
            {
                ConstRel.clearElementContent(document.getElementById("grupo_coCampo_" + info.id));
                if( groupElement.checked )
                {
                    groupElement.checked = false;

                    // Aciona o evento que atualiza os níveis
                    var event = new Event("click");
                    groupElement.dispatchEvent(event);

                }

                groupElement.disabled = true;

            }
            else
            {
                groupElement.disabled = false;

            }

            // Muda a operação para salvar
            //ConstRel.updateArgs();

        });
        
        return comboOperations;
        
    },
    
    /**
     * Cria um elemento para a área de ordem
     * 
     * @param {Object} field Objeto com as informações do field
     */
    createOrdem: function(field)
    {
        var row = document.createElement("tr");
        
        var campos = [];
        var checkBox = null;
        
        campos[campos.length] = checkBox = this.getCheckBox(field, "ordem");
        campos[campos.length] = this.getLabelCampo(field);
        campos[campos.length] = this.getComboOrdem(field, "ordem");
        campos[campos.length] = this.getRadioButtonOrdem(field);
        
        campos.forEach(function(item)
        {
            var td = document.createElement("td");
            
            td.appendChild(item);
            row.appendChild(td);
            
        });
        
        row.id = "ordem_campo_" + field.id;
        
        // Desabilida os campos
        this.changeChildsState(row, true, [checkBox.firstChild.id]);
        
        this.Vars.ordem[this.Vars.ordem.length] = field;
                
        this.Elements.divOrdem.firstChild.appendChild(row);
                
    },
    
    /**
     * Cria os radio buttons referentes ao sentido
     * 
     * @param {Object} info Informações do campo
     * 
     * @returns {DOMElement} Div
     */
    getRadioButtonOrdem: function(info)
    {
        var inputRb1 = document.createElement("input");
        var inputRb2 = document.createElement("input");
        var spanRb1 = document.createElement("span");
        var spanRb2 = document.createElement("span");
        var div = document.createElement("div");
        
        // RadioButton 1
        inputRb1.type = "radio";
        inputRb1.name = "ordem_rbCampo_" + info.id;
        inputRb1.id = "ordem_rbCampo_" + info.id + "_0";
        inputRb1.className = "repmaker-inline repmaker-inline-right";
        spanRb1.className = "repmaker-inline repmaker-inline-right";
        
        inputRb1.value = "ASC";
        inputRb1.checked = true;
        spanRb1.innerHTML = "Crescente";
        
        inputRb1.addEventListener("click", function()
        {
            //ConstRel.Vars.ajaxArgs.sentidoOrdem = ConstRel.verificaSentidos();
                        
        });
        
        // RadioButton 2
        inputRb2.type = "radio";
        inputRb2.name = "ordem_rbCampo_" + info.id;
        inputRb2.id = "ordem_rbCampo_" + info.id + "_1";
        inputRb2.className = "repmaker-inline repmaker-inline-right";
        spanRb2.className = "repmaker-inline repmaker-inline-right";
        
        inputRb2.value = "DESC";
        spanRb2.innerHTML = "Decrescente";
        
        inputRb2.addEventListener("click", function()
        {
            //ConstRel.Vars.ajaxArgs.sentidoOrdem = ConstRel.verificaSentidos();
                        
        });
        
        div.appendChild(inputRb1);
        div.appendChild(spanRb1);
        div.appendChild(inputRb2);
        div.appendChild(spanRb2);
        
        return div;
        
    },
    
    /**
     * Cria um elemento para a área de grupo
     * 
     * @param {Object} field Objeto com as informações do field
     */
    createGrupo: function(field)
    {
        var row = document.createElement("tr");
        
        var campos = [];
        var checkBox = null;
        
        campos[campos.length] = checkBox = this.getCheckBox(field, "grupo");
        campos[campos.length] = this.getLabelCampo(field);
        campos[campos.length] = this.getComboOrdem(field, "grupo");
        
        campos.forEach(function(item)
        {
            var td = document.createElement("td");
            
            td.appendChild(item);
            row.appendChild(td);
            
        });
        
        row.id = "grupo_campo_" + field.id;
        
        // Desabilida os campos
        this.changeChildsState(row, true, [checkBox.firstChild.id]);
        
        this.Vars.grupo[this.Vars.grupo.length] = field;
                
        this.Elements.divGrupos.firstChild.appendChild(row);
        
    },
    
    /**
     * Atualiza todos os elementos conforme as ações
     * 
     * @param {String} area campo, grupo ou ordem
     * @param {DOMElement} checkBox Referência do elemento que chamou esta
     * @param {String} count Nome da preferência que contém o contador
     * @param {String} selected Nome da preferência que contém os itens selecionados
     * @param {Object} currentField Field sendo usado
     */
    update: function(area, checkBox, count, selected, currentField)
    {
        if( this.Elements.divResultado.firstChild !== null )
        {
            var span = document.querySelector("#divHandlerResultado > span");
            
            if ( span )
            {
                span.innerHTML = "Prévia (desatualizada) do resultado";
            }

        }
        
        // Se checked adiciona campo ao de ordem e grupos, se ser desmarcado, remover o mesmo
        if( checkBox.checked )
        {
            // Na função, deve ser pego o parent do parent da mesma...
            this.changeChildsState(checkBox.parentNode.parentNode.parentNode, false, [checkBox.id]);
            
            this.Vars[count]++;
            
            // Adiciona itens ao array de itens selecionados se o mesmo for menor que o número de campos.
            if( this.Vars[selected].length < this.Vars[area].length )
            {
                this.Vars[selected][this.Vars[selected].length] = currentField;
                
                this.Vars["orderS" + selected.substr(1)][this.Vars["orderS" + selected.substr(1)].length] = currentField;

            }
            
            // Só cria os campos se for um elemento da parte de campo.
            if( area === "campo")
            {
                this.createGrupo(currentField);
                this.createOrdem(currentField);
                
            }
            
        }
        else
        {
            this.changeChildsState(checkBox.parentNode.parentNode.parentNode, true, [checkBox.id]);
            
            this.Vars[count]--;
            
            var combo = document.getElementById(area + "_coCampo_" + currentField.id);
            this.clearElementContent(combo);
            
            if( area === "campo")
            {
                // Apaga o elemento das opções de grupos
                var groupItem = document.getElementById("grupo_campo_" + currentField.id);
                this.Elements.divGrupos.firstChild.removeChild(groupItem);
                
                var indexGrupo = this.Vars.grupo.indexOf(currentField);
                var indexGrupoSel = this.Vars.selectedGrupo.indexOf(currentField);
                var indexGrupoOrderSel = this.Vars.orderSelectedGrupo.indexOf(currentField);
                
                // Exclui o item da lista de itens selecionados.
                this.Vars.grupo.splice(indexGrupo, 1);
                
                if( indexGrupoSel !== (-1) )
                {
                    this.Vars.countGrupo--;
                    this.Vars.selectedGrupo.splice(indexGrupoSel, 1);
                    this.Vars.orderSelectedGrupo.splice(indexGrupoOrderSel, 1);
                }

                // Apaga os elementos das opções de ordenação
                var ordemItem = document.getElementById("ordem_campo_" + currentField.id);
                this.Elements.divOrdem.firstChild.removeChild(ordemItem);

                var indexOrdem = this.Vars.ordem.indexOf(currentField);
                var indexOrdemSel = this.Vars.selectedOrdem.indexOf(currentField);
                var indexOrdemOrderSel = this.Vars.orderSelectedOrdem.indexOf(currentField);
                
                // Exclui o item da lista de itens selecionados.
                this.Vars.ordem.splice(indexOrdem, 1);

                if( indexOrdemSel !== (-1) )
                {
                    this.Vars.countOrdem--;
                    this.Vars.selectedOrdem.splice(indexOrdemSel, 1);
                    this.Vars.orderSelectedOrdem.splice(indexOrdemOrderSel, 1);
                    
                }

            }
            
            var index = this.Vars[selected].indexOf(currentField);
            // Exclui o item da lista de itens selecionados.
            if( index !== -1)
            {
                this.Vars[selected].splice(index, 1);
                
            }
            
            var indexOrder = this.Vars["orderS" + selected.substr(1)].indexOf(currentField);
            // Exclui o item da lista de itens selecionados.
            this.Vars["orderS" + selected.substr(1)].splice(indexOrder, 1);

        }
        
        // Atualiza todos os combos
        this.updateCombo("campo", "selectedCampo", "countCampo");
        this.updateCombo("ordem", "selectedOrdem", "countOrdem");
        this.updateCombo("grupo", "selectedGrupo", "countGrupo");
        
        //this.updateArgs();
                
    },
    
    getSave: function()
    {
        this.updateArgs();
        
        return this.Vars.ajaxArgs;
        
    },
    
    /**
     * Atualiza as combos
     * 
     * @param {String} area campo, ordem ou grupo
     * @param {String} selected Referência ao nome da variável que contém as
     * informações dos itens selecionados
     * @param {String} count Referência ao nome da variável que contém o contador
     * dos itens selecionados
     */
    updateCombo: function(area, selected, count)
    {
        // Atualiza todos os itens da lista
        for( var i = 0; i < this.Vars[selected].length; i++ )
        {
            var campo = this.Vars[selected][i];

            var combo = document.getElementById(area + "_coCampo_" + campo.id);

            if( combo !== null )
            {
                this.clearElementContent(combo);

                for( var j = 1; j <= this.Vars[count]; j++ )
                {
                    var comboOption = document.createElement("option");

                    comboOption.innerHTML = j;
                    comboOption.value = j;

                    combo.appendChild(comboOption);

                }

                var index = this.Vars["orderS" + selected.substr(1)].indexOf(campo);
                                
                combo.selectedIndex = index !== (-1) ? index : i;
                
            }

        }
        
        //this.updateArgs();
                
    },
    
    /**
     * Muda a ordem entre os comboBox caso algum deles escolha
     * uma posição já usada
     * 
     * @param {String} area campo, grupo, ordem
     * @param {DOMElement} comboBox Referência ao elemento que chamou esta
     * @param {String} selected Nome da preferência que contém os itens selecionados
     * os itens selecionados
     * @param {Object} currentField Field sendo usado
     */
    changeOrdem: function(area, comboBox, selected, currentField)
    {
        var index = comboBox.selectedIndex;
        
        for( var i = 0; i < this.Vars[selected].length; i++ )
        {
            var campo = this.Vars[selected][i];
            
            var combo = document.getElementById(area + "_coCampo_" + campo.id);

            if( combo.selectedIndex === index && combo !== comboBox )
            {
                combo.selectedIndex = this.Vars.previousSelectedIndex;
                
                var indexChange = this.Vars["orderS" + selected.substring(1)].indexOf(campo);
                var indexCurrent = this.Vars["orderS" + selected.substring(1)].indexOf(currentField);
                var aux = this.Vars["orderS" + selected.substring(1)][indexChange];
                
                comboBox.blur();
                combo.blur();
                
                this.Vars["orderS" + selected.substring(1)][indexChange] = this.Vars["orderS" + selected.substring(1)][indexCurrent];
                this.Vars["orderS" + selected.substring(1)][indexCurrent] = aux;
                                
            }
            
            //this.updateArgs();
                        
        }
                        
    },
    
    /**
     * Limpa o conteúdo do elemento informado
     * 
     * @param {DOMElement} elemento Elemento a ter seu conteúdo removido
     */
    clearElementContent: function(elemento)
    {
        while( elemento.firstChild )
        {
            elemento.removeChild(elemento.firstChild);

        }
        
    },
    
    /**
     * Desabilita ou habilita as childs de um elemento
     * 
     * @param {DOMElement} pai Elemento raiz
     * @param {Boolean} disable True para desabilitar, False caso contrário
     * @param {Array} ignoreList Lista de ids os quais devem ser ignorados
     */
    changeChildsState: function(pai, disable, ignoreList)
    {
        var childs = pai.childNodes;
                
        pai.classList.toggle("disabled");
                
        for( var i = 0; i < childs.length; i++ )
        {
            if( ignoreList.indexOf(childs[i].id) === (-1) )
            {
                childs[i].disabled = disable;
                
                if( childs[i].childNodes.length > 0 )
                {
                    ConstRel.changeChildsState(childs[i], disable, ignoreList);

                }
                
            }
                        
        }
        
    },
        
    /**
     * Verifica o sentido referente aos campos de ordem
     * 
     * @returns {Array} Resultado
     */
    verificaSentidos: function()
    {
        var retorno = [];
        
        for( var i = 0; i < ConstRel.Vars.orderSelectedOrdem.length; i++ )
        {
            var id = ConstRel.Vars.orderSelectedOrdem[i].id;
            var rb = document.querySelector("input[name='ordem_rbCampo_" + id + "']:checked");
            
            retorno[retorno.length] = rb.value;
            
        }
        
        return retorno;
        
    },
    
    /**
     * Verifica as operações referentes aos campos
     * 
     * @returns {Array} Resultado
     */
    verificaOperacoes: function()
    {
        var retorno = [];
        
        for( var i = 0; i < ConstRel.Vars.orderSelectedCampo.length; i++ )
        {
            var id = ConstRel.Vars.orderSelectedCampo[i].id;
            var cb = document.getElementById("campo_coOperacoesCampo_" + id);
            
            retorno[retorno.length] = ( (cb.value !== "-- Operação --") && (cb.value !== "") && (cb !== null) ) ? cb.value : null;
                        
        }
        
        return retorno;
        
    },
    
    /**
     * Verifica os filtros referentes aos campos
     * 
     * @returns {Array} Resultado
     */
    verificaFiltros: function()
    {
        var retorno = [];
        
        for( var i = 0; i < ConstRel.Vars.orderSelectedCampo.length; i++ )
        {
            var id = ConstRel.Vars.orderSelectedCampo[i].id,
                tipo = ConstRel.Vars.orderSelectedCampo[i].tipo,
                input = document.getElementById("campo_filterCampo_" + id),
                filtro = "";
            
            if( tipo === "boolean" )
            {
                input = document.querySelector("input[name='campo_filterCampo_" + id + "']:checked");
                                
                // 'd' desconsiderar
                retorno[retorno.length] = input.value !== 'd' ? input.value : null;
                
                // Vai para o próxima "volta" do loop
                continue;
                
            }
            
            if( input.value === "" )
            {
                filtro = null;
                                
            }
            else
            {
                // Se for do tipo data, é necessário
                if( tipo === "date" )
                {
                    var dt = document.getElementById("campo_filterCampo_dt_" + id).value === "" ? null : document.getElementById("campo_filterCampo_dt_" + id).value;
                    
                    filtro = [input.value, dt];

                }
                else
                {
                    filtro = input.value;
                    
                }
                
            }
            
            retorno[retorno.length] = filtro;
            
        }
                
        return retorno;
        
    },
    
    /**
     * Verifica os apelidos dos campos
     * 
     * @returns {Array} Resultado
     */
    verificaApelidos: function()
    {
        var retorno = [];
        
        for( var i = 0; i < ConstRel.Vars.orderSelectedCampo.length; i++ )
        {
            var id = ConstRel.Vars.orderSelectedCampo[i].id,
                tipo = ConstRel.Vars.orderSelectedCampo[i].tipo,
                input = document.getElementById("campo_aliasCampo_" + id),
                alias = null;
            
            if( input.value !== "" )
            {
                alias = input.value;
                
            }
            
            retorno[retorno.length] = alias;
            
        }
                
        return retorno;
        
    },
    
    /**
     * Veririfica e retorna se os campos selecionados tem seu valor definitivo
     *
     * 
     */
    verificaValoresDefinitivos: function()
    {
        var retorno = [];
        
        for( var i = 0; i < ConstRel.Vars.orderSelectedCampo.length; i++ )
        {
            var id = ConstRel.Vars.orderSelectedCampo[i].id,
                input = document.getElementById("campo_cbValorDefinidoCampo_" + id);
            
            retorno[retorno.length] = input.checked;
            
        }
                
        return retorno;
        
    },
    
    /**
     * Verifica os filtros referentes aos campos
     * 
     * @returns {Array} Resultado
     */
    verificaParametros: function()
    {
        var retorno = [];
        
        for( var i = 0; i < ConstRel.Vars.parametros.length; i++ )
        {
            var id = i;
            var input = document.getElementById("parametro_valor_" + id);
            var valorDefault = ConstRel.Vars.parametros[i].valorDefault;
            
            if( ConstRel.Vars.parametros[i].tipo === "boolean" )
            {
                input = document.querySelector("input[name='parametro_valor_" + id + "']:checked");
                                
                // 'd' desconsiderar
                retorno[retorno.length] = input.value !== 'd' ? input.value : valorDefault;
                
                // Vai para o próxima "volta" do loop
                continue;
                
            }
            
            retorno[retorno.length] = input.value !== "" ? input.value : valorDefault;
            
        }
        
        return retorno;
        
    },
    
    /**
     * Veririca e retorna se os parametros selecionados tem seu valor definitivo
     * 
     */
    verificaValoresDefinitivosParametros: function()
    {
        var retorno = [];
        
        for( var i = 0; i < ConstRel.Vars.parametros.length; i++ )
        {
            var id = ConstRel.Vars.parametros[i].id,
                input = document.getElementById("parametro_cbValorDefinidoCampo_" + id);
                            
            retorno[retorno.length] = input.checked;
            
        }
                
        return retorno;
        
    },
    
    /**
     * Verifica os apelidos dos parametros
     * 
     * @returns {Array} Resultado
     */
    verificaApelidosParametros: function()
    {
        var retorno = [];
        
        for( var i = 0; i < ConstRel.Vars.parametros.length; i++ )
        {
            var id = ConstRel.Vars.parametros[i].id,
                input = document.getElementById("parametro_aliasCampo_" + id),
                alias = null;
            
            if( input.value !== "" )
            {
                alias = input.value;
                
            }
            
            retorno[retorno.length] = alias;
            
        }
                
        return retorno;
        
    },
            
    /**
     * Atualiza os argumentos que são enviados via ajax
     * 
     */
    updateArgs: function()
    {
        this.Vars.ajaxArgs.parametros = this.Vars.parametros;
        this.Vars.ajaxArgs.ordemCampo = this.Vars.orderSelectedCampo;
        this.Vars.ajaxArgs.ordemGrupo = this.Vars.orderSelectedGrupo;
        this.Vars.ajaxArgs.ordemOrdem = this.Vars.orderSelectedOrdem;
        this.Vars.ajaxArgs.countCampo = this.Vars.countCampo;
        this.Vars.ajaxArgs.countGrupo = this.Vars.countGrupo;
        this.Vars.ajaxArgs.countOrdem = this.Vars.countOrdem;
        this.Vars.ajaxArgs.sentidoOrdem = this.verificaSentidos();
        this.Vars.ajaxArgs.filtrosCampo = this.verificaFiltros();
        this.Vars.ajaxArgs.parametrosValor = this.verificaParametros();
        this.Vars.ajaxArgs.parametrosValorDefinitivo = this.verificaValoresDefinitivosParametros();
        this.Vars.ajaxArgs.operacoesCampo = this.verificaOperacoes();
        this.Vars.ajaxArgs.apelidosCampo = this.verificaApelidos();
        this.Vars.ajaxArgs.apelidosParametros = this.verificaApelidosParametros();
        this.Vars.ajaxArgs.camposEhFiltroUsuario = this.verificaValoresDefinitivos();
        this.Vars.ajaxArgs.validado = this.valida();
        this.Vars.ajaxArgs.habilitado = document.querySelector("input[name='rbgHabilitado']:checked").value;
        
        // Se foi atualizado, é possível salvar
        this.Vars.saved = false;
                
    },
    
    /**
     * Valida o input dos parametros
     * 
     * @returns {Boolean}
     */
    valida: function()
    {
        var form = document.getElementsByTagName("form")[0];
        var params = true;
        
        for( var i = 0; i < this.Vars.parametros.length; i++ )
        {
            var id = i;
            var input = document.getElementById("parametro_valor_" + id);
            
            if( input !== null )
            {
                if( !input.checkValidity() )
                {
                    params = false;

                    break;

                }
                
            }
            
        }
        
        return (!form.checkValidity || form.checkValidity()) && params;
                
    },
    
    /**
     * Handler do evento de click do resultado
     * 
     */
    handlerHide: function()
    {
        this.classList.toggle("repmaker-div-escondida-label");
        
        var hide = document.querySelector("#" + this.id + " + div");
                
        hide.classList.toggle("repmaker-div-escondida");
        
    },
    
    /**
     * Handler do evento de scroll das divs que contém os campos selecionaveis
     * 
     */
    handlerScroll: function()
    {
        var header = (this.firstChild ? this.firstChild.firstChild : null),
            scrollTop = this.scrollTop,
            nodes = null;

        if( header !== null )
        {
            nodes = header.childNodes;

            for(var i = 0; i < nodes.length; i++)
            {
                // Corrige com o -1
                nodes[i].firstChild.style.top = (scrollTop - 1) + "px";

            }
        
        }
        
    },
    
    /**
     * Handler do evento window.onbeforeunload
     * 
     * @param {Object} e Informações do evento
     */
    handlerBeforeUnload: function(e)
    {
        ConstRel.reset();
        
        var msg = "Dados não salvos. Têm certeza que desejas sair?";
        
        if( !ConstRel.Vars.saved && ConstRel.Vars.countCampo > 0 )
        {
            (e || window.event).returnValue = msg;
            
            return msg;

        }
        
        ConstRel.Vars.saved = false;

    },
    
    /**
     * Faz a animação de scroll
     * 
     * @param {String} to Rolar referente a qual id
     * @param {Integer} duracao Duração em milisegundos
     * @param {Integer} correcao Margem de correção
     */
    doSmoothScroll: function(to, duracao, correcao)
    {
        // Pega a coordenada referente ao top do elemento
        var destino = typeof correcao !== "undefined" ? document.getElementById(to).offsetTop - correcao : document.getElementById(to).offsetTop,
            current = ConstRel.getCurrentYScroll(),
            total = destino - current,
            tempo = duracao/10,
            permili = total/tempo;
            
            var subindo = destino < current;
            
            (function animacao()
            {
                current += permili;

                window.scroll(0, current);

                if( subindo )
                {
                    if( !(current <= destino) )
                    {
                        requestAnimationFrame(animacao);

                    }
                    
                }
                else
                {
                    if( !(current >= destino) )
                    {
                       requestAnimationFrame(animacao);

                    }
                    
                }

            })();
        
    },
    
    /**
     * 
     * @returns {Node.scrollTop|window.document.documentElement.scrollTop|Number|document.documentElement.scrollTop|HTMLDocument.documentElement.scrollTop|Node.documentElement.scrollTop|Window.pageYOffset|document.body.scrollTop|Node.body.scrollTop|window.document.body.scrollTop|HTMLElement.scrollTop|Document.body.scrollTop}Pega o scroll Y
     * 
     */
    getCurrentYScroll: function()
    {
        var yScroll;

        if (window.pageYOffset)
        {
            yScroll = window.pageYOffset;
          
        }
        else if (document.documentElement && document.documentElement.scrollTop)
        {
            yScroll = document.documentElement.scrollTop;
          
        }
        else if (document.body)
        {
            yScroll = document.body.scrollTop;
          
        }
        
        return yScroll;
        
    },
    
    /**
     * Pega a posição referete a página de um dado elemento
     * 
     * @param {DOMElemento} elemento Que deve ter sua posição encontrada
     * @returns {Object} Com as coordenadas
     */
    getPosicao: function(elemento)
    {
        for (var lx=0, ly=0;
             elemento !== null;
             lx += elemento.offsetLeft, ly += elemento.offsetTop, elemento = elemento.offsetParent
                     
        );
        
        return {x: lx,y: ly};
        
    }
    
};
