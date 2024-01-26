<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 130px; height: 30px;">車種コード</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('car_model_code', (!empty($data['car_model_code'])) ? $data['car_model_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'car_model_code', 'style' => 'width:70px;', 'min' => '0', 'max' => '999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">車種名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('car_model_name', (!empty($data['car_model_name'])) ? $data['car_model_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_model_name', 'style' => 'width:110px;', 'maxlength' => '5', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">トン数</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('tonnage', (!empty($data['tonnage'])) ? $data['tonnage'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'tonnage', 'style' => 'width:80px;', 'min' => '0', 'max' => '99', 'step' => '0.1', 'tabindex' => '3')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">集約トン数</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('aggregation_tonnage', (!empty($data['aggregation_tonnage'])) ? $data['aggregation_tonnage'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'aggregation_tonnage', 'style' => 'width:80px;', 'min' => '0', 'max' => '99', 'step' => '0.1', 'tabindex' => '4')); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('cancel', 'キャンセル', array('class' => 'buttonB', 'tabindex' => '901')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php if ($total > 0) : ?>
            <div class="content-row">
                検索結果：<?php echo $total; ?> 件
            </div>
            <!-- ここからPager -->
            <div>
                <?php echo $pager; ?>
            </div>
            <!-- ここまでPager -->
            <div class="table-wrap">
                <table class="table-inq" style="width: 650px">
                    <tr>
                        <th style="width: 60px">選択</th>
                        <th style="width: 100px">車種コード</th>
                        <th style="width: 100px">車種名</th>
                        <th style="width: 100px">トン数</th>
                        <th style="width: 100px">集約トン数</th>
                        <th style="width: 100px">積載トン数</th>
                        <th>ソート順</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                            <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
                            <?php echo Form::submit('select', '選択', array('class' => 'buttonA', 'onclick' => '')); ?>
                            <?php echo Form::hidden('select_code', $val['car_model_code']);?>
                            <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
                            <?php echo Form::close(); ?></td>
                            <td style="width: 100px"><?php echo sprintf('%03d', $val['car_model_code']); ?></td>
                            <td style="width: 100px"><?php echo $val['car_model_name']; ?></td>
                            <td style="width: 100px"><?php echo $val['tonnage']; ?></td>
                            <td style="width: 100px"><?php echo $val['aggregation_tonnage']; ?></td>
                            <td style="width: 100px"><?php echo $val['freight_tonnage']; ?></td>
                            <td><?php echo $val['sort']; ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif ; ?>
                </table>
            </div>
            <!-- ここからPager -->
            <div>
                <?php echo $pager; ?>
            </div>
            <!-- ここまでPager -->
        <?php endif ; ?>
    </div>
</section>