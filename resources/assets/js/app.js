(function($){
    function updateTooltips()
    {
        $('[title]').each(function(){

            if($(this).attr('title') == '')
                $(this).removeAttr('title');
            else
                $(this)
                    .addClass('tooltipped')
                    .attr('data-tooltip', $(this).attr('title'))
                    .removeAttr('title');

        }).promise().done(function(){
            $('.material-tooltip').remove();
            $('.tooltipped').tooltip({delay: 0});
        });
    }

    $(function(){

        $('.button-collapse').sideNav();
        $(".dropdown-button").dropdown({ belowOrigin: true, constrain_width: false });
        $('.modal-trigger').leanModal();

        updateTooltips();

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

        var $copyCommand = $('#copyCommand');

        if($copyCommand.length > 0) {
            $copyCommand.on('click', function (event) {
                event.preventDefault();
            });

            ZeroClipboard.config({swfPath: $copyCommand.data('zclip-path')});
            var clip = new ZeroClipboard($copyCommand);

            clip.on("ready", function () {
                $('#global-zeroclipboard-html-bridge').attr('title', 'Copy Command');
                updateTooltips();

                this.on("copy", function (event) {
                    var clipboard = event.clipboardData;
                    clipboard.setData("text/plain", $('#commandText').val());
                });

                this.on("aftercopy", function (event) {
                    Materialize.toast('Command copied!', 2500);
                });
            });
        }

    }); // end of document ready
})(jQuery); // end of jQuery name space