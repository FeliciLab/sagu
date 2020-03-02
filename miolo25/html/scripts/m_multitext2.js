dojo.declare("Miolo.MultiTextField2",null,
{
    mtfName: '',
	constructor: function(mtfName) {
        this.mtfName = mtfName;
    	this.leftSeparator = '[';
	    this.rightSeparator = ']';
        this.separator = this.rightSeparator + ' ' + this.leftSeparator;
	    this.emptyField = this.leftSeparator + this.rightSeparator;
	},
    onSubmit: function (formid, name) {
       var list = miolo.getElementById(this.mtfName + this.emptyField);
       if (list != null && list.options != null) {
          for (var i = 0; i < list.length; i++) {
            list.options[i].value = list.options[i].text;
            list.options[i].selected = true;
          }
       }
//       list.id = list.name = this.mtfName + this.emptyField;
       return true;
	},
    split: function (value) {
       return value.substring(1, value.length - 1).split(this.separator);
    },
    join: function (fields) {
       var value = this.leftSeparator;
       for (var i = 0; i < fields.length; i++) {
          if (i > 0) {
             value += this.separator;
          }
          value += fields[i];
       }
       value += this.rightSeparator;
       return value;
    },
    onKeyDown: function (source, frmObj, mtfName, event, numFields) {
       // IE and compatibles use 'keyCode', NS and compatibles 'which'
       var key = (document.all != null) ? event.keyCode : event.which;
       var name = mtfName + '_text';
       var len = name.length;
       if (source.name.substring(0, len) == name) {
          if (key == 13) { // enter key
             this.add(frmObj, mtfName, numFields);
             return false;
          }
       }
       else if (source.name == mtfName + this.emptyField) {
          if (key == 46) { // delete key
             this.remove(frmObj, mtfName, numFields);
             return false;
          }
       }
	},
    onSelect: function (numFields) {
       var list = miolo.getElementById(this.mtfName + this.emptyField);
       var i = list.selectedIndex;
       if (i != -1) {
          var a = this.split(list.options[i].text);
          for (var j = 1; j <= numFields; j++) {
              var tf = miolo.getElementById(this.mtfName + '_text' + j);
              if (tf != null) {
                 tf.value = a[j - 1];
              }
              else {
                 var op = miolo.getElementById(this.mtfName + '_options' + j);
                 if (op != null) {
                    // preselect option based on value
                    for (var n = 0; n < op.options.length; n++) {
                        if (op.options[n].text == a[j - 1]) {
                            op.selectedIndex = n;
                            break;
                        }
                    }
                 }
              }
          }
       }
       else {
          for (var j = 1; j <= numFields; j++) {
              var tf = miolo.getElementById(this.mtfName + '_text' + j);
              if (tf != null) {
                 tf.value = '';
              }
              else {
                 var op = miolo.getElementById(this.mtfName + '_options' + j);
                 if (op != null) {
                    op.selectedIndex = -1;
                 }
              }
          }
       }
    },
    getInput: function (numFields) {
       var list = miolo.getElementById(this.mtfName + this.emptyField);
       var fields = new Array(numFields);
       var value = '';
       for (var i = 1; i <= numFields; i++) {
           var tf = miolo.getElementById(this.mtfName + '_text' + i);
           fields[i - 1] = '';
           if (tf != null) {
              fields[i - 1] = tf.value;
           }
           else {
              var list = miolo.getElementById(this.mtfName + '_options' + i);
              if (list != null) {
                 fields[i - 1] = list.options[list.selectedIndex].text;
              }
           }
       }
       return this.join(fields);
    },
    add: function (numFields) {
       var list = miolo.getElementById(this.mtfName + this.emptyField);
       var i = list.length;
       list.options[i] = new Option(this.getInput(numFields));
       list.selectedIndex = i;
    },
    remove: function (numFields) {
       var list = miolo.getElementById(this.mtfName + this.emptyField);
       for (var i = 0; i < list.length; i++) {
           if (list.options[i].selected) {
              list.options[i] = null;
              if (i >= list.length) {
                 i = list.length - 1;
              }
              if (i >= 0) {
                 list.options[i].selected = true;
              }
              break;
           }
       }
    },
    modify: function (numFields)
    {
       var list = miolo.getElementById(this.mtfName + this.emptyField);
       var i = list.selectedIndex;
       if (i != -1) {
          list.options[i].text = this.getInput(numFields);
       }
       else {
          alert('Ã preciso selecionar o item a ser modificado!');
       }
    },
    moveUp: function (numFields)
    {
       var list = miolo.getElementById(this.mtfName + this.emptyField);
       var i = list.selectedIndex;
       if (i != -1) {
          if (i > 0) {
             var u = list.options[i - 1].text;
             list.options[i - 1].text = list.options[i].text;
             list.options[i - 1].selected = true;
             list.options[i].text = u;
             list.options[i].selected = false;
             list.selectedIndex = i - 1;
          }
       }
       else {
          alert('Ã preciso selecionar o item a ser modificado!');
       }
    },
    moveDown: function (numFields)
    {
       var list = miolo.getElementById(this.mtfName + this.emptyField);
       var i = list.selectedIndex;
       if (i != -1) {
          if (i < list.options.length - 1) {
             var u = list.options[i + 1].text;
             list.options[i + 1].text = list.options[i].text;
             list.options[i + 1].selected = true;
             list.options[i].text = u;
             list.options[i].selected = false;
             list.selectedIndex = i + 1;
          }
       }
       else {
          alert('Ã preciso selecionar o item a ser modificado!');
       }
    }
});
