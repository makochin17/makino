<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'excelForm', 'name' => 'excelForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('import_action', 1);?>
        <?php echo Form::hidden('list_url', $list_url);?>
        <?php echo Form::hidden('export_url', $export_url);?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■出力条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">締日</td>
                    <td style="width: 380px; height: 30px;">
                      <?php echo Form::input('closing_date', (!empty($data['closing_date'])) ? $data['closing_date']:'', array('type' => 'date', 'id' => 'closing_date','class' => 'input-date','style' => 'width: 150px;','maxlength' => '20','tabindex' => '1')); ?>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">部署</td>
                    <td style="width: 380px; height: 30px;">
                    <?php echo Form::select('division_code', $data['division_code'], $division_list,
                    array('class' => 'select-item', 'id' => 'division_code', 'style' => 'width: 150px', 'tabindex' => '2')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('execution', '一括登録', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::close(); ?>
            <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'dispatch_back', 'tabindex' => '901')); ?>
        </div>
    </div>
</section>