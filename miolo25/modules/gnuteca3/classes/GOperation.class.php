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
 * Class GMessages extend GnutecaBussines that extends the default MBussines,
 * including default database configuration and some usefull functions.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 04/12/2008
 *
 **/
class GOperation extends GMessages
{

	public function __construct()
	{
		$this->MIOLO  = MIOLO::getInstance();
		$this->module = MIOLO::getCurrentModule();
        $this->busLocationForMaterialMovement = $this->MIOLO->getBusiness($this->module, 'BusLocationForMaterialMovement');
		parent::__construct();
	}


    /**
     * Define the Library Unit of this operation (and verify if exists)
     *
     * @param integer $libraryUnitId the id of the library unit
     * @return boolean if is seted or not
     */
    public function setLibraryUnit($libraryUnitId)
    {
        $busLibraryUnit = $this->MIOLO->getBusiness( 'gnuteca3', 'BusLibraryUnit');
        
    	//Verifica se a unidade realmente existe 
        $libraryUnit = $busLibraryUnit->getLibraryUnit($libraryUnitId,true);
        
        if ($libraryUnit->libraryUnitId)
        {
            $this->libraryUnitId            = $libraryUnitId;
            $this->libraryUnit              = $libraryUnit;
            $_SESSION['libraryUnit2']       = $libraryUnit;
            $_SESSION['libraryUnitId2']     = $libraryUnitId;
            return true;
        }
        else
        {
            return false;
        }
    }


    public function getLibraryUnit($object=FALSE)
    {
        if ( !$object)
        {
            $libraryUnit = $this->libraryUnitId;
            if ( !$libraryUnit)
            {
                $libraryUnit = $_SESSION['libraryUnitId2'];
            }
        }
        else
        {
            $libraryUnit = $this->libraryUnit;
            
            if (!$libraryUnit)
            {
                $libraryUnit = $_SESSION['libraryUnit2']; //não esta funcionando da erro no miolo25
            }
        }
        return $libraryUnit;
    }


    /**
     * Define o local da devolução. A função verificará se o id passado existe.
     *
     * @param $locationForMaterialMovementId o id do local a ser setado
     *
     */
    public function setLocation($locationForMaterialMovementId)
    {
    	//verifica se o local existe
        $location = $this->busLocationForMaterialMovement->getLocationForMaterialMovement($locationForMaterialMovementId, true);
        
        if ($location->locationForMaterialMovementId)
        {
            $this->locationForMaterialMovementId        = $locationForMaterialMovementId;
            $this->locationForMaterialMovement          = $location;
            $_SESSION['locationForMaterialMovementId']  = $locationForMaterialMovementId;
            $_SESSION['locationForMaterialMovement']    = $location;
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * Return the seted location for material movement
     *
     * @param boolean $object if is to return object or not
     * @return the required data
     */
    public function getLocation($object = FALSE)
    {
        if ( !$object)
        {
            $location =  $this->locationForMaterialMovementId;
            if ( !$location )
            {
                $location = $_SESSION['locationForMaterialMovementId'];
            }
        }
        else
        {
            $location =  $this->locationForMaterialMovement;
            if ( !$location )
            {
                $location = $_SESSION['locationForMaterialMovement'];
            }
        }
        return $location;
    }

    public function setOperator($operator)
    {
        $this->operator = $operator;
        return true;
    }


    public function getOperator()
    {
    	return GOperator::getOperatorId();
    }


    protected function _setPerson($person)
    {
    	$this->person = $person;
    	$_SESSION['personTemp'] = $person;
    }


    /**
     * Return the seted person
     *
     * @return busPerson object with extra data
     */
    public function getPerson()
    {
        $person = $this->person;
        if ( !$person->personId)
        {
            $person = $_SESSION['personTemp'];
            $this->person = $person;
        }
        return $person;
    }

    /**
     * Clear all data that refers to a person in this class
     *
     */
    public function unsetPerson()
    {
        unset($this->person);
        unset($_SESSION['personTemp']);
    }


    /**
     * Add an exemplary to item list
     *
     * @param integer $itemNumber the itemNumber of exemplar
     * @param string $statusDescription the description of actual status of the exemplary
     */
    protected function _addItemNumber($exemplary, $sessionItem = 'items')
    {
        $items = $this->getItems($sessionItem);

        //procura verificando se ja existe na lista
        if ($items && is_array($items))
        {
            foreach ( $items as $line => $info)
            {
                if ( $info->itemNumber == $exemplary->itemNumber )
                {
                	$this->addError( _M('Item já está na lista', $this->module ) );
                    return false;
                }
            }
        }

        //se tiver itemNumber adiciona no item referido
        if ( $exemplary->itemNumber )
        {
            $_SESSION[$sessionItem][$exemplary->itemNumber] = $exemplary;
        }
        else
        {
        	$_SESSION[$sessionItem][]        = $exemplary;
        }
        return true;
    }



    public function deleteItemNumber( $itemNumber, $sessionItem = 'items')
    {
        $items = $this->getItems($sessionItem);

        if (is_array($items))
        {
            foreach ($items as $line => $info)
            {
                if ($info->itemNumber != $itemNumber )
                {
                     $temp[$line] = $info;
                }
            }
        }
        unset($_SESSION[$sessionItem]);
        $_SESSION[$sessionItem] = $temp;
    }


    /**
     * Return a specific item number from items list
     *
     * @param string $itemNumber
     * @param string $sessionItem
     */
    public function getExemplary( $itemNumber, $sessionItem = 'items')
    {
        //TODO
    }

    /**
     *
     * Return an array of objects  with all item (exemplary) informatin.
     *
     * @return array an array of objects  with all item (exemplary) informatin.
     */
    public function getItems( $sessionItem = 'items' )
    {
        return $_SESSION[$sessionItem];
    }


    /**
     * Clear the exemplary list
     *
     */
    public function clearItems( $sessionItem = 'items' )
    {
        unset( $_SESSION[$sessionItem] );
    }

    public function clean()
    {
         $this->clearMessages();
    }

}
?>