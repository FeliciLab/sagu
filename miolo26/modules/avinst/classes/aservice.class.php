<?php

class AService
{
    const MAIL_PERSON_ID_ATTRIBUTE = 'id';
    const MAIL_PERSON_NAME_ATTRIBUTE = 'name';
    const MAIL_PERSON_EMAIL_ATTRIBUTE = 'email';
    const MAIL_CADASTRE_SEND_GROUP_DESCRIPTIVE = 'emailSendGroupDescriptive';    
    
    /**
     * Função que retorna os tipos de atributos utilizados pelo sistema
     *
     * @return array
     */
    public static function getSystemAttributes()
    {
        $attributes[self::MAIL_PERSON_ID_ATTRIBUTE] = 'ENVIO DE EMAILS - Código da pessoa a enviar email';
        $attributes[self::MAIL_PERSON_NAME_ATTRIBUTE] = 'ENVIO DE EMAILS - Nome da pessoa a enviar email';
        $attributes[self::MAIL_PERSON_EMAIL_ATTRIBUTE] = 'ENVIO DE EMAILS - Email da pessoa a enviar email';
        $attributes[self::MAIL_CADASTRE_SEND_GROUP_DESCRIPTIVE] = 'ENVIO DE EMAILS - Descrição do grupo no processo de envio de emails';
        return $attributes;
    }
}

?>
