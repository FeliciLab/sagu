<?php 

class AGranularity
{
    // Constantes do tipo de retorno
    const GRANULARITY_RETURN_BOOLEAN = 1;
    const GRANULARITY_RETURN_ARRAY_OF_OBJECTS = 2;
    
    // Constantes do tipo de tratamento para os formulários
    const GRANULARITY_FORM_TYPE_HEADER = 'header';
    const GRANULARITY_FORM_TYPE_INDEX = 'index';
    const GRANULARITY_FORM_TYPE_ATTRIBUTE = 'attribute';
    
    // Constantes para tratamento estatístico
    const GRANULARITY_STATISTICS_TYPE_ATTRIBUTE = '1';
    const GRANULARITY_STATISTICS_TYPE_CODE = '2';
    const GRANULARITY_STATISTICS_TYPE_DESCRIPTION = '3';
    
    // Constantes para tratamento tipo de granularidade.
    const GRANULARITY_TYPE_SEM_GRANULARIDADE= '1';
    const GRANULARITY_TYPE_POR_CURSO = '2';
    const GRANULARITY_TYPE_POR_DISCIPLINA = '3';
    const GRANULARITY_TYPE_POR_SETOR= '4';
    const GRANULARITY_TYPE_OUTRO = '5';
    
    /**
     * Retorna um array com os tipos de retorno previstos para interpretação
     * do gerador de formulários
     * @return string 
     */
    public static function getGranularityReturn()
    {
        // Constantes do tipo de retorno
        $tipos[self::GRANULARITY_RETURN_BOOLEAN] = 'Booleano';
        $tipos[self::GRANULARITY_RETURN_ARRAY_OF_OBJECTS] = 'Array de objetos';
        return $tipos;
    }
    
    private static function getFormGranularityTreatment()
    {
        // Constantes do tipo de retorno
        $tipos[self::GRANULARITY_FORM_TYPE_HEADER] = 'Cabeçalho';
        $tipos[self::GRANULARITY_FORM_TYPE_INDEX] = 'Índice';
        $tipos[self::GRANULARITY_FORM_TYPE_ATTRIBUTE] = 'Atributo';
        return $tipos;
    }
    
    private static function getStatisticsGranularityTreatment()
    {
        // Constantes do tipo de retorno
        $tipos[self::GRANULARITY_STATISTICS_TYPE_ATTRIBUTE] = 'Atributo';
        $tipos[self::GRANULARITY_STATISTICS_TYPE_CODE] = 'Índice';
        $tipos[self::GRANULARITY_STATISTICS_TYPE_DESCRIPTION] = 'Descrição';
        return $tipos;
    }
    
    /**
     *  Obtém os tipos de granularidades.
     * 
     * @return array Vetor com as descrições das granularidades.
     */
    public static function getGranularityTypes()
    {
        $module = MIOLO::getCurrentModule();
        
        $tipos = array();
        $tipos[self::GRANULARITY_TYPE_SEM_GRANULARIDADE] = _M('Sem granularidade', $module);
        $tipos[self::GRANULARITY_TYPE_POR_CURSO] = _M('Por curso', $module);
        $tipos[self::GRANULARITY_TYPE_POR_DISCIPLINA] = _M('Por disciplina', $module);
        $tipos[self::GRANULARITY_TYPE_POR_SETOR] = _M('Por setor', $module);
        $tipos[self::GRANULARITY_TYPE_OUTRO] = _M('Outro', $module);
        
        return $tipos;
    }
    
    /**
     * Retorna os campos da granularidade para a inserção/edição das informações
     */
    public static function returnGranularityReturnFields($granularityReturn)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('classes/aservice.class.php', 'avinst');
        switch ($granularityReturn)
        {
            case self::GRANULARITY_RETURN_ARRAY_OF_OBJECTS:
            {
                //
                // Subdetail das opções do formulário
                //
                // Campos da subdetail
                $sdtFields[] = new MTextField('atributo', NULL, 'Atributo', 10);
                $sdtFields[] = new MTextField('descritivo', null, 'Descritivo', '70');
                $sdtFields[] = new MSelection('tipoDeTratamento', null, 'Tipo de tratamento para formulários', self::getFormGranularityTreatment());
                $sdtFields[] = new MTextField('ordem', null, 'Ordem', 10);
                
                // Colunas da grid da Subdetail
                $sdtFieldsColumns[] = new MGridColumn('Atributo', 'left', false, '60%', true, 'atributo');
                $sdtFieldsColumns[] = new MGridColumn('Descritivo', 'left', false, '20%', true, 'descritivo');
                $sdtFieldsColumns[] = new MGridColumn('Tipo de tratamento', 'left', false, '20%', true, 'tipoDeTratamento');
                $sdtFieldsColumns[] = new MGridColumn('Ordem', 'left', false, '20%', true, 'ordem');
                
                $sFields[] = new MSpan(null,'&nbsp','label');
                $sFields[] = $sdt = new MSubDetail('opcoesFormulario', _M('Interpretação dos campos para formulários'), $sdtFieldsColumns, $sdtFields);
                $sdt->setAttribute('style','width: 70%');
                $fields[] = new MDiv(null, $sFields, 'mFormRow');
                unset($sdtFields);
                unset($sdtFieldsColumns);
                unset($sFields);
                
                //
                // Subdetail das opções das estatísticas
                //
                // Campos da subdetail
                $sdtFields[] = new MTextField('atributo', NULL, 'Atributo', 10);
                $sdtFields[] = new MTextField('descritivo', null, 'Descritivo', '70');
                $sdtFields[] = new MSelection('tipoDeTratamento', null, 'Tipo de tratamento', self::getStatisticsGranularityTreatment());
                
                // Colunas da grid da Subdetail
                $sdtFieldsColumns[] = new MGridColumn('Atributo', 'left', false, '60%', true, 'atributo');
                $sdtFieldsColumns[] = new MGridColumn('Descritivo', 'left', false, '20%', true, 'descritivo');
                $sdtFieldsColumns[] = new MGridColumn('Tipo de tratamento', 'left', false, '20%', true, 'tipoDeTratamento');
                
                $sFields[] = new MSpan(null,'&nbsp','label');
                $sFields[] = $sdt = new MSubDetail('opcoesEstatisticas', _M('Interpretação dos campos para estatísticas'), $sdtFieldsColumns, $sdtFields);
                $sdt->setAttribute('style','width: 70%');
                $fields[] = new MDiv(null, $sFields, 'mFormRow');
                unset($sdtFields);
                unset($sdtFieldsColumns);
                unset($sFields);
                
                //
                // Subdetail das opções de e-mails
                //
                // Campos da subdetail
                $sdtFields[] = new MTextField('atributo', NULL, 'Atributo', 10);
                $sdtFields[] = new MTextField('descritivo', null, 'Descritivo', '70');
                $sdtFields[] = new MSelection('tipoDeTratamento', null, 'Tipo de tratamento', AService::getSystemAttributes());
                
                // Colunas da grid da Subdetail
                $sdtFieldsColumns[] = new MGridColumn('Atributo', 'left', false, '60%', true, 'atributo');
                $sdtFieldsColumns[] = new MGridColumn('Descritivo', 'left', false, '20%', true, 'descritivo');
                $sdtFieldsColumns[] = new MGridColumn('Tipo de tratamento', 'left', false, '20%', true, 'tipoDeTratamento');
                
                $sFields[] = new MSpan(null,'&nbsp','label');
                $sFields[] = $sdt = new MSubDetail('opcoesEmail', _M('Interpretação dos campos para emails'), $sdtFieldsColumns, $sdtFields);
                $sdt->setAttribute('style','width: 70%');
                $fields[] = new MDiv(null, $sFields, 'mFormRow');
                unset($sdtFields);
                unset($sdtFieldsColumns);
                unset($sFields);
            }
            default:
                $fields[] = new MHiddenField('opcoesFormulario', null);
                $fields[] = new MHiddenField('opcoesEstatisticas', null);
                $fields[] = new MHiddenField('opcoesEmail', null);
        }
        return $fields;
    }

    /**
     * Trata os campos para gravar na base de dados
     */
    public static function parseFields($data)
    {
        switch ($data->tipo)
        {
            case self::GRANULARITY_RETURN_ARRAY_OF_OBJECTS:
            {
                //
                // Subdetail: Formulários
                //
                $sdtData = MSubDetail::getData('opcoesFormulario');
                if (is_array($sdtData))
                {
                    foreach ( $sdtData as $sdtRow )
                    {
                        if( $sdtRow->dataStatus != MSubDetail::STATUS_REMOVE )
                        {
                            $optionObject = new stdClass();
                            $optionObject->atributo = $sdtRow->atributo;
                            $optionObject->descritivo = $sdtRow->descritivo;
                            $optionObject->tipoDeTratamento = $sdtRow->tipoDeTratamento;
                            $optionObject->ordem = $sdtRow->ordem;
                            $optionsFormulario[] = $optionObject;
                        }
                    }
                    $options->opcoesFormulario = $optionsFormulario;
                }
                unset($sdtData);
                unset($sdtRow);
                unset($optionObject);
                
                //
                // Subdetail: Estatísticas
                //
                $sdtData = MSubDetail::getData('opcoesEstatisticas');
                if (is_array($sdtData))
                {
                    foreach ( $sdtData as $sdtRow )
                    {
                        if( $sdtRow->dataStatus != MSubDetail::STATUS_REMOVE )
                        {
                            $optionObject = new stdClass();
                            $optionObject->atributo = $sdtRow->atributo;
                            $optionObject->descritivo = $sdtRow->descritivo;
                            $optionObject->tipoDeTratamento = $sdtRow->tipoDeTratamento;
                            $optionsEstatisticas[] = $optionObject;
                        }
                    }
                    $options->opcoesEstatisticas = $optionsEstatisticas;
                }
                unset($sdtData);
                unset($sdtRow);
                unset($optionObject);
                
                //
                // Subdetail: emails
                //
                $sdtData = MSubDetail::getData('opcoesEmail');
                if (is_array($sdtData))
                {
                    foreach ( $sdtData as $sdtRow )
                    {
                        if( $sdtRow->dataStatus != MSubDetail::STATUS_REMOVE )
                        {
                            $optionObject = new stdClass();
                            $optionObject->atributo = $sdtRow->atributo;
                            $optionObject->descritivo = $sdtRow->descritivo;
                            $optionObject->tipoDeTratamento = $sdtRow->tipoDeTratamento;
                            $optionsEmail[] = $optionObject;
                        }
                    }
                    $options->opcoesEmail = $optionsEmail;
                }
                unset($sdtData);
                unset($sdtRow);
                unset($optionObject);
                
                // Retorna o objeto com os três conjuntos de arrays
                return $options;
                break;
            }
            default:
                return '';
                break;
        }
    }
    
    /*
     * Chamada para executar o parse nas opções de amostragem das informações
     */
    public static function parseFormOptions($options)
    {
        $options = unserialize($options);
        if (is_array($options->opcoesFormulario))
        {
            foreach ($options->opcoesFormulario as $option)
            {
                if (is_object($option))
                {
                    $ordem = $option->ordem;
                    
                    if ($option->tipoDeTratamento == AGranularity::GRANULARITY_FORM_TYPE_INDEX)
                    {
                        if (is_null($ordem))
                        {
                            $indices[] = $option;
                        }
                        else
                        {
                            $indices[$ordem] = $option;
                        }
                    }
                    elseif ($option->tipoDeTratamento == AGranularity::GRANULARITY_FORM_TYPE_HEADER)
                    {
                        if (is_null($ordem))
                        {
                            $cabecalhos[] = $option;
                        }
                        else
                        {
                            $cabecalhos[$ordem] = $option;
                        }   
                    }
                    elseif ($option->tipoDeTratamento == AGranularity::GRANULARITY_FORM_TYPE_ATTRIBUTE)
                    {
                        if (is_null($ordem))
                        {
                            $atributos[] = $option;
                        }
                        else
                        {
                            $atributos[$ordem] = $option;    
                        }
                    }
                           
                }
            }
            if (is_array($indices))
            {
                ksort($indices);
            }
            if (is_array($cabecalhos))
            {
                ksort($cabecalhos);
            }
            $return['indices'] = $indices;
            $return['cabecalhos'] = $cabecalhos;
            $return['atributos'] = $atributos;
            return $return;
        }
        return null;
    }
    
    //
    // Trata os campos para ser utilizado pelas rotinas estatísticas
    //
    public static function parseStatisticsOptions($options)
    {
        $options = unserialize($options);
        return $options->opcoesEstatisticas;
    }
    
    //
    // Trata todos os campos e retorna as opções em sua forma bruta
    //
    public static function parseOptions($options)
    {
        $options = unserialize($options);
        return $options;
    }
}
?>