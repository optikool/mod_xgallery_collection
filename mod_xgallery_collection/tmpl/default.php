<?php
/*
 * @package Joomla 3.0
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component XGallery Component
 * @copyright Copyright (C) Dana Harris optikool.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die ('Restricted access.');

$currRow = 1;
$itemCount = 1;
$collCount = count($collImages);
$shadowb = 'lightbox';
$collImgPerRow = $params->get('images_per_row', 4);
$span = $params->get('bootstrap_size', 0);
$span_rounded = round(12 / intval($collImgPerRow));

if($span == 0) {
	$span = '';
} else {
	$span = "span{$span}";
}

if($collCount > 0) {
	$showTitle = $params->get('show_title');

	if($showTitle) {
		if($params->get('show_title')) { ?>
			<h4><?php echo htmlspecialchars($collInfo->title); ?></h4>
		<?php }
	}

	if ($collImgPerRow > 1) {
		$listDirection = 'inline';
	} else {
		$listDirection = 'unstyled';
	}
	if($params->get('show_hits') || $params->get('show_date') || $params->get('show_submitter')) { ?>
		<ul class="list <?php echo $listDirection; ?>">
			<?php if($params->get('show_date')) {?>
			<li class="image-date">
				<strong><?php echo JTEXT::_('MOD_XGALLERY_COLLECTION_DATE_ADDED'); ?>:</strong> <?php echo JHTML::Date($collInfo->creation_date, 'm-d-Y'); ?>
			</li>
			<?php } ?>
			
			<?php if($params->get('show_hits')) {?>
			<li class="image-hits">
				<strong><?php echo JTEXT::_('MOD_XGALLERY_COLLECTION_HITS'); ?>:</strong> <?php echo $collInfo->hits; ?>
			</li>
			<?php } ?>
	
			<?php if($params->get('show_submitter')) {?>
			<li class="image-submitter">
				<strong><?php echo JTEXT::_('MOD_XGALLERY_COLLECTION_SUBMITTER'); ?>:</strong> <?php echo $collInfo->submitter; ?>
			</li>
			<?php } ?>
			<?php if($params->get('show_tags')) {?>
			<li class="image-tags">
				<?php
				$collInfo->tags = new JHelperTags;
				$collInfo->tags->getItemTags('com_xgallery.collection' , $collInfo->id);
				$collInfo->tagLayout = new JLayoutFile('joomla.content.tags');
				?>
				<strong><?php echo JTEXT::_('MOD_XGALLERY_COLLECTION_TAGS'); ?>:</strong> <?php echo $collInfo->tagLayout->render($collInfo->tags->itemTags); ?>
			</li>
			<?php } ?>
		</ul>
		<?php 
	}

	if($params->get('show_quicktake')) { ?>	
		<div class="row-fluid"><?php echo $collInfo->introtext; ?></div>
	<?php }
		
	if($params->get('show_description')) { ?>
		<div class="row-fluid"><?php echo $collInfo->fulltext; ?></div>
	<?php }

	if($params->get('group_images')) {
		$shadowb .= '[' . $collInfo->title . ']';
	}
	
	foreach($collImages as $collImage) {
		if($currRow == 1) {
		?>
		<div class="row-fluid <?php echo $span;?> <?php echo $params->get('moduleclass_sfx'); ?>">
		<?php
		}
		?>
		<div class="image-item span<?php echo $span_rounded; ?>">
			<?php 
			$collLink = "file=".$collImage."&amp;tn=0";		
			$collString = "file=".$collImage."&amp;tn=g";

			if($params->get('show_thumbnails')) {
			?>
			<a href="<?php echo JURI::base(true); ?>/components/com_xgallery/helpers/img.php?<?php echo $collLink; ?>" rel="<?php echo $shadowb; ?>;player=img" title="<?php echo htmlspecialchars($collInfo->title); ?>" <?php if($params->get('show_caption')) { echo 'rev="' . htmlspecialchars($collInfo->introtext) . '"'; } ?>>					
				<img class="thumbnail img-responsive" src="<?php echo JURI::base(true); ?>/components/com_xgallery/helpers/img.php?<?php echo $collString; ?>" alt="<?php echo htmlspecialchars($collInfo->title); ?>" />
			</a>	
			<?php } ?>
			<?php if($params->get('show_tname')) { 
				$imgName = pathinfo($collImage);
				$items = array('/\_/', '/\-/');
				$thmName = preg_replace($items, ' ', $imgName['filename']);
			?>
			<p class="caption">
				<?php if($params->get('show_thumbnails')) {
				 		echo ucwords($thmName);
					} else { ?>
						<a href="<?php echo JURI::base(true); ?>/components/com_xgallery/helpers/img.php?<?php echo $collLink; ?>" rel="<?php echo $shadowb; ?>;player=img" title="<?php echo htmlspecialchars($collInfo->title); ?>" <?php if($params->get('show_caption')) { echo 'rev="' . htmlspecialchars($collInfo->introtext) . '"'; } ?>>					
					<?php echo ucwords($thmName); ?>
						</a>
					<?php
					} ?>
			</p>
			<?php } ?>				
		</div>
	<?php 
		if($currRow < $collImgPerRow && $itemCount != $collCount) {
			$currRow++;
		} else {
			echo '<div class="clearfix"></div>';
			echo '</div>';
			$currRow = 1;
		}
		$itemCount++;			
	}
}
?>


<?php /*
<div class="xgallery-collection<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php if($params->get('show_title')) {?>
	<h3><?php echo htmlspecialchars($collInfo->title); ?></h3>
	<?php } ?>	
	<?php if($params->get('show_hits') || $params->get('show_date') || $params->get('show_submitter')) {?>
	<ul class="list list-horizontal">
		<?php if($params->get('show_date')) {?>
		<li class="image-date">
			<strong><?php echo JTEXT::_('MOD_XGALLERY_COLLECTION_DATE_ADDED'); ?>:</strong> <?php echo JHTML::Date($collInfo->creation_date, 'm-d-Y'); ?>
		</li>
		<?php } ?>
		
		<?php if($params->get('show_hits')) {?>
		<li class="image-hits">
			<strong><?php echo JTEXT::_('MOD_XGALLERY_COLLECTION_HITS'); ?>:</strong> <?php echo $collInfo->hits; ?>
		</li>
		<?php } ?>

		<?php if($params->get('show_submitter')) {?>
        <li class="image-hits">
        	<strong><?php echo JTEXT::_('MOD_XGALLERY_COLLECTION_SUBMITTER'); ?>:</strong> <?php echo $collInfo->submitter; ?>
        </li>
        <?php } ?>
	</ul>
	<?php } ?>	
	
	<?php if($params->get('show_quicktake')) {	
		echo $collInfo->introtext;
	} ?>
		
	<?php if($params->get('show_description')) {
		echo $collInfo->fulltext;
	} ?>
		<div class="xgallery-collection-container">		
		<?php if(count($collImages) > 0) {
			if($params->get('images_per_row') != '' || $params->get('images_per_row') == 0) {
				$width = 'style="width:' . $width . '%;"';
			}
			
			if($params->get('group_images')) {
				$shadowb .= '[' . $collInfo->title . ']';
			}
			foreach($collImages as $collImage) { 
				$collLink = "file=".$collImage."&amp;tn=0";		
				$collString = "file=".$collImage."&amp;tn=g";
				if($count < $params->get('images_num')) {
			?>
					<div class="image-img-coll" <?php echo $width; ?>>
						<div class="image-item-inner">
							<a href="<?php echo JURI::base(true); ?>/components/com_xgallery/helpers/img.php?<?php echo $collLink; ?>" rel="<?php echo $shadowb; ?>;player=img" title="<?php echo htmlspecialchars($collInfo->title); ?>" <?php if($params->get('show_caption')) { echo 'rev="' . htmlspecialchars($collInfo->introtext) . '"'; } ?>>					
								<img src="<?php echo JURI::base(true); ?>/components/com_xgallery/helpers/img.php?<?php echo $collString; ?>" alt="<?php echo htmlspecialchars($collInfo->title); ?>" <?php echo $imageStyle; ?> />
							</a>
						</div>
						<?php if($params->get('show_tname')) { 
							$imgName = pathinfo($collImage);
							$items = array('/\_/', '/\-/');
							$thmName = preg_replace($items, ' ', $imgName['filename']);
						?>
						<div class="image-name"><small><?php echo ucwords($thmName); ?></small></div>
						<?php } ?>
					</div>					
					<?php if($currRow < ($params->get('images_per_row') - 1)) {
						$currRow++;
					} else {
						echo '<div class="image-coll-clear"><!-- clear --></div>';
						$currRow = 0;						
					}
				}
				$count++;
			} 		
		} ?>		
		</div>	
	</div>
</div>
 */ ?>

