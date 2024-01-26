<?php if (!empty($tree['page_title'])) : ?>
<div style="margin-top:15px;">
	<?php echo Html::anchor(\Uri::create('top'), 'TOP'); ?> 
	＞ 
	<?php echo $tree['management_function']; ?>
	＞ 
	<?php echo (empty($tree['page_url']) && !empty($tree['page_title'])) ? $tree['page_title']:Html::anchor($tree['page_url'], $tree['page_title']); ?> 
	<br />
</div>
<?php endif; ?>