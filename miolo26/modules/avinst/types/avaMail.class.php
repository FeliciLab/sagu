<?php

/**
 * Type que repesenta a tabela ava_mail.
 *
 * @author Andre Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * Andre Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 24/01/2012
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
class avaMail implements AType
{
    const TIPO_ENVIO_IMEDIATO = 1; // Envio imediato
    const TIPO_ENVIO_AGENDADO = 2; // Envio agendado
    const GRUPO_ENVIO_RESPONDENTES = 1; // Envio para o grupo "Respondentes"
    const GRUPO_ENVIO_NAO_RESPONDENTES = 2; // Envio para o grupo "Não respondentes"
    const GRUPO_ENVIO_AMBOS = 3; // Envio para o grupo "Ambos"
    const HEADERS_HTML = "MIME-Version: 1.0\r\nContent-type: text/html; charset=utf-8;\r\n";
    
    /**
     * @AttributeType integer
     * 
     */
    protected $idMail;
    /**
     * @AttributeType integer
     * 
     */
    protected $refAvaliacao;
    /**
     * @AttributeType integer
     * 
     */
    protected $refPerfil;
    /**
     * @AttributeType integer
     * 
     */
    protected $refFormulario;
    /**
     * @AttributeType timestamp without time zone
     * 
     */
    protected $datahora;
    /**
     * @AttributeType text
     * 
     */
    protected $assunto;
    /**
     * @AttributeType text
     * 
     */
    protected $conteudo;
    /**
     * @AttributeType integer
     * 
     */
    protected $tipoEnvio;
    /**
     * @AttributeType integer
     * 
     */
    protected $grupoEnvio;
    /**
     * @AttributeType integer
     * 
     */
    protected $processo; // Numero do processo rodando no SO (PID)
    /**
     * @AttributeType timestamp
     * 
     */
    protected $horarioInicial;
    /**
     * @AttributeType timestamp
     * 
     */
    protected $horarioFinal;
    /**
     * @AttributeType text
     * 
     */
    protected $cco;
    
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
        $this->idMail = $data->idMail;
        $this->refAvaliacao = $data->refAvaliacao;
        $this->refPerfil = $data->refPerfil;
        $this->refFormulario = $data->refFormulario;
        $this->datahora = $data->datahora;
        $this->assunto = $data->assunto;
        $this->conteudo = $data->conteudo;
        $this->tipoEnvio = $data->tipoEnvio;
        $this->grupoEnvio = $data->grupoEnvio;
        $this->processo = $data->processo;
        $this->horarioInicial = $data->horarioInicial;
        $this->horarioFinal = $data->horarioFinal;
        $this->cco = $data->cco;
    }

    public function populate()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $sql = 'SELECT id_mail,
        			   ref_avaliacao,
                       ref_perfil,
                       ref_formulario,
                       TO_CHAR(datahora, \''.DB_MASK_TIMESTAMP.'\'),
                       assunto,
                       conteudo,
                       tipo_envio,
                       grupo_envio,
                       processo,
                       cco
                  FROM ava_mail
                 WHERE id_mail = ?';
        $result = ADatabase::query($sql, array($this->idMail));

        if ( !strlen($result[0][0]) )
        {
            throw new Exception(_M('Registro inexistente.', $module));
        }

        list($this->idMail, $this->refAvaliacao, $this->refPerfil, $this->refFormulario, $this->datahora, $this->assunto, $this->conteudo, $this->tipoEnvio, $this->grupoEnvio, $this->processo, $this->cco) = $result[0];
    }

    public function search( $returnType  =  ADatabase::RETURN_ARRAY )
    {
        $sql = 'SELECT id_mail,
        			   ref_avaliacao,
                       ref_perfil,
                       ref_formulario,
                       TO_CHAR(datahora, \''.DB_MASK_TIMESTAMP.'\'),
                       assunto,
                       conteudo,
                       tipo_envio,
                       grupo_envio,
                       processo,
                       cco
                  FROM ava_mail';
        $where.=ADatabase::generateFilters($this);
        
        if( strlen($this->horarioInicial) > 0 )
        {
            $where .= ' AND ava_mail.datahora >= ?';
            $args[] = $this->horarioInicial;
        }
        
        if( strlen($this->horarioFinal) > 0 )
        {
            $where .= ' AND ava_mail.datahora <= ?';
            $args[] = $this->horarioFinal;
        }

        if ( strlen($where)  >  0 )
        {
            $sql.=' WHERE '.substr($where, 5);
        }

        $sql.=' ORDER BY id_mail ';
        $result = ADatabase::query($sql,$args);

        if ( $returnType  ==  ADatabase::RETURN_TYPE )
        {
            $result = AVinst::getArrayOfTypes($result, __CLASS__);
        }

        return $result;
    }

    public function insert()
    {
        $sql = 'INSERT INTO ava_mail 
                            (id_mail, ref_avaliacao, ref_perfil, ref_formulario, datahora, assunto, conteudo, tipo_envio, grupo_envio, processo, cco)
                     VALUES (?, ?, ?, ?, TO_TIMESTAMP(?, \''.DB_MASK_TIMESTAMP.'\'), ?, ?, ?, ?, ?, ?)';
        $idMail = ADatabase::nextVal('ava_mail_id_mail_seq');
        $params = array($idMail, $this->refAvaliacao, $this->refPerfil, $this->refFormulario, $this->datahora, $this->assunto, $this->conteudo, $this->tipoEnvio, $this->grupoEnvio, $this->processo, $this->cco);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idMail = $idMail;
            
            if( $this->tipoEnvio == self::TIPO_ENVIO_IMEDIATO ) // Se for envio imediato
            {
                $MIOLO = MIOLO::getInstance();
                // Dispara o processo de evio de e-mail em background http://www.ptpatv.info/index.php?db=so&id=45953
                $pid = exec("php {$MIOLO->getModulePath($MIOLO->getCurrentModule(),'crontabs')}/emails.php $this->idMail > /tmp/amail.log 2>&1 & echo $!");
                // Atualiza pid do lote de emails com o processo que esta rodando em background
                $sql = 'UPDATE ava_mail SET processo = ? WHERE id_mail = ?';
                $result = ADatabase::execute($sql, array($pid,$idMail));
            }
        }

        return $result;
    }

    public function update()
    {
        $sql = 'UPDATE ava_mail
                   SET ref_avaliacao = ?,
                   	   ref_perfil = ?,
                   	   ref_formulario = ?,
                       datahora = TO_TIMESTAMP(?, \''.DB_MASK_TIMESTAMP.'\'),
                       assunto = ?,
                       conteudo = ?,
                       tipo_envio = ?,
                       grupo_envio = ?,
                       processo = ?,
                       cco = ?
                 WHERE id_mail = ?';
        $params = array($this->refAvaliacao, $this->refPerfil, $this->refFormulario, $this->datahora, $this->assunto, $this->conteudo, $this->tipoEnvio, $this->grupoEnvio, $this->processo, $this->cco, $this->idMail);
        $result = ADatabase::execute($sql, $params);
        
        if ( $result )
        {
            if( $this->tipoEnvio == self::TIPO_ENVIO_IMEDIATO ) // Se for envio imediato
            {
                $MIOLO = MIOLO::getInstance();
                // Dispara o processo de evio de e-mail em background http://www.ptpatv.info/index.php?db=so&id=45953
                $pid = exec("php {$MIOLO->getModulePath($MIOLO->getCurrentModule(),'crontabs')}/emails.php $this->idMail > /tmp/amail.log 2>&1 & echo $!");
                // Atualiza pid do lote de emails com o processo que esta rodando em background
                $sql = 'UPDATE ava_mail SET processo = ? WHERE id_mail = ?';
                $result = ADatabase::execute($sql, array($pid,$this->idMail));
            }
        }
        
        return $result;        
    }

    public function delete()
    {
        if ( strlen($this->idMail)  ==  0 )
        {
            throw new Exception(_M('Não é possível excluir um registro que ainda não foi salvo.', $module));
        }

        $sql = 'DELETE FROM ava_mail
                      WHERE id_mail = ?';
        $params = array($this->idMail);
        $result = ADatabase::execute($sql, $params);

        if ( $result )
        {
            $this->idMail = null;
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
        return 'idMail';
    }

    public function generateAttributesInfo()
    {
        $attributesInfo['idMail'] = new stdClass();
        $attributesInfo['idMail']->columnName = 'id_mail';
        $attributesInfo['idMail']->type = 'integer';
        $attributesInfo['refAvaliacao'] = new stdClass();
        $attributesInfo['refAvaliacao']->columnName = 'ref_avaliacao';
        $attributesInfo['refAvaliacao']->type = 'integer';
        $attributesInfo['refPerfil'] = new stdClass();
        $attributesInfo['refPerfil']->columnName = 'ref_perfil';
        $attributesInfo['refPerfil']->type = 'integer';
        $attributesInfo['refFormulario'] = new stdClass();
        $attributesInfo['refFormulario']->columnName = 'ref_formulario';
        $attributesInfo['refFormulario']->type = 'integer';
        $attributesInfo['datahora'] = new stdClass();
        $attributesInfo['datahora']->columnName = 'datahora';
        $attributesInfo['datahora']->type = 'timestamp without time zone';
        $attributesInfo['assunto'] = new stdClass();
        $attributesInfo['assunto']->columnName = 'assunto';
        $attributesInfo['assunto']->type = 'text';
        $attributesInfo['conteudo'] = new stdClass();
        $attributesInfo['conteudo']->columnName = 'conteudo';
        $attributesInfo['conteudo']->type = 'text';
        $attributesInfo['tipoEnvio'] = new stdClass();
        $attributesInfo['tipoEnvio']->columnName = 'tipo_envio';
        $attributesInfo['tipoEnvio']->type = 'integer';
        $attributesInfo['grupoEnvio'] = new stdClass();
        $attributesInfo['grupoEnvio']->columnName = 'grupo_envio';
        $attributesInfo['grupoEnvio']->type = 'integer';
        $attributesInfo['processo'] = new stdClass();
        $attributesInfo['processo']->columnName = 'processo';
        $attributesInfo['processo']->type = 'integer';
        $attributesInfo['cco'] = new stdClass();
        $attributesInfo['cco']->columnName = 'cco';
        $attributesInfo['cco']->type = 'text';
        return $attributesInfo;
    }
    
    public static function getSendTypes()
    {
        return array( self::TIPO_ENVIO_IMEDIATO => 'Imediato', self::TIPO_ENVIO_AGENDADO => 'Agendado' );
    }

    public static function getSendGroups()
    {
        return array( self::GRUPO_ENVIO_AMBOS => 'Ambos', self::GRUPO_ENVIO_RESPONDENTES => 'Respondentes', self::GRUPO_ENVIO_NAO_RESPONDENTES => 'Não respondentes' );
    }
    
    public function obterTotalEnviados()
    {
        $sql = 'SELECT count(*)
                  FROM ava_mail_log
                 WHERE ref_mail = ?
                   AND envio = ?';
        $result = ADatabase::query($sql,array($this->idMail,DB_TRUE));
        return $result[0][0];
    }
    
    public function obterTotalNaoEnviados()
    {
        $sql = 'SELECT count(*)
                  FROM ava_mail_log
                 WHERE ref_mail = ?
                   AND envio IS NULL';
        $result = ADatabase::query($sql,array($this->idMail));
        return $result[0][0];
    }
    
    public function processoRodando()
    {
        $estado = exec("ps {$this->processo}"); // Se o processo está rodando
        return strlen(stristr($estado, 'avinst/crontabs/emails.php')) > 0;        
    }
}
?>