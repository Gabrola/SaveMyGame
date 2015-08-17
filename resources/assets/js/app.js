(function($){
    function updateTooltips()
    {
        $('[title]').qtip({
            style: 'qtip-tipsy',
            position: {
                my: 'top center',
                at: 'bottom center'
            }
        });
    }

    function updateZeroClipboard()
    {
        var $copyCommand = $('.copy-button');

        if($copyCommand.length > 0) {
            ZeroClipboard.config({swfPath: $copyCommand.data('zclip-path')});
            var clip = new ZeroClipboard($copyCommand);

            clip.on("ready", function () {
                $('#global-zeroclipboard-html-bridge').attr('title', 'Copy Command');
                updateTooltips();

                this.on("copy", function (event) {
                    var clipboard = event.clipboardData;
                    var copyElement = $(event.target).data('copy-element');
                    clipboard.setData("text/plain", $('#' + copyElement).val());
                });

                this.on("aftercopy", function (event) {
                    Materialize.toast('Command copied!', 2500);
                });
            });
        }
    }

    $(function(){

        $('.button-collapse').sideNav();
        $(".dropdown-button").dropdown({ belowOrigin: true, constrain_width: false });
        $('.modal-trigger:not(.disabled)').leanModal();

        updateTooltips();

        $('a[href="#"], a.disabled').on('click', function(event){
            event.preventDefault();
        });

        $('.server-choice').on('click', function(event){
            event.preventDefault();

            var region = $(this).data('region');

            $('.current-server').html(region);
            $('#search_region').val(region);
        });

        $(window).on('resize scroll load', function(){
            $stick = $('.stick-bottom');
            if($stick.length > 0){
                var bottomButton = $stick.offset().top + $stick.outerHeight();
                var footerBottom = $('.page-footer').offset().top;
                var space = $(window).height() - ($stick.offset().top - $(window).scrollTop()) - $stick.outerHeight();

                if(bottomButton + space >= footerBottom){
                    var space2 = $(window).height() - ($('.page-footer').offset().top - $(window).scrollTop()) + 20;
                    $stick.css('position', 'fixed').css('bottom', space2 + 'px');
                } else {
                    $stick.css('position', 'fixed').css('bottom', '20px');
                }
            }
        });

        updateZeroClipboard();

        $('.record-button').on('click', function(event){
            event.preventDefault();

            if($(this).hasClass('disabled'))
                return;

            $(this).addClass('disabled');

            $.ajax({
                url: $(this).attr('href'),
                type: 'GET',
                dataType: 'json',
                success: function(response){
                    Materialize.toast('Summoner games will be recorded automatically!', 5000);
                }
            });
        });

        $('#games-container').on('click', '.load-more-matches', function(event){
            event.preventDefault();

            if($(this).hasClass('disabled'))
                return;

            $button = $(this);
            $button.addClass('disabled').html('<i class="mdi mdi-navigation-refresh mdi-spin right"></i>Load More');

            $.ajax({
                url: $(this).attr('href'),
                type: 'GET',
                dataType: 'html',
                success: function(response){
                    $button.remove();
                    $('#games-container').append(response);
                    updateTooltips();
                }
            });
        });

        $('.record-form-submit').on('click', function(event){
            event.preventDefault();

            if($(this).hasClass('disabled'))
                return;

            $('#record-form').submit();
        });

        $('#record-form').on('submit', function(event){
            event.preventDefault();

            $('.begin-record-submit').addClass('disabled')
                .html('<i class="mdi mdi-navigation-refresh mdi-spin right"></i>Please wait');

            $.ajax({
                url: $(this).attr('action'),
                type: 'post',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(response){
                    $('.record-form-area').html('<div class="col s12"><p>Please rename one of your rune pages to <strong>'+ response.code +'</strong> and it will be confirmed within one minute. After it is confirmed you are free to rename your rune page.</p></div>')
                },
                error: function(data){
                    var errors = $.parseJSON(data.responseText);

                    $.each(errors, function(index, value) {
                        Materialize.toast('Error: ' + value.join(' '), 5000);
                    });

                    if(typeof grecaptcha != 'undefined'){
                        grecaptcha.reset();
                    }

                    $('.begin-record-submit').removeClass('disabled')
                        .html('Begin');
                }
            });
        });

        if($('.event-table').length > 0) {
            $('table.event-table').scrollTableBody({rowsToDisplay: 5});
            $('.jqstb-scroll').slimScroll({
                height: $('.jqstb-scroll').css('height'),
                alwaysVisible: true
            });

            var inBetweenChecks = [];

            function updateTimers(){
                var $checkedBoxes = $('.event-checkbox:checked');
                var firstCheck;
                var firstTime;
                var lastCheck;
                var lastTime;

                if($checkedBoxes.length > 0) {
                    firstCheck = parseInt($checkedBoxes.first().data('event-id'));
                    firstTime = $('#row-event-' + firstCheck).find('td:first-child').text();

                    if($checkedBoxes.length > 1) {
                        lastCheck = parseInt($checkedBoxes.last().data('event-id'));
                        lastTime = $('#row-event-' + lastCheck).find('td:first-child').text();
                    }
                }

                if($checkedBoxes.length > 0) {
                    $('.replay-begin-time').html(firstTime);

                    if($checkedBoxes.length > 1) {
                        $('.replay-end-time').html(lastTime);
                    } else {
                        $('.replay-end-time').html(firstTime);
                    }

                    $('#watch-interval-btn').show();
                } else {
                    $('#watch-interval-btn').hide();
                    $('.replay-begin-time').html('00:00');
                    $('.replay-end-time').html('00:00');
                }
            }

            $('.event-checkbox').on('change', function(){
                if($(this).is(':not(:disabled)')) {
                    var $checkedBoxes = $('.event-checkbox:checked');
                    var firstCheck;
                    var lastCheck;

                    if($checkedBoxes.length > 0) {
                        firstCheck = parseInt($checkedBoxes.first().data('event-id'));

                        if($checkedBoxes.length > 1) {
                            lastCheck = parseInt($checkedBoxes.last().data('event-id'));
                        }
                    }

                    if($(this).is(':checked')) {
                        if($checkedBoxes.length > 1) {
                            inBetweenChecks = [];
                            for (var i = firstCheck + 1; i < lastCheck; i++) {
                                inBetweenChecks.push(i);
                                $('.event-checkbox[data-event-id="' + i + '"]').prop('checked', true).prop('disabled', true);
                            }
                        }
                    } else {
                        $.each(inBetweenChecks, function(index, value){
                            $('.event-checkbox[data-event-id="'+ value +'"]').prop('checked', false).prop('disabled', false);
                        });
                    }

                    updateTimers();
                }
            });

            var checkChampion = 0;

            $('.filter-champion').on('click', function(){
                var playerId = parseInt($(this).data('player-id'));

                if(checkChampion == playerId) {
                    $(this).addClass('hide-shadow');
                    checkChampion = 0;

                    $('.event-tbody > tr').show();
                } else {
                    if(checkChampion != 0)
                        $('.filter-champion[data-player-id="'+checkChampion+'"]').addClass('hide-shadow');

                    $(this).removeClass('hide-shadow');

                    checkChampion = playerId;

                    $('.event-tbody > tr').each(function(){
                        if(parseInt($(this).data('killer')) == checkChampion){
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });

            $('#watch-interval-btn').on('click', function(){
                $button = $(this);

                if($button.is('.disabled'))
                    return;

                $button.addClass('disabled').html('<i class="mdi-navigation-refresh mdi-spin right"></i>Watch Partial Replay');

                var $checkedBoxes = $('.event-checkbox:checked');
                var startTime = $checkedBoxes.first().closest('tr').data('timestamp');
                var endTime = $checkedBoxes.last().closest('tr').data('timestamp');
                var callURL = window.location.href;
                if(callURL.substr(-1) === '/' || callURL.substr(-1) === '#') {
                    callURL = callURL.substr(0, callURL.length - 1);
                }

                callURL += '/events/' + startTime + '/' + endTime;

                $.ajax({
                    url: callURL,
                    type: 'GET',
                    dataType: 'html',
                    success: function(response){
                        $button.removeClass('disabled').html('<i class="mdi-av-videocam left"></i>Watch Partial Replay');
                        $('#partial-modal').html(response).openModal();
                        $('.collapsible').collapsible();
                        updateTooltips();
                        updateZeroClipboard();
                    }
                });
            });
        }

    }); // end of document ready
})(jQuery); // end of jQuery name space