var dg = ''

function ascii_value(c)
    {
    // restrict input to a single character
    //	c = c . charAt (0);

    // loop through all possible ASCII values
    var i;

    for (i = 0; i < 256; ++i)
        {
        // convert i into a 2-digit hex string
        var h = i.toString(16);

        if (h.length == 1)
            h = "0" + h;

        // insert a % character into the string
        h = "%" + h;

        // determine the character represented by the escape code
        h = unescape(h);

        // if the characters match, we've found the ASCII value
        if (h == c)
            break;
        }

    return i;
    }

function makeArray(n)
    {
    for (var i = 1; i <= n; i++)
        {
        this[i] = 0
        }

    return this
    }

function rc4(key, text)
    {
    var i, x, y, t, x2, c;
    // status("rc4")
    s = makeArray(0);

    for (i = 0; i < 256; i++)
        {
        s[i] = i
        }

    y = 0

    for (x = 0; x < 256; x++)
        {
        //  y=(key.charCodeAt(x % key.length) + s[x] + y) % 256
        c = ascii_value(key.charAt(x % key.length))
        y = (c + s[x] + y) % 256
        t = s[x];
        s[x] = s[y];
        s[y] = t
        }

    x = 0;
    y = 0;
    var z = ""

    for (x = 0; x < text.length; x++)
        {
        x2 = x % 256
        y = (s[x2] + y) % 256
        t = s[x2];
        s[x2] = s[y];
        s[y] = t
        z += String.fromCharCode((text.charCodeAt(x) ^ s[(s[x2] + s[y]) % 256]))
        }

    return z
    }
