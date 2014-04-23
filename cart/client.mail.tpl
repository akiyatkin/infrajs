Заявка с сайта {host} от {~date(:j F Y H-i,time)}

Контактное лицо {user.name}
Организация {user.org}
Email {user.email}
Телефон {user.phone}
Сообщение 
{user.text}

Заявка
========
{list::pos}

{allcount} {~words(allcount,:наименование,:наименования,:наименований)}
Итого {allsum:cost}
========

IP: {ip}

{cost:}{~cost(.,~true)} руб.
{pos:}
{Производитель} {Артикул} {Цена?Цена:cost}
http://kvant63.ru/?Каталог/{Производитель}/{Артикул}
Количество {count}
{Цена?:summary}
 

{summary:}{sum:cost}