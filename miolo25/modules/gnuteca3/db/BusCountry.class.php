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
 * @author Jonas C. Rosa [jonas_rosa@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini   [eduardo@solis.coop.br]
 * Jamiel Spezia        [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 11/07/2012
 *
 * */
class BusinessGnuteca3BusCountry extends GBusiness
{

    public $countryIdS;
    public $nameS;
    public $nationalityS;
    public $currencyS;
    public $pluralCurrencyS;
    public $decimalDescriptionS;
    public $pluralDecimalDescriptionS;
    public $currencySymbolS;
    public $countryId;
    public $_name;
    public $name;
    public $nationality;
    public $currency;
    public $pluralCurrency;
    public $decimalDescription;
    public $pluralDecimalDescription;
    public $currencySymbol;

    function __construct()
    {
        //define a tabela e os campos padrão do bus
        parent::__construct('basCountry', 'countryId', 'name,
                  nationality,
                  currency,
                  pluralCurrency,
                  decimalDescription,
                  pluralDecimalDescription,
                  currencySymbol'
        );
    }

    public function listCountry($object = FALSE)
    {
        return $this->autoList();
    }

    public function getCountry($id)
    {
        $this->clear;
        //here you can pass how many where you want
        $get = $this->autoGet($id);
        $this->_name = $this->name;

        return $get;
    }

    public function searchCountry($toObject = false)
    {
        $filters = array(
            'countryId' => 'ilike',
            'name' => 'ilike',
            'nationality' => 'ilike',
            'currency' => 'ilike',
            'pluralCurrency' => 'ilike',
            'decimalDescription' => 'ilike',
            'pluralDecimalDescription' => 'ilike',
            'currencySymbol' => 'ilike'
        );

        $this->clear();

        return $this->autoSearch($filters, $toObject);
    }

    public function insertCountry()
    {
        $this->name = $this->_name;
        return $this->autoInsert();
    }

    public function updateCountry()
    {
        $this->name = $this->_name;
        return $this->autoUpdate();
    }

    public function deleteCountry($countryId)
    {
        return $this->autoDelete($countryId);
    }

}

?>