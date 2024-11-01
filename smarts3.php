<?php
/*
Plugin Name: SmartS3
Plugin URI: http://smarts3.com
Description: Enables easy embedding of Amazon S3 videos into a WordPress blog
Version: 0.0.2
Author: John Morris
Author URI: http://johnmorrisonline.com
Text Domain: smarts3
License: GPL3

Copyright 2013  John Morris  (email : johnmorrislive@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 3, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( dirname( __FILE__ ) . '/includes/aws/aws-autoloader.php' );
require_once( dirname( __FILE__ ) . '/includes/class-adapter-aws.php' );
require_once( dirname( __FILE__ ) . '/includes/class-methods-smarts3.php' );
require_once( dirname( __FILE__ ) . '/includes/class-player-smarts3.php' );
require_once( dirname( __FILE__ ) . '/admin/class-smarts3-admin.php' );
require_once( dirname( __FILE__ ) . '/includes/functions.php' );

if ( !class_exists( 'SmartS3' ) ) {
	class SmartS3 extends SmartS3_Admin {
		/**
		 * Performs necessary instantiation routines
		 */
		public function __construct() {
			parent::__construct();
		}
		/**
		 * Enqueues styles and scripts
		 */
		public function enqueue_scripts() {
			//Non-admin scripts
			if ( !is_admin() ) {
				wp_register_script('flowplayer', 'http://releases.flowplayer.org/5.4.2/flowplayer.min.js', array('jquery'));
				wp_register_style('flowplayer', 'http://releases.flowplayer.org/5.4.2/skin/minimalist.css');
				wp_register_style('smarts3_player', plugins_url('/css/style.css', __FILE__));
				
				wp_enqueue_script('flowplayer');
				wp_enqueue_style('flowplayer');
				wp_enqueue_style('smarts3_player');
			}
		}
		/**
		 * Shortcode handler
		 * @param array $atts Array of shortcode attributes
		 * @return string $html The video player HTML
		 */
		public function shortcode($atts) {
			extract($atts);
			
			$options = get_option('smarts3');
			$expiration = $options['expiration'];
			
			if ( !$mp4 && !$ogg && !$webm ) {
				return;
			}
			
			if ( $mp4 ) {
				$mp4_object = $this->Parse_URL($mp4);
				$mp4 = $this->GetSignedURL($mp4_object->bucket, $mp4_object->key, $expiration);
			} else {
				$mp4 = '';
			}
			
			if ( $ogg ) {
				$ogg_object = $this->Parse_URL($ogg);
				$ogg = $this->GetSignedURL($ogg_object->bucket, $ogg_object->key, $expiration);
			} else {
				$ogg = '';
			}
			
			if ( $webm ) {
				$webm_object = $this->Parse_URL($webm);
				$webm = $this->GetSignedURL($webm_object->bucket, $webm_object->key, $expiration);
			} else {
				$webm = '';
			}
						
			return $this->player($mp4, $ogg, $webm, $width, $height, $autoplay, $poster);
		}
		/**
		 * Creates the media button
		 * @param string $context Existing button HTML
		 * @return string $context Updated button HTML
		 */
		public function media_buttons($context) {
			$button = '<a href="#TB_inline?width=450&inlineId=smarts3_shortcode_generator" class="thickbox button" title="' . __("Add S3 Video", 'smarts3') . '">Add S3 Video</a>';
			
			return $context . $button;
		}
		/**
		 * Shortcode generator pop-up
		 */
		public function shortcode_generator() {?>
			<div id="smarts3_shortcode_generator" style="display:none;">
				<style>					
					#smarts3_shortcode_generator_inner {
						padding: 0 0 0 25px;
					}
					
					#smarts3_shortcode_generator_inner .form_field {
						clear: both;
						width: 100%;
					}
					
					#smarts3_shortcode_generator_inner .form_field_half {
						clear: none;
						float: left;
						width: 50%;
					}
					
					#smarts3_shortcode_generator_inner .form_field_submit {
						text-align: right;
					}
					
					#smarts3_shortcode_generator span {
						display: block;
						float: left;
					}
					
					#smarts3_shortcode_generator_inner h3 {
						font-size: 24px;
						font-weight: bold;
						margin-bottom: 15px;
					}
					
					#smarts3_shortcode_generator_inner p {
						margin: 0;
						padding: 0;
					}
					
					#smarts3_shortcode_generator_inner input[type="text"] {
						width: 100%;
						box-sizing: border-box;
						-moz-box-sizing: border-box;
						-webkit-box-sizing: border-box;
						margin-bottom: 14px;
						padding: 7px;
					}
					
					#smarts3_shortcode_generator_inner input.smarts3_half {
						width: 70px;
						float: left;
					}
					
					#smarts3_shortcode_generator_inner label.smarts3_autoplay {
						position: relative;
						top: 27px;
					}
				</style>
				<script>
					jQuery(document).ready(function($){
						$('#smarts3_submit_form').click(function(){
							var mp4 = $('#smarts3_mp4').val(),
								ogg = $('#smarts3_ogg').val(),
								webm = $('#smarts3_webm').val();
								
							if ( ( mp4 == '') && ( ogg == '' ) && ( webm == '')  ) {
								alert('Please enter a video URL');
								
								return;
							}
								
							var	poster = $('#smarts3_poster').val(),
								width = $('#smarts3_width').val(),
								height = $('#smarts3_height').val(),
								autoplay = $('#smarts3_autoplay').is(":checked");
								
							var shortcode = '[smarts3';
							
							if ( mp4 != '' ) {
								shortcode += ' mp4="'+mp4+'"';
							}
							
							if ( ogg != '' ) {
								shortcode += ' ogg="'+ogg+'"';
							}
							
							if ( webm != '' ) {
								shortcode += ' webm="'+webm+'"';
							}
							
							if ( poster != '' ) {
								shortcode += ' poster="'+poster+'"';
							}
							
							if ( width != '' ) {
								shortcode += ' width="'+width+'"';
							}
							
							if ( height != '' ) {
								shortcode += ' height="'+height+'"';
							}
							
							autoplay ? shortcode += ' autoplay="true"' : "";
							
							shortcode += ']';
							
							var win = window.dialogArguments || opener || parent || top;
							win.send_to_editor(shortcode);
						});
					});
				</script>
				<div class="wrap" id="smarts3_shortcode_generator_inner">
					<h3><?php _e("Insert S3 Video", "smartS3"); ?></h3>
					<div class="form_field">
						<p><label for="smarts3_mp4"><?php _e('MP4'); ?></label></p>
						<p><input type="text" id="smarts3_mp4" placeholder="<?php _e('Enter the URL of your MP4 video file', 'smarts3'); ?>"></p>
					</div>
					<div class="form_field">
						<p><label for="smarts3_ogg"><?php _e('OGG (optional)'); ?></label></p>
						<p><input type="text" id="smarts3_ogg" placeholder="<?php _e('Enter the URL of your OGG video file', 'smarts3');?>"></p>
					</div>
					<div class="form_field">
						<p><label for="smarts3_webm"><?php _e('WebM (optional)'); ?></label></p>
						<p><input type="text" id="smarts3_webm" placeholder="<?php _e('Enter the URL of your WebM video file', 'smarts3'); ?>"></p>
					</div>
					<div class="form_field">
						<p><label for="smarts3_poster"><?php _e('Splash Image URL (optional)'); ?></label></p>
						<p><input type="text" id="smarts3_poster" placeholder="<?php _e('Enter the URL of your splash image', 'smarts3'); ?>"></p>
					</div>
					<div class="form_field form_field_half">
						<p><label for="smarts3_width"><?php _e('Size (optional)'); ?></label></p>
						<p>
							<input class="smarts3_half" type="text" id="smarts3_width" placeholder="<?php _e('width', 'smarts3'); ?>">
							<input class="smarts3_half" type="text" id="smarts3_height" placeholder="<?php _e('height', 'smarts3'); ?>">
						</p>
					</div>
					<div class="form_field form_field_half">
						<p>
							<label class="smarts3_autoplay" for="smarts3_autoplay">
								<input type="checkbox" id="smarts3_autoplay">
								<?php _e('Autoplay', 'smarts3'); ?>
							</label>
						</p>
					</div>
					<div class="form_field form_field_submit">
						<input type="button" class="button-primary" id="smarts3_submit_form" value="Insert Video" />
						<a class="button" style="color:#aaa;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "smarts3"); ?></a>
					</div>
				</div>
			</div>
		<?php }
	}
}
//Instantiate SmartS3 class
$smarts3 = new SmartS3;

//Hook into WordPress
add_action( 'wp_enqueue_scripts', array( $smarts3, 'enqueue_scripts' ) );
add_action( 'media_buttons_context', array( $smarts3, 'media_buttons' ) );
add_action( 'admin_footer', array( $smarts3, 'shortcode_generator' ) );
add_shortcode( 'smarts3' , array( $smarts3, 'shortcode' ) );
add_filter( 'smarts3_player_options', array( $smarts3, 'Player_Options') );
add_action( 'admin_init', array( $smarts3, 'admin_init' ) );
add_action( 'admin_enqueue_scripts', array( $smarts3, 'admin_pointers_header' ) );