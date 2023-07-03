// Enterキーによるsubmit無効化
document.onkeypress = enter;
function enter(){
    if( window.event.keyCode == 13 ){
        return false;
    }
}

$(function(){

    // 配車入力モーダル戻る処理
    $('#dispatch_back').click(function(e){

        window.location.href = $('input[name=list_url]').val();

        return e.preventDefault();
    });

    // 雛形ファイル出力
    $('#output').click(function(e){

        $('#excelForm').submit();

        return e.preventDefault();
    });

});
