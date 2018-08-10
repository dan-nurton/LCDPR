$( document ).ready(function() {
    var text = $('.commentReview').text();
    $('#comment').val(text);

});

$( ".button_author" ).click(function() {
    $('.critique_admin').slideUp('slow');
    $('.author_admin').slideToggle('slow');

});