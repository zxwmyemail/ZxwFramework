<?php
namespace core\library;

class WaterMask {

	// **************************************** // 
	// 功能:图片叠加
	// 参数: $dst 背景图片地址
	// src 叠加图片地址
	// newfile 另存图片文件名
	// left 距离背景图片左边的距离
	// top 距离背景图片上部的距离
	// **************************************** // 
	public function superimposedPng($dst, $src, $newfile, $left = null, $top = null) {
		//得到原始图片信息
		$dst_im = null;
		$dst_info = getimagesize($dst);
		switch ($dst_info[2]) {
		  	case 1: $dst_im = imagecreatefromgif($dst); break;
		  	case 2: $dst_im = imagecreatefromjpeg($dst);  break;
		  	case 3: $dst_im = imagecreatefrompng($dst); break;
		  	default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
		}

		if (($dst_info[2] == 1) OR ($dst_info[2] == 3)) {
			imagesavealpha($dst_im, true);
		}

		//水印图像
		$src_im = null;
		$src_info = getimagesize($src);
		switch ($src_info[2]) {
		  	case 1: $src_im = imagecreatefromgif($src); break;
		  	case 2: $src_im = imagecreatefromjpeg($src);  break;
		  	case 3: $src_im = imagecreatefrompng($src); break;
		  	default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
		}
		 
		if(empty($left)){
			$left = $dst_info[0]-$src_info[0];
		}
		if(empty($top)){
			$top = $dst_info[1]-$src_info[1];
		}
		 
		//合并水印图片
		imagecopy($dst_im,$src_im,$left,$top,0,0,$src_info[0],$src_info[1]);

		switch ($dst_info[2]) {
		  	case 1: imagegif($dst_im,$newfile); break;
		  	case 2: imagejpeg($dst_im,$newfile);  break;
		  	case 3: imagepng($dst_im,$newfile); break;
		  	default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
		}
		 
		//输出合并后水印图片
		imagedestroy($dst_im);
		imagedestroy($src_im);
	}

	// **************************************** // 
	// 功能:添加文字水印
	// 参数: 
	// filePath 叠加图片地址
	// posX 文字对于图片的x坐标
	// poxY 文字对于图片的y坐标
	// fontSize 文字大小
	// tilt 文字倾斜角度
	// color 文字颜色
	// ttf 文字字体文件位置
	// **************************************** // 
	public function waterMask($filePath, $text = '', $posX = 0, $poxY = 0, $fontSize = 20, $tilt = 0, $color = [0,0,0], $ttf = 'C:/Windows/Fonts/simfang.ttf') {

		$imgHandle = null;
        $imgInfo = getimagesize($filePath); 
        switch ($imgInfo[2]) {
		  	case 1: $imgHandle = imagecreatefromgif($filePath); break;
		  	case 2: $imgHandle = imagecreatefromjpeg($filePath);  break;
		  	case 3: $imgHandle = imagecreatefrompng($filePath); break;
		  	default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
		}
		if (($imgInfo[2] == 1) OR ($imgInfo[2] == 3)) {
			imagesavealpha($imgHandle, true);
		}
		
		$color = imagecolorallocate($imgHandle, $color[0], $color[1], $color[2]);
		  
		imagettftext($imgHandle, $fontSize, $tilt, $posX, $poxY, $color, $ttf, $text);  

		switch ($imgInfo[2]) {
		  	case 1: imagegif($imgHandle,$filePath); break;
		  	case 2: imagejpeg($imgHandle,$filePath);  break;
		  	case 3: imagepng($imgHandle,$filePath); break;
		  	default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
		}
    }
 
	// **************************************** // 
	// 功能:图片叠加
	// 参数: $dst 背景图片地址
	// src png图片地址
	// size ico的大小
	// filename 转换的ico的名字
	// **************************************** // 
	public function covertPngToIco($src,$size,$filename) {
		$im = imagecreatefrompng($src);
		$imginfo = getimagesize($src);
		 
		$resize_im = @imagecreatetruecolor($size,$size);
		 
		imagealphablending($resize_im, false);
		imagecolortransparent($resize_im, imagecolorallocatealpha($resize_im, 0, 0, 0,0));
		 
		imagecopyresampled($resize_im,$im,0,0,0,0,$size,$size,$imginfo[0],$imginfo[1]);
		include "phpthumb.ico.php";
		$icon = new phpthumb_ico();
		$gd_image_array = array($resize_im);
		$icon_data = $icon->GD2ICOstring($gd_image_array);
		$filename = $filename.".ico";
		//保存ico
		file_put_contents($filename, $icon_data);
	}
 
	// **************************************** // 
	// 功能:重置图片大小
	// 参数: $im 图片值
	// maxwidth 转换长度
	// maxheight 转换高度
	// name 转换的名字
	// filetype 转换类型
	// **************************************** // 
	public function resizeImage($img, $w, $h, $newfilename) {
		//Check if GD extension is loaded
		if (!extension_loaded('gd') && !extension_loaded('gd2')) {
		  	trigger_error("GD is not loaded", E_USER_WARNING);
		  	return false;
		}
		 
		//Get Image size info
		$imgInfo = getimagesize($img);
		switch ($imgInfo[2]) {
		  	case 1: $im = imagecreatefromgif($img); break;
		  	case 2: $im = imagecreatefromjpeg($img);  break;
		  	case 3: $im = imagecreatefrompng($img); break;
		  	default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
		}
		 
		//If image dimension is smaller, do not resize
		if ($imgInfo[0] <= $w && $imgInfo[1] <= $h) {
		  	$nHeight = $imgInfo[1];
		  	$nWidth = $imgInfo[0];
		} else {
		    //yeah, resize it, but keep it proportional
			if ($w/$imgInfo[0] > $h/$imgInfo[1]) {
			   $nWidth = $w;
			   $nHeight = $imgInfo[1]*($w/$imgInfo[0]);
			}else{
			   $nWidth = $imgInfo[0]*($h/$imgInfo[1]);
			   $nHeight = $h;
		  	}
		}
		$nWidth = round($nWidth);
		$nHeight = round($nHeight);
		 
		$newImg = imagecreatetruecolor($nWidth, $nHeight);
		 
		/* Check if this image is PNG or GIF, then set if Transparent*/  
		if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)){
			imagealphablending($newImg, false);
			imagesavealpha($newImg,true);
			$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
			imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
		}
		imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);
		 
		//Generate the file, and rename it to $newfilename
		switch ($imgInfo[2]) {
		  	case 1: imagegif($newImg,$newfilename); break;
		  	case 2: imagejpeg($newImg,$newfilename);  break;
		  	case 3: imagepng($newImg,$newfilename); break;
		  	default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
		}
	   
	   	return $newfilename;
	}

	/**
     * 将图片变为圆形
     */
    public function cropToCircle($oldHeadPath, $newHeadPath, $isSave = true) {
    	$imgInfo = getimagesize($oldHeadPath);
		switch ($imgInfo[2]) {
		  	case 1: 
		  		$img = imagecreatefromgif($oldHeadPath);
		  		imagepng ($img, $newHeadPath); 
		  		break;
		  	case 2: 
		  		$img = imagecreatefromjpeg($oldHeadPath); 
		  		imagepng ($img, $newHeadPath); 
		  		break;
		  	default:   
		  		break;
		}

        $src_img = imagecreatefromstring(file_get_contents($newHeadPath));
        $w = imagesx($src_img);
        $h = imagesy($src_img);
        $w = $h = min($w, $h);
     
        $img = imagecreatetruecolor($w, $h);
        //这一句一定要有
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $w / 2; //圆半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        
        //返回资源
        if(!$isSave) return $img;
        //输出图片到文件
        imagepng($img, $newHeadPath);
        //释放空间
        imagedestroy($src_img);
        imagedestroy($img);
    }
}
