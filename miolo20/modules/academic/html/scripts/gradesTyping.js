var allDegreesId = new Array();

//AJAX Functions
var clickEvent;

/**
 * Graus academicos
 */
var tableDegrees = Array();

var degreeIdNotaFinal = null;

var degreeIdMedia = null;

var posDegreeMedia = null;

var degreeIdExame = null;

var evaluationTypeId = null;

var notaMaxima = null;

function getDegreeEvaluations(degreeId, enrollId)
{
    divEvaluations = document.getElementById('divEvaluations' + degreeId + '_' + enrollId);

    if (divEvaluations.innerHTML == '' || divEvaluations.innerHTML == '<div></div>')
    {
        groupId = document.getElementById('groupId').value;
        showFunction = 'showResult' + degreeId + '_' + enrollId;
        args = Array(degreeId, enrollId, groupId);

        divEvaluations.innerHTML = '<img src="/images/loading.gif"/>';
        divEvaluations.style.display = 'block';

        cpaint_call(document.getElementById('currentUrl').value, "POST", "getDegreeEvaluations", args, eval(showFunction), "TEXT");
    }
    else
    {
        expandRetractContainer('divEvaluations' + degreeId + '_' + enrollId);
    }
}

function updateFieldValue(degreeId, enrollId, grade, type, descArray, notaMaiorQueAtual, parentDegreeId)
{
    if (type == 'E')
    {
        div = document.getElementById('evaluation[' + degreeId + '][' + enrollId + ']');
        field = document.getElementById('evaluationsArray');
    }
    else if (type == 'D')
    {
        div = document.getElementById('degree[' + degreeId + '][' + enrollId + ']');
        divExtra = document.getElementById('degreeExtra[' + degreeId + '][' + enrollId + ']');
        field = document.getElementById('degreesArray');
    }

    if (div)
    {
        if (field)
        {
            field.value = descArray;
        }

        divExtra.value = '';
        if ( notaMaiorQueAtual )
        {
            divExtra.value = grade;
        }
        else
        {
            div.value = grade;
        }

        if ( div.onchange && parentDegreeId.length > 0 )
        {
            document.getElementById('isFromNoteEdit').value = '1';
            
            div.onchange();
        }
    }

    // Atualiza somatorio final
    if ( parentDegreeId )
    {
        parent = document.getElementById('degree[' + parentDegreeId + '][' + enrollId + ']');
        parent.onfocus();
    }
}

//Classes definition
function objDegreeEnroll(degreeId, enrollId, weight, parentDegreeId, mayBeNull, facimedMaxEvaluationPoints)
{
    this.degreeId = degreeId;
    this.enrollId = enrollId;
    this.weight = weight;
    this.parentDegreeId = parentDegreeId;
    this.mayBeNull = mayBeNull;
    this.finalAverage = 0;
    this.maximumFinalAverage = null;
    this.enrollStatusApproved = null;
    this.enrollStatusDisapproved = null;
    this.enrollStatusDisapprovedForLacks = null;
    this.utilizaCalculoPorSoma = false;
    this.minimumFrequency = null;

    this.evaluations = Array();
    this.childDegrees = Array();
    this.detailEnrollStatusList = Array();
    this.currentDetailEnrollStatusId = null;
    this.estadoFuturoAtual = null;

    this.evaluationTypeId = null;
    this.evaluationTypeByFrequency = null;
    this.evaluationTypeByNoteAndFrequency = null;
    
    this.valueSum = 0;
    this.weightSum = 0;
    this.eGrauFinal = false;

    this.addEvaluation = function (objEvaluationEnroll)
    {
        this.evaluations.push(objEvaluationEnroll);
    }

    this.addChildDegree = function (objDegreeEnroll)
    {
        this.childDegrees.push(objDegreeEnroll);
    }

    this.setFinalAverage = function(finalAverage)
    {
        this.finalAverage = finalAverage;
    }
    
    this.setMaximumFinalAverage = function(value)
    {
        this.maximumFinalAverage = value;
    }
    
    this.setUtilizaCalculoPorSoma = function(value)
    {
        this.utilizaCalculoPorSoma = value;
    }
    
    this.setEnrollStatusDisapproved = function(enrollStatusId)
    {
        this.enrollStatusDisapproved = enrollStatusId;
    }
    
    this.setEnrollStatusApproved = function(enrollStatusId)
    {
        this.enrollStatusApproved = enrollStatusId;
    }
    
    this.setEnrollStatusDisapprovedForLacks = function(enrollStatusId)
    {
        this.enrollStatusDisapprovedForLacks = enrollStatusId;
    }
    
    this.setMinimumFrequency = function(value)
    {
        this.minimumFrequency = value;
    }
    
    this.setDetailEnrollStatusList = function(arrList)
    {
        this.detailEnrollStatusList = arrList;
    }
    
    this.setCurrentDetailEnrollStatusId = function(num)
    {
        this.currentDetailEnrollStatusId = num;
    }

    this.setEstadoFuturoAtual = function(num)
    {
        this.estadoFuturoAtual = num;
    }
    
    this.getEstadoFuturoAtual = function()
    {
        return this.estadoFuturoAtual;
    }

    this.setEvaluationTypeId = function(value)
    {
        this.evaluationTypeId = value;
        evaluationTypeId = value;
    }

    this.setEvaluationTypeByFrequency = function(value)
    {
        this.evaluationTypeByFrequency = value;
    }

    this.setEvaluationTypeByNoteAndFrequency = function(value)
    {
        this.evaluationTypeByNoteAndFrequency = value;
    }
    
    this.setValueSum = function(value)
    {
        this.valueSum = value;
    }
    
    this.getValueSum = function()
    {
        return this.valueSum;
    }
    
    this.setWeightSum = function(value)
    {
        this.weightSum = value;
    }
    
    this.getWeightSum = function()
    {
        return this.weightSum;
    }
    
    this.setEGrauFinal = function(value)
    {
        this.eGrauFinal = value;
    }

    this.updateValue = function (isParent, displayDetailedStatus)
    {
        var i, noteField;
        var average = 0;
        var doUpdate = true;
        var noteHasValue = null; // Utilizado para casos onde a nota e 0.00 
        var childEvaluationPoints = 0;
        var childCheckEvaluationPoints = 0;
        var childMaxEvaluationPoints = 0;
        var preenchidoTodosRequeridos = true; // Define se foi preenchido todos campos mayBeNull = false (nao pode ser nulo)
        var field;
        var fieldValue;
      
        /**
         * Legenda da variável "typeCalc"
         * 1 = SOMA;
         * 2 = MÉDIA;
         * 3 = REGRA DE 3 SIMPLES;
         * 4 = MÉDIA PARA AVALIAÇÕES QUE NÃO POSSUAM FILHOS COM NOTA FINAL;
         */
        var typeCalc = ( this.evaluations.length > 0 ) ? 4 : 0;

        var typeCalcAverage = false;
        var typeCalcSum = 0;
        var typeCalcRule = 0;
        
        this.setValueSum(0);
        this.setWeightSum(0);
        this.setEGrauFinal(this.degreeId == degreeIdNotaFinal);

        // Se utiliza controle de pontuacao nas notas do professor
        controlePontuacao = document.getElementById('controlePontuacao');

        for (i = 0; i < this.childDegrees.length; i++)
        {
            noteField = document.getElementById('degree[' + this.childDegrees[i].degreeId + '][' + this.childDegrees[i].enrollId + ']');

            if ( noteField.value.length > 0 && isNumeric(noteField))
            {
                this.setValueSum(this.getValueSum() + (parseFloat(noteField.value) * this.childDegrees[i].weight));
                this.setWeightSum(this.getWeightSum() + this.childDegrees[i].weight);
                
                if ( noteHasValue == null )
                {
                    noteHasValue = true;
                }
                
                /**
                 * CASO SEJA UMA AVALIAÇÃO QUE NÃO POSSUA FILHOS - NO CASO - A NOTA FINAL "SOMA DE TODAS AS NOTAS DAS N's" SERÁ REALIZADO CÁLCULO PO MÉDIA
                 */
                typeCalc = 4;
            }
            else
            {
                doUpdate = false;
                noteHasValue = false;

                if ( isParent && this.childDegrees[i].mayBeNull == true )
                {
                    doUpdate = true;
                    noteHasValue = true;
                }
            }
        }
        
        /**
         * ARMAZENA O TOTAL DE PONTOS QUE SERÁ OBTIDO POR TODAS AS AVALIAÇÕES NA VARIÁVEL "typeCalcSum" E NA "childEvaluationPoints".
         */
        for ( i = 0; i < this.evaluations.length; i++ )
        {
        	typeCalcSum += eval( ( this.evaluations[i].weight ) * ( this.evaluations[i].maxPoints ) );
        	childMaxEvaluationPoints  = typeCalcSum;
        }
        
        /**
         * CASO TODAS AS NOTAS MÁXIMAS DAS AVALIAÇÕES SEJAM IGUAIS ELE IRÁ ATRIBUIR "true" A VARIÁVEL "typeCalcAverage"
         */
        for ( i = 0; i < this.evaluations.length; i++ )
        {
            childEvaluationPoints = eval( this.evaluations[i].maxPoints );
        	
        	if ( i != 0 && childEvaluationPoints == childCheckEvaluationPoints )
        	{
        		typeCalcAverage = true;
        	}
        	else
        	{
        		typeCalcAverage = false;
        	}
        	
        	childCheckEvaluationPoints = childEvaluationPoints;
        }
        
        /**
         * SE A SOMA DO TOTAL MAXIMO DE PONTOS FOR IGUAL AO VALOR ARMAZENADO NA VARIÁVEL "facimedMaxEvaluationPoints" SERÁ REALIZADO A SOMA DAS AVALIAÇÕES.
         */
        if ( typeCalcSum == facimedMaxEvaluationPoints )
        {
            typeCalc = 1;
        }
        
        /**
         * SE A "faciemdTypeCalcAverage" FOR VERDADEIRA SERÁ REALIZADA MÉDIA DAS AVALIAÇÕES.
         */
        if ( typeCalcAverage )
        {
            typeCalc = 2;
        }
        
        /**
         * SE O TIPO DE CÁLCULO FOR DIFERENTE DE 1(SOMA), NÃO FOR MÉDIA E NÃO POSSUIR AVALIAÇÕES FILHAS SERÁ REALIZADO CÁCULO POR REGRA DE 3 SIMPLES.
         */
        if ( typeCalc != 1 && !typeCalcAverage && this.childDegrees.length <= 0 && this.evaluations.length <= 0 )
        {
            typeCalc = 3;
        }
        
        if ( this.evaluations.length > 0 )
        {
            noteHasValue = true;
        }
        
        for (i = 0; i < this.evaluations.length; i++)
        {
            noteField = document.getElementById('evaluation[' + this.evaluations[i].evaluationId + '][' + this.evaluations[i].enrollId + ']');
            
            if ( noteField.value > eval( this.evaluations[i].maxPoints ) )
            {
                if ( controlePontuacao.value == 'TRUE' )
                {
                    alert("A nota não pode ser maior que o valor máximo( "+this.evaluations[i].maxPoints+" ) cadastrado para a avaliação.	");
            	}
                noteField.value = 0;
            	noteField.focus();
            }

            if ( noteField.value.length == 0 || ! isNumeric(noteField))
            {
                doUpdate = false;
            }
            else
            {
                this.setValueSum(this.getValueSum() + (parseFloat(noteField.value) * this.evaluations[i].weight));
                this.setWeightSum(this.getWeightSum() + this.evaluations[i].weight);
            }
        }

        if ( doUpdate && noteHasValue || this.eGrauFinal )
        {
            /**
             * SOMA
             */
            if ( typeCalc == 1 )
            {
                average = roundNumber( this.getValueSum() , '1');
            }
            /**
             * MÉDIA
             */
            else if ( typeCalc == 2 || typeCalc == 4 )
            {
                average = roundNumber(this.getValueSum() / this.getWeightSum(), '1');
            }
            /**
             * REGRA DE 3 SIMPLES
             */
            else if ( typeCalc == 3 )
            {
                average = roundNumber( ( ( this.getValueSum() * facimedMaxEvaluationPoints ) / childMaxEvaluationPoints ), '1');
            }
            
            if ( this.eGrauFinal || this.childDegrees.length > 0 )
            {
                average = this.escalaDoGrauFinal(average);
            }

            average = arredondaFloat(average);
            
            // Se existe regra de nota maxima e ela ultrapassou, faz limitar para a mesma
            if ( ( this.maximumFinalAverage != null ) && ( average > this.maximumFinalAverage ) )
            {
                average = this.maximumFinalAverage;
            }
            
            if ( this.finalAverage > 0 )
            {
                atualizaEstadoFrequencia(this, average);
            }
            
            average = average >= 0 ? average : '';
            var degreeEnrollId = 'degree[' + this.degreeId + '][' + this.enrollId + ']';
         
            if ( this.eGrauFinal )
            {                
                for ( i=0; i < tableDegrees.length; i++ )
                {        
                    // Verifica qual
                    field = getFldDegreeElement(tableDegrees[i].degreeId, this.enrollId);
                    
                    if ( field )
                    {
                        fieldValue = field.value ? field.value : -1;
                        
                        if ( !tableDegrees[i].podeSerNulo && ( fieldValue <= -1 ) && tableDegrees[i].degreeId != this.degreeId )
                        {
                            preenchidoTodosRequeridos = false;
                        }
                    }
                }
                
                
                if ( preenchidoTodosRequeridos )
                {
                    document.getElementById(degreeEnrollId).value = arredondaFloat(average);
                }
            }
            else
            {
                document.getElementById(degreeEnrollId).value = arredondaFloat(average);
            }
            
            // Quando existir grau de exame cadastrado, deve verificar se a media das notas digitadas e menor que Nota mínima para não fazer exame        
            if ( degreeIdExame != null )
            {
                calculaExame(this, this.degreeId, this.enrollId);
            }
        }
    }
    
    /**
     * Calcula a escala do grau final, configurada pelo parâmetro 'ESCALA_DE_ARREDONDAMENTO_DO_GRAU_FINAL'.
     * 
     * @param {float} average
     * @returns {float} average
     */
    this.escalaDoGrauFinal = function(average)
    {
        // Se nao utiliza escala de arredondamento
        escala = document.getElementById('escala');

        if ( escala.value == '0' )
        {
            average = this.utilizaCalculoPorSoma ? this.getValueSum() : ( this.getValueSum() / this.getWeightSum() );
        }
        else
        {
            if ( document.getElementById('calc') )
            {
                this.utilizaCalculoPorSoma = document.getElementById('calc').value;
                average = this.getValueSum();
            }

            // Fórmula de arredondamento por escala
            var noteValue = Math.round(parseFloat(average)/parseFloat(escala.value)) * parseFloat(escala.value);  
            average = noteValue;
        }
                
        return average;
    }

    this.formatNote = function (noteSeparator, roundValue)
    {
        var noteField, formatedValue, preFormated;
        noteField = document.getElementById('degree[' + this.degreeId + '][' + this.enrollId + ']');

        if ( noteField.value )
        {
            preFormated = noteField.value.replace(',', '.');
            preFormated = parseFloat(preFormated);
            preFormated = preFormated;
            
            formatedValue = preFormated.toString();
            formatedValue = formatedValue.replace('.', noteSeparator);
            
            noteField.value = formatedValue;
        }
    }
}

function objEvaluationEnroll (evaluationId, enrollId, degreeId, weight, maxPoints)
{
    this.evaluationId = evaluationId;
    this.enrollId = enrollId;
    this.weight = weight;
    this.degreeId = degreeId;
    this.maxPoints = maxPoints;
}

function objDegrees ()
{
    this.degreeEnrolls = Array();
    this.concepts = Array();
    this.enrollStatus = Array();
    this.detailedEnrollStatusList = Array();
    this.enrollStatusDisapproved = null;
    this.justifyDisapprovals = false;

    this.addDegreeEnroll = function (objDegreeEnroll)
    {
        this.degreeEnrolls.push(objDegreeEnroll);
    }

    this.addConcept = function(objConcept)
    {
        this.concepts.push(objConcept);
    }

    this.setEnrollStatusList = function(es)
    {
        this.enrollStatus = es;
    }

    this.setDetailedEnrollStatusList = function(es)
    {
        this.detailedEnrollStatusList = es;
    }

    this.setEnrollStatusDisapproved = function(enrollStatusId)
    {
        this.enrollStatusDisapproved = enrollStatusId;
    }

    this.setJustifyDisapprovals = function(justifyDisapprovals)
    {
        this.justifyDisapprovals = justifyDisapprovals;
    }

    this.getDegreeEnroll = function (degreeId, enrollId)
    {
        var i;
        var retVal = null;

        for (i = 0; i < this.degreeEnrolls.length; i++)
        {
            if (this.degreeEnrolls[i].degreeId == degreeId &&
                this.degreeEnrolls[i].enrollId == enrollId)
            {
                    return this.degreeEnrolls[i];
            }
        }

        return null;
    }

    this.updateChilds = function ()
    {
        var i, degreeEnroll;

        for (i = 0; i < this.degreeEnrolls.length; i++)
        {
            if (this.degreeEnrolls[i].parentDegreeId)
            {
                degreeEnroll = this.getDegreeEnroll(this.degreeEnrolls[i].parentDegreeId, this.degreeEnrolls[i].enrollId);
                degreeEnroll.addChildDegree(this.degreeEnrolls[i]);
            }
        }
    }

    this.showEvaluations = function(degreeId)
    {
        var i;

        for (i = 0; i < this.degreeEnrolls.length; i++)
        {
            if (this.degreeEnrolls[i].degreeId == degreeId)
            {
                getDegreeEvaluations(this.degreeEnrolls[i].degreeId, this.degreeEnrolls[i].enrollId);
            }
        }
    }
    
    /**
     * Caso a frequência tenha sido alterada, verifica se é nescessário
     * atualizar o estado.
     */
    this.alternateStatus = function(enrollId, degreeId)
    {
        possibleConcepts = Array();
        foundConcept     = null;
        
        id  = "degree[" + degreeId + "][" + enrollId + "]";
        obj = document.getElementById(id);
        
        //Verifica se é nota final
        isFinalNote  = false;
        minFrequency = null;
        
        if ( degreeId && enrollId )
        {
            degree       = this.getDegreeEnroll(degreeId, enrollId);
            minFrequency = degree.minimumFrequency;
            isFinalNote  = ! degree.parentDegreeId;
        }
        for (i = 0; i < this.concepts.length; i++)
        {
            if ( obj.value.toLowerCase() == this.concepts[i].description.toLowerCase() )
            {
                foundConcept = this.concepts[i];
            }

            possibleConcepts.push( this.concepts[i].description );
        }

        if (!foundConcept)
        {
            if ( isFinalNote )
            {
                document.getElementById('status[' + enrollId + ']').value = '';
            }
                
            return;
        }

        enrollStatusId = foundConcept.enrollStatusId;

        // Verifica se foi reprovado por faltas
        if ( ( minFrequency != null ) && ( ( evaluationTypeId == 2 ) || ( evaluationTypeId == 3 ) ) )
        {
            currentFrequency = getCurrentEnrollFrequency(enrollId);

            if ( currentFrequency < minFrequency )
            {
                enrollStatusId = degree.enrollStatusDisapprovedForLacks;
            }
        }
        
        document.getElementById('status[' + enrollId + ']').value = enrollStatusId;
    }

    /**
    * Valida se um conceito digitado e valido (se esta presente na tabela de conceitos)
    */
    this.validateConcept = function(degreeId, enrollId, obj)
    {
        if ( !obj.value )
        {
            return;
        }
        
        isFromNoteEdit = document.getElementById('isFromNoteEdit').value.length > 0;
        document.getElementById('isFromNoteEdit').value = '';

        //Verifica se é nota final
        isFinalNote = false;
        minFrequency = null;
        if ( degreeId && enrollId )
        {
            degree = this.getDegreeEnroll(degreeId, enrollId);
            minFrequency = degree.minimumFrequency;
            isFinalNote = ! degree.parentDegreeId;
        }

        possibleConcepts = Array();
        foundConcept = null;
        for (i = 0; i < this.concepts.length; i++)
        {
            if ( obj.value.toLowerCase() == this.concepts[i].description.toLowerCase() )
            {
                foundConcept = this.concepts[i];
            }

            possibleConcepts.push( this.concepts[i].description );
        }

        if (!foundConcept)
        {
            alert('Este conceito não é válido. Os conceitos possíveis são: ' + possibleConcepts.join(', '));
            obj.value = ''; //apaga o valor do campo

            if ( isFinalNote )
            {
                document.getElementById('status[' + enrollId + ']').value = '';
            }
            
            return;
        }

        //Altera o estado de matricula na tela quando for alterada a nota final
        if ( isFinalNote )
        {
            enrollStatusId = foundConcept.enrollStatusId;

            // Verifica se foi reprovado por faltas
            if ( minFrequency != null )
            {
                currentFrequency = getCurrentEnrollFrequency(enrollId);

                if ( currentFrequency < minFrequency )
                {
                    enrollStatusId = degree.enrollStatusDisapprovedForLacks;
                }
            }
        
            document.getElementById('status[' + enrollId + ']').value = enrollStatusId;

            args = Array();
            document.getElementById('currentEnrollId').value = enrollId;
            document.getElementById('currentDegreeId').value = degreeId;

            //Verifica se deve exigir que usuario digite uma justificativa caso o estado deste conceito digitado for REPROVADO (ticket #8340)
            if ( !isFromNoteEdit )
            {
                if ( this.justifyDisapprovals &&
                       this.enrollStatusDisapproved &&
                       ( foundConcept.enrollStatusId == this.enrollStatusDisapproved ) )
                {
                    cpaint_call(document.getElementById('currentUrl').value, "POST", "openDisapprovalReason", args, openDisapprovalReason, "TEXT");
                }
                else
                {
                    _saveDisapprovalReason(true);
                }
            }
        }
    }
}

function openDisapprovalReason(result)
{
    document.getElementById('divPopup').innerHTML = result;

    MIOLO_parseAjaxJavascript(result);
    stopShowLoading();
}

/**
 * erase (boolean) TRUE para limpar descricao, false para definir descricao digitada pelo usuario
 */
function _saveDisapprovalReason(erase)
{
    var fieldDisapprovalReason = document.getElementById('disapprovalReason');
    var disapprovalReason = fieldDisapprovalReason ? fieldDisapprovalReason.value : '';
    
    if ( (disapprovalReason.length <= 0) && ( ! erase ) )
    {
        alert('Você deve informar o motivo de reprovação.');
        return;
    }

    args = Array(
        document.getElementById('currentDegreeId').value,
        document.getElementById('currentEnrollId').value,
        erase ? '' : disapprovalReason,
        document.getElementById('degreesArray').value
    );
    cpaint_call(document.getElementById('currentUrl').value, "POST", "saveDisapprovalReason", args, __saveDisapprovalReason, "TEXT");

    mpopup.remove();
}

function __saveDisapprovalReason(result)
{
    MIOLO_parseAjaxJavascript(result);
}

function setDegreesArray(arg)
{
    document.getElementById('degreesArray').value = arg;
}


/**
 * Objeto do tipo Conceito
 */
function objConcept(description, enrollStatusId)
{
    this.description = '';
    this.enrollStatusId = null;
}

function getCurrentEnrollFrequency(enrollId)
{
    var elId = 'frequency[' + enrollId + ']';
    return document.getElementById(elId) ? document.getElementById(elId).value : null;
}

function arredondaFloat(value)
{
    var roundDecimals = document.getElementById('round_value').value;
    
    value = parseFloat(value);
    value = roundNumber(value,roundDecimals);
    
    if ( isNaN(value) )
    {
        value = '';
    }
    
    return value;
}

function getFldDegreeElement(degreeId, enrollId)
{
    return document.getElementById('degree[' + degreeId + '][' + enrollId + ']');
}

/**
 * Grau academico (acdDegree)
 */
function tableDegree()
{
    this.degreeId = null;
    this.examMinimumNote = null;
    this.examMaximumNote = null;
    this.isExam = false;
    this.substituiGrauPai = false;
    this.exameSubstituiMenorNota = false;
    this.mediaComGrauPai = false;
    this.notaMinimaNaoPegarExame = null;
    this.parentDegreeId = null;
    this.weight = null;
    this.podeSerNulo = false;
    
    /**
     * Indica quando e uma nota normal (nao e media, exame, nota final..)
     */
    this.eNotaNormal = false;

    this.setDegreeId = function(value)
    {
        this.degreeId = value;
    }

    this.setParentDegreeId = function(value)
    {
        this.parentDegreeId = value;
    }

    this.setExamMinimumNote = function(value)
    {
        this.examMinimumNote = value;
    }
    
    this.setExamMaximumNote = function(value)
    {
        this.examMaximumNote = value;
    }
    
    this.setIsExam = function(value)
    {
        this.isExam = value;
    }

    this.setSubstituiGrauPai = function(value)
    {
        this.substituiGrauPai = value;
    }
    
    this.setExameSubstituiMenorNota = function(value)
    {
        this.exameSubstituiMenorNota = value;
    }

    this.setMediaComGrauPai = function(value)
    {
        this.mediaComGrauPai = value;
    }

    this.setNotaMinimaNaoPegarExame = function(value)
    {
        this.notaMinimaNaoPegarExame = value;
    }
    
    this.setENotaNormal = function(value)
    {
        this.eNotaNormal = value;
    }
    
    this.setPoderSerNulo = function(value)
    {
        this.podeSerNulo = value;
    }
    
    this.setWeight = function(value)
    {
        this.weight = value;
    }
}

function getTableDegree(degreeId)
{
    for ( i = 0; i < tableDegrees.length; i++ )
    {
        if ( tableDegrees[i].degreeId == degreeId )
        {
            return tableDegrees[i];
        }
    }
    
    return new tableDegree();
}

/**
 * Funcao que calcula a media das notas digitadas (ex.: NOTA1, NOTA2), semelhante a forma antiga.
 * Foi criada para atender a novos requisitos (exame), mas nao impactar no antigo funcionamento (legado).
 * Do jeito antigo, nao era possivel obter certos objetos (de notas) que agora necessitam ser acessados.
 */
function getNewAverage(enrollId)
{
    var valueSum = 0;
    var weightSum = 0;
    var avg = 0;
    var field;
    var fieldId;
    var fieldValue;
    var preenchidoTodosRequeridos = true; // Define se foi preenchido todos campos mayBeNull = false (nao pode ser nulo)
    var existeNotaNormal = false;
    var escala = document.getElementById('escala');

    while ( !existeNotaNormal )
    {
        for ( i=0; i < tableDegrees.length; i++ )
        {        
            // Verifica qual a posição da média na array.
            if ( tableDegrees[i].degreeId == degreeIdMedia )
            {
                posDegreeMedia = i;
            }

            if ( tableDegrees[i].eNotaNormal )
            {
                existeNotaNormal = true;
                field = getFldDegreeElement(tableDegrees[i].degreeId, enrollId);

                if ( field )
                {
                    fieldValue = field.value ? field.value : -1;

                    if ( !tableDegrees[i].podeSerNulo && ( fieldValue <= -1 ) )
                    {
                        preenchidoTodosRequeridos = false;
                    }

                    if ( fieldValue > -1 )
                    {                    
                        valueSum  += parseFloat(fieldValue) * tableDegrees[i].weight;
                        weightSum += tableDegrees[i].weight;
                    }
                }
            }
        }
        
        // Caso não existam notas normais, define a média como sendo nota normal.
        if ( !existeNotaNormal && posDegreeMedia != null )
        {
            tableDegrees[posDegreeMedia].eNotaNormal = true;
        }
        else
        {
            existeNotaNormal = true;            
        }
    }

    if ( valueSum > 0 )
    {
        avg = valueSum / weightSum;
    }
    
    if ( !preenchidoTodosRequeridos )
    {
        avg = 0;
    }
    
    if ( escala.value != '0' )
    {
        avg = Math.round(parseFloat(avg)/parseFloat(escala.value)) * parseFloat(escala.value);
    }
    
    return arredondaFloat(avg);
}

/**
 * Executa logicas de calculo de exame, nota final de acordo como foi configurado na tabela acdDegree.
 */
function calculaExame(objDegreeEnroll, degreeId, enrollId)
{
    var fieldExame     = getFldDegreeElement(degreeIdExame, enrollId);
    var fieldMedia     = getFldDegreeElement(degreeIdMedia, enrollId);
    var fieldNotaFinal = getFldDegreeElement(degreeIdNotaFinal, enrollId);
    var fieldCurrent   = getFldDegreeElement(degreeId, enrollId);

    if ( fieldExame )
    {
        var newAverage  = getNewAverage(enrollId);
        var degreeExame = getTableDegree(degreeIdExame);
        var minimumNote = degreeExame.examMinimumNote;
        var maximumNote = degreeExame.examMaximumNote;
        var statusId = objDegreeEnroll.getEstadoFuturoAtual();
        var reprovouPorFalta = ( statusId && ( statusId == objDegreeEnroll.enrollStatusDisapprovedForLacks ) );
        var divImgId    = 'updateNoteImg_' + degreeIdExame + '_' + enrollId;
        var divImgExame = document.getElementById(divImgId) ? document.getElementById(divImgId) : document.getElementById('divLimboImg');
        
        if ( ( newAverage >= minimumNote && newAverage < maximumNote ) && !reprovouPorFalta )
        {
        }
        else
        {            
            fieldExame.style.display = 'none';
            divImgExame.style.display = 'none';

            notaFinal = newAverage;
        }
    }

    // Previne erros
    if ( !fieldExame || !fieldMedia || !fieldNotaFinal )
    {
        return;
    }
 
    var notaFinal   = 0;
    var degreeExame = getTableDegree(degreeIdExame);
    var newAverage  = getNewAverage(enrollId);
    var eamValue    = fieldExame.value;
    var divImgId    = 'updateNoteImg_' + degreeIdExame + '_' + enrollId;
    var divImgExame = document.getElementById(divImgId) ? document.getElementById(divImgId) : document.getElementById('divLimboImg');
    var minimumNote = degreeExame.examMinimumNote;
    var maximumNote = degreeExame.examMaximumNote;
    var finalAverage = degreeExame.notaMinimaNaoPegarExame;
    var divFreqIns = document.getElementById('divFreqIns[' + enrollId + ']');
    var divEvaluations = document.getElementById('divEvaluations' + degreeId + '_' + enrollId);
    var statusId = objDegreeEnroll.getEstadoFuturoAtual();
    var reprovouPorFalta = ( statusId && ( statusId == objDegreeEnroll.enrollStatusDisapprovedForLacks ) );
    
    // Nota minima para fazer exame
    if ( !( minimumNote > 0 ) )
    {
        return;
    }

    // Se atingiu nota minima exame, esconde campo EXAME, senao, exibe para nota ser digitada
    // Caso media fique muto baixa (abaixo do campo Nota mínima para não fazer exame / acddegree.examminimumnote), nao libera exame e marca estado como REPROVADO
    if ( ( newAverage >= minimumNote && newAverage < maximumNote ) && !reprovouPorFalta )
    {
        fieldExame.style.display = 'block';
        divImgExame.style.display = 'block';
        
        
        if ( degreeExame.exameSubstituiMenorNota )
        {
            notaFinal = fieldMedia.value;
        }
        else
        {
            notaFinal = fieldExame.value;
        }
        
        if ( notaFinal >= 0 )
        { 
            // nota de exame deve ser usada para o calculo da média final ao invés do seu pai.
            if ( degreeExame.substituiGrauPai )
            {
                // mantem nota final atual
            }
            else if ( degreeExame.mediaComGrauPai )
            {
                contador = 0;
                valor = 0;
                currentDegree = this.getTableDegree(degreeId);
                
                for ( i = 0; i < tableDegrees.length; i++ )
                {
                    if ( tableDegrees[i].parentDegreeId == currentDegree.parentDegreeId )
                    {
                        field = this.getFldDegreeElement(tableDegrees[i].degreeId, enrollId);
                        
                        if ( field.value )
                        {
                            valor = valor + field.value;
                        }
                        
                        contador ++;
                    }
                }                
                
                // Se a média é uma nota normal.
                if ( tableDegrees[posDegreeMedia].eNotaNormal )
                {
                    notaFinal = fieldNotaFinal.value;
                }
                else
                {
                    // nota final deve utilizar a média da nota de exame com o grau pai.
                    notaFinal = valor > 0 ? ( valor / contador ) : fieldNotaFinal.value;
                }
            }
        }
    }
    else
    {                
        fieldExame.style.display = 'none';
        divImgExame.style.display = 'none';

        notaFinal = newAverage;
    }

    // Caso estado atual seja REPROVADO POR FALTAS, nao deve ativar campo EXAME FINAL
    if ( reprovouPorFalta )
    {
        divFreqIns.style.visibility = 'visible';
        fieldExame.style.display = 'none';
        divImgExame.style.display = 'none';
        //divEvaluations.innerHTML = '<div />';
        
        divFreqIns.innerHTML = '<span class="m-label">Frequência insuficiente</span>';
    }
    else
    {
        divFreqIns.style.visibility = 'hidden';
    }
    
    if ( objDegreeEnroll.eGrauFinal && !(degreeExame.substituiGrauPai) )
    {
        notaFinal = objDegreeEnroll.escalaDoGrauFinal(notaFinal);
    }
    
    notaFinal = arredondaFloat(notaFinal);
    atualizaEstadoFrequencia(objDegreeEnroll, notaFinal);
    fieldNotaFinal.value = notaFinal;
}

/**
 * Recebe o estado da matrícula e faz os estilos de exibição
 */
function changeStatus(statusId)
{
    switch ( statusId )
    {
        case 'REPROVADO POR FALTAS':
            statusId = '<span style="color: red;font-weight:bold">'+ statusId +'</span>';
            break;
            
        case 'APROVADO':
            statusId = '<span style="color: blue;font-weight:bold">'+ statusId +'</span>';
            break;
            
        case 'REPROVADO':
            statusId = '<span style="color: blue;font-weight:bold">'+ statusId +'</span>';
            break;
            
        case 'CANCELADA':
            statusId = '<span style="color: red;font-weight:bold">'+ statusId +'</span>';
            break;
           
        case 'MATRICULADO':
            statusId = '<span style="font-weight:bold">'+ statusId +'</span>';
            break;
    }
    
    return statusId;
}

function atualizaEstadoFrequencia(objDegreeEnroll, average)
{
    currentFrequency = getCurrentEnrollFrequency(objDegreeEnroll.enrollId);
    isEvaluationByFrequency = objDegreeEnroll.evaluationTypeId == objDegreeEnroll.evaluationTypeByNote || objDegreeEnroll.evaluationTypeId == objDegreeEnroll.evaluationTypeByNoteAndFrequency;
    
    if ( isEvaluationByFrequency && ( ( currentFrequency && currentFrequency < objDegreeEnroll.minimumFrequency ) || !currentFrequency ) )
    {
        statusId = objDegreeEnroll.enrollStatusDisapprovedForLacks;
        objDegreeEnroll.setEstadoFuturoAtual(statusId);
    }
}

/**
 * Atualiza as notas em tempo real.
 */
function atualizaNotas(degreeId, enrollId)
{
    // Validacao de nota maxima
    if ( notaMaxima != null )
    {
        var idDegreeField = "degree[" + degreeId + "][" + enrollId + "]";
        var degreeField = document.getElementById(idDegreeField);
        var notaDigitada = degreeField.value;
        
        if ( notaDigitada > 0 && parseFloat(notaDigitada) > parseFloat(notaMaxima) )
        {
            degreeField.value = notaMaxima;
            
            alert('A nota máxima permitida é ' + notaMaxima);
        }
    }
    
    try
    {
        var nextDegree = degreeId;
    
        for ( x = 0; x < allDegreesId.length; x++ )
        {
            var idDegree = "degree[" + allDegreesId[x] + "][" + enrollId + "]";
            var objDegree = document.getElementById(idDegree);
            objDegree.focus();

            nextDegree = ( degreeId == allDegreesId[x] ) ? allDegreesId[x + 1] : nextDegree;
        }
        var nextIdDegree = "degree[" + nextDegree + "][" + enrollId + "]";
        document.getElementById(nextIdDegree).focus();
    }
    catch ( err )
    {
    }
}
