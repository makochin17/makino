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
        <?php echo Form::hidden('processing_division', (!empty($data['processing_division'])) ? $data['processing_division']:1); ?>

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
        <br />
        <div style="padding-top:10px;">
            <!-- <input type="button" value="検索" class='buttonB' tabindex="2" onclick="carModelSearch('<?php echo Uri::create('search/s0090'); ?>')"/> -->
            <?php echo Form::submit('back', '戻　　　る', array('class' => 'buttonB', 'onclick' => 'return submitChkBack()', 'tabindex' => '2')); ?>
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'onclick' => 'return submitChkClear()' , 'tabindex' => '3')); ?>
            <?php echo Form::submit('excel', 'エクセル出力', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'tabindex' => '4')); ?>
            <?php // echo Form::submit('csv_download', 'CSVフォーマット', array('class' => 'buttonB', 'tabindex' => '4')); ?>
            <?php //echo Form::submit('csv_capture', 'CSV取込', array('id' => 'csv_capture', 'data-trigger' => '#fileUpload', 'class' => 'buttonB', 'tabindex' => '5')); ?>
        </div>
        <br />

			<table class="search-area" style="height: 90px; width: 680px">
				<tr>
					<td style="width: 200px; height: 30px;">
						ユーザーコード<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('text_member_code', (!empty($data['member_code'])) ? $data['member_code'] : '', 
	                    array('class' => 'input-text', 'type' => 'text', 'id' => 'text_member_code', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '5', 'tabindex' => '5')); ?>
	                    <?php echo Form::hidden('member_code', (!empty($data['member_code'])) ? $data['member_code'] : '');?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					氏名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('full_name', (!empty($data['full_name'])) ? $data['full_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'full_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '6')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					ふりがな<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('name_furigana', (!empty($data['name_furigana'])) ? $data['name_furigana'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'name_furigana', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '15', 'tabindex' => '7')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					メールアドレス</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('mail_address', (!empty($data['mail_address'])) ? $data['mail_address'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'mail_address', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '8')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					ユーザ名</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('text_user_id', (!empty($data['user_id'])) ? $data['user_id'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'text_user_id', 'style' => 'width:140px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '9')); ?>
	                    <?php echo Form::hidden('user_id', (!empty($data['user_id'])) ? $data['user_id'] : '');?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
					ユーザ権限</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo Form::select('user_authority', $data['user_authority'], $user_permission,
				            array('class' => 'select-item', 'id' => 'user_authority', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '10')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様紐付け
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php // echo Form::input('customer_code', (!empty($data['customer_code'])) ? $data['customer_code'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'customer_code', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '5', 'tabindex' => '11')); ?>
                        <?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code']:'');?>
                        <?php echo Form::hidden('customer_name', (!empty($data['customer_name'])) ? $data['customer_name']:'');?>
                        <?php echo Form::hidden('customer_type', (!empty($data['customer_type'])) ? $data['customer_type']:'');?>
                        <input type="button" name="s_client" value="お客様検索" class='buttonA' tabindex="15" onclick="onCustomerSearch('<?php echo Uri::create('search/s0010'); ?>', 0)" />
                        <span style="margin-left: 20px;margin-right: 10px;font-weight: bold;"><?php echo (!empty($data['customer_code'])) ? $data['customer_code'] : ''; ?></span>
                        <span style="font-weight: bold;"><?php echo (!empty($data['customer_name'])) ? $data['customer_name'] : ''; ?></span>
                        <br />
	                    <i class='fa fa-asterisk' style="color:#FF4040;font-size:12px;margin-top: 5px;"> お客様情報と紐付ける場合にご選択ください</i>
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
