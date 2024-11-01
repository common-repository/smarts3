<?php
/**
 * Gets a non-parsed, signed URL
 * @global object $smarts3 The SmartS3 object
 * @param string $bucket Name of the bucket
 * @param string $key Path to the requested file
 * @param string $expiration Expiration period
 * @return string $url The signed URL
 */
function smarts3_get_signed_url($bucket, $key, $expiration = '10 minutes') {
	global $smarts3;
	
	return $smarts3->GetSignedURL($bucket, $key, $expiration);
}
/**
 * Parses a URL
 * @global object $smarts3 The SmartS3 object
 * @param string $url The URL to parse
 * @return string $parsed_url The parsed URL
 */
function smarts3_parse_url($url) {
	global $smarts3;
	
	return $smarts3->Parse_URL($url);
}
/**
 * Parses and signs a URL
 * @param string $url The URL to parse and sign
 * @param string $expiration Expiration period
 * @return string $signed_url The parsed and signed URL
 */
function smarts3_get_url($url, $expiration = '10 minutes') {
	$url = smarts3_parse_url($url);
	
	return smarts3_get_signed_url($url->bucket, $url->key);
}
/**
 * Builds the video player
 * @param string $mp4 URL of the MP4 video
 * @param string $ogg URL of the OGG video
 * @param string $webm URL of the WebM video
 * @param int $width Desired width of the player
 * @param int $height Desired height of the player
 * @param boolean $autoplay True/false to autoplay the video
 * @param string $poster URL of the poster image
 * @return string $player The player HTML code
 */
function smarts3_player($mp4 = false, $ogg = false, $webm = false, $width = false, $height = false, $autoplay = false, $poster = false) {
	if ( !$mp4 && !$ogg && !$webm ) {
		return;
	}
	
	global $smarts3;
	
	if ( $mp4 ) {
		$mp4 = smarts3_get_url($mp4, $expiration = '10 minutes');
	} else {
		$mp4 = '';
	}

	if ( $ogg ) {
		$ogg = smarts3_get_url($ogg, $expiration = '10 minutes');
	} else {
		$ogg = '';
	}

	if ( $webm ) {
		$webm = smarts3_get_url($webm, $expiration = '10 minutes');
	} else {
		$webm = '';
	}
	
	return $smarts3->player($mp4, $ogg, $webm, $width, $height, $autoplay, $poster);
}
