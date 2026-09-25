// Live like counts across every open tab/user, pushed over Reverb instead
// of waiting on the existing 5s poller in post-interactions.js (that
// poller still runs and stays as the self-healing fallback if a socket
// drops — this script is pure enhancement on top of it, never a
// replacement dependency).
document.addEventListener('DOMContentLoaded', function () {
    if (!window.Echo) return; // Echo not configured — poller still covers it

    document.querySelectorAll('.post-item[data-post-id]').forEach(function (postItem) {
        const postId = postItem.dataset.postId;

        window.Echo.channel('post.' + postId).listen('.PostLikeToggled', function (e) {
            const likeBtn = postItem.querySelector('.post-like-btn .post-like-count');
            if (likeBtn) likeBtn.textContent = e.like_count;
        });
    });
});
