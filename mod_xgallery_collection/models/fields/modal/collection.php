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

jimport('joomla.form.formfield');

class JFormFieldCollection extends JFormField {
	protected $type = 'Collection';

	protected function getInput() {
		$db =& JFactory::getDBO();
		
		$query = 'SELECT id, title FROM #__xgallery WHERE published=1 ORDER BY title';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		
		$first[] = new stdClass;
		$first[0]->id = 0;
		$first[0]->title = JTEXT::_('MOD_XGALLERY_COLLECTION_SELECT_ALL');
		
		$moptions = array_merge($first, $options);
					
		return JHtmlSelect::genericlist($moptions, $this->name, 'class="inputbox"', 'id', 'title', $this->value);
	}
}