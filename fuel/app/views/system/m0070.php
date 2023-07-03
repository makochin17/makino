<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Form::hidden('notice_number', (!empty($data['notice_number'])) ? $data['notice_number'] : '');?>
        <?php echo Asset::js('system/m0070.js');?>
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
            <input type="button" value="検索" class='buttonB' tabindex="2" onclick="noticeSearch('<?php echo Uri::create('search/s0070'); ?>')"/>
            <?php echo Form::submit('input_clear', '入力項目クリア', array('class' => 'buttonB', 'onclick' => 'return submitChkClear()' , 'tabindex' => '3')); ?>
            <?php echo Form::submit('csv_capture', 'CSV取込', array('class' => 'buttonB', 'tabindex' => '4')); ?>
        </div>
        <br />
        <table class="search-area" style="width: 680px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">課<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::select('division', $data['division'], $division_list,
                        array('class' => 'select-item', 'id' => 'division', 'style' => 'width: 150px', 'tabindex' => '5')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">役職<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::select('position', $data['position'], $position_list,
                        array('class' => 'select-item', 'id' => 'position', 'style' => 'width: 150px', 'tabindex' => '6')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">通知日付<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('notice_date', (!empty($data['notice_date'])) ? $data['notice_date'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'notice_date', 'style' => 'width:160px;', 'tabindex' => '7')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">通知タイトル</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('notice_title', (!empty($data['notice_title'])) ? $data['notice_title'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'notice_title', 'style' => 'width:300px;', 'maxlength' => '20', 'tabindex' => '8')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">通知メッセージ<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::textarea('notice_message', (!empty($data['notice_message'])) ? $data['notice_message'] : '', 
                        array('id' => 'notice_message', 'style' => 'width: 420px; height: 110px; resize: none; padding: 8px 10px;', 'maxlength' => '100', 'tabindex' => '9')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">通知開始日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('notice_start', (!empty($data['notice_start'])) ? $data['notice_start'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'notice_start', 'style' => 'width:160px;', 'tabindex' => '10')); ?></td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">通知終了日<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('notice_end', (!empty($data['notice_end'])) ? $data['notice_end'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'notice_end', 'style' => 'width:160px;', 'tabindex' => '11')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('execution', '確定', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>