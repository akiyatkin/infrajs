/*Здесь нет подключения infra так как на клиенте require не сработает а infra.load ещё нет*/
//Подключено в infra fibers view install

if(infra.NODE)infra.load('*infra/install.sjs','r');

infra.load('*infra/ext/seq.js','r');
infra.load('*infra/ext/template.js','r');
infra.load('*infra/ext/state.js','r');
infra.load('*infra/ext/html.js','r');

if(infra.NODE)infra.load('*infra/ext/mail.sjs');
if(infra.NODE)infra.load('*infra/ext/admin.sjs');
if(infra.NODE)infra.load('*infra/ext/system.sjs');
if(infra.NODE)infra.load('*infra/ext/cache.sjs');

