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
 * Class
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/

class sendReceiptMail extends GBackgroundTask implements GBackgroundTaskTemplate
{
    public function  __construct($args)
    {
        parent::__construct($args);
        $this->setLabel('Envio de email na Circulação de material');
    }

    public function execute()
    {
        $args = $this->args;
        $this->MIOLO->uses('classes/GMail.class.php','gnuteca3');

        $mail = new GMail();
        $mail->setAddress( $args->email );
        $mail->setContent( $args->content );
        $mail->setSubject( $args->subject );
        $mail->addAttachment( $args->attachment );

        if ( $mail->send() )
        {
            $this->setMessage("OK - Email enviado com sucesso para {$args->email}");
            return true;
        }
        else
        {
            $this->setMessage( "ERRO - Erro ao enviar email para {$args->email}. Motivo: " .$mail->ErrorInfo );
            return false;
        }
    }
}
?>
