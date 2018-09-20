/**
 * Created by dailysocial on 18/04/17.
 * Object function collection
 * Author Tommy Dian P
 */
Object.size = function (obj) {
    var size = 0,
        key;
    for (key in obj) {
        if (obj.hasOwnProperty(key))
            size++;
    }
    return size;
};
Object.toQuery = function (obj) {
    var keys = Object.keys(obj),
        arrQuery = [],
        query = '';
    for (var index = 0; index < keys.length; index++) {
        arrQuery.push(keys[index] + '=' + obj[keys[index]]);
    }
    query = arrQuery.join('&');

    return query;
};
Object.refresh = function (obj) {
    var tmp = obj.cloneNode(true);
    obj.parentNode.replaceChild(tmp, obj);
};

