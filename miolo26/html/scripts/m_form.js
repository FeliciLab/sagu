dojo.declare("Miolo.Form", null, {
    id: null,
    onLoad: null,
    onSubmit: null,
    validators: null,
    connections: null,
    constructor: function(id) {
		this.id = id;
        this.connections = [];
	},
    setFocus: function (fieldName) {
		if (fieldName == '') {
			var element = null;
			var f = miolo.getElementById(this.id);
    	    var children = f.getElementsByTagName('input');
			if (children.length == 0) {
        	    var children = f.getElementsByTagName('select');
    			if (children.length > 0) {
					element = children[0];
				}
			} else {
				element = children[0];
			}
		} else {
			var element = miolo.getElementById(fieldName);
		}
        if (element != null) {
           element.focus();
        }
    },
	getInputs: function() {
      var getstr = new Object();
	  var f = miolo.getElementById(this.id);
      var inputs = f.getElementsByTagName('input');
      for (var i = 0, length = inputs.length; i < length; i++) {
	      var input = inputs[i];
		  if ((input.type == "text") || (input.type == "hidden")) {
			  if (getstr[input.name])
			  {
	  			  getstr[input.name] += "&" + input.value;
			  } else {
    			  getstr[input.name] = input.value;
			  }
		  }
		  if (input.type ==	"checkbox") {
			  if (input.checked) {
				  getstr[input.name] = (input.value == '' ? 'on' : input.value);
			  }
		  } 
		  if (input.type ==	"radio") {
			  if (input.checked) {
				  getstr[input.name] = input.value;
			  }
		  } 
      }
      var inputs = f.getElementsByTagName('select');
      for (var i = 0, length = inputs.length; i < length; i++) {
	      var input = inputs[i];
		  getstr[input.name] = input.options[input.selectedIndex].value;
	  }
	  return getstr;
	},
    getForm: function() {
        return miolo.getElementById('frm_'+this.id);               
    },
    setAction: function(url) {
        miolo.getElementById('frm_'+this.id).action = url;               
    },
    getAction: function() {
        return miolo.getElementById('frm_'+this.id).action;
    },
    setEnctype: function(enctype) {
        miolo.getElementById('frm_'+this.id).setAttribute('enctype', enctype);
    },
    connect: function(elementId, event, handler) {
        var node = dojo.byId(elementId);
        if (!node) return;
        this.connections.push(
           dojo.connect(node,event,handler)
        );
    },
    disconnect: function() {
        dojo.forEach(this.connections, dojo.disconnect);
        this.connections.length = 0;
    },
    hideScroll: function() {
        if (document.all && document.createAttribute && document.compatMode != 'BackCompat') {
            // IE6 (and above) in standards mode
            document.getElementsByTagName('html')[0].style.overflow = 'hidden'; 
        } else {
            document.body.style.overflow = 'hidden';
        }
    },
    showScroll: function() {
        if (document.all && document.createAttribute && document.compatMode != 'BackCompat') {
            // IE6 (and above) in standards mode
            document.getElementsByTagName('html')[0].style.overflow = '';
        } 
        else {
            document.body.style.overflow = '';
        }
    }
});