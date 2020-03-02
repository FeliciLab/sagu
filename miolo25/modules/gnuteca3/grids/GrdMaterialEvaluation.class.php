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
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @since
 * Class created on 16/06/2011
 *
 **/
$MIOLO->uses('classes/controls/GStar.class.php','gnuteca3');
class GrdMaterialEvaluation extends GSearchGrid
{
    protected $busSearchFormat;

    public function __construct($data)
    {
        $module = 'gnuteca3';
        $MIOLO = MIOLO::getInstance();
        $this->busSearchFormat = $MIOLO->getBusiness( $module, 'BusSearchFormat');

        $columns = array(
            new MGridColumn(_M('Código', $module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Número de Controle', $module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Pessoa',  $module), MGrid::ALIGN_LEFT,null, null, true, null, true),
            new MGridColumn(_M('Nome',  $module), MGrid::ALIGN_LEFT,null, null, true, null, true),
            new MGridColumn(_M('Data', $module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Comentário', $module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Avaliação',$module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Informações', $module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
        );

        parent::__construct($data, $columns);

        $args       = array( 'function' => 'update','materialEvaluationId' => '%0%');
        $hrefUpdate = $MIOLO->getActionURL($this->module, $this->action, null, $args);
        $args       = array( 'function' => 'delete','materialEvaluationId' => '%0%');

        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );

        $this->setRowMethod($this, 'checkValues');
    }

    public function checkValues($i, $row, $actions, $columns)
    {
        $controlNumber = $columns[1]->control[$i]->value;

        if ($controlNumber)
        {
            $data = $this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID);
            $columns[7]->control[$i]->setValue($data);
        }

        $evaluation  = $columns[6]->control[$i]->value;
        $gStar = new GStar('evaluation'.$row , $evaluation, true );
        $columns[6]->control[$i]->setValue( $gStar->generate() );
    }

    /**
     * Trata a linha($line) para gerar o texto das colunas posição 1 e 4  corretamente
     * nos relatórios, CSV e PDF.
     * 
     * @param $line
     * @return $line
     * */

    public function reportLine( $line )
    {
        set_time_limit(0);
        $controlNumber = $line[0];

        $line[1] = strip_tags( trim($this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID)));

        if ( $status == 'missing' )
        {
            $stringStatus = _M('Não encontrado', 'gnuteca3');
        }
        else
        {
            $stringStatus = _M('Fora de lugar', 'gnuteca3');
        }
        $line[4] = $stringStatus;


        return $line;
    }

}
?>
