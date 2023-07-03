<!-- Content -->
<div class="container-inner">

  <!-- Box1 -->
  <div class="content">
  <!-- ここから -->
<?php /* スマホ専用の画面リンク　ADD Y.Sashow 2017/02/15 */ ?>
<?php if (Agent::is_smartphone()): ?>
     <div class="sp-link">
      <?php echo Html::anchor("airport/receive/dispatchlist", '空港カウンター/発送リスト',  array('class' => 'btn btn-sp-link')); ?>
      <?php echo Html::anchor("return/pouch", '返却/ポーチ返却',  array('class' => 'btn btn-sp-link')); ?>
    </div>
<?php endif; ?>
  <!-- ここまで -->
  </div>
  <!-- //Box1 -->
</div>
<!-- Content -->
