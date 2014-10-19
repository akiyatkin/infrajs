{priceblock:}
	<table cellpadding="0" cellspacing="0">
		<tr>
			<td class="price" style="padding:0 10px">{Цена оптовая?Цена оптовая:itemcost?:itemnocost}</td>
			<td>
				<div data-article="{article}" data-producer="{Производитель}" class="basket_img"></div>
			</td>
		</tr>
	</table>
	<div class="posbasket" style="margin-bottom:3px; display:none">
		<small>Позиция в <a href="?office/cart">корзине</a></small>
	</div>
	{itemcost:}{~cost(.)} руб.
	{itemnocost:}<a style="color:white" href="?Контакты менеджеров">Уточнить</a>