/**
 * Created by dailysocial on 18/04/17.
 * document function collection
 * Author Tommy Dian P
 */
document.updateMetaTitle = function (title, path) {
    if (window.location.pathname != path) {
        window.history.pushState(title, title, path);
        document.title = title;
    }
};
document.getGridSize = function(){
    return (window.innerWidth < 600) ? 2 :
        (window.innerWidth < 900) ? 3 : 4;
};
document.textarea2array = function(id){
    var split = document.getElementById(id).value.replace(/\r\n/g,"\n").split("\n"),
        valid = [];
    for(var index=0;index<split.length;index++){
        if(!empty(split[index])){
            valid.push(split[index]);
        }
    }

    return valid;
};
document.assignBase64Image = function(src, hidden, target) {
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
};
