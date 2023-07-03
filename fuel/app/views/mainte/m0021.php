<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'inputForm', 'name' => 'inputForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Form::hidden('company_radio', null);?>
        <?php echo Form::hidden('l_client_company_name', $data['l_client_company_name']);?>
        <?php echo Asset::js('mainte/m0021.js');?>
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
                        <?php echo Form::label('新規', 'CompanyR1'); ?>
                        <?php echo Form::radio('company_radio', 2, $data['company_radio'] == '2', 
                        array('id' => 'form_CompanyR2', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('既存', 'CompanyR2'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        得意先会社名
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('company_name', (!empty($data['company_name'])) ? $data['company_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'company_name', 'style' => 'width:130px;', 'maxlength' => '8', 'tabindex' => '1')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        得意先会社コード
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('client_company_code', (!empty($data['client_company_code'])) ? $data['client_company_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'client_company_code', 'style' => 'width:90px;', 'min' => '0', 'max' => '99999', 'tabindex' => '2', 'disabled')); ?>
                        <input type="button" id="company_search" class="buttonA" value="検索" tabindex="2" onclick="companySearch('<?php echo Uri::create('search/s0021'); ?>')" disabled/>
                        <?php echo $data['l_client_company_name'];?>
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