document.addEventListener('DOMContentLoaded', () => {
    // Khai báo các phần tử giao diện bộ lọc[cite: 9]
    const searchInput = document.getElementById('search-input') || document.querySelector('input[name="keyword"]');
    const categoryFilter = document.getElementById('category-filter') || document.querySelector('select[name="category"]');
    const severityFilter = document.getElementById('severity-filter') || document.querySelector('select[name="severity"]');
    const statusFilter = document.getElementById('status-filter') || document.querySelector('select[name="status"]');
    const cardContainer = document.getElementById('report-list-container') || document.querySelector('.grid-reports');

    let debounceTimer = null;

    // Hiển thị hiệu ứng Loading Skeleton trong khi chờ máy chủ phản hồi[cite: 9]
    function showSkeletonLoading() {
        if (!cardContainer) return;
        cardContainer.innerHTML = Array(6).fill(0).map(() => `
            <div class="skeleton-card">
                <div class="skeleton-image"></div>
                <div class="skeleton-body">
                    <div class="skeleton-badge"></div>
                    <div class="skeleton-title"></div>
                    <div class="skeleton-text"></div>
                </div>
            </div>
        `).join('');
    }

    // Gọi fetch() tới /report/api_list[cite: 9]
    async function fetchReports() {
        if (!cardContainer) return;
        showSkeletonLoading();

        const params = new URLSearchParams({
            keyword: searchInput ? searchInput.value.trim() : '',
            category: categoryFilter ? categoryFilter.value : '',
            severity: severityFilter ? severityFilter.value : '',
            status: statusFilter ? statusFilter.value : ''
        });

        try {
            const response = await fetch(`/report/api_list?${params.toString()}`);
            if (!response.ok) throw new Error('Không thể kết nối đến server');
            const data = await response.json();

            renderReportCards(data.reports || []);
        } catch (error) {
            console.error('Lỗi tải danh sách cảnh báo:', error);
            cardContainer.innerHTML = '<p class="error-message">Đã có lỗi xảy ra khi tải dữ liệu. Vui lòng thử lại.</p>';
        }
    }

    // Render danh sách Card sự cố động[cite: 9]
    function renderReportCards(reports) {
        if (!cardContainer) return;
        if (reports.length === 0) {
            cardContainer.innerHTML = '<p class="empty-message">Không tìm thấy cảnh báo phù hợp.</p>';
            return;
        }

        cardContainer.innerHTML = reports.map(report => `
            <div class="report-card" data-id="${report.id}">
                <div class="card-image">
                    <img src="${report.image || '/assets/images/placeholder.jpg'}" alt="${report.title}">
                </div>
                <div class="card-content">
                    <div class="card-badges">
                        <span class="badge badge-severity-${report.severity}">${report.severity_text || report.severity}</span>
                        <span class="badge badge-status-${report.status}">${report.status_text || report.status}</span>
                    </div>
                    <h3 class="card-title"><a href="/report/detail/${report.id}">${report.title}</a></h3>
                    <p class="card-desc">${report.description || ''}</p>
                </div>
            </div>
        `).join('');
    }

    // Áp dụng kỹ thuật Debounce (300ms) khi gõ tìm kiếm[cite: 9]
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchReports();
            }, 300);
        });
    }

    // Lắng nghe sự kiện thay đổi trên các ô lọc[cite: 9]
    [categoryFilter, severityFilter, statusFilter].forEach(element => {
        if (element) {
            element.addEventListener('change', fetchReports);
        }
    });
});