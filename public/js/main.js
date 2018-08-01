var main = {
    init:function(){
        console.log('salut');
    },
    editComment: function (elt) {
        console.log('.form_update_comment'+elt);
            $('.form_update_comment'+elt).toggle();
    },


}