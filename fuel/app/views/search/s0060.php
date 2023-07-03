<section id="banner" style="padding-top:10px;">
    <div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 130px; height: 30px;">商品コード</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('product_code', (!empty($data['product_code'])) ? $data['product_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'product_code', 'style' => 'width:70px;', 'min' => '0', 'max' => '9999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">商品名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('product_name', (!empty($data['product_name'])) ? $data['product_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'product_name', 'style' => 'width:180px;', 'maxlength' => '10', 'tabindex' => '2')); ?>
                    </td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">分類</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::select('category', $data['category'], $category_list,
                        array('class' => 'select-item', 'id' => 'category', 'style' => 'width: 180px', 'tabindex' => '3')); ?></td>
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
                <table class="table-inq" style="width: 600px">
                    <tr>
                        <th style="width: 60px">選択</th>
                        <th style="width: 100px">商品コード</th>
                        <th style="width: 200px">商品名</th>
                        <th style="width: 110px">分類</th>
                        <th>ソート順</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                            <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
                            <?php echo Form::submit('select', '選択', array('class' => 'buttonA', 'onclick' => '')); ?>
                            <?php echo Form::hidden('select_code', $val['product_code']);?>
                            <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
                            <?php echo Form::close(); ?></td>
                            <td style="width: 100px"><?php echo sprintf('%04d', $val['product_code']); ?></td>
                            <td style="width: 200px"><?php echo $val['product_name']; ?></td>
                            <td style="width: 110px"><?php echo $val['category']; ?></td>
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