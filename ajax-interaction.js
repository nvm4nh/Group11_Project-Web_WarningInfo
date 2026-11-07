document.addEventListener('DOMContentLoaded', () => {

    // -------------------------------------------------------------
    // 1. Nút Upvote: Gọi API tăng lượt xác minh & hiệu ứng micro-interaction[cite: 9]
    // -------------------------------------------------------------
    const upvoteBtn = document.getElementById('btn-upvote') || document.querySelector('.btn-upvote');
    if (upvoteBtn) {
        upvoteBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const reportId = upvoteBtn.dataset.reportId;
            const countSpan = upvoteBtn.querySelector('.upvote-count');

            try {
                const response = await fetch('/report/api_upvote', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ report_id: reportId })
                });

                const data = await response.json();
                if (data.success) {
                    // Hiệu ứng micro-interaction (đổi màu & nhảy số)[cite: 9]
                    upvoteBtn.classList.toggle('active', data.is_upvoted);
                    
                    if (countSpan) {
                        countSpan.classList.add('number-bounce');
                        countSpan.textContent = data.new_count;
                        setTimeout(() => countSpan.classList.remove('number-bounce'), 300);
                    }
                }
            } catch (error) {
                console.error('Lỗi khi thực hiện upvote:', error);
            }
        });
    }

    // -------------------------------------------------------------
    // 2. Nạp bình luận không tải lại trang & nút "Tải thêm bình luận"[cite: 9]
    // -------------------------------------------------------------
    const commentForm = document.getElementById('comment-form');
    const commentList = document.getElementById('comment-list');
    const loadMoreBtn = document.getElementById('btn-load-more-comments');
    let commentPage = 1;

    // Gửi bình luận không tải lại trang[cite: 9]
    if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const textarea = commentForm.querySelector('textarea[name="content"]');
            const content = textarea.value.trim();
            const reportId = commentForm.dataset.reportId;

            if (!content) return;

            try {
                const response = await fetch('/report/api_add_comment', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ report_id: reportId, content: content })
                });

                const data = await response.json();
                if (data.success && commentList) {
                    const newCommentHTML = `
                        <div class="comment-item fade-in">
                            <div class="comment-avatar"></div>
                            <div class="comment-content">
                                <h4 class="comment-user">${data.comment.user_name}</h4>
                                <p class="comment-text">${data.comment.content}</p>
                            </div>
                        </div>
                    `;
                    commentList.insertAdjacentHTML('afterbegin', newCommentHTML);
                    textarea.value = '';
                }
            } catch (error) {
                console.error('Lỗi gửi bình luận:', error);
            }
        });
    }

    // Nút "Tải thêm bình luận"[cite: 9]
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', async () => {
            const reportId = loadMoreBtn.dataset.reportId;
            commentPage++;

            try {
                const response = await fetch(`/report/api_comments?report_id=${reportId}&page=${commentPage}`);
                const data = await response.json();

                if (data.comments && data.comments.length > 0) {
                    data.comments.forEach(comment => {
                        const html = `
                            <div class="comment-item">
                                <div class="comment-avatar"></div>
                                <div class="comment-content">
                                    <h4 class="comment-user">${comment.user_name}</h4>
                                    <p class="comment-text">${comment.content}</p>
                                </div>
                            </div>
                        `;
                        commentList.insertAdjacentHTML('beforeend', html);
                    });

                    if (!data.has_more) {
                        loadMoreBtn.style.display = 'none';
                    }
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            } catch (error) {
                console.error('Lỗi tải thêm bình luận:', error);
            }
        });
    }

    // -------------------------------------------------------------
    // 3. Đánh dấu đã đọc thông báo qua AJAX[cite: 9]
    // -------------------------------------------------------------
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', async () => {
            const notifId = item.dataset.id;
            if (item.classList.contains('unread')) {
                try {
                    const response = await fetch('/notification/api_mark_read', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ notification_id: notifId })
                    });
                    const data = await response.json();
                    if (data.success) {
                        item.classList.remove('unread');
                        item.classList.add('read');
                    }
                } catch (error) {
                    console.error('Lỗi cập nhật trạng thái thông báo:', error);
                }
            }
        });
    });
});