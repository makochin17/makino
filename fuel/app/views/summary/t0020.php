<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('select_record', null);?>
        <?php echo Asset::js('summary/t0020.js');?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■出力条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">集計対象</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('summary_category', $data['summary_category'], $summary_category_list,
                        array('class' => 'select-item', 'id' => 'summary_category', 'style' => 'width: 150px', 'tabindex' => '1')); ?></td>
                </tr>
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
                        <input type="button" id="client_search" class="buttonA" value="検索" tabindex="2" onclick="clientSearch('<?php echo Uri::create('search/s0020'); ?>')" disabled/>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">配送区分</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('delivery_category', $data['delivery_category'], $delivery_category_list,
                        array('class' => 'select-item', 'id' => 'delivery_category', 'style' => 'width: 130px', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">集計単位</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('aggregation_unit_date', $data['aggregation_unit_date'], $aggregation_unit_date_list,
                        array('class' => 'select-item', 'id' => 'aggregation_unit_date', 'style' => 'width: 100px', 'tabindex' => '4', 'onchange' => 'change()')); ?>
                        &emsp;
                        <?php echo Form::select('aggregation_unit_company', $data['aggregation_unit_company'], $aggregation_unit_company_list,
                        array('class' => 'select-item', 'id' => 'aggregation_unit_company', 'style' => 'width: 130px', 'tabindex' => '5')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">集計開始日</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('start_year', $data['start_year'], $year_list,
                        array('class' => 'select-item', 'id' => 'start_year', 'style' => 'width: 100px', 'tabindex' => '6')); ?>
                        年
                        <?php echo Form::select('start_month', $data['start_month'], $month_list,
                        array('class' => 'select-item', 'id' => 'start_month', 'style' => 'width: 80px', 'tabindex' => '7')); ?>
                        月
                        <?php echo Form::select('start_day', $data['start_day'], $day_list,
                        array('class' => 'select-item', 'id' => 'start_day', 'style' => 'width: 80px', 'tabindex' => '8')); ?>
                        日
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">集計終了日</td>
                    <td style="width: 380px; height: 30px;">
                        <?php echo Form::select('end_year', $data['end_year'], $year_list,
                        array('class' => 'select-item', 'id' => 'end_year', 'style' => 'width: 100px', 'tabindex' => '9')); ?>
                        年
                        <?php echo Form::select('end_month', $data['end_month'], $month_list,
                        array('class' => 'select-item', 'id' => 'end_month', 'style' => 'width: 80px', 'tabindex' => '10')); ?>
                        月
                        <?php echo Form::select('end_day', $data['end_day'], $day_list,
                        array('class' => 'select-item', 'id' => 'end_day', 'style' => 'width: 80px', 'tabindex' => '11')); ?>
                        日
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