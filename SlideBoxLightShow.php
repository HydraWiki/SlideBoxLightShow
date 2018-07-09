<?php
/**
 * Curse Inc.
 * Slide Box Light Show
 * Slide Box Light Show Mediawiki Settings
 *
 * @author		Alexia E. Smith
 * @copyright	(c) 2014 Curse Inc.
 * @license		GPL v3.0
 * @package		Slide Box Light Show
 * @link		https://github.com/HydraWiki/SlideBoxLightShow
 *
 **/

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SlideBoxLightShow' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['SlideBoxLightShow'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for SlideBoxLightShow extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
 } else {
	die( 'This version of the SlideBoxLightShow extension requires MediaWiki 1.25+' );
}
