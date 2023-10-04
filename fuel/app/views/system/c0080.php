<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Form::hidden('id', (!empty($data['id'])) ? $data['id'] : '');?>
        <?php echo Form::hidden('mode', (!empty($data['mode'])) ? $data['mode']:1); ?>
        <?php echo Asset::js('system/c0080.js');?>
        <script>
            var clear_msg = '<?php echo Config::get('m_CI0005'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_CO0001'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_CO0002'); ?>';
            var processing_msg3 = '<?php echo Config::get('m_CO0003'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <br />
        <table class="search-area" style="width: 680px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">運営会社名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('company_name', (!empty($data['company_name'])) ? $data['company_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'company_name', 'style' => 'width:320px;', 'tabindex' => '1')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">システム名<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('system_name', (!empty($data['system_name'])) ? $data['system_name'] : 'タイヤハウスまきの予約管理システム', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'system_name', 'style' => 'width:320px;', 'tabindex' => '2')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">営業開始時間<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('start_time', (!empty($data['start_time'])) ? date('H:i', strtotime($data['start_time'])) : '', 
                        array('class' => 'input-text', 'type' => 'time', 'id' => 'start_time', 'style' => 'width:240px;', 'tabindex' => '3')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">営業終了時間<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('end_time', (!empty($data['end_time'])) ? date('H:i', strtotime($data['end_time'])) : '', 
                        array('class' => 'input-text', 'type' => 'time', 'id' => 'end_time', 'style' => 'width:240px;', 'tabindex' => '4')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">夏タイヤ残溝警告（赤表示）数</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('summer_tire_warning', (!empty($data['summer_tire_warning'])) ? $data['summer_tire_warning'] : 0.00, 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_warning', 'style' => 'width:240px;', 'tabindex' => '5')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">夏タイヤ残溝注意（黄色表示）数</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('summer_tire_caution', (!empty($data['summer_tire_caution'])) ? $data['summer_tire_caution'] : 0.00, 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'summer_tire_caution', 'style' => 'width:240px;', 'tabindex' => '6')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">冬タイヤ残溝警告（赤表示）数</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('winter_tire_warning', (!empty($data['winter_tire_warning'])) ? $data['winter_tire_warning'] : 0.00, 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_warning', 'style' => 'width:240px;', 'tabindex' => '7')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">冬タイヤ残溝注意（黄色表示）数</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('winter_tire_caution', (!empty($data['winter_tire_caution'])) ? $data['winter_tire_caution'] : 0.00, 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'winter_tire_caution', 'style' => 'width:240px;', 'tabindex' => '8')); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('input_clear', '入力内容クリア', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkClear()', 'tabindex' => '900')); ?>
            <?php echo Form::submit('execution', '確　　　　定', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution(1)', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>