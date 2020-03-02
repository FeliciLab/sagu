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
 * DictionaryContent business
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
 * Class created on 19/01/2009
 *
 **/
$MIOLO = MIOLO::getInstance();
$MIOLO->usesBusiness('gnuteca3', 'BusMaterial');

class BusinessGnuteca3BusDictionaryContent extends GBusiness
{
    public $dictionaryContentId;
    public $dictionaryContentIdS;
    public $dictionaryId;
    public $dictionaryContent;
	public $searchFormatAccess;

    public $busDictionaryRelatedContent;
    public $dictionaryRelatedContent;


    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $table = 'gtcDictionaryContent';
        $pkeys = 'dictionaryContentId';
        $cols  = 'dictionaryId,
                  dictionaryContent';
        parent::__construct($table, $pkeys, $cols);
        $this->busDictionaryRelatedContent = $this->MIOLO->getBusiness($this->module, 'BusDictionaryRelatedContent');
    }


    public function insertDictionaryContent()
    {
    	$this->dictionaryContentId = $this->db->getNewId('seq_dictionaryContentId');
        $insert = $this->autoInsert();
        if ($this->dictionaryRelatedContent)
        {
            foreach ($this->dictionaryRelatedContent as $v)
            {
                $this->busDictionaryRelatedContent->setData($v);
                $this->busDictionaryRelatedContent->dictionaryContentId = $this->dictionaryContentId;
                $this->busDictionaryRelatedContent->insertDictionaryRelatedContent();
            }
        }

        return $insert;
    }


    public function updateDictionaryContent()
    {
        if ($this->dictionaryRelatedContent)
        {
            foreach ($this->dictionaryRelatedContent as $v)
            {
                $this->busDictionaryRelatedContent->setData($v);
                $this->busDictionaryRelatedContent->dictionaryContentId        = $this->dictionaryContentId;
                $this->busDictionaryRelatedContent->dictionaryRelatedContentId = $v->dictionaryRelatedContentId;
                $this->busDictionaryRelatedContent->updateDictionaryRelatedContent();
            }
        }

        return $this->autoUpdate();
    }


    public function deleteDictionaryContent($dictionaryContentId)
    {
    	$this->busDictionaryRelatedContent->dictionaryContentId = $dictionaryContentId;
    	$search = $this->busDictionaryRelatedContent->searchDictionaryRelatedContent(TRUE);
    	if ($search)
    	{
    		foreach ($search as $v)
    		{
    			$this->busDictionaryRelatedContent->deleteDictionaryRelatedContent($v->dictionaryRelatedContentId);
    		}
    	}

    	$c = $this->getDictionaryContent($dictionaryContentId);

        return $this->autoDelete($dictionaryContentId);
    }

    public function deleteDictionaryContentFromDictionary($dictionaryId)
    {
        if ( !$dictionaryId )
        {
            return null;
        }

        $sql = "DELETE FROM gtcDictionaryContent WHERE dictionaryId = $dictionaryId";
        return $result = $this->query($sql, true);
    }

    public function getDictionaryContent($dictionaryContentId)
    {
        $this->clear();
        $data = $this->autoGet($dictionaryContentId);
        $this->setData($data);
        $this->busDictionaryRelatedContent->dictionaryContentId = $dictionaryContentId;
        $this->dictionaryRelatedContent = $this->busDictionaryRelatedContent->searchDictionaryRelatedContent(TRUE);
        return $data;
    }


    public function searchDictionaryContent($toObject = false)
    {
        $this->clear();
        if ($this->dictionaryContentIdS)
        {
            $this->setWhere('A.dictionaryContentId = ?');
        	$args[] = $this->dictionaryContentIdS;
        }
        if ($this->dictionaryId)
        {
        	$this->setWhere('A.dictionaryId = ?');
        	$args[] = $this->dictionaryId;
        }
        if ($this->dictionaryContent)
        {
        	$this->setWhere('lower(A.dictionaryContent) LIKE lower(?)');
        	$args[] = '%' . $this->dictionaryContent . '%';
        }
        $this->setTables('gtcDictionaryContent  A
                LEFT JOIN gtcDictionary         B
                       ON (A.dictionaryId = B.dictionaryId)');
        $this->setOrderBy('B.description, A.dictionaryContent');
        $this->setColumns('A.dictionaryContentId,
                           B.description,
                           A.dictionaryContent');
        $sql = $this->select($args);

        return $this->query($sql, $toObject);
    }



    public function getAllDictionaryContent($dictionaryId)
    {
        $this->clear();

        $this->setColumns   ('dictionaryContent');
        $this->setTables    ('gtcDictionaryContent');
        $this->setWhere     ('dictionaryId = ?');
        $this->setOrderBy   ('dictionaryContent');

        $sql = $this->select(array($dictionaryId));
        return $this->query($sql, true);
    }


    public function listDictionaryContent()
    {
        return $this->autoList();
    }


    function clean()
    {
        $this->dictionaryContentId  =
        $this->dictionaryId         =
        $this->dictionaryContent              = null;
    }


    public function getUpdateMaterialContentCount($dictionaryId, $olderContent )
    {
        $busDictionary  = $this->MIOLO->getBusiness($this->module, 'BusDictionary');
        $dic            = $busDictionary->getDictionary( $dictionaryId );
        $tags           = explode( ',', $dic->tags);

        if ( is_array($tags) )
        {

            foreach ( $tags as $line => $tag )
            {
                $tag = explode('.', $tag); //separa campo e subcampo
                $tags[$line]=  "( fieldId='$tag[0]' and subfieldid='$tag[1]')";
            }

            $tags   = implode(' or ' , $tags);
            $sql    = "select count(*) from gtcMaterial where content = $$$olderContent$$ and ( $tags )";
            $result = $this->query($sql);

            return $result[0][0];

        }

        return 0;
    }
    
    public function updateMaterialContent($dictionaryId, $olderContent, $currentContent )
    {
        $busDictionary  = $this->MIOLO->getBusiness($this->module, 'BusDictionary');
        $dic            = $busDictionary->getDictionary( $dictionaryId );
        $tags           = explode( ',', $dic->tags);

        if ( is_array($tags) )
        {

            foreach ( $tags as $line => $tag )
            {
                $tag = explode('.', $tag); //separa campo e subcampo
                $tags[$line]=  "( fieldId='$tag[0]' and subfieldid='$tag[1]')";
            }

            $tags   = implode(' or ' , $tags);

            //atualizar históricos
            $sql = "INSERT INTO gtcMaterialHistory
                                (controlNumber,
                                revisionNumber,
                                operator,
                                data,
                                chancesType,
                                fieldId,
                                subfieldid,
                                previousLine,
                                previousIndicator1,
                                previousIndicator2,
                                previousContent,
                                currentLine,
                                currentIndicator1,
                                currentIndicator2,
                                currentContent)
                         SELECT controlNumber,
                                ( select coalesce(max(revisionnumber)+1,1) from gtcMaterialHistory where controlNumber = a.controlNumber),
                                'gnuteca3',
                                now()::date,
                                'U',
                                a.fieldId,
                                a.subfieldid,
                                a.line,
                                a.indicator1,
                                a.indicator2,
                                $$$olderContent$$,
                                a.line,
                                a.indicator1,
                                a.indicator2,
                                $$$currentContent$$
                           FROM gtcMaterial a
                          WHERE content = $$$olderContent$$
                            AND ( $tags );";
            

            $result = $this->query($sql);

            /* Alterado na data: 09/04/2014 por Tcharles Silva
             * Motivo: Comentado, pois não garante para o modo classificação.
             * Alterado para função abaixo.
             * $searchContent = GString::construct($currentContent)->toASCII()->generate();
             */
            $searchContent = BusinessGnuteca3BusMaterial::prepareSearchContent('', $currentContent);
            $searchContentModule = BusinessGnuteca3BusMaterial::prepareSearchContentForSearchModule('', $currentContent);
            $sql = "UPDATE gtcMaterial SET content = $$$currentContent$$ , searchContent = $$$searchContent$$ , searchcontentforsearchmodule = $$$searchContentModule$$  WHERE content = $$$olderContent$$ and ( $tags );";

            $result = $this->query($sql);

            return true;

        }
        return 0;
    }

    //begin;

    //Update gtcMaterial set content = 'Jamiel' where content = 'Eduardo' and (  ( fieldId='100' and subfieldid='a') or ( fieldId='110' and subfieldid='a'));

    //commit;
}
?>