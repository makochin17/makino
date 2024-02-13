<?php $auth_data        = Session::get('auth_data'); ?>
<?php $user_authority   = !empty($auth_data['user_authority']) ? $auth_data['user_authority'] : 0; ?>
<?php $customer_code    = !empty($auth_data['customer_code']) ? $auth_data['customer_code'] : 0; ?>

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
          <span class="opener">お客様メニュー</span>
          <ul>
            <?php if (!empty($customer_code)) : ?>
              <li><?php echo Html::anchor(\Uri::create('schedule/s1020?init=1'), '予約状況一覧'); ?></li>
            <?php endif; ?>
            <li><?php echo Html::anchor(\Uri::create('schedule/s0012'), '予約スケジュール'); ?></li>
            <li><?php echo Html::anchor(\Uri::create('schedule/s0013'), '配達予約スケジュール'); ?></li>
            <?php if (!empty($customer_code)) : ?>
              <li><?php echo Html::anchor(\Uri::create('car/c0020'), '車両情報'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('customer/c0020'), 'お客様情報'); ?></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php if (in_array($user_authority, array(1, 2))) : ?>
          <li>
            <span class="opener">入出庫管理</span>
            <ul>
              <li><?php echo Html::anchor(\Uri::create('logistics/l0010'), '入出庫一覧'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('logistics/l0011'), '入庫入力'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('logistics/l0013'), '出庫指示'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('logistics/l0012'), '出庫入力'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('location/l0010'), '保管場所検索'); ?></li>
            </ul>
          </li>
          <li>
            <span class="opener">社内管理</span>
            <ul>
              <li><?php echo Html::anchor(\Uri::create('schedule/s1010'), '予約状況一覧'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('schedule/s0010'), '予約スケジュール'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('schedule/s0011'), '配達予約スケジュール'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('car/c0010'), '車両情報'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('customer/c0010'), 'お客様情報'); ?></li>
            </ul>
          </li>
          <li>
            <span class="opener">マスタ管理</span>
            <ul>
              <li><?php echo Html::anchor(\Uri::create('mainte/m0011'), 'ユーザーマスタ'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('mainte/m0020'), 'ユニットマスタ'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('mainte/m0030'), '保管場所リレーション'); ?></li>
            </ul>
          </li>
        <?php endif; ?>
        <li>
          <span class="opener">システム管理</span>
          <ul>
            <?php if (in_array($user_authority, array(1, 2))) : ?>
              <li><?php echo Html::anchor(\Uri::create('system/c0080'), '会社情報設定'); ?></li>
              <li><?php echo Html::anchor(\Uri::create('system/c0050'), 'カレンダー休日設定'); ?></li>
            <?php endif; ?>
            <li><?php echo Html::anchor(\Uri::create('auth/c0030'), 'パスワード変更'); ?></li>
          </ul>
        </li>
      </ul>
    </nav>
  </div>
</div>