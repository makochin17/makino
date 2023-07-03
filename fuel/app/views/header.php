<header id="header" style="padding-top:5em;">

    <div style="display:block;position:absolute;text-align:right;width:100%;margin-top:-50px;" class="logout-button">
        <?php echo Html::anchor(\Uri::create('auth/c0020'), 'ログアウト'); ?>
    </div>
    <div class="page-id"><?php echo $page_id; ?></div>
    <h2 class="page-title"><?php echo $header_title; ?></h2>
</header>
