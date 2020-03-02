dojo.declare("Miolo.DnD",null,
{
    id: '',
    dropped: null,
    constructor: function(id) {
		this.id = id; 
		this.dropped = new Array();
	},
	onDrop: function(dropOn, s ,n , c) {
        var sid = s.node.id;
        var obj = this;
        dojo.forEach(n, function (e,i,a) {obj.dropped.push(e.id + '=' + dropOn);});
	},
	onSubmit: function() {
		var s = '';
        dojo.forEach(this.dropped, function (e,i,a) {s = s + ((s == '') ? '' : '&') + e}); 
		dojo.byId(this.id).value = s;
        return true;
	}
});
