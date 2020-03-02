dojo.declare("Miolo.editMask", null, {
    version: '0.1',
    h1: null, // keypress
    h2: null, // focus
    h3: null, // submit
    h4: null, // blur
    constructor: function (element, mask, optional, errMsg) {
        this.element = miolo.getElementById(element); 
        if (this.element != null)
        {
            this.mask = mask;
            this.errMsg = errMsg != '' ? errMsg : "Caracter invÃ¡lido!";
            this.optional = optional;
            var value = this.element.value ? this.filterStrip(this.element.value) : '';
            this.element.value = this.fillMask(value);
            this.fcolor = this.element.style.color == '' ? 'black' : element.style.color;
            this.first = true;
            this.element.editMask = this;
            this.h1 = dojo.connect(this.element,'keypress', this, this.process);
            this.h2 = dojo.connect(this.element,'focus', this, this.onEnter);
            this.h3 = dojo.connect(this.element,'submit', this, this.onSubmit);
        }
    },
    onEnter: function(event) {
        element = event.target;
        element.editMask.color = element.style.color;
        element.editMask.first = true;
        var value = element.value ? element.editMask.filterStrip(element.value) : '';
        element.value = element.editMask.fillMask(value);
    },
    onExit: function(event) {
        element = event.target;
		var ok = element.editMask.canBlur(element);
        if (ok)
        {
            dojo.disconnect(element.editMask.h4);
            element.editMask.first = true;
        }
        else {
            element.focus();
        }
        return ok;
    },
    canBlur: function(element) {
        var value = element.editMask.filterExit(element.value);
        var ok = true;
        if (!element.editMask.optional) {
           ok = (value.length == element.editMask.mask.length);
        }
        if (ok)
        {
            element.value = value;
            element.style.color = element.editMask.fcolor;
        }
        else {
            element.style.color = 'red';
            element.focus();
        }
        return ok;
    },
    onError: function(errMsg) {
        alert(errMsg);
    },
    onSubmit: function() {
        this.element.value = this.filterExit(this.element.value);
        return true;
    },
    process: function(event) {
        element = event.target;
        keyCode = event.charCode != 0 ? event.charCode : event.keyCode;
        editMask = element.editMask;
        if (element.editMask.first)
        {
            element.editMask.h4 = dojo.connect(element, 'blur', element.editMask, element.editMask.onExit);
            element.editMask.first = false;
        }
        filter = editMask.filterStrip(element.value);
        var filterTemp = '';
        if (keyCode==9) {
            return true;
        }
        else
        if (keyCode==8&&filter.length!=0) {
              filter = filter.substring(0,filter.length-1);
        }
        else if ( ((keyCode>47&&keyCode<122)) 
            && filter.length<editMask.filterMax() ) {
            filterTemp = filter + String.fromCharCode(keyCode);
        }
        else {
            filterTemp = filter;
        }
        var filterFinalMask = '';
        var filterFinal = editMask.validateMask(filterTemp);
        if (filterFinal) {
            filterFinalMask = editMask.fillMask(filterFinal);
        } else {
            filterFinalMask = editMask.fillMask(filter);
        }
        element.value = filterFinalMask;
        event.preventDefault();
        return false;
    },
    filterStrip: function(filterTemp) {
        mask = this.mask;
        for (var filterStep = 0; filterStep < mask.length++; filterStep++) {
            var c = mask.charAt(filterStep);
            if (this.isEditChar(c)) {
                  mask = this.filterReplace(mask,mask.substring(filterStep,filterStep+1),'');
            }
        }
        filterMask = mask + '_';
        for (var filterStep = 0; filterStep < filterMask.length++; filterStep++) {
            filterTemp = this.filterReplace(filterTemp,filterMask.substring(filterStep,filterStep+1),'');
        }
        return filterTemp;
    },
    filterMask: function(filterTemp) {
        filterMask = '_';
        for (var filterStep = 0; filterStep < filterMask.length++; filterStep++) {
            filterTemp = this.filterReplace(filterTemp,filterMask.substring(filterStep,filterStep+1),'');
        }
        return filterTemp;
    },
    filterMax: function() {
         filterTemp = this.mask;
        for (var filterStep = 0; filterStep < (this.mask.length+1); filterStep++) {
            var c = this.mask.charAt(filterStep);
            if (!this.isEditChar(c)) {
                filterTemp = this.filterReplace(filterTemp,c,'');
            }
        }
        return filterTemp.length;
    },
    filterExit: function(filterTemp) {
        mask = this.mask + '_';
        while ((filterTemp.length > 0) && (mask.indexOf(filterTemp.charAt(0))>-1))
        {
               filterTemp = filterTemp.substr(1);
        }
        return filterTemp;
    },
    filterReplace: function (fullString,text,by) {
        // Replaces text with by in string
        var strLength = fullString.length, txtLength = text.length;
        if ((strLength == 0) || (txtLength == 0)) return fullString;
        var i = fullString.indexOf(text);
        if ((!i) && (text != fullString.substring(0,txtLength))) return fullString;
        if (i == -1) return fullString;
        var newstr = fullString.substring(0,i) + by;
        if (i+txtLength < strLength)
           newstr += this.filterReplace(fullString.substring(i+txtLength,strLength),text,by);
        return newstr;
    },
    isEditChar: function(c) {  // is this char a meaningful mask char
        switch (c) {
        case "_":
        case "#":
        case "a":
        case "A":
        case "l":
        case "L":
            return true;
        default:
            return false;
        }
        return false;
    },
    displayMaskChar: function(c) {  // display mask chars as _ 
        if (this.isEditChar(c)) {       // otherwise just show normal char
            return "_";
        } else {
            return c;
        }
    },
    displayMask: function(mask) {  // display entire mask using about subroutine
        var d = "";
        for (var i = 0 ; i < mask.length ; i++) {
            d+=this.displayMaskChar(mask.substr(i,1));
        }
        return d;
    },
    validateMask: function(value) { 
        var ok = true;
        var nmask = this.mask.length - 1;
        var pos = posmask = 0;
        var n = value.length - 1;
        var filter = '';
        while ((pos <= n) && (posmask <= nmask))
        {
            var m = this.mask.charAt(posmask);
               var c = value.charCodeAt(pos);
            if (this.isEditChar(m)) {
                var code = this.isInsertOK(c, m);
                 if (ok = ok && (code != null)) {
                        filter = filter + String.fromCharCode(code);
                } else {
                    this.onError(this.errMsg);
                }
                pos += 1;
            }
            posmask += 1;
        }
        return ok ? filter : null;
    },
    fillMask: function(value) { 
        var filter = '';
        var n = value.length - 1;
        var nmask = this.mask.length - 1;
        var pos = n;
        var posmask = nmask;
        while (posmask >= 0)
        {
            var m = this.mask.charAt(posmask);
            if (pos >= 0)
            {
                 var c = value.charAt(pos);
                if (this.isEditChar(m)) {
                    filter = c + filter;
                    pos -= 1;
                }
                else {
                    filter = m + filter;
                }
            }
            else {
                if (this.isEditChar(m)) {
                    filter = '_' + filter;
                }
                else {
                    filter = m + filter;
                }
            }
            posmask -= 1;
        }
        return filter;
    },
    isInsertOK: function(code, mchar) {  // check if you're good to insert a char
//        var mchar = s.mask;
        switch (mchar) {
        case "_":
            return true;
            break;
        case "#":
            return this.checkDigit(code);
            break;
        case "a":
            return this.checkAlphaNumeric(code);
            break;
        case "A":
            return this.checkUpCaseAlphaNumeric(code);
            break;
        case "l":
            return this.checkAlpha(code);
            break;
        case "L":
            return this.checkUpCaseAlpha(code);
            break;
        }
        return false;
    },
    // functions to check the key code, good ol ASCII
    // fairly straightforward
    checkDigit: function(code) {
        if ((code>=48) && (code<=57)) {
            return code;
        } else {
            return null;
        }
    },
    checkAlpha: function(code) {
        if (((code>=65) && (code<=90)) || ((code>=97) && (code<=122))) {
            return code;
        } else {
            return null;
        }
    },
    checkUpCaseAlpha: function(code) {
        if ((code>=65) && (code<=90)) {
            return code;
        } else if ((code>=97) && (code<=122)) {
            return code - 32;
        } else {
            return null;
        }
    },
    checkAlphaNumeric: function(code) {
        if (((code>=65) && (code<=90)) || ((code>=97) && (code<=122)) || ((code>=48) && (code<=57))) {
            return code;
        } else {
            return null;
        }
    },
    checkUpCaseAlphaNumeric: function(code) {
        if ((code>=65) && (code<=90)) {
            return code;
        } else if ((code>=97) && (code<=122)) {
            return code - 32;
        } else if ((code>=48) && (code<=57)) {
            return code;
        } else {
            return null;
        }
    }
});
