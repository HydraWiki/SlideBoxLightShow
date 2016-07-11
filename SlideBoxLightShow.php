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

/******************************************/
/* Credits								  */
/******************************************/
$credits = [
	'path'				=> __FILE__,
	'name'				=> 'Slide Box Light Show',
	'author'			=> ['Alexia E. Smith', 'Curse Inc&copy;'],
	'license-name'		=> 'GPL-3.0',
	'descriptionmsg'	=> 'slideboxlightshow_description',
	'version'			=> '1.2'
];
$wgExtensionCredits['parserhook'][] = $credits;

/******************************************/
/* Language Strings, Page Aliases, Hooks  */
/******************************************/
$extDir = __DIR__;

$wgExtensionMessagesFiles['SlideBoxLightShow']	= "{$extDir}/SlideBoxLightShow.i18n.php";
$wgMessagesDirs['SlideBoxLightShow']			= "{$extDir}/i18n";

$wgAutoloadClasses['SlideBoxLightShowHooks']	= "{$extDir}/SlideBoxLightShow.hooks.php";

$wgHooks['ParserFirstCallInit'][]				= 'SlideBoxLightShowHooks::onParserFirstCallInit';

$wgResourceModules['ext.slideboxlightshow'] = [
	'localBasePath' => $extDir,
	'remoteExtPath' => 'SlideBoxLightShow',
	'styles'		=> ['css/slideboxlightshow.css'],
	'scripts'		=> ['js/slideboxlightshow.js', 'js/lightbox.js'],
	'targets'		=> ['desktop', 'mobile']
];

