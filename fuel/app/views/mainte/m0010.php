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
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0010.js');?>
        <script>
            var clear_msg 		= '<?php echo Config::get('m_CI0005'); ?>';
            var error_msg1 		= '<?php echo Config::get('m_MW0013'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_MI0001'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_MI0002'); ?>';
            var processing_msg3 = '<?php echo Config::get('m_MI0003'); ?>';
            var processing_msg4 = '<?php echo Config::get('m_MI0008'); ?>';
            var processing_msg5 = '<?php echo Config::get('m_MI0010'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        処理区分&emsp;
        <?php echo Form::select('processing_division', $data['processing_division'], $processing_division_list,
            array('class' => 'select-item', 'id' => 'processing_division', 'style' => 'width: 80px', 'onchange' => 'change()', 'tabindex' => '1')); ?>
        <br />
        <div style="padding-top:10px;">
            <input type="button" value="検索" class='buttonB' tabindex="2" onclick="carModelSearch('<?php echo Uri::create('search/s0010'); ?>')"/>
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'onclick' => 'return submitChkClear()' , 'tabindex' => '3')); ?>
            <?php echo Form::submit('excel', 'エクセル出力', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'tabindex' => '4')); ?>
            <?php // echo Form::submit('csv_download', 'CSVフォーマット', array('class' => 'buttonB', 'tabindex' => '4')); ?>
            <?php //echo Form::submit('csv_capture', 'CSV取込', array('id' => 'csv_capture', 'data-trigger' => '#fileUpload', 'class' => 'buttonB', 'tabindex' => '5')); ?>
        </div>
        <br />

			<table class="search-area" style="height: 90px; width: 680px">
				<tr>
					<td style="width: 200px; height: 30px;">
						社員コード<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                    <?php echo Form::input('text_member_code', (!empty($data['member_code'])) ? $data['member_code'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'text_member_code', 'style' => 'width:80px;', 'minlength' => '1', 'maxlength' => '5', 'tabindex' => '5')); ?></td>
                    <?php echo Form::hidden('member_code', (!empty($data['member_code'])) ? $data['member_code'] : '');?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						課<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo Form::select('division_code', $data['division_code'], $division_list,
				            array('class' => 'select-item', 'id' => 'division_code', 'style' => 'width: 150px', 'onchange' => 'change()', 'tabindex' => '6')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					氏名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('full_name', (!empty($data['full_name'])) ? $data['full_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'full_name', 'style' => 'width:220px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '7')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					ふりがな<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('name_furigana', (!empty($data['name_furigana'])) ? $data['name_furigana'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'name_furigana', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '15', 'tabindex' => '8')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					役職<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
				        <?php echo Form::select('position_code', $data['position_code'], $position_list,
				            array('class' => 'select-item', 'id' => 'position_code', 'style' => 'width: 150px', 'onchange' => 'change()', 'tabindex' => '9')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					車両</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('car_code', (!empty($data['car_code'])) ? sprintf('%04d', $data['car_code']) : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'car_code', 'style' => 'width:80px;', 'minlength' => '1', 'maxlength' => '4', 'tabindex' => '10')); ?>
						<input name="CarSearch" class="buttonA" type="button" value="検索" onclick="carModelSearch('<?php echo Uri::create('search/s0050'); ?>')" />
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					ドライバー名</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('driver_name', (!empty($data['driver_name'])) ? $data['driver_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'driver_name', 'style' => 'width:150px;', 'minlength' => '1', 'maxlength' => '6', 'tabindex' => '10')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					電話番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('phone_number', (!empty($data['phone_number'])) ? $data['phone_number'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'phone_number', 'style' => 'width:180px;', 'minlength' => '1', 'maxlength' => '15', 'tabindex' => '11')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					ユーザ名</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('text_user_id', (!empty($data['user_id'])) ? $data['user_id'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'text_user_id', 'style' => 'width:140px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '12')); ?>
	                    <?php echo Form::hidden('user_id', (!empty($data['user_id'])) ? $data['user_id'] : '');?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					ユーザ権限</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo Form::select('user_authority', $data['user_authority'], $user_permission,
				            array('class' => 'select-item', 'id' => 'user_authority', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '14')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					ロックアウト</td>
					<td style="width: 480px; height: 30px;">
						<?php echo ($data['lock_status'] > 0) ? 'ロック中':'-'; ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					ユーザ操作</td>
					<td style="width: 480px; height: 30px;">
		            <?php echo Form::submit('unlock', 'ロックアウト解除', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkUnlock()', 'tabindex' => '901')); ?>
		            <?php echo Form::submit('passinitialize', 'パスワード初期化', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkPassInitialize()', 'tabindex' => '902')); ?>
				</tr>
			</table>
			<br />
        <div class="search-buttons">
            <?php echo Form::submit('execution', '確定', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
	</div>
</section>
