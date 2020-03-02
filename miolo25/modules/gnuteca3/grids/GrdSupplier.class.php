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
 * Grid
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 01/12/2008
 *
 **/
class GrdSupplier extends GSearchGrid
{
    public function __construct($data)
    {
    	global $MIOLO, $module, $action;

        $columns = array
        (
            new MGridColumn(_M('Código', $module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Nome', $module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Tipos de fornecedor', $module), MGrid::ALIGN_CENTER,  null, null, true, null, true),
            new MGridColumn(_M('Tipos de fornecedor', $module), MGrid::ALIGN_CENTER,  null, null, false, null, true),
            new MGridColumn(_M('Tipos de fornecedor', $module), MGrid::ALIGN_CENTER,  null, null, false, null, true),
        );

        parent::__construct($data, $columns);

        $this->addActionUpdate( $this->MIOLO->getActionURL($this->module, $this->action, null, array( 'function' => 'update','supplierId' => '%0%')  ) );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', array( 'function' => 'delete','supplierId' => '%0%') ) );

        if ( GPerms::checkAccess('gtcKardexControl',null, false) )
        {
            $args = array( 'function'   => 'search', 'supplierId' => '%0%');
            $this->addActionIcon( _M('Mostrar coleções', $module), GUtil::getImageTheme('kardexControl-16x16.png'),  GUtil::getAjax('searchColectionOfSupplier', $args) );

        }

        $this->setRowMethod($this, 'checkValues');
    }


    public function checkValues($i, $row, $actions, $columns)
    {
        unset($values);
        $y=0;
        
        for ($x=2; $x<5; $x++)
        {
            $value  = $columns[$x]->control[$i]->getValue();
            $values[$y] = explode("||", $value);
            $y++;
        }
        $tableData = null;
        $tableData[] = array(_M("Nome da companhia", $this->module) . ":", $values[0][4], $values[1][4], $values[2][4]);
        $tableData[] = array(_M("CNPJ", $this->module) . ":", $values[0][5], $values[1][5], $values[2][5]);
        $tableData[] = array(_M("Logradouro", $this->module) . ":", $values[0][6], $values[1][6], $values[2][6]);
        $tableData[] = array(_M("Bairro", $this->module) . ":", $values[0][7], $values[1][7], $values[2][7]);
        $tableData[] = array(_M("Cidade", $this->module) . ":", $values[0][8], $values[1][8], $values[2][8]);
        $tableData[] = array(_M("CEP", $this->module) . ":", $values[0][9], $values[1][9], $values[2][9]);
        $tableData[] = array(_M("Telefone", $this->module) . ":", $values[0][10], $values[1][10], $values[2][10]);
        $tableData[] = array(_M("Fax", $this->module) . ":", $values[0][11], $values[1][11], $values[2][11]);
        $tableData[] = array(_M("Telefone alternativo",  $this->module) . ":", $values[0][12], $values[1][12], $values[2][12]);
        $tableData[] = array(_M("E-mail", $this->module) . ":", $values[0][13], $values[1][13], $values[2][13]);
        $tableData[] = array(_M("Email alternativo", $this->module) . ":", $values[0][14], $values[1][14], $values[2][14]);
        $tableData[] = array(_M("Contato", $this->module) . ":", $values[0][15], $values[1][15], $values[2][15]);
        $tableData[] = array(_M("Site", $this->module) . ":", $values[0][16], $values[1][16], $values[2][16]);
        $tableData[] = array(_M("Observação", $this->module) . ":", $values[0][17], $values[1][17], $values[2][17]);
        $tableData[] = array(_M("Depósito bancário", $this->module) . ":", $values[0][18], $values[1][18], $values[2][18]);

        for ($k=0; $k<3; $k++)
        {
            $date[$k] = new GDate($values[$k][19]);
        }
        
        $tableData[] = array(_M("Data", $this->module) . ":", $date[0]->getDate(GDate::MASK_DATE_USER), $date[1]->getDate(GDate::MASK_DATE_USER), $date[2]->getDate(GDate::MASK_DATE_USER));

        $colTitle = array
        (
            _M("Campos",      $this->module),
            _M("Compra",      $this->module),
            _M("Permuta",     $this->module),
            _M("Doação",      $this->module),
        );

        $table = new MTableRaw(null, $tableData, $colTitle);
        $table->addAttribute('width', '100%');
        $table->addAttribute('vertical-align', 'top');
        $table->setCellAttribute(0, 0, 'width', '110');
        $table->setAlternate(true);

        $columns[2]->control[$i]->setValue($table->generate());
    }
}
?>