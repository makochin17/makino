<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'inputForm', 'name' => 'inputForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Form::hidden('sales_office_radio', null);?>
        <?php echo Form::hidden('l_carrier_sales_office_name', $data['l_carrier_sales_office_name']);?>
        <?php echo Asset::js('mainte/m0032.js');?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■営業所情報</label>
        <table class="search-area" style="width: 450px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        区分
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::radio('sales_office_radio', 1, ($data['sales_office_radio'] == '1' || $data['sales_office_radio'] == ''), 
                        array('id' => 'form_CompanyR1', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('新規', 'CompanyR1'); ?>
                        <?php echo Form::radio('sales_office_radio', 2, $data['sales_office_radio'] == '2', 
                        array('id' => 'form_CompanyR2', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('既存', 'CompanyR2'); ?>
                        <?php echo Form::radio('sales_office_radio', 3, $data['sales_office_radio'] == '3', 
                        array('id' => 'form_CompanyR3', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('営業所なし', 'CompanyR3'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        庸車先営業所名
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('sales_office_name', (!empty($data['sales_office_name'])) ? $data['sales_office_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'sales_office_name', 'style' => 'width:130px;', 'maxlength' => '5', 'tabindex' => '1')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">
                        庸車先営業所コード
                    </td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('carrier_sales_office_code', (!empty($data['carrier_sales_office_code'])) ? $data['carrier_sales_office_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'carrier_sales_office_code', 'style' => 'width:90px;', 'min' => '0', 'max' => '99999', 'tabindex' => '2', 'disabled')); ?>
                        <input type="button" id="sales_office_search" class="buttonA" value="検索" tabindex="2" onclick="salesOfficeSearch('<?php echo Uri::create('search/s0032'); ?>')" disabled/>
                        <?php echo $data['l_carrier_sales_office_name'];?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('back', '戻　　る', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('next', '次　　へ', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '901')); ?>
            <?php echo Form::submit('cancel', 'キャンセル', array('class' => 'buttonB', 'tabindex' => '902')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>