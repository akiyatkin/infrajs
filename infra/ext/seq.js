/*
 * autosave template session state
 * */
infra.seq={
	seldom:'·',
	offen:'.',
	short:function(val,offen,seldom){//Возвращает строку - короткая запись последовательности
		offen=offen||infra.seq.offen;
		seldom=seldom||infra.seq.seldom;
		if(typeof(val)=='string')return val;
		if(!val||typeof(val)!='object'||val.constructor!=Array)val=[];
		var nval=[];
		if(val[0]=='')nval.push('');
		infra.forr(val,function(s){ 
			s=String(s);
			nval.push(s.replace(offen,seldom));
		});

		

		return nval.join(offen);
	},
	contain:function(search,subject){
		return !infra.forr(search,function(name,index){
			if(name!=subject[index])return true;
		});
	},
	right:function(val,offen,seldom){//Возвращает массив - правильную запись последовательности
		offen=offen||infra.seq.offen;
		seldom=seldom||infra.seq.seldom;
		if(!val||typeof(val)!=='object'||val.constructor!==Array){
			if(typeof(val)!='string')val='';
			val=val.split(offen);
			infra.forr(val,function(s,i){
				val[i]=s.replace(seldom,offen);//Знак offen используется часто и должна быть возможность его указать в строке без специального смысла.. вот для этого и используется знак seldom 
			});
			if(val[val.length-1]==='')val.pop();
			if(val[0]==='')val.shift();
		}
		var res=[];
		for(var i=0,l=val.length;i<l;i++){
			var s=val[i];
			if(s===''&&res.length!=0&&res[i-1]!==''){
				//if()break;
				res.pop();
			}else{
				res.push(s);
			}
		}
		/*infra.forr(val,function(s){//удаляются пустые
			//if(s==='')return;
			res.push(s);//Знак offen используется часто и должна быть возможность его указать в строке без специального смысла.. вот для этого и используется знак seldom 
		});*/
		return res;
	},
	set:function(obj,right,val){
		var make=(typeof(val)=='undefined'||val===null?false:true);
		var i=right.length-1;
		if(i==-1)return val;
		if(make&&(!obj||typeof(obj)!=='object')&&typeof(obj)!=='function')obj={};
		var need=infra.seq.get(obj,right,0,i,make);
		if(!make&&(need&&typeof(need)=='object'))delete need[right[i]];
		if(make)need[right[i]]=val;
		return obj;
	},
	get:function(obj,right,start,end,make){//получить из obj значение right до end брать начинаем с start
		if(typeof(start)==='undefined')start=0;
		if(typeof(end)==='undefined')end=right.length;
		if(end===start)return obj;
		if(obj===undefined)return;

		if(make&&((!obj[right[start]]||typeof(obj[right[start]])!=='object')&&typeof(obj[right[start]])!=='function'))obj[right[start]]={};
		if((obj&&typeof(obj)=='object')||typeof(obj)=='function'){
			if(((obj===location||(!obj.hasOwnProperty))&&obj[right[start]])||obj.hasOwnProperty(right[start])){
				//в ie у location есть свойство hasOwnProperty но все свойства не являются собственными у location. в ff у location нет метода hasOwnProperty
				return infra.seq.get(obj[right[start]],right,++start,end,make);
			}
		}
	}
}
