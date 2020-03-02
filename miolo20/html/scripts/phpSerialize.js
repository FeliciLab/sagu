/*
* PHP Serialize
* Morten Amundsen
* mor10am@gmail.com
*/
function php_serialize(obj)
{
    var string = ''

    if (typeof(obj) == 'object') {
        if (obj instanceof Array) {
            string = 'a:'
            tmpstring = ''
            var count = 0
            for (var key in obj) {
                tmpstring += php_serialize(key)
                tmpstring += php_serialize(obj[key])
                count++
            }
            string += count + ':{'
            string += tmpstring
            string += '}'
        } else if (obj instanceof Object) {
            classname = obj.toString()

            if (classname == '[object Object]') {
                classname = 'StdClass'
            }

            string = 'O:' + classname.length + ':"' + classname + '":'
            tmpstring = ''
            var count = 0
            for (var key in obj) {
                if ( typeof(obj[key]) != 'function' )
                {
                    tmpstring += php_serialize(key)
                    if (obj[key]) {
                        tmpstring += php_serialize(obj[key])
                    } else {
                        tmpstring += php_serialize('')
                    }
                    count++
                }
            }
            string += count + ':{' + tmpstring + '}'
        }
    } else {
        switch (typeof(obj)) {
            case 'number':
                if (obj - Math.floor(obj) != 0) {
                    string += 'd:' + obj + ';'
                } else {
                    string += 'i:' + obj + ';'
                }
                break
            case 'string':
                string += 's:' + obj.length + ':"' + obj + '";'
                break
            case 'boolean':
                if (obj) {
                    string += 'b:1;'
                } else {
                    string += 'b:0;'
                }
                break
        }
    }

    return string
}
