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
 * This file handles the connection and actions for gtcSpreadsheet table
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
 * Class created on 26/09/2008
 *
 **/


class BusinessGnuteca3BusSpreadsheet extends GBusiness
{
    public $pkeys;
    public $pkeysWhere;
    public $cols;
    public $fullColumns;

    public $category;
    public $level;
    public $field;
    public $required;
    public $repeatFieldRequired;
    public $defaultValue;
    public $menuName;
    public $menuOption;
    public $menuLevel;

    public $categoryS;
    public $levelS;
    public $fieldS;
    public $requiredS;
    public $repeatFieldRequiredS;
    public $defaultValueS;
    public $menuNameS;
    public $menuOptionS;
    public $menuLevelS;
    
    public $busTag;


    /**
     * Class constructor
     **/
    function __construct()
    {
        parent::__construct();
        $this->tables  = 'gtcSpreadsheet';
        $this->pkeys   = 'category,
                          level';
        $this->id = $this->pkeys;
        $this->cols    = 'field,
                          required,
                          repeatFieldRequired,
                          defaultValue,
                          menuName,
            			  menuOption,
            			  menuLevel';

        $this->fullColumns = $this->pkeys . ',' . $this->cols;
        $this->pkeysWhere  = 'category = ? AND level = ?';
        
        $this->busTag = $this->MIOLO->getBusiness($this->module, 'BusTag');
    }


    /**
     * Insert a new record
     *
     * @return TRUE if succed, otherwise FALSE
     **/
    public function insertSpreadsheet()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        //Define spreadsheet baseado na categoria e nível da planilha.
        $this->menuOption = $this->getSpreadSheetLeader($this->category, $this->level);
        $sql = $this->insert( $this->associateData( $this->fullColumns ) );
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Update data from a specific record
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     **/
    public function updateSpreadsheet()
    {
        $this->clear();
        $this->setColumns($this->cols);
        $this->setTables($this->tables);
        $this->setWhere($this->pkeysWhere);
        //Define spreadsheet baseado na categoria e nível da planilha.
        $this->menuOption = $this->getSpreadSheetLeader($this->category, $this->level);
        $sql = $this->update( $this->associateData( $this->cols . ',' . $this->pkeys ) );
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Delete a record
     *
     * @param $category (String)
     * @param $level (String)
     *
     * @return (boolean): TRUE if succeed, otherwise FALSE
     *
     **/
    public function deleteSpreadsheet($category, $level)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setWhere($this->pkeysWhere);
        $sql = $this->delete( array($category, $level) );
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Return a specific record from the database
     *
     * @param $category (String)
     * @param $level (String)
     *
     * @return (Object): Return an object of the type handled by the class
     **/
    public function getSpreadsheet($category, $level)
    {
        $this->clear();
        $this->setTables($this->tables);
        $this->setColumns($this->fullColumns);
        $this->setWhere("category = '$category' AND level = '$level'");
        $sql = $this->select();
        $rs  = $this->query($sql, TRUE);
        if ($rs)
        {
        	$this->setData($rs[0]);
            return $rs[0];
        }
        else
        {
            return false;
        }
    }


    /**
     * Do a search on the database table handled by the class
     *
     * @return (Array): An array containing the search results
     **/
    public function searchSpreadsheet($toObject = FALSE)
    {
        $this->clear();

        if ($this->categoryS)
        {
            $this->setWhere('category = ?');
            $data[] = $this->categoryS;
        }
        if ($this->levelS)
        {
            $this->setWhere('level = ?');
            $data[] = $this->levelS;
        }
        if ($this->fieldS)
        {
            $this->setWhere('lower(field) LIKE lower(?)');
            $data[] = '%' . $this->fieldS . '%';
        }
        if ($this->requiredS)
        {
            $this->setWhere('lower(required) LIKE lower(?)');
            $data[] = '%' . $this->requiredS . '%';
        }
        if ($this->repeatFieldRequiredS)
        {
            $this->setWhere('lower(repeatFieldRequired) LIKE lower(?)');
            $data[] = '%' . $this->repeatFieldRequiredS . '%';
        }
        if ($this->defaultValueS)
        {
            $this->setWhere('lower(defaultValue) LIKE lower(?)');
            $data[] = '%' . $this->defaultValueS . '%';
        }
        if ($this->menuNameS)
        {
            $this->setWhere('lower(menuName) LIKE lower(?)');
            $data[] = '%' . $this->menuNameS . '%';
        }
        if ($this->menuItemS)
        {
        	$this->setWhere('menuItem = ?');
        	$data[] = $this->menuItemS;
        }

        $this->setTables($this->tables);
        $this->setColumns($this->fullColumns);
        $sql = $this->select($data);
        $rs  = $this->query($sql, $toObject ? TRUE : FALSE);
        return $rs;
    }


    /**
     * List all records from the table handled by the class
     *
     * @return (Array): Return an array with the entire table
     *
     **/
    public function listSpreadsheet()
    {
        $this->clear();
        $this->setColumns($this->fullColumns);
        $this->setTables($this->tables);
        $sql = $this->select();
        $rs  = $this->query($sql);
        return $rs;
    }


    public function getMenus( $category = false , $level = false , $onlyWithMenuName = true )
    {
        $this->clear();
        $this->setColumns("menuname, menuoption, menulevel, category, level");
        $this->setTables($this->tables);
        
        if ( $onlyWithMenuName )
        {
            $this->setWhere(" menuname is not null AND menuoption is not null AND menuname != '' AND menuoption != '' ");
        }

        if ($category)
        {
        	$this->setWhere('category = ?');
        	$args[] = $category;
        }

        if ($level)
        {
            $this->setWhere('level = ?');
            $args[] = $level;
        }

        $this->setOrderBy("menulevel");
        $sql = $this->select( $args );
        $rs  = $this->query($sql, true);
        return $rs;
    }
    
    
    /**
     * Método público que pega todas as tags de determinada planilha
     * 
     * @param $category
     * @param $level
     */
    public function getTagsOfSpreadSheet($category = null, $level = null)
    {
    	$this->categoryS = $category;
    	$this->levelS = $level;
    	
    	//pega a(s) planilha(s)
        $result = $this->searchSpreadsheet(true);
        
        $newTags = array();
        if ( is_array($result) )
        {
            foreach ( $result as $key=>$value )
            {
            	$spreadSheet = $value->category . '.' . $value->level;
                $linhas = explode("\n", $value->field); //pega todas as linhas

                if ( is_array($linhas) )
                {
                    foreach( $linhas as $x=>$linha )
                    {
                        $tab = explode('=', $linha); //pega somente os campos
                        $fields = explode(',', $tab[1]); //joga em um array
                        
                        if ( is_array($fields) )
                        {
                           //percorre os campos pra buscar todas as tags
                           foreach($fields as $i=>$tag)
                           {
                               if (strlen($tag) > 0)
                               {
                                   $tagEx = explode('.', $tag);
                                   $etiqueta = $tagEx[0];
                                   $subCampo = $tagEx[1];
                  
                                   //procura as tags da etiqueta, caso não tiver na planilha
                                   if ( strlen($subCampo) == 0 )
                                   {
                                       $allTags = $this->busTag->getTag($etiqueta, '#');
                                       $allTags = $allTags->tag;
                                       
                                       //se tiver tags
                                       if ( is_array($allTags) )
                                       {
                                           ///pega os fields e subfields
                                           foreach ( $allTags as $v=>$nTag )
                                           {
                                               $newTags[$spreadSheet][] = $nTag->fieldId . '.' . $nTag->subfieldId;
                                           }
                                       }
                                   }
                                   else 
                                   {
                                        $newTags[$spreadSheet][] = $tag;
                                   }
                               }
                           }
                        }
                    }
                    
                }
                
            }
        }
        
        return $newTags;
    }
    
    /**
     * Recebe a categoria e nível de uma planilha, então retorna o leader desta.
     * 
     * @param string $category
     * @param string $level
     * @return string 
     */
    public function getSpreadSheetLeader($category, $level)
    {
        //Cria array com as strings padrões do leader para cada categoria de material.
        $leaderString['SE'] = "00000nas##2200000?##4500";
        $leaderString['MX'] = "00000npm##2200000?##4500";
        $leaderString['SA'] = "00000nab##2200000?##4500";
        $leaderString['VM'] = "00000ngm##2200000?##4500";
        $leaderString['CF'] = "00000nmm##2200000?##4500";
        $leaderString['MU'] = "00000ncm##2200000?##4500";
        $leaderString['BA'] = "00000naa##2200000?#r4500";

        //Todas categorias que não se enquadram nas padrões são definidas com o leader livro BK.
        $leaderString['BK'] = "00000nam##2200000?##4500";
        $leaderString['MP'] = "00000nam##2200000?##4500";
        $leaderString['AM'] = "00000nam##2200000?##4500";

        //Define menuoption com o leader da categoria escolhida e com o ? trocado pelo nível escolhido.
        return str_replace("?", $level, $leaderString[$category]);
    }

}
?>
