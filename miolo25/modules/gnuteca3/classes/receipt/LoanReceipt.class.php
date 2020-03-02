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
 * Class Gnuteca Loan Receipt;
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 28/06/2010
 *
 **/

$MIOLO = MIOLO::getInstance();
$MIOLO->uses('/classes/receipt/LoanReceiptWork.class.php', 'gnuteca3');

class LoanReceipt extends Receipt
{
    public function __construct($data, $isPostable = false, $isPrintable = false, $operation = 'Empréstimo')
    {
        parent::__construct($data, $isPostable, $isPrintable);
        $this->operation = $operation;
    }

    public function setData($data)
    {
        parent::setData($data);
        //add to Item list
        $this->addItem($data);
    }

    public function addItem($data)
    {
        if ( $data instanceof Receipt )
        {
            $data = $data->getData();
        }

        parent::addItem( new LoanReceiptWork( $data ) );

    }
}
?>