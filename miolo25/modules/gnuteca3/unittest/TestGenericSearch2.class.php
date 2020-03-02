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
 *  Teste unitário do business BusGenericSearch2
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Creation date 19/12/2011
 *
 **/
include_once '../classes/GUnitTest.class.php';
$MIOLO->uses('db/BusGenericSearch2.class.php', 'gnuteca3');

class TestGenericSearch2 extends GUnitTest
{
    public function setUp()
    {
        parent::setUp();
    }
    
    public function teste()
    {
        $this->exibe("Teste da BusGenericSearch");

        // Instância business.
        $business = $this->MIOLO->getBusiness('gnuteca3', 'BusGenericSearch2');

        // Tags para a visualização dos dados.
        $viewData = array();
        $viewData[] = array('001', 'a', 'Número de controle');
        $viewData[] = array('245', 'a', 'Título');
        $viewData[] = array('100', 'a', 'Autor');
        $viewData[] = array('090', 'a', 'Classificação');
        $viewData[] = array('090', 'b', 'Cutter');
        $viewData[] = array('650', 'a', 'Assunto');

        foreach ($viewData as $vData)
        {
            $business->addSearchField($vData[0], $vData[1]);
        }

        // Faz a expressão.
        $exp = "machado 245.a:Quincas Borba OR 245.a,245.b,100.a:Memorias postumas de Bras Cubas NOT 950.a:142";
        
        // Adiciona a expressão no SQL.
        $business->addMaterialWhereByExpression($exp);

        // Faz a busca
        $data = $business->getWorkSearch();

        $this->exibe("Número de obras: " . (($data) ? count($data) : 0));
        $this->exibe("");

        foreach ( $data as $dt )
        {
            $this->exibe("Número de controle: " . $dt['001.a'][0]->content);
            $this->exibe("Titulo: " . $dt['245.a'][0]->content);
            $this->exibe("Autor:  " . $dt['100.a'][0]->content);
            $this->exibe("");
        }
    }
}
?>