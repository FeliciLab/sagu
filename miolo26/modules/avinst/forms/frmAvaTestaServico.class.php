<?php

/**
 * Formulário para replicar registros nas tabelas ava_formulario, ava_bloco, ava_bloco_questoes.
 *
 * @author André Chagas Dias [andre@solis.coop.br]
 *
 * \b Maintainers: \n
 * André Chagas Dias [andre@solis.coop.br]
 *
 * @since
 * Creation date 29/11/2011
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */
class frmAvaTestaServico extends AForm
{
    // verificacao para ativar o eventHandler
    public static $doEventHandler;
    
    /**
     * Construtor do formulário.
     */
    public function __construct()
    {
        parent::__construct('Testar serviço');
    }

    /**
     * Criar campos do formulário.
     */
    public function createFields()
    {
        parent::createFields();
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $fields[] = MMessage::getMessageContainer();
        $fields[] = new MDiv('<br />');
        $fields[] = new MLookupContainer('idServico', null, 'Serviço', $module, 'Servico');
        $fields[] = new MMultiLineField('parametros', null, 'Parâmetros', 70, 5, 70);
        $validators[] = new MRequiredValidator('idServico', 'Serviço');
        
        $button[] = new MButton('chamaServico', 'Chama serviço');
        $div = new MDiv('divButtons', $button);
        $div->addAttribute('align', 'center');
        $fields[] = $div;
        $fields[] = new MDiv('divRetorno', null);
        $this->setFields($fields);
        $this->setValidators($validators);
    }
    
    //
    //
    //   
    public function chamaServico_click()
    {
        $validate = true;
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/avaServico.class.php', 'avinst');
        $parametros = $this->getFormValue('parametros');
        if (strlen($parametros)>0)
        {
            $parametros = explode("\n", $parametros);
            foreach ($parametros as $key => $parametro)
            {
                $parserData = explode("=", $parametro, 2);
                if (count($parserData) != 2)
                {
                    $validate = false;
                }
                else
                {
                    $parser[$parserData[0]] = $parserData[1];
                }
            }
        }
        if ($validate == true)
        {
            $filter = new stdClass();
            $filter->idServico = $this->getFormValue('idServico');
            $avaServico = new avaServico($filter, true);
            
            $result = $avaServico->chamaServico($parser, false);
            
            $resultFields = array();
            $fieldL['caption1'] = new MLabel('Localização:');
            $fieldL['caption1']->setClass('mCaption');
            $field[] = new MSpan(null, $fieldL, 'label');
            $field['span1'] = new MTextField('localizacaoServico', $avaServico->localizacao, null, 150);
            $field['span1']->setReadOnly(true);
            $resultFields[] = new MDiv('localizacaoData', $field, 'mFormRow');
            unset($field);
            unset($fieldL);

            $fieldL['caption2'] = new MLabel('Método:');
            $fieldL['caption2']->setClass('mCaption');
            $field[] = new MSpan('metodoServicoLabel', $fieldL, 'label');
            $field['span2'] = new MTextField('metodoServico', $avaServico->metodo, null, 150);
            $field['span2']->setReadOnly(true);
            $resultFields[] = new MDiv('vctResult', $field, 'mFormRow');
            unset($field);
            unset($fieldL);
            
            $span = new MSpan('spanDebug', '<pre>'.substr(var_export($result, true), 0, 1000000).'</pre>');
            $resultFields[] = new MPanel('debugPanel', 'Retorno do webservice', array($span));
            $this->setResponse($resultFields, 'divRetorno');
        }
        else
        {
            new MMessageWarning('Falha na interpretação dos parâmetros, por favor, verifique a passagem dos parâmetros');
        }
    }
}
?>