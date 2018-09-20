/**
 * Created by dailysocial on 18/04/17.
 * PHP function collection
 * Author Tommy Dian P
 */
var PHP = {

    unserialize: function (data) {
        var $global = (typeof window !== 'undefined' ? window : global)
        var utf8Overhead = function (str) {
            var s = str.length
            for (var i = str.length - 1; i >= 0; i--) {
                var code = str.charCodeAt(i)
                if (code > 0x7f && code <= 0x7ff) {
                    s++
                } else if (code > 0x7ff && code <= 0xffff) {
                    s += 2
                }
                // trail surrogate
                if (code >= 0xDC00 && code <= 0xDFFF) {
                    i--
                }
            }
            return s - 1
        }
        var error = function (type,
                              msg, filename, line) {
            throw new $global[type](msg, filename, line)
        }
        var readUntil = function (data, offset, stopchr) {
            var i = 2
            var buf = []
            var chr = data.slice(offset, offset + 1)
            while (chr !== stopchr) {
                if ((i + offset) > data.length) {
                    error('Error', 'Invalid')
                }
                buf.push(chr)
                chr = data.slice(offset + (i - 1), offset + i)
                i += 1
            }
            return [buf.length, buf.join('')]
        }
        var readChrs = function (data, offset, length) {
            var i, chr, buf
            buf = []
            for (i = 0; i < length; i++) {
                chr = data.slice(offset + (i - 1), offset + i)
                buf.push(chr)
                length -= utf8Overhead(chr)
            }
            return [buf.length, buf.join('')]
        }

        function _unserialize(data, offset) {
            var dtype
            var dataoffset
            var keyandchrs
            var keys
            var contig
            var length
            var array
            var readdata
            var readData
            var ccount
            var stringlength
            var i
            var key
            var kprops
            var kchrs
            var vprops
            var vchrs
            var value
            var chrs = 0
            var typeconvert = function (x) {
                return x
            }
            if (!offset) {
                offset = 0
            }
            dtype = (data.slice(offset, offset + 1)).toLowerCase()
            dataoffset = offset + 2
            switch (dtype) {
                case 'i':
                    typeconvert = function (x) {
                        return parseInt(x, 10)
                    }
                    readData = readUntil(data, dataoffset, ';')
                    chrs = readData[0]
                    readdata = readData[1]
                    dataoffset += chrs + 1
                    break
                case 'b':
                    typeconvert = function (x) {
                        return parseInt(x, 10) !== 0
                    }
                    readData = readUntil(data, dataoffset, ';')
                    chrs = readData[0]
                    readdata = readData[1]
                    dataoffset += chrs + 1
                    break
                case 'd':
                    typeconvert = function (x) {
                        return parseFloat(x)
                    }
                    readData = readUntil(data, dataoffset, ';')
                    chrs = readData[0]
                    readdata = readData[1]
                    dataoffset += chrs + 1
                    break
                case 'n':
                    readdata = null
                    break
                case 's':
                    ccount = readUntil(data, dataoffset, ':')
                    chrs = ccount[0]
                    stringlength = ccount[1]
                    dataoffset += chrs + 2
                    readData = readChrs(data, dataoffset + 1, parseInt(stringlength, 10))
                    chrs = readData[0]
                    readdata = readData[1]
                    dataoffset += chrs + 2
                    if (chrs !== parseInt(stringlength, 10) && chrs !== readdata.length) {
                        error('SyntaxError', 'String length mismatch')
                    }
                    break
                case 'a':
                    readdata = {}
                    keyandchrs = readUntil(data, dataoffset, ':')
                    chrs = keyandchrs[0]
                    keys = keyandchrs[1]
                    dataoffset += chrs + 2
                    length = parseInt(keys, 10)
                    contig = true
                    for (i = 0; i < length; i++) {
                        kprops = _unserialize(data, dataoffset)
                        kchrs = kprops[1]
                        key = kprops[2]
                        dataoffset += kchrs
                        vprops = _unserialize(data, dataoffset)
                        vchrs = vprops[1]
                        value = vprops[2]
                        dataoffset += vchrs
                        if (key !== i) {
                            contig = false
                        }
                        readdata[key] = value
                    }
                    if (contig) {
                        array = new Array(length)
                        for (i = 0; i < length; i++) {
                            array[i] = readdata[i]
                        }
                        readdata = array
                    }
                    dataoffset += 1
                    break
                default:
                    error('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype)
                    break
            }
            return [dtype, dataoffset - offset, typeconvert(readdata)]
        }

        return _unserialize((data + ''), 0)[2]
    },
    round: function (value, precision, mode) {
        //  discuss at: http://phpjs.org/functions/round/
        // original by: Philip Peterson
        //  revised by: Onno Marsman
        //  revised by: T.Wild
        //  revised by: Rafał Kukawski (http://blog.kukawski.pl/)
        //    input by: Greenseed
        //    input by: meo
        //    input by: William
        //    input by: Josep Sanz (http://www.ws3.es/)
        // bugfixed by: Brett Zamir (http://brett-zamir.me)
        //        note: Great work. Ideas for improvement:
        //        note: - code more compliant with developer guidelines
        //        note: - for implementing PHP constant arguments look at
        //        note: the pathinfo() function, it offers the greatest
        //        note: flexibility & compatibility possible
        //   example 1: round(1241757, -3);
        //   returns 1: 1242000
        //   example 2: round(3.6);
        //   returns 2: 4
        //   example 3: round(2.835, 2);
        //   returns 3: 2.84
        //   example 4: round(1.1749999999999, 2);
        //   returns 4: 1.17
        //   example 5: round(58551.799999999996, 2);
        //   returns 5: 58551.8

        var m, f, isHalf, sgn; // helper variables
        precision |= 0; // making sure precision is integer
        m = Math.pow(10, precision);
        value *= m;
        sgn = (value > 0) | -(value < 0); // sign of the number
        isHalf = value % 1 === 0.5 * sgn;
        f = Math.floor(value);

        if (isHalf) {
            switch (mode) {
                case 'PHP_ROUND_HALF_DOWN':
                    value = f + (sgn < 0); // rounds .5 toward zero
                    break;
                case 'PHP_ROUND_HALF_EVEN':
                    value = f + (f % 2 * sgn); // rouds .5 towards the next even integer
                    break;
                case 'PHP_ROUND_HALF_ODD':
                    value = f + !(f % 2); // rounds .5 towards the next odd integer
                    break;
                default:
                    value = f + (sgn > 0); // rounds .5 away from zero
            }
        }

        return (isHalf ? value : Math.round(value)) / m;
    }

};