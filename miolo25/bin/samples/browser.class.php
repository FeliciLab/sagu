<?php
/**
 * Business class representing browser table
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/03/14
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class Business#ModuleBrowser extends MBusiness
{
    /**
     * @var integer Identifier column
     */
    private $identifier;

    /**
     * @var string Description column
     */
    private $description;

    /**
     * Business constructor
     *
     * @param object $data Object of type stdClass to populate the instance
     */
    public function __construct($data=NULL)
    {
       parent::__construct('#db');

       if ( $data )
       {
           $this->identifier = $data->identifier;
           $this->description = $data->description;
       }
    }

    /**
     * @param integer $identifier Set the identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return integer Get the identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $description Set the description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string Get the description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Insert the register on the table
     *
     * @return boolean Whether the insert was successfull
     */
    public function insert()
    {
        $msql = new MSQL('identifier, description', 'browser');
        $sql = $msql->insert(array( $this->identifier, $this->description ));
        return $this->getDb()->execute($sql);
    }

    /**
     * Update the register on the table
     *
     * @return boolean Whether the update was successfull
     */
    public function update()
    {
        if ( !$this->identifier )
        {
            return false;
        }
        $msql = new MSQL('description', 'browser');
        $msql->setWhere("identifier = '{$this->identifier}'");
        $sql = $msql->update(array( $this->description ));
        return $this->getDb()->execute($sql);
    }

    /**
     * Delete the register of the table
     *
     * @return boolean Whether the delete was successfull
     */
    public function delete()
    {
        if ( !$this->identifier )
        {
            return false;
        }
        $msql = new MSQL(NULL, 'browser');
        $msql->setWhere("identifier = '{$this->identifier}'");
        $sql = $msql->delete();
        return $this->getDb()->execute($sql);
    }

    /**
     * Search through the table
     *
     * @param object $filters Object of type stdClass to filter the search
     * @return array Search result on bidimensional array format
     */
    public function search($filters=NULL)
    {
        $msql = new MSQL('*', 'browser');
        if ( $filters->identifier )
        {
            $msql->setWhere("identifier = '{$filters->identifier}'");
        }
        if ( $filters->description )
        {
            $msql->setWhere("description = '{$filters->description}'");
        }

        $query = $this->getDb()->getQuery($msql);
        return $query->result;
    }
}

?>