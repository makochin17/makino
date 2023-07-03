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
                    <td style="width: 150px; height: 30px;">請求区分</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('kind', $data['kind'], $category_list,
                        array('class' => 'select-item', 'id' => 'kind', 'style' => 'width: 150px', 'tabindex' => '1')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php /* アップロード */ ?>
            <input type="button" id="btnUpload" data-trigger="#fileUpload" class="buttonB" style="margin-right: 20px;" tabindex="900" value="一括登録">
            <input type="file" name="fileUpload" id="fileUpload" style="display:none">
            <?php /* //アップロード */ ?>
            <?php echo Form::close(); ?>
            <?php echo Form::button('back', '戻　　　る', array('type' => 'button', 'class' => 'buttonB', 'id' => 'dispatch_back', 'tabindex' => '901')); ?>
        </div>
    </div>
</section>