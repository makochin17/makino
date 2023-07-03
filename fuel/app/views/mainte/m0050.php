<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0050.js');?>
        <script>
            var clear_msg = '<?php echo Config::get('m_CI0005'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_MI0001'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_MI0002'); ?>';
            var processing_msg3 = '<?php echo Config::get('m_MI0003'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        処理区分&emsp;
        <?php echo Form::select('processing_division', $data['processing_division'], $processing_division_list,
            array('class' => 'select-item', 'id' => 'processing_division', 'style' => 'width: 80px', 'onchange' => 'change()', 'tabindex' => '1')); ?>
        <br />
        <div style="padding-top:10px;">
            <input type="button" value="検索" class='buttonB' tabindex="2" onclick="carSearch('<?php echo Uri::create('search/s0050'); ?>')"/>
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'onclick' => 'return submitChkClear()' , 'tabindex' => '3')); ?>
            <?php echo Form::submit('excel', 'エクセル出力', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'tabindex' => '4')); ?>
            <?php //echo Form::submit('csv_capture', 'CSV取込', array('class' => 'buttonB', 'tabindex' => '4')); ?>
        </div>
        <br />
        <table class="search-area" style="width: 680px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">車両コード<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('car_code_text', (!empty($data['car_code'])) ? $data['car_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'car_code_text', 'style' => 'width:80px;', 'min' => '0', 'max' => '9999', 'tabindex' => '5')); ?></td>
                        <?php echo Form::hidden('car_code', null);?>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車種<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::select('car_model_code', $data['car_model_code'], $car_model_list,
                        array('class' => 'select-item', 'id' => 'car_model_code', 'style' => 'width: 130px', 'tabindex' => '6')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車両名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('car_name', (!empty($data['car_name'])) ? $data['car_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_name', 'style' => 'width: 300px;', 'maxlength' => '20', 'tabindex' => '7')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車両番号<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('car_number', (!empty($data['car_number'])) ? $data['car_number'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_number', 'style' => 'width:190px;', 'maxlength' => '12', 'tabindex' => '8')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('execution', '確定', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>