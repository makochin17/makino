<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
		<?php echo Form::hidden('list_url', $list_url);?>
	    <?php echo Form::hidden('car_id', $car_id, array('id' => 'car_id')); ?>
        <?php echo Form::hidden('mode', (!empty($data['mode'])) ? $data['mode']:1); ?>

        <?php echo Form::hidden('select_record', null);?>
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
						車両ID<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('old_car_id', (!empty($data['old_car_id'])) ? $data['old_car_id'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'old_car_id', 'style' => 'width:200px;', 'minlength' => '1', 'maxlength' => '15', 'tabindex' => '1')); ?>
                    <i class='fa fa-asterisk' style="color:#FF4040;font-size:12px;margin-left:10px;">旧システムの車両IDを入力してください</i>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						登録番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo Form::input('car_code', (!empty($data['car_code'])) ? $data['car_code'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'car_code', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '500', 'tabindex' => '2')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
                        <?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code']:'');?>
                        <?php echo Form::hidden('customer_name', (!empty($data['customer_name'])) ? $data['customer_name']:'');?>
                        <input type="button" name="s_client" value="お客様検索" class='buttonA' tabindex="15" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
                        <span style="margin-left: 20px;margin-right: 10px;font-weight: bold;"><?php echo (!empty($data['customer_code'])) ? $data['customer_code'] : ''; ?></span>
                        <span style="font-weight: bold;"><?php echo (!empty($data['customer_name'])) ? $data['customer_name'] : ''; ?></span>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						所有者名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo Form::input('owner_name', (!empty($data['owner_name'])) ? $data['owner_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'owner_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '500', 'tabindex' => '4')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						使用者名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo Form::input('consumer_name', (!empty($data['consumer_name'])) ? $data['consumer_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'consumer_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '500', 'tabindex' => '5')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						車種名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 600px; height: 30px;">
	                    <?php echo Form::input('car_name', (!empty($data['car_name'])) ? $data['car_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'car_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '500', 'tabindex' => '6')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						作業所要時間
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php echo Form::select('work_required_time', $data['work_required_time'], $work_time_list,
				            array('class' => 'select-item', 'id' => 'work_required_time', 'style' => 'width: 100px', 'onchange' => 'change()', 'tabindex' => '7')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤメーカー
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_maker', (!empty($data['summer_tire_maker'])) ? $data['summer_tire_maker'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_maker', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '8')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ商品名
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_product_name', (!empty($data['summer_tire_product_name'])) ? $data['summer_tire_product_name'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_product_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '9')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤサイズ
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_size', (!empty($data['summer_tire_size'])) ? $data['summer_tire_size'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_size', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '10')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤサイズ２
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_size2', (!empty($data['summer_tire_size2'])) ? $data['summer_tire_size2'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_size2', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '11')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤタイヤパターン
					</td>
					<td style="width: 600px; height: 30px; color: #FF0000;">
                    <?php echo Form::input('summer_tire_pattern', (!empty($data['summer_tire_pattern'])) ? $data['summer_tire_pattern'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_pattern', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '12')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤホイール商品名
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_wheel_product_name', (!empty($data['summer_tire_wheel_product_name'])) ? $data['summer_tire_wheel_product_name'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_wheel_product_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '13')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤホイールサイズ
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_wheel_size', (!empty($data['summer_tire_wheel_size'])) ? $data['summer_tire_wheel_size'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_wheel_size', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '14')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤホイールサイズ２
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_wheel_size2', (!empty($data['summer_tire_wheel_size2'])) ? $data['summer_tire_wheel_size2'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_wheel_size2', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '15')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ製造年
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_made_date', (!empty($data['summer_tire_made_date'])) ? $data['summer_tire_made_date'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_made_date', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '4', 'tabindex' => '16')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ残溝数１
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_remaining_groove1', (!empty($data['summer_tire_remaining_groove1'])) ? $data['summer_tire_remaining_groove1'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_remaining_groove1', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '17')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ残溝数２
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_remaining_groove2', (!empty($data['summer_tire_remaining_groove2'])) ? $data['summer_tire_remaining_groove2'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_remaining_groove2', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '18')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ残溝数３
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_remaining_groove3', (!empty($data['summer_tire_remaining_groove3'])) ? $data['summer_tire_remaining_groove3'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_remaining_groove3', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '19')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤ残溝数４
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_remaining_groove4', (!empty($data['summer_tire_remaining_groove4'])) ? $data['summer_tire_remaining_groove4'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_remaining_groove4', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '20')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤパンク、傷
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('summer_tire_punk', (!empty($data['summer_tire_punk'])) ? $data['summer_tire_punk'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_punk', 'style' => 'width:600px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '21')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #FF0000;">
						夏タイヤナット有無
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php echo Form::select('summer_nut_flg', $data['summer_nut_flg'], $yes_no_list,
				            array('class' => 'select-item', 'id' => 'summer_nut_flg', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '22')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						夏タイヤ保管場所
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php echo Form::select('summer_location_id', $data['summer_location_id'], $location_list,
				            array('class' => 'select-item', 'id' => 'summer_location_id', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '23')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤメーカー
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_maker', (!empty($data['winter_tire_maker'])) ? $data['winter_tire_maker'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_maker', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '24')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ商品名
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_product_name', (!empty($data['winter_tire_product_name'])) ? $data['winter_tire_product_name'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_product_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '25')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤサイズ
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_size', (!empty($data['winter_tire_size'])) ? $data['winter_tire_size'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_size', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '26')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤサイズ２
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_size2', (!empty($data['winter_tire_size2'])) ? $data['winter_tire_size2'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_size2', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '27')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤタイヤパターン
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_pattern', (!empty($data['winter_tire_pattern'])) ? $data['winter_tire_pattern'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_pattern', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '28')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤホイール商品名
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_wheel_product_name', (!empty($data['winter_tire_wheel_product_name'])) ? $data['winter_tire_wheel_product_name'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_wheel_product_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '29')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤホイールサイズ
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_wheel_size', (!empty($data['winter_tire_wheel_size'])) ? $data['winter_tire_wheel_size'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_wheel_size', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '30')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤホイールサイズ２
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_wheel_size2', (!empty($data['winter_tire_wheel_size2'])) ? $data['winter_tire_wheel_size2'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_wheel_size2', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '31')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ製造年
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_made_date', (!empty($data['winter_tire_made_date'])) ? $data['winter_tire_made_date'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_made_date', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '4', 'tabindex' => '32')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ残溝数１
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_remaining_groove1', (!empty($data['winter_tire_remaining_groove1'])) ? $data['winter_tire_remaining_groove1'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_remaining_groove1', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '33')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ残溝数２
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_remaining_groove2', (!empty($data['winter_tire_remaining_groove2'])) ? $data['winter_tire_remaining_groove2'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_remaining_groove2', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '34')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ残溝数３
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_remaining_groove3', (!empty($data['winter_tire_remaining_groove3'])) ? $data['winter_tire_remaining_groove3'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_remaining_groove3', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '35')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤ残溝数４
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_remaining_groove4', (!empty($data['winter_tire_remaining_groove4'])) ? $data['winter_tire_remaining_groove4'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_remaining_groove4', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '36')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤパンク、傷
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('winter_tire_punk', (!empty($data['winter_tire_punk'])) ? $data['winter_tire_punk'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_punk', 'style' => 'width:600px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '37')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px; color: #0000FF;">
						冬タイヤナット有無
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php echo Form::select('winter_nut_flg', $data['winter_nut_flg'], $yes_no_list,
				            array('class' => 'select-item', 'id' => 'winter_nut_flg', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '38')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						冬タイヤ保管場所
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php echo Form::select('winter_location_id', $data['winter_location_id'], $location_list,
				            array('class' => 'select-item', 'id' => 'winter_location_id', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '39')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						保管区分夏
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php echo Form::select('summer_class_flg', $data['summer_class_flg'], $yes_no_list,
				            array('class' => 'select-item', 'id' => 'summer_class_flg', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '40')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						保管区分冬
					</td>
					<td style="width: 600px; height: 30px;">
				        <?php echo Form::select('winter_class_flg', $data['winter_class_flg'], $yes_no_list,
				            array('class' => 'select-item', 'id' => 'winter_class_flg', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '41')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						注意事項
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('note', (!empty($data['note'])) ? $data['note'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'note', 'style' => 'width:600px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '42')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						メッセージ
					</td>
					<td style="width: 600px; height: 30px;">
                    <?php echo Form::input('message', (!empty($data['message'])) ? $data['message'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'message', 'style' => 'width:600px;', 'minlength' => '1', 'maxlength' => '100', 'tabindex' => '43')); ?>
					</td>
				</tr>
		        <?php echo Form::close(); ?>
				<?php if (!empty($car_id)) : ?>
					<?php /* 夏タイヤ写真 */ ?>
					<tr>
						<td style="width: 200px; height: 30px;">
							夏タイヤ写真①
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/car/summer/'.$car_id.'/1.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/1.png').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/1.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/1.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/1.jpg').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/1.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/1.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/1.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/1.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/1.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/summer/'.$car_id.'1.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
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
						<?php echo Form::hidden('folder', 'img/car/summer');?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('car_id', $car_id, array('id' => 'car_id')); ?>
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
							夏タイヤ写真②
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/car/summer/'.$car_id.'/2.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/2.png').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/2.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/2.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/2.jpg').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/2.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/2.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/2.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/2.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/2.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/summer/'.$car_id.'2.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
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
						<?php echo Form::hidden('folder', 'img/car/summer');?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('car_id', $car_id, array('id' => 'car_id')); ?>
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
							夏タイヤ写真③
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/car/summer/'.$car_id.'/3.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/3.png').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/3.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/3.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/3.jpg').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/3.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/3.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/3.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/3.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/3.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/summer/'.$car_id.'/3.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
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
						<?php echo Form::hidden('folder', 'img/car/summer');?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('car_id', $car_id, array('id' => 'car_id')); ?>
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
							夏タイヤ写真④
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/car/summer/'.$car_id.'/4.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/4.png').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/4.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/4.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/4.jpg').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/4.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/4.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/summer/'.$car_id.'/4.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/car/summer/'.$car_id.'/4.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/summer/'.$car_id.'/4.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/summer/'.$car_id.'/4.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
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
						<?php echo Form::hidden('folder', 'img/car/summer');?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('car_id', $car_id, array('id' => 'car_id')); ?>
						<?php echo Form::close(); ?>
						<!-- 画像アップロード -->

		                <?php echo Form::input('fileUpload4', '', array('onclick' => "$('#file_input4').click();", 'class' => 'input-text', 'style' => 'height:30px;width:240px;margin-top: 10px;', 'id' => 'fake_input_file4', 'readonly' => 'readonly')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'ファイル選択', array('class' => 'buttonB', 'style' => 'width:110px;font-size:12px;padding: 5px;', 'onclick' => '$("#file_input4").click();', 'tabindex' => '902')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'アップロード', array('class' => 'buttonB', 'id' => 'btnUpload4', 'data-id' => '4', 'style' => 'width:140px;font-size:14px;margin-left:20px;padding: 5px;', 'tabindex' => '902')); ?><br>
						<span class="error_m" style="font-size:14px;" id="file_data_err4"></span>
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
						<?php if (file_exists($docroot.'img/car/winter/'.$car_id.'/1.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/1.png').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/1.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/1.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/1.jpg').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/1.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/1.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/1.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/1.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/1.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/winter/'.$car_id.'1.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
						<?php else: ?>
							<?php echo Html::anchor(
								Uri::create('img/no_img.png').'?'.time(), 
								Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php endif; ?>

						<!-- 画像アップロード -->
						<?php echo Form::open(array('id' => 'fileform5', 'action' => Uri::create($upload_url), 'method' => 'post', 'enctype' => 'multipart/form-data')); ?>
						<?php echo Form::file('fileUpload', array('id' => 'file_input5', 'style' => 'DISPLAY: none', 'onchange' => "$('#fake_input_file5').val($(this).val())")); ?>
						<?php echo Form::hidden('check_url', $check_url);?>
						<?php echo Form::hidden('folder', 'img/car/winter');?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('car_id', $car_id, array('id' => 'car_id')); ?>
						<?php echo Form::close(); ?>
						<!-- 画像アップロード -->

		                <?php echo Form::input('fileUpload5', '', array('onclick' => "$('#file_input5').click();", 'class' => 'input-text', 'style' => 'height:30px;width:240px;margin-top: 10px;', 'id' => 'fake_input_file5', 'readonly' => 'readonly')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'ファイル選択', array('class' => 'buttonB', 'style' => 'width:110px;font-size:12px;padding: 5px;', 'onclick' => '$("#file_input5").click();', 'tabindex' => '902')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'アップロード', array('class' => 'buttonB', 'id' => 'btnUpload5', 'data-id' => '5', 'style' => 'width:140px;font-size:14px;margin-left:20px;padding: 5px;', 'tabindex' => '902')); ?><br>
						<span class="error_m" style="font-size:14px;" id="file_data_err5"></span>
						</td>
					</tr>
					<tr>
						<td style="width: 200px; height: 30px;">
							冬タイヤ写真②
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/car/winter/'.$car_id.'/2.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/2.png').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/2.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/2.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/2.jpg').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/2.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/2.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/2.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/2.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/2.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/winter/'.$car_id.'2.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
						<?php else: ?>
							<?php echo Html::anchor(
								Uri::create('img/no_img.png').'?'.time(), 
								Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php endif; ?>

						<!-- 画像アップロード -->
						<?php echo Form::open(array('id' => 'fileform6', 'action' => Uri::create($upload_url), 'method' => 'post', 'enctype' => 'multipart/form-data')); ?>
						<?php echo Form::file('fileUpload', array('id' => 'file_input6', 'style' => 'DISPLAY: none', 'onchange' => "$('#fake_input_file6').val($(this).val())")); ?>
						<?php echo Form::hidden('check_url', $check_url);?>
						<?php echo Form::hidden('folder', 'img/car/winter');?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('car_id', $car_id, array('id' => 'car_id')); ?>
						<?php echo Form::close(); ?>
						<!-- 画像アップロード -->

		                <?php echo Form::input('fileUpload6', '', array('onclick' => "$('#file_input6').click();", 'class' => 'input-text', 'style' => 'height:30px;width:240px;margin-top: 10px;', 'id' => 'fake_input_file6', 'readonly' => 'readonly')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'ファイル選択', array('class' => 'buttonB', 'style' => 'width:110px;font-size:12px;padding: 5px;', 'onclick' => '$("#file_input6").click();', 'tabindex' => '902')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'アップロード', array('class' => 'buttonB', 'id' => 'btnUpload6', 'data-id' => '6', 'style' => 'width:140px;font-size:14px;margin-left:20px;padding: 5px;', 'tabindex' => '902')); ?><br>
						<span class="error_m" style="font-size:14px;" id="file_data_err6"></span>
						</td>
					</tr>
					<tr>
						<td style="width: 200px; height: 30px;">
							冬タイヤ写真③
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/car/winter/'.$car_id.'/3.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/3.png').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/3.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/3.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/3.jpg').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/3.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/3.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/3.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/3.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/3.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/winter/'.$car_id.'/3.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
						<?php else: ?>
							<?php echo Html::anchor(
								Uri::create('img/no_img.png').'?'.time(), 
								Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php endif; ?>

						<!-- 画像アップロード -->
						<?php echo Form::open(array('id' => 'fileform7', 'action' => Uri::create($upload_url), 'method' => 'post', 'enctype' => 'multipart/form-data')); ?>
						<?php echo Form::file('fileUpload', array('id' => 'file_input7', 'style' => 'DISPLAY: none', 'onchange' => "$('#fake_input_file7').val($(this).val())")); ?>
						<?php echo Form::hidden('check_url', $check_url);?>
						<?php echo Form::hidden('folder', 'img/car/winter');?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('car_id', $car_id, array('id' => 'car_id')); ?>
						<?php echo Form::close(); ?>
						<!-- 画像アップロード -->

		                <?php echo Form::input('fileUpload7', '', array('onclick' => "$('#file_input7').click();", 'class' => 'input-text', 'style' => 'height:30px;width:240px;margin-top: 10px;', 'id' => 'fake_input_file7', 'readonly' => 'readonly')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'ファイル選択', array('class' => 'buttonB', 'style' => 'width:110px;font-size:12px;padding: 5px;', 'onclick' => '$("#file_input7").click();', 'tabindex' => '902')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'アップロード', array('class' => 'buttonB', 'id' => 'btnUpload7', 'data-id' => '7', 'style' => 'width:140px;font-size:14px;margin-left:20px;padding: 5px;', 'tabindex' => '902')); ?><br>
						<span class="error_m" style="font-size:14px;" id="file_data_err7"></span>
						</td>
					</tr>
					<tr>
						<td style="width: 200px; height: 30px;">
							冬タイヤ写真④
						</td>
						<td style="width: 600px; height: 30px;">
						<?php /* 画像表示 */ ?>
						<?php if (file_exists($docroot.'img/car/winter/'.$car_id.'/4.png')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/4.png').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/4.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/4.jpg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/4.jpg').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/4.jpg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/4.jpeg')): ?>
							<?php echo Html::anchor(
								Uri::create('img/car/winter/'.$car_id.'/4.jpeg').'?'.time(), 
								Asset::img(Uri::create('img/car/winter/'.$car_id.'/4.jpeg').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php elseif (file_exists($docroot.'img/car/winter/'.$car_id.'/4.pdf')): ?>
								<?php echo Asset::img('img/icon_1r_192.png', array('id' => 'img_url', 'data-url' => Uri::create('img/car/winter/'.$car_id.'/4.pdf'), 'style' => 'width:80px;margin-right:10px;margin-top:-15px;', 'align' => 'left')); ?>
						<?php else: ?>
							<?php echo Html::anchor(
								Uri::create('img/no_img.png').'?'.time(), 
								Asset::img(Uri::create('img/no_img.png').'?'.time(), array('style' => 'width:60px;height:46px;margin-right:10px;', 'align' => 'left')), 
								array('data-lightbox' => 'group', 'tabindex' => '910', 'width' => '300')
							); ?>
						<?php endif; ?>

						<!-- 画像アップロード -->
						<?php echo Form::open(array('id' => 'fileform8', 'action' => Uri::create($upload_url), 'method' => 'post', 'enctype' => 'multipart/form-data')); ?>
						<?php echo Form::file('fileUpload', array('id' => 'file_input8', 'style' => 'DISPLAY: none', 'onchange' => "$('#fake_input_file8').val($(this).val())")); ?>
						<?php echo Form::hidden('check_url', $check_url);?>
						<?php echo Form::hidden('folder', 'img/car/winter');?>
						<?php echo Form::hidden('file_id', null);?>
						<?php echo Form::hidden('car_id', $car_id, array('id' => 'car_id')); ?>
						<?php echo Form::close(); ?>
						<!-- 画像アップロード -->

		                <?php echo Form::input('fileUpload8', '', array('onclick' => "$('#file_input8').click();", 'class' => 'input-text', 'style' => 'height:30px;width:240px;margin-top: 10px;', 'id' => 'fake_input_file8', 'readonly' => 'readonly')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'ファイル選択', array('class' => 'buttonB', 'style' => 'width:110px;font-size:12px;padding: 5px;', 'onclick' => '$("#file_input8").click();', 'tabindex' => '902')); ?>
						<?php echo Html::anchor('javascript:void(0)', 'アップロード', array('class' => 'buttonB', 'id' => 'btnUpload8', 'data-id' => '8', 'style' => 'width:140px;font-size:14px;margin-left:20px;padding: 5px;', 'tabindex' => '902')); ?><br>
						<span class="error_m" style="font-size:14px;" id="file_data_err8"></span>
						</td>
					</tr>
					<?php /* 冬タイヤ写真 */ ?>
				<?php endif; ?>
			</table>
			<br />
        <div class="search-buttons">
            <?php echo Form::submit('back', '戻　　　る', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkBack()', 'tabindex' => '900')); ?>
            <?php echo Form::submit('execution', '更　　　新', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution(2)', 'tabindex' => '901')); ?>
        </div>
	</div>
</section>
