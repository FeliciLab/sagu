<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * @author Equipe SAGU [sagu@solis.coop.br]
 *
 * \b Maintainers \n
 * Equipe SAGU [sagu@solis.coop.br]
 *
 * @since
 * Class created on 02/03/2015
 *
 */
class FrmNotasModulo extends SManagementForm
{

    private $countTable;
    private $moduloid;
    private $notasModuloId = array();
    private $residente;

    private $allowUpdate;
    private $allowInsert;
    private $isReadOnlyMode;

    public $_columns;


    public function __construct()
    {

        $module = SAGU::getFileModule(__FILE__);
        $this->_columns = array(
            _M('Módulo', $module),
            _M('Nota de Assiduidade', $module),
            _M('Nota atividade de produto', $module),
            _M('Nota de avaliação de desempenho', $module),
            _M('Nota geral', $module)
        );

        parent::__construct(new ResNotasModulo(), array('notapormoduloid'));

        //Acessado pelo módulo de serviços
        if ( SAGU::userIsFromServices() )
        {
            // Desabilita a Toolbar
            $this->disableToolbar();
        }
        else
        {
            // Desabilita alguns botões da toolbar
            $this->toolbar->disableButton(MToolBar::BUTTON_NEW);
            $this->toolbar->disableButton(MToolBar::BUTTON_SAVE);
            $this->toolbar->disableButton(MToolBar::BUTTON_SEARCH);
            $this->toolbar->disableButton(MToolBar::BUTTON_DELETE);
            $this->toolbar->disableButton(MToolBar::BUTTON_PRINT);

        }

    }

    public function defineFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);
        $action = MIOLO::getCurrentAction();
        $function = MIOLO::_REQUEST('function');

        $residenteId = $this->getRequestValue('residenteId');


        //login
        $mioloUserName = trim($MIOLO->getLogin()->id);
        $busPerson = new BusinessBasicBusPerson();
        $personData = $busPerson->getPersonByMioloUserName($mioloUserName);

        if ( strlen($residenteId) <= 0 )
        {
            $MIOLO->error( _M('Deve ser informado um residente para acessar esta interface', $module) );
        }

        $resNotasModulo = new ResNotasModulo();
        $residenteId = MIOLO::_REQUEST('residenteId');

        $this->residente = $residente = new ResResidente( $residenteId );


        $fields[] = new ResResidenteInformation(array('residente' => $this->residente));

        $periodo = $resNotasModulo->periodoResidente($residenteId);
        //turmaid para tcc
        $turmaid = $periodo[2];

        $anoInicio = explode('-', $periodo[0]);
        $anoFim = explode('-', $periodo[1]);


        $cont = 0;
        if($anoInicio[1] >= $anoInicio[1] + 6)
        {
            $anoInicio[0]++;
            $cont++;
        }
        if($anoFim[1] >= $anoInicio[1] + 6)
        {
            $cont++;
            $anoFim[0]--;
        }

        $semestre = (($anoFim[0] - $anoInicio[0]) * 2) + $cont;
        for($a=0; $a < $semestre;$a++)
        {

            $modulos = $resNotasModulo->moduloSemestre($residenteId);
            $linha = 0;

            foreach ( $modulos as $ind => $modulo )
            {
                $data = explode('-',$modulo[0]);

                //logica para modulo ir para o semestre correto
                if($anoInicio[1] < $anoInicio[1] + 6)
                {
                    if($data[1] < $anoInicio[1] + 6)
                    {
                        $semestreDoModulo = ((2*($data[0] - $anoInicio[0])) + 1) * 1;
                    }
                    else
                    {
                        $semestreDoModulo = ($data[0] - $anoInicio[0] + 1) * 2;
                    }
                }
                else
                {
                    if($data[1] < $anoInicio[1] + 6)
                    {
                        $semestreDoModulo = ((2*($data[0] - $anoInicio[0])) + 1) * 1;
                    }
                    else
                    {
                        $semestreDoModulo = ($data[0] - $anoInicio[0] + 1) * 2;
                    }
                }

                if ($semestreDoModulo > 4) {
                    $semestreDoModulo = 4;
                }

                if($semestreDoModulo == ($a+1))
                {
                    $ofertaDoResidente = new ResOfertaDoResidente($modulo[4]);
                    $assiduidadeValue = $ofertaDoResidente->percentualCargaHorariaRealizadaSemCargaHorariaComplementar/10;

                    $filters = new stdClass();
                    $filters->moduloid = $modulo[5];
                    $filters->semestre = $semestreDoModulo;
                    $filters->residenteid = $residenteId;

                    $valoresModulo = $resNotasModulo->search($filters);

                    //Construindo valores
                    if(!$valoresModulo[0][4])
                    {
                        $valoresModulo[0][4] = 0;
                    }
                    if(!$valoresModulo[0][5])
                    {
                        $valoresModulo[0][5] = 0;
                    }
                    if(!$assiduidadeValue)
                    {
                        $assiduidadeValue = 0;
                    }
                    if(!$valoresModulo[0][7])
                    {
                        $valoresModulo[0][7] = SAGU::calcNumber(" ({$assiduidadeValue} * " . ResNotasModulo::PESO_NOTA_ASSIDUIDADE . ") + ({$valoresModulo[0][4]} * " . ResNotasModulo::PESO_NOTA_PRODUTO . ") + ({$valoresModulo[0][5]} * " . ResNotasModulo::PESO_NOTA_DESEMPENHO . ")", true);
                    }


                    if($valoresModulo)
                    {
                        $this->notasModuloId[$a][$linha] = $valoresModulo[0][6];
                    }
                    //moduloid para o save
                    $this->moduloid[$a][$linha] = $modulo[5];


                    $textFieldProdutoisRead = false;
                    $textFieldDesempenhoisRead = false;

                    $resFuncaoModulo = new ResPreceptoriaModuloFuncao();

                    $filters = new stdClass();
                    $filters->moduloid = $modulo[5];
                    $filters->personid = $personData->personId;

                    $tutorModulo = $resFuncaoModulo->searchFuncao($filters);
                    $funcaoModulo = $tutorModulo[0][2];
                    if ($funcaoModulo == ResPreceptoriaModuloFuncao::FUNCAO_TUTOR)
                    {
                        $textFieldProdutoisRead = false;
                        $textFieldDesempenhoisRead = true;
                    } else if ($funcaoModulo == ResPreceptoriaModuloFuncao::FUNCAO_PRECEPTOR)
                    {
                        $textFieldProdutoisRead = true;
                        $textFieldDesempenhoisRead = false;
                    }

                    $assiduidadeValue = str_replace(',', '.', $assiduidadeValue);
                    $notaAssiduidade = new MTextField('notaAssiduidade['.$a.']['.$linha.']', $assiduidadeValue <= 0 ? null : SAGU::calcNumber($assiduidadeValue,true), null, 10, null, null, true);
                    $notaAssiduidade->_addStyle('text-align', 'center');

                    $notaProduto = new MTextField('notaProduto['.$a.']['.$linha.']', $valoresModulo[0][4] <= 0 ? null : SAGU::calcNumber($valoresModulo[0][4],true), null, 10, null, null, $textFieldProdutoisRead);
                    $notaProduto->addAttribute('onchange',SForm::getAjaxAction('calculaNota', 'divResposta', false, array('a' => $a, 'linha' => $linha)));
                    $notaProduto->_addStyle('text-align', 'center');

                    $notaDesempenho = new MTextField('notaDesempenho['.$a.']['.$linha.']', $valoresModulo[0][5] <= 0 ? null : SAGU::calcNumber($valoresModulo[0][5],true), null, 10, null, null, $textFieldDesempenhoisRead);
                    $notaDesempenho->addAttribute('onchange',SForm::getAjaxAction('calculaNota', 'divResposta', false, array('a' => $a, 'linha' => $linha)));
                    $notaDesempenho->_addStyle('text-align', 'center');

                    $notaGeral = new MTextField('notaGeral['.$a.']['.$linha.']', $valoresModulo[0][7] <= 0 ? null : SAGU::calcNumber($valoresModulo[0][7],true), null, 10, null, null, true);
                    $notaGeral->_addStyle('text-align', 'center');
                    $linha++;
                    $dataTable[] = array($modulo[3], $notaAssiduidade->generate(), $notaProduto->generate(), $notaDesempenho->generate(), $notaGeral->generate());


                    $notasModuloPeriodo[$a+1][] = SAGU::calcNumber($valoresModulo[0][7]);
                }
            }




            if(count($dataTable) == 0)
            {
                $dataTable = array('<center>Nenhum módulo cadastrado neste semestre</center>');
                $table = new MTableRaw('<font style="font-size: 15px">' . _M('Semestre '.($a+1), $module) . '</font>', $dataTable);
            }
            else
            {
                $table = new MTableRaw('<font style="font-size: 15px">' ._M('Semestre '.($a+1), $module). '</font>', $dataTable, $this->_columns);
            }

            // alinhamento
            for($b = 0;$b < count($dataTable); $b++)
            {
                for($c = 1;$c < 5;$c++)
                {
                    $table->setCellAttribute($b, $c, 'align', 'center');
                }
            }

            $fields[] = $divTable = new MDiv('divTable', $table);
            $dataTable = null;
            $fields[] = new MSeparator('<br><br>');
        }

        // Botão salvar
        if( SAGU::userHasAccessAny('FrmNotasModulo', array(A_UPDATE, A_INSERT)) )
        {
            $btns[] = $btnSModulos = new MButton('tbBtnSave', _M('Salvar notas do módulo', $module));
            $btnSModulos->_addStyle('margin-left', '45%');
        }

        $fields[] = new MDiv('cntButtons', $btns);

        $fields[] = new MDiv('divResposta');
        $fields[] = new MSeparator('<br><br>');



        if (count($funcaoModulo) == 0) {
            $fields[] = MMessage::getStaticMessage('msgInfo', _M("Ao 'Salvar notas do módulo' em seguida valide as notas dos períodos. Clique em 'Validar e salvar notas do residente'.", $module), MMessage::TYPE_INFORMATION);

            // -------------------------------------- notas do residente ----------------------------------------------
            $this->residente = $residente = new ResResidente($residenteId);
            $data = $this->residente->getAtivPraticasAsObject($residenteId);

            $fields[] = new MDiv('divLimbo', null, null, array('style' => 'display: none'));
            $fields[] = new SHiddenField('residenteId', $residenteId);

            //Cria baseGroups identicos
            //Caso o residente possua uma turma no registro, s?o apenas dois
            //Caso contrário ele cria conforme a quantidade de periodos cadastros na turma
            if (strlen($residente->turmaId) > 0) {
                $resTurma = new ResTurma($residente->turmaId);
                $quantPeriodos = $resTurma->quantidadePeriodo;
            } else {
                $quantPeriodos = 2;
            }

            //Percorre periodos
            $baseGroups = array();

            $arrayNotasPeriodo = array();
            for ($i = 1; $i <= $quantPeriodos; $i++) {
                $controls = array();

                $id = 'mediaPeriodo' . $i;

                $mediaPeriodo = null;
                $somaNotasPeriodo = 0;
                $qtdNotasPeriodo = 0;
                foreach ($notasModuloPeriodo as $semestre => $notas) {
                    if ($i == 1) {
                        if ($semestre == 1 || $semestre == 2) {
                            foreach ($notas as $nota) {
                                $somaNotasPeriodo += $nota;
                                $qtdNotasPeriodo++;
                            }
                        }
                    } else if ($i == 2) {
                        if ($semestre == 3 || $semestre == 4) {
                            foreach ($notas as $nota) {
                                $somaNotasPeriodo += $nota;
                                $qtdNotasPeriodo++;
                            }
                        }
                    } else if ($i == 3) {
                        if ($semestre == 5 || $semestre == 6) {
                            foreach ($notas as $nota) {
                                $somaNotasPeriodo += $nota;
                                $qtdNotasPeriodo++;
                            }
                        }
                    }
                }
                $mediaPeriodo = $somaNotasPeriodo/$qtdNotasPeriodo;
                $arrayNotasPeriodo[] = $mediaPeriodo;
                $controls[] = $media = new MTextField($id, $mediaPeriodo <= 0 ? null : SAGU::formatNumber($mediaPeriodo, 2) , _M('Média do @1º período', $module, $i));
                $media->setReadOnly(true);
                $media->addAttribute('onchange', $this->getAjaxAction('calculaMediaFinal', 'divLimbo', true));
                $media->_addStyle('text-align', 'center');
                $validators[] = new mFloatValidator($id, _M('Média do @1º período', $module, $i), '.');

                $controls[] = new MSeparator();

                $id = "parecerMediaPeriodo{$i}";
                $controls[] = $parecerMedia = new MMultiLineField($id, $this->getRequestValue($id, $residente->$id), _M('Parecer', $module), null, SAGU::getParameter('BASIC', 'FIELD_MULTILINE_NUM_ROWS'), SAGU::getParameter('BASIC', 'FIELD_MULTILINE_NUM_COLS'));

                $baseGroups[] = $bgr = new sBaseGroup("bgrPeriodo{$i}", _M('Período @1', $module, 'R' . $i), $controls);
                $bgr->setWidth('48%');
            }

            $fields[] = $vct = new MHContainer('hctPeriodos', $baseGroups);
            $vct->setFormMode(MControl::FORM_MODE_SHOW_SIDE);

            // Media final
            $controls = array();
            $controls[] = $notaFinal = new MTextField('notaFinal', SAGU::formatNumber(array_sum($arrayNotasPeriodo) / count($arrayNotasPeriodo)), _M('Média', $module));
            $notaFinal->setReadOnly(true);
            $notaFinal->_addStyle('text-align', 'center');
            $validators[] = new mFloatValidator('notaFinal', _M('Média', $module), '.');

            $controls[] = new MSeparator();

            $controls[] = $parecerFinal = new MMultiLineField('parecerFinal', $this->getRequestValue('parecerFinal', $residente->parecerFinal), _M('Parecer', $module), null, SAGU::getParameter('BASIC', 'FIELD_MULTILINE_NUM_ROWS'), SAGU::getParameter('BASIC', 'FIELD_MULTILINE_NUM_COLS'));

            $bgrMediaFinal = new sBaseGroup("bgrMedFinal", _M('Média final', $module), $controls);
            $bgrMediaFinal->setWidth('48%');

            $controls = array();

            $controls[] = new MSeparator();


            $controls[] = new MSpacer();

            $fields[] = $vct = new MHContainer(rand(), array($bgrMediaFinal));
            $vct->setFormMode(MControl::FORM_MODE_SHOW_SIDE);


            if ( !(strlen($this->residente->trabalhoDeConclusao->orientadorId) <= 0) )
            {
                if ($funcaoModulo != ResPreceptoriaModuloFuncao::FUNCAO_PRECEPTOR) {
                    $resTurma = new ResTurma($turmaid);

                    //Verificar qual é o tipo de avaliacao, buscando da turma ou deixando conceito
                    strlen($turmaid) > 0 ? $tipoAv = $resTurma->tipoAvaliacaoTCR : $tipoAv = ResTurma::TCR_POR_CONCEITO;

                    if ($tipoAv == ResTurma::TCR_POR_NOTA) {

                        $notaQualificacao1 = new MTextField('notaQualificacao1', $residente->trabalhoDeConclusao->notaQualificacao1, null, 10, null, null);
                        $notaQualificacao1->addAttribute('onBlur', 'validaCampoDouble(\'notaQualificacao1\')');
                        $notaQualificacao1->_addStyle('text-align', 'center');

                        $notaQualificacao2 = new MTextField('notaQualificacao2', $residente->trabalhoDeConclusao->notaQualificacao2, null, 10, null, null);
                        $notaQualificacao2->addAttribute('onBlur', 'validaCampoDouble(\'notaQualificacao2\')');
                        $notaQualificacao2->_addStyle('text-align', 'center');

                        $notaDefesa = new MTextField('notaDefesa', $residente->trabalhoDeConclusao->notaDefesa, null, 10, null, null);
                        $notaDefesa->addAttribute('onBlur', 'validaCampoDouble(\'notaDefesa\')');
                        $notaDefesa->_addStyle('text-align', 'center');

                        $nota = new MTextField('nota', $residente->trabalhoDeConclusao->nota, null, 10, null, null);
                        $nota->setReadOnly(true);
                        $nota->addAttribute('onBlur', 'validaCampoDouble(\'nota\')');
                        $nota->_addStyle('text-align', 'center');

                        $situacao = new MTextField('situacao', $residente->trabalhoDeConclusao->retornaSituacao(), null, 10, null, null);
                        $situacao->setReadOnly(true);
                        $situacao->_addStyle('text-align', 'center');

                        $dataTable[] = array($notaQualificacao1->generate() ,$notaQualificacao2->generate(), $notaDefesa->generate(), $nota->generate(), $situacao->generate());

                        $colunas = array(
                            _M('Nota de qualificação 1', $module),
                            _M('Nota de qualificação 2', $module),
                            _M('Nota de defesa', $module),
                            _M('Nota final', $module),
                            _M('Situação', $module)
                        );

                        $table = new MTableRaw('<font style="font-size: 15px">' ._M('Notas do TCR', $module). '</font>', $dataTable, $colunas);
                        // alinhamento
                        for($b = 0;$b < count($dataTable); $b++)
                        {
                            for($c = 0;$c < 5;$c++)
                            {
                                $table->setCellAttribute($b, $c, 'align', 'center');
                            }
                        }
                        $fields[] = $divTable = new MDiv('divTable', $table);
                    }
                }
            }

            $fields[] = new MSeparator('<br><br>');

            // Botão salvar
            if( SAGU::userHasAccessAny('FrmNotasDoResidente', array(A_UPDATE, A_INSERT)) )
            {
                $btns2[] = $btnSPeriodo = new MButton('tbBtnSaveNotasDoResidente', _M('Validar e salvar notas do residente', $module));
                $btnSPeriodo->_addStyle('margin-left', '45%');
            }

            $fields[] = new MDiv('cntButtons', $btns2);
        }



        parent::defineFields($fields, $validators, $data);
    }


    public function calculaNota($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);



        $valorAssiduidade = SAGU::NVL($args->notaAssiduidade[$args->a][$args->linha], '0');
        $valorProduto = str_replace(',', '.', SAGU::NVL($args->notaProduto[$args->a][$args->linha], '0'));
        $valorDesempenho = str_replace(',', '.', SAGU::NVL($args->notaDesempenho[$args->a][$args->linha], '0'));

        $idNotaProduto = "notaProduto[$args->a][$args->linha]";
        $idNotaDesempenho = "notaDesempenho[$args->a][$args->linha]";

        if($valorProduto < 0 || $valorProduto > 10  )
        {
            $MIOLO->page->addAJAXJsCode(" document.getElementById('{$idNotaProduto}').value = 0.0 ");
            return $MIOLO->page->addAJAXJsCode("alert('Nota produto tem ser um numero entre 0 e 10')");
        }
        if($valorDesempenho < 0 || $valorDesempenho > 10)
        {
            $MIOLO->page->addAJAXJsCode(" document.getElementById('{$idNotaDesempenho}').value = 0.0 ");
            return $MIOLO->page->addAJAXJsCode("alert('Nota desempenho tem ser um numero entre 0 e 10')");
        }
        if(is_numeric($valorProduto) && is_numeric($valorDesempenho) )
        {
            $notaFinal = SAGU::calcNumber(" ({$valorAssiduidade} * " . ResNotasModulo::PESO_NOTA_ASSIDUIDADE . ") + ({$valorProduto} * " . ResNotasModulo::PESO_NOTA_PRODUTO .  ") + ({$valorDesempenho} * " . ResNotasModulo::PESO_NOTA_DESEMPENHO . ")", true);

            $idNotaGeral = "notaGeral[$args->a][$args->linha]";

            $MIOLO->page->addAJAXJsCode(" document.getElementById('{$idNotaGeral}').value = '{$notaFinal}' ");
            return '';
        }
        else
        {
            if(!is_numeric($valorProduto))
            {
                $MIOLO->page->addAJAXJsCode(" document.getElementById('{$idNotaProduto}').value = '0.00' ");
            }
            if(!is_numeric($valorDesempenho))
            {
                $MIOLO->page->addAJAXJsCode(" document.getElementById('{$idNotaDesempenho}').value = '0.00' ");
            }
            return $MIOLO->page->addAJAXJsCode("alert('Nota não é um numero')");
        }

    }


    /**
     * Calcula media dos valores inseridos e exibe na tela
     *
     * @param stdClass $args
     * @return null
     */
    public function calculaMedia($args)
    {
        $nota1 = $args->{"notaPeriodo{$args->_periodo}Semestre1"};
        $nota2 = $args->{"notaPeriodo{$args->_periodo}Semestre2"};
        $mediaId = "mediaPeriodo{$args->_periodo}";

        //So calcula medias quando possuir os 2 valores de notas
        if ( (strlen($nota1) > 0) && (strlen($nota2) > 0) )
        {
            //Altera valor de campo Média do semestre
            $media = SAGU::calcNumber("({$nota1} + {$nota2} + 0.0) / 2", true);
            $this->page->addAJAXJsCode(" xGetElementById('{$mediaId}').value = '{$media}'");

            //Altera valor de média final
            $args->$mediaId = $media;
            $this->calculaMediaFinal($args);
        }

        return '';
    }

    /**
     * Calcula media final dos valores inseridos e exibe na tela
     *
     * @param stdClass $args
     */
    public function calculaMediaFinal($args)
    {
        if (property_exists($args, 'mediaPeriodo3'))
        {
            if ( ( strlen($args->mediaPeriodo1) > 0 ) && ( strlen($args->mediaPeriodo2) > 0 ) && strlen($args->mediaPeriodo3) > 0 )
            {
                $mediaFinal = SAGU::calcNumber("({$args->mediaPeriodo1} + {$args->mediaPeriodo2} + {$args->mediaPeriodo3} + 0.0) / 3", true);
                $this->page->addAJAXJsCode(" xGetElementById('notaFinal').value = '{$mediaFinal}'");
            }
        }
        else
        {
            if ( ( strlen($args->mediaPeriodo1) > 0 ) && ( strlen($args->mediaPeriodo2) > 0 ) )
            {
                $mediaFinal = SAGU::calcNumber("({$args->mediaPeriodo1} + {$args->mediaPeriodo2} + 0.0) / 2", true);
                $this->page->addAJAXJsCode(" xGetElementById('notaFinal').value = '{$mediaFinal}'");
            }
        }
    }


    public function  tbBtnSave_click($sender = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);
        $data = $this->getData();
        try
        {
            SDatabase::beginTransaction();

            foreach ( $_REQUEST['notaProduto'] as $chave => $valor )
            {
                $notaProduto[] = $valor;
            }
            foreach ( $_REQUEST['notaDesempenho'] as $chave => $valor )
            {
                $notaDesempenho[] = $valor;
            }
            foreach ( $_REQUEST['notaGeral'] as $chave => $valor )
            {
                $notaGeral[] = $valor;
            }

            foreach ( $_REQUEST['notaAssiduidade'] as $chave => $valor )
            {
                $notaAssiduidade[] = $valor;
            }

            foreach ( $notaGeral as $indexSemestre=>$as)
            {
                $this->countTable = count($as);

                for($d = 0; $d < $this->countTable; $d++)
                {
                    $notasModulo = new ResNotasModulo();

                    if($this->notasModuloId[$indexSemestre][$d])
                    {
                        $notasModulo->notapormoduloid = $this->notasModuloId[$indexSemestre][$d];
                    }

                    $notasModulo->residenteid = $_REQUEST['residenteId'];
                    $notasModulo->moduloid = $this->moduloid[$indexSemestre][$d];
                    $notasModulo->semestre = ($indexSemestre+1);

                    $notasModulo->notadeatividadedeproduto = str_replace(',', '.', $notaProduto[$indexSemestre][$d]);
                    $notasModulo->notadeavaliacaodedesempenho = str_replace(',', '.', $notaDesempenho[$indexSemestre][$d]);
                    $notasModulo->notageral = str_replace(',', '.', $notaGeral[$indexSemestre][$d]);
                    $notasModulo->notadeassiduidade = str_replace(',', '.', $notaAssiduidade[$indexSemestre][$d]);

                    $notasModulo->save();
                }
            }

            SDatabase::commit();

            SAGU::information(_M('Dados salvos com sucesso.', $module));
        }
        catch (Exception $e)
        {
            $this->AddAlert($e->getMessage());
        }
    }



    public function  tbBtnSaveNotasDoResidente_click($sender = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);
        $data = $this->getData();
        try
        {
            SDatabase::beginTransaction();


            foreach ( $data as $key => $val )
            {
                $this->residente->$key = $val;

                if ( strlen($val) > 0 )
                {
                    $this->residente->$key = $val;
                }
            }

            //Salva dados do TCR
            if ( $this->residente->trabalhoDeConclusao )
            {
                $this->residente->trabalhoDeConclusao->membroDaBanca = array();
                $this->residente->trabalhoDeConclusao->notaQualificacao1 = $_REQUEST['notaQualificacao1'];
                $this->residente->trabalhoDeConclusao->notaQualificacao2 = $_REQUEST['notaQualificacao2'];
                $this->residente->trabalhoDeConclusao->notaDefesa = $_REQUEST['notaDefesa'];

                $this->residente->trabalhoDeConclusao->save();
            }

            $this->residente->save();

            SDatabase::commit();

            SAGU::information(_M('Dados salvos com sucesso.', $module));
        }
        catch (Exception $e)
        {
            $this->AddAlert($e->getMessage());
        }
    }

}
?>