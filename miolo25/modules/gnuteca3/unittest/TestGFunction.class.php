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
 *  Teste unitário
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
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
$MIOLO->uses('classes/GFunction.class.php', 'gnuteca3');

class TestGFunction extends GUnitTest
{
    public function setUp()
    {
        parent::setUp();
    }
    
    public function teste()
    {
        $this->exibe("Teste da GFunction");

        $GFunction = new GFunction();

        $this->exibe("");
        $GFunction->setVariable('$100.a', "Spezia, Jamiel");
        $this->exibe("Teste da funcao <b>". htmlentities("<upper><pregmatch ^[\w]{0,} | $100.a></pregmatch></upper><pregmatch [\w\s]{0,}$ | $100.a></pregmatch>") ."</b>");
        $text = '<upper><pregmatch ^[\w]{0,} | $100.a></pregmatch></upper> === ';
        $text.= '<pregmatch [\w\s]{0,}$ | $100.a></pregmatch> ==  ';
        $retorno = $GFunction->interpret($text);
        $this->exibe($retorno);
        $this->exibe("");
    }
}
?>