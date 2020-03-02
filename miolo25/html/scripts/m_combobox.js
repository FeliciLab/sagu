Miolo.prototype.comboBox = {
    onTextChange : function (label,textField,selectionList) {
        var tf = miolo.getElementById(textField);
        var sl = miolo.getElementById(selectionList);  
        var text = tf.value;
    
        for ( var i=0; i < sl.options.length; i++ ) {
            if ( sl.options[i].value == text ) {
                sl.selectedIndex = i;
                return;
            }
        }
    
        tf.value = '';
        tf.focus();
    },
    onSelectionChange : function (label,selectionList,textField) {
        var tf = miolo.getElementById(textField);
        var sl = miolo.getElementById(selectionList);  
        var index = sl.selectedIndex;
        if ( index != -1 ) {
            tf.value = String(sl.options[index].value);
        }
    } 
}