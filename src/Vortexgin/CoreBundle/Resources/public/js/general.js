/* String Prototype Collection */
String.prototype.sha1 = function(){
      var str = this.toString();
      //  discuss at: http://phpjs.org/functions/sha1/
      // original by: Webtoolkit.info (http://www.webtoolkit.info/)
      // improved by: Michael White (http://getsprink.com)
      // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
      //    input by: Brett Zamir (http://brett-zamir.me)
      //  depends on: utf8_encode
      //   example 1: sha1('Kevin van Zonneveld');
      //   returns 1: '54916d2e62f65b3afa6e192e6a601cdbe5cb5897'

      var rotate_left = function(n, s) {
        var t4 = (n << s) | (n >>> (32 - s));
        return t4;
      };

      /*var lsb_hex = function (val) { // Not in use; needed?
        var str="";
        var i;
        var vh;
        var vl;

        for ( i=0; i<=6; i+=2 ) {
          vh = (val>>>(i*4+4))&0x0f;
          vl = (val>>>(i*4))&0x0f;
          str += vh.toString(16) + vl.toString(16);
        }
        return str;
      };*/

      var cvt_hex = function(val) {
        var str = '';
        var i;
        var v;

        for (i = 7; i >= 0; i--) {
          v = (val >>> (i * 4)) & 0x0f;
          str += v.toString(16);
        }
        return str;
      };

      var blockstart;
      var i, j;
      var W = new Array(80);
      var H0 = 0x67452301;
      var H1 = 0xEFCDAB89;
      var H2 = 0x98BADCFE;
      var H3 = 0x10325476;
      var H4 = 0xC3D2E1F0;
      var A, B, C, D, E;
      var temp;

      str = this.utf8_encode(str);
      var str_len = str.length;

      var word_array = [];
      for (i = 0; i < str_len - 3; i += 4) {
        j = str.charCodeAt(i) << 24 | str.charCodeAt(i + 1) << 16 | str.charCodeAt(i + 2) << 8 | str.charCodeAt(i + 3);
        word_array.push(j);
      }

      switch (str_len % 4) {
        case 0:
          i = 0x080000000;
          break;
        case 1:
          i = str.charCodeAt(str_len - 1) << 24 | 0x0800000;
          break;
        case 2:
          i = str.charCodeAt(str_len - 2) << 24 | str.charCodeAt(str_len - 1) << 16 | 0x08000;
          break;
        case 3:
          i = str.charCodeAt(str_len - 3) << 24 | str.charCodeAt(str_len - 2) << 16 | str.charCodeAt(str_len - 1) <<
            8 | 0x80;
          break;
      }

      word_array.push(i);

      while ((word_array.length % 16) != 14) {
        word_array.push(0);
      }

      word_array.push(str_len >>> 29);
      word_array.push((str_len << 3) & 0x0ffffffff);

      for (blockstart = 0; blockstart < word_array.length; blockstart += 16) {
        for (i = 0; i < 16; i++) {
          W[i] = word_array[blockstart + i];
        }
        for (i = 16; i <= 79; i++) {
          W[i] = rotate_left(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1);
        }

        A = H0;
        B = H1;
        C = H2;
        D = H3;
        E = H4;

        for (i = 0; i <= 19; i++) {
          temp = (rotate_left(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
          E = D;
          D = C;
          C = rotate_left(B, 30);
          B = A;
          A = temp;
        }

        for (i = 20; i <= 39; i++) {
          temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
          E = D;
          D = C;
          C = rotate_left(B, 30);
          B = A;
          A = temp;
        }

        for (i = 40; i <= 59; i++) {
          temp = (rotate_left(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
          E = D;
          D = C;
          C = rotate_left(B, 30);
          B = A;
          A = temp;
        }

        for (i = 60; i <= 79; i++) {
          temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
          E = D;
          D = C;
          C = rotate_left(B, 30);
          B = A;
          A = temp;
        }

        H0 = (H0 + A) & 0x0ffffffff;
        H1 = (H1 + B) & 0x0ffffffff;
        H2 = (H2 + C) & 0x0ffffffff;
        H3 = (H3 + D) & 0x0ffffffff;
        H4 = (H4 + E) & 0x0ffffffff;
      }

      temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);
      return temp.toLowerCase();
};

/* Object Prototype Collection */
Object.size = function(obj) {
  var size = 0,
      key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

function objectRefresh(obj){
  var tmp = obj.cloneNode(true);
  obj.parentNode.replaceChild(tmp, obj);
}

/* Date Function */
Date.prototype.customFormat = function(format){
    var format = format || 'Y-m-d',
        time = this.getFullYear()
            + '-' + ((this.getMonth()+1 <10)?'0':'') + (this.getMonth()+1)
            + '-' + ((this.getDate() <10)?'0':'') + this.getDate();
    if(format == 'd-m-Y'){
        time = ((this.getDate() <10)?'0':'') + this.getDate()
            + '-' + ((this.getMonth()+1 <10)?'0':'') + (this.getMonth()+1)
            + '-' + this.getFullYear();
    }else if(format == 'dayofmonth'){
      time = this.getFullYear()
          + '-' + ((this.getMonth()+1 <10)?'0':'') + (this.getMonth()+1)
          + '-01';
    }

    return time;
}

/* MISC Collection */
function assignBase64Image(src, hidden, target) {
    if (src.files && src.files[0]) {
        var FR = new FileReader();
        FR.onload = function (e) {
            var encode = e.target.result;
            var split = encode.split('base64,');
            jQuery(target).attr("src", e.target.result);
            jQuery(hidden).val(encode);
        };
        FR.readAsDataURL(src.files[0]);
    }
}

function round(value, precision, mode) {
  //  discuss at: http://phpjs.org/functions/round/
  // original by: Philip Peterson
  //  revised by: Onno Marsman
  //  revised by: T.Wild
  //  revised by: RafaÅ‚ Kukawski (http://blog.kukawski.pl/)
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

/**
 * @source http://phpjs.org/functions/empty/
 * @param {string} mixed_var
 * @returns {Boolean}
 */
function empty(mixed_var) {
    var undef, key, i, len;
    var emptyValues = [undef, null, false, 0, '', '0', 'undefined'];

    for (i = 0, len = emptyValues.length; i < len; i++) {
        if (mixed_var === emptyValues[i]) {
            return true;
        }
    }

    if (typeof mixed_var === 'object') {
        for (key in mixed_var) {
            return false;
        }
        return true;
    }

    return false;
}

// tiny helper function to add breakpoints
function getGridSize() {
    return (window.innerWidth < 600) ? 2 :
            (window.innerWidth < 900) ? 3 : 4;
}

function getParameterByName(name, url) {
    var url = url || location.search;
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(url);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}