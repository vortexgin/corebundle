var Cookie = {
    Create: function(key, value, path, days){
        var path = path || '/',
            days = days || 1,
            expires;

        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
        } else {
            expires = "";
        }

        document.cookie = encodeURIComponent(key) + "=" + encodeURIComponent(value) + expires + "; path=" + path;
    },
    Read: function(key){
        var nameEQ = encodeURIComponent(key) + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1, c.length);
            }

            if (c.indexOf(nameEQ) === 0) {
                return decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
        }

        return null;

    },
    Delete: function(key, path){
        var path = path || '/';
        Cookie.Create(key, "", path, -1); 
    }
};
