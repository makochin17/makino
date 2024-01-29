<section id="banner" style="padding-top:10px;">
	<div class="content" style="margin-top:0px;">
	    <?php echo Form::open(array('id' => 'calendar', 'name' => 'calendar', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
	    <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
	    <?php echo Form::hidden('upDay', '', array('id' => 'upDay'));?>
	    <?php echo Form::hidden('upWeek', '', array('id' => 'upWeek'));?>
	    <?php echo Form::hidden('holidayMode', '', array('id' => 'holidayMode'));?>
	    <?php echo Form::hidden('y', $current_year);?>
		<div style="margin:20px 0px 15px 10px;"></div>
		<div style="text-align:center;margin-top:20px;">

			<span style="font-size:18px;font-weight:bold;;">
				<?php echo Html::anchor(\Uri::create('system/c0050?y=').($current_year-1), '<<'); ?>
				<?php echo $current_year;?> 年度
				<?php echo Html::anchor(\Uri::create('system/c0050?y=').($current_year+1), '>>'); ?>
			</span>
			<div style="margin-top:3px;text-align:right;margin-right:50px;"><span style="background-color:#FFD2E1;">　　</span>&nbsp;休業日</div>
			<ul style="width:100%;">
				<?php /* カレンダー表示 */ ?>
				<?php echo $calendar; ?>
			</ul>
			<br>
		</div>
		<?php echo Form::close(); ?>
	</div>
</section>