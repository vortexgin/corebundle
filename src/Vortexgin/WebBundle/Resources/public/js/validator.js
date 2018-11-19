/**
 * Created by dailysocial on 18/04/17.
 * Validator function collection
 * Author Tommy Dian P
 */
var Validator = {
    object: {
        validate: function (_var, _key, _is) {
            var arrKey = _key.split('.'),
                curr;
            for (var index = 0; index < arrKey.length; index++) {
                curr = (index == 0) ? _var : curr;
                if (!curr.hasOwnProperty(arrKey[index])) {
                    return false;
                } else {
                    curr = curr[arrKey[index]];
                }
            }

            if (_is == 'number') {
            } else {
                if (empty(curr))
                    return false;
            }

            return true;
        }
    },
    array: {
        validate: function (_var, _key, _is) {

        }
    }, 
    escape: function(toOutput){
        if (typeof toOutput !== typeof undefined && toOutput !== false) {
            return toOutput.replace(/\&/g, '&amp;')
            .replace(/\</g, '&lt;')
            .replace(/\>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/\'/g, '&#x27')
            .replace(/\//g, '&#x2F');
        }

        return toOutput;
    }
};
