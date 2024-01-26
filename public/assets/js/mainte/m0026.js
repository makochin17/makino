window.onload = function(){
	change();
}

// 項目の活性状態制御
function change() {
	
	//ラジオボタン操作
	var companyRs = document.getElementsByName("company_radio");
	for ( var i=0, i=companyRs.length; i--; ) {
		if ( companyRs[i].checked ) {
			var r_value = companyRs[i].value ;
			break;
		}
	}
	if(r_value == 1) {
		document.getElementById('company_name').disabled = true;
		//document.getElementById('client_company_code').disabled = true;
		//document.getElementById('company_search').disabled = true;
	}
	if(r_value == 2) {
		document.getElementById('company_name').disabled = false;
		//document.getElementById('client_company_code').disabled = false;
		//document.getElementById('company_search').disabled = false;
	}
}

// 検索ボタン押下時処理
function companySearch(url_str) {
    var callback_id = 'callback_s0021'; //IDをふる
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