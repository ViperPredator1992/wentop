$(document).ready(function () {

    $(".menu-vertical__name").click(function () {
        if ($(this).next('.menu-vertical__list').css("display") == "none") {
            $(this).next('.menu-vertical__list').slideDown();
        }
        else {
            $(this).next('.menu-vertical__list').slideUp();
        }
    });

       

});