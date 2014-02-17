<?php

/*
Plugin Name: Media Thumbnail Generator
Description: Generates a media file thumbnail linked to the media file (doc,xls,ppt,pdf,txt)
Version: 1.0
Author: Michael Bianco
Author URI: http://cliffsidemedia.com/
*/

define('MEDIA_THUMBNAIL_PLUGIN_URL', plugin_dir_url(__FILE__));

function iloveitaly_download_thumbnail($attr) {
	$download_url = $attr['url'];

	// default to 24 hour expiration
	$expire = 60 * 60 * 24;

	$width = 200;
	$height = 0;

	if(!empty($attr['width'])) {
		$width = $attr['width'];
	}

	if(!empty($attr['height'])) {
		$height = $attr['height'];
		$width = 0;
	}

	$cache_file_hash = md5($attr['url'].$height.$width).'.png';
	$cache_file = dirname(__FILE__).'/cache/'.$cache_file_hash;

	if(file_exists($cache_file)) {
		$cache_file_modified = filemtime($cache_file);

		// delete the cached file if it's expired
		if($cache_file_modified < time() - $expire || filesize($cache_file) == 0) {
			unlink($cache_file);
		}
	}

	if(!file_exists($cache_file)) {
		$encoded_download_url = urlencode($download_url);
		$thumbnail_url = "http://docs.google.com/viewer?a=bi&pagenumber=1&url={$encoded_download_url}";

		// height has preference if w & h are defined
		if($height !== 0) {
			$thumbnail_url .= "&h={$height}";
		} else {
			$thumbnail_url .= "&w={$width}";
		}

		file_put_contents($cache_file, wp_remote_fopen($thumbnail_url));
	}

	$public_cache_file = MEDIA_THUMBNAIL_PLUGIN_URL.'cache/'.$cache_file_hash;

	$text_link = "";

	if(!empty($attr['title'])) {
		$text_link = "<br/><a href='{$download_url}' target='_blank'>{$attr['title']}</a>";
	}

	return "<a href='{$download_url}' target='_blank'><img src='{$public_cache_file}' /></a>".$text_link;
}

add_shortcode("download_thumbnail", "iloveitaly_download_thumbnail");
?>
