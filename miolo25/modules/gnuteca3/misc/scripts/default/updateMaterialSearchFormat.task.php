<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Script de sincronização da tabela de pesquisa
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 07/05/2014
 *
 **/

ini_set('max_execution_time', 0);
class updateMaterialSearchFormat extends GTask
{
    /**
     * Método construtor.
     * 
     * @param $MIOLO Instância do MIOLO.
     * @param int $myTaskId Código da tarefa.
     */
    function __construct($MIOLO, $myTaskId)
    {
        parent::__construct($MIOLO, $myTaskId);
    }

    /**
     * Método disparado na execução do agendador de tarefas.
     *
     * @return boolean positivo caso tenha executado com sucesso.
     */
    public function execute()
    {
        
        $busMaterialSearchFormat = $this->MIOLO->getBusiness('gnuteca3', 'BusMaterialSearchFormat');
        $busSearchFormat = $this->MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');
        
        if ( $this->parameters[0] )
        {
            $searchFormat = $busSearchFormat->getSearchFormat($this->parameters[0]);
            
            if ( $searchFormat )
            {
                $busMaterialSearchFormat->updateCacheOfSearchFormat($searchFormat->searchFormatId, $searchFormat->date);
            }
            else    
            {
                return FALSE;
            }
            
        }
        else
        {
            $searchFormat = $busSearchFormat->listSearchFormat();
            
            if ( is_array($searchFormat) )
            {
                foreach ( $searchFormat as $k => $value )
                {
                    $busMaterialSearchFormat->updateCacheOfSearchFormat($value[0], $value[3]);
                }
            }
        }
        
        return true;
    }
}

?>