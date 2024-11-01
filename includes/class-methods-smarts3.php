<?php
if ( !class_exists( 'Methods_SmartS3' ) ) {
	class Methods_SmartS3 extends Adapter_AWS {
		/**
		 * Performs necessary instantiation routines
		 */
		public function __construct() {
			$this->cipher = MCRYPT_RIJNDAEL_256;
			$this->mode = MCRYPT_MODE_ECB;
			
			parent::__construct();
		}
		/**
		 * Retrives the specified key type
		 * @param string $type Key type to retrieve
		 * @return string|boolean $key The specified key. False if no key specified
		 */
		public function Get_Keys($type) {
			$options = get_option('smarts3');
			
			if ( $type == 'access' ) {
				return $options['access_key'];
			} elseif ( $type = 'secret' ) {
				return $this->decrypt($options['secret_key']);
			}
			
			return false;
		}
		/**
		 * Parses a URL. Necessary for different URL forms from Amazon S3
		 * @param string $url The URL to parse
		 * @return object $object Object containing bucket and key
		 */
		public function Parse_URL($url) {
			$pieces = explode('/', $url);
			
			if ( $pieces[2] == 's3.amazonaws.com' ) {
				$object = (object) array();
				$object->bucket = $pieces[3];
				unset($pieces[0], $pieces[1], $pieces[2], $pieces[3]);
				$object->key = implode('/', $pieces);
			} else {
				$object = (object) array();
				$more = explode('.', $pieces[2]);
				$object->bucket = $more[0];
				unset($pieces[0], $pieces[1], $pieces[2]);
				$object->key = implode('/', $pieces);
			}
			
			return $object;
		}
		/**
		 * Performs an HTTP request
		 * @param string $url The URL to query
		 * @param string $type Type of request (POST or GET)
		 * @param array $args Array of arguments to pass with the request
		 * @return array|boolean $response Array of response headers. False if no type specified
		 */
		public function _request($url, $type, $args = array()) {
			switch ($type) {
				case 'get':
					$response = wp_remote_get($url, $args);

					break;
				
				case 'post':
					$response = wp_remote_post($url, $args);
					
					break;
				default:
					$response = false;
					
					break;
			}
			
			return $response;
		}
		public function encrypt($value) {
			//Calculate padding for algo
			$block = mcrypt_get_block_size($this->cipher, $this->mode);
			$pad = $block - (strlen($value) % $block);			
			$value .= str_repeat(chr($pad), $pad);
			
			//Set up nonce and salt
			$nonce = AUTH_SALT . NONCE_KEY . NONCE_SALT;
			$salt = substr(md5('save_smarts3_options' . $nonce), 0, 24);
			
			//Encrypt the value
			$encrypted_value = base64_encode(mcrypt_encrypt($this->cipher, $salt, $value, $this->mode));
			
			return $encrypted_value;
		}
		public function decrypt($value) {
			//Set up once and salt
			$nonce = AUTH_SALT . NONCE_KEY . NONCE_SALT;
			$salt = substr(md5('save_smarts3_options' . $nonce), 0, 24);
			
			//Decrypt the value
			$decrypted_value = mcrypt_decrypt($this->cipher, $salt, base64_decode($value), $this->mode);
			
			//Calculate padding
			$block = mcrypt_get_block_size($this->cipher, $this->mode);
			$len = strlen($decrypted_value);
			$pad = ord($decrypted_value[$len-1]);
			
			//Remove the padding
			$decrypted_value = substr($decrypted_value, 0, strlen($decrypted_value) - $pad);
			
			return $decrypted_value;
		}
	}
}