var XMLObject = {
    createXHR:function() {
        var xmlhttp = null;

        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }

        return xmlhttp;
    },

    ajax: function (url, method, params, async, handler) {
        xmlhttp = this.createXHR();
    
        xmlhttp.open(method, url, async);
        if("POST" == method)
        {
            xmlhttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
            xmlhttp.send(params);
        } else {
            xmlhttp.send();
        }

        xmlhttp.onreadystatechange = function ()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
            {
                handler.call(self, xmlhttp.responseText);
            }
        }
    }
}

var Ajax = XMLObject;
Ajax.call = XMLObject.ajax;

