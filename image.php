<?php
/** Copyright (c) 2019 Mesibo
 * https://mesibo.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the terms and condition mentioned on https://mesibo.com
 * as well as following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list
 * of conditions, the following disclaimer and links to documentation and source code
 * repository.
 *
 * Redistributions in binary form must reproduce the above copyright notice, this
 * list of conditions and the following disclaimer in the documentation and/or other
 * materials provided with the distribution.
 *
 * Neither the name of Mesibo nor the names of its contributors may be used to endorse
 * or promote products derived from this software without specific prior written
 * permission.
 *
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA,
 * OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Documentation
 * https://mesibo.com/documentation/
 *
 * Source Code Repository
 * https://github.com/mesibo/messenger-app-backend
 *
 * Android App Source code Repository
 * https://github.com/mesibo/messenger-app-android
 *
 * iOS App Source code Repository
 * https://github.com/mesibo/messenger-app-ios
 *
 */



function image_convert($srcfile, $ext, $dstfile, $dwidth, $dheight, $quality=70) {
	if('' == $ext) {
		$ext_arr = explode(".",basename($srcfile));
		$ext = strtolower($ext_arr[count($ext_arr)-1]); //Get the last extension
	}

	$imgtype = exif_imagetype($srcfile);
	if(IMAGETYPE_JPEG == $imgtype) {
		$ext = 'jpg';
	} else if(IMAGETYPE_PNG == $imgtype) {
		$ext = 'png';
	} else if(IMAGETYPE_GIF == $imgtype) {
		$ext = 'gif';
	}

	if ('jpg' == $ext || 'jpeg' == $ext)
		$image=imagecreatefromjpeg($srcfile);
	else if ('png' == $ext)
		$image=@imagecreatefrompng($srcfile);
	else if ('gif' == $ext)
		$image=imagecreatefromgif($srcfile);
	else if ('bmp' == $ext)
		$image=imagecreatefrombmp($srcfile);
	else {
		//print 'wrong src file';
		return false;
	}

	if(!$image)
		return false;

	$width = imagesx($image);
	$height = imagesy($image);

	$dst_img = imagecreatetruecolor($dwidth, $dheight);
	$src_img = $image;

	$width_new = $height * $dwidth / $dheight;
	$height_new = $width * $dheight / $dwidth;
	//if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
	if($width_new > $width){
		//cut point by height
		$h_point = (($height - $height_new) / 2);
		//copy image
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $dwidth, $dheight, $width, $height_new);
	}else{
		//cut point by width
		$w_point = (($width - $width_new) / 2);
		imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $dwidth, $dheight, $width_new, $height);
	}

	imagejpeg($dst_img, $dstfile, $quality);

	if($dst_img)imagedestroy($dst_img);
	if($src_img)imagedestroy($src_img);

	return true;


}

