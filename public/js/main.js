var main = {
    editComment: function (elt) {
        console.log('.form_update_comment'+elt);
            $('.form_update_comment'+elt).toggle();
    },

    ajaxUpdateComment : function(){

        $.ajax({
            type: 'POST',
            url: 'ajax/massAction.php',
            data: {
                action: 'autocompleteSearchCategories',
                term: request.term,
                thesaurusId: controlId
            },
            success: function (data) {
                data = jQuery.parseJSON(data);
                response($.map(data, function (item) {
                    return {
                        pathLabel: item.pathLabel,
                        id: item.id
                    };
                }));
            }
        });

    }



}