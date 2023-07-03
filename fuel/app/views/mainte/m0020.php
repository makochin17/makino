<?php use \Model\Common\closingdate; ?>
<section id="banner" style="padding-top:20px;">
	<div class="content" style="margin-top:0px;">
        <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Asset::js('mainte/m0020.js');?>
        <script>
            var processing_msg1 = '<?php echo Config::get('m_MI0014'); ?>';
        </script>
        <p class="error-message-head"><?php echo $error_message; ?></p>
        <label>■検索条件</label>
        <table class="search-area" style="width: 380px">
            <tbody>
                <tr>
                    <td style="width: 130px; height: 30px;">得意先コード</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('client_code', (!empty($data['client_code'])) ? $data['client_code'] : '', 
                        array('class' => 'input-text', 'type' => 'number', 'id' => 'client_code', 'style' => 'width:90px;', 'min' => '0', 'max' => '99999', 'tabindex' => '1')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">会社名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('company_name', (!empty($data['company_name'])) ? $data['company_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'company_name', 'style' => 'width:130px;', 'maxlength' => '8', 'tabindex' => '2')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">営業所名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('sales_office_name', (!empty($data['sales_office_name'])) ? $data['sales_office_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'sales_office_name', 'style' => 'width:90px;', 'maxlength' => '5', 'tabindex' => '3')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">部署名</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('department_name', (!empty($data['department_name'])) ? $data['department_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'department_name', 'style' => 'width:90px;', 'maxlength' => '5', 'tabindex' => '4')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">締日</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::select('closing_date', $data['closing_date'], $closing_date_list,
                        array('class' => 'select-item', 'id' => 'closing_date', 'style' => 'width: 90px', 'tabindex' => '5')); ?>
                    日</td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">正式名称</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('official_name', (!empty($data['official_name'])) ? $data['official_name'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'official_name', 'style' => 'width:200px;', 'maxlength' => '40', 'tabindex' => '6')); ?></td>
                </tr>
                <tr>
                    <td style="width: 130px; height: 30px;">正式名称（カナ）</td>
                    <td style="width: 250px; height: 30px;">
                        <?php echo Form::input('official_name_kana', (!empty($data['official_name_kana'])) ? $data['official_name_kana'] : '', 
                        array('class' => 'input-text', 'type' => 'text', 'id' => 'official_name_kana', 'style' => 'width:200px;', 'maxlength' => '60', 'tabindex' => '7')); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="search-buttons">
            <?php echo Form::submit('search', '検索', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '900')); ?>
            <?php echo Form::submit('excel', 'エクセル出力', array('class' => 'buttonB', 'style' => 'margin-right: 20px;', 'tabindex' => '901')); ?>
            <?php echo Form::submit('add', '新規登録', array('class' => 'buttonB', 'onclick' => 'onAdd(\''.Uri::create('mainte/m0021').'\')', 'tabindex' => '902')); ?>
        </div>
        <?php echo Form::close(); ?>
        <br />
        <?php echo Form::open(array('id' => 'selectForm', 'name' => 'selectForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
        <?php echo Form::hidden('processing_division', '');?>
        <?php echo Form::hidden('client_code', '');?>
        <?php echo Form::hidden('select_record', '');?>
        <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
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
                <table class="table-inq" style="width: 1220px">
                    <tr>
                        <th style="width: 160px">操作</th>
                        <th style="width: 120px">得意先コード</th>
                        <th style="width: 160px">会社名</th>
                        <th style="width: 100px">営業所名</th>
                        <th style="width: 100px">部署名</th>
                        <th style="width: 130px">締日</th>
                        <th>正式名称</th>
                    </tr>
                    <?php if (!empty($list_data)) : ?>
                      <?php foreach ($list_data as $key => $val) : ?>
                        <?php
                        //締日成形
                        $closing_date = closingdate::genClosingDate($val['closing_date'], $val['closing_date_1'], $val['closing_date_2'], $val['closing_date_3']);
                        ?>
                        <tr>
                            <td style="width: 60px; text-align: center;">
                                <button type="button" onclick="onEdit('<?php echo Uri::create('mainte/m0025'); ?>', <?php echo $val['client_code']; ?>)" class="buttonA"><i class='fa fa-edit' style="font-size:14px;"></i> 編集</button>
                                <button type="button" onclick="onDelete(<?php echo $val['client_code']; ?>)" class="buttonA"><i class='fa fa-trash' style="font-size:15px;"></i> 削除</button>
                            </td>
                            <td style="width: 120px"><?php echo sprintf('%05d', $val['client_code']); ?></td>
                            <td style="width: 160px"><?php echo $val['company_name']; ?></td>
                            <td style="width: 100px"><?php echo (empty($val['sales_office_name'])) ? "-" : $val['sales_office_name']; ?></td>
                            <td style="width: 100px"><?php echo (empty($val['department_name'])) ? "-" : $val['department_name']; ?></td>
                            <td><?php echo $closing_date; ?>日</td>
                            <td style="white-space: normal;"><?php echo $val['official_name']; ?></td>
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
