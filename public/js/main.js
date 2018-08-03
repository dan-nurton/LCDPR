var main = {
    init:function(){
        console.log('salut');
    },
    editComment: function (elt) {
        console.log('.form_update_comment'+elt);
            $('.form_update_comment'+elt).toggle();
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

