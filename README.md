1# Inline Parameters
#### Inline Parameters
#### Заголовок
hello
- Создаётся репозитарий на bitbucket для разработки нужного сайта.
- клонируется с bitbucket SSH с поролем в адресе для пользователя
- добавляется сабмодуль в папку infra/plugins с github infrajs. 
- git submodule add https://github.com/akiyatkin/infrajs.git infra/plugins
- на разработческом сервере с гитом в подпапку корня вебсервера делается git clone, git submodule init, git submodule update
- На дев сервере также создаётся git проекта
- на gitbucket добавляется HOOK POST. указывается путь до разработческого сервера, до папки где развёрнут git /infra/plugins/infra/gitpull.php для автоматического обновления разработческого сервера

.gitignore
  infra/backup
  infra/cache
  infra/data
  infra/lib
  *~
  *.swp
  *.swo
