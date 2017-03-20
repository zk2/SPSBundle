$(function () {
    var $img_add = $('<img class="sps-filter-img img_add" src="/bundles/zk2sps/images/new.png" title="Add fliter" alt="Add fliter" />');
    var $img_del = $('<img class="sps-filter-img img_del" src="/bundles/zk2sps/images/delete.png" title="Delete fliter" alt="Delete fliter" />');
    var $td = $('#sps_filter table td');
    $td.find('.sps-field-filter:first').css('margin-left', '50px');
    $td.find('.sps-field-filter:not(:first)').append($img_del).filter(function () {
        return $(this).find('input,select').eq(2).val() == '' &&
            ( !$(this).find('select').eq(1).val().match("NULL") );
    }).hide();
    $td.filter(function () {
        return $(this).find('.sps-field-filter').length > 1
    }).find('.sps-field-filter:first').find('.sps-child-filter:last').append($img_add);

    $('#sps_filter input[type="date"]').each(function() {
        $(this).attr('type', 'text');
    });

    if (window.filterDateParameters !== undefined) {
        $.each(filterDateParameters, function(key, val){
            $('#' + key).datepicker(val);
        });
    }

    $('.img_add').click(function (e) {
        e.preventDefault();
        var $thisTd = $(this).parent().parent().parent();
        $thisTd.find('.sps-field-filter:hidden:first').show();
        if ($thisTd.find('.sps-field-filter:hidden').length == 0) {
            $(this).hide();
        }
    });

    $('.img_del').click(function (e) {
        e.preventDefault();
        $(this).parent().find('.zk2-sps-filter-field').val('');
        $(this).parent().hide();
        $(this).parent().parent().find('.sps-field-filter:first .img_add').show();
    });

    $('.zk-preview, .zk-split').popover();
});
