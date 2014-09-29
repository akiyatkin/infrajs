window.catalog={
	initPrice:function(div){
		div.find('.cat_item').each(function(){
			var cart=$(this).find('.basket_img');

			var id=cart.data('producer')+' '+cart.data('article');
			if(infra.session.get('order.basket.'+id)){
				$(this).find('.posbasket').show();
				cart.addClass('basket_img_sel');
				cart.attr('title','Удалить из корзины');
			}else{
				cart.attr('title','Добавить в корзину');
			}
		});
		var callback=function(){
			infrajs.global.set('cat_basket');
			infrajs.check();
		}
		div.find('.cat_item .basket_img').click(function(){
			var cart=$(this);
			var id=cart.data('producer')+' '+cart.data('article');
			var name='order.basket.'+id;
			var r=infra.session.get(name);
			infra.loader.show();
			if(r){
				$(this).removeClass('basket_img_over');
				$(this).removeClass('basket_img_sel');
				$(this).parents('.cat_item').find('.posbasket').hide();
				infra.session.set(name,null,callback);
				cart.attr('title','Добавить в корзину');
			}else{
				$(this).addClass('basket_img_sel');
				$(this).parents('.cat_item').find('.posbasket').show();
				infra.session.set(name,{ count:1 },callback);
				cart.attr('title','Удалить из корзины');

			}
		}).hover(function(){
			$(this).addClass('basket_img_over');
		},function(){
			$(this).removeClass('basket_img_over');
		});
	}
}