window.confirm = function (o, cb) {
    var modal = Dom.createClass({
        render: function () {
            return Dom.node(
                '<div id="modal"><div id="modal_first"/></div>',
                null,
                Dom.node(
                    '<div id="modal_last"/>',
                    null,
                    Dom.node(
                        '<form action="javascript:void(0);"/>',
                        null,
                        function (form) {
                            if(o.header) Dom.render(form, Dom.node('<header>'+o.header+'</header>'));
                            if(o.content) Dom.render(form, Dom.node('<section/>', null, o.content));
                            if(o.footer) Dom.render(form, Dom.node('<footer class="clearfix"><span/></footer>', null, o.footer));
                            return form;
                        }
                    )    
                )
            )
        }
    });
    Dom.render(o.body||document.body, modal);
    var last = document.getElementById("modal_last");
    last.style.left = (window.innerWidth-last.clientWidth-20)/2 + "px";
    if(o.style){for(var i in o.style) last.style[i]=o.style[i]};
    return modal;
};