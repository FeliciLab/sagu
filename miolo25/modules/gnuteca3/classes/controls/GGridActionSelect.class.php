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
 *
 * @author Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 05/07/2011
 *
 **/
class GGridActionSelect extends MGridActionSelect
{
    public function __construct($grid, $index = 0)
    {
        parent::__construct($grid, 'select', null, null, null, true, $index);
    }

    public function generate()
    {
        $i = $this->grid->currentRow;
        $row = $this->grid->data[$i];

        $primaryKeysTmp = $this->grid->getPrimaryKey(); //seta chaves primárias em uma variável temporária
        
        foreach ( $primaryKeysTmp as $primaryKeyTmp => $index ) //prepara a variavel de cahves primárias definitiva no formato  argumento=valor
        {
            $primaryKeys[] = $primaryKeyTmp . '=' . $row[$index];
        }
        
        $index = implode ($primaryKeys,'|@|'); //junta os argumentos por |@|
        $control = new MCheckBox("select".$this->grid->name."[$i]", $index, '');
        $control->addAttribute('onclick', "miolo.grid.check(this,'".$this->grid->name."[$i]"."');", false);
        
        return $control;
    }
}
?>