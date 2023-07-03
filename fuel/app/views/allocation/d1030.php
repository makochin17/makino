<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'excelForm', 'name' => 'excelForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('output_action', 1);?>
        <?php echo Form::hidden('list_url', $list_url);?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■出力条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">種別</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('kind', $data['kind'], $file_list,
                        array('class' => 'select-item', 'id' => 'kind', 'style' => 'width: 150px', 'tabindex' => '1')); ?></td>
                </tr>
            </tbody>
        </table>
        <?php echo Form::close(); ?>
        <div class="search-buttons">
          <?php echo Form::submit('output', '雛形ファイル出力', array('id' => 'output', 'class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
          <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'dispatch_back', 'tabindex' => '901')); ?>
        </div>
    </div>
</section>