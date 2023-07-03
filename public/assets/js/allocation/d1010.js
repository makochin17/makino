// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

// 売上確定のチェックボックス全選択
function allChecked(){
	for (var i=1; i<=list_count; i++){
		document.getElementById('form_sales_status_' + i).checked = true;
	}
}

// 売上確定のチェックボックス全解除
function allUncheck(){
	for (var i=1; i<=list_count; i++){
		if (!document.getElementById('form_sales_status_' + i).disabled) {
			document.getElementById('form_sales_status_' + i).checked = false;
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
function onEdit(url_str, dispatch_number) {
    var f = document.forms["searchForm"];
    f.processing_division.value = '2';
    f.dispatch_number.value = dispatch_number;
    f.select_record .value = 1;
    f.method = "POST";
    f.action = url_str;
    f.submit();
    
    return true;
}

// 削除ボタン押下時処理
function onDelete(dispatch_number) {
	//削除確認
	var dispatch_number_p = ('0000000000' + dispatch_number).slice(-10);
	var flag = window.confirm(processing_msg1.replace('XXXXX', dispatch_number_p));
	if (!flag)return false;
	
    var f = document.forms["selectForm"];
    f.processing_division.value = '3';
    f.dispatch_number.value = dispatch_number;
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

// 得意先検索ボタン押下時処理
function onClientSearch(url_str) {

    var callback_id = 'callback_s0020';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 傭車先検索ボタン押下時処理
function onCarrierSearch(url_str) {

    var callback_id = 'callback_s0030';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 商品検索ボタン押下時処理
function onProductSearch(url_str) {

    var callback_id = 'callback_s0060';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// 車両検索ボタン押下時処理
function onCarSearch(url_str) {

    var callback_id = 'callback_s0050';
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=1000,height=700');
}

// 社員検索ボタン押下時処理
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

});

