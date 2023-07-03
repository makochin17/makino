<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0025.js');?>
        <script>
            var processing_msg1 = '<?php echo Config::get('m_MI0002'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_MI0003'); ?>';
        </script>
        <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>

        <p class="error-message-head"><?php echo $error_message; ?></p>
        <div style="padding-top:10px;">
            <?php echo Form::submit('hierarchy_edit', '得意先階層編集', array('class' => 'buttonB' , 'tabindex' => '3')); ?>
        </div>
        <br />
			<table class="search-area" style="height: 90px; width: 780px">
				<tr>
					<td style="width: 140px; height: 30px;">
						得意先コード<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::label((!empty($data['client_code'])) ? sprintf('%05d', $data['client_code']):'', 'client_code', array('style' => 'display:inline;')); ?>
	                    <?php echo Form::hidden('client_code', (!empty($data['client_code'])) ? $data['client_code'] : '');?>
					</td>
				</tr>
<!--				<tr>
					<td style="width: 140px; height: 30px;">
						会社コード
					</td>
					<td style="width: 480px; height: 30px;">
						<?php echo Form::label((!empty($data['client_company_code'])) ? $data['client_company_code']:'', 'client_company_code', array('id' => 'client_company_code', 'style' => 'display:inline;')); ?>
					</td>
				</tr>-->
				<tr>
					<td style="width: 140px; height: 30px;">
						会社名
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::label((!empty($data['company_name'])) ? $data['company_name']:'', 'company_name', array('style' => 'display:inline;')); ?>
                        <?php echo Form::hidden('company_name', (!empty($data['company_name'])) ? $data['company_name'] : '');?>
					</td>
				</tr>
<!--				<tr>
					<td style="width: 140px; height: 30px;">
						営業所コード
					</td>
					<td style="width: 480px; height: 30px;">
						<?php echo Form::label((!empty($data['client_sales_office_code'])) ? $data['client_sales_office_code']:'', 'client_sales_office_code', array('id' => 'client_sales_office_code', 'style' => 'display:inline;')); ?>
					</td>
				</tr>-->
				<tr>
					<td style="width: 140px; height: 30px;">
						営業所名
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::label((!empty($data['sales_office_name'])) ? $data['sales_office_name']:'', 'sales_office_name', array('style' => 'display:inline;')); ?>
	                    <?php echo Form::hidden('sales_office_name', (!empty($data['sales_office_name'])) ? $data['sales_office_name'] : '');?>
					</td>
				</tr>
<!--				<tr>
					<td style="width: 140px; height: 30px;">
						部署コード
					</td>
					<td style="width: 480px; height: 30px;">
						<?php echo Form::label((!empty($data['client_department_code'])) ? $data['client_department_code']:'', 'client_department_code', array('id' => 'client_department_code', 'style' => 'display:inline;')); ?>
					</td>
				</tr>-->
				<tr>
					<td style="width: 140px; height: 30px;">
						部署名
					</td>
					<td style="width: 480px; height: 30px;">
                        <?php echo Form::label((!empty($data['department_name'])) ? $data['department_name']:'', 'department_name', array('style' => 'display:inline;')); ?>
	                    <?php echo Form::hidden('department_name', (!empty($data['department_name'])) ? $data['department_name'] : '');?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						締日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
                        区分:
				        <?php echo Form::select('closing_category', (!empty($data['closing_category'])) ? $data['closing_category']:1, $closing_category_list,
				            array('class' => 'select-item', 'id' => 'closing_category', 'style' => 'width: 100px;margin-right: 30px;', 'tabindex' => '9', 'onchange' => 'change()')); ?>
                        1回目:
                        <?php echo Form::select('closing_date_1', $data['closing_date_1'], $closing_date_list1,
				            array('class' => 'select-item', 'id' => 'closing_date_1', 'style' => 'width: 70px;margin-right: 10px;', 'tabindex' => '9')); ?>
                        2回目:
                        <?php echo Form::select('closing_date_2', $data['closing_date_2'], $closing_date_list2,
				            array('class' => 'select-item', 'id' => 'closing_date_2', 'style' => 'width: 70px;margin-right: 10px;', 'tabindex' => '9')); ?>
                        3回目:
                        <?php echo Form::select('closing_date_3', $data['closing_date_3'], $closing_date_list2,
				            array('class' => 'select-item', 'id' => 'closing_date_3', 'style' => 'width: 70px', 'tabindex' => '9')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						基準締日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
						<?php echo Form::radio('criterion_closing_date', 1, ($data['criterion_closing_date'] == '1' || $data['criterion_closing_date'] == ''), 
                        array('id' => 'form_closingR1', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('積日', 'closingR1'); ?>
                        <?php echo Form::radio('criterion_closing_date', 2, $data['criterion_closing_date'] == '2', 
                        array('id' => 'form_closingR2', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('降日', 'closingR2'); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						正式名称<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 640px; height: 30px;">
	                    <?php echo Form::input('official_name', (!empty($data['official_name'])) ? $data['official_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'official_name', 'style' => 'width:620px;', 'minlength' => '1', 'maxlength' => '40', 'tabindex' => '10')); ?>
					</td>
				</tr>
                <tr>
					<td style="width: 140px; height: 30px;">
						正式名称（カナ）
					</td>
					<td style="width: 640px; height: 30px;">
	                    <?php echo Form::input('official_name_kana', (!empty($data['official_name_kana'])) ? $data['official_name_kana'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'official_name_kana', 'style' => 'width:620px;', 'minlength' => '1', 'maxlength' => '60', 'tabindex' => '11')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						郵便番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('postal_code', (!empty($data['postal_code'])) ? $data['postal_code'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'postal_code', 'style' => 'width:100px;', 'minlength' => '1', 'maxlength' => '8', 'onKeyUp' => 'AjaxZip3.zip2addr(this,"","address","address");', 'tabindex' => '12')); ?>
					</td>
				</tr>
				<tr>
					<td style="width: 140px; height: 30px;">
						住所１<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('address', (!empty($data['address'])) ? $data['address'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'address', 'style' => 'width:480px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '13')); ?>
					</td>
				</tr>
                <tr>
					<td style="width: 140px; height: 30px;">
						住所２
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('address2', (!empty($data['address2'])) ? $data['address2'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'address2', 'style' => 'width:480px;', 'minlength' => '1', 'maxlength' => '50', 'tabindex' => '13')); ?>
					</td>
				</tr>
                <tr>
					<td style="width: 140px; height: 30px;">
						電話番号
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('phone_number', (!empty($data['phone_number'])) ? $data['phone_number'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'phone_number', 'style' => 'width:140px;', 'minlength' => '1', 'maxlength' => '15', 'tabindex' => '14')); ?>
					</td>
				</tr>
                <tr>
					<td style="width: 140px; height: 30px;">
						FAX番号
					</td>
					<td style="width: 480px; height: 30px;">
	                    <?php echo Form::input('fax_number', (!empty($data['fax_number'])) ? $data['fax_number'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'fax_number', 'style' => 'width:140px;', 'minlength' => '1', 'maxlength' => '15', 'tabindex' => '15')); ?>
					</td>
				</tr>
                <tr>
					<td style="width: 140px; height: 30px;">
						担当者
					</td>
					<td style="width: 480px; height: 30px;">
	                    姓:
                        <?php echo Form::input('person_in_charge_surname', (!empty($data['person_in_charge_surname'])) ? $data['person_in_charge_surname'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'person_in_charge_surname', 'style' => 'width:120px;margin-right: 20px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '16')); ?>
                        名:
                        <?php echo Form::input('person_in_charge_name', (!empty($data['person_in_charge_name'])) ? $data['person_in_charge_name'] : '', 
    	                array('class' => 'input-text', 'type' => 'text', 'id' => 'person_in_charge_name', 'style' => 'width:120px;', 'minlength' => '1', 'maxlength' => '10', 'tabindex' => '17')); ?>
					</td>
				</tr>
                <tr>
					<td style="width: 140px; height: 30px;">
						担当部署
					</td>
					<td style="width: 480px; height: 30px;">
	                    <table style="width: 480px">
	                    <?php
                        $cnt = 0;
                        //１行あたり4つのチェックボックスを出力
                        foreach ($division_list as $key => $value) {
                            $cnt++;
                            if ($cnt % 4 == 1)echo '<tr style="background: transparent;">';
                            echo '<td style="width: 120px;margin: 0px;padding: 0px">';
                            
                            $division_code = $key;
                            $division_name = $value;
                            echo Form::checkbox('department_in_charge'.$division_code, 1, empty($data['department_in_charge'.$division_code]) ? false : true, array('id' => 'form_department_in_charge'.$division_code, 'class' => 'text'));
                            echo Form::label($division_name, 'department_in_charge'.$division_code, array('style' => 'display:inline;padding-left: 2.2em;padding-top: 0.2em;padding-bottom: 0.6em;margin-right: 20px;'));
                            echo '</td>';
                            
                            if ($cnt % 4 == 0)echo '</tr>';
                        }
                        if ($cnt % 4 != 0)echo '</tr>';
                        ?>
                        </table>
					</td>
				</tr>
                <tr>
					<td style="width: 140px; height: 30px;">
						保管料
					</td>
					<td style="width: 480px; height: 30px;">
	                    金額:
                        <?php echo Form::input('storage_fee', (!empty($data['storage_fee'])) ? $data['storage_fee'] : '',
    	                array('class' => 'input-text', 'type' => 'number', 'id' => 'storage_fee', 'style' => 'width:120px;', 'min' => '0', 'max' => '9999999999', 'tabindex' => '17')); ?>
                        <br>
                        部署:
                        <?php echo Form::select('storage_in_charge', ($data['storage_in_charge'] != '') ? $data['storage_in_charge'] : '0', $division_list_storage,
                        array('class' => 'select-item', 'id' => 'division', 'style' => 'width: 150px', 'tabindex' => '2')); ?>
					</td>
				</tr>
			</table>
			<br />
        <div class="search-buttons">
            <?php echo Form::submit('update', '更　　新', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkUpdate()', 'tabindex' => '900')); ?>
            <?php echo Form::submit('delete', '削　　除', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkDelete()', 'tabindex' => '901')); ?>
            <?php echo Form::submit('back', '戻　　る', array('class' => 'buttonB', 'tabindex' => '902')); ?>
        </div>
        <?php echo Form::close(); ?>
	</div>
</section>
