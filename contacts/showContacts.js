contacts={
	extlayer:{
		divs:{},
		external:'*contacts/contacts.layer.js',
		config:{}
	},
	show:function(){
		infra.require('*popup/popup.js');
		popup.open(this.popup);
	},
	popup:{
		config:{
			title:'Форма контактов'
		}
	},
	layer:{autofocus:false,div:'showContacts',reparse:true}
}
contacts.popup.external=contacts.extlayer;
contacts.layer.external=contacts.extlayer;

/*infrajs.listen(infrajs,'oninitone',function(){//depricated
	//infrajs(contacts.layer);//должна добавиться после того как основные слои добавятся, и при этом участвовать в первой пробежке
});*/
infra.require('*infrajs/infrajs.js');
infra.listen(infrajs,'onshow',function(){
	$('.showContacts[showContacts!=true]').attr('nohref','1').attr('showContacts','true').click(function(){
		contacts.show();
		return false;
	});
});
