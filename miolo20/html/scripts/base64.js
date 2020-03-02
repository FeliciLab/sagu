/* Copyright (C) 2001 Donald J Bindner.
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 */

var b64chr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

function arrayToBase64( arr ) {
    /* Converts an array of values to a base64 string */
    var i;
    var str = new String();
    var l = 0;

    for( i = 0; i < arr.length - (arr.length%3); i += 3 ) {
	str += b64chr.charAt( arr[i]>>2 );
	str += b64chr.charAt( ((arr[i]&3)<<4) + (arr[i+1]>>4) );
	str += b64chr.charAt( ((arr[i+1]&15)<<2) + (arr[i+2]>>6) );
	str += b64chr.charAt( arr[i+2]&63 );

	l += 4;
	if( l >= 60 ) {
	    str += "\n";
	    l = 0;
	}
    }

    i = arr.length-(arr.length%3);

    if( arr.length%3 == 1 ) {
	str += b64chr.charAt( arr[i]>>2 );
	str += b64chr.charAt( (arr[i]&3)<<4 );
	str += "==";
    } else if( arr.length %3 == 2 ) {
	str += b64chr.charAt( arr[i]>>2 );
	str += b64chr.charAt( ((arr[i]&3)<<4) + (arr[i+1]>>4) );
	str += b64chr.charAt( (arr[i+1]&15)<<2 );
	str += "=";
    }

    return str;
}

function stringToBase64( str ) {
    /* Returns the base64 representation of a string
     * Crude: converts the string to an array and then calls arrayToBase64()
     */
    var arr = new Array( str.length );

    for( var i = 0; i < str.length; i++ ) arr[i] = str.charCodeAt(i);

    return arrayToBase64( arr );
}

function base64ToArray( str ) {
    /* Decodes a base64 string to an array of values */
    var arr = new Array;
    var l = 0;
    var pad = 0;

    for( var i = 0; i < str.length; ) {
	while( i<str.length  &&  b64chr.indexOf( str.charAt(i)) == -1 ) i++;
	if( i >= str.length ) break;
	if( str.charAt(i) == "=" ) break;
	a = b64chr.indexOf( str.charAt( i++ ));

	while( i<str.length  &&  b64chr.indexOf( str.charAt(i)) == -1 ) i++;
	if( i >= str.length ) break;
	if( str.charAt(i) == "=" ) break;
	b = b64chr.indexOf( str.charAt( i++ ));

	while( i<str.length  &&  b64chr.indexOf( str.charAt(i)) == -1 ) i++;
	if( i >= str.length ) break;
	if( str.charAt(i) == "=" ) {
	    pad = 2;
	    c = d = 0;
	    break;
	}
	c = b64chr.indexOf( str.charAt( i++ ));

	while( i<str.length  &&  b64chr.indexOf( str.charAt(i)) == -1 ) i++;
	if( i >= str.length ) break;
	if( str.charAt(i) == "=" ) {
	    pad = 1;
	    d = 0;
	    break;
	}
	d = b64chr.indexOf( str.charAt( i++ ));

	arr[l++] = ((a<<2) + (b>>4)) & 0xFF;
	arr[l++] = ((b<<4) + (c>>2)) & 0xFF;
	arr[l++] = ((c<<6) + d) & 0xFF;
    }

    if( pad == 2 ) {
	arr[l++] = ((a<<2) + (b>>4)) & 0xFF;
    } else if( pad == 1 ) {
	arr[l++] = ((a<<2) + (b>>4)) & 0xFF;
	arr[l++] = ((b<<4) + (c>>2)) & 0xFF;
    }

    return arr;
}

function base64ToString( str ) {
    /* Decodes a base64 tring to a regular string
     * Crude: calls base64ToArray() and converts it back to a string
     */
    var arr = base64ToArray( str );
    var rstr = new String;

    for( var i = 0; i < arr.length; i++ ) rstr += String.fromCharCode(arr[i]);

    return rstr;
}

