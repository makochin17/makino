<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" dir="ltr" lang="ja">
<!-- <!doctype html>
<html lang="ja">
 -->
 <head>
	<?php echo $head_schedule2; ?>
</head>
<body>
	<div id="wrapper">
		<div id="main">
			<div class="inner">
				<?php echo $header; ?>
				<?php //echo $tree; ?>
				<?php echo $content; ?>
				<?php // echo $footer; ?>
			</div>
		</div>
		<?php echo $sidemenu; ?>
	</div>
	<?php echo $footer; ?>
	<script type="text/javascript">
		$(function(){
			$('[id=dialog]').jqm();
		});
	</script>

	<script type="text/javascript">

		var $children = $('[class=children]'); //子の要素を変数に入れます。
		var original = $children.html(); //後のイベントで、不要なoption要素を削除するため、オリジナルをとっておく

		$(function(){

			//親側のselect要素が変更になるとイベントが発生
			$('[class=parent]').change(function() {

				//選択された店舗グループのvalueを取得し変数に入れる
				var val1 = $(this).val();

				//現時点の中分類の選択値を取得しておく
				// var current_val = $('[name="cboClassM"] option:selected').val();

				//削除された要素をもとに戻すため.html(original)を入れておく
				$children.html(original).find('option').each(function() {

					var val2 = $(this).val(); 

					var val = val2.split('-');
					var val3 = val[0];

					//valueと異なるdata-valを持つ要素を削除
					if (val1 != val3) {
						$(this).not(':first-child').remove();
					}

				});

				//親のselect要素が未選択の場合、子をdisabledにする
				if ($(this).val() == "") {
					$children.attr('disabled', 'disabled');
				} else {
					$children.removeAttr('disabled');
				}
			}).change();
		});

		var $children2 = $('[class=children2]'); //子の要素を変数に入れます。
		var original2 = $children2.html(); //後のイベントで、不要なoption要素を削除するため、オリジナルをとっておく

		$(function(){

			//親側のselect要素が変更になるとイベントが発生
			$('[class=parent2]').change(function() {

				//選択された店舗グループのvalueを取得し変数に入れる
				var val1 = $(this).val();

				//現時点の中分類の選択値を取得しておく
				// var current_val = $('[name="cboClassM"] option:selected').val();

				//削除された要素をもとに戻すため.html(original)を入れておく
				$children2.html(original2).find('option').each(function() {

					var val2 = $(this).val(); 

					var val = val2.split('-');
					var val3 = val[0];

					//valueと異なるdata-valを持つ要素を削除
					if (val1 != val3) {
						$(this).not(':first-child').remove();
					}

				});

				//親のselect要素が未選択の場合、子をdisabledにする
				if ($(this).val() == "") {
					$children2.attr('disabled', 'disabled');
				} else {
					$children2.removeAttr('disabled');
				}
			}).change();
		});

		var $children3 = $('[class=children3]'); //子の要素を変数に入れます。
		var original3 = $children3.html(); //後のイベントで、不要なoption要素を削除するため、オリジナルをとっておく

		$(function(){

			//親側のselect要素が変更になるとイベントが発生
			$('[class=parent3]').change(function() {

				//選択された店舗グループのvalueを取得し変数に入れる
				var val1 = $(this).val();

				//現時点の中分類の選択値を取得しておく
				// var current_val = $('[name="cboClassM"] option:selected').val();

				//削除された要素をもとに戻すため.html(original)を入れておく
				$children3.html(original3).find('option').each(function() {

					var val2 = $(this).val(); 

					var val = val2.split('-');
					var val3 = val[0];

					//valueと異なるdata-valを持つ要素を削除
					if (val1 != val3) {
						$(this).not(':first-child').remove();
					}

				});

				//親のselect要素が未選択の場合、子をdisabledにする
				if ($(this).val() == "") {
					$children3.attr('disabled', 'disabled');
				} else {
					$children3.removeAttr('disabled');
				}
			}).change();
		});

	</script>

	<!-- ▼ColorboxのCSSを読み込む記述 -->
	<!-- <link href="../css/colorbox/colorbox.css" rel="stylesheet" /> -->
    <?php echo Asset::css('schedule/colorbox/colorbox.css', array('rel' => 'stylesheet'));?>

	<!-- ▼jQueryとColorboxのスクリプトを読み込む記述 -->
    <?php echo Asset::js('fullcalendar/jquery.colorbox-min.js');?>
    <?php echo Asset::js('fullcalendar/jquery.colorbox-ja.js');?>

	<!-- ▼Colorboxの適用対象の指定とオプションの記述 -->
	<script>
	   $(document).ready(function(){
	      $(".iframe").colorbox({iframe:true, width:"80%", height:"90%"});
	   });
	</script>
</body>
</html>
