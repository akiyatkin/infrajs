1# Inline Parameters
#### Inline Parameters
#### Заголовок
hello
- Создаётся репозитарий на bitbucket для разработки нужного сайта.
- клонируется с bitbucket SSH с поролем в адресе для пользователя
- добавляется сабмодуль в папку infra/plugins с github infrajs
- на разработческом сервере с гитом в подпапку корня вебсервера делается git clone, git submodule init, git submodule update
- на gitbucket добавляется HOOK POST. указывается путь до разработческого сервера до папки где развёрнут git /infra/plugins/gitpull.php для автоматического обновления разработческого сервера
