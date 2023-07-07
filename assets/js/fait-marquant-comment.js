import $ from 'jquery';
import { t } from './translation';

let avatarPublicUrl;

$(".comment-content-new textarea").on("input", function () {
    this.style.height = "auto";
    this.style.height = (this.scrollHeight) + "px";
});

$(document).on('click', '.comment-count', function (e) {
    var $commentContent = $(this).parents('.comment-container').find('.comment-content'),
        $fa = $(this).find('i.fa');

    if ($($commentContent).is(":visible")){
        $($fa).removeClass('fa-angle-up').addClass('fa-angle-down');
        $($commentContent).slideUp(400, function() {
            $($commentContent).find('textarea').val('');
        });
    } else {
        $($fa).removeClass('fa-angle-down').addClass('fa-angle-up');
        $($commentContent).slideDown(400);
    }
});

$(document).on('click', '.btn-comment-publish', function (e) {
    var $textarea = $(this).parent().find('textarea'),
        $faitMarquantId = $(this).parents('.comment-content').data('faitMarquantId'),
        $commentContentList = $(this).parents('.comment-content').find('.comment-content-list'),
        $commentContainer = $(this).parents('.comment-container');

    if ($($textarea).val()){
        $.ajax({
            url: `/corp/api/fm-commentaire/${$faitMarquantId}/ajouter`,
            method: 'POST',
            data: {
                text: $($textarea).val()
            },
            success: function (response) {
                avatarPublicUrl = response.avatarPublicUrl;
                var data = response.data;

                $($commentContentList).append(generateHtmlComment(data)).ready(function () {
                    $($textarea).val('').trigger('input');
                    updateCounter($commentContainer, 1)
                });
            },
        });
    }
});

$(document).on('click', '.btn-comment-delete', function (e) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?')) {
        return false;
    }
    var $faitMarquantId = $(this).parents('.comment-content').data('faitMarquantId'),
        $commentId = $(this).data('commentId'),
        $commentElem = $(this).parents('.comment-elem'),
        $commentContainer = $(this).parents('.comment-container');

    $.ajax({
        url: `/corp/api/fm-commentaire/${$faitMarquantId}/supprimer/${$commentId}`,
        method: 'DELETE',
        success: function (response) {
            $commentElem.hide('slow', function () {
                $commentElem.remove();
                updateCounter($commentContainer, -1)
            });
        },
    });
});

function generateHtmlComment(data) {
    return `<div class="comment-elem">
                <div class="header">
                    <div>
                        <span>
                            <img src="${avatarPublicUrl + data.createdBy.user.avatar.nomMd5}" 
                                alt="${data.createdBy.user.prenom} ${data.createdBy.user.nom}" 
                                class="img-expend rounded-circle" width="24" height="24"/>
                        </span>
                        <span class="text-primary">${data.createdBy.user.prenom} ${data.createdBy.user.nom}</span> | 
                        <span class="badge badge-secondary">${t(data.role)}</span>
                    </div>
                    <div class="actions">
                        <span class="created_at"><i>${data.createdAt}</i></span> |
                            <a href="javascript:;" class="ml-1 btn-comment-delete" data-comment-id="${data.id}"><i class="fa fa-trash"></i></a>
                    </div>
                </div>
                <div class="body text-justify">
                    <p>${data.text}</p>
                </div>
            </div>`;
}

function updateCounter($commentContainer, digit) {
    var old = parseInt($($commentContainer).find('.counter').text());
    $($commentContainer).find('.counter').text(old + digit);
}