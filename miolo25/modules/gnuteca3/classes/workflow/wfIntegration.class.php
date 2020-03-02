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
 * @author Lucas Gerhardt [lucas_gerhardt@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 14/04/2014
 *
 * */
class wfIntegration
{
    /**
     * Dados passados para a função
     * 
     * @var stdClass
     */
    protected $data;

    /**
     * Objeto GFunction
     *
     * @var GFunction
     */
    protected $gFunction;

    /**
     * Definição do email administrativo
     *
     * @var string
     */
    protected $adminMail;
    /**
     * Variável que identidica o código do estado finalizado pelo Participante.
     *
     * @var integer
     */
    //protected static $finalizedStatusClient = 200006;
    /**
     * Variável que identidica o código do estado finalizado pela Biblioteca Integradora.
     *
     * @var integer
     */
    protected static $finalizedStatus = 200007;
    
    public $busIntegrationClient;
    /**
     * Passa pelo construtor toda vez e define os parametros básicos.
     *
     * @param stdClass $data
     */
    public function __construct( $data )
    {
        $MIOLO = MIOLO::getInstance();
        $busIntegrationClient = $MIOLO->getBusiness('gnuteca3', 'BusIntegrationClient');
        
        $this->setData($data);
        
        $variables['$nameClient'] = $this->data->nameClient;
        $variables['$emailClient'] = $this->data->emailClient;
        $variables['$nameServer'] = $this->data->nameServer;
        $variables['$AMOUNT_MATERIALS'] = $busIntegrationClient->getInitialAmountClientMaterials($this->data->tableId);
        $variables['$AMOUNT_EXEMPLARYS'] = $busIntegrationClient->getInitialAmountClientExemplarys($this->data->tableId);
        
        $variables['$justify'] = $data->comment;
        
        $this->gFunction = new GFunction();
        $this->gFunction->setVariables( $variables );
    }
    
     public function getData()
    {
        return $this->data;
    }

    /**
     * Define os dados.
     * Pode ser extendido para modificar os dados.
     *
     * @param stdClass $data
     */
    public function setData( $data )
    {
        $this->data = $data;
    }

    /**
     * Inicializa solicitação de compras enviando email para administrador e solicitante
     *
     * @return boolean
     */
    public function initialize()
    {
        $MIOLO = MIOLO::getInstance();
        
        if($this->data->emailServer)
        {        
            $mail = new GMail(); 
            $mail->addAddress( $this->data->emailServer );
            $mail->setSubject( EMAIL_BIBLIO_VIRTUAL_INITIALIZE_SUBJECT );
            $mail->setContent( $this->gFunction->interpret( EMAIL_BIBLIO_VIRTUAL_INITIALIZE_CONTENT) );
            $mail->setIsHtml( true );

            $mail->send();
        }
        
        return true;
    }
    public function aprove() 
    {
        $MIOLO = MIOLO::getInstance();
        
        $mail = new GMail();
        $mail->addAddress( $this->data->emailClient );
        $mail->setSubject( EMAIL_BIBLIO_VIRTUAL_APROVE_SUBJECT );
        $mail->setContent( $this->gFunction->interpret( EMAIL_BIBLIO_VIRTUAL_APROVE_CONTENT) );
        $mail->setIsHtml( true );

        $mail->send();
        
        return true;
    }

    public function cancel() 
    {
        $MIOLO = MIOLO::getInstance();
        if ( !$this->data->comment )
        {
            throw new Exception ( _M('É necessário informar um motivo para o cancelamento!') );
        }
        
        $mail = new GMail();
        $mail->addAddress( $this->data->emailClient );
        $mail->setSubject( EMAIL_BIBLIO_VIRTUAL_CANCEL_SUBJECT );
        $mail->setContent( $this->gFunction->interpret( EMAIL_BIBLIO_VIRTUAL_CANCEL_CONTENT) );
        $mail->setIsHtml( true );

        $mail->send();
        
        return true;
    }

    public function deny() 
    {
        $MIOLO = MIOLO::getInstance();
        
        return true;
    }

    public function synchronize() 
    {
        $MIOLO = MIOLO::getInstance();
        
        return true;
    }    

    public function finalize() 
    {
        $MIOLO = MIOLO::getInstance();
        
        $mail = new GMail();
        $mail->addAddress( $this->data->emailClient );
        $mail->setSubject( EMAIL_BIBLIO_VIRTUAL_INITIALIZE_SUBJECT );
        $mail->setContent( $this->gFunction->interpret( EMAIL_BIBLIO_VIRTUAL_INITIALIZE_CONTENT) );
        $mail->setIsHtml( true );

        $mail->send();
        
        return true;
    }
    
}
?>
