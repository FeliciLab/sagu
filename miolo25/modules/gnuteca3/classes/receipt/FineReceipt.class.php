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
 * Class Gnuteca Fine Receipt;
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 28/06/2010
 *
 **/

$MIOLO = MIOLO::getInstance();
$MIOLO->uses('/classes/receipt/FineReceiptWork.class.php', 'gnuteca3');

class FineReceipt extends Receipt
{
    public $libraryName;  //$LIBRARY_UNIT_DESCRIPTION
    public $personId;     //$USER_CODE
    public $personName;   //$USER_NAME
    public $date;         //$DATE
    public $operator;     //$OPERATOR

    public function __construct($data, $isPostable = false, $isPrintable = false)
    {
        parent::__construct($data, $isPostable, $isPrintable);
        $this->title = _M('Multa');
        $this->setModel( FINE_RECEIPT, FINE_RECEIPT_WORK,EMAIL_FINE_RECEIPT_SUBJECT, EMAIL_FINE_RECEIPT_CONTENT );
    }

    public function parseData()
    {
        parent::parseData();
        
        $itens = $this->getItens();

        if ( is_array($itens ))
        {
            foreach( $itens as $line => $item )
            {
                $fineTotal = $fineTotal + $item->value;
            }
        }

        $this->setVariable('$TOTAL_FINE_VALUE', GUtil::moneyFormat($fineTotal) );
    }
}
?>