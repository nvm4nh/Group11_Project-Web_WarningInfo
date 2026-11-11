/* =========================================================
   WarningInfo – Đăng nhập / Đăng ký (TV4 – Milestone 4)
   - Nút con mắt hiện / ẩn mật khẩu
   - Trang đăng ký: báo ngay mật khẩu đủ độ dài và 2 ô mật khẩu có khớp nhau không
   ========================================================= */
(function () {
  'use strict';

  document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
    const input = document.querySelector(btn.dataset.togglePassword);
    if (!input) return;
    btn.addEventListener('click', () => {
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
      btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
      input.focus();
    });
  });

  const pass = document.getElementById('regPassword');
  const confirm = document.getElementById('regConfirm');
  const passHint = document.getElementById('passHint');
  const confirmHint = document.getElementById('confirmHint');
  if (!pass || !confirm) return;

  const check = () => {
    const len = pass.value.length;
    if (passHint) {
      passHint.textContent = len === 0 ? 'Tối thiểu 6 ký tự.' : (len < 6 ? `Còn thiếu ${6 - len} ký tự.` : 'Độ dài mật khẩu hợp lệ.');
      passHint.className = 'form-text' + (len === 0 ? '' : (len < 6 ? ' text-danger' : ' text-success'));
    }
    if (confirmHint) {
      if (confirm.value === '') { confirmHint.textContent = ''; confirmHint.className = 'form-text'; return; }
      const same = confirm.value === pass.value;
      confirmHint.textContent = same ? 'Mật khẩu khớp.' : 'Mật khẩu xác nhận chưa khớp.';
      confirmHint.className = 'form-text ' + (same ? 'ok' : 'bad');
    }
  };
  pass.addEventListener('input', check);
  confirm.addEventListener('input', check);
})();
