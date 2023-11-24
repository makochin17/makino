// お客様検索ボタン押下時処理
function onCustomerSearch(url_str) {

    var callback_id = 'callback_s0010';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.head_form.select_record.value = '1';
        document.head_form.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    // window.open(url_str, callback_id, 'width=1200,height=1200');
    window.open(url_str, callback_id, 'width=1200,height=600');
}

// フォームデータ処理
$(function(){

    // 検索ボタンクリック
    $('[id^=search_button1]').click(function(e){
        window.open($(this).attr('data-url'), "WindowName","width=500,height=500,resizable=yes,scrollbars=yes");
        // window.open($(this).data('url'), "WindowName","width=900,height=700,resizable=yes,scrollbars=yes");
        return e.preventDefault();
    });

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

});
