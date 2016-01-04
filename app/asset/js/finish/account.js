window.Socket = {
	connect: function(wsUri){
		var uri = wsUri.replace(/^(http)/, 'ws');
		this.io = new WebSocket(uri);
		return this;
	},
	emit: function (name, data) {
		var o = {}; o[name]=data;
		this.io.send(JSON.stringify(o));
	},
	listen: function (cb) {
		var o = null;
		this.io.onmessage = function (e) {
			Socket.data = JSON.parse(e.data);
			return cb();
		};
	},
	on: function (name, cb) {
		switch(name){
			case 'connect': return this.io.onopen=cb;
			case 'disconnect': return this.io.onclose=cb;
			case 'error': return this.io.onerror=cb;
			default:
				var data = this.data[name];
				if(data){
					delete this.data[name];
					return cb(data);
				}else return false;
		}
	}
};
window.Dom = (function(){
	if('undefined'==typeof DomNode){
		function DomNode(object){
			this.DOMParser =  new DOMParser();
			this.exec = /\{(.+)\}+/;
			this.constructor = function(object){
				this.ref = {};
				this.props = object.props||{};
				this.state = object.state||{};
				this.state_text = {};
				this.state_dom = {};
				return this;
			};
			this.node = function(array){
				var i, m, k, dom, txt;
				dom = this.DOMParser.parseFromString(array[0], "text/html").body.childNodes[0];
				
				if(array[2]){
					i=2; m=array.length;
					for(; i<m; i++){
						switch(typeof array[i]){
							case "function": array[i](dom); break;
							case "object": dom.appendChild(array[i]); break;
							case "string": dom.innerHTML = array[i]; break;
							default: continue;
						}
					}
				};
				
				
				if(dom&&(k=dom.getAttribute("ref"))){
					this.ref[k] = dom;
					dom.removeAttribute("ref");
				};
				
				if(txt=dom.innerHTML){
					if(k=this.exec.exec(txt)){
						var a = k[1], b = a.replace('this.state.', '');
						this.state_dom[b] = dom;
						this.state_text[b] = txt;
						dom.innerHTML = txt.replace(k[0], eval(a));
					}
				};
				
				if(array[1]){
					var i, o = array[1];
					for(i in o) dom.addEventListener(i, o[i]);
				};
				return dom;
			};
			this.setState = function(o){
				var m, a;
				for(var i in o){
					if('function'==typeof o[i]) this.state[i]=o[i]();
					else this.state[i]=o[i];
					if(m=this.exec.exec(this.state_text[i])){
						this.state_dom[i].innerHTML = this.state_text[i].replace(m[0], eval(m[1]));
					}
				};
			};
			return ('object'==typeof object)?this.constructor(object):this;
		}	
	};
	return {
		node: function(){
			return this.dom.node(arguments);
		},
		createClass: function (object) {
			if(object.state) this.state = object.state;
			this.dom = new DomNode(object);
			this.dom.element = object.render();

			for(var i in object){
				if(!(/render/.test(i))) this.dom.element[i]=object[i];
			};

			return this.dom;
		},
		render: function (parent, child) {
			if(child.element) parent.appendChild(child.element);
		}
	};
})();
var socket = Socket.connect('http://localhost:3000/');
var content = document.getElementById('content');

var chat = document.getElementById('chat');
var time = document.getElementById('time');
var show = setInterval(function(){
	socket.emit('time', Date.now());
}, 5000);

socket.listen(function(){
	socket.on('chat', function (object){
		chat.innerHTML = chat.innerHTML+('<b>'+object.name+'</b>: '+object.text+'<br/>');
	});
	socket.on('time', function (number){
		time.innerHTML = 'this time now is '+number;
	});
});

window.FormChat = Dom.createClass({
	state: {
		name: '',
		text: ''
	},
	handleSubmit: function(e){
		e.preventDefault();
		var form = FormChat, name, data;

		if(!form.state.name){
			if(name=this.name.value){
				FormChat.setState({name: name});
				this.name.remove();
			}else return;
		};
		if(form.state.name){
			if(this.text.value>5){
				socket.emit('chat', {name: form.state.name, text: this.text.value});
				this.text.value = '';
				this.text.focus();
			}else return;
		};
	},
	render: function(){
		return Dom.node(
			'<div class="row"/>',
			null,
			Dom.node(
				'<form action="#"/>',
				{submit: this.handleSubmit},
				Dom.node('<div>State text: {this.state.text}</div>'),
				Dom.node('<div class="form-group"><input type="text" class="form-control" name="name" placeholder="press name"/></div>'),
				Dom.node(
					'<div class="form-group"/>',
					null,
					Dom.node(
						'<input type="text" class="form-control" name="text" placeholder="press text"/>',
						{keyup: function(e){
							FormChat.setState({text: this.value});
						}}
					)
				),
				Dom.node('<div class="form-group clearfix"><input type="submit" class="btn btn-primary pull-right" value="Send"/></div>')
			)
		);
	}
});

Dom.render(content, FormChat);
