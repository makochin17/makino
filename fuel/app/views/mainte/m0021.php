<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'inputForm', 'name' => 'inputForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('mainte/m0021.js');?>
        <script>
            var clear_msg       = '<?php echo Config::get('m_CI0005'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_MI0001'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■ユニット情報</label>
        <table class="search-area" style="width: 480px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        ユニット名
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('unit_name', (!empty($data['unit_name'])) ? $data['unit_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'unit_name', 'style' => 'width:130px;', 'maxlength' => '8', 'tabindex' => '1')); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('back', '戻　　る', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('execution', '登　　録', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>