<?php $auth_data = Session::get('auth_data'); ?>
<?php $permission_level = isset($auth_data[0]) ? $auth_data[0]['permission_level'] : 1; ?>

<div id="sidebar">
  <div class="inner">
    <section id="search" class="alt" style="">
      <?php echo Html::anchor(\Uri::create('top/c0040'), Asset::img('sidemenu.jpg', array('style' => 'width:320px;height:160px;', 'alt' => 'logo')), array('class' => 'logo')); ?>
      <br />
    </section>
    <div style="text-align:right;margin-top:-40px;padding-bottom:10px;border:0px;margin-bottom:10px;">
      ログイン：<?php echo $login_user_name; ?> 殿
    </div>
    <nav id="menu">
      <header class="major">
        <h2>　Menu　　</h2>
      </header>
      <ul>
        <li>
          <span class="opener">配車入力</span>
          <ul>
            <li><?php echo Html::anchor(\Uri::create('allocation/d0011'), '配車入力（チャーター便）'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('allocation/d1010'), '配車入力（共配便）'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('allocation/d0031'), '月極その他情報入力'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('allocation/d0040'), '配車照会（チャーター便）'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('allocation/d1040'), '配車照会（共配便）'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('allocation/d0060'), '月極その他情報照会'); ?></li>
          </ul>
        </li>
        <li>
          <span class="opener">在庫管理</span>
          <ul>
            <li><?php echo Html::anchor(\Uri::create('stock/d1110'), '在庫入力'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('stock/d1130'), '保管料入力'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('stock/d1140'), '在庫照会'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('stock/d1150'), '入出庫照会'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('stock/d1160'), '保管料照会'); ?></li>
          </ul>
        </li>
        <li>
          <span class="opener">請求管理</span>
          <ul>
            <li><?php echo Html::anchor(\Uri::create('bill/b1010?init'), '請求情報入力（共配便）'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('bill/b1040'), '請求情報照会（共配便）'); ?></li>
          </ul>
        </li>
        <li>
          <span class="opener">集計</span>
          <ul>
            <li><?php echo Html::anchor(\Uri::create('summary/t0010'), '課別売上集計'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('summary/t0020'), '得意先別売上集計'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('summary/t0030'), '庸車別売上集計'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('summary/t0040'), '車両別売上集計'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('summary/t0050'), '取扱いトン数集計'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('summary/t0060'), 'ドライバー別売上集計'); ?></li>
          </ul>
        </li>
        <li>
          <span class="opener">各種印刷</span>
          <ul>
            <li><?php echo Html::anchor(\Uri::create('printing/t0070'), '車番案内印刷'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('printing/t0082'), '請求明細印刷'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('printing/t0080'), '売上請求予定明細印刷'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('printing/t0090'), '傭車支払予定明細印刷'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('printing/t1110'), '請求明細印刷（共配便）'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('printing/t1120'), '納品書印刷'); ?></li>
          </ul>
        </li>
        <li>
          <span class="opener">社内システム管理</span>
          <ul>
            <li><?php echo Html::anchor(\Uri::create('customer/c0010'), 'お客様情報'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('car/c0010'), '車両情報'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('schedule/s0010'), '予約スケジュール'); ?></li>
          </ul>
        </li>
        <li>
          <span class="opener">マスタ管理</span>
          <ul>
            <li><?php echo Html::anchor(\Uri::create('mainte/m0010'), 'ユーザーマスタ'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('mainte/m0020'), 'ユニットマスタ'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('mainte/m0030'), '保管場所リレーション'); ?></li>
          </ul>
        </li>
        <li>
          <span class="opener">システム管理</span>
          <ul>
            <li><?php echo Html::anchor(\Uri::create('system/c0080'), '会社情報設定'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('system/m0070'), '通知データメンテナンス'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('system/c0050'), 'カレンダー休日設定'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('auth/c0030'), 'パスワード変更'); ?></li>
          </ul>
        </li>
      </ul>
    </nav>
  </div>
</div>