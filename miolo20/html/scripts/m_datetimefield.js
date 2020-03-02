var mdatetimefield =
{
    update: function (id) {
        var timeId = id + 'Time';
        var dateId = id + 'Date';

        var time = document.getElementById(timeId);
        var date = document.getElementById(dateId);
        var timestamp = document.getElementById(id);

        timestamp.value = '';

        if ( !MIOLO_Validate_Check_DATEDMY(date.value) )
        {
            date.value = '';
        }

        if ( !MIOLO_Validate_Check_TIME(time.value) )
        {
            time.value = '';
        }

        if ( date.value && time.value )
        {
            timestamp.value = date.value + ' ' + time.value;
            this.lastDate = date.value;
            this.lastTime = time.value;
        }
    }
}