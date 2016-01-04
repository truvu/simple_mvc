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
			if(this.text.value.length>5){
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