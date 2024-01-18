<?php use \Model\Common\closingdate; ?>
<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('mode', $mode);?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 130px; height: 30px;">車両番号</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('car_code', (!empty($data['car_code'])) ? $data['car_code'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_code', 'style' => 'width:130px;', 'maxlength' => '10', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">車種</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('car_name', (!empty($data['car_name'])) ? $data['car_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'car_name', 'style' => 'width:130px;', 'maxlength' => '250', 'tabindex' => '2')); ?></td>
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
                <table class="table-inq" style="width: 1400px">
                    <tr>
                        <th style="width: 60px">選択</th>
                        <th style="width: 140px">車両番号</th>
                        <th style="width: 180px">車種</th>
                        <th style="width: 180px">お客様</th>
                        <th style="width: 180px">所有者</th>
                        <th style="width: 180px">使用者</th>
                        <th style="width: 100px">旧車両ID</th>
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
                            <td style="width: 140px"><?php echo $val['car_code']; ?></td>
                            <td style="width: 180px"><?php echo $val['car_name']; ?></td>
                            <td style="width: 180px"><?php echo (empty($val['customer_name'])) ? "-" : $val['customer_name']; ?></td>
                            <td style="width: 180px"><?php echo (empty($val['owner_name'])) ? "-" : $val['owner_name']; ?></td>
                            <td style="width: 180px"><?php echo (empty($val['consumer_name'])) ? "-" : $val['consumer_name']; ?></td>
                            <td style="width: 100px"><?php echo (empty($val['old_car_id'])) ? "-" : $val['old_car_id']; ?></td>
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