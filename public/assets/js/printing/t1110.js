window.onload = function(){
	change();
}

// 項目の活性状態制御
function change() {
	
	//得意先ラジオボタン操作
	var clientRs = document.getElementsByName("client_radio");
	if(clientRs[1].checked) {
		document.getElementById('client_code').disabled = false;
		document.getElementById('client_search').disabled = false;
	} else {
		document.getElementById('client_code').disabled = true;
		document.getElementById('client_search').disabled = true;
	}
	
	//帳票種別ラジオボタン操作
	var reportRs = document.getElementsByName("report_radio");
	if(reportRs[1].checked) {
		document.getElementById('bill_report').disabled = false;
		document.getElementById('area_code').disabled = false;
	} else {
		document.getElementById('bill_report').disabled = true;
		document.getElementById('area_code').disabled = true;
	}
}

// 検索ボタン押下時処理
function clientSearch(url_str) {
    var callback_id = 'callback_s0020'; //IDをふる
    window[callback_id] = function() { //windowにコールバックを登録
        //コールバック時処理
        document.searchForm.select_record.value = '1';
        document.searchForm.submit();
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

// 項目の活性状態制御
function message_clear() {
	var em = document.getElementById("error_message");
	em.innerHTML = "";
	return true;
}

// 地区プルダウン制御
$(function(){
	$('select[id=area_code]').prop('disabled', true);
	if ($('select[id=bill_report]').val() == 1) {
		$('select[id=area_code]').prop('disabled', false);
	}

	// 帳票種別の変更内容によって地区コードを制御
	$('select[id=bill_report]').on('change', function(e) {
		if ($(this).val() == 1) {
			$('select[id=area_code]').prop('disabled', false);
		} else {
			$('select[id=area_code]').prop('disabled', true);
		}

		return e.preventDefault();
	});
});