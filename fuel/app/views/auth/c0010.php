<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('auth/c0010.js');?>
        <script>
            var clear_msg = '<?php echo Config::get('m_CI0005'); ?>';
            var processing_msg1 = '<?php echo Config::get('m_MI0001'); ?>';
            var processing_msg2 = '<?php echo Config::get('m_MI0002'); ?>';
            var processing_msg3 = '<?php echo Config::get('m_MI0003'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>

        <table class="search-area" style="width: 500px">
            <tbody>
                <tr>
                    <td style="width: 200px; height: 30px;">ログインID</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('userid', (!empty($data['userid'])) ? $data['userid'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'userid', 'style' => 'width:240px;', 'minlength' => '6', 'maxlength' => '10', 'placeholder' => 'ログインIDを入力してください', 'tabindex' => '1')); ?></td>
                    </td>
                </tr>
                <tr>
                    <td style="width: 200px; height: 30px;">パスワード</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('password', (!empty($data['password'])) ? $data['password'] : '', 
                        array('class' => 'input-text', 'type' => 'password', 'id' => 'password', 'style' => 'width:240px;', 'minlength' => '6', 'maxlength' => '14', 'placeholder' => 'パスワードを入力してください', 'tabindex' => '2')); ?></td>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('execution', 'ログイン', array('class' => 'buttonB', 'id' => 'login', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>