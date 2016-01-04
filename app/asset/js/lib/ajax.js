function MyAjax(){
    this.headers = {};
    this.requestHttp = "GET";
    this.dataType = "text";
    this.async = false;
    this.x = window.XMLHttpRequest?new XMLHttpRequest():new ActiveXObject("Microsoft.XMLHTTP");
    this.setRequestHttp = function(n) {
        this.requestHttp = n;
        return this;
    };
    this.getRequestHttp = function() {
        return this.requestHttp;
    };
    this.setUrl = function(url) {
        if(!this.a) this.a = document.createElement("a");
        this.a.href = url;
        return this;
    };
    this.getUrl = function() {
        return this.a.href;
    };
    this.setDataType = function(t) {
        this.dataType = t;
        return this;
    };
    this.getDataType = function() {
        return this.dataType;
    };
    this.setData = function(data) {
        if(data){
            switch(typeof data){
                case "object":
                    if(!this.data) this.data = "";
                    for(var i in data) this.data += i+"="+data[i]+"&"; 
                        this.data = this.data.replace(/\&$/, "");
                    break;
                case "string": this.data = data; break;
            };
            if("GET"==this.getRequestHttp()) {
                if(this.a.search) this.a.search+="&"+this.data;
                else this.a.search = this.data;
                this.setUrl(this.a);
                this.data = null;
            };
        };
        return this;
    };
    this.getData = function() {
        return this.data?this.data:null;
    };
    this.setAsync = function(a) {
        this.async = a;
        return this;
    };
    this.getAsync = function() {
        return this.async;
    };
    this.setHeader = function(a, b) {
        if(b) this.headers[a]=b;
        else for(var i in a) this.headers[i] = a[i];
        return this;
    };
    this.request = function(cb) {
        var dataType = this.getDataType(), x=this.x;
        x.open(this.getRequestHttp(), this.getUrl(), this.getAsync());
        if(cb){
            x.onreadystatechange = function() {
                if(x.readyState==4&&x.status==200) {
                    switch(dataType){
                        case "xml": return cb(this.responseXML);
                        case "upload": return cb(this.upload);
                        case "script":
                            var script = document.createElement("script");
                            script.type = "text/javascript";
                            script.innerHTML = this.responseText;
                            document.getElementsByTagName("head")[0].appendChild(script);
                            return cb(script);
                        case "json": return cb(JSON.parse(this.responseText));
                        default: return cb(this.responseText);
                    }
                }
            }
        };
        x.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        for(var i in this.headers){ 
            x.setRequestHeader(i, this.headers[i]);
        };
        x.send(this.getData());
        return x;
    };
    return this;
};
function __chooseAction (m,a,b,c) {
    var Ajax = new MyAjax();
    Ajax.setRequestHttp(m);
    if(m=="POST") Ajax.setHeader("Content-Type", "application/x-www-form-urlencoded");
    if("string"==typeof a){
        Ajax.setUrl(a).setAsync(true);
        if(c) return Ajax.setData(b).request(c);
        else return b?Ajax.request(b):Ajax.request();
    }else{
        Ajax.setUrl(a.url);
        if(a.data) Ajax.setData(a.data);
        if(a.headers) Ajax.setHeader(a.headers);
        if(a.async) Ajax.setAsync(a.async);
        if(a.dataType) Ajax.setDataType(a.dataType);
        if(a.success) Ajax.request(a.success); else Ajax.request();
    }
};
function $(selector) {
    this.parser = new DOMParser();
    this.selector = (function(s, p){
        switch(typeof s){
            case "object": return [s];
            case "string":
                if(/[a-z]+/.test(s)) return document.querySelectorAll(s);
                else return p.parserFromString("text/html").body.childNodes[0];
            default: return null;
        }
    })(selector, this.parser);
    this.each = function (action) {
        return Array.prototype.forEach.call(this.selector, action);
    };
    this.html = function (a) {
        if("string"==typeof a) this.each(function(node){node.innerHTML=a});
        else return this.selector[0].innerHTML;
        return this;
    };
    this.val = function (a) {
        if("string"==typeof a) this.each(function(node){node.value=a});
        else return this.selector[0].value;
        return this;
    };
    this.text = function (a) {
        if("string"==typeof a) this.each(function(node){node.textContent=a});
        else return this.selector[0].textContent;
        return this;
    };
    this.css = function (a, b) {
        if("string"==typeof b) this.each(function(node){node.style[a]=b});
        else{
            if("string"==typeof a) return this.selector[0].style[a];
            else for(var i in a) this.each(function(node){node.style[i]=a[i]});
        };
        return this;
    };
    this.attr = function (a, b) {
        if("string"==typeof b) this.each(function(node){node.setAttribute(a,b)});
        else{
            if("string"==typeof a) return this.selector[0].getAttribute(a);
            else for(var i in a) this.each(function(node){node.setAttribute(i, a[i])});
        };
        return this;
    };
    this.removeAttr = function (a) {
        this.each(function(node){node.removeAttribute(a)});
        return this;
    };
    this.show = function () {
        this.css("display",  "block");
        return this;
    };
    this.hide = function () {
        this.css("display",  "none");
        return this;
    };
    this.toggle = function () {
        this.css("display", (this.css("display")=="none")?"none":"block");
        return this;
    };
    this.hasClass = function (a) {
        return this.selector[0].classList.constant(a);
    };
    this.addClass = function (a) {
        this.each(function(node){node.classList.add(a)});
        return this;
    };
    this.removeClass = function (a) {
        this.each(function(node){node.classList.remove(a)});
        return this;
    };
    this.children = function (a) {
        var node = this.selector[0];
        switch(typeof a){
            case "number": return node.childNodes[a];
            case "string": return node.querySelector(a);
        }
    };
    return this;
};
$.get = function (a,b,c) {
    return __chooseAction("GET",a,b,c);
};
$.post = function (a,b,c) {
    return __chooseAction("POST",a,b,c);
};
$.load = function(url, data, cb) {
    var u = url.split(" ");
    if(!cb){ cb = data; data = null; };
    if(u[1]) {
        $.get(u[0], data, function (a) {
            if(a){
                url = url.replace(u[0]+" ", "");
                var parser = new DOMParser();
                var dom = parser.parseFromString(s, "text/html");
                return cb(dom.querySelector(url));
            }else return cb();
        });
    }else return $.get(u[0], data, cb);
};
$.user = "";
$.pass = "";
$.api = function (a) {
    return "http://localhost:3000/"+a;
};