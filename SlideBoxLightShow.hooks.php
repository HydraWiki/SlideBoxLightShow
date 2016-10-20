<?php
/**
 * Curse Inc.
 * Slide Box Light Show
 * Slide Box Light Show Hooks
 *
 * @author		Alex Smith
 * @copyright	(c) 2014 Curse Inc.
 * @license		GPL v3.0
 * @package		Slide Box Light Show
 * @link		https://github.com/HydraWiki/SlideBoxLightShow
 *
 **/

class SlideBoxLightShowHooks {
	/**
	 * Hooks Initialized
	 *
	 * @var		boolean
	 */
	private static $initialized = false;

	/**
	 * Existing MD5 Set Identifications
	 *
	 * @var		array
	 */
	private static $md5Sets = [];

	/**
	 * Default Arguments
	 *
	 * @var		array
	 */
	private static $defaultArguments = [
		'sequence'			=> 'forward',
		'transition'		=> 'fade',
		'transitionspeed'	=> 500,
		'halign'			=> 'center',
		'valign'			=> 'middle',
		'interval'			=> 5000,
		'width'				=> null,
		'height'			=> null,
		'popup'				=> true,
		'slideshowonly'		=> false,
		'carousel'			=> false
	];

	/**
	 * Initiates some needed classes.
	 *
	 * @access	public
	 * @return	void
	 */
	static public function init() {
		global $sbDefaultArguments;
		if (!self::$initialized) {
			define('SB_EXT_DIR', dirname(__FILE__));

			if (is_array($sbDefaultArguments) && count($sbDefaultArguments)) {
				self::$defaultArguments = array_merge(self::$defaultArguments, $sbDefaultArguments);
			}

			self::$initialized = true;
		}
	}

	/**
	 * Sets up this extensions parser functions.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @return	boolean	true
	 */
	static public function onParserFirstCallInit(Parser &$parser) {
		self::init();

		$parser->setHook('slideBoxLightShow', 'SlideBoxLightShowHooks::slideBoxLightShow');

		return true;
	}

	/**
	 * Generates and returns a slide box light show.
	 *
	 * @access	public
	 * @param	string	Content between opening and closing tags.
	 * @param	array	Array of arguments passed to tag.
	 * @param	object	Parser Object
	 * @param	object	PPFrame Object
	 * @return	string	Wiki Text
	 */
	static public function slideBoxLightShow($items, array $arguments, Parser $parser, PPFrame $frame) {
		global $wgScriptPath;
		self::init();

		$parser->getOutput()->addModules('ext.slideboxlightshow');

		$fileHTML = '';

		$arguments = self::sanitizeArguments($arguments);

		$md5Set = self::calculateSetMD5($items);

		$items = self::parseItems($items);

		$files = [];
		$heights = [];
		$widths = [];
		foreach ($items as $key => $info) {
			if ($arguments['carousel']) {
				$files[] = ['html' => $parser->recursiveTagParse($info)];
			} else {
				$info = explode('|', $info);
				$description = $info[1];
				$link = $info[2];

				if ($link) {
					$_title = Title::newFromText($link);
					if (filter_var($link, FILTER_VALIDATE_URL)) {
						$link = $link;
					} elseif (!empty($_title) && $_title->isKnown()) {
						$link = $_title->getFullURL();
					} else {
						$link = false;
					}
					$arguments['popup'] = ($link !== false ? false : $arguments['popup']);
				} else {
					$link = false;
				}

				$_file = trim($info[0]);
				$_file = wfFindFile(Title::newFromText($_file));

				if (is_object($_file) && $_file->exists()) {
					$thumbHeight = null;
					$thumbWidth = null;
					if (!$arguments['height']) {
						$thumbHeight = $_file->getHeight();
					} else {
						$thumbHeight = $arguments['height'];
					}
					if (!$arguments['width']) {
						$thumbWidth = $_file->getWidth();
					} else {
						$thumbWidth = $arguments['width'];
					}
					$fileThumb = $_file->transform(['width' => $thumbWidth, 'height' => $thumbHeight]);
					if ($fileThumb === false || ($fileThumb->getHeight() > $_file->getHeight() && $fileThumb->getWidth() > $_file->getWidth())) {
						unset($fileThumb);
						$fileThumb = $_file;
					}
					$heights[] = $fileThumb->getHeight();
					$widths[] = $fileThumb->getWidth();
					$files[] = ['full' => $_file, 'thumb' => $fileThumb, 'description' => $description, 'link' => $link];
				}
			}
		}
		if ($arguments['height'] > 0) {
			$heights[] = $arguments['height'];
		}
		if ($arguments['width'] > 0) {
			$widths[] = $arguments['width'];
		}
		if (!$arguments['valign']) {
			$arguments['valign'] = 'middle';
		}

		if (!empty($heights)) {
			$boxHeight = max($heights);
		} else {
			$boxHeight = 0;
		}

		if (!empty($widths)) {
			$boxWidth = max($widths);
		} else {
			$boxWidth = 0;
		}

		if ($arguments['carousel'] || $arguments['slideshowonly']) {
			$fileHTML .= "
				<div class='sbls-carousel'>";
		}
		$fileHTML .= "<div id='sbls-{$md5Set}' class='slideboxlightshow' data-sequence='{$arguments['sequence']}' data-transition='{$arguments['transition']}' data-transitionspeed='{$arguments['transitionspeed']}' data-interval='{$arguments['interval']}' style='width: {$boxWidth}px; height: {$boxHeight}px; line-height: {$boxHeight}px;'>";
		foreach ($files as $file) {
			if ($arguments['carousel']) {
				$fileHTML .= "
						<div id='{$md5Set}-".md5($file['html'])."' class='sbls-image' style='height: {$boxHeight}px; width: {$boxWidth}px; text-align: {$arguments['halign']}; vertical-align: {$arguments['valign']};'>{$file['html']}</div>";
			} elseif ($arguments['slideshowonly']) {
				$fileHTML .= "
						<div id='{$md5Set}-".md5($file['thumb']->getUrl())."' class='sbls-image' style='height: {$boxHeight}px; width: {$boxWidth}px; text-align: {$arguments['halign']}; vertical-align: {$arguments['valign']};'><img src='{$file['thumb']->getUrl()}'/>";
				if (!empty($file['description'])) {
					$fileHTML .= "
							<a href='".($file['link'] !== false ? $file['link'] : $file['full']->getUrl())."'><span class='sbls-description'>".$file['description']."</span></a>";
				}
				$fileHTML .= "
						</div>";
			} else {
				$fileHTML .= "<a id='{$md5Set}-".md5($file['thumb']->getUrl())."' href='".($file['link'] !== false ? $file['link'] : $file['full']->getUrl())."' ".($arguments['popup'] ? "data-lightbox='sbls-{$md5Set}-set' " : null)."class='sbls-image' style='height: {$boxHeight}px; width: {$boxWidth}px; text-align: {$arguments['halign']}; vertical-align: {$arguments['valign']};'><img src='{$file['thumb']->getUrl()}'/>";
				if (!empty($file['description'])) {
					$fileHTML .= "<span class='sbls-description'>".$file['description']."</span>";
				}
				$fileHTML .= "</a>";
			}
		}
		$fileHTML .= "
					</div>";
		if ($arguments['carousel'] || $arguments['slideshowonly']) {
			$fileHTML .= "
					<div class='sbls-nav'><a class='sbls-prev'>←</a><a class='sbls-next'>→</a></div>
			</div>";
		}

		if (MW_API === true) {
			$fileHTML .= "<script type='text/javascript'>mw.loader.load('ext.slideboxlightshow');</script>";
		}

		return $fileHTML;
	}

	/**
	 * Parse the raw new line separated item list into an array.
	 *
	 * @access	private
	 * @param	string	Raw item text.
	 * @return	array	Parsed item text.
	 */
	static private function parseItems($items) {
		$items = str_replace("\r\n", "\n", $items);
		$items = trim($items, "\n");
		$items = explode("\n", $items);

		return $items;
	}

	/**
	 * Calculate the MD5 of the set based on the raw items text.
	 *
	 * @access	private
	 * @param	string	Raw item text.
	 * @return	string	32 character MD5
	 */
	static private function calculateSetMD5($items) {
		$md5Set = md5($items);
		$i = false;
		$_md5Set = $md5Set;
		while (in_array($_md5Set, self::$md5Sets)) {
			$_md5Set = $md5Set;
			$i++;
			$_md5Set .= '-'.$i;
		}
		if ($i !== false) {
			$md5Set = $_md5Set;
		}
		self::$md5Sets[] = $md5Set;

		return $md5Set;
	}

	/**
	 * Sanitize tag arguments.
	 *
	 * @access	private
	 * @param	array	Array of optional arguments.
	 * @return	array	Sanitized arguments.
	 */
	static private function sanitizeArguments($arguments) {
		$validArguments = self::$defaultArguments;

		if (is_array($arguments)) {
			foreach ($arguments as $key => $value) {
				switch (strtolower($key)) {
					case 'sequence':
						if (in_array($value, ['forward', 'reverse', 'random'])) {
							$validArguments['sequence'] = $value;
						}
						break;
					case 'transition':
						if (in_array($value, ['fade', 'left', 'right', 'up', 'down'])) {
							$validArguments['transition'] = $value;
						}
						break;
					case 'transitionspeed':
						$validArguments['transitionspeed'] = intval($value);
						break;
					case 'halign':
						if (in_array($value, ['left', 'center', 'right'])) {
							$validArguments['halign'] = $value;
						}
						break;
					case 'valign':
						if (in_array($value, ['top', 'middle', 'bottom'])) {
							$validArguments['valign'] = $value;
						}
						break;
					case 'interval':
						$validArguments['interval'] = intval($value);
						break;
					case 'width':
						$validArguments['width'] = intval($value);
						break;
					case 'height':
						$validArguments['height'] = intval($value);
						break;
					case 'popup':
						$validArguments['popup'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
						break;
					case 'slideshowonly':
						$validArguments['slideshowonly'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
						$validArguments['popup'] = false;
						break;
					case 'carousel':
						$validArguments['carousel'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
						$validArguments['popup'] = false;
						$validArguments['slideshowonly'] = true;
						break;
				}
			}
		}

		return $validArguments;
	}
}
