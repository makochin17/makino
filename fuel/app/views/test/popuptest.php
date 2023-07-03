<section id="banner" style="padding-top:10px;">
	<div class="content" style="margin-top:0px;">
		<div style="margin:20px 0px 15px 10px;">
            <h1>動作確認用ページ</h1>
    </div>
    <?php echo Form::open(array('id' => 'searchForm', 'name' => 'searchForm', 'action' => '', 'method' => 'post', 'class' => 'form-stacked','enctype'=>"multipart/form-data")); ?>
    <?php echo Form::hidden(\Config::get('security.csrf_token_key'), \Security::fetch_token());?>
      <div style="text-align:left;margin-top:10px;font-size:16px;">
          検索系：
        <input type="button" value="社員検索"
    onclick="
        var callback_id = 'callback_s0010'; //適当にIDをふる
        window[callback_id] = function(){ //windowにコールバックを登録
            //コールバック時処理
            document.searchForm.submit();
            //alert('callback');
        }
        //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
        window.open('<?php echo Uri::create('search/s0010/s0010'); ?>', callback_id, 'width=1500,height=700');
    "/>
        <input type="button" value="得意先検索"
    onclick="
        var callback_id = 'callback_s0020'; //適当にIDをふる
        window[callback_id] = function(){ //windowにコールバックを登録
            //コールバック時処理
            document.searchForm.submit();
            //alert('callback');
        }
        //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
        window.open('<?php echo Uri::create('search/s0020/s0020'); ?>', callback_id, 'width=700,height=700');
    "/>
        <input type="button" value="庸車先検索"
    onclick="
        var callback_id = 'callback_s0030'; //適当にIDをふる
        window[callback_id] = function(){ //windowにコールバックを登録
            //コールバック時処理
            document.searchForm.submit();
            //alert('callback');
        }
        //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
        window.open('<?php echo Uri::create('search/s0030/s0030'); ?>', callback_id, 'width=800,height=700');
    "/>
        <input type="button" value="車種検索"
    onclick="
        var callback_id = 'callback_s0040'; //適当にIDをふる
        window[callback_id] = function(){ //windowにコールバックを登録
            //コールバック時処理
            document.searchForm.submit();
            //alert('callback');
        }
        //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
        window.open('<?php echo Uri::create('search/s0040/s0040'); ?>', callback_id, 'width=700,height=700');
    "/>
        <input type="button" value="車両検索"
    onclick="
        var callback_id = 'callback_s0050'; //適当にIDをふる
        window[callback_id] = function(){ //windowにコールバックを登録
            //コールバック時処理
            document.searchForm.submit();
            //alert('callback');
        }
        //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
        window.open('<?php echo Uri::create('search/s0050/s0050'); ?>', callback_id, 'width=1000,height=700');
    "/>
        <input type="button" value="商品検索"
    onclick="
        var callback_id = 'callback_s0060'; //適当にIDをふる
        window[callback_id] = function(){ //windowにコールバックを登録
            //コールバック時処理
            document.searchForm.submit();
            //alert('callback');
        }
        //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
        window.open('<?php echo Uri::create('search/s0060/s0060'); ?>', callback_id, 'width=700,height=700');
    "/>
        <input type="button" value="通知検索"
    onclick="
        var callback_id = 'callback_s0070'; //適当にIDをふる
        window[callback_id] = function(){ //windowにコールバックを登録
            //コールバック時処理
            document.searchForm.submit();
            //alert('callback');
        }
        //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
        window.open('<?php echo Uri::create('search/s0070/s0070'); ?>', callback_id, 'width=1700,height=700');
    "/>
        <input type="button" value="配車検索" onclick="location.href='<?php echo Uri::create('search/s0080/s0080'); ?>'"/>
        <input type="button" value="売上補正検索" onclick="location.href='<?php echo Uri::create('search/s0090/s0090'); ?>'"/>
      </div>
      
      <div style="text-align:left;margin-top:10px;font-size:16px;">
          マスタメンテ系：
          <input type="button" value="車種マスタメンテ" onclick="location.href='<?php echo Uri::create('mainte/m0040/m0040'); ?>'"/>
          <input type="button" value="車両マスタメンテ" onclick="location.href='<?php echo Uri::create('mainte/m0050/m0050'); ?>'"/>
          <input type="button" value="商品マスタメンテ" onclick="location.href='<?php echo Uri::create('mainte/m0060/m0060'); ?>'"/>
          <input type="button" value="通知データメンテ" onclick="location.href='<?php echo Uri::create('mainte/m0070/m0070'); ?>'"/>
          <?php echo password_hash("gorogoro", PASSWORD_DEFAULT); ?>
          <?php echo password_verify('gorogoro', '$2y$10$HAXGj/cuQ9V2XnczNyAb7euG9iWUYQPRX3Gz.qIlKujY6QH5JKrt.'); ?>
      </div>
    <?php echo Form::close(); ?>
	</div>
</section>