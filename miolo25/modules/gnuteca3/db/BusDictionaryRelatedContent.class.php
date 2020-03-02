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
 * DictionaryRelatedContent business
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
class BusinessGnuteca3BusDictionaryRelatedContent extends GBusiness
{
    public $dictionaryRelatedContentId;
    public $dictionaryContentId;
    public $relatedContent;


    public function __construct()
    {
        $table = 'gtcDictionaryRelatedContent';
        $pkeys = 'dictionaryRelatedContentId';
        $cols  = 'dictionaryContentId,
                  relatedContent';
        parent::__construct($table, $pkeys, $cols);
    }


    public function insertDictionaryRelatedContent()
    {
    	if ($this->removeData)
    	{
    		if ( $this->dictionaryRelatedContentId != FALSE )
    		{
    			$this->deleteDictionaryRelatedContent($this->dictionaryRelatedContentId);
    		}
    		return TRUE;
    	}
        return $this->autoInsert();
    }


    public function updateDictionaryRelatedContent()
    {
    	if (($this->removeData) || (!$this->dictionaryRelatedContentId))
    	{
    		$this->insertDictionaryRelatedContent();
    		return TRUE;
    	}
        return $this->autoUpdate();
    }


    public function deleteDictionaryRelatedContent($dictionaryRelatedContentId)
    {
        return $this->autoDelete($dictionaryRelatedContentId);
    }


    public function getDictionaryRelatedContent($dictionaryRelatedContentId)
    {
        $this->clear();
        return $this->autoGet($dictionaryRelatedContentId);
    }


    public function searchDictionaryRelatedContent($toObject = false)
    {
        $this->clear();
        $filters = array(
            'dictionaryRelatedContentId'    => 'equals',
            'dictionaryContentId'           => 'equals',
            'relatedContent'                => 'ilike'
        );
        return $this->autoSearch($filters, $toObject);
    }


    public function listDictionaryRelatedContent()
    {
        return $this->autoList();
    }
}
?>