<?php
/*
Copyright 2008-2011 ITLife, Ltd. Togliatti, Samara Oblast, Russian Federation. http://itlife-studio.ru
*/

function infra_imager_browser($agent = false)
{
	if (!$agent) {
		$agent = $_SERVER['HTTP_USER_AGENT'];
	}
	$agent = strtolower($agent);
	$name = infra_once('infra_imager_browser', function ($agent) {
		if (preg_match('/msie (\d)/', $agent, $matches)) {
			$name = 'ie ie'.$matches[1];
		} elseif (preg_match('/opera/', $agent)) {
			$name = 'opera';
			if (preg_match('/opera\/9/', $agent)) {
				$name .= ' opera9';
			} elseif (preg_match('/opera (\d)/', $agent, $matches)) {
				$name .= ' opera'.$mathces[1];
			}
			if (preg_match('/opera\smini/', $agent)) {
				$name .= ' opera_mini';
			}
		} elseif (preg_match('/gecko\//', $agent)) {
			$name = 'gecko';
			if (preg_match('/firefox/', $agent)) {
				$name .= ' ff';
				if (preg_match('/firefox\/2/', $agent)) {
					$name .= ' ff2';
				} elseif (preg_match('/firefox\/3/', $agent)) {
					$name .= ' ff3';
				}
			}
		} elseif (preg_match('/webkit/', $agent)) {
			$name = 'webkit';
			if (preg_match('/chrome/', $agent)) {
				$name .= ' chrome';
			} else {
				$name .= ' safari';
			}
		} elseif (preg_match('/konqueror/', $agent)) {
			$name = 'konqueror';
		} elseif (preg_match('/flock/', $agent)) {
			$name = 'flock';
		} else {
			$name = 'stranger';
		}
		if (!preg_match('/ie/', $name)) {
			$name .= ' noie';
		}
		if (preg_match('/linux|x11/', $agent)) {
			$name .= ' linux';
		} elseif (preg_match('/macintosh|mac os x/', $agent)) {
			$name .= ' mac';
		} elseif (preg_match('/windows|win32/', $agent)) {
			$name .= ' win';
		}
		if (preg_match('/stranger/', $name)) {
			$name = '';
		}

		return $name;
	}, array($agent));

	return $name;
}

function imager_makeGray($img_path)
{
	return infra_cache(array($img_path), 'imager_makeGray', function ($img_path) {
		$dirs = infra_dirs();
		$type = imager_type($img_path);
		$name = md5($img_path);
		$output_path = $dirs['cache'].'imager_gray/'.$name.'.'.$type;

		$type_img = exif_imagetype($img_path);
		$gd = gd_info();

		if ($type_img == 3 and $gd['PNG Support'] == 1) {
			$img_png = imagecreatefromPNG($img_path);
			imagesavealpha($img_png, true);

			if ($img_png and imagefilter($img_png, IMG_FILTER_GRAYSCALE)) {
				@unlink($output_path);
				imagepng($img_png, $output_path);
			}
			imagedestroy($img_png);
		} elseif ($type_img == 2) {
			$img = imagecreatefromJPEG($img_path);
			if ($img and imagefilter($img, IMG_FILTER_GRAYSCALE)) {
				@unlink($output_path);
				imagejpeg($img, $output_path);
			}
			imagedestroy($img);
			/*
			if(!$color_total = imagecolorstotal($img_jpg)) {
			$color_total = 256;		          
			}   	          		          
			imagetruecolortopalette( $img_jpg, FALSE, $color_total );    
			
			for( $c = 0; $c < $color_total; $c++ ) {    
			 $col = imagecolorsforindex( $img_jpg, $c );		        
				 $i   = ( $col['red']+$col['green']+$col['blue'] )/3;
			 imagecolorset( $img_jpg, $c, $i, $i, $i );
			}		    
			@unlink( $output_path );
			imagejpeg( $img_jpg, $output_path );
			imagedestroy( $img_jpg );*/
		} elseif ($type_img == 1) {
			$img = imagecreatefromGIF($img_path);
			if ($img and imagefilter($img, IMG_FILTER_GRAYSCALE)) {
				@unlink($output_path);
				imagegif($img, $output_path);
			}
			imagedestroy($img);
			/*if(!$color_total = imagecolorstotal( $img_gif )) {
			$color_total = 256;		          
			}   
			imagetruecolortopalette( $img_gif, FALSE, $color_total );    
			
			for( $c = 0; $c < $color_total; $c++ ) {    
			 $col = imagecolorsforindex( $img_gif, $c );		        
				 $i   = ( $col['red']+$col['green']+$col['blue'] )/3;
			 imagecolorset( $img_gif, $c, $i, $i, $i );
			}		    
			@unlink( $output_path );
			imagegif( $img_gif, $output_path );
			imagedestroy( $img_gif );*/
		} else {
			return $img_path;
		}

		return $output_path;
	}, array($img_path), isset($_GET['re']));
}
function imager_getReal($src)
{
	$p = explode('?', $src, 2);
	$path = $p[0];
	$query = $p[1];
	if (!$query) {
		return infra_theme($path, 'fn');
	}
	if (preg_match("/theme\.php$/", $path)) {
		return imager_getReal($query);
	} elseif (preg_match("/imager\.php$/", $path)) {
		$p = explode('&', $query);
		$obj = array();
		for ($i = 0, $l = sizeof($p); $i < $l; ++$i) {
			$b = explode('=', $p[$i], 2);
			$obj[$b[0]] = $b[1];
		}

		return imager_getReal($obj['src']);
	}

	return false;
}
function imager_prepareSrc($src)
{
	if (preg_match("/^https{0,1}:\/\//", $src)) {
		//$src=infra_theme('*imager/noimage.png');
		$src = imager_remote($src, $hour);
	} else {
		if (preg_match("/\.php/", $src)) {
			//Такое может быть если путь до картинки передан тоже с imager то есть двойной вызов
			$src = imager_getReal($src);
		} else {
			$src = infra_theme($src, true);
		}
	}

	if ($src && is_dir($src)) {
		//папка смотрим в ней для src
		$ar = infra_loadJSON('*pages/list.php?src='.infra_toutf($src).'&e=jpg,gif,png&onlyname=1');
		//$ar=glob($src.'*.{png,gif,jpg}',GLOB_BRACE);
		$r = false;
		if (@$ar[0]) {
			$src = $src.infra_tofs($ar[0]);
			$src = infra_theme($src);
		} else {
			$src = false;
		}
	}

	return $src;
}
function imager_remote($src, $t)
{
	$dirs = infra_dirs();
	$dir = $dirs['cache'].'imager_remote/';

	@mkdir($dir);
	$esrc = $dir.infra_tofs(imager_encode($src));
	$remotecache = infra_theme($esrc);
	if ($remotecache && !isset($_GET['re'])) {
		$cachetime = filemtime($remotecache);
		$now = time();
		if ($now - ($t * 60 * 60) < $cachetime) {
			return $esrc;
		}
	}
	$filecontent = file_get_contents($src);
	file_put_contents($esrc, $filecontent);

	return $esrc;
}
function imager_type($src)
{
	$src = infra_tofs($src);

	return infra_cache(array($src), 'imager_type', function ($src) {
		$handle = fopen($src, 'r');
		$line = fgets($handle, 50);
		$line2 = fgets($handle, 50);
		fclose($handle);
		if (preg_match('/JFIF/', $line)) {
			return 'jpeg';
		}
		if (preg_match('/PNG/', $line)) {
			return 'png';
		}
		if (preg_match('/GIF/', $line)) {
			return 'gif';
		}
		if (preg_match('/Exif/', $line)) {
			return 'jpeg';
		}
		if (preg_match('/Exif/', $line2)) {
			return 'jpeg';
		}
		if (preg_match('/BM/', $line)) {
			return 'wbmp';
		}

		return false;
	}, array($src), isset($_GET['re']));
}
function &imager_readInfo($src)
{
	return infra_once('imager_readInfo', '_imager_readInfo', array($src));
}
function &_imager_readInfo($src)
{
	/*
	imager -2
	json
	====
	date
	host
	size
	orig
	imager -1
	====
	date
	host
	size
	orig
	imager - 4
	size
	host
	ignore
	====


	if($metka=='imager'){//Водяной знак уже есть
		$orig=trim(infra_toutf($file[$l-2]));//Путь до оригинала
	}else{
		$metka=preg_replace("/[\s\n]/",'',$file[$l-5]);//-1
		if($metka=='imager'){//Водяной знак уже есть
			$orig=trim(infra_toutf($file[$l-6]));//Путь до оригинала
		}
	}
	*/

	$file = file($src);
	$l = sizeof($file);
	$metka = preg_replace("/[\n]/", '', $file[$l - 2]);
	if ($metka == 'imager') {
		$json = preg_replace("/[\n]/", '', $file[$l - 1]);
		$data = json_decode($json, true, 512);
	} else {
		$metka = preg_replace("/[\s\n]/", '', $file[$l - 1]);
		if ($metka == 'imager') {
			$data = array(
				'orig' => preg_replace("/[\s\n]/", '', $file[$l - 2]),
				'size' => preg_replace("/[\s\n]/", '', $file[$l - 3]),
				'host' => preg_replace("/[\s\n]/", '', $file[$l - 4]),
				'date' => preg_replace("/[\s\n]/", '', $file[$l - 5]),
				'water' => true,
				'ignore' => false,
			);
		} else {
			$metka = preg_replace("/[\s\n]/", '', @$file[$l - 4]);
			if ($metka === 'imager') {
				$data = array(
					'orig' => preg_replace("/[\s\n]/", '', @$file[$l - 5]),
					'size' => preg_replace("/[\s\n]/", '', @$file[$l - 6]),
					'host' => preg_replace("/[\s\n]/", '', @$file[$l - 7]),
					'date' => preg_replace("/[\s\n]/", '', @$file[$l - 8]),
					'water' => true,
					'ignore' => preg_replace("/[\s\n]/", '', @$file[$l - 1]),
				);
				$data['ignore'] = ($data['ignore'] == 'ignore');
			} else {
				$data = array();
			}
		}
	}
	if (!is_array($data)) {
		$data = array();
	}

	return $data;
}

function imager_writeinfo($src, $data)
{
	$file = file($src);
	$l = sizeof($file);
	$metka = preg_replace("/[\s\n]/", '', $file[$l - 2]);
	$json = json_encode($data, JSON_UNESCAPED_UNICODE);
	if ($metka == 'imager') {
		unset($file[$l - 1]);
		unset($file[$l - 2]);
	} else {
		$metka = preg_replace("/[\s\n]/", '', $file[$l - 1]);
		if ($metka == 'imager') {
			unset($file[$l - 1]);
			unset($file[$l - 2]);
			unset($file[$l - 3]);
			unset($file[$l - 4]);
			unset($file[$l - 5]);
		} else {
			$metka = preg_replace("/[\s\n]/", '', $file[$l - 4]);
			if ($metka === 'imager') {
				unset($file[$l - 1]);
				unset($file[$l - 2]);
				unset($file[$l - 3]);
				unset($file[$l - 4]);
				unset($file[$l - 5]);
				unset($file[$l - 6]);
				unset($file[$l - 7]);
				unset($file[$l - 8]);
			}
		}
	}
	$l = sizeof($file);
	$file[] = "\n".'imager';
	$file[] = "\n".$json;
	infra_once('imager_readInfo', $data, array($src));

	return file_put_contents($src, implode('', $file));
}

function imager_encode($file)
{
	//Передаётся имя со слэшами надо сделать имя файла и путь до него
	$file = preg_replace('/[\/:]/', ',', $file);//Если в имени файла уже есть запятая не получится сделать водяной знак
	if (mb_strlen($file) > 50) {
		$file = md5($file);
	}

	return $file;
}
function &imager_makeInfo($src)
{
	$info = imager_readInfo($src);
	if ($info) {
		return $info;
	}

	$dirs = infra_dirs();
	$dir = $dirs['data'].'imager/';
	@mkdir($dir);
	$dir .= '.notwater/';
	@mkdir($dir);

	$i = '';
	$orig = $dir.infra_forFS($src);
	while (is_file($orig)) {
		$orig = $orig.$i;
		$i .= 'i';
	}
	$r = copy($src, $orig);//по адресу orig не существует файла было проверено
	if (!$r) {
		die('Не удалось сохранить оригинал');
	}
	$info = array();
	$info['host'] = $_SERVER['HTTP_HOST'];
	$info['size'] = filesize(infra_tofs($orig));
	$info['date'] = date('j.m.Y');
	$info['orig'] = infra_toutf($orig);

	return $info;
}
function imager_mark($src, $type)
{
	$conf = infra_config();
	if (!$conf['imager']['watermark']) {
		return;
	}

	if (!$type) {
		return;
	}

	if ($type == 'gif') {
		return;
	}//Проблема прозрачности

	$info = &imager_readInfo($src);

	if (@$info['ignore']) {
		return;
	}//В изображении указано что не нужно делать водяной знак на нём

	if (@$info['water']) {
		return;
	}//Если нужно повторно наложить водяной знак, для этого нужно удалить все старые знаки

	$water = infra_theme('*imager/mark.png');

	if (!$water) {
		return;
	}
	//Добавляем водяной знак
	$orig = $info['orig'];

	if (!$orig) {
		$orig = $src;
	} elseif (!infra_theme($orig)) {
		return;
	}//Защита.. оригинал не найден.. значит старая версия водяной знак есть,
	//метке water нет. второй знак не нужен

	$fn = 'imagecreatefrom'.$type;
	$img = $fn($orig);

	$orig = infra_theme($orig);
	list($w, $h) = getimagesize($orig);
	$w = $w * 9 / 10;
	$h = $h * 9 / 10;
	$water = imager_scale($water, 'png', $w, $h);
	
	$temp=tmpfile();
	fwrite($temp, $water);
	$meta=stream_get_meta_data($temp);
	$water = imagecreatefrompng($meta['uri']);
	$img = create_watermark($type, $img, $water, 100);//$img - картинка с водяным знаком


	$info = imager_makeInfo($src);//Сделали бэкап, или считали info у существующего файла, чтобы после изменений сохранить прошлые

	$fn = 'image'.$type;
	$fn($img, $src);//Подменили картинку на картинку с водяным знаком

	$info['water'] = true;
	imager_writeInfo($src, $info);

	return;
}

# given two images, return a blended watermarked image
function create_watermark($type, $main_img_obj, $watermark_img_obj, $alpha_level = 100)
{
	$alpha_level    /= 100;    # convert 0-100 (%) alpha to decimal

	# calculate our images dimensions
	$main_img_obj_w = imagesx($main_img_obj);
	$main_img_obj_h = imagesy($main_img_obj);
	$watermark_img_obj_w = imagesx($watermark_img_obj);
	$watermark_img_obj_h = imagesy($watermark_img_obj);

	# determine center position coordinates
	$main_img_obj_min_x = floor(($main_img_obj_w / 2) - ($watermark_img_obj_w / 2));
	$main_img_obj_max_x = ceil(($main_img_obj_w / 2) + ($watermark_img_obj_w / 2));
	$main_img_obj_min_y = floor(($main_img_obj_h / 2) - ($watermark_img_obj_h / 2));
	$main_img_obj_max_y = ceil(($main_img_obj_h / 2) + ($watermark_img_obj_h / 2));

	# create new image to hold merged changes
	$return_img = imagecreatetruecolor($main_img_obj_w, $main_img_obj_h);

	# walk through main image
	for ($y = 0; $y < $main_img_obj_h; ++$y) {
		for ($x = 0; $x < $main_img_obj_w; ++$x) {
			$return_color = null;

			# determine the correct pixel location within our watermark
			$watermark_x = $x - $main_img_obj_min_x;
			$watermark_y = $y - $main_img_obj_min_y;

			# fetch color information for both of our images
			$main_rgb = imagecolorsforindex($main_img_obj, imagecolorat($main_img_obj, $x, $y));

			# if our watermark has a non-transparent value at this pixel intersection
			# and we're still within the bounds of the watermark image
			if ($watermark_x >= 0 && $watermark_x < $watermark_img_obj_w &&
						$watermark_y >= 0 && $watermark_y < $watermark_img_obj_h) {
				$watermark_rbg = imagecolorsforindex($watermark_img_obj, imagecolorat($watermark_img_obj, $watermark_x, $watermark_y));

				# using image alpha, and user specified alpha, calculate average
				$watermark_alpha = round(((127 - $watermark_rbg['alpha']) / 127), 2);
				$watermark_alpha = $watermark_alpha * $alpha_level;

				# calculate the color 'average' between the two - taking into account the specified alpha level
				$avg_red = _get_ave_color($main_rgb['red'], $watermark_rbg['red'], $watermark_alpha);
				$avg_green = _get_ave_color($main_rgb['green'], $watermark_rbg['green'], $watermark_alpha);
				$avg_blue = _get_ave_color($main_rgb['blue'], $watermark_rbg['blue'], $watermark_alpha);

				# calculate a color index value using the average RGB values we've determined
				$return_color = _get_image_color($return_img, $avg_red, $avg_green, $avg_blue);

			# if we're not dealing with an average color here, then let's just copy over the main color
			} else {
				$return_color = imagecolorat($main_img_obj, $x, $y);
			} # END if watermark

			# draw the appropriate color onto the return image
			imagesetpixel($return_img, $x, $y, $return_color);
		} # END for each X pixel
	} # END for each Y pixel

	# return the resulting, watermarked image for display
	return $return_img;
}
function _get_ave_color($color_a, $color_b, $alpha_level)
{
	return round((($color_a * (1 - $alpha_level)) + ($color_b    * $alpha_level)));
}

# return closest pallette-color match for RGB values
function _get_image_color($im, $r, $g, $b)
{
	$c = imagecolorexact($im, $r, $g, $b);
	if ($c != -1) {
		return $c;
	}
	$c = imagecolorallocate($im, $r, $g, $b);
	if ($c != -1) {
		return $c;
	}

	return imagecolorclosest($im, $r, $g, $b);
}
function imager_setTransparency($new_iamge, $image_source)
{
	var_dump($image_source);
	$transparencyIndex = imagecolortransparent($image_source);
	var_dump($transparenceIndex);
	exit;
	$transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);
	if ($transparencyIndex >= 0) {
		$transparencyColor = imagecolorsforindex($image_source, $transparencyIndex);
	}
	$transparencyIndex = imagecolorallocate($new_image, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
	imagefill($new_image, 0, 0, $transparencyIndex);
	imagecolortransparent($new_image, $transparencyIndex);
}

function imager_scale($src, $w, $h, $crop = false, $top = false, $bottom = false)
{
	$type=imager_type($src);
	if (!$type || (!$w && !$h)) {
		return file_get_contents($src);
	}
	
	
	list($width_orig, $height_orig) = getimagesize($src);
	if (!$height_orig) {
		return file_get_contents($src);
	}

	//Размер который делаем не должен быть больше оригинального
	//На случай если требуемый размер слишком большой оставляем оригинальный
	if (($w && $width_orig < $w) || !$w) {
		$w = $width_orig;
	}
	if (($h && $height_orig < $h) || !$h) {
		$h = $height_orig;
	}
	$dh = 0;
	$dw = 0;
	if ($w && $h) {
		$k = $w / $h;
		$k_orig = $width_orig / $height_orig;
		if ($k_orig == $k) {//Не важно.. что уменьшаем пропорции останутся одинаковыми
		} elseif ($k_orig > $k) {
			//ширины в оригинале больше чем в требуемом.. с учётом размеров высоты.
			if (!$crop) {
				//Значит чтобы ничего не обрезать и быть в рамках меняем ширину а высота и так относительно меньше требуемой
				$h = false;//Значит высоту нужно высчитать отностительно ширины
			} else {
				//Ну а если нужно чтобы указанные размеры были полностью заполнены то ширину надо обрезать и равняться будем уже на высоту
				$d = $h / $height_orig;//Коэфициент на сколько изменяем оригинальный размер
				$dw = (($width_orig * $d - $w)) / $d;
			}
		} else {
			if (!$crop) {
				//Значит меняем ширину а высота и так относительно меньше требуемой
				$w = false;
			} else {
				//Ну а если обрезать, то высоту/ Ровняемся на ширину
				$d = $w / $width_orig;//Коэфициент на сколько изменяем оригинальный размер
				$dh = (($height_orig * $d - $h)) / $d;
			}
		}
	}

	if (!$w) {
		$w = ($h / $height_orig) * $width_orig;
	}
	if (!$h) {
		$h = ($w / $width_orig) * $height_orig;
	}
	$c = ($crop && ($dh || $dw)) ? ' crop' : '';
	$t = ($top && $c) ? ' top' : '';
	$b = ($bottom && $c) ? ' bottom' : '';

	$dir = 'imager_resize/';
	$dir = $dir.infra_forFS($src);
	$src_cache = $dir.'/w'.$w.' x h'.$h.$c.$t.$b.'.'.$type;


	$image_p = imagecreatetruecolor($w, $h);

	$fn = 'imagecreatefrom'.$type;
	$image = $fn($src);

	//image_p пустая картинка но нужных размеров
	//image забрали нужную картинку которую нужно превратить в image_p
	//echo '<br>w '.$w;echo '<br>width_orig '.$width_orig;echo '<br>k '.$k;echo '<br>k_orig '.$k_orig;echo '<br>d '.$d;echo '<br>dw '.$dw;echo '<br>dh '.$dh;exit;
	if ($type == 'png') {
		imagealphablending($image_p, false);
		imagesavealpha($image_p, true);
	}
	if ($type == 'gif') {
		$colorcount = imagecolorstotal($image);
		imagetruecolortopalette($image_p, true, $colorcount);
		imagepalettecopy($image_p, $image);
		$transparentcolor = imagecolortransparent($image);
		if ($transparentcolor == -1) {
			$transparentcolor = 255;
		}
		imagefill($image_p, 0, 0, $transparentcolor);
		imagecolortransparent($image_p, $transparentcolor);
	}
	//if($crop&&$top)$dh=0;
	if ($top) {
		$fromtop = 0;
	} elseif ($bottom) {
		$fromtop = $dh;
	} else {
		$fromtop = $dh / 2;
	}

	imagecopyresampled($image_p, $image, 0, 0, $dw / 2, $fromtop, $w, $h, $width_orig - $dw, $height_orig - $dh);
	$fn = 'image'.$type;
	$quality = 90;
	if ($type == 'png') {
		$quality = 2;
	}
	
	ob_start();
	$fn($image_p, null, $quality);
	$data=ob_get_contents();
	ob_end_clean();
	imagedestroy($image);
	imagedestroy($image_p);

	return $data;
}
