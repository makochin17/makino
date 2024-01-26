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

    /* ======== アップロード処理用 ======== */

    /* クリックでファイル選択を起動 */
    $('#btnUpload').click(function(e){

        // var importMenu = $('#importMenu').val();

        // if (importMenu != '') {

        //     switch(importMenu) {
        //         case 'carrierreturn':   // 1:キャリアー返送
        //         case 'lost':            // 2:紛失
        //         case 'malfunction':     // 3:故障
        //         case 'deterioration':   // 4:劣化
        //         case 'pause':           // 5:休止
        //         case 'resumption':      // 6:再開通
        //         case 'discard':         // 7:破棄
        //         case 'doneimport':
        //         case 'cancelimport':
        //             var fileUpload = $(this).data('trigger');
        //             $(fileUpload).click();
        //             break;
        //         default:
        //             $('form').submit();
        //             break;
        //     }

        // }

        var fileUpload = $(this).data('trigger');
        $(fileUpload).click();
        // $('form').submit();

        e.preventDefault();/* 以降のクリックイベント処理をさせない */

    });

    /* ======== アップロード・エクスポートdivの開閉 ======== */

    $('#fileUpload').change(function(e){

        // $('#excelForm').attr('action', $('input[name=export_url]').val());

        $('#excelForm').submit();
        $(this).val('');
        return e.preventDefault();

    });

});
