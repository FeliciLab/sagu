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
 * This file handles the connection and actions for gtcSearchableField table
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
 *
 * @since
 * Class created on 01/12/2008
 *
 **/


/**
 * Class to manipulate the basConfig table
 **/
class BusinessGnuteca3BusSearchableField extends GBusiness
{
    public $busSearchableFieldAccess;
    public $group;
    public $libraryUnitId;
    public $searchableFieldId;
    public $description;
    public $field;
    public $identifier;
    public $observation;
    public $isRestricted;
    public $level;
    public $fieldType;
    public $helps;
    public $filterType;

    public $searchableFieldIdS;
    public $descriptionS;
    public $fieldS;
    public $identifierS;
    public $observationS;
    public $isRestrictedS;
    public $levelS;
    public $filterTypeS;


    public function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->table = 'gtcSearchableField';
        $this->pkeys = 'searchableFieldId';
        $this->cols  = 'description,
                        field,
                        identifier,
                        observation,
                        isRestricted,
                        level,
                        fieldType,
                        helps,
                        filterType';
        parent::__construct($this->table , $this->pkeys, $this->cols);
        $this->busSearchableFieldAccess   = $this->MIOLO->getBusiness($this->module, 'BusSearchableFieldAccess');
    }
    
    public function getAllAdvanceFields()
    {
        $busAuth = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $busSearchableAcess = $this->MIOLO->getBusiness($this->module, 'BusSearchableFieldAccess');
        $busBond = $this->MIOLO->getBusiness($this->module, 'BusBond');
        
        $admin  = GOperator::isLogged();
        $personId = $busAuth->getUserCode();
        
        $this->clear();
        $this->setColumns($this->pkeys . ',' . $this->cols);
        $this->setTables('gtcSearchableField');
        $sql = $this->select($data);
        $rs  = $this->query($sql);
        
        //Verifica todos que são do tipo avançado
        foreach ($rs as $adv)
        {
            //Verifica se o tipo do campo é Simples ou Avançado
            if($adv[9] == ADVANCED_TYPE_FIELD)
            {
                //Caso for filtro avançado, e o usuario logado não for admin
                if(!$admin)
                {
                    //Caso for um campo restrito
                    if($adv[5] == DB_TRUE)
                    {
                        //Realiza a pesquisa pelo campo corrente
                        $busSearchableAcess->searchableFieldIdS = $adv[0];
                        $permCampo = $busSearchableAcess->searchSearchableFieldAccess(TRUE);

                        /* Se não for array, significa que não terá controle de acesso 
                         * para a pessoa corrente */
                        if(!is_array($permCampo))
                        {
                            $retorno[] = $adv;
                        }
                        else
                        {
                            /* Se chegou até, significa  que as permissões do usuario deverão ser checadas.
                             * Deverá haver a checagem para confirmar se a pessoa poderá acessar
                             * o campo corrente, obtendo a permissão de 'link' do campo e comparar com a 
                             * pessoa.
                             */
                            if($personId)
                            {
                                //Todos os linksid do campo, estarão no array $linksField
                                foreach($permCampo as $pC)
                                {
                                    $linksField[] = $pC->linkId;
                                }
                                
                                //Define os links da pessoa logada
                                $userLink = $busBond->getAllPersonLink($personId);
                                
                                //Caso o usuário tenha vinculos
                                if($userLink)
                                {
                                    //Obtem todos os linkIds da pessoa
                                    foreach ($userLink as $uL)
                                    {
                                        $linksPerson[] = $uL->linkId;
                                    }
                                    
                                    //Verifica se os linksId da pessoa, estão nos linksid do campo
                                    foreach($linksPerson as $lP)
                                    {
                                        //Se tiver igualdade nos linksid
                                        if(in_array($lP,$linksField))
                                        {
                                            $retorno[] = $adv;
                                            break;
                                        }
                                    }
                                }
                                else
                                {
                                    //Usuário não tem vinculos, vai para o próximo campo
                                    //continue;
                                    break;
                                }
                            }
                            else
                            {
                                //Caso o usuário não esteja logado, vai para o proximo filtro
                                //continue;
                                break;
                            }
                        }
                    }
                    else
                    {
                        //Caso não for campo restrito, apenas adiciona o mesmo
                        $retorno[] = $adv;
                    }
                }
                else
                {
                    //Aqui o usuario é admin, e o campo foi adicionado sem checagem de restrição
                    $retorno[] = $adv;
                }
            }
        }
        //Retorna todos do tipo avançado
        return $retorno;
    }


    public function insertSearchableField()
    {
        $data = $this->associateData( $this->cols );
        parent::clear();
        parent::setColumns($this->cols);
        parent::setTables($this->table);
        $sql=parent::insert($data);
        $ok = parent::execute();

        if ($this->group && $ok)
        {
            foreach ($this->group as $value)
            {
                $this->busSearchableFieldAccess->setData($value);
                $this->busSearchableFieldAccess->searchableFieldId = $this->getNextSearchableFieldId();
                $this->busSearchableFieldAccess->insertSearchableFieldAccess();
            }
        }
        return $ok;
    }


    public function updateSearchableField()
    {
       // return $this->autoUpdate();
        $data = $this->associateData( $this->cols . ', searchableFieldId' );
        $this->clear();
        $this->setWhere('searchableFieldId = ?');
        $this->setColumns($this->cols);
        $this->setTables($this->tables);
        $sql = $this->update($data);
        $rs  = $this->execute($sql);

        if ($this->group && $rs)
        {
            $this->busSearchableFieldAccess->deleteByGroup($this->searchableFieldId);
            foreach ($this->group as $value)
            {
                $this->busSearchableFieldAccess->setData($value);
                $this->busSearchableFieldAccess->insertSearchableFieldAccess();
            }
        }
        return $rs;
    }


    public function deleteSearchableField($searchableFieldId)
    {
        if ($searchableFieldId)
        {
            $this->busSearchableFieldAccess->searchableFieldS = $searchableField;
            $search = $this->busSearchableFieldAccess->searchSearchableFieldAccess(TRUE);
            if ($search)
            {
                foreach ($search as $value)
                {
                    $this->busSearchableFieldAccess->deleteSearchableFieldAccess($searchableFieldId, $value->linkId);
                }
            }
        }

        return $this->autoDelete($searchableFieldId);
    }


    public function getSearchableField($searchableFieldId)
    {
        $this->clear();

        $this->busSearchableFieldAccess->searchableFieldIdS = $searchableFieldId;
        $this->group = $this->busSearchableFieldAccess->searchSearchableFieldAccess(TRUE);

        return $this->autoGet($searchableFieldId);
    }


    public function searchSearchableField($object = false)
    {
        $this->clear();
        $this->setColumns($this->pkeys . ',' . $this->cols);
        $this->setTables('gtcSearchableField');
        if ( $this->searchableFieldIdS )
        {
            $this->setWhere('searchableFieldId = ?');
            $data[] = $this->searchableFieldIdS;
        }
        if ($this->descriptionS)
        {
            $this->descriptionS = str_replace(' ','%', $this->descriptionS);
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = '%' . strtolower($this->descriptionS) . '%';
        }
        if ($this->fieldS)
        {
            $this->fieldS = str_replace(' ','%', $this->fieldS);
            $this->setWhere('lower(field) LIKE lower(?)');
            $data[] = '%' . strtolower($this->fieldS) . '%';
        }
        if ($this->identifierS)
        {
            $this->identifierS = str_replace(' ','%', $this->identifierS);
            $this->setWhere('lower(identifier) LIKE lower(?)');
            $data[] = '%' . strtolower($this->identifierS) . '%';
        }
        if ($this->observationS)
        {
            $this->observationS = str_replace(' ','%', $this->observationS);
            $this->setWhere('lower(observation) LIKE lower(?)');
            $data[] = '%' . strtolower($this->observationS) . '%';
        }
        if ( $this->levelS )
        {
            $this->setWhere('level = ?');
            $data[] = $this->levelS;
        }
        if ( $this->isRestrictedS )
        {
            $this->setWhere('isRestricted = ?');
            $data[] = $this->isRestrictedS;
        }
        if ( $this->fieldTypeS )
        {
            $this->setWhere('fieldType = ?');
            $data[] = $this->fieldTypeS;
        }
        if ($this->helpsS)
        {
            $this->helpsS = str_replace(' ','%', $this->helpsS);
            $this->setWhere('lower(helps) LIKE lower(?)');
            $data[] = '%' . strtolower($this->helpsS) . '%';
        }
        
        if ( $this->filterTypeS )
        {
            $this->setWhere('filterType = ?');
            $data[] = $this->filterTypeS;
        }
        
        $this->setOrderBy('searchableFieldId');
        $sql = $this->select($data);
        $rs  = $this->query($sql);
        
        foreach($rs as $result)
        {
            //Define o nome do filtro (Avançado ou Simples)
            $filterTypeName = $this->getFilterType($result[9]);
            $result[9] = $filterTypeName;
            $retorno[] = $result;
        }
        
        return $retorno;
    }



    /**
     * retorna detalhes necessarios para filtro de pesquisa
     *
     * @param integer $searchableFieldId
     * @return object
     */
    public function getDetaisForOrder($searchableFieldId)
    {
        parent::clear();
        parent::setColumns("field, fieldType");
        parent::setTables($this->table);
        parent::setWhere("searchableFieldId = ?");
        parent::select(array($searchableFieldId));
        $result = parent::query(null, true);
        return $result[0];
    }


    /**
     * retorna os campos que tem uniï¿½o na consulta
     */
    public function getUnionFields()
    {
        parent::clear();
        parent::setColumns("field");
        parent::setTables($this->table);
        parent::setWhere("field like '%+%'");
        parent::select();
        $result = parent::query(null, true);
        return $result;
    }


    /**
     * Return a list of searchable field,
     *
     * @param boolean $field_title if is to return the array in field => title form.
     * @return array
     */
    public function listSearchableField($field_title = false)
    {
        $busAuth            = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $busSearchableAcess = $this->MIOLO->getBusiness($this->module, 'BusSearchableFieldAccess');
        $busBond            = $this->MIOLO->getBusiness($this->module, 'BusBond');

        $admin  = GOperator::isLogged();
        $personId    = $busAuth->getUserCode();

        $where = '';

        // SE ESTIVER LOGADO COMO USUARIO PEGA AS PERMISSOES
        if($personId && !$admin)
    	{
    	    if($userLink = $busBond->getAllPersonLink($personId))
            {
                foreach ($userLink as $links)
                {
                    $linkId[] = $links->linkId;
                }
                $linkId = ($linkId) ? implode(',', $linkId) : 'null';

                if($idsPermitidos = $busSearchableAcess->getSearchableFieldAccessByLinkId($linkId))
                {
                    $idsArray = array();
                    foreach ($idsPermitidos as $content)
                    {
                        $idsArray[] = $content->searchableFieldId;
                    }
		   
                    $where = implode(',', $idsArray);
                    $where = "isRestricted = false OR searchableFieldId IN ($where) AND filtertype = 1";
                }
    	    }
        }

        // SE NAO TIVER WHERE E NAO FOI ADMIN... PEGA SOMENTE OS NAO RESTRITOS
        if(!strlen($where) && !$admin)
        {
            $where = 'isRestricted = false';
            if ( $field_title )
            {
                $where .= ' AND filtertype = 1';
            }
        }

        if(strlen($where))
        {
            $this->setWhere($where);
        }

    	$this->setOrderBy('level');

    	if (!$field_title)
    	{
            return $this->autoList();
    	}

		$data = $this->autoList(true);
		if (is_array($data))
		{
			foreach ($data as $line => $info)
			{
				$temp[$info->field] = $info->description;
			}
		}

		return $temp;
    }


    /**
     * Esta funï¿½ï¿½o recebe um expressï¿½o de busca usada no BusGenericSearch, e retorna a mesma exp.
     * O detalhe ï¿½ que ela troca campos nominais por tags, por exemplo: troca "autor", por "100.a".
     * De acordo com o que estiver na base.
     *
     * @param string $exp a expressï¿½o original
     * @return string $exp a espressï¿½o tratada
     */
    public function parseExpression($exp)
    {
        $exp    = ' '.$exp; //adiciona um espaï¿½o extra para funcionar o replace com ' '.$info
        $fields = $this->listSearchableField();
        if ( is_array($fields) && $fields)
        {
            foreach ($fields as $line => $info)
            {
            	$identifier    = ' '.$info[3].':';
            	$field         = ' '.$info[2].':';
            	$exp           = str_replace($identifier, $field, $exp); //faz replace com espaï¿½o na frente para resolver problema com palavras tipo 'titulo' e 'subtï¿½tulo'
            }
        }
        $exp = trim($exp);
        return $exp;
    }

    public function listFieldType()
    {
        $listType = array(
            1 => _M('Numérico', $this->module),
            2 => _M('Texto', $this->module),
            3 => _M('Data', $this->module),
            4 => _M('ComboBox', $this->module),
            5 => _M('Período', $this->module)
        );
        return $listType;
    }
    
    public function listFilterType()
    {
        $listFilter = array(
            1 => _M('Simples', $this->module),
            2 => _M('Avançado', $this->module)
        );
        return $listFilter;
    }
    
    public function getFilterType($filterType)
    {
        if($filterType)
        {
            switch($filterType)
            {
                case 1:
                    return "Simples";
                case 2:
                    return "Avançado";
            }
        }
        return false;
    }
    
    public function getNextSearchableFieldId()
    {
        $query = $this->query("SELECT currval('seq_searchablefieldid')");
        return $query[0][0];
    }

}
?>
