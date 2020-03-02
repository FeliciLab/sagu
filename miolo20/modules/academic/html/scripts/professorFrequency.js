/**
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Samuel Koch [samuel@solis.cooop.br]
 * Cristian Edson Göhl [cristian@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 10/09/2007
 *
 * \b @organization \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyleft \n
 * Copyleft (L) 2007 - SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html )
 *
 * \b History \n
 * This function select and retun a value correctly from document javascript form
 */

var arrayImageSources = new Array();
var srcPresenceImage;
var srcHalfPresenceImage;
var srcAbsenceImage;
var srcEmptyImage;
var srcEmptyOldImage;

var enrolls = new Array();
var enrollsFrequency = new Array();

var countSchedule = 0;

var numberHours = new Array();
var allowHalfPresence;
var totalNumberHours = 0;

var foiModificado = false;

var enrollId_c;
var classOccurred_c;
var scheduleId_c;
var timeId_c;
var date_c;
var turnId_c;

function confirmExit()
{
    if ( this.foiModificado )
    {
        return "Alguns dados foram modificados sem salvar, deseja sair desta página?";
    }
    
    return null;    
}

/**
 * Objeto leve que corresponde a uma "bolinha" na tela.
 * Possui apenas as chaves principais.
 */
function objEnrollFrequency(enrollId, classOccurred, scheduleId, timeId, date, turnId)
{
    this.enrollId_c = enrollId;
    this.scheduleId_c = scheduleId;
    this.timeId_c = timeId;
    this.date_c = date;
    this.turnId_c = turnId;
    this.classOccurred_c = classOccurred;
}


/**
 * Objeto que corresponde a uma linha da tabela da tela ( uma aluno )
 * Este objeto possui varias frequencias ( objFrequency )
 */
function objEnroll(enrollId)
{
    this.enrollId = enrollId;
    this.frequencies = new Array();
    this.presenceHours = 0;
    this.absenceHours  = 0;
   
    this.addFrequency = function(objFrequency)
    {
        this.frequencies.push(objFrequency);
    };
    
    /**
     * Busca uma frequencia pelas "chave primaria"
     */
    this.findFrequencyPositionByPk = function(timeId, scheduleId, frequencyDate, turnId)
    {
        var found = -1;
        
        for ( x in this.frequencies )
        {
            if ( (this.frequencies[x].timeId == timeId) &&
                 (this.frequencies[x].scheduleId == scheduleId) &&
                 (this.frequencies[x].frequencyDate == frequencyDate) )
            {
                found = x;
            }
        }
        
        if ( found == -1 )
        {
            if ( ( timeId ) && ( scheduleId ) && ( frequencyDate ) && ( turnId ) )
            {
                found = 1;
            }
        }
        
        return found;
    };
    
    /**
     * Obtem uma frequencia por "chave primaria"
     */
    this.getFrequencyByPk = function(timeId, scheduleId, frequencyDate, turnId)
    {
        var pos = this.findFrequencyPositionByPk(timeId, scheduleId, frequencyDate, turnId);
        return pos > -1 ? this.frequencies[pos] : null;
    };

    this.setPresenceHours = function(presenceHours)
    {
        this.presenceHours = presenceHours;

        // Ja calcula automaticamente horas de ausencia
        var totalHours = getTotalNumberHours();

        this.setAbsenceHours( totalHours - this.presenceHours );
    };
    
    this.getPresenceHours = function()
    {
        return Math.round( this.presenceHours * 100 ) / 100;
    };   

    this.setAbsenceHours = function(absenceHours)
    {
        this.absenceHours = absenceHours;
    };
    
    this.getAbsenseHours = function()
    {
        return Math.round( this.absenceHours * 100 ) / 100;
    };
    
    /**
     * Obtem a porcentagem de presenca em relacao ao numero total de horas
     */
    this.getPercentPresence = function()
    {
        var value = 0;
        var presenceHours = this.getPresenceHours();
        var totalHours = getTotalNumberHours();
        
        // Faz verificacoes para nao exibir calculos invalidos (NaN / Infinity)
        if ( totalHours > 0 )
        {
            value = Math.round( (presenceHours / totalHours) * 100 );
        }
        
        return value;
    };
    
    /**
     * Obtem a porcentagem de ausencia em relacao ao numero total de horas
     */
    this.getPercentAbsence = function()
    {
        var percentPresence = this.getPercentPresence();
        return percentPresence > 0 ? (100 - percentPresence) : 0;
    };
}

/**
 * Objeto que corresponde a um item de frequencia da tela ( uma bolinha )
 */
function objFrequency(timeId, scheduleId, frequencyDate, turnId, numberHours)
{
    this.timeId = timeId;
    this.scheduleId = scheduleId;
    this.frequencyDate = frequencyDate;
    this.turnId = turnId;
    this.frequency = null;
    this.removeFrequency = 0;
    this.numberHours = numberHours;

    /**
     * Deve ser passado objEnroll pois a funcao js php_serialize() travava quando
     *  mantinha-se o objeto aqui.
     */
    this.setFrequency = function(objEnroll, frequency)
    {
        // Subtrai frequencia atual e depois soma nova
        if ( this.frequency > 0 )
        {
            objEnroll.setPresenceHours( objEnroll.presenceHours + ( this.numberHours[this.timeId] * this.frequency * -1 ) );
        }
        if ( frequency > 0 )
        {
            objEnroll.setPresenceHours( objEnroll.presenceHours + ( this.numberHours[this.timeId] * frequency ) );
        }
     
        this.frequency = frequency;
    };

    this.setRemoveFrequency = function(objEnroll, removeFrequency)
    {
        // Subtrai frequencia
        if ( removeFrequency == true )
        {
            objEnroll.setPresenceHours( objEnroll.presenceHours - ( this.numberHours[this.timeId] * this.frequency ) );
        }
        
        this.removeFrequency = removeFrequency;
    };
    
    this.getRemoveFrequency = function()
    {
        return this.removeFrequency;
    };
}

function setImageSources(urlPresence, urlHalfPresence, urlAbsence, urlEmpty, urlEmptyOld, urlJustifiedAbsense)
{
    this.srcPresenceImage = urlPresence;
    this.srcHalfPresenceImage = urlHalfPresence;
    this.srcAbsenceImage = urlAbsence;
    this.srcEmptyImage = urlEmpty;
    this.srcEmptyOldImage = urlEmptyOld;
    this.srcJustifiedAbsense = urlJustifiedAbsense;
    
    //
    this.arrayImageSources["presence"] = this.srcPresenceImage;
    this.arrayImageSources["halfPresence"] = this.srcHalfPresenceImage;
    this.arrayImageSources["absence"] = this.srcAbsenceImage;
    this.arrayImageSources["removeFrequency"] = this.srcEmptyImage;
    this.arrayImageSources["empty"] = this.srcEmptyOldImage;
    this.arrayImageSources["justifiedAbsense"] = this.srcJustifiedAbsense;
    this.arrayImageSources[""] = null;
}

/*
 * Define the duration (in hours) of the specified timeId and
 * define if half presences are allowed or not.
 */
function setNumberHours(timeId, numberHours, allowHalfPresence)
{
    this.numberHours[timeId] = numberHours;
    this.allowHalfPresence = allowHalfPresence;
}

function setSchedulesNumber(number)
{
    this.countSchedule = number;
}

function setData(timeId, scheduleId, enrollId, frequencyDate, turnId, type, func, imageTitle)
{
    var freqValue = null;

    switch ( type )
    {
        case 'presence':
            freqValue = 1;
        break;
        case 'halfPresence':
            freqValue = 0.5;
        break;
        case 'absence':
            freqValue = 0;
        break;
        case 'empty':
            freqValue = 'emptyFreq';
        break;
        case 'justifiedAbsense':
            freqValue = 1;
        break;
        case '':
            freqValue = 'emptyFreq';
        break;
    }

    // locate selected enrollId
    var pos = -1;

    for ( x in this.enrolls )
    {
        if ( this.enrolls[x].enrollId == enrollId )
        {
            pos = x;
            break;
        }
    }

    if ( pos == -1 )
    {
        return;
    }

    // add a new frequency object
    if ( this.enrolls[pos].frequencies )
    {
//        objFreq = this.enrolls[pos].getFrequencyByPk(timeId, scheduleId, frequencyDate, turnId);        

        var freq;
        var objFreq = null;
        var y = null;
        
        // Busca por objeto de frequencia existente
        for ( f in this.enrolls[pos].frequencies )
        {
            freq = this.enrolls[pos].frequencies[y];
            if ( freq )
            {
                if ( freq.timeId == timeId && freq.scheduleId == scheduleId && freq.frequencyDate == frequencyDate && freq.turnId == turnId )
                {
                    objFreq = freq;
                    break;
                }
            }
        }

        if ( objFreq == null )
        {
            objFreq = new objFrequency(timeId, scheduleId, frequencyDate, turnId, this.numberHours);
            this.enrolls[pos].addFrequency(objFreq);
        }
        
        objFreq.setFrequency( this.enrolls[pos], freqValue );
        objFreq.setRemoveFrequency( this.enrolls[pos], type == "removeFrequency" );
    }
    
    this.updateHtmlPresenceAbsence(this.enrolls[pos]);

    setImg(type, frequencyDate, timeId, scheduleId, enrollId, imageTitle)
}

function setImg(type, frequencyDate, timeId, scheduleId, enrollId, imageTitle)
{
    //Altera o .src da imagem (presenca/meiaPresenca/ausente)
    var element = document.getElementById('imgFreq_' + frequencyDate + '_' + timeId + '_' + scheduleId + '_' + enrollId);
    if ( element )
    {        
        element.src = this.arrayImageSources[type];
        element.title = imageTitle;

        if ( type.length == 0 )
        {
            element.src = this.arrayImageSources["empty"];
            element.title = "Sem frequencia registrada";
        }

        if ( type == 'justifiedAbsense' )
        {
            element.onclick = null;
        }

         //Desativa o clicar para alterar a frequencia
        if ( type == 'removeFrequency' )
        {
//            element.onclick = null;
        }
     }
}

/*
 * Add the duration of the specified timeId to the total
 * of hours for the enroll. At the end, this.totalNumberHours
 * will contain the total number of hours of the group
 */
function setTotalNumberHour(timeId)
{
    this.totalNumberHours += this.numberHours[timeId];
}

function setTotalNumberHours(hours)
{
    this.totalNumberHours = hours;
}

function addTotalNumberHours(hours)
{
    this.setTotalNumberHours( this.totalNumberHours + hours );
}

function subtractTotalNumberHours(hours)
{
    this.setTotalNumberHours( this.totalNumberHours - hours );
}

function getTotalNumberHours()
{
    var value = Math.round( this.totalNumberHours * Math.pow( 10 , 2 ) ) / Math.pow( 10 , 2 );
    if ( value == 0 )
    {
//        value = 10;
    }
    return value;
}

/**
 * Verifica se existe frequencia,
 * caso sim, atualiza a mesma,
 * do contrario verifica se o AddButton está off,
 * se sim insere frequencia como presente.
 */
function updateFrequency(timeId, frequencyDate, scheduleId, turnId, enrollId)
{
    var id = "descriAula_" + frequencyDate + "_" + timeId + "_" + scheduleId;    
    var foiRegistradoDescricao = document.getElementById(id).value;
    
    // Não pode registrar frequências sem antes registrar a descrição da aula.
    if ( foiRegistradoDescricao == 't' )
    {
        // find the enroll line to test
        this.foiModificado = true;

        var pos = -1;
        for ( x in this.enrolls )
        {
            if ( this.enrolls[x].enrollId == enrollId)
            {
                pos = x;
            }
        }

        // find the frequency within enroll line to process
        var objFrequency = this.enrolls[pos].getFrequencyByPk(timeId, scheduleId, frequencyDate, turnId);

        // Quando clicar em uma bolinha de frequencia removida, ignorar
        if ( objFrequency.getRemoveFrequency() == true )
        {
            return;
        }

        // Verifica se existe
        if ( objFrequency != null )
        {
            var type = '';
            var newFreq = 0;
            // if pupil has absence, turn into presence
            if ( ( objFrequency.frequency == 0 ) || ( objFrequency.frequency == 'emptyFreq') )
            {
                newFreq = 1;
                type = 'presence';
            }
            // if pupil has half presence, turn into absence
            else if ( objFrequency.frequency == 0.5 )
            {
                newFreq = 0;
                type = 'absence';
            }
            // if pupil has presence, turn into half presence
            else if ( objFrequency.frequency == 1 )
            {
                if ( allowHalfPresence )
                {
                    newFreq = 0.5
                    type = 'halfPresence';
                }
                else
                {
                    newFreq = 0;
                    type = 'absence';
                }
            }

            objFrequency.setFrequency( this.enrolls[pos], newFreq );        
            this.updateHtmlPresenceAbsence( this.enrolls[pos] );
            document.getElementById('divContent').innerHTML = "";
        }

        setImg(type, frequencyDate, timeId, scheduleId, enrollId, null)
    }
}

function setClassOccurred(classOccurred, timeId, scheduleId, frequencyDate, turnId)
{
    if ( classOccurred )
    {
        var hasFrequency = false;
        
        this.countSchedule = this.countSchedule + 1;
        this.addTotalNumberHours( this.numberHours[timeId] );

        //Busca se ja existe alguma frequencia
        for ( x in this.enrolls )
        {
            for ( y in this.enrolls[x].frequencies )
            {
                if ( this.enrolls[x].frequencies[y] )
                {
                    __freq = this.enrolls[x].frequencies[y];
                    if ( (__freq.timeId == timeId) &&
                         (__freq.frequencyDate == frequencyDate) &&
                         (__freq.scheduleId == scheduleId) &&
                         (__freq.getRemoveFrequency() == false ) )
                    {
                        hasFrequency = true;
                    }
                }
            }
        }

        if ( !hasFrequency )
        {
            for (x in this.enrolls)
            {
                setData(timeId, scheduleId, this.enrolls[x].enrollId, frequencyDate, turnId, 'presence', 'insert', null);
            }
        }
    }
    else
    {
        this.subtractTotalNumberHours( this.numberHours[timeId] );

        //Deixa todos alunos sem presenca/ausencia e bloqueia alteracoes no horario
        for ( x in this.enrolls )
        {
            for ( y in this.enrolls[x].frequencies )
            {
                freq = this.enrolls[x].frequencies[y];
                if ( (freq.timeId == timeId) && (freq.frequencyDate == frequencyDate) && (freq.scheduleId == scheduleId) )
                {
                    setData(timeId, scheduleId, this.enrolls[x].enrollId, frequencyDate, turnId, 'removeFrequency', 'update', null);
                }
            }
        }
    }
}

/**
 * Recebe um objeto instanciado objEnroll e atualiza o conteudo de presencas e ausencias no html
 */
function updateHtmlPresenceAbsence(objEnroll)
{
    // Estava ocorrendo erro na primeira vez ao carregar tela de frequencias,
    //  fazendo com que nao fosse exibido as frequencias e ausencias do aluno.
    if ( document.getElementById('lbPres_' + objEnroll.enrollId) == null )
    {
        return;
    }
    
    document.getElementById('lbPres_' + objEnroll.enrollId).innerHTML = objEnroll.getPresenceHours();
    document.getElementById('lbPercentPres_' + objEnroll.enrollId).innerHTML = '(' + objEnroll.getPercentPresence() + '%)';
    
    /**
     * Caso a presença seja 0 (faltou todas aulas), as horas de faltas recebem a carga horária total
     * e o percentual de faltas vai para 100%.
     */
    if ( objEnroll.presenceHours == '0' ) 
    {
        document.getElementById('lbAbs_' + objEnroll.enrollId).innerHTML = this.getTotalNumberHours();
        document.getElementById('lbPercentAbs_' + objEnroll.enrollId).innerHTML = '(100%)';
    }
    else
    {
        document.getElementById('lbAbs_' + objEnroll.enrollId).innerHTML = objEnroll.getAbsenseHours();
        document.getElementById('lbPercentAbs_' + objEnroll.enrollId).innerHTML = '(' + objEnroll.getPercentAbsence() + '%)';
    }
}


function showDescribeForm(response)
{
    xGetElementById('divMPopup').innerHTML = response;
    xGetElementById('mPopup').style.width="600px";
    xGetElementById('mPopup').style.minHeight="0px";

    mpopup.configureClose();
    mpopup.show();

    var script = xGetElementById('divMPopup').getElementsByTagName('script');
    if ( script.length > 0 )
    {
        for ( var i=0; i < script.length; i++ )
        {
            setTimeout(script[i].innerHTML, 0);
        }
    }
}