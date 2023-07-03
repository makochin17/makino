<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0060.js');?>
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
            <input type="button" value="検索" class='buttonB' tabindex="2" onclick="productSearch('<?php echo Uri::create('search/s0060'); ?>')"/>
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()' , 'tabindex' => '3')); ?>
            <?php echo Form::submit('csv_capture', 'CSV取込', array('class' => 'buttonB', 'tabindex' => '4')); ?>
        </div>
        <br />
        <table class="search-area" style="width: 580px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">商品コード<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('product_code_text', (!empty($data['product_code'])) ? $data['product_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'product_code_text', 'style' => 'width:80px;', 'min' => '0', 'max' => '9999', 'tabindex' => '5')); ?></td>
                        <?php echo Form::hidden('product_code', null);?>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">商品名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('product_name', (!empty($data['product_name'])) ? $data['product_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'product_name', 'style' => 'width: 180px;', 'maxlength' => '10', 'tabindex' => '6')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">分類<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('category', $data['category'], $category_list,
                        array('class' => 'select-item', 'id' => 'category', 'style' => 'width: 180px', 'tabindex' => '7')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">ソート順</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('sort', (!empty($data['sort'])) ? $data['sort'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'sort', 'style' => 'width:80px;', 'min' => '0', 'max' => '9999', 'tabindex' => '8')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('execution', '確定', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>