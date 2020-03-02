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
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 26/09/2011
 *
 **/

class GrdInventoryCheck extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;
    
    private $busSearchFormat;

    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->comma = '|';
        $this->printCSVTitleLine = TRUE;

        $columns = array(
            new MGridColumn(_M('Número de controle', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Dados',     $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Exemplar',     $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Estado',     $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Mensagem',     $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
        );

        parent::__construct($data, $columns);

        $this->setIsScrollable();
        $this->setRowMethod($this, 'checkValues');
    }
    
    public function checkValues($i, $row, $actions, $columns)
    {
        $controlNumber = $columns[0]->control[$i]->value;
        //obtém formato de pesquisa formatado de acordo com número de controle
        $columns[1]->control[$i]->setValue( $string = $this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID));
    
        $status =  $columns[4]->control[$i]->value;  //obtém status 
        
        if ( $status == 'Não encontrado' )
        {
            $stringStatus = _M($status, 'gnuteca3');
        }
        else
        {
            $stringStatus = _M('Fora de lugar', 'gnuteca3');
        }
        
        //seta a mensagem
        $columns[4]->control[$i]->setValue($stringStatus);
    }   
}
?>
