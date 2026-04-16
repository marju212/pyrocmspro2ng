$(function() {



    $(".thumbnail-description").text(function(index, currentText) {
        var limit = 160;
        if (currentText.length > limit)
            return currentText.substr(0, limit) + ' [...]';
        return currentText;
    });



    $("#tictail_search_box").addClass('form-control').addClass('input-sm');












    $(".fa-search").on("click", function() {
        $(".search-container").slideToggle('',function(){$(".sub-categories").slideToggle();});
        
       
         history.pushState({}, '', '/products');
    });
});



function capitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function sliderInit() {
    jQuery("#layerslider").layerSlider({pauseOnHover: false, autoPlayVideos: false, showCircleTimer: false, skinsPath: 'layerslider/skins/'});

}
function carouselInit() {
jQuery("#carousel").layerSlider({
			autoStart: false,
			skin: 'v5',
			hoverPrevNext: false,
			navStartStop: false,
			showCircleTimer: false,
			thumbnailNavigation: 'always',
			skinsPath: 'layerslider/skins/',
			tnContainerWidth:'80%',
			cbInit : function() {
 
$(".ls-thumbnail-slide img").css('border-radius','4px');

    }
			
		
		});
}

function select_all(el) {
    if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined") {
        var range = document.createRange();
        range.selectNodeContents(el);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    } else if (typeof document.selection != "undefined" && typeof document.body.createTextRange != "undefined") {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(el);
        textRange.select();
    }
}