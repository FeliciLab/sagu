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
 * Authenticate
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
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
 * Class created on 01/10/2008
 *
 **/


class FrmVerifyLinks extends GForm
{
    public function __construct($args)
    {
        $this->setTransaction('gtcVerifyLinks');
        parent::__construct( _M('Verificar links', $this->module) );
    }


    public function mainFields()
    {
    	$fields[] = new MDiv('divDescription', _M('Aumentar o tempo limite para testar cada link, aumenta a probabilidade de encontrar o link, mas faz com que o teste demore mais a acontecer.', $this->module), 'reportDescription');
        $fields[] = new MTextField('timeOut', ini_get('default_socket_timeout'), _M('Tempo limite, em segundos, para testar cada link', $this->module));
        $fields[] = new MTextField('tag', MARC_NAME_SERVER, _M('Etiqueta a verficar', $this->module));
        $fields[] = new MButton('btnVerifyLinks',_M('Verificar links', $this->module));
        $fields[] = new MDiv('divResult');
        $this->setFields( $fields );
    }


    public function btnVerifyLinks_click($args)
    {
        $busMaterial = $this->MIOLO->getBusiness( $this->module, 'BusMaterial');
        $data = $busMaterial->verifyLinks($args->tag, $args->timeOut);

        $fields[] = new MTableRaw( _M('Relação de links com problemas - Total: ', $this->module). count($data) , $data, array(_M('Número de controle', $this->module), _M('Vínculo', $this->module) ) );

        $this->setResponse( $fields ,'divResult');
    }
}
?>
