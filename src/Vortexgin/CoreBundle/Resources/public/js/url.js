/**
 * Created by dailysocial on 18/04/17.
 * URL function collection
 * Author Tommy Dian P
 */
var URL = {
    getQuery: function(name){
        var url = location.search;
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(url);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    },
    getQueryToObject: function(){
        var queries = document.location.search.replace(/(^\?)/,'').split("&").map(function(n){return n = n.split("="),this[n[0]] = n[1],this}.bind({}))[0], 
            returns = {};
        Object.keys(queries).forEach(function(key) {
            if(!empty(queries[key])){
                returns[key] = queries[key];
            }
        });
        return returns;
    }
};
