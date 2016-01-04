window.cookie = {
	data: {},
	start: function () {
		this.hasCheck = true;
		var it = document.cookie, array = [];
		if(!it) return false;
		it = it.split('; ');
		for(var i=0, n=it.length; i<n; i++){
			array = it[i].split('=');
			this.data[decodeURIComponent(array[0])] = decodeURIComponent(array[1]);
		};
		it = null; array = [];
		return this;
	},
	set: function (o) {
		for(var i in o){ document.cookie = encodeURIComponent(i) + '=' + encodeURIComponent(o[i]) + ';' + this.str };
		return this;
	},
	has: function (a) {
		if(!this.hasCheck) this.start();
		return(this.data[decodeURIComponent(a)]);
	},
	get: function (a) {
		return this.data[decodeURIComponent(a)];
	},
	init: function (o) {
		var d = new Date();
		var a = '';
		var time = o.time||1;
		d.setTime(d.getTime() + (time*24*60*60*1000));
		
		a += 'expires='+d.toUTCString();
		a += o.path?('; path='+o.path):'; path=/';
		a += o.domain?('; domain'+o.domain):'';
		a += o.secure?('; secure'+o.secure):'';

		this.str = a;
		return this;
	},
	remove: function (name) {
		if(name){
			if(!this.str) this.init();
			if("string"==typeof name) document.cookie = decodeURIComponent(name)+'=;'+this.str;
			else if("object"==typeof name&&name.length) for(var i=0, n=name.length; i<n; i++) document.cookie = decodeURIComponent(name[i])+"=;"+this.str;
		};
		return true;
	}
};