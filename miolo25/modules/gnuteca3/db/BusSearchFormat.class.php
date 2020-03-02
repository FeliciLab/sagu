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
 * This file handles the connection and actions for gtcWeekDay table
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
 * Class created on 28/11/2008
 *
 * */

/**
 * Class to manipulate the basConfig table
 * */
class BusinessGnuteca3BusSearchFormat extends GBusiness
{
    public $busSearchFormatAccess;
    public $busSearchFormatColumn;
    public $busSearchPresentationFormat;
    private $busMaterialSearchFormat;
    public $searchFormatAccess;
    public $searchFormatColumn;
    public $searchPresentationFormat;
    public $searchFormatId;
    public $description;
    public $isRestricted;
    public $date;
    public $searchFormatIdS;
    public $descriptionS;
    public $isRestrictedS;
    public $dateS;
    public $periodicInformationContent;
    public $busMaterial;
    public static $relationOfFieldsWithTable = true; // se é ou não pra fazer relação com tabelas

    public function __construct()
    {
        parent::__construct('gtcSearchFormat', 'searchFormatId', 'description,isRestricted,date');
        $this->busSearchFormatAccess = $this->MIOLO->getBusiness($this->module, 'BusSearchFormatAccess');
        $this->busSearchFormatColumn = $this->MIOLO->getBusiness($this->module, 'BusSearchFormatColumn');
        $this->busSearchPresentationFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchPresentationFormat');
        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busMaterialSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusMaterialSearchFormat');
    }

    public function clean()
    {
        $this->searchFormatAccess =
                $this->searchFormatColumn =
                $this->searchPresentationFormat =
                $this->searchFormatId =
                $this->description =
                $this->isRestricted =
                $this->date =
                $this->dateS =
                $this->searchFormatIdS =
                $this->descriptionS =
                $this->isRestrictedS = null;

        parent::clear(); //limpa o objeto MSql
    }

    public function insertSearchFormat()
    {
        $this->searchFormatId = $this->db->getNewId('seq_searchFormatId');
        $ok = $this->autoInsert();
        $this->addExternalData();
        return $ok;
    }

    public function updateSearchFormat()
    {
        $this->addExternalData();
        return $this->autoUpdate();
    }

    public function deleteSearchFormat($searchFormatId)
    {
        $reservedSearchFormat = explode(',', RESERVED_SEARCH_FORMAT);
        
        // Verifica se o formato de pesquisa é um formato protegido do sistema.
        if ( in_array($searchFormatId, $reservedSearchFormat) )
        {
            throw new Exception(_M('Este formato de pesquisa não pode ser excluído pois é necessário para o funcioamento do sistema!'));
        }
        
        // Apaga o cache do formato de pesquisa.
        $this->busMaterialSearchFormat->deleteAllSearchFormatForSearchFormat($searchFormatId);
        
        $this->busSearchFormatColumn->deleteSearchFormatColumn($searchFormatId);

        $this->getExternalData($searchFormatId);

        foreach ( $this->searchFormatAccess as $val )
        {
            $this->busSearchFormatAccess->deleteSearchFormatAccess($val->searchFormatId, $val->linkId);
        }

        foreach ( $this->searchPresentationFormat as $val )
        {
            $this->busSearchPresentationFormat->deleteSearchPresentationFormat($val->searchFormatId, $val->category);
        }

        return $this->autoDelete($searchFormatId);
    }

    public function getSearchFormat($searchFormatId)
    {
        $this->clear();

        $data = $this->autoGet($searchFormatId);
        $dataExternal = $this->getExternalData($searchFormatId, true);

        $data->searchFormatAccess = $dataExternal[0];
        $data->searchPresentationFormat = $dataExternal[1];
        $data->searchFormatColumn = $dataExternal[2];

        $this->setData($data);

        return $data;
    }

    /**
     * Do a search on the database table handled by the class
     *
     * @param $object (bool): Case TRUE return as Object, otherwise Array
     *
     * @return (Array): An array containing the search results
     * */
    public function searchSearchFormat($object = false)
    {
        $this->clear();
        $this->setColumns('searchFormatId, description, isRestricted, date');
        $this->setTables('gtcSearchFormat');

        if ( $this->searchFormatIdS )
        {
            $this->setWhere('searchFormatId = ?');
            $data[] = $this->searchFormatIdS;
        }

        if ( ($this->descriptionS) || ($this->description) )
        {
            if ( $this->description )
            {
                $this->descriptionS = $this->description;
            }

            $this->descriptionS = str_replace(' ', '%', $this->descriptionS);
            $this->setWhere('lower(description) LIKE lower(?)');
            $data[] = '%' . strtolower($this->descriptionS) . '%';
        }

        if ( $this->isRestrictedS )
        {
            $this->setWhere('isRestricted = ?');
            $data[] = $this->isRestrictedS;
        }
        
        if ( $this->dateS )
        {
            $this->setWhere('date = ?');
            $data[] = $this->dateS;
        }

        $sql = $this->select($data);

        $rs = $this->query($sql);
        return $rs;
    }

    /**
     * Lista formatos de pesquisa de acordo com permissões.
     *
     * @param $radioButton, retorna o array de forma diferenciada para MRadioButton
     * @param $hideRestrict, caso seja para esconder as restritas, utilizada na pesquisa simples, para simples somente as do usuário logado
     *
     * TODO essa função pode receber uma otimização, mas vale lembrar que ela sempre
     * precisará de no minimo 2 sqls em função das bases diferenes de miolo/gnuteca.
     *
     * @return array: return um array com os dados filtrados
     *
     * */
    public function listSearchFormat($radioButton = FALSE, $hideRestrict = false)
    {
        $busAuthenticate = $this->MIOLO->getBusiness('gnuteca3', 'BusAuthenticate');
        $busPerson = $this->MIOLO->getBusiness('gnuteca3', 'BusPerson');
        $busAccess = $this->MIOLO->getBusiness('gnuteca3', 'BusSearchFormatAccess');

        $this->setOrderBy('description');

        if ( $hideRestrict )
        {
            $this->setWhere('isRestricted = false');

            $userCode = $busAuthenticate->getUserCode();

            //se esta logado lista os searchFormat extras com permissões de grupo
            if ( $userCode )
            {
                $person = $busPerson->getPerson($userCode, true, 'ALL'); //pega pessoa logado, somente links ativos
                $links = $person->bond; //pega seus bonds
                //monta array simples com os ids de bonds
                if ( is_array($links) )
                {
                    foreach ( $links as $line => $link )
                    {
                        $linksA[] = $link->linkId;
                    }
                }

                //busca acessos permitidos no devido bus
                if ( is_array($linksA) )
                {
                    $busAccess->links = implode(',', $linksA);
                    $access = $busAccess->searchSearchFormatAccess(false, true); //distinct
                }

                //monta array de acessos na forma de dados padrão
                if ( $access )
                {
                    foreach ( $access as $line => $acesso )
                    {
                        //([0] = Id | [1] = Descricao) do formato de pesquisa, este eh o padrao do $this->autolist()
                        $extraAccess[] = array( $acesso[0], $acesso[1] );
                    }
                }
            }
        }

        $extraAccess = is_array($extraAccess) ? $extraAccess : array( ); //para suportar array_merge

        if ( !$radioButton )
        {
            return array_merge($this->autoList(), $extraAccess);
        }
        else
        {
            $data = $this->autoList(true);

            if ( is_array($data) )
            {
                foreach ( $data as $line => $info )
                {
                    $result[] = array( $info->description, $info->searchFormatId );
                }
            }

            $result = is_array($result) ? $result : array( ); //para suportar array_merge
            $result = array_merge($result, $extraAccess);

            return $result;
        }
    }

    /**
     * Add data on other tables
     */
    public function addExternalData()
    {
        if ( $this->searchFormatAccess )
        {
            $this->busSearchFormatAccess->deleteBySearchFormat($this->searchFormatId);
            foreach ( $this->searchFormatAccess as $val )
            {
                $this->busSearchFormatAccess->setData($val);
                $this->busSearchFormatAccess->searchFormatId = $this->searchFormatId;
                $this->busSearchFormatAccess->insertSearchFormatAccess();
            }
        }
        if ( $this->searchPresentationFormat )
        {
            $this->busSearchPresentationFormat->deleteBySearchFormat($this->searchFormatId);
            foreach ( $this->searchPresentationFormat as $val )
            {
                $this->busSearchPresentationFormat->setData($val);
                $this->busSearchPresentationFormat->searchFormatId = $this->searchFormatId;
                $this->busSearchPresentationFormat->insertSearchPresentationFormat();
            }
        }

        $this->busSearchFormatColumn->deleteSearchFormatColumn($this->searchFormatId);

        if ( $this->searchFormatColumn )
        {
            foreach ( $this->searchFormatColumn as $column )
            {
                $v = new StdClass();
                $v->searchFormatId = $this->searchFormatId;
                $v->column = $column;
                $this->busSearchFormatColumn->setData($v);
                $this->busSearchFormatColumn->insertSearchFormatColumn();
            }
        }
    }

    public function getExternalData($searchFormatId, $return = false)
    {
        if ( !$searchFormatId )
        {
            return false;
        }

        $this->searchFormatColumn = array( );
        $this->busSearchFormatAccess->searchFormatId = $searchFormatId;
        $this->busSearchPresentationFormat->searchFormatId = $searchFormatId;
        $this->busSearchFormatColumn->searchFormatIdS = $searchFormatId;

        $searchFormatAccess = $this->busSearchFormatAccess->searchSearchFormatAccess(true);
        $this->searchFormatAccess = $searchFormatAccess;

        $searchPresentationFormat = $this->busSearchPresentationFormat->searchSearchPresentationFormat(true);
        $this->searchPresentationFormat = $searchPresentationFormat;

        $search = $this->busSearchFormatColumn->searchSearchFormatColumn(true);

        if ( $search )
        {
            foreach ( $search as $v )
            {
                $this->searchFormatColumn[] = $v->column;
            }
        }

        if ( $return )
        {
            return array( $searchFormatAccess, $searchPresentationFormat, $this->searchFormatColumn );
        }
    }

    /**
     * Return the string formated with passed searchFormatid and data
     *
     * @param intger $searchFormatId
     * @param array $data
     * @param string $type 'search' or 'detail'
     * @return string formated text
     * @return string pode ser passada a catoria para evitar uma sql ao busMaterialControl
     */
    public function formatSearchData($searchFormatId, $data, $type = 'search', $category = null) //detail
    {
        $busMaterial = $this->busMaterial;

        //força search caso passe null
        if ( !$type )
        {
            $type = 'search';
        }

        if ( !$category )
        {
            $busMaterialControl = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');
            $category = $busMaterialControl->getCategory($data['CONTROLNUMBER']);
        }

        //FIXME da pra otimizar pra pra pesquisa selecionando todos e pondo em uma varivel depois só pegando o que precisa
        $presentationFormat = $this->busSearchPresentationFormat->getSearchPresentationFormat($searchFormatId, $category);

        $GFunction = new GFunction();
        $GFunction->SetExecuteFunctions(true); //Permitir executar comandos SQL ou PHP no formato de pesquisa.

        foreach ( $data as $line => $info )
        {
            //Vide ticket #12310 validação feita para garantir que campo 0 vindo do 
            //$data não seja setado como $0 pela função $GFunction->setVariable('$'.$line, $content ); 
            //pois campos como $090.a $041.a terão o seu inicio '$0' trocado por ''
            if ( $line == '0' )
            {
                continue;
            }

            $content = array( );

            if ( is_array($info) )
            {
                foreach ( $info as $l => $i )
                {
                    $value = $busMaterial->relationOfFieldsWithTable($line, $i->content);

                    $content[] = $value ? $value : $i->content;
                }
            }

            //set tiver várias linha implode por -#-
            $content = implode('-#-', $content);
            $content = trim($content);

            //se no fim não tiver content seta como vazio para não aparecer a variavel
            if ( !strlen($content) )
            {
                $content = '';
            }

            $GFunction->setVariable('$' . $line, $content);
        }

        $GFunction->setVariable('$LN', "###BREAKLINE###");
        $GFunction->setVariable('$SP', '&nbsp;');

        if ( $type == 'search' )
        {
            //tentar pegar o format padrão caso não exista
            if ( !$presentationFormat->searchFormat )
            {
                $presentationFormat = $this->busSearchPresentationFormat->getSearchPresentationFormat($searchFormatId, 'DF');
            }

            $format = $presentationFormat->searchFormat;
        }
        else
        {
            //tentar pegar o format padrão caso não exista
            if ( !$presentationFormat->detailFormat )
            {
                $presentationFormat = $this->busSearchPresentationFormat->getSearchPresentationFormat($searchFormatId, 'DF');
            }

            $format = $presentationFormat->detailFormat;
        }

        $presentationDone = $GFunction->interpret($format);
        $presentationDone = str_replace("###BREAKLINE###", "<br/>", $presentationDone);

        return $presentationDone;
    }

    /**
     * Return an array with the needed variables names.
     *
     * @param integer $searchFormatId
     * @param array $type
     * @return array
     */
    public function getVariablesFromSearchFormat($searchFormatId, $type = array( 'search', 'detail' ))
    {
        $this->busSearchPresentationFormat->searchFormatId = $searchFormatId;
        $format = $this->busSearchPresentationFormat->searchSearchPresentationFormat(true);

        $result = array( );

        foreach ( $format as $line => $info )
        {
            if ( in_array('search', $type) )
            {
                $matches = GUtil::extractMarcVariables($info->searchFormat);
                $result = array_merge($result, $matches);
            }

            if ( in_array('detail', $type) )
            {
                $matches = GUtil::extractMarcVariables($info->detailFormat);
                $result = array_merge($result, $matches);
            }
        }

        return $result;
    }
    
    
    private function getFormated($controlNumber, $searchFormatId, $type)
    {
        $fieldsList = $this->getVariablesFromSearchFormat($searchFormatId, array( $type ));
        $busGenericSearch = $this->MIOLO->getBusiness($this->module, 'BusGenericSearch2');
        $busGenericSearch->clean();

        if ( is_array($fieldsList) )
        {
            foreach ( $fieldsList as $tag )
            {
                $tag = str_replace("$", "", $tag);
                $busGenericSearch->addSearchTagField($tag);
            }
        }

        $busGenericSearch->addControlNumber($controlNumber);

        $data = $busGenericSearch->getWorkSearch(1, true);
        
        $data = array_values($data);
        
        $this->periodicInformationContent = null;

        if ( $data[0][MARC_PERIODIC_INFORMATIONS] )
        {
            $content = $data[0][MARC_PERIODIC_INFORMATIONS][0]->content;
            $this->periodicInformationContent = null;

            $year = "0000";
            $month = "00";
            $volume = "0000";
            $number = "0000";

            //Get year
            preg_match("/[0-9]{4}$/", $content, $match);
            $year = isset($match[0]) ? $match[0] : $year;

            //Get month // EXPRESSÂO: jun. 2009 ou maio 2009
            preg_match("/ [a-zA-Z\.]{4} [0-9]{4}$/", $content, $match);
            if ( isset($match[0]) )
            {
                $month = substr($match[0], 1, -5);
            }

            // GET MOUNTH // EXPRESSÂO: mar./abr. 2009 ou nov./dez 2008
            if ( $month == "00" )
            {
                preg_match("/ [a-zA-Z\.]{4}\/[a-zA-Z\.]{4} [0-9]{4}$/", $content, $match);
                if ( isset($match[0]) )
                {
                    $month = substr($match[0], 1, -5);
                    if ( ereg("/", $month) )
                    {
                        list($m, $month) = explode("/", $month);
                    }
                }
            }

            if ( $month != "00" )
            {
                $m1 = array( 'jan.', 'fev.', 'mar.', 'abr.', 'maio', 'jun.', 'jul.', 'ago.', 'set.', 'out.', 'nov.', 'dez.' );
                $m2 = array( '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' );
                $month = strtolower($month);
                $month = str_replace($m1, $m2, $month);
            }

            if ( strlen($month) != 2 )
            {
                $month = "00";
            }

            //Get volume
            preg_match("/^v\. [0-9]{1,4}/", $content, $match);
            if ( isset($match[0]) )
            {
                $vol = str_replace(array( 'v. ', ',' ), '', $match[0]);
                $volume = GUtil::strPad($vol, 4, 0, STR_PAD_LEFT);
            }

           //Get number
            preg_match("/no?\. [0-9]{1,4}?[\-\/0-9]{0,4}?[ \w\W]{0,100}\,/", $content, $match);
            if (isset($match[0]))
            {
                $num    = str_replace(array('n. ', ','), '', $match[0]);

                if(ereg("-", $num))
                {
                    list($n, $num) = explode("-", $num);
                }
                if(ereg("/", $num))
                {
                    list($n, $num) = explode("/", $num);
                }

                $num    = preg_replace('/[^0-9]/', '', $num);
                $number = GUtil::strPad($num, 4, 0, STR_PAD_LEFT);
            }

            $this->periodicInformationContent = "$year$month$volume$number";
            //Add zeros on number for correct sorting
            $this->periodicInformationContent = GUtil::strPad($this->periodicInformationContent, 14, 0, STR_PAD_LEFT);
        }
        
        $result = $this->formatSearchData($searchFormatId, $data[0], $type);
        
        return $result;
    }
    

    /**
     * Return the formated data (using a passed search format) of a control number
     *
     * @param integer $controlNumber
     * @param integer $searchFormatId
     * @param string $type 'search' or 'detail'
     * @return string the string formated with data of seted control number
     */
    public function getFormatedString($controlNumber, $searchFormatId, $type = 'search', $returnAsObject = false)
    {
        $busMaterialSearchFormat = $this->MIOLO->getBusiness('gnuteca3', 'BusMaterialSearchFormat');
        $busMaterialControl = $this->MIOLO->getBusiness('gnuteca3', 'BusMaterialControl');
        
        $existMaterial = $busMaterialControl->existsMaterial($controlNumber);
                
        //segurança para evitar erros na aplicação #11953
        if ( !$controlNumber || !$searchFormatId || !$existMaterial)
        {
            return '';
        }
        
        // Atualiza cache de formato de pesquisa.
        $materialSearchFormat = $busMaterialSearchFormat->getMaterialSearchFormat($controlNumber, $searchFormatId);
        
        $materialSearchFormat->controlNumber = $controlNumber;
        $materialSearchFormat->searchFormatId = $searchFormatId;

        // Verifica se deve adicionar o formato no cache ou atualizar.
        if ( !$materialSearchFormat->date )
        {
            $materialSearchFormat->searchFormat = $this->getFormated($controlNumber, $searchFormatId, 'search');;
            $materialSearchFormat->detailFormat = $this->getFormated($controlNumber, $searchFormatId, 'detail');

            $materialSearchFormat->date = date(GDate::MASK_TIMESTAMP_USER);
            
            $materialSearchFormat->insertMaterialSearchFormat();
        }
        else
        {
            $searchFormat = $this->getSearchFormat($searchFormatId);
            $dateSearchFormat = new GDate($searchFormat->date);
            $dateMaterialSearchFormat = new GDate($materialSearchFormat->date);
            
            // Sem entrar aqui não ordena os fascículos corretamente. Ticket #32281.
            //if ( $dateMaterialSearchFormat->compare($dateSearchFormat, '<') )
            {
                // Atualiza.
                $materialSearchFormat->searchFormat = $this->getFormated($controlNumber, $searchFormatId, 'search');;
                $materialSearchFormat->detailFormat = $this->getFormated($controlNumber, $searchFormatId, 'detail');
                
                $materialSearchFormat->date = date(GDate::MASK_TIMESTAMP_USER);
                
                // Atualiza cache.
                $materialSearchFormat->updateMaterialSearchFormat();
            }
        }
        
        if ( $type == 'search' )
        {
            $result = $materialSearchFormat->searchFormat;
        }
        else
        {
            $result = $materialSearchFormat->detailFormat;
        }
        
        //Se for para retornar um objeto.
        if ( $returnAsObject )
        {
            $resultObject['format'] = $result;
            $resultObject['260c'] = $data[0]['260.c'][0]->content;
            $result = $resultObject;
        }

        return $result;
    }
}

?>
