/**
 * <--- Copyright 2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Base.
 *
 * O Base é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 *  Classe javascript do módulo Base.
 *          
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 31/07/2012
 */

dojo.declare ("base", null,
{

    /**
     * Seta o foco no elemento desejado.
     * 
     * @param: string fieldId Id do elemento em que se deseja definir o foco.
     * @param: boolean now Quando verdadeiro, seta o foco imediatamente, caso contrario, aguarda determinado instante.
     */
    definirFoco : function ( campoId, imediato)
    {
        if ( !campoId )
        {
            return;
        }

        // Obtém o elemento.
        elemento = dojo.byId(campoId);

        // Verifica se elemento existe.
        if ( !elemento )
        {
            return;
        }

        // Obtém a "tagName" do elemento.
        nomeTag = elemento.tagName;

        // Caso não seja um elemento input define um tabindex para poder definir um foco.
        if ( ! ( nomeTag == 'INPUT' || nomeTag == 'SELECT' || nomeTag == 'a') && ! elemento.getAttribute('tabindex'))
        {
            elemento.setAttribute('tabindex',0);
        }

        // Testa se é necessário definir o foco imediatamente.
        if ( imediato )
        {
            // Bloco "try/catch" necessário para manter a compatibilidade com Internet Explorer.
            try
            {
                elemento.focus();
            }
            catch (err)
            {
                console.log('Erro ao definir o foco no elemento '+ campoId);
            }
        }
        else
        {
            // Aguarda 750 milisegundos para definir o foco no elemento desejado.
            setTimeout( 'base.definirFoco( \'' + campoId + '\', true )', 750);
        }
    }
    ,

    /**
     * Mostra/esconde um elemento considerando diversas situações.
     * 
     * @param string fieldId Id do elemento em que se deseja alterar a visualização.
     * @param boolean rotulo Caso verdadeiro, esconde/mostra o rótulo do campo.
     * @param string display [block|nome] Valores possiveis para visualização do elemento.
     */
    definirVisualizacao : function ( campoId, rotulo, display )
    {
        // Obtém o elemento.
        campo =  document.getElementById( campoId );

        if ( campo )
        {
            // Testa se elemento é DIV, caso for, não possui rótulo.
            if ( campo.tagName == 'DIV' )
            {
                rotulo = false;
            }

            // Bloco responsável por mostrar/esconder o rótulo do elemento.
            if ( rotulo )
            {
                // Acessa as DIV's superiores, para esconder o rótulo.
                if ( campo.type == 'checkbox' || campo.type == 'fieldset' || campo.tagName == 'select' )
                {
                    campo = campo.parentNode.parentNode.parentNode;
                }
                else
                {
                    // Para funcionar em casos normais e casos com container.
                    if ( campo.parentNode.className == 'mContainerHorizontal' )
                    {
                        campo = campo.parentNode;
                    }
                    else
                    {
                        campo = campo.parentNode.parentNode;
                    }
                }
            }

            // Esconde/mostra elemento desejado.
            campo.style.display = display;
        }
    }
    ,
    
    /**
     * Mostra/esconde um campo considerando sua situação
     * 
     * @param string divId Id da div que terá a visualização alterada.
     * @param string dibImagemId Id da div que contém imagens e que terá a visualização alterada.
     */
    alterarVisualizacao : function ( divId )
    {
        elemento = document.getElementById(divId);

        if ( elemento.style.display == 'none' )
        {
            elemento.style.display = 'block';

        }
        else
        {
            elemento.style.display = 'none';
        }
    }
    ,

    /**
     * Método para desabilitar o campo desejado.
     * 
     * @param string campoId Id do campo que será habilitado.
     */
    desabilitarCampo: function ( campoId )
    {
        elemento = document.getElementById(campoId); 
        
        if ( elemento) 
        { 
            elemento.disabled = 'true';
        }
    }
    ,
    
    /**
     * Método para habilitar o campo desejado.
     * 
     * @param string campoId Id do campo que será habilitado.
     */
    habilitarCampo : function ( campoId )
    {
        elemento = document.getElementById(campoId); 
        
        if ( elemento) 
        { 
            elemento.disabled = 'false';
        }
    }
    ,
    
    /**
     * Seta o valor no campo desejado.
     * 
     * @param string campoId Id do campo.
     * @param string valor Valor que sera setado no campo desejado.
     */
    definirValor : function ( campoId, valor )
    {
        elemento = dojo.byId( campoId ); 
        
        if (elemento) 
        { 
            elemento.value = valor;
        }
    }
    ,
    
    /**
     * Seta o conteúdo no elemento desejado.
     * 
     * @param string elementoId Id do elemento desejado.
     * @param string conteudo Conteúdo que à ser adicionado no elemento desejado.
     */
    definirConteudo : function ( elementoId, conteudo )
    {
        elemento = dojo.byId( elementoId ); 
        
        if (elemento)
        { 
            elemento.innerHTML = conteudo;
        }
    }
    ,
    
    /**
     * Seta o campo desejado como somente leitura.
     * 
     * @param string campoId Id do campo.
     * @param boolean somenteLeitura Caso positivo, seta o campo como somente leitura.
     */
    definirSomenteLeitura : function ( campoId, somenteLeitura )
    {
        campo = dojo.byId(campoId); 
        
        if (campo) 
        {
            if ( somenteLeitura )
            {
                campo.className = 'mTextField mReadOnly';
            }
            else
            {
                campo.className = 'mTextField';
            }
            
            campo.readOnly = somenteLeitura; 
        }
    }
    ,
    
    /**
     * Checa o campo desejado.
     * 
     * @param string campoId Id do campo.
     */
    checarCampo : function ( campoId, checar )
    {
        campo = dojo.byId(campoId);
        
        if (campo)
        { 
            campo.checked = checar;
        }
    }
       
}
);

base = new base;