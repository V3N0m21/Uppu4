var template = $('#commentTemplate').html();
$(document).ready(function(){
    $("#commentButton").click(function(){
        $("#commentButton").remove();
        $(".commentReply").remove();
        $(template).insertAfter("#commentButtonPost");
    })
});
function refreshComments(){
    $.ajax("/ajaxComments/"+ fileId,
        {
            type: "GET",
            success : function(text)
            {
                $( "#comments" ).empty().append(text);
            },
        })
}
function appendPostForm(id, user) {
    $(".commentReply").remove();
    $(template).insertAfter("#comment"+id);
    $("#parentComment").val(id);
    $("#commentBody").val(user+ ', ');

};

function postComment() {
    var fd = new FormData(document.forms.commentForm);
    $.ajax("/ajaxComments/"+ fileId ,
        {
            type: "POST",
            processData: false,
            contentType: false,
            data: fd,
            success : function(text)
            {
                $( "#comments" ).empty().append(text);
            },
            error: function(xhr, textStatus, errorThrown) {
                alert(errorThrown + ' - ' + textStatus);
            },

        });
};
modal = $(".modal");

$(document).on({
    ajaxStart: function() { modal.css({"display": "block"}); },
    ajaxStop: function() { modal.css({"display": "none"}); }
});