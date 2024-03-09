<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">

        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code'] : '');?>
        <script>
            var clear_msg 		= '<?php echo Config::get('m_CI0005'); ?>';
            var error_msg1 		= '<?php echo Config::get('m_MW0013'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_CAR001'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_CAR002'); ?>';
            var processing_msg3 = '<?php echo Config::get('m_CAR003'); ?>';
            var processing_msg4 = '<?php echo Config::get('m_MI0008'); ?>';
            var processing_msg5 = '<?php echo Config::get('m_MI0010'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <br />
			<table class="search-area" style="height: 90px; width: 900px">
				<tr>
					<td style="width: 200px; height: 30px;">
						旧車両ID
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['old_car_id'])) ? $data['old_car_id'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						登録番号
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['car_code'])) ? $data['car_code'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様名
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['customer_name'])) ? $data['customer_name'] : ''; ?>
	                    <?php echo (!empty($data['customer_code'])) ? ' 【'.$data['customer_code'].'】' : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						所有者名
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['owner_name'])) ? $data['owner_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						使用者名
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['consumer_name'])) ? $data['consumer_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						車種名
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['car_name'])) ? $data['car_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						作業所要時間
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (isset($work_time_list[$data['work_required_time']])) ? $work_time_list[$data['work_required_time']] : ''; ?> 分
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤメーカー
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_maker'])) ? $data['summer_tire_maker'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ商品名
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_product_name'])) ? $data['summer_tire_product_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤサイズ
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_size'])) ? $data['summer_tire_size'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤサイズ２
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_size2'])) ? $data['summer_tire_size2'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤタイヤパターン
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_pattern'])) ? $data['summer_tire_pattern'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤホイール商品名
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_wheel_product_name'])) ? $data['summer_tire_wheel_product_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤホイールサイズ
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_wheel_size'])) ? $data['summer_tire_wheel_size'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤホイールサイズ２
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_wheel_size2'])) ? $data['summer_tire_wheel_size2'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ製造年
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_made_date'])) ? $data['summer_tire_made_date'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ残溝数１
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_remaining_groove1'])) ? $data['summer_tire_remaining_groove1'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ残溝数２
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_remaining_groove2'])) ? $data['summer_tire_remaining_groove2'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ残溝数３
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_remaining_groove3'])) ? $data['summer_tire_remaining_groove3'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ残溝数４
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_remaining_groove4'])) ? $data['summer_tire_remaining_groove4'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤパンク、傷
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['summer_tire_punk'])) ? $data['summer_tire_punk'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤナット有無
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (isset($yes_no_list[$data['summer_nut_flg']])) ? $yes_no_list[$data['summer_nut_flg']] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						夏タイヤ保管場所
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (isset($location_list[$data['summer_location_id']])) ? $location_list[$data['summer_location_id']] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤメーカー
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_maker'])) ? $data['winter_tire_maker'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ商品名
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_product_name'])) ? $data['winter_tire_product_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤサイズ
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_size'])) ? $data['winter_tire_size'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤサイズ２
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_size2'])) ? $data['winter_tire_size2'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤタイヤパターン
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_pattern'])) ? $data['winter_tire_pattern'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤホイール商品名
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_wheel_product_name'])) ? $data['winter_tire_wheel_product_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤホイールサイズ
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_wheel_size'])) ? $data['winter_tire_wheel_size'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤホイールサイズ２
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_wheel_size2'])) ? $data['winter_tire_wheel_size2'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ製造年
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_made_date'])) ? $data['winter_tire_made_date'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ残溝数１
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_remaining_groove1'])) ? $data['winter_tire_remaining_groove1'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ残溝数２
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_remaining_groove2'])) ? $data['winter_tire_remaining_groove2'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ残溝数３
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_remaining_groove3'])) ? $data['winter_tire_remaining_groove3'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ残溝数４
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_remaining_groove4'])) ? $data['winter_tire_remaining_groove4'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤパンク、傷
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['winter_tire_punk'])) ? $data['winter_tire_punk'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤナット有無
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (isset($yes_no_list[$data['winter_nut_flg']])) ? $yes_no_list[$data['winter_nut_flg']] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						冬タイヤ保管場所
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (isset($location_list[$data['winter_location_id']])) ? $location_list[$data['winter_location_id']] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						保管区分夏
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (isset($yes_no_list[$data['summer_class_flg']])) ? $yes_no_list[$data['summer_class_flg']] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						保管区分冬
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (isset($yes_no_list[$data['winter_class_flg']])) ? $yes_no_list[$data['winter_class_flg']] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						注意事項
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['note'])) ? $data['note'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						メッセージ
					</td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo (!empty($data['message'])) ? $data['message'] : ''; ?>
					</td>
				</tr>
				<?php /* 夏タイヤ写真 */ ?>
				<tr>
					<td style="width: 200px; height: 30px;">
						夏タイヤ写真①
					</td>
					<td style="width: 600px; height: 30px;">
					<?php /* 画像表示 */ ?>
					<?php if (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/1.png')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/1.png').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/1.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/1.jpg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/1.jpg').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/1.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/1.jpeg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/1.jpeg').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/1.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/1.pdf')): ?>
							<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/summer/'.$data['car_id'].'1.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
					<?php else: ?>
						<?php echo Html::anchor(
							Uri::create('img/no_img.png').'?'.time(), 
							Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						夏タイヤ写真②
					</td>
					<td style="width: 600px; height: 30px;">
					<?php /* 画像表示 */ ?>
					<?php if (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/2.png')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/2.png').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/2.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/2.jpg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/2.jpg').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/2.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/2.jpeg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/2.jpeg').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/2.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/2.pdf')): ?>
							<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/summer/'.$data['car_id'].'2.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
					<?php else: ?>
						<?php echo Html::anchor(
							Uri::create('img/no_img.png').'?'.time(), 
							Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						夏タイヤ写真③
					</td>
					<td style="width: 600px; height: 30px;">
					<?php /* 画像表示 */ ?>
					<?php if (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/3.png')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/3.png').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/3.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/3.jpg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/3.jpg').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/3.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/3.jpeg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/3.jpeg').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/3.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/3.pdf')): ?>
							<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/summer/'.$data['car_id'].'/3.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
					<?php else: ?>
						<?php echo Html::anchor(
							Uri::create('img/no_img.png').'?'.time(), 
							Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						夏タイヤ写真④
					</td>
					<td style="width: 600px; height: 30px;">
					<?php /* 画像表示 */ ?>
					<?php if (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/4.png')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/4.png').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/4.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/4.jpg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/4.jpg').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/4.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/4.jpeg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/summer/'.$data['car_id'].'/4.jpeg').'?'.time(), 
							Asset::img(Uri::create('img/car/summer/'.$data['car_id'].'/4.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/summer/'.$data['car_id'].'/4.pdf')): ?>
							<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/summer/'.$data['car_id'].'/4.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
					<?php else: ?>
						<?php echo Html::anchor(
							Uri::create('img/no_img.png').'?'.time(), 
							Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php endif; ?>
					</td>
				</tr>
				<?php /* 夏タイヤ写真 */ ?>
				<?php /* 冬タイヤ写真 */ ?>
				<tr>
					<td style="width: 200px; height: 30px;">
						冬タイヤ写真①
					</td>
					<td style="width: 600px; height: 30px;">
					<?php /* 画像表示 */ ?>
					<?php if (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/1.png')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/1.png').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/1.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/1.jpg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/1.jpg').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/1.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/1.jpeg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/1.jpeg').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/1.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/1.pdf')): ?>
							<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/winter/'.$data['car_id'].'1.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
					<?php else: ?>
						<?php echo Html::anchor(
							Uri::create('img/no_img.png').'?'.time(), 
							Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						冬タイヤ写真②
					</td>
					<td style="width: 600px; height: 30px;">
					<?php /* 画像表示 */ ?>
					<?php if (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/2.png')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/2.png').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/2.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/2.jpg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/2.jpg').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/2.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/2.jpeg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/2.jpeg').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/2.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/2.pdf')): ?>
							<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/winter/'.$data['car_id'].'2.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
					<?php else: ?>
						<?php echo Html::anchor(
							Uri::create('img/no_img.png').'?'.time(), 
							Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						冬タイヤ写真③
					</td>
					<td style="width: 600px; height: 30px;">
					<?php /* 画像表示 */ ?>
					<?php if (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/3.png')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/3.png').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/3.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/3.jpg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/3.jpg').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/3.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/3.jpeg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/3.jpeg').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/3.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/3.pdf')): ?>
							<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/winter/'.$data['car_id'].'/3.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
					<?php else: ?>
						<?php echo Html::anchor(
							Uri::create('img/no_img.png').'?'.time(), 
							Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						冬タイヤ写真④
					</td>
					<td style="width: 600px; height: 30px;">
					<?php /* 画像表示 */ ?>
					<?php if (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/4.png')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/4.png').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/4.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/4.jpg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/4.jpg').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/4.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/4.jpeg')): ?>
						<?php echo Html::anchor(
							Uri::create('img/car/winter/'.$data['car_id'].'/4.jpeg').'?'.time(), 
							Asset::img(Uri::create('img/car/winter/'.$data['car_id'].'/4.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php elseif (file_exists($docroot.'img/car/winter/'.$data['car_id'].'/4.pdf')): ?>
							<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/winter/'.$data['car_id'].'/4.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
					<?php else: ?>
						<?php echo Html::anchor(
							Uri::create('img/no_img.png').'?'.time(), 
							Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
							array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
						); ?>
					<?php endif; ?>
					</td>
				</tr>
				<?php /* 冬タイヤ写真 */ ?>
			</table>
			<br />
        <div class="search-buttons">
            <?php echo Form::submit('back', '戻　　　る', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkBack()', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
	</div>
</section>
