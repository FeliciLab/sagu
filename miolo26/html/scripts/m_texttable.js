dojo.declare("Miolo.TextTable",null,
{
    constructor: function(id,zebra,cols) {
		this.id = id; 
		this.table = miolo.getElementById(id);
		this.rowSelected = '';
		this.lastRow = 0;
		this.onmouse = true;
		this.zebra = true;
		this.data = new Array();
		this.textTable = this;
		this.cols = cols;
    	this.iterate(this.init);  
	},
    setRowClass: function (obj, isRow, r) {
        obj.className = this.zebra ? 'row' + (r % 2) : 'row0';
        obj.baseClassName = this.oldClassName = obj.className;
    },
    setRowId: function (obj, r) {
        var tbl = obj.parentNode.parentNode;
        obj.id = tbl.id + 'row' + r;
    },
    init: function (obj, isRow,r,c,data,tbl, texttbl) {
        if (isRow) {
		   obj.textTable = texttbl;
	       texttbl.setRowId(obj, r);
	       texttbl.setRowClass(obj, isRow, r);
           obj.onclick = function() { 
               var tbl = this.textTable;
	           tbl.select(this.id);
           };
		   if (texttbl.onmouse)
		   {
               obj.onmouseover = function (){ 
				   this.oldClassName = this.className; 
				   this.className = 'hover'; 
			   };
               obj.onmouseout = function (){ 
				   this.className = this.oldClassName; 
			   }
		   }
		}
    },
	iterate: function(fnCallback, data) {
        var r, c;
        if (!fnCallback) { return; }
        for (r = 0; r < this.table.rows.length; ++r) {
           if (false == fnCallback(this.table.rows[r], true, r, c, data, this.table, this)) { return; }
           for (c = 0; c < this.table.rows[r].cells.length; ++c) {
              if (false == fnCallback(this.table.rows[r].cells[c], false, r, c, data, this.table, this)) { return; }
           }
        }
    },
	add: function(fields) {
        var tbody = miolo.getElementsByTagName('TBODY',this.table).item(0);
        var rows = miolo.getElementsByTagName('TR',tbody);
    	if (fields.length > 0) {
    	    var r = document.createElement("TR");
	    	tbody.appendChild(r);
    		for (var i = 0; i < fields.length; i++) {
        	    var c = document.createElement("TD");
	    		r.appendChild(c);
		    	var t = document.createTextNode(fields[i]);
			    c.appendChild(t);
            }
    	    var lastRow = this.lastRow++;
            this.init(r, true, lastRow, null, null, null, this);
		} 
	},
	drop: function(rowId) {
        var tbody = miolo.getElementsByTagName('TBODY',this.table).item(0);
		var row = miolo.getElementById(rowId);
		if (row)
		{
		    tbody.removeChild(row);
            var nrows = this.table.rows.length; 
            for (r = 0; r < nrows; ++r) {
			    this.setRowClass(this.table.rows[r], true, r);
			    this.setRowId(this.table.rows[r], r);
            }
			this.lastRow--;
		    this.rowSelected = '';
		}
	},
	modify: function(rowId, fields) {
		var row = miolo.getElementById(rowId);
    	if (fields.length > 0) {
            var cols = miolo.getElementsByTagName('TD',row);
        	if (cols.length > 0) {
        		for (var i = 0; i < cols.length; i++) {
	    		    cols[i].firstChild.nodeValue = fields[i]; 
				}
            }
		} 
	},
	select: function(rowId) {
        var row = miolo.getElementById(rowId);
    	if (rowId == this.rowSelected)
	    {
            row.className = row.baseClassName;
            row.oldClassName = row.baseClassName;
		    this.rowSelected = '';
     	}
	    else
	    {
            row.oldClassName = row.className;
	        row.className = 'hover'; 
	        if (this.rowSelected != '')
	        {
		        row = miolo.getElementById(this.rowSelected);
	            row.className = row.baseClassName;
	        }
	        this.rowSelected = rowId;
            this.customSelect();
        }
	},
	get: function(rowId) {
        var row = miolo.getElementById(rowId);
        var cols = miolo.getElementsByTagName('TD',row);
		var text = new Array(cols.length);
    	if (cols.length > 0) {
    		for (var i = 0; i < cols.length; i++) {
			    text[i] = cols[i].firstChild ? cols[i].firstChild.nodeValue : '';
            }
		} 
		return text;
	},
    setdata: function () {
        var r, c;
        for (r = 0; r < this.table.rows.length; ++r) {
			this.data[r] = new Array();
            for (c = 0; c < this.table.rows[r].cells.length; ++c) {
               this.data[r][c] = this.table.rows[r].cells[c].firstChild.nodeValue;
            }
        }
    },
	getdata: function() {
    	this.setdata();          
		return this.data;
	},
	customSelect: function() { 
	},
    unload: function() { 
		this.iterate(function (o) { o.onmouseover = o.onmouseout = null; }); 
	}
});