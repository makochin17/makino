<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 580px">
            <tbody>
                <tr>
                    <td style="width: 130px; height: 30px;">通知番号</td>
                    <td style="width: 450px; height: 30px;">
                        <?php echo Form::input('notice_number', (!empty($data['notice_number'])) ? $data['notice_number'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'notice_number', 'style' => 'width:90px;', 'min' => '0', 'max' => '99999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">課</td>
                    <td style="width: 450px; height: 30px;">
                        <?php echo Form::select('division', $data['division'], $division_list,
                        array('class' => 'select-item', 'id' => 'division', 'style' => 'width: 150px', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">役職</td>
                    <td style="width: 450px; height: 30px;">
                        <?php echo Form::select('position', $data['position'], $position_list,
                        array('class' => 'select-item', 'id' => 'position', 'style' => 'width: 150px', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">通知日付</td>
                    <td style="width: 450px; height: 30px;">
                        <?php echo Form::input('notice_date', (!empty($data['notice_date'])) ? $data['notice_date'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'notice_date', 'style' => 'width:160px;', 'tabindex' => '4')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">通知タイトル</td>
                    <td style="width: 450px; height: 30px;">
                        <?php echo Form::input('notice_title', (!empty($data['notice_title'])) ? $data['notice_title'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'notice_title', 'style' => 'width:300px;', 'maxlength' => '20', 'tabindex' => '5')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">通知範囲</td>
                    <td style="width: 450px; height: 30px;">
                        <?php echo Form::input('notice_start', (!empty($data['notice_start'])) ? $data['notice_start'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'notice_start', 'style' => 'width:160px;', 'tabindex' => '6')); ?>
                        &emsp;～&emsp;
                        <?php echo Form::input('notice_end', (!empty($data['notice_end'])) ? $data['notice_end'] : '', 
                        array('class' => 'input-text', 'type' => 'date', 'id' => 'notice_end', 'style' => 'width:160px;', 'tabindex' => '7')); ?>
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
                <table class="table-inq" style="width: 1580px">
                    <tr>
                        <th style="width: 60px">選択</th>
                        <th style="width: 90px">通知番号</th>
                        <th style="width: 120px">課</th>
                        <th style="width: 190px">役職</th>
                        <th style="width: 100px">通知日付</th>
                        <th style="width: 340px">通知タイトル</th>
                        <th style="width: 400px">通知メッセージ</th>
                        <th style="width: 100px">通知開始日</th>
                        <th style="width: 100px">通知終了日</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                            <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
                            <?php echo Form::submit('select', '選択', array('class' => 'buttonA', 'onclick' => '')); ?>
                            <?php echo Form::hidden('select_code', $val['notice_number']);?>
                            <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
                            <?php echo Form::close(); ?></td>
                            <td style="width: 90px"><?php echo sprintf('%05d', $val['notice_number']); ?></td>
                            <td style="width: 120px"><?php echo (empty($val['division_name'])) ? "-" : $val['division_name']; ?></td>
                            <td style="width: 190px"><?php echo (empty($val['position_name'])) ? "-" : $val['position_name']; ?></td>
                            <td style="width: 100px"><?php $notice_date = new DateTime($val['notice_date']);echo $notice_date->format('Y/m/d'); ?></td>
                            <td style="width: 340px"><?php echo $val['notice_title']; ?></td>
                            <td style="width: 400px;white-space: normal"><?php echo $val['notice_message']; ?></td>
                            <td style="width: 100px"><?php $notice_start = new DateTime($val['notice_start']);echo $notice_start->format('Y/m/d'); ?></td>
                            <td><?php $notice_end = new DateTime($val['notice_end']);echo $notice_end->format('Y/m/d'); ?></td>
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