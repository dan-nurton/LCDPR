$( ".button_critique" ).click(function() {
    $('.author_admin').slideUp('slow');
   $('.critique_admin').slideToggle('slow');

});

$( ".button_author" ).click(function() {
    $('.critique_admin').slideUp('slow');
    $('.author_admin').slideToggle('slow');

});