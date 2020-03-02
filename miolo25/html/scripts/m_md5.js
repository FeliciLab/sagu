/*
 *  md5.jvs 1.0b 27/06/96
 *
 * Javascript implementation of the RSA Data Security, Inc. MD5
 * Message-Digest Algorithm.
 *
 * Copyright (c) 1996 Henri Torgemane. All Rights Reserved.
 *
 * Permission to use, copy, modify, and distribute this software
 * and its documentation for any purposes and without
 * fee is hereby granted provided that this copyright notice
 * appears in all copies. 
 *
 * Of course, this soft is provided "as is" without express or implied
 * warranty of any kind.
 *
 * $Id: md5.js,v 1.1.1.1 2000/04/17 16:40:07 kk Exp $
 *
 * Adapted to Miolo by Ely Matos (ely.matos@ufjf.edu.br)
 */

function md5_array(n) {
  for(i=0;i<n;i++) this[i]=0;
  this.length=n;
}

dojo.declare("Miolo.md5",null,
{
    constructor: function() {
    },
    array: function (n) {
        var a;
        for(i=0;i<n;i++) a[i]=0;
        a.length=n;
        return a;
    },
 /* Some basic logical functions had to be rewritten because of a bug in
 * Javascript.. Just try to compute 0xffffffff >> 4 with it..
 * Of course, these functions are slower than the original would be, but
 * at least, they work!
 */
    integer: function (n) { return n%(0xffffffff+1); },
    shr: function (a,b) {
        a=this.integer(a);
        b=this.integer(b);
        if (a-0x80000000>=0) {
            a=a%0x80000000;
            a>>=b;
            a+=0x40000000>>(b-1);
        } else
            a>>=b;
        return a;
    },
    shl1: function(a) {
        a=a%0x80000000;
        if (a&0x40000000==0x40000000){
            a-=0x40000000;  
            a*=2;
            a+=0x80000000;
        } else
            a*=2;
        return a;
    },
    shl: function (a,b) {
        a=this.integer(a);
        b=this.integer(b);
        for (var i=0;i<b;i++) a=this.shl1(a);
        return a;
    },
    and: function (a,b) {
        a=this.integer(a);
        b=this.integer(b);
        var t1=(a-0x80000000);
        var t2=(b-0x80000000);
        if (t1>=0) 
            if (t2>=0) 
              return ((t1&t2)+0x80000000);
            else
              return (t1&b);
        else
            if (t2>=0)
              return (a&t2);
            else
              return (a&b);  
    },
    or: function (a,b) {
  a=this.integer(a);
  b=this.integer(b);
  var t1=(a-0x80000000);
  var t2=(b-0x80000000);
  if (t1>=0) 
    if (t2>=0) 
      return ((t1|t2)+0x80000000);
    else
      return ((t1|b)+0x80000000);
  else
    if (t2>=0)
      return ((a|t2)+0x80000000);
    else
      return (a|b);  
},
    xor: function (a,b) {
  a=this.integer(a);
  b=this.integer(b);
  var t1=(a-0x80000000);
  var t2=(b-0x80000000);
  if (t1>=0) 
    if (t2>=0) 
      return (t1^t2);
    else
      return ((t1^b)+0x80000000);
  else
    if (t2>=0)
      return ((a^t2)+0x80000000);
    else
      return (a^b);  
},
    not:function (a) {
  a=this.integer(a);
  return (0xffffffff-a);
},
/* Here begin the real algorithm */

    state : new md5_array(4),
    count : new md5_array(2),
//	count[0] = 0,
//	count[1] = 0,                     
    buffer : new md5_array(64),
    transformBuffer : new md5_array(16),
    digestBits : new md5_array(16),

    S11 : 7,
    S12 : 12,
    S13 : 17,
    S14 : 22,
    S21 : 5,
    S22 : 9,
    S23 : 14,
    S24 : 20,
    S31 : 4,
    S32 : 11,
    S33 : 16,
    S34 : 23,
    S41 : 6,
    S42 : 10,
    S43 : 15,
    S44 : 21,

    F: function (x,y,z) {
	return this.or(this.and(x,y),this.and(this.not(x),z));
    },

    G: function (x,y,z) {
	return this.or(this.and(x,z),this.and(y,this.not(z)));
    },

    H: function (x,y,z) {
	return this.xor(this.xor(x,y),z);
    },

    I: function(x,y,z) {
	return this.xor(y ,this.or(x , this.not(z)));
    },

    rotateLeft: function(a,n) {
	return this.or(this.shl(a, n),(this.shr(a,(32 - n))));
    },

    FF: function(a,b,c,d,x,s,ac) {
        a = a+this.F(b, c, d) + x + ac;
	a = this.rotateLeft(a, s);
	a = a+b;
	return a;
    },

    GG: function(a,b,c,d,x,s,ac) {
	a = a+this.G(b, c, d) +x + ac;
	a = this.rotateLeft(a, s);
	a = a+b;
	return a;
    },

    HH: function(a,b,c,d,x,s,ac) {
	a = a+this.H(b, c, d) + x + ac;
	a = this.rotateLeft(a, s);
	a = a+b;
	return a;
    },

    II: function(a,b,c,d,x,s,ac) {
	a = a+this.I(b, c, d) + x + ac;
	a = this.rotateLeft(a, s);
	a = a+b;
	return a;
    },

    transform: function(buf,offset) { 
	var a=0, b=0, c=0, d=0; 
	var x = this.transformBuffer;
	
	a = this.state[0];
	b = this.state[1];
	c = this.state[2];
	d = this.state[3];
	
	for (i = 0; i < 16; i++) {
	    x[i] = this.and(buf[i*4+offset],0xff);
	    for (j = 1; j < 4; j++) {
		x[i]+=this.shl(this.and(buf[i*4+j+offset] ,0xff), j * 8);
	    }
	}

	/* Round 1 */
	a = this.FF ( a, b, c, d, x[ 0], this.S11, 0xd76aa478); /* 1 */
	d = this.FF ( d, a, b, c, x[ 1], this.S12, 0xe8c7b756); /* 2 */
	c = this.FF ( c, d, a, b, x[ 2], this.S13, 0x242070db); /* 3 */
	b = this.FF ( b, c, d, a, x[ 3], this.S14, 0xc1bdceee); /* 4 */
	a = this.FF ( a, b, c, d, x[ 4], this.S11, 0xf57c0faf); /* 5 */
	d = this.FF ( d, a, b, c, x[ 5], this.S12, 0x4787c62a); /* 6 */
	c = this.FF ( c, d, a, b, x[ 6], this.S13, 0xa8304613); /* 7 */
	b = this.FF ( b, c, d, a, x[ 7], this.S14, 0xfd469501); /* 8 */
	a = this.FF ( a, b, c, d, x[ 8], this.S11, 0x698098d8); /* 9 */
	d = this.FF ( d, a, b, c, x[ 9], this.S12, 0x8b44f7af); /* 10 */
	c = this.FF ( c, d, a, b, x[10], this.S13, 0xffff5bb1); /* 11 */
	b = this.FF ( b, c, d, a, x[11], this.S14, 0x895cd7be); /* 12 */
	a = this.FF ( a, b, c, d, x[12], this.S11, 0x6b901122); /* 13 */
	d = this.FF ( d, a, b, c, x[13], this.S12, 0xfd987193); /* 14 */
	c = this.FF ( c, d, a, b, x[14], this.S13, 0xa679438e); /* 15 */
	b = this.FF ( b, c, d, a, x[15], this.S14, 0x49b40821); /* 16 */

	/* Round 2 */
	a = this.GG ( a, b, c, d, x[ 1], this.S21, 0xf61e2562); /* 17 */
	d = this.GG ( d, a, b, c, x[ 6], this.S22, 0xc040b340); /* 18 */
	c = this.GG ( c, d, a, b, x[11], this.S23, 0x265e5a51); /* 19 */
	b = this.GG ( b, c, d, a, x[ 0], this.S24, 0xe9b6c7aa); /* 20 */
	a = this.GG ( a, b, c, d, x[ 5], this.S21, 0xd62f105d); /* 21 */
	d = this.GG ( d, a, b, c, x[10], this.S22,  0x2441453); /* 22 */
	c = this.GG ( c, d, a, b, x[15], this.S23, 0xd8a1e681); /* 23 */
	b = this.GG ( b, c, d, a, x[ 4], this.S24, 0xe7d3fbc8); /* 24 */
	a = this.GG ( a, b, c, d, x[ 9], this.S21, 0x21e1cde6); /* 25 */
	d = this.GG ( d, a, b, c, x[14], this.S22, 0xc33707d6); /* 26 */
	c = this.GG ( c, d, a, b, x[ 3], this.S23, 0xf4d50d87); /* 27 */
	b = this.GG ( b, c, d, a, x[ 8], this.S24, 0x455a14ed); /* 28 */
	a = this.GG ( a, b, c, d, x[13], this.S21, 0xa9e3e905); /* 29 */
	d = this.GG ( d, a, b, c, x[ 2], this.S22, 0xfcefa3f8); /* 30 */
	c = this.GG ( c, d, a, b, x[ 7], this.S23, 0x676f02d9); /* 31 */
	b = this.GG ( b, c, d, a, x[12], this.S24, 0x8d2a4c8a); /* 32 */

	/* Round 3 */
	a = this.HH ( a, b, c, d, x[ 5], this.S31, 0xfffa3942); /* 33 */
	d = this.HH ( d, a, b, c, x[ 8], this.S32, 0x8771f681); /* 34 */
	c = this.HH ( c, d, a, b, x[11], this.S33, 0x6d9d6122); /* 35 */
	b = this.HH ( b, c, d, a, x[14], this.S34, 0xfde5380c); /* 36 */
	a = this.HH ( a, b, c, d, x[ 1], this.S31, 0xa4beea44); /* 37 */
	d = this.HH ( d, a, b, c, x[ 4], this.S32, 0x4bdecfa9); /* 38 */
	c = this.HH ( c, d, a, b, x[ 7], this.S33, 0xf6bb4b60); /* 39 */
	b = this.HH ( b, c, d, a, x[10], this.S34, 0xbebfbc70); /* 40 */
	a = this.HH ( a, b, c, d, x[13], this.S31, 0x289b7ec6); /* 41 */
	d = this.HH ( d, a, b, c, x[ 0], this.S32, 0xeaa127fa); /* 42 */
	c = this.HH ( c, d, a, b, x[ 3], this.S33, 0xd4ef3085); /* 43 */
	b = this.HH ( b, c, d, a, x[ 6], this.S34,  0x4881d05); /* 44 */
	a = this.HH ( a, b, c, d, x[ 9], this.S31, 0xd9d4d039); /* 45 */
	d = this.HH ( d, a, b, c, x[12], this.S32, 0xe6db99e5); /* 46 */
	c = this.HH ( c, d, a, b, x[15], this.S33, 0x1fa27cf8); /* 47 */
	b = this.HH ( b, c, d, a, x[ 2], this.S34, 0xc4ac5665); /* 48 */

	/* Round 4 */
	a = this.II ( a, b, c, d, x[ 0], this.S41, 0xf4292244); /* 49 */
	d = this.II ( d, a, b, c, x[ 7], this.S42, 0x432aff97); /* 50 */
	c = this.II ( c, d, a, b, x[14], this.S43, 0xab9423a7); /* 51 */
	b = this.II ( b, c, d, a, x[ 5], this.S44, 0xfc93a039); /* 52 */
	a = this.II ( a, b, c, d, x[12], this.S41, 0x655b59c3); /* 53 */
	d = this.II ( d, a, b, c, x[ 3], this.S42, 0x8f0ccc92); /* 54 */
	c = this.II ( c, d, a, b, x[10], this.S43, 0xffeff47d); /* 55 */
	b = this.II ( b, c, d, a, x[ 1], this.S44, 0x85845dd1); /* 56 */
	a = this.II ( a, b, c, d, x[ 8], this.S41, 0x6fa87e4f); /* 57 */
	d = this.II ( d, a, b, c, x[15], this.S42, 0xfe2ce6e0); /* 58 */
	c = this.II ( c, d, a, b, x[ 6], this.S43, 0xa3014314); /* 59 */
	b = this.II ( b, c, d, a, x[13], this.S44, 0x4e0811a1); /* 60 */
	a = this.II ( a, b, c, d, x[ 4], this.S41, 0xf7537e82); /* 61 */
	d = this.II ( d, a, b, c, x[11], this.S42, 0xbd3af235); /* 62 */
	c = this.II ( c, d, a, b, x[ 2], this.S43, 0x2ad7d2bb); /* 63 */
	b = this.II ( b, c, d, a, x[ 9], this.S44, 0xeb86d391); /* 64 */

	this.state[0] +=a;
	this.state[1] +=b;
	this.state[2] +=c;
	this.state[3] +=d;

    },

    init: function() {
	this.count[0]=this.count[1] = 0;
	this.state[0] = 0x67452301;
	this.state[1] = 0xefcdab89;
	this.state[2] = 0x98badcfe;
	this.state[3] = 0x10325476;
	for (i = 0; i < this.digestBits.length; i++)
	    this.digestBits[i] = 0;
    },

    update: function(b) { 
	var index,i;
	
	index = this.and(this.shr(this.count[0],3) , 0x3f);
	if (this.count[0]<0xffffffff-7) 
	  this.count[0] += 8;
        else {
	  this.count[1]++;
	  this.count[0]-=0xffffffff+1;
          this.count[0]+=8;
        }
	this.buffer[index] = this.and(b,0xff);
	if (index  >= 63) {
	    this.transform(this.buffer, 0);
	}
    },

    finish: function() {
	var bits = new md5_array(8);
	var	padding; 
	var	i=0, index=0, padLen=0;

	for (i = 0; i < 4; i++) {
	    bits[i] = this.and(this.shr(this.count[0],(i * 8)), 0xff);
	}
        for (i = 0; i < 4; i++) {
	    bits[i+4]=this.and(this.shr(this.count[1],(i * 8)), 0xff);
	}
	index = this.and(this.shr(this.count[0], 3) ,0x3f);
	padLen = (index < 56) ? (56 - index) : (120 - index);
	padding = new md5_array(64); 
	padding[0] = 0x80;
        for (i=0;i<padLen;i++)
	  this.update(padding[i]);
        for (i=0;i<8;i++) 
	  this.update(bits[i]);

	for (i = 0; i < 4; i++) {
	    for (j = 0; j < 4; j++) {
		this.digestBits[i*4+j] = this.and(this.shr(this.state[i], (j * 8)) , 0xff);
	    }
	} 
    },

/* End of the MD5 algorithm */

hexa: function(n) {
 var hexa_h = "0123456789abcdef";
 var hexa_c=""; 
 var hexa_m=n;
 for (hexa_i=0;hexa_i<8;hexa_i++) {
   hexa_c=hexa_h.charAt(Math.abs(hexa_m)%16)+hexa_c;
   hexa_m=Math.floor(hexa_m/16);
 }
 return hexa_c;
},


ascii : "01234567890123456789012345678901" + " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ"+
          "[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~",

MD5: function(entree) 
{
 var l,s,k,ka,kb,kc,kd;

 this.init();
 for (k=0;k<entree.length;k++) {
   l=entree.charAt(k);
   this.update(this.ascii.lastIndexOf(l));
 }
 this.finish();
 ka=kb=kc=kd=0;
 for (i=0;i<4;i++) ka+=this.shl(this.digestBits[15-i], (i*8));
 for (i=4;i<8;i++) kb+=this.shl(this.digestBits[15-i], ((i-4)*8));
 for (i=8;i<12;i++) kc+=this.shl(this.digestBits[15-i], ((i-8)*8));
 for (i=12;i<16;i++) kd+=this.shl(this.digestBits[15-i], ((i-12)*8));
 s=this.hexa(kd)+this.hexa(kc)+this.hexa(kb)+this.hexa(ka);
 return s; 
}
});
