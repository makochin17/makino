<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('summary/t0060.js');?>
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
                    <td style="width: 150px; height: 30px;">ドライバー</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::radio('member_radio', 1, $data['member_radio'] != '2', 
                        array('id' => 'form_memberR1', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('全て', 'memberR1'); ?>
                        <br />
                        <?php echo Form::radio('member_radio', 2, $data['member_radio'] == '2', 
                        array('id' => 'form_memberR2', 'onchange' => 'change(this)')); ?>
                        <?php echo Form::label('指定', 'memberR2'); ?>
                        &emsp;
                        <?php echo Form::input('member_code', (!empty($data['member_code'])) ? $data['member_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'member_code', 'style' => 'width:100px;', 'min' => '0', 'max' => '99999', 'tabindex' => '2', 'disabled')); ?>
                        <input type="button" id="member_search" class="buttonA" value="検索" tabindex="2" onclick="clientSearch('<?php echo Uri::create('search/s0010'); ?>')" disabled/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">集計開始日</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('start_date', (!empty($data['start_date'])) ? date('Y-m-d', strtotime($data['start_date'])):'', array('type' => 'date', 'id' => 'start_date','class' => 'input-date','tabindex' => '3')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">集計終了日</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::input('end_date', (!empty($data['end_date'])) ? date('Y-m-d', strtotime($data['end_date'])):'', array('type' => 'date', 'id' => 'end_date','class' => 'input-date','tabindex' => '4')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">運賃出力</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::radio('fare_radio', 1, $data['fare_radio'] != '2', 
                        array('id' => 'form_fareR1')); ?>
                        <?php echo Form::label('あり', 'fareR1', ); ?>
                        &emsp;
                        <?php echo Form::radio('fare_radio', 2, $data['fare_radio'] == '2', 
                        array('id' => 'form_fareR2')); ?>
                        <?php echo Form::label('なし', 'fareR2'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('output', '出力', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
        </div>
        <?php echo Form::close(); ?>
    </div>
</section>