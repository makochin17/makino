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
        <?php echo Form::hidden('mode', (!empty($data['mode'])) ? $data['mode']:1); ?>

        <?php echo Form::hidden('select_record', null);?>
        <script>
            var clear_msg 		= '<?php echo Config::get('m_CI0005'); ?>';
            var error_msg1 		= '<?php echo Config::get('m_MW0013'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_CUS001'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_CUS002'); ?>';
            var processing_msg3 = '<?php echo Config::get('m_CUS003'); ?>';
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
			<table class="search-area" style="height: 90px; width: 680px">
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo Form::select('customer_type', $data['customer_type'], $customer_type_list,
				            array('class' => 'select-item', 'id' => 'customer_type', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '1')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                    <?php echo Form::input('customer_code', (!empty($data['customer_code'])) ? $data['customer_code'] : '', 
                    array('class' => 'input-text', 'type' => 'text', 'id' => 'customer_code', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '2')); ?></td>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('customer_name', (!empty($data['customer_name'])) ? $data['customer_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'customer_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '500', 'tabindex' => '3')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様名かな<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('customer_name_kana', (!empty($data['customer_name_kana'])) ? $data['customer_name_kana'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'customer_name_kana', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '500', 'tabindex' => '4')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						郵便番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('zip', (!empty($data['zip'])) ? $data['zip'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'zip', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '5')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						住所１<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('addr1', (!empty($data['addr1'])) ? $data['addr1'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'addr1', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '6')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						住所２
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('addr2', (!empty($data['addr2'])) ? $data['addr2'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'addr2', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '7')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						電話番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('tel', (!empty($data['tel'])) ? $data['tel'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'tel', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '8')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						FAX番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('fax', (!empty($data['fax'])) ? $data['fax'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'fax', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '9')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						携帯番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('mobile', (!empty($data['mobile'])) ? $data['mobile'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'mobile', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '10')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						メールアドレス<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('mail_address', (!empty($data['mail_address'])) ? $data['mail_address'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'mail_address', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '11')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						勤務先名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('office_name', (!empty($data['office_name'])) ? $data['office_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'office_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '12')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						担当者名
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('manager_name', (!empty($data['manager_name'])) ? $data['manager_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'manager_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '13')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						生年月日
					</td>
					<td style="width: 480px; height: 30px;">
						<?php echo Form::input('birth_date', (!empty($data['birth_date'])) ? $data['birth_date']:'', array('type' => 'date', 'id' => 'birth_date','style' => 'width: 140px;','class' => 'input-date','maxlength' => '20','tabindex' => '14')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						性別
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo Form::select('sex', $data['sex'], $sex_list,
				            array('class' => 'select-item', 'id' => 'sex', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '15')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						退会フラグ
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo Form::select('resign_flg', $data['resign_flg'], $resign_flg_list,
				            array('class' => 'select-item', 'id' => 'resign_flg', 'style' => 'width: 170px', 'onchange' => 'changeResignFlg()', 'tabindex' => '16')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						退会日
					</td>
					<td style="width: 480px; height: 30px;">
						<?php echo Form::input('resign_date', (!empty($data['resign_date'])) ? $data['resign_date']:'', array('type' => 'date', 'id' => 'resign_date','style' => 'width: 140px;','class' => 'input-date','maxlength' => '20','tabindex' => '17')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						退会理由
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('resign_reason', (!empty($data['resign_reason'])) ? $data['resign_reason'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'resign_reason', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '18')); ?>
					</td>
				</tr>
			</table>
			<br />
        <div class="search-buttons">
            <?php echo Form::submit('back', '戻　　　る', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkBack()', 'tabindex' => '900')); ?>
            <?php echo Form::submit('execution', '確　　　定', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution(1)', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
	</div>
</section>
