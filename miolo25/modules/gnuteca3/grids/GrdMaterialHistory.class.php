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
 * @author Guilherme Soares Soldatelli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Guilherme Soares Soldatelli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 30/05/2011
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdMaterialHistory extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busMaterial;
    public $busMaterialHistory;

    public $operations;
    
    public function __construct($data)
    {
        $this->MIOLO              = MIOLO::getInstance();
        $this->module             = MIOLO::getCurrentModule();
        $this->action             = MIOLO::getCurrentAction();
        $this->busMaterial        = $this->MIOLO->getBusiness($this->module,'BusMaterial');
        $this->busMaterialHistory = $this->MIOLO->getBusiness($this->module,'BusMaterialHistory');

        $this->operations = $this->busMaterialHistory->listChangeTypes();
        $columns = array(
            new MGridColumn(_M('Número de controle', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Revisão', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Operação', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Campo', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Subcampo', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Etiqueta', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Linha atual', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Linha anterior', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Conteúdo anterior', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Conteúdo atual', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Indicador1 anterior', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Indicador1 atual', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Indicador2 anterior', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Indicador2 atual', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Prefixo anterior', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Prefixo atual', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Sufixo anterior', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Sufixo atual', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Separador anterior', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Separador atual', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Data/Hora', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Operador', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true)
        );

        parent::__construct($data, $columns, $this->MIOLO->getCurrentURL(), LISTING_NREGS, 0, 'gridMaterialHistory');

        $this->setIsScrollable();
        
        $this->setRowMethod($this, 'checkValues');
    }


    public function checkValues($i, $row, $actions, $columns)
    {
        //Converte a operação para uma string
    	$columns[2]->control[$i]->value = $this->operations[$columns[2]->control[$i]->value];

        //Verifica os campos que possuem relação com tabelas para concatenar a descrição
        $tag = $columns[3]->control[$i]->value . '.' . $columns[4]->control[$i]->value;
        
        //Conteudo anterior
        if ( strlen($columns[8]->control[$i]->value) > 0 )
        {
            $newContent = $this->busMaterial->optionsTable($tag, $columns[8]->control[$i]->value);

            if ($newContent)
            {
                $columns[8]->control[$i]->value .= ' - ' . $newContent ;
            }
        }
        
        //conteudo atual
        if ( strlen($columns[9]->control[$i]->value) > 0 )
        {
            $newContent = $this->busMaterial->optionsTable($tag, $columns[9]->control[$i]->value);
            if ($newContent)
            {
                $columns[9]->control[$i]->value .= ' - ' . $newContent ;
            }
        }
    }
}
?>
