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
 *  Teste unitário do business "busOperationRenew".
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Creation date 09/01/2012
 *
 **/
include_once '../classes/GUnitTest.class.php';
$MIOLO->getClass('gnuteca3', 'GBusiness');
$MIOLO->getClass('gnuteca3', 'GMessages');

class TestOperationRenew extends GUnitTest
{
    private $business;
    
    private $module;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->module = 'gnuteca3';
        $this->business = $this->MIOLO->getBusiness($this->module, 'BusOperationRenew');
    }
    
    public function test()
    {
        $loanId = 1000;
        
        $this->business->setRenewType(ID_RENEWTYPE_WEB);
        $this->exibe('Definindo tipo de renovação como web id='. ID_RENEWTYPE_WEB.'.');
        $this->exibe('Fazendo checkLoan.');
        $loan = $this->business->checkLoan($loanId);
        
        if ( !$this->business->getErrors() )
        {
            $this->exibe('Tudo ok, pode adicionar.');
            $this->business->addLoan($loan);
            
            if ($this->business->finalize() )
            {
                $this->exibe('Processo finalizado com sucesso');
            }
            
            return true;
        }
        else
        {
            $this->exibe('A renovação retornou os seguintes erros:');
            $message = $this->business->getErrors();
            $this->exibe($message[0]->message) ;
            
            return false;
        }
    }
    
}
?>