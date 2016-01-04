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
				/* create child node */
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
				
				/* get ref */
				if(dom&&(k=dom.getAttribute("ref"))){
					this.ref[k] = dom;
					dom.removeAttribute("ref");
				};
				/* get state */
				if(txt=dom.innerHTML){
					if(k=this.exec.exec(txt)){
						var a = k[1], b = a.replace('this.state.', '');
						this.state_dom[b] = dom;
						this.state_text[b] = txt;
						dom.innerHTML = txt.replace(k[0], eval(a));
					}
				};
				/* set windown event */
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