<?php echo Form::hidden('current_url', $current_url);?>

<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'upForm', 'name' => 'upForm', 'action' => '', 'method' => 'post', 'enctype' => 'multipart/form-data')); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Form::hidden('csv_capture', 'csv_capture'); ?>
        <?php echo Form::input('fileUpload','',array('type' => 'file', 'id' => 'fileUpload', 'style' => 'display:none')); ?>
        <?php echo Form::close(); ?>

        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
		<?php echo Form::hidden('list_url', $list_url);?>
		<?php echo Form::hidden('logistics_id', (!empty($logistics_id)) ? $logistics_id : '');?>
        <?php echo Form::hidden('mode', (!empty($data['mode'])) ? $data['mode']:1); ?>

        <?php echo Form::hidden('select_record', null);?>
        <script>
            var clear_msg 		= '<?php echo Config::get('m_CI0005'); ?>';
            var error_msg1 		= '<?php echo Config::get('m_MW0013'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_RE0001'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_RE0002'); ?>';
            var processing_msg3 = '<?php echo Config::get('m_RE0003'); ?>';
            var processing_msg4 = '<?php echo Config::get('m_RE0013'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <br />
        <div style="padding-top:10px;">
            <!-- <input type="button" value="検索" class='buttonB' tabindex="2" onclick="carModelSearch('<?php echo Uri::create('search/s0010'); ?>')"/> -->
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'onclick' => 'return submitChkClear()' , 'tabindex' => '3')); ?>
            <?php //echo Form::submit('excel', 'エクセル出力', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'tabindex' => '4')); ?>
            <?php // echo Form::submit('csv_download', 'CSVフォーマット', array('class' => 'buttonB', 'tabindex' => '4')); ?>
            <?php //echo Form::submit('csv_capture', 'CSV取込', array('id' => 'csv_capture', 'data-trigger' => '#fileUpload', 'class' => 'buttonB', 'tabindex' => '5')); ?>
        </div>
        <br />
			<table class="search-area" style="height: 90px; width: 900px">
				<tr>
					<td style="width: 200px; height: 30px;">
						入庫日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 600px; height: 30px;">
						<?php echo Form::input('receipt_date', (!empty($data['receipt_date'])) ? $data['receipt_date']:'', array('type' => 'date', 'id' => 'receipt_date','style' => 'width: 300px;','class' => 'input-date','maxlength' => '20','tabindex' => '1')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						入庫時間<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 600px; height: 30px;">
						<?php echo Form::input('receipt_time', (!empty($data['receipt_time'])) ? $data['receipt_time']:'', array('type' => 'time', 'id' => 'receipt_time','style' => 'width: 300px;','class' => 'input-date','maxlength' => '20','tabindex' => '1')); ?>
					</td>
				</tr>
				<?php if (!empty($logistics_id)) : ?>
					<tr>
						<td style="width: 200px; height: 30px;">
							出庫予定日(出庫指示日)<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
						</td>
						<td style="width: 600px; height: 30px;">
							<?php echo Form::input('delivery_schedule_date', (!empty($data['delivery_schedule_date'])) ? $data['delivery_schedule_date']:'', array('type' => 'date', 'id' => 'delivery_schedule_date','style' => 'width: 300px;','class' => 'input-date','maxlength' => '20','tabindex' => '1')); ?>
						</td>
					</tr>
					<tr>
						<td style="width: 200px; height: 30px;">
							出庫予定時間(出庫指示時間)<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
						</td>
						<td style="width: 600px; height: 30px;">
							<?php echo Form::input('delivery_schedule_time', (!empty($data['delivery_schedule_time'])) ? $data['delivery_schedule_time']:'', array('type' => 'time', 'id' => 'delivery_schedule_time','style' => 'width: 300px;','class' => 'input-date','maxlength' => '20','tabindex' => '1')); ?>
						</td>
					</tr>
				<?php endif; ?>
				<tr>
					<td style="width: 200px; height: 30px;">
						登録番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 600px; height: 30px;">
                        <?php echo Form::input('car_code', (!empty($data['car_code'])) ? $data['car_code'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_code', 'style' => 'width:300px;', 'maxlength' => '50', 'tabindex' => '2')); ?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onCarCodeSearch('<?php echo Uri::create('search/s0020?mode=num'); ?>', 0)" />
						<?php echo Form::hidden('car_id', (!empty($data['car_id'])) ? $data['car_id'] : '');?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤ種別<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php echo Form::select('tire_type', $data['tire_type'], $tire_kubun_list,
				            array('class' => 'select-item', 'id' => 'tire_type', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '3')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
						<?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code'] : '');?>
						<?php echo Form::hidden('customer_name', (!empty($data['customer_name'])) ? $data['customer_name'] : '');?>
	                    <?php echo (!empty($data['customer_name'])) ? $data['customer_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						所有者名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
						<?php echo Form::hidden('owner_name', (!empty($data['owner_name'])) ? $data['owner_name'] : '');?>
	                    <?php echo (!empty($data['owner_name'])) ? $data['owner_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						使用者名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
						<?php echo Form::hidden('consumer_name', (!empty($data['consumer_name'])) ? $data['consumer_name'] : '');?>
	                    <?php echo (!empty($data['consumer_name'])) ? $data['consumer_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						登録番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
						<?php echo Form::hidden('car_code', (!empty($data['car_code'])) ? $data['car_code'] : '');?>
	                    <?php echo (!empty($data['car_code'])) ? $data['car_code'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						車種名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
						<?php echo Form::hidden('car_name', (!empty($data['car_name'])) ? $data['car_name'] : '');?>
	                    <?php echo (!empty($data['car_name'])) ? $data['car_name'] : ''; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						総走行距離
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('total_mileage', (!empty($data['total_mileage'])) ? $data['total_mileage'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'total_mileage', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '4')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤメーカー
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_maker', (!empty($data['tire_maker'])) ? $data['tire_maker'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_maker', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '5')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤ商品名
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_product_name', (!empty($data['tire_product_name'])) ? $data['tire_product_name'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_product_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '6')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤサイズ
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_size', (!empty($data['tire_size'])) ? $data['tire_size'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_size', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '7')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤパターン
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_pattern', (!empty($data['tire_pattern'])) ? $data['tire_pattern'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_pattern', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '8')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤ製造年
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_made_date', (!empty($data['tire_made_date'])) ? $data['tire_made_date'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_made_date', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '4', 'tabindex' => '9')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤ残溝数１
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_remaining_groove1', (!empty($data['tire_remaining_groove1'])) ? $data['tire_remaining_groove1'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_remaining_groove1', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '10')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤ残溝数２
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_remaining_groove2', (!empty($data['tire_remaining_groove2'])) ? $data['tire_remaining_groove2'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_remaining_groove2', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '11')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤ残溝数３
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_remaining_groove3', (!empty($data['tire_remaining_groove3'])) ? $data['tire_remaining_groove3'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_remaining_groove3', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '12')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤ残溝数４
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_remaining_groove4', (!empty($data['tire_remaining_groove4'])) ? $data['tire_remaining_groove4'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_remaining_groove4', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '13')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤパンク、傷
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('tire_punk', (!empty($data['tire_punk'])) ? $data['tire_punk'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'tire_punk', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '14')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						タイヤナット有無
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php echo Form::select('nut_flg', $data['nut_flg'], $yes_no_list,
				            array('class' => 'select-item', 'id' => 'nut_flg', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '15')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						保管場所<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php //echo Form::select('location_id', $data['location_id'], $location_combo_list,array('class' => 'select-item', 'id' => 'location_id', 'style' => 'width: 300px', 'onchange' => 'change()', 'tabindex' => '16')); ?>
                        <input type="button" name="s_client" value="検索" class='buttonA' tabindex="15" onclick="onLocationCodeSearch('<?php echo Uri::create('search/s0040?mode=choose&location_id='.$data['location_id']); ?>', 0)" />
                        <span style="padding-left: 10px;">
							<?php echo Form::hidden('location_id', (!empty($data['location_id'])) ? $data['location_id'] : '');?>
					        <?php echo (isset($location_list[$data['location_id']]) && !empty($data['location_id'])) ? $location_list[$data['location_id']]:''; ?>
					    </span>
					</td>
				</tr>
		        <?php echo Form::close(); ?>
				<?php if (!empty($logistics_id) && !empty($data['tire_type'])) : ?>
					<?php /* タイヤ写真 */ ?>
					<tr>
						<td style="width: 200px; height: 30px;">
							タイヤ写真①
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.png').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.jpg').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/1.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'1.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
						<?php else: ?>
							<?php echo Html::anchor(
								Uri::create('img/no_img.png').'?'.time(), 
								Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php endif; ?>

						<!-- 画像アップロード -->
						<?php echo Form::open(array('id' => 'fileform1', 'action' => Uri::create($upload_url), 'method' => 'post', 'enctype' => 'multipart/form-data')); ?>
						<?php echo Form::file('fileUpload', array('id' => 'file_input1', 'style' => 'DISPLAY: none', 'onchange' => "$('#fake_input_file1').val($(this).val())")); ?>
						<?php echo Form::hidden('check_url', $check_url);?>
						<?php echo Form::hidden('folder', 'img/logistics/'.$data['tire_type']);?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('logistics_car_id', $data['car_id']);?>
						<?php echo Form::hidden('logistics_id', $logistics_id, array('id' => 'logistics_id')); ?>
						<?php echo Form::close(); ?>
						<!-- 画像アップロード -->

		                <?php echo Form::input('fileUpload1', '', array('onclick' => "$('#file_input1').click();", 'class' => 'input-text', 'style' => 'height:30px;width:240px;margin-top: 10px;', 'id' => 'fake_input_file1', 'readonly' => 'readonly')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'ファイル選択', array('class' => 'buttonB', 'style' => 'width:110px;font-size:12px;padding: 5px;', 'onclick' => '$("#file_input1").click();', 'tabindex' => '902')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'アップロード', array('class' => 'buttonB', 'id' => 'btnUpload1', 'data-id' => '1', 'style' => 'width:140px;font-size:14px;margin-left:20px;padding: 5px;', 'tabindex' => '902')); ?><br>
						<span class="error_m" style="font-size:14px;" id="file_data_err1"></span>
						</td>
					</tr>
					<tr>
						<td style="width: 200px; height: 30px;">
							タイヤ写真②
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.png').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.jpg').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/2.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'2.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
						<?php else: ?>
							<?php echo Html::anchor(
								Uri::create('img/no_img.png').'?'.time(), 
								Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php endif; ?>

						<!-- 画像アップロード -->
						<?php echo Form::open(array('id' => 'fileform2', 'action' => Uri::create($upload_url), 'method' => 'post', 'enctype' => 'multipart/form-data')); ?>
						<?php echo Form::file('fileUpload', array('id' => 'file_input2', 'style' => 'DISPLAY: none', 'onchange' => "$('#fake_input_file2').val($(this).val())")); ?>
						<?php echo Form::hidden('check_url', $check_url);?>
						<?php echo Form::hidden('folder', 'img/logistics/'.$data['tire_type']);?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('logistics_car_id', $data['car_id']);?>
						<?php echo Form::hidden('logistics_id', $logistics_id, array('id' => 'logistics_id')); ?>
						<?php echo Form::close(); ?>
						<!-- 画像アップロード -->

		                <?php echo Form::input('fileUpload2', '', array('onclick' => "$('#file_input2').click();", 'class' => 'input-text', 'style' => 'height:30px;width:240px;margin-top: 10px;', 'id' => 'fake_input_file2', 'readonly' => 'readonly')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'ファイル選択', array('class' => 'buttonB', 'style' => 'width:110px;font-size:12px;padding: 5px;', 'onclick' => '$("#file_input2").click();', 'tabindex' => '902')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'アップロード', array('class' => 'buttonB', 'id' => 'btnUpload2', 'data-id' => '2', 'style' => 'width:140px;font-size:14px;margin-left:20px;padding: 5px;', 'tabindex' => '902')); ?><br>
						<span class="error_m" style="font-size:14px;" id="file_data_err2"></span>
						</td>
					</tr>
					<tr>
						<td style="width: 200px; height: 30px;">
							タイヤ写真③
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.png').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.jpg').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/3.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
						<?php else: ?>
							<?php echo Html::anchor(
								Uri::create('img/no_img.png').'?'.time(), 
								Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php endif; ?>

						<!-- 画像アップロード -->
						<?php echo Form::open(array('id' => 'fileform3', 'action' => Uri::create($upload_url), 'method' => 'post', 'enctype' => 'multipart/form-data')); ?>
						<?php echo Form::file('fileUpload', array('id' => 'file_input3', 'style' => 'DISPLAY: none', 'onchange' => "$('#fake_input_file3').val($(this).val())")); ?>
						<?php echo Form::hidden('check_url', $check_url);?>
						<?php echo Form::hidden('folder', 'img/logistics/'.$data['tire_type']);?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('logistics_car_id', $data['car_id']);?>
						<?php echo Form::hidden('logistics_id', $logistics_id, array('id' => 'logistics_id')); ?>
						<?php echo Form::close(); ?>
						<!-- 画像アップロード -->

		                <?php echo Form::input('fileUpload3', '', array('onclick' => "$('#file_input3').click();", 'class' => 'input-text', 'style' => 'height:30px;width:240px;margin-top: 10px;', 'id' => 'fake_input_file3', 'readonly' => 'readonly')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'ファイル選択', array('class' => 'buttonB', 'style' => 'width:110px;font-size:12px;padding: 5px;', 'onclick' => '$("#file_input3").click();', 'tabindex' => '902')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'アップロード', array('class' => 'buttonB', 'id' => 'btnUpload3', 'data-id' => '3', 'style' => 'width:140px;font-size:14px;margin-left:20px;padding: 5px;', 'tabindex' => '902')); ?><br>
						<span class="error_m" style="font-size:14px;" id="file_data_err3"></span>
						</td>
					</tr>
					<tr>
						<td style="width: 200px; height: 30px;">
							タイヤ写真④
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.png').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.jpg').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/logistics/'.$data['tire_type'].'/'.$logistics_id.'/4.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
						<?php else: ?>
							<?php echo Html::anchor(
								Uri::create('img/no_img.png').'?'.time(), 
								Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php endif; ?>

						<!-- 画像アップロード -->
						<?php echo Form::open(array('id' => 'fileform4', 'action' => Uri::create($upload_url), 'method' => 'post', 'enctype' => 'multipart/form-data')); ?>
						<?php echo Form::file('fileUpload', array('id' => 'file_input4', 'style' => 'DISPLAY: none', 'onchange' => "$('#fake_input_file4').val($(this).val())")); ?>
						<?php echo Form::hidden('check_url', $check_url);?>
						<?php echo Form::hidden('folder', 'img/logistics/'.$data['tire_type']);?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('logistics_car_id', $data['car_id']);?>
						<?php echo Form::hidden('logistics_id', $logistics_id, array('id' => 'logistics_id')); ?>
						<?php echo Form::close(); ?>
						<!-- 画像アップロード -->

		                <?php echo Form::input('fileUpload4', '', array('onclick' => "$('#file_input4').click();", 'class' => 'input-text', 'style' => 'height:30px;width:240px;margin-top: 10px;', 'id' => 'fake_input_file4', 'readonly' => 'readonly')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'ファイル選択', array('class' => 'buttonB', 'style' => 'width:110px;font-size:12px;padding: 5px;', 'onclick' => '$("#file_input4").click();', 'tabindex' => '902')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'アップロード', array('class' => 'buttonB', 'id' => 'btnUpload4', 'data-id' => '4', 'style' => 'width:140px;font-size:14px;margin-left:20px;padding: 5px;', 'tabindex' => '902')); ?><br>
						<span class="error_m" style="font-size:14px;" id="file_data_err4"></span>
						</td>
					</tr>
					<?php /* タイヤ写真 */ ?>
				<?php endif; ?>
			</table>
			<br />
        <div class="search-buttons">
            <?php echo Form::submit('back', '戻　　　る', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkBack()', 'tabindex' => '900')); ?>
            <?php if (!empty($logistics_id)) : ?>
	            <?php echo Form::submit('execution', '更　　　新', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution(2)', 'tabindex' => '901')); ?>
			<?php else: ?>
	            <?php echo Form::submit('execution', '登　　　録', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution(1)', 'tabindex' => '901')); ?>
			<?php endif; ?>
            <button type="button" onclick="submitReceiptPrint('<?php echo Uri::create('logistics/l0020'); ?>', '<?php echo $logistics_id; ?>')" class="buttonB">　入庫シール印刷 </button>
        </div>
	</div>
</section>
