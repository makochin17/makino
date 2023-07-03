<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 480px">
            <tbody>
                <tr>
                    <td style="width: 130px; height: 30px;">車両コード</td>
                    <td style="width: 350px; height: 30px;">
                        <?php echo Form::input('car_code', (!empty($data['car_code'])) ? $data['car_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'car_code', 'style' => 'width:80px;', 'min' => '0', 'max' => '9999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">車種</td>
                    <td style="width: 350px; height: 30px;">
                        <?php echo Form::select('car_model', $data['car_model'], $car_model_list,
                        array('class' => 'select-item', 'id' => 'car_model', 'style' => 'width: 130px', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">車両名</td>
                    <td style="width: 350px; height: 30px;">
                        <?php echo Form::input('car_name', (!empty($data['car_name'])) ? $data['car_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_name', 'style' => 'width: 300px;', 'maxlength' => '20', 'tabindex' => '3')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">車両番号</td>
                    <td style="width: 350px; height: 30px;">
                        <?php echo Form::input('car_number', (!empty($data['car_number'])) ? $data['car_number'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_number', 'style' => 'width:170px;', 'maxlength' => '12', 'tabindex' => '4')); ?>
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
                <table class="table-inq" style="width: 830px">
                    <tr>
                        <th style="width: 60px">選択</th>
                        <th style="width: 100px">車両コード</th>
                        <th style="width: 120px">車種</th>
                        <th style="width: 370px">車両名</th>
                        <th>車両番号</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                            <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
                            <?php echo Form::submit('select', '選択', array('class' => 'buttonA', 'onclick' => '')); ?>
                            <?php echo Form::hidden('select_code', $val['car_code']);?>
                            <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
                            <?php echo Form::close(); ?></td>
                            <td style="width: 100px"><?php echo sprintf('%04d', $val['car_code']); ?></td>
                            <td style="width: 120px"><?php echo $val['car_model_name']; ?></td>
                            <td style="width: 370px"><?php echo $val['car_name']; ?></td>
                            <td><?php echo $val['car_number']; ?></td>
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