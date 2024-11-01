<?php
if ( !class_exists( 'Player_SmartS3' ) ) {
	class Player_SmartS3 extends Methods_SmartS3 {
		/**
		 * Performs necessary instantiation routines
		 */
		public function __construct() {
			parent::__construct();
		}
		/**
		 * Default player options filter
		 * @param array $options Options to filter
		 * @return array $options The filtered options
		 */
		public function Player_Options($options) {
			$settings = get_option('smarts3');
			
			if ( !empty( $settings['fp_key'] ) ) {
				$options['key'] = $settings['fp_key'];
			}
			
			$options['native_fullscreen'] = true;
			
			return $options;
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
		public function player($mp4, $ogg, $webm, $width = false, $height = false, $autoplay = false, $poster = false) {
			//Set up player ID to allow multiple videos per page
			$player_id = rand();
			
			//Style setup
			$style = 'style="margin-bottom: 35px;';
			
			if ( $width && $height ) {
				$style .= 'width:' . $width . 'px;height:' . $height . 'px;';
			}
			
			if ( $poster ) {
				$style .= 'background:url(' . $poster . ') no-repeat;';
			}
			
			$style .= '"';
			
			//Class setup
			$class = 'smarts3_player player_' . $player_id;
			
			if ( $poster ) {
				$class .= ' is-splash';
			}
			
			$player = '<div class="' . $class . '"  '. $style . '>';
			
			//Video tag attributes setup
			if ( $autoplay ) {
				$attributes['autoplay'] = 'autoplay';
			}
			
			$video_tag = implode(' ', $attributes);
			
			$player .= '<video ' . $video_tag . ' controls>';
			
			//Video source setup
			if ( !empty( $mp4 ) ) {
				$player .= '<source type="video/mp4" src="' . $mp4 . '">';
			}
			
			if ( !empty( $ogg ) ) {
				$player .= '<source type="video/ogg" src="' . $ogg . '">';
			}
			
			if ( !empty( $webm ) ) {
				$player .= '<source type="video/webm" src="' . $webm . '">';
			}
			
			//Closing tags
			$player .= '</video>';
			$player .= '</div>';
			
			//Javascript setup
			$jsvars = apply_filters('smarts3_player_options', array());
			$options = json_encode($jsvars);
						
			$player .= '
				<script>
				jQuery(function ($) {
				   $(".player_' . $player_id . '").flowplayer(' . $options . ');
				});
				</script>';
			
			return $player;
		}
	}
}