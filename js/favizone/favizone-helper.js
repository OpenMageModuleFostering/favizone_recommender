/**
 * 2016 Favizone Solutions Ltd
 *
 *  * Favizone Events sender
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
function FavizoneHelper(){

    this.getCookie = function(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
        }
        return "";
    }

    this.setCookie = function(cname, cvalue, expiry_time) {

        var expires;
        var path = "/";
        var domain= window.location.hostname.split('.').reverse()[1] + "." +  window.location.hostname.split('.').reverse()[0] ;
       
        cookieStr = cname + "=" + escape(cvalue) + "; ";  
        if(expiry_time){
            expires = new Date();
            expires = new Date(expires.getTime() + expiry_time * 1000);
        }  else {
            var today = new Date();
            expires = new Date(today.getTime() + 15 * 24 * 60 * 60 * 1000);
        }
      
        expires = expires.toGMTString();
        document.cookie = this.setCookieParams(cname, cvalue, expires, path, window.location.hostname);
        document.cookie = this.setCookieParams(cname, cvalue, expires, path, "."+window.location.hostname);
    }

    this.insertParam = function (paramName, paramValue)
    {
        var url = window.location.href;
        var hash = location.hash;
        url = url.replace(hash, '');
        if (url.indexOf(paramName + "=") >= 0)
        {
            var prefix = url.substring(0, url.indexOf(paramName));
            var suffix = url.substring(url.indexOf(paramName));
            suffix = suffix.substring(suffix.indexOf("=") + 1);
            suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
            url = prefix + paramName + "=" + paramValue + suffix;
        }
        else
        {
            if (url.indexOf("?") < 0)
                url += "?" + paramName + "=" + paramValue;
            else
                url += "&" + paramName + "=" + paramValue;
        }

        return url;
    }

    this.setCookieParams = function(cname, cvalue, expires, path, domain){

        var cookieStr = cname + "=" + escape(cvalue) + "; ";
        if(expires){
            cookieStr += "expires=" + expires + "; ";
        }
        if(path){
            cookieStr += "path=" + path + "; ";
        }
        if(domain){
            cookieStr += "domain=" + domain + "; ";
        }

        return cookieStr;
    }
}
