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
 * @author Luiz G Gregory Filho [luiz@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/

/**
 * Class to manipulate 
 **/

class BusinessGnuteca3BusLabelLayout extends GBusiness
{
    /**
     * Attributes
     */
    
    public $MIOLO;
    
    
    // Attributos Formulario
    public $labelLayoutId,
            $description,
            $topMargin,
            $leftMargin,
            $verticalSpacing,
            $horizontalSpacing,
            $height,
            $width_,
            $lines,
            $columns_,
            $pageFormat;

    // Attributos Formulario Search
    public $labelLayoutIdS,
            $descriptionS,
            $pageFormatS;


    /**
     * Constructor Method
     */
    
    function __construct()
    {
        parent::__construct();
        
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        
        $this->setData(null);
        $this->setColumns();
        $this->setTables();
    }

    /**
     * Seta as tabelas
     *
     * @param (String || Array) $tables
     */
    
    public function setTables($tables = null)
    {    
        if(is_null($table))
        {
            $table = "gtclabellayout";
        }
        
        parent::setTables($table);
    }
        
    /**
     * Este método seta as colunas da tabela.     
     *
     * @param (String || Array) $columns
     */
    
    public function setColumns($type = null)
    {
        //FIXME Ver o que da para ser feito para deixar no padrão esse bus.
        $this->id = 'labelLayoutId'; //Define um id para deixar no padrão do teste unitário

        switch($type)
        {
            case "update":
            case "insert" :
                $columns = array
                (
                    'description',
                    'topMargin',
                    'leftMargin',
                    'verticalSpacing',
                    'horizontalSpacing',
                    'height',
                    'width',
                    'lines',
                    'columns',
                    'pageFormat',
                );
                break;

            case "All":
            default:
                $columns = array
                (
                    $this->id,
                    'description',
                    'topMargin',
                    'leftMargin',
                    'verticalSpacing',
                    'horizontalSpacing',
                    'height',
                    'width',
                    'lines',
                    'columns',
                    'pageFormat',
                );            
        }
        
        parent::setColumns($columns);
    }      

    
    /**
     * Do a search on the database table handled by the class
     *
     * @return (array): An array containing the search results
     */
    public function searchLabelLayout()
    {
        $this->clear();
        $args = array();

        if( strlen($this->labelLayoutIdS) > 0 )
        {
            $this->setWhere('labelLayoutId = ?');
            $args[] = $this->labelLayoutIdS;
        }

        if( strlen($this->descriptionS) > 0 )
        {
            $this->setWhere('lower(description) LIKE lower(?)');
            $args[] = $this->descriptionS . '%';
        }

        if( strlen($this->pageFormatS) > 0 )
        {
            $this->setWhere('lower(pageFormat) LIKE lower(?)');
            $args[] = $this->pageFormatS . '%';
        }

        $this->setTables();
        $this->setColumns();
        $sql = $this->select($args);

        return $this->query($sql);
    }


    /**
     * Insert a new record
     * 
     * @return True if succed, otherwise False     
     */
    
    public function insertLabelLayout()
    {
        $this->clear();
        
        $this->setTables();
        $this->setColumns("insert");
                
        $data = array
        (
            $this->description,
            $this->topMargin,
            $this->leftMargin,
            $this->verticalSpacing,
            $this->horizontalSpacing,
            $this->height,
            $this->width_,
            $this->lines,
            $this->columns_,
            $this->pageFormat,
        );                   

        $sql = $this->insert($data);

        return $this->execute($sql);
    }


    /**
     * Atualiza um determinado registro
     * 
     * @return True if succed, otherwise False     
     */
    
    public function updateLabelLayout()
    {
        $this->clear();
        $this->setTables();
        $this->setColumns('update');
        $this->setWhere('labellayoutid = ?');

        $data = array
        (
            $this->description,
            $this->topMargin,
            $this->leftMargin,
            $this->verticalSpacing,
            $this->horizontalSpacing,
            $this->height,
            $this->width_,
            $this->lines,
            $this->columns_,
            $this->pageFormat,
            $this->labelLayoutId
        );                   
        
        $sql = $this->update($data);

        return $this->execute($sql);
    }   


    /**
     * retorna um determinado registro
     *
     * @param (int) $labelLayoutId - Id do registro
     * @return (Array)
     */
    public function getLabelLayout($labelLayoutId)
    {    
        $this->labelLayoutId = $labelLayoutId;

        $this->clear();
        $this->setTables();
        $this->setColumns('All');
        $this->setWhere('labellayoutid = ?');
        
        $sql = $this->select($labelLayoutId);
        $result = $this->query($sql, true);

        if ( $result )
        {
            $result[0]->width_ = $result[0]->width;
            $result[0]->columns_ = $result[0]->columns;

            $this->setData($result[0]);
            return $this;
        }
        else
        {
            return null;
        }
    }


    /**
     * Delete a record
     *
     * @param $labelLayoutId (int): Primary key for deletion
     *
     * @return (boolean): True if succeed, otherwise False
     *
     */
    
    public function deleteLabelLayout($labelLayoutId)
    {        
        $this->clear();
        $this->setTables();      
        $this->labelLayoutId = $labelLayoutId;
        $this->setWhere('labellayoutid = ?');
        $sql = $this->delete(array($labelLayoutId));
       
        return $this->execute($sql);
    }
    
}
?>
