<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Form::hidden('unit_code', $data['unit_code']);?>
        <?php echo Asset::js('mainte/m0025.js');?>
        <script>
            var processing_msg1 = '<?php echo Config::get('m_MI0002'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_MI0003'); ?>';
        </script>

        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■ユニット情報</label>
        <table class="search-area" style="width: 480px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        予約タイプ
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('schedule_type', $data['schedule_type'], $schedule_type_list,
                            array('class' => 'select-item', 'id' => 'schedule_type', 'style' => 'width: 100px', 'onchange' => 'change()', 'tabindex' => '1')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        ユニット名
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('unit_name', (!empty($data['unit_name'])) ? $data['unit_name'] : '', array('class' => 'input-text', 'type' => 'text', 'id' => 'unit_name', 'style' => 'width:180px;', 'maxlength' => '8', 'tabindex' => '1')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        顧客表示フラグ
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('disp_flg', $data['disp_flg'], $disp_flg_list,
                            array('class' => 'select-item', 'id' => 'disp_flg', 'style' => 'width: 100px', 'onchange' => 'change()', 'tabindex' => '1')); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('back', '戻　　る', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('update', '更　　新', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkUpdate()', 'tabindex' => '901')); ?>
            <?php echo Form::submit('delete', '削　　除', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkDelete()', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
	</div>
</section>
