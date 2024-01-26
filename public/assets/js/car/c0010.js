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

// 詳細ボタン押下時処理
function onDetail(url_str, car_id) {
    var f = document.forms["selectForm"];
    f.car_id.value = car_id;
    f.mode.value = 'detail';
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
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

// 新規登録ボタン押下時処理
function onAdd(url_str) {
    var f = document.forms["searchForm"];
    f.processing_division.value = '1';
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
}

// 編集ボタン押下時処理
function onEdit(url_str, car_id) {
    var f = document.forms["selectForm"];
    f.processing_division.value = '2';
    f.car_id.value = car_id;
    f.select_record.value = 1;
    f.mode.value = 'list';
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
}

// 削除ボタン押下時処理
function onDelete(car_id) {
	//削除確認
	var flag = window.confirm(processing_msg1.replace('XXXXX', car_id));
	if (!flag)return false;

    var f = document.forms["selectForm"];
    f.processing_division.value = '3';
    f.car_id.value = car_id;
    f.method = "POST";
    f.submit();

    return true;
}

// 更新ボタン押下時処理
function onSalesUpdate() {
	//売上確定の更新確認
	var flag = window.confirm(processing_msg2);
	if (!flag)return false;

    var f = document.forms["selectForm"];
    f.processing_division.value = '4';
    f.method = "POST";
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

    // チェックボックス
    $('input[id=form_warning_flg]').on('change', function(e) {
        if($(this).prop('checked')){
            // チェックボックスにチェックが入ったときに処理
            $(this).val(true);
        }else{
            // チェックボックスのチェックがはずれたときの処理
            $(this).val(false);
        }

        return e.preventDefault();
    });

    $('input[id=form_caution_flg]').on('change', function(e) {
        if($(this).prop('checked')){
            // チェックボックスにチェックが入ったときに処理
            $(this).val(true);
        }else{
            // チェックボックスのチェックがはずれたときの処理
            $(this).val(false);
        }

        return e.preventDefault();
    });


});

