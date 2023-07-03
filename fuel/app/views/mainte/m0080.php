<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0080.js');?>
        <script>
            var clear_msg = '<?php echo Config::get('m_CI0005'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_MI0001'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_MI0002'); ?>';
            var processing_msg3 = '<?php echo Config::get('m_MI0003'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        更新対象：&emsp;
        <?php echo Form::select('division_code', $data['division_code'], $division_list,
                        array('class' => 'select-item', 'id' => 'division_code', 'style' => 'width: 150px', 'onchange' => 'onDivisionSelect();', 'tabindex' => '1')); ?>
        <?php echo Form::hidden('division_code_select', null);?>
        <br /><br />
        <table class="search-area" style="width: 500px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">課コード<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 300px; height: 30px;">
                        <?php echo Form::input('division', (!empty($data['division_code'])) ? $data['division_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'division', 'style' => 'width:80px;', 'min' => '0', 'max' => '999', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">支社<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 300px; height: 30px;">
                        <?php echo Form::select('branch_office_code', $data['branch_office_code'], $branch_office_list,
                        array('class' => 'select-item', 'id' => 'branch_office_code', 'style' => 'width: 130px', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">庸車先コード<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 300px; height: 30px;">
                        <?php echo Form::input('carrier_code', (!empty($data['carrier_code'])) ? $data['carrier_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'carrier_code', 'style' => 'width:80px;', 'min' => '0', 'max' => '99999', 'tabindex' => '4')); ?>
                        <input type="button" name="s_carrier" value="検索" class='buttonA' tabindex="5" onclick="onCarrierSearch('<?php echo Uri::create('search/s0030'); ?>', 0)" />
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">課名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 300px; height: 30px;">
                        <?php echo Form::input('division_name', (!empty($data['division_name'])) ? $data['division_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'division_name', 'style' => 'width: 150px;', 'maxlength' => '6', 'tabindex' => '6')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">専用回線<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 300px; height: 30px;">
                        <?php echo Form::input('private_line_number', (!empty($data['private_line_number'])) ? $data['private_line_number'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'private_line_number', 'style' => 'width:150px;', 'maxlength' => '15', 'tabindex' => '7')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">FAX番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 300px; height: 30px;">
                        <?php echo Form::input('fax_number', (!empty($data['fax_number'])) ? $data['fax_number'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'fax_number', 'style' => 'width:150px;', 'maxlength' => '15', 'tabindex' => '8')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">携帯電話<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 300px; height: 30px;">
                        <?php echo Form::input('mobile_phone_number', (!empty($data['mobile_phone_number'])) ? $data['mobile_phone_number'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'mobile_phone_number', 'style' => 'width:150px;', 'maxlength' => '15', 'tabindex' => '9')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">担当者<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 300px; height: 30px;">
                        <?php echo Form::input('person_in_charge', (!empty($data['person_in_charge'])) ? $data['person_in_charge'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'person_in_charge', 'style' => 'width:150px;', 'maxlength' => '10', 'tabindex' => '10')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('execution', '確定', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>