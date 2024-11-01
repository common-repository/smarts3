=== Plugin Name ===
Contributors: johnmorris10
Donate link: http:johnmorrisonline.com
Tags: amazon s3, video, flowplayer
Requires at least: 3.5.1
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

SmartS3 is a simple video plugin that lets you easily embed Amazon S3 videos into your WordPress blog.

== Description ==

SmartS3 is a simple video plugin that lets you easily embed Amazon S3 videos into your WordPress blog. The current version
supports:

* Signed, expiring URLs
* Flowplayer video player
* Flowplayer commercial license
* HTML5 video support (via FlowPlayer)
* Responsive video display
* Flash fallback
* MP4, OGG, and WebM video formats
* Shortcode generator for easy embedding
* Hooks for custom JavaScript variables

Keep in mind, this is an initial beta version meant for development and testing. This version is not yet suitable
for production sites. Please send any bug reports or feature requests to @jpmorris on Twitter.

== Installation ==

1. Upload the smarts3 folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the `Add S3 Video` button on the edit post screen to embed a video

== Frequently Asked Questions ==

= How do I add a custom JavaScript variable? =

`add_filter( 'smarts3_player_options', 'my_custom_js_function' );

function my_custom_js_function($options) {
	$options['variable_name_here'] = 'variable value here';

	return $options;
}`

== Screenshots ==

== Changelog ==

= 0.0.1 =
* Initial commit