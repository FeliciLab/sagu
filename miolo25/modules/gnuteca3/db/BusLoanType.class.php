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
 * This file handles the connection and actions for general generalPolicy table
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 04/08/2008
 *
 **/


/**
 * Class to manipulate the generalPolicy table
 **/
class BusinessGnuteca3BusLoanType extends GBusiness
{
    public $loanTypeId;
    public $description;


    public function __construct()
    {
        parent::__construct('gtcLoanType', 'loanTypeId', 'description');
    }


    public function getLoanType($loanTypeId)
    {
        return $this->autoGet($loanTypeId);
    }


    /**
     * List all records from the table handled by the class
     *
     * @param: None
     *
     * @returns (array): Return an array with the entire table
     *
     **/
    public function listLoanType($object = false, $checkAccess = false)
    {
        $this->clear();
        $this->setColumns($this->columns);
        $this->setTables($this->tables);

        if ($checkAccess && (!GPerms::checkAccess('gtcMaterialMovementLoanMomentary', NULL, FALSE)))
        {
            $this->setWhere('loanTypeId != ?', ID_LOANTYPE_MOMENTARY);
        }
        if ($checkAccess && (!GPerms::checkAccess('gtcMaterialMovementLoanForced', NULL, FALSE)))
        {
            $this->setWhere('loanTypeId != ?', ID_LOANTYPE_FORCED);
        }

        $sql = $this->select();
        $rs  = $this->query($sql, $object);
        if (!$object)
        {
        	if (is_array($rs))
        	{
        	   foreach ($rs as $line => $info)
        	   {
                    list($loanTypeId, $description) = $info;
                    if (($loanTypeId == ID_LOANTYPE_FORCED) && (!GPerms::checkAccess('gtcMaterialMovementLoanForced', NULL, FALSE)))
                    {
                    	continue;
                    }
                    $result[$loanTypeId] = $info;
        	   }
        	}
        	$rs = $result;
        }
        return $rs;
    }
}
?>
