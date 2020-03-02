/**
 * Script desenvolvido para funcionar em conjunto com o SAGU,
 * por meio da aplicação de gerenciamento de consultas de BD
 * 
 * @since Classe criada em 04/02/2015
 * @author Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
 * 
 */

/**
 * Classe StatusBanco
 * 
 * @type Object
 */
var StatusBanco = {
    
    Props: {
        intervaloAtualizar: 30000
        
    },
    
    Elementos: {
        container: null,
        tempo: null
        
    },
        
    inicializa: function(dados)
    {
        // Limpa o parent, onde serão adicionadas as informações
        this.utilLimpaInnerElemento(this.Elementos.container);
                
        this.atualizaTempo();
                
        // Se há dados
        if( dados.length > 0 )
        {
            this.cria(dados);
            
        }
        else
        {
            var p = document.createElement("p");
            
            p.classList.add("sbMensagemErro");
            p.innerHTML = "Não há nenhuma consulta rodando a mais de 3 minutos";
            
            this.Elementos.container.appendChild(p);
            
        }
                
    },
    
    cria: function(dados)
    {
        try
        {
            if( dados === null )
            {
                throw "Dados não inicializados";

            }

            for( var i = 0; i < dados.length; i++)
            {
                this.Elementos.container.appendChild(this.criaInformacaoQuery(dados[i]));

            }

        }
        catch (e)
        {
            alert(e);

        }


    },

    criaInformacaoQuery: function(dado)
    {
        var div = document.createElement("div"),
            divQuery = document.createElement("div"),
            label = document.createElement("h1"),
            labelQuery = document.createElement("h1"),
            labelTempo = document.createElement("h1"),
            labelUser = document.createElement("h1"),
            query = document.createElement("p"),
            tempo = document.createElement("p"),
            user = document.createElement("p"),
            button = document.createElement("button");


        label.innerHTML = "Processo " + dado.id;
        label.classList.add("sbInfoLabel");
        labelQuery.innerHTML = "Query";
        query.innerHTML = dado.query;
        divQuery.appendChild(query);
        divQuery.classList.add("sbQueryContainer");
        divQuery.classList.add("sbQueryContainerClosed");
        divQuery.addEventListener("click", function()
        {
            this.classList.toggle("sbQueryContainerClosed");
            
        });
        
        labelTempo.innerHTML = "Tempo em execução";
        tempo.innerHTML = dado.tempo + " minutos";
        
        labelUser.innerHTML = "Usuário";
        user.innerHTML = dado.user;
        
        button.id = "botao" + dado.id;
        button.type = "button";
        button.innerHTML = "Cancelar processo";
        button.addEventListener("click", function()
        {
            StatusBanco.deleta(dado.id);
            
        });
        
        div.classList.add("sbInfoContainer");
        div.appendChild(label);
        div.appendChild(labelQuery);
        div.appendChild(divQuery);
        div.appendChild(labelTempo);
        div.appendChild(tempo);
        div.appendChild(labelUser);
        div.appendChild(user);
        div.appendChild(button);

        div.id = dado.id;

        return div;

    },
    
    atualizaTempo: function()
    {
        var tempo = new Date();
                
        tempo = "Última atualização: "
                    + this.utilFormataTempo(tempo.getHours()) + ":"
                    + this.utilFormataTempo(tempo.getMinutes()) + ":"
                    + this.utilFormataTempo(tempo.getSeconds());
        
        if( this.Elementos.tempo.firstChild )
        {
            this.Elementos.tempo.firstChild.innerHTML = tempo;
            
        }
        else
        {
            var p = document.createElement("p");
        
            p.innerHTML = tempo;
            
            this.Elementos.tempo.appendChild(p);
            
        }
                
    },
    
    consulta: function()
    {
        saguDoAjax("atualizaQuerys", "divResultado", true, "");
        
    },
    
    deleta: function(id)
    {
        saguDoAjax("removeQuery", "divResultado", true, "query='" + id + "'");
        
    },
    
    removeInfo: function(id)
    {
        try
        {
            this.Elementos.container.removeChild(document.getElementById(id));
            
            if( !this.Elementos.container.firstChild )
            {
                var p = document.createElement("p");

                p.classList.add("sbMensagemErro");
                p.innerHTML = "Não há nenhuma consulta rodando a mais de 3 minutos";

                this.Elementos.container.appendChild(p);

            }
            
        }
        catch(e)
        {}
        
    },
    
    evtSetup: function()
    {
        StatusBanco.Elementos.container = document.getElementById("divInformacoes");
        StatusBanco.Elementos.tempo = document.getElementById("divTempo");
        
        (function atualizaQuerys()
        {
            StatusBanco.consulta();
            
            setTimeout(atualizaQuerys, StatusBanco.Props.intervaloAtualizar);
                        
        })();
                
    },
    
    utilLimpaInnerElemento: function(elemento)
    {
        while(elemento.firstChild)
        {
            elemento.removeChild(elemento.firstChild);
            
        }
        
    },
    
    utilFormataTempo: function(tempo)
    {
        var length = 2;
        
        tempo = tempo + "";
        
        while( tempo.length < length )
        {
            tempo = "0" + tempo;
            
        }
        
        return tempo;
        
    }
    
};

window.addEventListener("load", StatusBanco.evtSetup);