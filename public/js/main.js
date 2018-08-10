var main = {
    init:function(){
        console.log('salut');
    },
    editComment: function (elt) {
            $('.form_update_comment'+elt).toggle();
           var com = $('#comment'+elt).text();
        $('#update_comment'+elt).val(com);
        $('#comment'+elt).toggle();

    },

    ajaxTest:function (blogPostId,commentId){
        $.ajax({
            type: 'GET',
            url: 'admin/comment/creation/review/ajax/',
            datatype:'json',
            data: {
                id:blogPostId
            },
            async: true,
            success: function(data){
                console.log('test')
            }
});
    }}

