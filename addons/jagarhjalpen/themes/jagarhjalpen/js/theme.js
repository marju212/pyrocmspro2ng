   $(function()        {            

$('.form-group input,.form-group textarea').addClass('form-control').addClass('input-sm');


 $('input').keypress(function(event){       
        if (event.which == 13) {
            event.preventDefault();
            return false;   
        }
    });


});