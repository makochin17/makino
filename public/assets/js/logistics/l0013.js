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

// 入庫シール印刷のチェックボックス全選択
function allChecked(){
    var f   = document.forms["selectForm"];
    var ids = f.all_logistics_ids.value;
    for (var i=1; i<=list_count; i++){
        document.getElementById('form_select_' + i).checked = true;
        f.select_id.value = ids;
    }
}

// 入庫シール印刷のチェックボックス全解除
function allUncheck(){
    var f   = document.forms["selectForm"];
    for (var i=1; i<=list_count; i++){
        if (!document.getElementById('form_select_' + i).disabled) {
            document.getElementById('form_select_' + i).checked = false;
            f.select_id.value = '';
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

// 入庫ボタン押下時処理
function onReceipt(url_str) {
    var f = document.forms["searchForm"];
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
}

// 出庫ボタン押下時処理
function onDelivery(url_str) {
    var f = document.forms["searchForm"];
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
}

// 出庫指示ボタン押下時処理
function onDeliverySchedule() {
    //出庫確定の更新確認
    var flag = window.confirm(processing_msg1);
    if (!flag)return false;

    var f = document.forms["selectForm"];
    f.method = "POST";
    f.mode.value = 'deliveryschedulefix';
    f.submit();

    return true;
}

// 出庫指示書印刷ボタン押下時処理
function onDeliverySchedulePrint(url_str) {
    //入庫シールの印刷確認
    var flag = window.confirm(processing_msg3);
    if (!flag)return false;

    var f = document.forms["selectForm"];
    f.method = "POST";
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
function onEdit(url_str, logistics_id, mode) {
    var f = document.forms["selectForm"];
    // f.processing_division.value = '2';
    f.logistics_id.value = logistics_id;
    f.mode.value = mode;
    f.method = "POST";
    f.action = url_str;
    f.submit();

    return true;
}

// 削除ボタン押下時処理
function onDelete(logistics_id, receipt_date, delivery_date, customer_name, car_code) {
	//削除確認
    var msg    = "\n" + '入庫日 : ' + receipt_date + "\n" + '出庫日 : ' + delivery_date + "\n" + 'お客様名 : ' + customer_name + "\n" + '登録番号 : ' + car_code + "\n";
	var flag   = window.confirm(processing_msg1.replace('XXXXX', msg));
	if (!flag)return false;

    var f = document.forms["selectForm"];
    f.processing_division.value = '3';
    f.logistics_id.value = logistics_id;
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

    // 選択チェックボックス
    $('input[id^=form_select]').on('change', function(e) {

        var prop            = $(this).prop('checked');
        var id              = $(this).attr('id');
        var names           = id.split('_');
        var listno          = names[2];
        // hiddenデータを配列化
        var logistics_ids   = [];
        var str_ids         = $('#select_id').val();
        var logistics_id    = $('#logistics_id_'+listno).val();

        if (str_ids.length > 0) {
            logistics_ids = str_ids.split(',');
        }

        if (!prop) {
            // 指定のIDを削除してhiddenに戻す
            var index = logistics_ids.indexOf(logistics_id);
            logistics_ids.splice(index, 1);
            $('#select_id').val(logistics_ids);
            $('#form_status_'+listno).val(logistics_id).prop("checked", false);
        } else {
            logistics_ids.push(logistics_id);
            $('#select_id').val(logistics_ids);
            $('#form_status_'+listno).val(logistics_id).prop("checked", true);
        }

        return e.preventDefault();
    });

});

