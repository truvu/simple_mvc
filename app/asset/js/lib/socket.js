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