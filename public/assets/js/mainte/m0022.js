window.onload = function(){
	change();
}

// 項目の活性状態制御
function change() {
	
	//ラジオボタン操作
	var sales_officeRs = document.getElementsByName("sales_office_radio");
	for ( var i=0, i=sales_officeRs.length; i--; ) {
		if ( sales_officeRs[i].checked ) {
			var r_value = sales_officeRs[i].value ;
			break;
		}
	}
	
	if(r_value == 1) {
		document.getElementById('sales_office_name').disabled = false;
		document.getElementById('client_sales_office_code').disabled = true;
		document.getElementById('sales_office_search').disabled = true;
	}
	if(r_value == 2){
		document.getElementById('sales_office_name').disabled = true;
		document.getElementById('client_sales_office_code').disabled = false;
		document.getElementById('sales_office_search').disabled = false;
	}
	if(r_value == 3){
		document.getElementById('sales_office_name').disabled = true;
		document.getElementById('client_sales_office_code').disabled = true;
		document.getElementById('sales_office_search').disabled = true;
	}
}

// 検索ボタン押下時処理
function salesOfficeSearch(url_str) {
    var callback_id = 'callback_s0022'; //IDをふる
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.inputForm.select_record.value = '1';
        document.inputForm.submit();
    }
    //第2引数でcallbackのID名を渡す。子画面側では window.name として取得できる。
    window.open(url_str, callback_id, 'width=700,height=700');
}

// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}