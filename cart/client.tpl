<div class="client">
	<style>
		.client {
			font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif;
			text-align:left;
		}
		.client label {
			margin-top:5px;
			text-align: left;
			font-size: 14px;
			padding-top: 5px;
		}
		.client label span {
			color:red;
		}
		.client form {
			padding-bottom: 5px;
		}
		.client .answer {
			width: 290px;
		}
		.cartcontacts input {
			width: 290px;
			height: 18px;
			padding-top: 2px;
			border: 1px solid #7f9db9;
			margin: 0 auto;
			margin-bottom:10px;
			margin-top:2px;
		}
		.cartcontacts textarea {
			width: 290px;
			height:102px;
			border: 1px solid #7f9db9;
		}
		.client .submit {
			margin-top:20px;			
			font-size:14px;
			padding: 5px 10px;			
		}
	</style>
	<div class="answer"><b class="alert">{config.ans.msg}</b></div>
	<div>{config.ans.msg?config.ans:cartanswer}</div>
	<form action="infra/layers/client.php?submit=1" method="post">
		<table>
			<tr>
				<td>
					<div class="cartcontacts">
						<h1>Заявка</h1>
						{data.carttime?:carttime}
						<label>Контактное лицо<span>*&nbsp;</span></label><br> 
						<input value="{name}" name="name" type="text"><br />
						<label>Организация</label><br> 
						<input value="{org}" name="org" type="text"><br />
						<label>Email<span>*</span></label><br> 
						<input value="{email}" name="email" type="email"><br />
						<label>Телефон<span>*</span></label><br> 
						<input value="{phone}" name="phone" type="text"><br />
						<label>Сообщение</label><br> 
						<textarea name="text" cols=35 rows=5></textarea>
						<br>
						<button class="submit" type="submit">Отправить заявку</button>
					</div>
					<div class="answer"><b class="alert">{config.ans.msg}</b></div>
				</td>
				<td>
					<div id="cart">
					</div>
				</td>
			</tr>
		</table>
		
		
	</form>
	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer('{unick}');
			var div=$('#'+layer.div);
			var counter={counter};
			infra.listen(layer,'onsubmit',function(layer){
				if(!layer.showed||counter!=layer.counter)return;
				var ans=layer.config.ans;
				infrajs.global.set('cat_basket');
				roller.goTop();
			});
		});
	</script>
</div>
{carttime:}
<div style="margin-bottom:5px">
Последний раз заявка отправлялась<br>{~date(:j F Y,data.carttime)} в {~date(:H:i,data.carttime)}<br>
</div>
{cartanswer:}
	<pre>{mail}</pre>
{cart:}
	<style>
		.usercart input {
			width:30px;
			padding:1px 5px;
		}
		.usercart .img {
			text-align:center;
			vertical-align:top;
			padding:5px 2px;
		}
		
		.usercart .cartparam {
			margin-bottom:20px;
		}
		.usercart .cartparam td {
			vertical-align: middle;
		}


		.usercart .title a {
			text-decoration: none;
			display: block;
			background-color: #efefef;
			color: black;
			text-align: left;
			font: 14px "Open sans", sans-serif;
			line-height: 22px;
			padding:4px;
			font-weight:bold;
		}
		.usercart .cartsum {
			font-size:24px;
			color:#333333;
			margin-top:10px;
		}
		/*.usercart .title a:hover {
			background-color: #009ec3;
			color: white;
		}*/
	</style>
	
	<div class="usercart" style="margin-left:30px; margin-top:70px;">		
		{data.allcount?:cartlist?:cartmsg}
	</div>
	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer('{unick}');
			var div=$('#'+layer.div);
			catalog.initPrice(div);
			var calc=function(inp,sum,cost){
				var count=Number(inp.val());
				if(isNaN(count))count=1;
				var s=count*cost;
				sum.data('sum',s);
				s=infra.template.scope['~cost'](s);
				sum.html(s+' руб.');
			}
			var summ=function(div){
				var sum=0;
				div.find('.cat_item .sum').each(function(){
					var s=$(this).data('sum');
					if(!s)return;
					sum+=s;
				});
				if(!sum){
					div.find('.cartsum').parent().hide();
				}else{
					div.find('.cartsum').parent().show();
				}
				var s=infra.template.scope['~cost'](sum);
				div.find('.cartsum').html(s+' руб.');
			}
			div.find('.cat_item').each(function(){
				var cost=$(this).data('cost');
				if(!cost)return;
				var sum=$(this).find('.sum');
				var inp=$(this).find('input');
				inp.change(function(){
					calc(inp,sum,cost);
					summ(div);
				});
				calc(inp,sum,cost);
			});
			summ(div);
		});
	</script>
	{cartlist:}
		<table cellpading="0" cellspacing="0">
		{data.list::cartpos}
		</table>
		<div>Итого: <span class="cartsum"></span></div>
		<div style="margin-top:10px"><button class="submit" type="submit">Отправить заявку</button>
	{cartmsg:}<p>Заявка пуста.</p>
	<p>Добавьте в заявку интересующие позиции из <a href="?Каталог/Каталог">каталога</a> или опишите ваши потребности в поле для сообщения.</p>
	<p>Чтобы добавить позицию в заявку нужно кликнуть по иконке корзины рядом с ценой.</p>
	<p>После отправления заявки с вами свяжется менеджер и уточнит способ доставки, стоимость и другие вопросы.</p>
	{cartpos:}
	<tr><td colspan="2">
		<a style="font-weight:normal; font-size:12px" href="?Каталог/{group_title}">{group_title}</a>
		<div class="title">
			<a href="?Каталог/{Производитель}/{article}">{Производитель} {Артикул}</a>
		</div>
	</td></tr>
	<tr><td class="img">
		<a href="?Каталог/{Производитель}/{article}">
			<img src="infra/plugins/imager/imager.php?w=100&h=80&src={infra.conf.catalog.dir}{Производитель}/{article}/&or=*imager/empty">
		</a>
	</td>
	<td class="cat_item" data-cost="{Цена}">
		
		<table style="width:100%" cellpadding="0" cellspacing="1">
			<tr>
				<td style="text-align:left; padding-left:10px; float:none;" class="price">
					{Цена?:itemcost?:itemnocost}
				</td>
				<td style="width:30px">
					<div data-article="{article}" data-producer="{Производитель}" class="basket_img"></div>
				</td>
			</tr>
		</table>
		<table class="cartparam common" style="margin-top:0" cellpading="0" cellspacing="0">
		<tr><td>Количество:&nbsp;</td><td><input type="text" name="{Производитель} {article}.count"></td></tr>
		{Цена?:summary}
		</table>

	</td>
{summary:}<tr><td>Итого:&nbsp;</td><td><span class="sum">{:itemcost}</span></td></tr>
{itemcost:}{~cost(Цена)} <small>руб.</small>
{itemnocost:}<a style="color:white" href="?Контакты менеджеров">Уточнить</a>
{basket:}
	<div id="basket_text">
		В <a href="?Каталог/Корзина">корзине</a>
		<span class="bold_basket">{data.allcount}</span> {~words(data.allcount,:позиция,:позиции,:позиций)}<br> Сумма <span class="bold_basket">{~cost(data.allsum)} руб.</span>
	</div>