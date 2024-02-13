// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

// 検索窓のチェックボックス
function check(element_id){
    var f = document.forms["searchForm"];
    if (f.element_id.checked) {
alert('checkbox is checked.');
        f.element_id.checked = true;
    } else {
alert('checkbox is not checked.');
        f.element_id.checked = false;
    }
}

// 「入力項目クリア」の確認ダイアログ表示
function submitChkClear(kind) {

    var flag = true;
    if (!kind) {
        var flag = window.confirm (clear_msg);
    }
    return flag;
}

// チェックボックス全選択
function allChecked(){
    var f   = document.forms["selectForm"];
    var ids = f.all_logistics_ids.value;
    for (var i=1; i<=list_count; i++){
        document.getElementById('form_print_status_' + i).checked = true;
        f.print_status_id.value = ids;
    }
}

// チェックボックス全解除
function allUncheck(){
    var f   = document.forms["selectForm"];
    for (var i=1; i<=list_count; i++){
        if (!document.getElementById('form_print_status_' + i).disabled) {
            document.getElementById('form_print_status_' + i).checked = false;
            f.print_status_id.value = '';
        }
    }
}

// 画面遷移ボタン押下時処理
function onJump(url_str) {
    var f = document.forms["searchForm"];
    f.processing_division.value = '1';
    f.method = "GET";
    f.action = url_str;
    f.submit();

    return true;
}

// お客様検索ボタン押下時処理
function onCustomerSearch(url_str) {

    var callback_id = 'callback_s0010';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1500,height=700');
}

// 車両検索ボタン押下時処理（車両番号）
function onCarCodeSearch(url_str) {

    var callback_id = 'callback_s0020';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1500,height=700');
}

// 車両検索ボタン押下時処理（車種）
function onCarNameSearch(url_str) {

    var callback_id = 'callback_s0030';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1500,height=700');
}

$(function(){

    // 一括登録ボタン
    $('#import_regist').click(function(e){

        window.location.href = $('input[name=import_url]').val();

        return e.preventDefault();
    });

    // 雛形ファイル出力ボタン
    $('#output_format').click(function(e){

        window.location.href = $('input[name=output_url]').val();

        return e.preventDefault();
    });

    // キャンセルフラグチェックボックス
    $('input[id=form_cancel_flg]').on('change', function(e) {
        if($(this).prop('checked')){
            // チェックボックスにチェックが入ったときに処理
            $(this).val('YES');
        }else{
            // チェックボックスのチェックがはずれたときの処理
            $(this).val('NO');
        }

        return e.preventDefault();
    });

    // 持込みフラグチェックボックス
    $('input[id=form_carry_flg]').on('change', function(e) {
        if($(this).prop('checked')){
            // チェックボックスにチェックが入ったときに処理
            $(this).val('YES');
        }else{
            // チェックボックスのチェックがはずれたときの処理
            $(this).val('NO');
        }

        return e.preventDefault();
    });

});

