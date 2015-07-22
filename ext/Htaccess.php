<?php
namespace itlife\infrajs\ext;

/*
 *
Проверяет наличия файла .httaccess в корне сайта
и если его нет создаёт согласно конфигу .config.json

	"htaccess":{
		"www":false,
	}


 */
class Htaccess
{
	public static function init()
	{
		$conf = infra_config();
		if (empty($conf['infrajs']['htaccess'])) {
			return;
		}
		if (infra_theme('.htaccess')) {
			return;
		}
		$ht = $conf['htaccess'];
		$text = '#Сгенерировано infra/ext/htaccess.php'."\n";
		$text .= "\n";

		$text .= '#hide'."\n";
		$text .= '<FilesMatch "^\.">'."\n";
		$text .= "\t".'Order Deny,Allow'."\n";
		$text .= "\t".'Deny from all'."\n";
		$text .= '</FilesMatch>'."\n";
		$text .= "\n";

		$text .= '#charset'."\n";
		$text .= 'AddDefaultCharset utf-8'."\n";
		$text .= "\n";

		$text .= '# Заголовок Cache-Control'."\n";
		$text .= '<IfModule mod_headers.c>'."\n";
		$text .= ' Header append Cache-Control "no-cache"'."\n";
		$text .= '</IfModule>'."\n";
		$text .= "\n";

		$text .= '<ifModule mod_php.c>'."\n";
		$text .= "\n";

		$text .= '#register_globals'."\n";
		$text .= 'php_flag register_globals 0'."\n";
		$text .= "\n";

		$text .= '#max_execution_time in seconds 5 min'."\n";
		$text .= 'php_flag max_execution_time 300'."\n";
		$text .= "\n";

		$text .= '#post_max_size'."\n";//При отправке админом файлов в каталоге требуется большой размер для отправки...
		$text .= 'php_flag post_max_size 200M'."\n";
		$text .= 'php_flag upload_max_filesize 200M'."\n";
		$text .= "\n";

		//$text.='#max_input_nesting_level'."\n";
		//$text.='php_flag max_input_nesting_level 256'."\n";
		//$text.="\n";


		$text .= '#magic_quotes_gpc'."\n";
		$text .= 'php_flag magic_quotes_gpc 0'."\n";
		$text .= "\n";
		$text .= '</ifModule>'."\n";
		$text .= "\n";

		$text .= '#list'."\n";
		$text .= 'Options -Indexes'."\n";
		$text .= "\n";

		$text .= '#www'."\n";
		$text .= '<IfModule mod_rewrite.c>'."\n";
		$text .= "\t".'Options +FollowSymLinks'."\n";
		$text .= "\t".'RewriteEngine on'."\n";
		if (!empty($ht['www'])) {
			$text .= "\t".'RewriteCond %{HTTP_HOST} !^www\.(.*) [NC]'."\n";
			$text .= "\t".'RewriteRule ^(.*)$ http://www.%1/$1 [R=301,L]'."\n";
		} else {
			$text .= "\t".'RewriteCond %{HTTP_HOST} ^www\.(.*) [NC]'."\n";
			$text .= "\t".'RewriteRule ^(.*)$ http://%1/$1 [R=301,L]'."\n";
		}
		$text .= '</IfModule>'."\n";
		$text .= "\n";

		$dirs = infra_dirs();
		file_put_contents('.htaccess', $text);
	}
}
