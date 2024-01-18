<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php if (!empty($error_message)) : ?>
	        <p class="error-message-head"><?php echo $error_message; ?></p>
	        <br />
        <?php else: ?>
			<table class="search-area" style="height: 90px; width: 680px">
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様区分<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['customer_type'])) ? $data['customer_type']:'不明'; ?>
						<?php echo Form::hidden('customer_type', (isset($customer_type_value_list[$data['customer_type']])) ? $customer_type_value_list[$data['customer_type']]:'individual'); ?>
				        <?php // echo Form::select('customer_type', $data['customer_type'], $customer_type_list, array('class' => 'select-item', 'id' => 'customer_type', 'style' => 'width: 170px', 'onchange' => 'change()', 'tabindex' => '1')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['customer_code'])) ? $data['customer_code']:''; ?>
						<?php echo Form::hidden('customer_code', (!empty($data['customer_code'])) ? $data['customer_code']:''); ?>
	                    <?php // echo Form::input('customer_code', (!empty($data['customer_code'])) ? $data['customer_code'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'customer_code', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '2')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['customer_name'])) ? $data['customer_name']:''; ?>
						<?php echo Form::hidden('customer_name', (!empty($data['customer_name'])) ? $data['customer_name']:''); ?>
	                    <?php // echo Form::input('customer_name', (!empty($data['customer_name'])) ? $data['customer_name'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'customer_name', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '500', 'tabindex' => '3')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						お客様名かな<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['customer_name_kana'])) ? $data['customer_name_kana']:''; ?>
						<?php echo Form::hidden('customer_name_kana', (!empty($data['customer_name_kana'])) ? $data['customer_name_kana']:''); ?>
	                    <?php // echo Form::input('customer_name_kana', (!empty($data['customer_name_kana'])) ? $data['customer_name_kana'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'customer_name_kana', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '500', 'tabindex' => '4')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						郵便番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['zip'])) ? $data['zip']:''; ?>
						<?php echo Form::hidden('zip', (!empty($data['zip'])) ? $data['zip']:''); ?>
	                    <?php // echo Form::input('zip', (!empty($data['zip'])) ? $data['zip'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'zip', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '5')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						住所１<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['addr1'])) ? $data['addr1']:''; ?>
						<?php echo Form::hidden('addr1', (!empty($data['addr1'])) ? $data['addr1']:''); ?>
	                    <?php // echo Form::input('addr1', (!empty($data['addr1'])) ? $data['addr1'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'addr1', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '6')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						住所２
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['addr2'])) ? $data['addr2']:''; ?>
						<?php echo Form::hidden('addr2', (!empty($data['addr2'])) ? $data['addr2']:''); ?>
	                    <?php // echo Form::input('addr2', (!empty($data['addr2'])) ? $data['addr2'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'addr2', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '7')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						電話番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['tel'])) ? $data['tel']:''; ?>
						<?php echo Form::hidden('tel', (!empty($data['tel'])) ? $data['tel']:''); ?>
	                    <?php // echo Form::input('tel', (!empty($data['tel'])) ? $data['tel'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'tel', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '8')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						FAX番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['fax'])) ? $data['fax']:''; ?>
						<?php echo Form::hidden('fax', (!empty($data['fax'])) ? $data['fax']:''); ?>
	                    <?php // echo Form::input('fax', (!empty($data['fax'])) ? $data['fax'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'fax', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '9')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						携帯番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['mobile'])) ? $data['mobile']:''; ?>
						<?php echo Form::hidden('mobile', (!empty($data['mobile'])) ? $data['mobile']:''); ?>
	                    <?php // echo Form::input('mobile', (!empty($data['mobile'])) ? $data['mobile'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'mobile', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '10')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 200px; height: 30px;">
						メールアドレス<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
				        <?php echo (!empty($data['mail_address'])) ? $data['mail_address']:''; ?>
						<?php echo Form::hidden('mail_address', (!empty($data['mail_address'])) ? $data['mail_address']:''); ?>
	                    <?php // echo Form::input('mail_address', (!empty($data['mail_address'])) ? $data['mail_address'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'mail_address', 'style' => 'width:300px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '11')); ?>
					</td>
				</tr>
			</table>
        <?php endif; ?>
        <?php echo Form::close(); ?>
	</div>
</section>
