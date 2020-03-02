dojo.declare("Miolo.Hash",null, {
	length: 0,
	items: new Array(),
    constructor: function() {
        this.length = 0;
    },
	remove: function(in_key) {
		var tmp_value;
		if (typeof(this.items[in_key]) != 'undefined') {
			this.length--;
			var tmp_value = this.items[in_key];
			delete this.items[in_key];
		}
		return tmp_value;
	},
	get: function(in_key) {
		return this.items[in_key];
	},
	set: function(in_key, in_value) {
		if (typeof(in_value) != 'undefined') {
			if (typeof(this.items[in_key]) == 'undefined') {
				this.length++;
			}
			this.items[in_key] = in_value;
		}
		return in_value;
	},
	has: function(in_key) {
		return typeof(this.items[in_key]) != 'undefined';
	}
});