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
 * gtcTask business
 *
 * @author Luiz Gilberto Gregory F [luiz@solis.coop.br]
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
 * Class created on 06/08/2009
 *
 **/


class BusinessGnuteca3BusScheduleCycle extends GBusiness
{
    public $scheduleCycleId,    //integer,
           $description,        //varchar,
           $valueType;          //varchar

    public $columns,
           $table       = 'gtcScheduleCycle',
           $pkeys       = 'scheduleCycleId',
           $cols        = 'description, valueType';


    public function __construct()
    {
        $this->columns = "{$this->pkeys}, {$this->cols}";
        parent::__construct($this->table, $this->pkeys, $this->cols);
    }


    public function insertScheduleCycle()
    {
        return $this->autoInsert();
    }


    public function updateScheduleCycle()
    {
        return $this->autoUpdate();
    }


    public function deleteScheduleCycle($taskId)
    {
        return $this->autoDelete($taskId);
    }


    public function getScheduleCycle($taskId)
    {
        $this->clear();
        return $this->autoGet($taskId);
    }


    /*
    public function searchScheduleCycle($object = false)
    {
        $this->clear();
        $filters = array
        (
            'taskId'            => 'equals',
            'description'       => 'ilike',
            'parameters'        => 'ilike',
            'enable'            => 'ilike',
            'scriptName'        => 'ilike',
        );

        return $this->autoSearch($filters, $object);
    }
    */

}
?>
