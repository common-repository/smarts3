<?php
use Aws\S3\S3Client;

if ( !class_exists( 'Adapter_AWS' ) ) {
	class Adapter_AWS {
		/**
		 * The Amazon S3 Access Key
		 * @var string $AccessKey
		 */
		var $AccessKey;
		/**
		 * The Amazon S3 SecretKey
		 * @var string $SecretKey
		 */
		var $SecretKey;
		/**
		 * Performs necessary instantiation routines
		 */
		public function __construct() {
			$this->AccessKey = $this->Get_Keys('access');
			$this->SecretKey = $this->Get_Keys('secret');
			
			$this->S3 = S3Client::factory(array(
				'key' => $this->AccessKey,
				'secret' => $this->SecretKey
			));
		}
		/**
		 * Gets an array of buckets associated with the Amazon AWS account
		 * @return array $object['Buckets'] An array of buckets for this account
		 */
		public function GetBuckets() {
			$object = $this->S3->listBuckets();
			
			return $object['Buckets'];
		}
		/**
		 * Gets the contents of the specified bucket
		 * @param string $bucket Name of the bucket to retrieve contents for
		 * @return array $objects An array of objects of bucket contents
		 */
		public function GetBucketContents($bucket) {
			$iterator = $this->S3->getIterator('ListObjects', array(
				'Bucket' => $bucket
			));
			
			foreach ($iterator as $object) {
				$objects[] = $object;
			} 
			
			return $objects;
		}
		/**
		 * Generates a pre-signed URL for the specified object
		 * @param string $bucket The bucket the object resides in
		 * @param string $key The object to generate a signed URL for
		 * @param string $expiration The expiration period e.g. 10 minutes
		 * @return string $signed_url The newly singed URL
		 */
		public function GetSignedURL($bucket, $key, $expiration = '10 minutes') {
			$expiration = '+' . $expiration;
			
			$command = $this->S3->getCommand('GetObject', array(
				'Bucket' => $bucket,
				'Key' => $key
			));
			
			$signed_url = $command->createPresignedUrl($expiration);

			return $signed_url;
		}
	}
}