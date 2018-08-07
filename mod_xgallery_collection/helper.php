<?php
/*
 * @package Joomla 2.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component XGallery Component
 * @copyright Copyright (C) Dana Harris optikool.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */ 
defined('_JEXEC') or die('Restricted access');

class modXGalleryCollectionHelper {
	private $_moduleclass_sfx;
	private $_coll_id;
	private $_cid;
	private $_access_level;
	private $_images_num;
	private $_images_per_row;
	private $_show_quicktake;
	private $_show_description;	
	private $_show_date;
	private $_show_hits;
	private $_group_images;
	private $_enable_css;
	private $_sort;
	private $_show_protected;
	private $_display_layout;
	private $_show_thumbnails;
	private $_coll_info;
	private $_query;
	private $_document;
	private $_data;
	private $_itemid;
	private $_show_tname;
	private $_show_title;
	private $_show_caption;
	private $_coll_ids;
				
	public function __construct(&$params) {		

		$this->_moduleclass_sfx 	= $params->get('moduleclass_sfx');
		$this->_coll_id 		= $params->get('coll_id');
		$this->_access_level 		= $params->get('access_level');
		$this->_images_num 		= $params->get('images_num');
		$this->_images_per_row 		= $params->get('images_per_row');
		$this->_show_quicktake 		= $params->get('show_quicktake');
		$this->_show_description 	= $params->get('show_description');		
		$this->_show_date 		= $params->get('show_date');
		$this->_show_hits 		= $params->get('show_hits');
		$this->_group_images		= $params->get('group_images');
		$this->_sort			= $params->get('sort_method');
		$this->_show_protected		= $params->get('show_protected');
		$this->_display_layout		= $params->get('display_layout');
		$this->_show_thumbnails		= $params->get('show_thumbnails');
		$this->_show_tname		= $params->get('show_tname');
		$this->_show_title		= $params->get('show_title');	
		$this->_coll_info		= $this->_getInfo();
		$this->_document		= JFactory::getDocument();	
		$this->_show_caption		= $params->get('show_caption');	
		$this->_coll_ids 		= implode(',', $params->get('coll_id', array()));			
	}

	public function getList(&$params) {
		$cfgParams =  JComponentHelper::getParams('com_xgallery');
		
		$comMediaImagePath = JComponentHelper::getParams('com_media')->get('image_path', 'images');
		$galleryThumbFolder = $cfgParams->get('image_path_thumb');
		$galleryCollectionFolder = $cfgParams->get('image_path');
		$galleryPathIsExternal = $cfgParams->get('image_external');
		$galleryBasePathThumbnail = JPATH_ROOT;
			
		if($galleryPathIsExternal) {
			$galleryBasePathCollection = $cfgParams->get('image_external_path');
		} else {
			$galleryBasePathCollection = $galleryBasePathThumbnail . DS . $comMediaImagePath;
		}
		
		$galleryBasePathThumbnailFull = $galleryBasePathThumbnail . DS . $comMediaImagePath . DS . $galleryThumbFolder;
		$galleryBasePathCollectionFull = $galleryBasePathCollection . DS . $galleryCollectionFolder;
		$gallerySitePathFull = JURI::base(true) . DS . $comMediaImagePath . DS . $galleryCollectionFolder;
				
		$cookieParams = GalleryHelper::getCookieParams();		
		$config = JFactory::getConfig();
		
		$bpath = $galleryBasePathCollectionFull;
		
		$path = $bpath.DS.$this->_coll_info[0]->folder.DS;
		$dirfiles = array();
		
		if(!is_dir($path)) {
			JError::raiseWarning(500, JText::_('MOD_XGALLERY_COLLECTION_DIR_NOT_FOUND'));
			return $dirfiles;
		}
		
		$iterator = new DirectoryIterator($path);
		
				
		foreach($iterator as $file) {
			if(!$file->isDot() && $file->isFile()) {
				$tempPath = $bpath.DS.$this->_coll_info[0]->folder.DS.$file->getFilename();
				if($helper->isImage($tempPath)) {
					$dirfiles[] = DS.$this->_coll_info[0]->folder.DS.$file->getFilename();
				}
			}
		}

		switch($this->_sort) {
			case 'random':
				shuffle($dirfiles);
				break;
			default:
				sort($dirfiles);
				break;
		}
		
		return $dirfiles;
	}
			
	private function _getInfo() {
		$db = JFactory::getDBO();
		
		if(!$this->_query) {
			$this->_query = $this->_buildQuery();
		} else {
			$this->_query = $this->_query;
		}
		
		$db->setQuery($this->_query, 0, 1);
		if(!$rows = $db->loadObject()) {
			$rows = array();
		}
		
		if(!empty($rows)) {
			$this->_data = $rows;		
			$this->_cid = $this->_data->cid;
		}
		
		return $rows;
	}
	
	public function getCollInfo() {
		$itemids = GalleryHelper::getItemIds();
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$sort_method = $this->_sort;
		
		// Create a new query object.
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		// Select required fields from the categories.
		$query->select('a.*');
		$query->from($db->quoteName('#__xgallery').' AS a');
		
		// Join over the categories.
		$query->select('c.alias AS catalias, c.title AS cattitle');
		$query->join('LEFT', $db->quoteName('#__categories') . ' AS c ON c.id = a.catid');
		
		$query->where('a.access IN ('.$groups.')');				
		$query->where('a.published = 1');

		$idsArray = preg_split("/,/", $this->_coll_ids);
		if(count($idsArray) > 0) {				
			$query->where('catid IN ('.$this->_coll_ids.')');
		}
		
		switch($sort_method) {
			case 'random':
				$sort_method= "RAND()";
				break;
			case 'newest':
				$sort_method= "date DESC";
				break;
			case 'oldest':
				$sort_method= "date ASC";
				break;
			case 'popular':
				$sort_method= "hits DESC";
				break;	
			default:
				$sort_method = "title DESC";
				break;			
		}
			
		$query->order($sort_method);
				
		$db->setQuery($query, 0, 1);
		$this->_coll_info = $db->loadObject();
		$user = JFactory::getUser($this->_coll_info->user_id);			
		$this->_coll_info->submitter = $user->username;
		$itemid = $this->_coll_info->catid;
        $this->_coll_info->itemId = $itemids[$itemid]['itemId'];
			
		return $this->_coll_info;
	}
	
	public function getImages() {
		$cookieParams = GalleryHelper::getCookieParams();
		
		$config = JFactory::getConfig();
		
		$cfgParams =  JComponentHelper::getParams('com_xgallery');
		
		$comMediaImagePath = JComponentHelper::getParams('com_media')->get('image_path', 'images');
		$galleryThumbFolder = $cfgParams->get('image_path_thumb');
		$galleryCollectionFolder = $cfgParams->get('image_path');
		$galleryPathIsExternal = $cfgParams->get('image_external');
		$galleryBasePathThumbnail = JPATH_ROOT;
			
		if($galleryPathIsExternal) {
			$galleryBasePathCollection = $cfgParams->get('image_external_path');
		} else {
			$galleryBasePathCollection = $galleryBasePathThumbnail . DS . $comMediaImagePath;
		}
		
		$galleryBasePathThumbnailFull = $galleryBasePathThumbnail . DS . $comMediaImagePath . DS . $galleryThumbFolder;
		$galleryBasePathCollectionFull = $galleryBasePathCollection . DS . $galleryCollectionFolder;
		$gallerySitePathFull = JURI::base(true) . DS . $comMediaImagePath . DS . $galleryCollectionFolder;
		
  		$bpath = $galleryBasePathCollectionFull;
		
		$path = $bpath.DS.$this->_coll_info->folder.DS;
		$iterator = new DirectoryIterator($path);
		$dirfiles = array();
				
		foreach($iterator as $file) {
			if(!$file->isDot() && $file->isFile()) {
				$tempPath = $bpath.DS.$this->_coll_info->folder.DS.$file->getFilename();
				if(GalleryHelper::isImage($tempPath)) {
					$dirfiles[] = DS.$this->_coll_info->folder.DS.$file->getFilename();
				}
			}
		}
		
		sort($dirfiles);		

		switch($this->_sort) {
			case 'reverse':
				rsort($dirfiles);
				break;
			case 'random':
				shuffle($dirfiles);
				break;
			default:
				sort($dirfiles);
				break;
		}
		
		if(count($dirfiles) < $this->_images_num) {
			$this->_images_num = count($dirfiles);
		}
		
		for ($count = 0; $count < $this->_images_num; $count++) {			
				$displayImages[] = $dirfiles[$count];
		}
		
		return $displayImages;
	}

	public function getTags() {
		$this->_item->tags = new JHelperTags;
        $this->_item->tags->getItemTags('com_xgallery.collection' , $this->_item->id);
	}
		
	private function _buildQuery() {
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$sort_method = $this->_sort;
		
		// Create a new query object.
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		
		// Select required fields from the categories.
		$query->select('a.*');
		$query->from($db->quoteName('#__xgallery').' AS a');
		
		// Join over the categories.
		$query->select('c.alias AS catalias, c.title AS cattitle');
		$query->join('LEFT', $db->quoteName('#__categories') . ' AS c ON c.id = a.catid');
		
		$query->where('a.access IN ('.$groups.')');				
		$query->where('a.published = 1');
		
		switch($sort_method) {
			case 'random':
				$sort_method= "RAND()";
				break;	
			case 'default':
			default:
				$sort_method = "title DESC";
				break;			
		}
			
		$query->order($sort_method);

		return $query;
	}
	
	public function getGallerific() {
		$cfgParams =  JComponentHelper::getParams('com_xgallery');
  		  		
  		$width = $cfgParams->get('coll_width');
		$height = $cfgParams->get('coll_height');
  		
		$this->_document->addScript( JURI::base(true).'/components/com_xgallery/js/jquery.galleriffic.js' );
		$this->_document->addScript( JURI::base(true).'/components/com_xgallery/js/jquery.opacityrollover.js' );
		$this->_document->addScript( JURI::base(true).'/components/com_xgallery/js/jush.js' );
			
		if((bool)$cfgParams->get('galleriffic_enableHistory')) {
			$this->_document->addScript( JURI::root() . '/components/com_xgallery/js/jquery.history.js' );
		}
			
		$this->_document->addStyleSheet(JURI::base(true).'/components/com_xgallery/css/style.css');
		$this->_document->addStyleSheet(JURI::base(true).'/components/com_xgallery/css/basic.css');			
		$this->_document->addStyleSheet(JURI::base(true).'/components/com_xgallery/css/galleriffic-2.css');			
			
		$enableTopPager = ((bool)$cfgParams->get('galleriffic_enableTopPager')) ? 'true' : 'false';
		$enableBottomPager = ((bool)$cfgParams->get('galleriffic_enableBottomPager')) ? 'true' : 'false';
		$renderSSControls = ((bool)$cfgParams->get('galleriffic_renderSSControls')) ? 'true' : 'false';
		$renderNavControls = ((bool)$cfgParams->get('galleriffic_renderNavControls')) ? 'true' : 'false';
		$enableHistory = ($cfgParams->get('galleriffic_enableHistory')) ? 'true' : 'false';
		$autoStart = ($cfgParams->get('galleriffic_autoStart')) ? 'true' : 'false';
		$syncTransitions = ((bool)$cfgParams->get('galleriffic_syncTransitions')) ? 'true' : 'false';
		$jsScript = "
			jQuery(document).ready(function($) {
			// We only want these styles applied when javascript is enabled
			$('div.navigation').css({'width' : '".(int)$cfgParams->get('galleriffic_navwidth')."px'});
			$('div.content').css('display', 'block');

			// Initially set opacity on thumbs and add
			// additional styling for hover effect on thumbs
			var onMouseOutOpacity = 0.67;
			$('#thumbs ul.thumbs li').opacityrollover({
				mouseOutOpacity:   onMouseOutOpacity,
				mouseOverOpacity:  1.0,
				fadeSpeed:         'fast',
				exemptionSelector: '.selected'
			});
				
			// Initialize Advanced Galleriffic Gallery
			var gallery = $('#thumbs').galleriffic({
				delay:                     ".(int)$cfgParams->get('galleriffic_delay').",
				numThumbs:                 ".(int)$cfgParams->get('galleriffic_numthumbs').",
				preloadAhead:              ".(int)$cfgParams->get('galleriffic_preloadAhead').",
				enableTopPager:            ".$enableTopPager.",
				enableBottomPager:         ".$enableBottomPager.",
				maxPagesToShow:            ".(int)$cfgParams->get('galleriffic_maxPagesToShow').",
				imageContainerSel:         '#slideshow',
				controlsContainerSel:      '#controls',
				captionContainerSel:       '#caption',
				loadingContainerSel:       '#loading',
				renderSSControls:          ".$renderSSControls.",
				renderNavControls:         ".$renderNavControls.",
				playLinkText:              '".JTEXT::_('MOD_XGALLERY_COLLECTION_PLAY_LINK_TEXT_MOD')."',
				pauseLinkText:             '".JTEXT::_('MOD_XGALLERY_COLLECTION_PAUSE_LINK_TEXT_MOD')."',
				prevLinkText:              '".JTEXT::_('MOD_XGALLERY_COLLECTION_PREV_LINK_TEXT_MOD')."',
				nextLinkText:              '".JTEXT::_('MOD_XGALLERY_COLLECTION_NEXT_LINK_TEXT_MOD')."',
				nextPageLinkText:          '".JTEXT::_('MOD_XGALLERY_COLLECTION_NEXT_PAGE_LINK_TEXT_MOD')."',
				prevPageLinkText:          '".JTEXT::_('MOD_XGALLERY_COLLECTION_PREV_PAGE_LINK_TEXT_MOD')."',
				enableHistory:             ".$enableHistory.",
				autoStart:                 ".$autoStart.",
				syncTransitions:           ".$syncTransitions.",
				defaultTransitionDuration: ".(int)$cfgParams->get('galleriffic_defaultTransitionDuration').",
				onSlideChange:             function(prevIndex, nextIndex) {
					// 'this' refers to the gallery, which is an extension of $('#thumbs')
					this.find('ul.thumbs').children()
						.eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
						.eq(nextIndex).fadeTo('fast', 1.0);
				},
				onPageTransitionOut:       function(callback) {
					this.fadeTo('fast', 0.0, callback);
				},
				onPageTransitionIn:        function() {
					this.fadeTo('fast', 1.0);
				}
			});
			});";
			
		$this->_document->addScriptDeclaration($jsScript);	
			
		$cssStyle = "
			.noscript { display: none; }
			
			#thumbContainer img.thumbs {
				height:{$height}px;
				width:{$width}px; 
			}
		";
			
		$this->_document->addStyleDeclaration($cssStyle);
	}
}	
?>
