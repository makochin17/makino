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
	</header>

	<div id="wrapper" style="text-align:center;">

		<div style="text-align:center;margin:100px auto 50px auto;">

			<div class="logo" style="margin-bottom:40px;">
				<div style="text-align:center;margin:10px auto 10px auto;font-size: large;">
					<?php echo Asset::img('login_logo.jpg', array('style' => 'width:440px;', 'alt' => 'logo')); ?>
				</div>
			</div>

			<?php echo $content; ?>

		</div>
	</div>

	<!--====== Footer ======-->
	<?php // echo $footer; ?>
	<!--====== //Footer ======-->
</body></html>
