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
 *  Teste unitário do business "busOperationReserve".
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Creation date 06/01/2012
 *
 **/
include_once '../classes/GUnitTest.class.php';
$MIOLO->getClass('gnuteca3', 'GDate');
$MIOLO->getClass('gnuteca3', 'GBusiness');
$MIOLO->getClass('gnuteca3', 'GMessages');
class TestOperationReserve extends GUnitTest
{
    private $business;
    
    private $module;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->module = 'gnuteca3';
        $this->business = $this->MIOLO->getBusiness($this->module, 'BusOperationReserve');
        
        $data = new stdClass();
        
        $data->personId = 4;
        $data->reserveTypeId  = 1;
        $data->libraryUnitId  = 1;
        
        $this->business->setData($data);
    }
    
    public function test()
    {
        $this->exibe('Criando GOperationReserve-> (Operação de Reserva)');
       
        $this->exibe('Authenticando pessoa:');
        $this->exibe( $this->business->personAuthenticate('uhu') );

        $this->business->reorganizeQueueReserve('2008-11-04');
        $errors = $this->business->getErrors();

        if ($errors)
        {
            $this->exibe('Erros:');
            
            foreach ( $errors as $error )
            {
               $this->exibe( $error->getMessage() ); 
            }
                    
            
        }
    }
}
?>