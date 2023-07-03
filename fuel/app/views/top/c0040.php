<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
		<header>
			<h2 style="margin: 0px 0px 5px 10px;">お知らせ</h2>
		</header>
		<?php if (!empty($notice)) : ?>
			<?php foreach ($notice as $key => $val) : ?>
				<p style="margin: 10px 0px 0px 10px;">
				<?php echo date('Y年n月j日', strtotime($val['notice_date'])); ?><span style="margin-left:10px;"><?php echo $val['notice_title']; ?></span>
				<?php if ($val['notice_date'] > $new_date) : ?>
					<?php echo Asset::img('new_red.gif', array('style' => 'width:32px;margin: 5px 0px 0px 0px;')); ?>
				<?php endif; ?>
				</p>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</section>