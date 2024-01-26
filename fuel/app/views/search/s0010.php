<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 500px">
            <tbody>
                <tr>
                    <td style="width: 150px; height: 30px;">お客様番号</td>
                    <td style="width: 350px; height: 30px;">
                        <?php echo Form::input('customer_code', (!empty($data['customer_code'])) ? $data['customer_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'customer_code', 'style' => 'width:80px;', 'min' => '0', 'max' => '99999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">氏名</td>
                    <td style="width: 350px; height: 30px;">
                        <?php echo Form::input('customer_name', (!empty($data['customer_name'])) ? $data['customer_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'customer_name', 'style' => 'width:180px;', 'maxlength' => '10', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">ふりがな</td>
                    <td style="width: 350px; height: 30px;">
                        <?php echo Form::input('customer_name_kana', (!empty($data['customer_name_kana'])) ? $data['customer_name_kana'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'customer_name_kana', 'style' => 'width:240px;', 'maxlength' => '15', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">メールアドレス</td>
                    <td style="width: 450px; height: 30px;">
                        <?php echo Form::input('mail_address', (!empty($data['mail_address'])) ? $data['mail_address'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'mail_address', 'style' => 'width:160px;', 'maxlength' => '50', 'tabindex' => '4')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 150px; height: 30px;">勤務先</td>
                    <td style="width: 450px; height: 30px;">
                        <?php echo Form::input('office_name', (!empty($data['office_name'])) ? $data['office_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'office_name', 'style' => 'width:160px;', 'maxlength' => '12', 'tabindex' => '6')); ?>
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
                <table class="table-inq" style="width: 1020px">
                    <tr>
                        <th style="width: 60px">選択</th>
                        <th style="width: 110px">お客様番号</th>
                        <th style="width: 180px">氏名</th>
                        <!-- <th style="width: 150px">ふりがな</th> -->
                        <th style="width: 160px">メールアドレス</th>
                        <th style="width: 160px">勤務先</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                            <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
                            <?php echo Form::submit('select', '選択', array('class' => 'buttonA', 'onclick' => '')); ?>
                            <?php echo Form::hidden('select_code', $val['customer_code']);?>
                            <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
                            <?php echo Form::close(); ?></td>
                            <td style="width: 110px"><?php echo $val['customer_code']; ?></td>
                            <td style="width: 180px;font-size:14px"><?php echo $val['customer_name']; ?></td>
                            <!-- <td style="width: 150px;font-size:14px"><?php echo $val['customer_name_kana']; ?></td> -->
                            <td style="width: 160px;font-size:14px"><?php echo (empty($val['mail_address'])) ? "-" : $val['mail_address']; ?></td>
                            <td style="width: 160px;font-size:14px"><?php echo (empty($val['office_name'])) ? "-" : $val['office_name']; ?></td>
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