+function ($) {
    $(document).ready(function () {
        $("#success-alert").fadeTo(2000, 500).slideUp(500, function(){
            $("#success-alert").slideUp(500);
        });
        if (window.location.href.match("/admin/")) {
            $('.well').css("z-index", "2")
        }
    });


}(window.jQuery);
