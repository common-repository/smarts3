<?php
if ( !class_exists( 'SmartS3_Admin' ) ) {
	class SmartS3_Admin extends Player_SmartS3 {
		/**
		 * Performs necessary instantiation routines
		 */
		public function __construct() {
			$this->version = '0_0_1_1';
			
			parent::__construct();
		}
		/**
		 * Performs the admin init routines
		 */
		public function admin_init() {
			register_setting( 'media', 'smarts3', array( $this, 'sanitize' ) );
			add_settings_section( 'smarts3_media_settings', 'SmartS3 Settings', array( $this, 'media_settings' ), 'media' );
		}
		/**
		 * Settings fields for options-media.php
		 */
		public function media_settings() {
			$options = get_option('smarts3'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="smarts3_access_key"><?php _e('Amazon S3 Access Key', 'smarts3'); ?></label></th>
					<td>
						<input name="smarts3[access_key]" type="text" id="smarts3_access_key" value="<?php echo esc_attr($options['access_key']); ?>" class="regular-text" />
						<p class="description"><?php _e('Enter your Amazon S3 Access Key', 'smarts3'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="smarts3_secret_key"><?php _e('Amazon S3 Secret Key', 'smarts3'); ?></label></th>
					<td>
						<input name="smarts3[secret_key]" type="password" id="smarts3_secret_key" value="<?php echo esc_attr($this->decrypt($options['secret_key'])); ?>" class="regular-text" />
						<p class="description"><?php _e('Enter your Amazon S3 Secret Key', 'smarts3'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="smarts3_expiration"><?php _e('Expiration Period', 'smarts3'); ?></label></th>
					<td>
						<input name="smarts3[expiration]" type="text" id="smarts3_expiration" value="<?php echo esc_attr($options['expiration']); ?>" class="regular-text" />
						<p class="description"><?php _e('Enter the expiration period. e.g. 10 minutes (optional)', 'smarts3'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="smarts3_fp_key"><?php _e('FlowPlayer License Key', 'smarts3'); ?></label></th>
					<td>
						<input name="smarts3[fp_key]" type="text" id="smarts3_fp_key" value="<?php echo esc_attr($options['fp_key']); ?>" class="regular-text" />
						<p class="description"><?php _e('Enter your FlowPlayer license key (optional)', 'smarts3'); ?></p>
					</td>
				</tr>
			</table>
		<?php }
		/**
		 * Header for admin pointers
		 */
		public function admin_pointers_header() {
			if ( $this->admin_pointers_check() ) {
				add_action( 'admin_print_footer_scripts', array( $this, 'admin_pointers_footer' ) );

				wp_enqueue_script( 'wp-pointer' );
				wp_enqueue_style( 'wp-pointer' );
			}
		}
		/**
		 * Checks if any admin pointers exist
		 * @return boolean True if a pointer exists
		 */
		public function admin_pointers_check() {
			$admin_pointers = $this->admin_pointers();
			
			foreach ( $admin_pointers as $pointer => $array ) {
				if ( $array['active'] )
					return true;
			}
		}
		/**
		 * Footer for admin pointers
		 */
		public function admin_pointers_footer() {
			$admin_pointers = $this->admin_pointers(); ?>
			<script type="text/javascript">
			/* <![CDATA[ */
			( function($) {
			<?php
			foreach ( $admin_pointers as $pointer => $array ) {
			   if ( $array['active'] ) {
				  ?>
				  $( '<?php echo $array['anchor_id']; ?>' ).pointer( {
					 content: '<?php echo $array['content']; ?>',
					 position: {
					 edge: '<?php echo $array['edge']; ?>',
					 align: '<?php echo $array['align']; ?>'
				  },
					 close: function() {
						$.post( ajaxurl, {
						   pointer: '<?php echo $pointer; ?>',
						   action: 'dismiss-wp-pointer'
						} );
					 }
				  } ).pointer( 'open' );
				  <?php
			   }
			}
			?>
			} )(jQuery);
			/* ]]> */
			</script>
			<?php
		}
		/**
		 * Builds the individual admin pointers
		 * @return array $pointers Array of pointers
		 */
		function admin_pointers() {
			$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
			$prefix = 'smarts3_admin_pointers' . $this->version . '_';

			$new_pointer_content = '<h3>' . __( 'Set Up SmartS3' ) . '</h3>';
			$new_pointer_content .= '<p>' . __( 'Go to <a href="' . admin_url('options-media.php') . '">Settings > Media</a> and enter your Amazon S3 credentials in order to start using SmartS3.' ) . '</p>';

			return array(
				$prefix . 'smarts3_default' => array(
					'content' => $new_pointer_content,
					'anchor_id' => '#menu-settings',
					'edge' => 'bottom',
					'align' => 'left',
					'active' => ( ! in_array( $prefix . 'smarts3_default', $dismissed ) )
				),
			);
		}
		public function sanitize($options) {
			$secret = $options['secret_key'];
			$options['secret_key'] = $this->encrypt($secret);
			
			return $options;
		}
	}
}