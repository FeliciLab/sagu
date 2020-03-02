<?php
/**
 * Tarefa de envio de relatorios por e-mail.
 *
 * @author Guilherme Soldateli [guilherme@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Guilherme Soldateli [guilherme@solis.com.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2009
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solucoes Livres \n
 * The Gnuteca3 Development Team
 *
 * \b CopyLeft: \n
 * CopyLeft (L) 2007 SOLIS - Cooperativa de Solucoes Livres \n
 *
 * \b License: \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html
 *
 * \b History: \n
 * See history in SVN repository: http://gnuteca.solis.coop.br
 *
 */

class SendReport extends GTask
{

    /**
     * METODO CONSTRUCT É OBRIGATÓRIO, POIS A CLASSE DE SCHEDULE TASK SEMPRE VAI PASSAR O $MIOLO COMO PARAMETRO
     *
     * @param OBJECT $MIOLO
     */
    function __construct($MIOLO, $myTaskId)
    {
        parent::__construct($MIOLO, $myTaskId);
    }


    /**
     * MÉTODO OBRIGATORIO.
     * ESTE METODO SERA CHAMADO PELA CLASSE SCHEDULE TASK PARA EXECUTAR A TAREFA
     *
     * @return boolean
     */
    public function execute()
    {

        
        
        $MIOLO = MIOLO::getInstance();

       
        //Se id do relatorio nao estiver vazio
        if ( empty($this->parameters[0]))
        {
            throw new Exception("Id do relatório está vazio!");
        }
        else
        {
            $reportId = $this->parameters[0];
        }
        
        //Se nao tiver e-mail vazio adiciona
        if ( !empty($this->parameters[1]))
        {
            if ( strstr($this->parameters[1], ',') )
            {
                $addresses = explode(',', $this->parameters[1]);
            }
            else
            {
                $addresses[] = $this->parameters[1];
            }
        }
        else
        {
            throw new Exception("E-mail de envio do relatório está vazio!");
        }
        
        //Se nao tiver tipo do relatorio
        if ( !empty($this->parameters[2]))
        {
            if ( strstr($this->parameters[2], ',') )
            {
                $reportTypes = explode(',', $this->parameters[2]);
            }
            else
            {
                $reportTypes[] = $this->parameters[2];
            }
        }
        else
        {
            throw new Exception("Tipo de formato do relatório está vazio!");
        }


        $MIOLO->getClass('gnuteca3', 'GMail');
        $MIOLO->getClass('gnuteca3', 'GReport');     
        $busReport = $MIOLO->getBusiness('gnuteca3', 'BusReport');
        
        $gReport = new GReport();
        $gMail = new GMail();
        
        $reportInfo = $busReport->getReport($reportId);
        
        //Obtem os parametros do relatorio.
        $reportArguments = new stdClass();

        //Prepara o objeto com os valores padroes dos parametros do relatorio.
        foreach( $reportInfo->parameters as $reportParameter )
        {
            if (!empty($reportParameter->defaultValue) )
            {
                $reportArguments->{$reportParameter->identifier} = $reportParameter->defaultValue;
            }
        }

        //Gera relatorio sem formato definido.
        $gReport->executeReport($reportId, $reportArguments, null);        
        
        
        //Se nao tiver nenhum registro
        if ( empty($gReport->result) )
        {
            //Gera log dizendo que o relatorio nao foi enviado pois nao haviam dados para mostrar.
            throw new Exception(_M('Nenhum registro retornado pelo relatório.', 'gnuteca3'));
        }
        
        //Manda gerar relatorio e envia-lo por e-mail(para cada tipo de formato, CSV,ODT,PDF).
        foreach ( $reportTypes as $reportType )
        {
            $gReport->generateReportAs($reportType);
            //Se o formato de exibicao nao existir
            if ( empty($gReport->output) )
            {
                //Gera log dizendo que formato nao existe.
                throw new Exception(_M('Impossível gerar relatório no formato @1.', 'gnuteca3',$reportType));
            }

            //Anexa arquivo gerado pelo generateReportAs
            $gMail->addAttachment($gReport->getReportFilePath($reportType));
        }


        //O assunto do e-mail deve ser a descricao da tarefa.
        $description = strlen($this->myTask->ScheduleTaskDescription) ? $this->myTask->ScheduleTaskDescription : $this->myTask->description;
        $gMail->setSubject($description);
        
        //O conteudo do e-mail deve ser uma preferencia de e-mail para relatorio REPORT_MAIL_BODY.
        $gf   = new GFunction();
        $gf->setVariable('$LN', "\n");
        $gf->setVariable('$REPORT_DESCRIPTION', $reportInfo->description);
        $gf->setVariable('$REPORT_TITLE', $reportInfo->Title);
        $content = $gf->interpret(REPORT_MAIL_BODY);
        $gMail->setContent($content);        
        
        //Para cada e-mail configurado envia a mensagem.
        foreach ( $addresses as $address )
        {
            //Se for um endereco de e-mail valido.
            if ( filter_var($address, FILTER_VALIDATE_EMAIL) )
            {
                $gMail->setAddress($address);
                $gMail->send();
            }
            else
            {
                $invalidAddresses[] = $address;
            }
        }
        
        if ( count($invalidAddresses) > 0 )
        {
            throw new Exception(_M('E-mails não enviados para : @1.', 'gnuteca3',implode(', ', $invalidAddresses)));
        }
        
        return true;
    }

}

?>
