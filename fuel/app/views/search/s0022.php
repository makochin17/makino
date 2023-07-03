<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 130px; height: 30px;">営業所コード</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('client_sales_office_code', (!empty($data['client_sales_office_code'])) ? $data['client_sales_office_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'client_sales_office_code', 'style' => 'width:90px;', 'min' => '0', 'max' => '99999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">営業所名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('sales_office_name', (!empty($data['sales_office_name'])) ? $data['sales_office_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'sales_office_name', 'style' => 'width:130px;', 'maxlength' => '5', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">会社コード</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('client_company_code', (!empty($data['client_company_code'])) ? $data['client_company_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'client_company_code', 'style' => 'width:90px;', 'min' => '0', 'max' => '99999', 'tabindex' => '1')); ?></td>
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
                <table class="table-inq" style="width: 350px">
                    <tr>
                        <th style="width: 60px">選択</th>
                        <th style="width: 120px">営業所コード</th>
                        <th style="width: 160px">営業所名</th>
                        <th style="width: 120px">会社コード</th>
                        <th style="width: 160px">会社名</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                            <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
                            <?php echo Form::submit('select', '選択', array('class' => 'buttonA', 'onclick' => '')); ?>
                            <?php echo Form::hidden('select_code', $val['client_sales_office_code']);?>
                            <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
                            <?php echo Form::close(); ?></td>
                            <td style="width: 120px"><?php echo sprintf('%05d', $val['client_sales_office_code']); ?></td>
                            <td style="width: 160px"><?php echo $val['sales_office_name']; ?></td>
                            <td style="width: 120px"><?php echo sprintf('%05d', $val['client_company_code']); ?></td>
                            <td style="width: 160px"><?php echo $val['company_name']; ?></td>
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