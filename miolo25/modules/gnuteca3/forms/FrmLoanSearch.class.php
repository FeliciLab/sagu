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
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
$MIOLO->getClass('gnuteca3', 'RFID');
class FrmLoanSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('Loan', array('loanId'), 'loanId');
        parent::__construct();
    }

    public function mainFields()
    {
        $busLoanType = $this->MIOLO->getBusiness($this->module, 'BusLoanType');
        $businessLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');

        $fields[] = new MTextField('loanIdS', null, _M('Código', $this->module));
        $fields[] = new GSelection('loanTypeIdS', null, _M('Tipo de empréstimo',$this->module), $busLoanType->listLoanType());
        $fields[] = new GPersonLookup('personIdS', _M('Pessoa', $this->modules), 'person');
        $fields[] = new MTextField('itemNumberS', $this->itemNumberS->value, _M('Número do exemplar', $this->module));
        
        $businessLibraryUnit->filterOperator = TRUE;
        $businessLibraryUnit->labelAllLibrary = TRUE;
        $listLibraryUnit = $businessLibraryUnit->listLibraryUnit();

        $fields[]       = new GSelection('libraryUnitIdS',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $listLibraryUnit, null, null, null, TRUE);
        $lblDate        = new MLabel(_M('Data', $this->module) . ':');
        $beginLoanDateS = new MCalendarField('beginLoanDateS', $this->beginLoanDateS->value);
        $endLoanDateS   = new MCalendarField('endLoanDateS', $this->endLoanDateS->value);
        $fields[]       = new GContainer('hctDates', array($lblDate, $beginLoanDateS, $endLoanDateS));
        $fields[]       = new MTextField('loanOperatorS', null, _M('Operador', $this->module));

        $lblDate                    = new MLabel(_M('Data prevista da devolução', $this->module) . ':');
        $beginReturnForecastDateS   = new MCalendarField('beginReturnForecastDateS', $this->beginReturnDateS->value);
        $endReturnForecastDateS     = new MCalendarField('endReturnForecastDateS', $this->endReturnForecastDateS->value);
        $fields[]                   = new GContainer('hctDates', array($lblDate, $beginReturnForecastDateS, $endReturnForecastDateS));

        $lblDate            = new MLabel(_M('Data de devolução', $this->module) . ':');
        $beginReturnDateS   = new MCalendarField('beginReturnDateS', $this->beginReturnDateS->value);
        $endReturnDateS     = new MCalendarField('endReturnDateS', $this->endReturnDateS->value);
        $fields[]           = new GContainer('hctDates', array($lblDate, $beginReturnDateS, $endReturnDateS));

        $fields[]       = new MTextField('returnOperatorS', null, _M('Operador da devolução', $this->module));
        $fields[]       = new MIntegerField('renewalAmountS', null, _M('Quantidade de renovações', $this->module));
        $fields[]       = new MIntegerField('renewalWebAmountS', null, _M('Quantidade de renovações web', $this->module));
        $renewalWebBonusOpt = array(DB_TRUE=>_M('Sim', $this->module), DB_FALSE=>_M('Não', $this->module));
        $fields[]       = new GSelection('renewalWebBonusS', null, _M('Bônus de renovações web', $this->module), $renewalWebBonusOpt);
        $fields[]       = new GSelection('status', null, _M('Estado', $this->module), array( 1 => _M("Pendente", $this->module),2 => _M("Atrasado", $this->module) ) );
        
        $this->setFields( $fields );
    }
    
    
    /*
     * Ativar bit anti-furto
     */
    public function ativaAntiFurto($args)
    {
        $resp = RFID::addBitAgainstTheft();
        if(is_array($resp))
        {
            GPrompt::error("Erro ao ativar anti-furto. <br> ". $resp[0]);
        }else
        {
            GPrompt::information("O anti-furto foi ativado");
        }
    }
    
    /*
     * Desativar bit anti-furto
     */
    public function desativaAntiFurto($args)
    {
        $busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        
        //Obtem o LoanId
        $getLoan = explode('|loanId|~|', $args);
        $loanId = $getLoan[1];
        
        //Obtem informações do Loan
        $loan = $busLoan->getLoan($loanId, TRUE);
        
        //Verifica se o Status do livro é Emprestado
        $statusExemplar = $busExemplaryControl->getExemplaryStatus($loan->itemNumber);
        
        if($statusExemplar == DEFAULT_EXEMPLARY_STATUS_EMPRESTADO)
        {
            $resp = RFID::removeBitAgainstTheft();
            if(is_array($resp))
            {
                GPrompt::error("Erro ao desativar anti-furto. <br> ". $resp[0]);
            }else
            {
                GPrompt::information("O anti-furto do material #" . $loan->itemNumber . " foi desativado");
            }
        }
        //Caso não for, não é possivel desativar o anti-furto
        else
        {
            GPrompt::error("Só é possível desativar o anti-furto, se o material estiver emprestado.<br>
                           Material #".$loan->itemNumber);
        }
        
    }


    /**
     * Mostra as renovações relativas ao empréstimo
     *
     */
    public function showRenew()
    {
        $busRenew = $this->MIOLO->getBusiness($this->module, 'BusRenew');
        
        $search = $busRenew->getHistoryOfLoan(MIOLO::_REQUEST('loanId'));

        if ( is_array($search) ) 
        {
            $tbColumns = array(
                _M('Tipo de renovação', $this->module),
                _M('Data prevista da devolução', $this->module),
                _M('Data de renovação', $this->module),
                _M('Nova data prevista da devolução', $this->module),
                _M('Operador', $this->module)
            );

            $tb = new MTableRaw('', $search, $tbColumns);
            $tb->zebra = TRUE;
        }
        else
        {
            $tb = new MLabel (_M('Não há renovações para este empréstimo', $this->module));
        }

        $this->injectContent( $tb , true, _M('Histórico de renovação para empréstimo', $this->module) . MIOLO::_REQUEST('loanId') );
    }
    
    public function searchFunction($args)
    {
    	$data = $this->getData();
        
        if ( $data )
        {
        	$filters = '';
        	foreach ($data as $key=>$value )
        	{
        	    if ( $key == 'arrayItemTemp' || ($key == 'status' && $value == '0') || $key == 'GRepetitiveField' )
        	    {
        	    	continue;
        	    }
        	    $filters .= $value;
        	}
        }
    	
        if ( strlen($filters) == 0 )
        {
            $this->information(_M('Entre pelo menos com um filtro', $this->module), 'gnuteca.closeAction()');
        }
        else 
        {
    	   parent::searchFunction($args);
        }
    }
}
?>