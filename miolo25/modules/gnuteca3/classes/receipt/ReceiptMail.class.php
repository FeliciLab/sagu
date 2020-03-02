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
 * Class used to send receipt mail. It is stored in session, to resend
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

class ReceiptMail
{
    protected $email;
    protected $content;
    protected $subject;
    protected $attachment;
    protected $error;

    public function __construct($email, $subject, $content , $attachment)
    {
        $this->email        = $email;
        $this->content      = $content;
        $this->subject      = $subject;
        $this->attachment   = $attachment;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail($email)
    {
        return $this->email;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent($content)
    {
        return $this->content;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject($subject)
    {
        return $this->subject;
    }

    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    public function getAttachment()
    {
        return $this->attachment;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function resend()
    {
        $this->send(false);
    }

    public function send( $storeInSession=true , $fake = false )
    {
        $MIOLO   = MIOLO::getInstance();
        $session = $MIOLO->getSession();

        //essa conversão é necessária em função de problemas de sessão na circulação de material
        $receiptMailStdClass = new stdClass();
        $receiptMailStdClass->email         = $this->email;
        $receiptMailStdClass->content       = $this->content;
        $receiptMailStdClass->subject       = $this->subject;
        $receiptMailStdClass->attachment    = $this->attachment;

        //stores email in session
        if ( $storeInSession )
        {
            $receipitsInSession = $session->getValue('receiptMailStorage');
            $receipitsInSession[] = $receiptMailStdClass;
            $session->setValue('receiptMailStorage',$receipitsInSession);
        }

        if ( !$fake )
        {
            $MIOLO->getClass('gnuteca3', 'backgroundTasks/GBackgroundTask');
            $send = GBackgroundTask::executeBackgroundTask('sendReceiptMail', $receiptMailStdClass);
        
            return $send;
        }
        else
        {
            return true;
        }
    }

    /**
     * Return the list of Receipts stored in session
     * @return <array of ReceiptMail> return the list of Receipts stored in session
     */
    public static function getStoredEmails()
    {
        $MIOLO   = MIOLO::getInstance();
        $session = $MIOLO->getSession();
        $emails  = $session->getValue('receiptMailStorage');

        //essa conversão é necessária em função de problemas de sessão na circulação de material
        if ( is_array( $emails ) )
        {
            foreach ( $emails as $line => $email )
            {
                $emails[$line] = new ReceiptMail($email->email, $email->subject, $email->content, $email->attachment);
            }
        }

        return $emails;
    }

    public static function resendStoredEmails()
    {
        $emails  = ReceiptMail::getStoredEmails();

        if ( is_array($emails) )
        {
            foreach ($emails as $line => $email )
            {
                $email->resend();
            }
        }

        return $emails;
    }

    /**
     * Clear the list of ReceiptMail
     */
    public static function clearStoredEmails()
    {
        $MIOLO   = MIOLO::getInstance();
        $session = $MIOLO->getSession();
        $session->setValue('receiptMailStorage', null);
    }
}

?>
