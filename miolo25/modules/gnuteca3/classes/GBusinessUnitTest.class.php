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
 *  Teste unitário genérico de business
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Creation date 2010/12/16
 *
 **/
include_once 'GUnitTest.class.php';
$MIOLO->uses('classes/GBusinessUnitTestInterface.class.php', 'gnuteca3');

class GBusinessUnitTest extends GUnitTest implements GBusinessUnitTestInterface
{
    public $chave, $business, $object;
    
    public function setUp($chave) // executa a cada função (insert, search, update..)
    {
        parent::setUp();
        $this->chave = $chave;
        $this->business = $this->MIOLO->getBusiness('gnuteca3', 'Bus' . $chave);
    }

    public function setData($data)
    {
        $this->business->setData($data); 
        $this->object = $data;
    }

    public function getData($data)
    {
        return $this->object;
    }
    
    /**
     * Realiza o início da transação
     */
    public function testBeginTransaction()
    {
    	$this->business->beginTransaction();
    }    
    
    /**
     * Testa o método de inserção
     */
    public function testInsert() //nome do método test + String
    {
        $insert = 'insert' . $this->chave;
        $pk =  $this->business->id;

        if ( !$pk )
        {
            die( 'Bus não tem id definido! Está fora do padrão' );
        }

        $ok = $this->business->$insert();      
        $this->assertTrue($ok);
       
        $ok = $ok ? 'OK' : 'FAIL';
        $this->exibe('Teste de inserção - ' . $ok);
        $this->setValue('primaryKey', $this->business->$pk); //seta chave primária na 'sessão'
    }
        
    /**
     * Testa o método de busca
     */
    public function testSearch()
    {
        //busca
        $search = 'search' . $this->chave;
        $result = $this->business->$search();

        $ok = $result ? 'OK' : 'FAIL';
        $this->exibe('Teste de busca - ' . $ok);
        $this->exibe(sizeof($result) . ' registros encontrados');
        
        $this->assertTrue(true);
    }
    
    /**
     * Teste o método get
     */
    public function testGet()
    {
        $pk     = $this->getValue('primaryKey');
        $get    = 'get' . $this->chave;
        $result = $this->business->$get($pk);
        
        $ok = is_object( $result ) ? 'OK' : 'FAIL';

        $this->exibe('Teste de obtenção - ' . $ok);
        $this->assertTrue( is_object( $result ) );
    }
    
    /**
     * Teste o método update
     */
    public function testUpdate()
    {
        $pk = $this->business->id;
        $this->business->$pk = $this->getValue('primaryKey');
        $update = 'update' . $this->chave;
        $ok     = $this->business->$update();
        $this->assertTrue($ok);
        
        $ok = $ok ? 'OK' : 'FAIL';
        $this->exibe('Teste de atualização - ' . $ok);
    }
    
    /**
     * Teste o método delete
     */
    public function testDelete()
    {
        $pk = $this->business->id;
        $this->business->$pk = $this->getValue('primaryKey'); //seta o valor na chave primaria 
        
        $delete = 'delete' . $this->chave;
        $ok     = $this->business->$delete();
        $this->assertTrue($ok);

        $ok = $ok ? 'OK' : 'FAIL';
        $this->exibe('Teste de deleção - ' . $ok);
    }
    
    /**
     * Realiza o commit da transação
     */
    public function testEndTransaction()
    {
        $this->business->rollbackTransaction();
    }   
}
?>