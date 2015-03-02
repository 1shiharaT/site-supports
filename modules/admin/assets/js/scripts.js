;
(function ($) {
    $.fn.multiAccordion = function() {
        $(this).addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
            .find("h3")
            .addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom")
            .hover(function() { $(this).toggleClass("ui-state-hover"); })
            .prepend('<span class="ui-icon ui-icon-triangle-1-e"></span>')
            .click(function() {
                $(this)
                    .toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
                    .find("> .ui-icon").toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s").end()
                    .next().toggleClass("ui-accordion-content-active").slideToggle(100);
                return false;
            })
            .next()
            .addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom")
            .css("display", "block")
            .hide()
            .end().trigger("click");
    };
})(jQuery);

(function ($) {
    /**
     * メッセージを表示
     * @param type メッセージの種類
     */
    var timer;
    var ggsMessage = function (type, message) {
        var messageContainer = $('.ggs-message-' + type);
        if (message) {
            messageContainer.html(message);
        }
        var already = 'message-aleady';
        messageContainer.fadeIn();
        clearTimeout(timer);
        if ( ! messageContainer.hasClass(already)) {
            timer = setTimeout(function () {
                messageContainer.fadeOut();
            }, 800);
        }
        //messageContainer.addClass(already);
    }

    function changeOnHash() {
        var tab_id = location.hash;
        var tabIndexs = {
            '#ggs-basic-setting': 0,
            '#ggs-dashboard-setting': 1,
            '#ggs-admin-menu-setting': 2
        };
        $('#ggs_tabs').tabs(
            'enable', tab_id
        );
    }

    $(function () {
        window.addEventListener("hashchange", changeOnHash, false);
        $('.acoordion').multiAccordion({
            animate: 100,
            autoHeight: false,
            heightStyle: "content"
        });
        $('#ggs_tabs').tabs();
        $('#ggs_tabs ul li a').on('click', function () {
            location.hash = $(this).attr('href');
            window.scrollTo(0, 0);
        });
        $('.form-group-radiobox').buttonset();
        $('#submit').attr('disabled', 'disabled');

    });

    $(document).change('#ggs_settings_form *', function () {
        $('#submit').removeAttr('disabled');
    });

    var flag = true;
    /**
     * 変更を保存時のイベント
     * @return void
     */
    $(document).on('click', '#submit', function (e) {
        e.preventDefault();
        if (false == flag) {
            return false;
        }
        flag = false;
        $('#ggs_tabs ul').find('.spinner').show();
        $.ajax({
            'type': 'post',
            'url': ajaxurl,
            'data': {
                'action': GGSSETTINGS.action,
                '_wp_nonce': GGSSETTINGS._wp_nonce,
                'form': $('#ggs_settings_form').serialize(),
            },
            'success': function (data) {
                if (1 == data) {
                    $('#ggs_tabs ul').find('.spinner').hide();
                    ggsMessage('success');
                    $('#submit').attr('disabled', 'disabled');
                } else {
                    $('#ggs_tabs ul').find('.spinner').hide();
                    ggsMessage('faild');
                }
                flag = true;
            }
        })
    });
    function countReset( target ){
        if ( ! target ){
            return false;
        }
        var target = $('.post-count-' + target);
        target.text('0');
    }
    /**
     * 最適化の実行
     */
    $(document).on('click', '#optimize_submit', function (e) {
        e.preventDefault();
        if (false == flag) {
            return false;
        }
        flag = false;
        $('.run_optimize').find('.spinner').show();
        var nonce = $('#optimize_nonce').val();
        $.ajax({
            'type': 'post',
            'url': ajaxurl,
            'data': {
                'action': 'run_optimize',
                '_wp_optimize_nonce': nonce
            },
            'success': function (data) {
                $('.run_optimize').find('.spinner').hide();
                if (data.status == 'faild') {

                    ggsMessage('faild', '<h3>' + data.html + '</h3>');
                    return false;
                }

                var message;

                if (data.optimize_revision) {
                    message = '<h3>' + data.optimize_revision + '</h3>';
                    countReset('revision');
                }

                if (data.optimize_auto_draft) {
                    message += '<h3>' + data.optimize_auto_draft + '</h3>';
                    countReset('auto_draft');
                }

                if (data.optimize_trash) {
                    message += '<h3>' + data.optimize_trash + '</h3>';
                    countReset('trash');

                }
                if ( message ){
                    ggsMessage('success', message );
                }


                flag = true;
            }
        });

    })

})(jQuery);