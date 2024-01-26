<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" dir="ltr" lang="ja">
<!-- <!doctype html>
<html lang="ja">
 -->
 <head>
	<?php echo $head; ?>
</head>
<body>
	<header id="header" style="padding-top:1em;">
		<div style="text-align:center;margin:10px auto 10px auto;">
			<?php echo Html::anchor(\Uri::create('top'), Asset::img('sidemenu.jpg', array('style' => 'width:230px;', 'alt' => 'logo')), array('class' => 'logo')); ?>
		</div>
	</header>

	<div id="wrapper" style="text-align:center;">

		<div style="text-align:center;margin:60px auto 50px auto;">

			<?php echo $content; ?>

		</div>
	</div>

	<!--====== Footer ======-->
	<?php // echo $footer; ?>
	<!--====== //Footer ======-->
</body></html>
