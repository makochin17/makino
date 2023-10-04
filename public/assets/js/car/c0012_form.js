// フォームデータ処理
$(function(){

    var zeropadding = function(num, len) {
        return ('0000000000' + num).slice(-len);
    }

    // =================================================
    // 車両情報入力
    // =================================================
    // 残溝数
    $('input[id^=summer_tire_remaining_groove], input[id^=winter_tire_remaining_groove]').on('focus input',function () {
        if ($(this).val().length > 0) {
            var num = $(this).val().replace(/[^\d.]/g, '');
            $(this).val(num);
        }
    }).on('blur',function (e) {
        var num     = $(this).val();
        var float   = parseFloat(num).toFixed(6);
        var res     = float.split('.');
        if (float.length > 6) {
            if (res[1] == '00') {
                $(this).val(Number(float).toLocaleString() + '.00');
            } else {
                $(this).val(Number(float).toLocaleString(undefined, { maximumFractionDigits: 6 }));
            }
        } else {
            if (num.length > 0) {
                $(this).val(float.toLocaleString(undefined, { maximumFractionDigits: 6 }));
            }
        }
        return e.preventDefault();
    });

    // ページスーパーリロード
    var sp_reload = function() {

        if (document.URL.indexOf("#")==-1) {
            // url = document.URL+"?car_id="+car_id+"&"+(new Date()).getTime();
            // window.location.href = url;
            // location = "#";
            // window.location.href = window.location.href;
            location.reload();
        }

    }

    $('[id^=img_url]').click(function(e){
        window.open($(this).attr('data-url'), "WindowName","width=900,height=700,resizable=yes,scrollbars=yes");
        // window.open($(this).data('url'), "WindowName","width=900,height=700,resizable=yes,scrollbars=yes");
        return e.preventDefault();
    });

    /* ======== アップロード処理用 ======== */
    /* クリックでファイル選択を起動 */
    $('[id^=btnUpload]').click(function(e){

        var no      = $(this).data('id');
        var car_id  = $('[name=car_id]').val();
        // 未入力チェック
        if (!car_id) {
            return confirm("車両IDが存在していません");
        }
        // 重複チェック
        var file_data = $('input[name=fileUpload'+no+']').val();
        $('span#file_data_err'+no).html('');

        if (car_id) {
            // 更新
            $.ajax({
                type: "POST",
                url: $('[name=check_url]').val(),
                data: {
                    "car_id":car_id
                },
                success: function(res){
                    if (res == 0) {
                        $('span#file_data_err'+no).html('車両情報が存在しません');
                    } else {
                        if (!file_data) {
                            $('span#file_data_err'+no).html('ファイルが選択されていません');
                        } else {
                            // ファイルアップロード
                            if (no > 4) {
                                var file_id = (no - 4);
                            } else {
                                var file_id = no;
                            }
                            $('input[name=file_id]').val(file_id);
                            $('form[id=fileform'+no+']').submit();
                            $('span#file_data_err'+no).html('ファイルをアップロードしました');
                            // 2秒後にリロード
                            setTimeout(function(){
                                sp_reload();
                            }, 2000);
                        }
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    return false;
                }
            });

            return e.preventDefault();
        }

        e.preventDefault();
        return false;
    });

    /* ======== アップロード・エクスポートdivの開閉 ======== */
    $('[id^=fileUpload]').change(function(e){

        var car_id = $('[name=car_id]').val();
        // 未入力チェック
        if (!car_id) {
            return confirm("車両IDを入力してください");
        }
        // 重複チェック
        if (check_id(car_id)) {
            return confirm("車両IDが既に存在しています");
        }

        $('[name=file_id]').val(car_id);

        $('#fileform').submit();
        $(this).val('');
        // return e.preventDefault();

    });

});
