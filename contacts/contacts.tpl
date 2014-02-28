{root:}
	{infra.session.get(:safe.user,~true):start}
{start:}
		<center>
			<script>
				infra.loadCSS('*contacts/contacts.css');
			</script>
			<h1>Форма контактов</h1>
			<div class="plugin_contacts">

				<form action="{infra.theme(:*contacts/cont.php)}" method="post">
					<div id="contacts_more"></div>
					<label>Контактное лицо<span>*&nbsp;</span></label><br> 
					<input value="{name}" name="name" type="text"><br />
					<label>Организация</label>
					<input value="{org}" name="org" type="text"><br />
					<label>Email<span>*</span></label>
					<input value="{email}" name="email" type="email"><br />
					<label>Телефон<span>*</span></label>
					<input value="{phone}" name="phone" type="text"><br />
					<label>Текст письма<span>*</span></label>
					<textarea name="text" cols=35 rows=5></textarea>
					<div class="answer"><b class="alert">{config.ans.msg}<b></div>
					<center class="sub_center">
						<input class="submit" value="Отправить" type="submit"> <br>
					</center>
				</form>
			</div>
		</center>
		<script>
			var layer=infrajs.getUnickLayer('{unick}');
			infra.when(infrajs,'onshow',function(){
				if(popup.layer!=layer)return;
				infrajs.popup_memorize('contacts.show()');
			});
		</script>
