window.cookies = {
    get: function (name) {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    },
    set: function (name, value, options) {
        options = options || {};
        var expiresShift = options.expires;
        if (typeof expiresShift == "number") {
            expiresTime = new Date((new Date).getTime() + expiresShift*1000);
        }
        options.expires = expiresTime.toUTCString();
        value = encodeURIComponent(value);
        var updatedCookie = name + "=" + value;
        for(var propName in options) {
            updatedCookie += "; " + propName;
            var propValue = options[propName];
            if (propValue !== true) {
                updatedCookie += "=" + propValue;
            }
        }
        document.cookie = updatedCookie;
    },
    del: function (name) {
        setCookie(name, "", { expires: -1 });
    }
}

function checkUrl() {
    var uri = document.documentURI;
    if (uri.indexOf('access_token') != -1) {
        var pureParams = uri.slice(uri.indexOf('#')+1),
            parseQueryString = function( queryString ) {
                var params = {},queries,temp,i,l;
                queries = queryString.split("&");
                for (i=0,l=queries.length;i<l;i++){
                    temp = queries[i].split('=');
                    params[temp[0]] = temp[1];}
                return params;
            },
            params = parseQueryString(pureParams),
            expires = params.expires_in == 0 ? 31536e4 : params.expires_in,
            correction = 100;
        delete params['expires_in'];

        for (var item in params) {
            console.log(item+' -> '+params[item]);
            cookies.set('vk_'+item, params[item], {'expires': expires - correction, 'path': '/'});
        }
        return true;
    } else {
        return false;
    }
}

function getToken(scope) {
    var ArrayToURL = function (array) {
            var pairs = [];
            for (var key in array)
                if (array.hasOwnProperty(key))
                    pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(array[key]));
            return pairs.join('&');
        },
        params = {
            'client_id': '4543802',
            'scope': scope,
            'redirect_uri': 'http://room733.ru',
            'display': 'page',
            'v': '5.24',
            'response_type': 'token'
        }
    window.location.replace("https://oauth.vk.com/authorize?"+ArrayToURL(params));
}

$(function(){
    $('#form-add').hide();
    $('#posts').hide();
    if (token = cookies.get('vk_access_token')) {
        $('#posts').show();
        var user_email = cookies.get('vk_email'),
            user_id = cookies.get('vk_user_id');

        VK.init({
            apiId: 4543802
        });

        VK.Api.call('users.get', {user_ids: user_id, fields: 'photo_50'}, function(r) {
            if(r.response) {
                $('#author').attr('disabled','disabled').val(r.response[0].first_name+' '+r.response[0].last_name);
            }
        });

        function apiQuery(params, callback) {
            $.ajax({
                type: 'post',
                cache: false,
                url: 'http://room733.ru',
                data: params,
                success: function (data) {
                    callback();
                }
            });
        }

        $('#activate').click(function(){
            $('#form-add').slideToggle();
        });

        $('#addimg').click(function(){
            var selText = $('#body').selection();
            $('#body')
                .selection('insert', {text: '<img src="', mode: 'before'})
                .selection('insert', {text: '"/>', mode: 'after'});
        });

        $('.deletePost').click(function(e){
            var post_id = $(e.target).attr('postid');
            apiQuery({
                action: 'delpost',
                id: post_id
            }, function() {
                $(e.target).parent().parent().remove();
            })
        })
    } else {
        if (checkUrl()) {
            window.location.replace("http://room733.ru");
        } else {
            getToken();
        }
    }
});