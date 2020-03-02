<?php

/**
 * <--- Copyright 2005-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Classe que representa a tabela de mensagem de ouvidoria
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 24/09/2012
 *
 */
class rccRespostaOuvidoria extends bTipo
{
    /**
     * Popula objeto através do código da mensagem de ouvidoria. 
     */
    public function popularPorMensagemOuvidoriaId($mensagemDeOuvidoriaId)
    {
        $filtro = new stdClass();
        $filtro->mensagemouvidoriaid = $mensagemDeOuvidoriaId;

        $solicitacaoDeResposta = $this->buscar($filtro);
        
        if ( is_array($solicitacaoDeResposta) )
        {
            $this->definir($solicitacaoDeResposta[0]);
        }
        
    }
}

?>