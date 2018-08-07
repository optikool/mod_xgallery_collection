<?php
/*
 * @package Joomla 3.0
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component XMovie Component
 * @copyright Copyright (C) Dana Harris optikool.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */ 
defined('_JEXEC') or die('Restricted access');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

require_once (JPATH_SITE.DS.'components'.DS.'com_xgallery'.DS.'router.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_xgallery'.DS.'helpers'.DS.'gallery.php');

GalleryHelper::setCookieParams();

$document = JFactory::getDocument();


$list = new modXGalleryCollectionHelper($params);

$collInfo = $list->getCollInfo();
$collImages = $list->getImages();
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$layout = $params->get('layout', 'default');
$layouts = explode(':', $layout);

require JModuleHelper::getLayoutPath('mod_xgallery_collection', $layout);
