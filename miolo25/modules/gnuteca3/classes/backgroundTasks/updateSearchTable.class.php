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
 * Tarefa executada em segundo plano para atualizar tabela de pesquisa
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 19/01/2012
 *
 **/

class updateSearchTable extends GBackgroundTask implements GBackgroundTaskTemplate
{
    public function  __construct($args)
    {
        parent::__construct($args);
        $this->setLabel('Atualiza tabela de pesquisa');
    }

    /**
     * Método a ser executado em segundo plano.
     * 
     * @return boolean Positivo caso não tenha problemas na atualização da tabela de pesquisa. 
     */
    public function execute()
    {
        $busUpdateSearch = $this->MIOLO->getBusiness('gnuteca3', 'BusUpdateSearch'); 
        
        // Obtém número de controle
        $controlNumber = $this->args->controlNumber;
        
        // Obtém númer de controle do pai
        $controlNumberFather = $this->args->controlNumberFather;
        
        // Atualiza tabela de pesquisa para número de controle específico.
        $update = $busUpdateSearch->updateSearchForMaterial($controlNumber, $controlNumberFather);
        
        // Testa se a tabela de pesquisa foi atualizada com sucesso.
        if ( $update )
        {
            $this->setMessage(_M('OK - Tabela de pesquisa atualizada com sucesso para o número de controle "@1".', 'gnuteca3', $controlNumber));
        }
        else
        {
            $this->setMessage(_M('Erro - Não foi possível atualizar a tabela de pesquisa para o número de controle "@1".', 'gnuteca3', $controlNumber));
        }
        
        // Atualiza cache de formato de pesquisa.
        $busMaterialSearchFormat = $this->MIOLO->getBusiness('gnuteca3', 'BusMaterialSearchFormat');
        // Apaga o cache do material.
        $busMaterialSearchFormat->deleteAllSearchFormatForControlNumber($controlNumber);
        // Atualiza o cache do material.
        $busMaterialSearchFormat->updateAllSearchFormatOfMaterial($controlNumber);
        
        return $update;
    }
}
?>
