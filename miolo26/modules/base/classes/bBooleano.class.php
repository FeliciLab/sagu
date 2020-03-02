<?php

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
 *  Classe que define os rótulos para os valores booleanos.
 *          
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 22/08/2012
 */
class bBooleano
{
    /**
     * Método público e estático para obter o vetor com os valores Sim e Não.
     * 
     * @return array Vetor com valores de Sim e Não. 
     */
    public static function obterVetorSimNao()
    {
        $modulo = MIOLO::getCurrentModule();
        
        $arraySimNao = array(
            DB_TRUE => _M('Sim', $modulo),
            DB_FALSE => _M('Não', $modulo)
        );
        
        return $arraySimNao;
    }
}

?>