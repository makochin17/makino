<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'entryForm', 'name' => 'entryForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('mode', $mode);?>

        <?php if (!empty($mode_msg)) : ?>
            <span style=""><?php echo $mode_msg; ?></span>
        <?php else: ?>
            <span style="">現在のパスワードと、新しく設定するパスワードを入力してパスワードを変更してください</span>
        <?php endif; ?>
        <p class="error-message-head"><?php echo $error_message; ?></p>

        <table class="search-area" style="width: 640px">
            <tbody>
                <tr>
                    <td style="width: 240px; height: 30px;"><i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>現在のパスワード</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('old_password', (!empty($data['old_password'])) ? $data['old_password'] : '', 
                        array('class' => 'input-text', 'type' => 'password', 'id' => 'old_password', 'style' => 'width:320px;', 'minlength' => '8', 'maxlength' => '16', 'tabindex' => '1')); ?></td>
                    </td>
                </tr>
                <tr>
                    <td style="width: 240px; height: 30px;"><i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>新しいパスワード</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('new_password', (!empty($data['new_password'])) ? $data['new_password'] : '', 
                        array('class' => 'input-text', 'type' => 'password', 'id' => 'new_password', 'style' => 'width:320px;', 'minlength' => '8', 'maxlength' => '16', 'tabindex' => '2')); ?></td>
                    </td>
                </tr>
                <tr>
                    <td style="width: 240px; height: 30px;"><i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i>パスワード確認入力</td>
                    <td style="width: 480px; height: 30px;">
                        <?php echo Form::input('new_password_cf', '', 
                        array('class' => 'input-text', 'type' => 'password', 'id' => 'new_password', 'style' => 'width:320px;', 'minlength' => '8', 'maxlength' => '16', 'tabindex' => '3')); ?></td>
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="font-size:14px;margin: -10px 0px 20px 0px">※パスワードは８〜１６文字の半角英数字および記号で入力してください</div>

        <div class="search-buttons">
            <?php echo Form::submit('execution', 'パスワード変更', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'onclick' => 'return submitChkExecution()', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>

<?php if (!empty($modal_flg)) : ?>
    <?php echo Asset::js('auth/c0030.js'); ?>
<?php endif; ?>

<!-- モーダルエリアここから -->
<div id="modalArea" class="modalArea">
  <div id="modalBg" class="modalBg"></div>
  <div class="modalWrapper">
    <div class="modalContents" style="margin:20px 0px 50px 0px;text-align:center;">
      <p><?php echo Config::get("m_CI0002"); ?></p>
    </div>
    <div class="search-buttons" style="text-align:center;">
        <button id="sendModal" class="buttonB" data-href="<?php echo \Uri::create('auth/c0010'); ?>">ログインへ</button>
        <button id="closeModal" class="buttonB">閉じる</button>
    </div>
  </div>
</div>
<!-- モーダルエリアここまで -->
