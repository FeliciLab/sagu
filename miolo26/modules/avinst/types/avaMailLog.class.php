<?php

/**
 * Type que repesenta a tabela ava_mail_log.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 27/01/2012
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class avaMailLog implements AType
{
    /**
     * @AttributeType integer
     * 
     */
    protected $idMailLog;
    /**
     * @AttributeType integer
     * 
     */
    protected $refMail;
    /**
     * @AttributeType integer
     * 
     */
    protected $refDestinatario;
    /**
     * @AttributeType text
     * 
     */
    protected $destinatario;
    /**
     * @AttributeType boolean
     * 
     */
    protected $envio;
    /**
     * @AttributeType timestamp without time zone
     * 
     */
    protected $datahora;
    /**
     * @AttributeType integer
     * 
     */
    protected $refFormulario;
    public function __construct($data = null,  $populate = false)
    {
        if ( ! empty($data) )
        {
            $this->defineData($data);

            if ( $populate )
            {
                $this->populate();
            }
        }
    }

    public function defineData($data)
    {
        $this->idMailLog = $data->idMailLog;
        $this->refMail = $data->refMail;
        $this->refDestinatario = $data->refDestinatario;
        $this->destinatario = $data->destinatario;
        $this->envio = $data->envio;
        $this->datahora = $data->datahora;
        $this->refFormulario = $data->refFormulario;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_mail_log,
                       ref_mail,
                       ref_destinatario,
                       destinatario,
                       envio,
                       TO_CHAR(datahora, \''.DB_MASK_TIMESTAMP.'\'),
                       ref_formulario
                  FROM ava_mail_log
                 WHERE id_mail_log = ?';
        $result = ADatabase::query($sql, array($this->idMailLog));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idMailLog, $this->refMail, $this->refDestinatario, $this->destinatario, $this->envio, $this->datahora, $this->refFormulario) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_mail_log,
                       ref_mail,
                       ref_destinatario,
                       destinatario,
                       envio,
                       TO_CHAR(datahora, \''.DB_MASK_TIMESTAMP.'\'),
                       ref_formulario
                  FROM ava_mail_log';
        $where.=ADatabase::generateFilters($this);

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_mail_log ';
        $result = ADatabase::query($sql);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_mail_log 
                            (id_mail_log, ref_mail, ref_destinatario, destinatario, envio, datahora, ref_formulario)
                     VALUES (?, ?, ?, ?, ?, TO_TIMESTAMP(?, \''.DB_MASK_TIMESTAMP.'\'), ?)';
        $idMailLog = ADatabase::nextVal('ava_mail_log_id_mail_log_seq');
        $params = array($idMailLog, $this->refMail, $this->refDestinatario, $this->destinatario, $this->envio, $this->datahora, $this->refFormulario);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idMailLog = $idMailLog;
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_mail_log
                   SET ref_mail = ?,
                   	   ref_destinatario = ?,
                       destinatario = ?,
                       envio = ?,
                       datahora = TO_TIMESTAMP(?, \''.DB_MASK_TIMESTAMP.'\'),
                       ref_formulario = ?
                 WHERE id_mail_log = ?';
        $params = array($this->refMail, $this->refDestinatario, $this->destinatario, $this->envio, $this->datahora, $this->refFormulario, $this->idMailLog);
        return ADatabase::execute($sql, $params);
    }

    public function delete()
    {
        if ( strlen($this->idMailLog)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_mail_log
                      WHERE id_mail_log = ?';
        $params = array($this->idMailLog);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idMailLog = null;
        }

        return $result;
    }

    public function __set($attribute,  $value)
    {
        $this->$attribute = $value;
    }

    public function __get($attribute)
    {
        return $this->$attribute;
    }

    public function getPrimaryKeyAttribute()
    {
        return 'idMailLog';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idMailLog'] = new stdClass();
        $attributesInfo['idMailLog']->columnName = 'id_mail_log';
        $attributesInfo['idMailLog']->type = 'integer';
        $attributesInfo['refMail'] = new stdClass();
        $attributesInfo['refMail']->columnName = 'ref_mail';
        $attributesInfo['refMail']->type = 'integer';
        $attributesInfo['refDestinatario'] = new stdClass();
        $attributesInfo['refDestinatario']->columnName = 'ref_destinatario';
        $attributesInfo['refDestinatario']->type = 'integer';
        $attributesInfo['destinatario'] = new stdClass();
        $attributesInfo['destinatario']->columnName = 'destinatario';
        $attributesInfo['destinatario']->type = 'text';
        $attributesInfo['envio'] = new stdClass();
        $attributesInfo['envio']->columnName = 'envio';
        $attributesInfo['envio']->type = 'boolean';
        $attributesInfo['datahora'] = new stdClass();
        $attributesInfo['datahora']->columnName = 'datahora';
        $attributesInfo['datahora']->type = 'timestamp without time zone';
        $attributesInfo['refFormulario'] = new stdClass();
        $attributesInfo['refFormulario']->columnName = 'ref_formulario';
        $attributesInfo['refFormulario']->type = 'integer';
        
        return $attributesInfo;
    }
    
    public function clearData()
    {
        unset($this->idMailLog);
        unset($this->refMail);
        unset($this->refDestinatario);
        unset($this->destinatario);
        unset($this->envio);
        unset($this->datahora);
        unset($this->refFormulario);
    }
}


?>