<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('printing/t0070.js');?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■出力条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">課</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('division', $data['division'], $division_list,
                        array('class' => 'select-item', 'id' => 'division', 'style' => 'width: 150px', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">得意先</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::radio('client_radio', 1, $data['client_radio'] != '2', 
                        array('id' => 'form_ClientR1', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('全て', 'ClientR1'); ?>
                        <br />
                        <?php echo Form::radio('client_radio', 2, $data['client_radio'] == '2', 
                        array('id' => 'form_ClientR2', 'onchange' => 'change()')); ?>
                        <?php echo Form::label('指定', 'ClientR2'); ?>
                        &emsp;
                        <?php echo Form::input('client_code', (!empty($data['client_code'])) ? $data['client_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'client_code', 'style' => 'width:100px;', 'min' => '0', 'max' => '99999', 'tabindex' => '2', 'disabled')); ?>
                        <input type="button" id="client_search" class="buttonA" value="検索" tabindex="9" onclick="clientSearch('<?php echo Uri::create('search/s0020'); ?>')" disabled/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">対象日付<i class='fa fa-asterisk' style="color:#FF4040;font-size:10px;"></i></td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('target_date', (!empty($data['target_date'])) ? $data['target_date'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'target_date', 'style' => 'width:150px;', 'tabindex' => '3')); ?>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('output', '出力', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>