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
 * gtcDictionary business
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
 * Class created on 03/12/2008
 *
 **/
class BusinessGnuteca3BusDictionary extends GBusiness
{
    public $dictionaryId;
    public $dictionaryIdS;
    public $description;
    public $tags;
    public $readOnly;


    public function __construct()
    {
        $table = 'gtcDictionary';
        $pkeys = 'dictionaryId';
        $cols  = 'description,
                  tags,
                  readOnly';
        parent::__construct($table, $pkeys, $cols);
    }


    public function insertDictionary()
    {
        return $this->autoInsert();
    }


    public function updateDictionary()
    {
        return $this->autoUpdate();
    }


    public function deleteDictionary($dictionaryId)
    {   
        $busDictionaryContent = $this->MIOLO->getBusiness($this->module, 'BusDictionaryContent');

        $busDictionaryContent->deleteDictionaryContentFromDictionary($dictionaryId);
        return $this->autoDelete($dictionaryId);
    }


    public function getDictionary($dictionaryId)
    {
        $this->clear();
        return $this->autoGet($dictionaryId);
    }


    public function searchDictionary($object = false)
    {
        unset($this->dictionaryId); //estava ocorrendo bug pos-busca no formulario
        $this->clear();
        $filters = array(
            'dictionaryId'      => 'equals',
            'description'        => 'ilike',
            'tags'              => 'ilike',
            'readOnly'           => 'equals'
        );
        return $this->autoSearch($filters, $object);
    }

    /**
     * Obtém termos relacionados do dicionário
     * 
     * @param array $tags 
     * @param String $term termo a ser pesquisado
     * @return array de termos relacionados 
     */
    public function getRelatedTerms($tags, $term)
    {
        $this->clear();
        $this->setColumns("dictionarycontent");
        $this->setTables("gtcdictionary A 
               INNER JOIN gtcdictionarycontent B 
                    USING (dictionaryid) 
                LEFT JOIN gtcdictionaryrelatedcontent C 
                    USING(dictionarycontentid)");
        
        $orArray = array();
        foreach( $tags as $k => $tag )
        {
            $orArray[] = "tags ilike ('%{$tag}%')";
        }
        
        $this->setWhere("( " . implode(' OR ',  $orArray) . ")");
        
        $this->setWhere("lower(unaccent(relatedcontent)) like (unaccent(lower($$%{$term}%$$)))");
        $this->setLimit(8);
        $sql = $this->select();
        $result = $this->query($sql);
        
        return $result;
    }


    public function listDictionary()
    {
        return $this->autoList();
    }


    public function checkExistsDictionaryForTag($tag)
    {
        $this->clear();
        $this->tags = $tag;
        $filters = array
        (
            'tags' => 'like',
        );

        $d = $this->autoSearch($filters, true);

        if(!$d)
        {
            return false;
        }

        return isset($d[0]) ? $d[0] : false;
    }

    /**
     * Adiciona todos conteúdos que estão nos materiais, mas não estão no dicionário, tal e qual.
     *
     *
     * @param <integer> $dictionaryId
     * @return <boolean>
     *
     */
    public function addContentMaterials($dictionaryId)
    {

        if ( !$dictionaryId )
        {
            return null;
        }

        $sql = "INSERT INTO gtcDictionaryContent (dictionaryContent,dictionaryId)
                (
                SELECT distinct content, $dictionaryId
                FROM
                (
                    SELECT substring(subTag, 0, position('.' in subTag)) as field,
                           substring(subTag, position('.' in subTag)+1) as subfield
                      FROM regexp_split_to_table( (
                                SELECT tags
                                  FROM gtcDictionary where dictionaryid = $dictionaryId ) , ',' ) AS subTag
                )
                AS tags

                    INNER JOIN gtcMaterial M ON (tags.field = M.fieldid AND tags.subfield = M.subfieldID )

                EXCEPT

                    SELECT dictionaryContent, $dictionaryId FROM gtcDictionaryContent where dictionaryId = $dictionaryId
                )
        ";


        return $this->query($sql);
    }
}
?>