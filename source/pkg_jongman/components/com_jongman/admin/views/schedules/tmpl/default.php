<?php
/**
 * @version     $Id: default.php 506 2013-01-01 05:22:08Z mrs.siam $
 * @package     JONGman
 * @copyright   Copyright (C) 2009 - 2011  Prasit Gebsaap. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

$user		= JFactory::getUser();
$userId 	= $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder=='ordering';
?>
<form action="<?php echo JRoute::_('index.php?option=com_jongman&view=schedules'); ?>" method="post" name="adminForm" id="adminForm">
<?php
if (!$this->is_j25) :
	if (!empty($this->sidebar)) :
?>
	<div id="j-sidebar-container" class="span2">
    	<?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
    	<div id="j-main-container">
    <?php
    endif;
    //Search Toolbar
    echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
else:
	 echo $this->loadTemplate('filter_j25');
endif;
?>	
	<div class="clr"> </div>
<?php if (empty($this->items)) : ?>
	<div class="alert alert-no-items">
		<?php echo JText::_('COM_JONGMAN_NO_MATCHING_RESULTS'); ?>
	</div>
<?php else : ?>
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="1%" clas="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort',  'COM_JONGMAN_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
				</th>
                <th width="35% nowrap">
                    <?php echo JText::_('COM_JONGMAN_HEADING_RESERVANLE_SLOTS')?>
                </th>
                <th width="15%">
                    <?php echo JText::_('COM_JONGMAN_HEADING_BLOCKED_SLOTS')?>
                </th>
                <th width="5%">
                	<?php echo JHtml::_('grid.sort','COM_JONGMAN_HEADING_FIRST_DAY', 's.weekday_start', $listDirn, $listOrder)?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('grid.sort', 'COM_JONGMAN_HEADING_VIEW_DAYS', 's.view_days', $listDirn, $listOrder)?>
                </th>
				<th width="10%" class="hidden-phone">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="hidden-phone">
					<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'published', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tbody>         
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'ordering');
			$canCreate	= $user->authorise('core.create', 'com_jongman');
			$canEdit	= $user->authorise('core.edit', 'com_jongman');
			$canCheckin	= $user->authorise('core.manage',	'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			$canChange	= $user->authorise('core.edit.state', 'com_jongman') && $canCheckin;            
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->name, $item->checked_out_time, 'schedules.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_jongman&task=schedule.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->name); ?>
					<?php endif; ?>
					<p class="smallsub">
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?></p>
				</td>
				<?php 
					$slots = $item->layout->getSlots();
					$reservableSlots = array();
					$blockedSlots = array();
					foreach($slots as $slot) {
                		if ($slot->periodType == RFSchedulePeriodTypes::RESERVABLE) {
                			$reservableSlots[] = $slot->start->format('H:i').'-'.$slot->end->format('H:i');		
                		}elseif( $slot->periodType == RFSchedulePeriodTypes::NONRESERVABLE) {
                			$blockedSlots[] = $slot->start->format('H:i').'-'.$slot->end->format('H:i');	
                		} 
					}	
				?>
                <td class="center">
                	<?php // firefox has problem with long td content ?>
                	<div style="word-break:break-all; word-wrap: break-word;">
                	<?php echo implode(',', $reservableSlots)?>
                	</div>
                </td>
                <td class="center">
                	<div style="word-break:break-all; word-wrap: break-word;">
                	<?php echo implode(',', $blockedSlots)?>                  
                	</div>  
                </td>
                <td class="center">
                	<?php echo JText::plural('COM_JONGMAN_DAY_OF_WEEK',$item->weekday_start)?>
                </td>
                <td class="center">
                    <?php echo $item->view_days?>
                </td>
                <td class="center">
                    <?php echo $item->access_level?>
                </td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'schedules.', $canChange); ?>
				</td>
				<td class="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<?php if ($this->is_j25) : ?>
		<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<?php endif;?>
	</table>
	<?php if (!$this->is_j25) : echo $this->pagination->getListFooter(); endif; ?>
<?php endif?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
<?php if (!$this->is_j25) : ?>
	</div>
<?php endif; ?>
</form>