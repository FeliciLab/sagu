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
 * Class created on 13/09/2012
 *
 */
class rccMensagemOuvidoria2 extends bTipo
{
    public function __construct() 
    {
        parent::__construct('rccmensagemouvidoria');
    }
    
    public function obterConsulta($filtros)
    {            
        $msql = new MSQL(); 

        $msql->setTables($this->tabela . ' A LEFT JOIN rccRespostaOuvidoria B ON A.mensagemouvidoriaid = B.mensagemouvidoriaid');
        $msql->setColumns('A.mensagemouvidoriaid');
        $msql->setColumns('A.nome');
//        $msql->setColumns('nome');
//        $msql->setColumns('nome');
//        $msql->setColumns('nome');

        $argumentos = array();
        
        if ($filtros->cancelados == 1)
        {
            $msql->setWhere('A.estacancelada = TRUE');
        }

        if ($filtros->respondidos == 1)
        {
            $msql->setWhere('B.resposta is not null');
            $msql->setWhereOr('A.mensagem is not null');
        }
        
        if ($filtros->encaminhados == 1)
        {
            $msql->setWhere('B.respostaouvidoriaid is not null');
        }
        
        
        $msql->setOrderBy('nome');
        
        return $msql->select($argumentos);
    }
  
}

?>