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
 * This file handles the connection and actions for basConfig table
 *
 * @author Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 13/09/2011
 *
 **/
class BusinessGnuteca3BusHelp extends GBusiness
{
    public $columnsNoId;

    public $helpId;
    public $_form;
    public $__subForm;
    public $_hP;
    public $isActive;

    public $form;
    public $subForm;
    public $help;

    public $helpIdS;
    public $formS;
    public $subFormS;
    public $helpS;
    public $isActiveS;

    public function __construct()
    {
        parent::__construct();

        $this->tables   = 'gtchelp';
        $this->columnsNoId = 'form,
                           subForm,
                           help,
                           isActive';
        $this->id = 'helpId';
        $this->columns  =  $this->id . ',' . $this->columnsNoId;
    }

    /**
     * Return a specific record from the database
     *
     * @param $helpId (integer): Primary key of the record to be retrieved
     *
     * @return (object): Return an object of the type handled by the class
     *
     **/
    public function getHelp($helpId)
    {
        return $this->autoGet($helpId);
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     **/
    public function searchHelp( $object = false )
    {
        $this->clear();

        if ( $this->helpIdS )
        {
            $this->setWhere('helpId = ?');
            $data[] = $this->helpIdS;
        }

        if ( $this->formS )
        {
            $this->setWhere('form = ?');
            $data[] = $this->formS;
        }

        if ( $this->subFormS )
        {
            $this->setWhere('subForm = ?');
            $data[] = $this->subFormS;
        }
        
        if ( $this->helpS )
        {
            $this->setWhere('lower(help) like lower(?)');
            $data[] = str_replace('', '%', '%'.$this->helpS.'%');
        }

        if ( $this->isActiveS )
        {
            $this->setWhere('isActive = ?');
            $data[] = $this->isActiveS;
        }
        
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $this->setOrderBy('helpId');
        $sql = $this->select( $data );
        
        return $this->query($sql,$object);
    }

    /**
     * Insert a new record
     *
     * @param $data (object): An object of the type handled by the class
     *
     * @return True if succed, otherwise False
     *
     **/
    public function insertHelp()
    {
        return $this->autoInsert();
    }

    /**
     * Update data from a specific record
     *
     * @param $data (object): Data which will replace the old record data
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function updateHelp()
    {
        return $this->autoUpdate();
    }

    /**
     * Delete a record
     *
     * @param $helpId (string): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     **/
    public function deleteHelp($helpId)
    {
        return $this->autoDelete($helpId);
    }
    
    /**
     * Retorna a primeira ajuda ativa para o formulário e subformulário
     * 
     * @param string $form formulário
     * @param string $subForm subformulário
     * @return stdClass
     */
    public function getFormHelp($form, $subForm = null )
    {
        // Não busca ajuda se não tiver formulário.
        if ( !$form )
        {
            return;
        }

        $this->formS = $form;
        $this->subFormS = $subForm;
        $this->isActiveS = DB_TRUE;
        $help = $this->searchHelp(true);
        return $help[0];
    }
}
?>