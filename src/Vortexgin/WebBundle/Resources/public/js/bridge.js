var Bridge = {
    host: window.location.protocol + '//' + window.location.hostname, 
    requestAction: function(url, method, param, options, cache) {
        if (empty(url)) {
            Bridge.errorMessage('Please insert url');
        } else if (empty(method)) {
            Bridge.errorMessage('Please insert method');
        } else {
            var def = {
                onSuccess: function(data, textStatus, jqXHR) {
                    return;
                },
                onFailed: function(jqXHR) {
                    return;
                },
                beforeSend: function() {
                    return;
                },
                complete: function() {
                    return;
                }
            };
            $.extend(def, options);

            var query = Object.toQuery(param),
                cacheKey = url,
                url = Bridge.host + url;
            if (!empty(cache) && cache > 0) {
                if (typeof(Storage) !== "undefined") {
                    if (!empty(query)) {
                        if (url.indexOf('?') < 0) {
                            cacheKey += '?' + query;
                        } else {
                            cacheKey += '&' + query;
                        }
                    }
                    var dataCache = localStorage.getItem(cacheKey);
                    if (!empty(dataCache)) {
                        var data = JSON.parse(dataCache);
                        if (Validator.object.validate(data, 'timestamp.date')) {
                            var cacheDate = new Date(data.timestamp.date.substr(0, 4), data.timestamp.date.substr(5, 2) - 1, data.timestamp.date.substr(8, 2), data.timestamp.date.substr(11, 2), data.timestamp.date.substr(14, 2), data.timestamp.date.substr(17, 2)),
                                now = new Date(),
                                diff = (cacheDate - now);
                            if (diff > 0) {
                                def.onSuccess(data, {}, {});
                                return;
                            }
                        }
                    }
                }
            }

            $.ajax({
                url: url,
                method: method,
                data: param,
                headers: {
                    'Access-Control-Allow-Origin': window.location.origin
                },
                crossDomain: true,
                xhrFields: {
                    withCredentials: true
                },
                beforeSend: function() {
                    def.beforeSend();
                },
                complete: function() {
                    def.complete();
                },
                success: function(data, textStatus, jqXHR) {
                    def.onSuccess(data, textStatus, jqXHR);
                    if (!empty(cache) && cache > 0) {
                        localStorage.setItem(cacheKey, JSON.stringify(data));
                    }
                },
                error: function(jqXHR) {
                    def.onFailed(jqXHR);
                }
            });
        }
    },
    requestFilesAction: function(url, method, param, options, cache) {
        if (empty(url)) {
            Bridge.errorMessage('Please insert url');
        } else if (empty(method)) {
            Bridge.errorMessage('Please insert method');
        } else {
            var def = {
                onSuccess: function(data, textStatus, jqXHR) {
                    return;
                },
                onFailed: function(jqXHR) {
                    return;
                },
                beforeSend: function() {
                    return;
                },
                complete: function() {
                    return;
                }
            };
            $.extend(def, options);

            $.ajax({
                url: url,
                method: method,
                data: param,
                processData: true,
                contentType: 'x-www-form-urlencoded',
                headers: {
                    'Access-Control-Allow-Origin': window.location.origin
                },
                crossDomain: true,
                xhrFields: {
                    withCredentials: true
                },
                beforeSend: function() {
                    def.beforeSend();
                },
                complete: function() {
                    def.complete();
                },
                success: function(data, textStatus, jqXHR) {
                    def.onSuccess(data, textStatus, jqXHR);
                },
                error: function(jqXHR) {
                    def.onFailed(jqXHR);
                }
            });
        }
    },
    successMessage: function(errorMessage) {
        toastr['success'](errorMessage);
    },
    errorMessage: function(errorMessage) {
        toastr['error'](errorMessage);
    }
};
