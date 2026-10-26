/* =========================================================
   WarningInfo – Trang xử lý sự cố của cán bộ (TV4 – Milestone 3)
   Form "Cập nhật tiến độ": đổi gợi ý ô ghi chú theo trạng thái,
   bắt buộc ghi chú khi báo hoàn thành / cần bổ sung, hỏi xác nhận trước khi báo hoàn thành.
   ========================================================= */
(function () {
  'use strict';

  const form = document.getElementById('officerUpdateForm');
  if (!form) return;

  const status = document.getElementById('officerStatus');
  const note = document.getElementById('officerNote');
  const help = document.getElementById('officerNoteHelp');
  const HINTS = {
    in_progress: ['Ghi chú tiến độ (không bắt buộc)', false],
    need_more_info: ['Người dân cần bổ sung gì? (bắt buộc) – ví dụ: ảnh rõ hơn, số nhà chính xác...', true],
    resolved: ['Kết quả xử lý (bắt buộc) – ví dụ: đã thu gom rác, lập biên bản cơ sở vi phạm...', true],
  };

  const sync = () => {
    const [placeholder, required] = HINTS[status.value] || HINTS.in_progress;
    note.placeholder = placeholder;
    note.required = required;
    note.classList.remove('is-invalid');
    if (help) {
      help.textContent = required
        ? 'Bắt buộc nhập. Người gửi báo cáo sẽ nhận được thông báo kèm nội dung này.'
        : 'Người gửi báo cáo sẽ nhận được thông báo kèm ghi chú này.';
    }
  };

  status.addEventListener('change', sync);
  sync();

  form.addEventListener('submit', (e) => {
    if (note.required && note.value.trim() === '') {
      e.preventDefault();
      note.classList.add('is-invalid');
      note.focus();
      if (window.WI && WI.toast) WI.toast('Vui lòng nhập ghi chú cho trạng thái này.', 'danger');
      return;
    }
    if (status.value === 'resolved' && !window.confirm('Xác nhận báo đã khắc phục xong sự cố? Sau khi báo hoàn thành, nhiệm vụ sẽ được đóng.')) {
      e.preventDefault();
    }
  });
})();
