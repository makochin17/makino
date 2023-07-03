<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'inputForm', 'name' => 'inputForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Form::hidden('company_radio', null);?>
        <?php echo Asset::js('mainte/m0036.js');?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■会社情報</label>
        <table class="search-area" style="width: 480px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        区分
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::radio('company_radio', 1, $data['company_radio'] != '2', 
                        array('id' => 'form_CompanyR1', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('変更なし', 'CompanyR1'); ?>
                        <?php echo Form::radio('company_radio', 2, $data['company_radio'] == '2', 
                        array('id' => 'form_CompanyR2', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('名称変更', 'CompanyR2'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        庸車先会社名
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('company_name', (!empty($data['company_name'])) ? $data['company_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'company_name', 'style' => 'width:130px;', 'maxlength' => '8', 'tabindex' => '1')); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('back', '戻　　る', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('next', '次　　へ', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>