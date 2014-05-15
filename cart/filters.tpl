{root:}
	<link href="infra/lib/jquery-ui/css/smoothness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
	<script>
		infra.require('infra/lib/jquery-ui/js/jquery-ui-1.10.4.custom.js');
	</script>
	
	
	<style>
		.filters {
			font-family:Tahoma;
			font-size:12px;
		}
		.filters .count {
			font-size:10px;
			
			color:gray;
		}
		.filters input {
			margin:0;
		}
		.filters .opttitle {
			font-weight:bold;
			font-size:12px;
			margin-top:5px;
			margin-bottom:3px;
		}
		.filters .check {
			cursor:pointer;
		}
		.filters .cancel {
			font-size:12px;
		}
		.filters .cancel,
		.filters .admitmove,
		.filters .admit {
			display: none;
		}
		.filters .btn {
			margin: 0;
			padding: 3px 5px;
			background-color: white;
			border: solid 1px #ddd;
			cursor: pointer;
			color: #00809e;
		}

	</style>
	<div class="filters">
		<h1>Фильтры {:cancelbtn}</h1>
		{:admit}
		{data.params::option}
		<table style="width:100%; height:32px;">
			<tr><td style="vertical-align:middle">
				{:cancelbtn}
			</td><td style="text-align:right">
				{:admitbtn}
		</td></tr>
		</table>
	</div>
	<div style="text-align:right; margin-bottom:6px;">
		
	</div>
	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer('{unick}');
			var div=$('#'+layer.div);
			var counter={counter};
			var filters=infrajs.autosave.get(layer);
			if(filters&&filters.checks){
				div.find('.cancel').fadeIn('slow');
			}
			
			div.find('input').change(function(){
				if(!layer.showed||counter!=layer.counter)return;
				div.find('.cancel').fadeIn('slow');
			});
			div.find('.cancel').click(function(){
				if(!layer.showed||counter!=layer.counter)return;
				infrajs.autosave.clear(layer);
				infrajs.autosave.set(layer,'admit',true);
				infrajs.global.set('cat_filters');
				infrajs.check(layer);
				div.find('.admit:first').click();
			});
		});
	</script>
	

{comma:}, 
{option:}
<div id="option{name}">
	<div class="opttitle">{name}{:optunit?:comma}{:optunit}</div>
	{type=:string?:OPTSTR?:OPTSLIDE}
</div>
{optlabel:}{~cost(min,~true)} — {~cost(max,~true)} {:optunit}
{optunit:}{infra.conf.catalog[:optunitname]}
{optunitname:}unit{name}
{OPTSLIDE:}
	{:yescheck}
	{:no!:0?:nocheck}<br>

	
	<input type="text" name="checks.{name}" id="amount{~key}" value="{:optlabel}" style="width:150px; margin:5px 0; border:0; font-weight:bold;">
	
	<table style="width:100%" cellpadding="0" cellspacing="0">
		<tr>
		<td style="width:100%"><div id="slider{~key}"></div></td>
		<td style="font-size:10px; padding-left:10px;">&nbsp;{~cost(max)}</td>
		</tr>
	</table>

	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer('{unick}');
			var counter={counter};
			var div=$(document.getElementById('option{name}'));
			var sl=$(document.getElementById('slider{~key}'));
			var inp=$(document.getElementById('amount{~key}'));
			var yes=div.find('.yes').find('input');
			//yes.prop('checked',true);
			var no=div.find('.no').find('input');
			//no.prop('checked',true);
			
			var s=infrajs.autosave.get(layer,"checks.{name}",'');

			var parse=function(s){
				var min={min};
				var max={max};
				var r=s.split('—');
				var rmin=r[0]?r[0]:'';
				var rmax=r[1]?r[1]:'';
				rmax=Number(rmax.replace('{:optunit}','').replace(/\s/g,''));
				rmin=Number(rmin.replace('{:optunit}','').replace(/\s/g,''));
				if(!rmax)rmax=max;

				var smin=rmin;//Для слайдера
				var smax=rmax;
				if(rmin<min)smin=min;
				if(rmax>max)smax=max;

				if(!rmin)rmin=0;
				if(rmin<0)rmin=0;
				//if(rmax>max)rmax=max;
				if(rmin>rmax)rmin=min;
				if(rmin>rmax)rmax=max;
				if(!yes.prop('checked')){
					return {
						name:'{~key}',
						max:rmax,
						min:rmin,
						smin:smin,
						smax:smax
					}
				}else{
					return {
						name:'{~key}',
						max:max,
						min:min,
						smin:smin,
						smax:smax
					}
				}
			}
			var obj=parse(s);
			var val=infra.template.parse('*cart/filters.tpl',obj,'optlabel');
			inp.val(val);
			var work=false;

			sl.slider({
				range: true,
				min:{min},
				max:{max},
				values:[obj.min,obj.max],
				slide:function(event,ui) {
					if(!layer.showed||counter!=layer.counter)return;
					if(work)return;work=true;
					var obj={ min:ui.values[0],max:ui.values[1], name:'{~key}' };
					if(obj.min=={min}&&obj.max=={max}){
						if(!yes.prop('checked')){
							if(!no.prop('checked'))no.prop('checked',true).change();
							yes.prop('checked',true).change();
							
						}
						
					}else{
						if(yes.prop('checked')){
							if(no.prop('checked'))no.prop('checked',false).change();
							yes.prop('checked',false).change();
						}
					}
					var val=infra.template.parse('*cart/filters.tpl',obj,'optlabel');
					inp.val(val).change();
					work=false;
				}
			});

			
			yes.change(function(){
				if(!layer.showed||counter!=layer.counter)return;
				if(work)return;work=true;
				
				
				if(this.checked){
					sl.slider( "option","values",[{min},{max}]);
					var obj=parse('');
					var val=infra.template.parse('*cart/filters.tpl',obj,'optlabel');
					inp.val(val).change();
				}
				work=false;
			});
			
			inp.change(function(){
				if(!layer.showed||counter!=layer.counter)return;
				if(work)return;work=true;
				
				
				var nowyes=yes.prop('checked');
				yes.prop('checked',false).change();
				var val=this.value;
				var obj=parse(val);
				if(obj.min<={min}&&obj.max>={max}){
					yes.prop('checked',true).change();
					if(nowyes){
						no.prop('checked',true).change();
					}
				}
				var val=infra.template.parse('*cart/filters.tpl',obj,'optlabel');
				inp.val(val).change();

				sl.slider( "option","values",[obj.smin,obj.smax]);


				work=false;
			});
			
		});
	</script>

{admit:}
	<button class="btn admitmove admit" style="position:absolute; margin-left:-96px;">Применить</button>
	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer('{unick}');
			var counter={counter};
			var div=$('#'+layer.div);
			var btn=div.find('.admitmove');
			var over=false;
			div.hover(function(){
				over=true;
			},function(){
				over=false;
			});
			$(document).bind('mousemove',function(e){
				if(layer.counter!=counter||!layer.showed)return;
				if(!over)return;
				if(!window.showadmit)return;
				window.showadmit=false;
				btn.css('top',(e.pageY)+'px');
			});
		});
	</script>
{cancelbtn:}
	<span class="a cancel" style="display:none">Сбросить</span>
	
{admitbtn:}
	<button class="btn admit">Применить</button>
	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer('{unick}');
			var div=$('#'+layer.div);
			var counter={counter};
			if(infra.session.get(['filters','admit'])){
				div.find('.admit').fadeIn('slow');
			}
			var process=false;
			div.find('input').change(function(){
				if(!layer.showed||counter!=layer.counter)return;
				if(process)return;
				process=true;
				window.showadmit=true;
				setTimeout(function(){
					process=false;
					infrajs.autosave.set(layer,'admit',true);
					div.find('.admit').fadeIn('slow');
				},1);
			});
			div.find('.admit').click(function(){
				if(!layer.showed||counter!=layer.counter)return;
				
				infrajs.autosave.set(layer,'admit',false);
				div.find('.admit').fadeOut('slow');
				var save=infrajs.autosave.get(layer,'checks');
				
				infra.session.set('filtersadmit',save,true);
				infrajs.global.set('cat_search');
				infrajs.check();
				//infra.unload('*cart/test.php');
				//popup.open({
				//	tpl:'*cart/test.php'
				//});
				
			});
		});
	</script>
{optbtnmore:}<span class="a" onclick="$(this).next().toggle()">Ещё</span>
{br:}<br>
{OPTSTR:}
	{:no!:0?:yescheck}
	{:no!:0?:nocheck}
	{:no!:0?:br}
	{values::val}
	{~length(values_more)?:optbtnmore}
	<div style="display:none">
	{values_more::val}
	</div>
	
	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer('{unick}');
			var div=$(document.getElementById('option{name}'));
			
			
			var yes=div.find('.yes').find('input');
			var no=div.find('.no').find('input');
			var inps=div.find('.val input');

			var ignore=false;
			//yes.prop('checked',true).change();
			//no.prop('checked',true);
			

			yes.change(function(){
				if($(this).prop('checked')){
					ignore=true;
					inps.filter(':checked').prop('checked',false).change();
					ignore=false;
				}
			});

			div.find('.val input').change(function(){
				if(ignore)return;
				if(inps.filter(':checked').length){
					if(yes.prop('checked')){
						yes.prop('checked',false).change();
						//no.prop('checked',false).change();
					}
				}else{
					if(!yes.prop('checked')){
						//yes.prop('checked',true).change();
						//no.prop('checked',true).change();
					}
				}
			});
			
		});
	</script>
{val:}<label class="val"><input name="checks.{...name}.{infra.seq.short(~array(~key))}" type="checkbox"> {~key}&nbsp;<span class="count">{.}</span></label><br>
{yescheck:}
	<label class="yes">
		<nobr>
			<input name="checks.yes.{infra.seq.short(~array(name))}" type="checkbox">
			Указано
			<span class="count">{group?data.count?yes}</span>
		</nobr>
	</label>
{nocheck:}
	<label class="no">
		<nobr>
			<input name="checks.no.{infra.seq.short(~array(name))}" type="checkbox">
			Не указано
			<span class="count">{:no}</span>
		</nobr>
	</label>
{no:}{group?:0?~sum(data.count,:minusyes)}
{minusyes:}-{yes}