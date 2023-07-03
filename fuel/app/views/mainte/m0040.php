<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0040.js');?>
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
            <input type="button" value="検索" class='buttonB' tabindex="2" onclick="carModelSearch('<?php echo Uri::create('search/s0040'); ?>')"/>
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'onclick' => 'return submitChkClear()' , 'tabindex' => '3')); ?>
            <?php echo Form::submit('excel', 'エクセル出力', array('class' => 'buttonB', 'style' => 'margin-left: 20px;', 'tabindex' => '4')); ?>
            <?php //echo Form::submit('csv_capture', 'CSV取込', array('class' => 'buttonB', 'tabindex' => '4')); ?>
        </div>
        <br />
        <table class="search-area" style="width: 680px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">車種コード<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('car_model_code_text', (!empty($data['car_model_code'])) ? $data['car_model_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'car_model_code_text', 'style' => 'width:80px;', 'min' => '0', 'max' => '999', 'tabindex' => '5')); ?></td>
                        <?php echo Form::hidden('car_model_code', null);?>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">車種名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('car_model_name', (!empty($data['car_model_name'])) ? $data['car_model_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_model_name', 'style' => 'width:150px;', 'maxlength' => '5', 'tabindex' => '6')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">トン数<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('tonnage', (!empty($data['tonnage'])) ? $data['tonnage'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'tonnage', 'style' => 'width:150px;', 'min' => '0', 'max' => '99', 'step' => '0.1', 'tabindex' => '7')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">集約トン数<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('aggregation_tonnage', (!empty($data['aggregation_tonnage'])) ? $data['aggregation_tonnage'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'aggregation_tonnage', 'style' => 'width:150px;', 'min' => '0', 'max' => '99', 'step' => '0.1', 'tabindex' => '8')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">積載トン数<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('freight_tonnage', (!empty($data['freight_tonnage'])) ? $data['freight_tonnage'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'freight_tonnage', 'style' => 'width:150px;', 'min' => '0', 'max' => '99', 'step' => '0.1', 'tabindex' => '9')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">ソート順</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('sort', (!empty($data['sort'])) ? $data['sort'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'sort', 'style' => 'width:80px;', 'min' => '0', 'max' => '999', 'tabindex' => '10')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('execution', '確定', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>