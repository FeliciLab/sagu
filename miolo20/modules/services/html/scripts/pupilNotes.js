/**
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @since
 * Class created on 13/09/2007
 *
 * \b @organization \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyleft \n
 * Copyleft (L) 2005 - SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html )
 *
 * \b History \n
 * This function select and retun a value correctly from document javascript form 
 */

/*
 * Object to store a single evaluation entry.
 *
 * Contains the evaluation id and its weight.
 */
function objEvaluation(evaluationId, weight)
{
    this.evaluationId = evaluationId
    this.weight       = weight
}

/*
 * Object to store a single degree along with its evaluations
 */
function objDegree(degreeId, weight)
{
    this.degreeId = degreeId
    this.weight   = weight

    this.evaluations = new Array()

    /*
     * Adds a new evaluation object to the list of evaluations
     *
     * @param objEvaluation (object): an objEvaluation object
     */
    this.addEvaluation = function(objEvaluation)
    {
        this.evaluations.push(objEvaluation)
    }

}

/*
 * Contains all information related to one enroll entry
 *
 * @param enrollId(int): enroll identificator
 * @param averageWeight(float): the weight of the average used to calculate the final note
 * @param examWeight(float): the weight of the exam also used to calculate the final note
 * @param freeOfExamAverage(float): the necessary average for being free of the exam
 * @param approveAverage(float): the necessary average for being approved
 *
 * @return (null): nothing
 */
function objEnroll(enrollId, averageWeight, examWeight, freeOfExamAverage, approveAverage)
{
    // enroll identificator
    this.enrollId = enrollId
    // weight of the average for calculating the final note
    this.averageWeight = averageWeight
    // weight of the exam for calculating the final note
    this.examWeight = examWeight

    // minimum note for being free of the exam
    this.freeOfExamAverage = freeOfExamAverage
    // minimum note for being approved
    this.approveAverage = approveAverage

    // an array containing all degrees associated with this enroll
    this.degrees = new Array()

    // boolean indicating this pupil is free of exam
    this.isFreeOfExam = false
    // boolean indicating this pupil is approved
    this.isApproved = false

    /*
     * Adds a new degree to this enroll object
     *
     * @param objDegree (object): a degree object
     */
    this.addDegree = function(objDegree)
    {
        this.degrees.push(objDegree)
    }

    /*
     * Find a degree in the local list of degrees, querying by degreeId
     *
     * @param degreeId (int): the degree identificator
     *
     * @return (objDegree): the degree object or null if not found
     */
    this.getDegree = function(degreeId)
    {
        var i
        var retVal = null
        for ( i=0; i<this.degrees.length; i++ )
        {
            if ( this.degrees[i].degreeId == degreeId )
            {
                retVal = this.degrees[i]
                break
            }
        }
        return retVal
    }

    /*
     * Calculate the average of the informed degree by iterating through all evaluations
     * associated with the degree
     *
     * @param degreeId (int): the degree identificator
     */
    this.updateDegree = function(degreeId)
    {
        var i
        var noteField
        var weight
        var noteSum = 0
        var weightSum = 0

        var allFieldsValid = true

        for ( i=0; i<this.getDegree(degreeId).evaluations.length && allFieldsValid; i++ )
        {
            noteField = document.getElementById('evaluation[' + this.enrollId + '][' + degreeId + '][' + this.getDegree(degreeId).evaluations[i].evaluationId + ']')
            weight = this.getDegree(degreeId).evaluations[i].weight

            // make sure all fields are valid in order to generate a valid degree too
            allFieldsValid = (! isNaN(parseFloat(noteField.value)))

            noteSum += parseFloat(noteField.value) * weight
            weightSum += weight
        }

        if ( allFieldsValid )
        {
            // set the note of this degree
            document.getElementById('degree[' + this.enrollId + '][' + degreeId + ']').value = (noteSum / weightSum)

            // as the degree has changed, average must also change
            this.updateAverage()
        }
    }

    /*
     * Calculate the average of all degrees by iterating through them
     */
    this.updateAverage = function()
    {
        var i
        var noteField
        var weight
        var noteSum = 0
        var weightSum = 0

        var allFieldsValid = true

        for ( i=0; i<this.degrees.length && allFieldsValid; i++ )
        {
            noteField = document.getElementById('degree[' + this.enrollId + '][' + this.degrees[i].degreeId + ']')
            weight = this.degrees[i].weight

            // make sure all fields are valid in order to generate a valid degree too
            allFieldsValid = (! isNaN(parseFloat(noteField.value)))

            noteSum += parseFloat(noteField.value) * weight
            weightSum += weight
        }

        if ( allFieldsValid )
        {
            // set the average field value
            document.getElementById('average[' + this.enrollId + ']').value = (noteSum / weightSum)

            // as average has changed, final note will also change
            this.updateFinalNote()
        }
    }

    /*
     * Calculate the final note based on average and exam notes
     */
    this.updateFinalNote = function()
    {
        var averageField = document.getElementById('average[' + this.enrollId + ']')
        var examField = document.getElementById('exam[' + this.enrollId + ']')

        var finalNote;

        this.isFreeOfExam = (parseFloat(averageField.value) >= this.freeOfExamAverage)

        var allFieldsValid = (! (isNaN(parseFloat(averageField.value))) && ( ! (isNaN(parseFloat(examField.value))) || this.isFreeOfExam))

        if ( allFieldsValid )
        {
            // if pupil is free of exam, we must only copy the average value to the final note
            if ( this.isFreeOfExam )
            {
                finalNote = averageField.value
            }
            else
            {
                var noteSum = parseFloat(averageField.value) * this.averageWeight + parseFloat(examField.value) * this.examWeight
                var weightSum = this.averageWeight + this.examWeight
                finalNote = (noteSum / weightSum)
            }

            // set final note field
            document.getElementById('finalNote[' + this.enrollId + ']').value = finalNote
            

            this.updateApprovalStatus()
        }
        
        // disable exam field if it is not necessary
        document.getElementById('exam[' + this.enrollId + ']').disabled = this.isFreeOfExam
    }

    this.updateApprovalStatus = function()
    {
        // set final note field
        var finalNote = document.getElementById('finalNote[' + this.enrollId + ']')

        if ( ! isNaN(parseFloat(finalNote.value)) )
        {
            this.isApproved = (parseFloat(finalNote.value) >= this.approveAverage)

            // control the pupil approval status
            if ( this.isApproved )
            {
                document.getElementById('status[' + this.enrollId + ']').src = approvedImage
                document.getElementById('status[' + this.enrollId + ']').title = approvedText
            }
            else
            {
                document.getElementById('status[' + this.enrollId + ']').src = reprovedImage
                document.getElementById('status[' + this.enrollId + ']').title = reprovedText
            }
        }

        // hide information message
        // FIXME: component name should be divInfo and not m_divInfo
        divInfo = document.getElementById('m_divInfo')
        if ( divInfo != null )
        {
            divInfo.style.display = 'none'
        }
    }
}

