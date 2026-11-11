// WarningInfo – bộ kiểm thử end-to-end (Playwright) cho 3 vai trò + API + bảo mật + sẵn sàng deploy
// Cách chạy: xem tests/e2e/README.md. Kết quả + ảnh chụp ghi vào tests/e2e/results/
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const BASE = (process.env.BASE_URL || 'http://localhost/Group11_Project-Web_WarningInfo').replace(/\/$/, '');
const ROOT = path.resolve(__dirname, '../..');
const LIVE = path.join(__dirname, 'results');
const SHOTS = path.join(LIVE, 'shots');
fs.mkdirSync(LIVE, { recursive: true });
// Liệt kê file theo đuôi (bỏ qua .git, node_modules) – chạy được cả Windows lẫn Linux
const walk = (dir, ext) => fs.readdirSync(dir, { withFileTypes: true }).flatMap((d) => {
  if (['.git', 'node_modules'].includes(d.name)) return [];
  const p = path.join(dir, d.name); return d.isDirectory() ? walk(p, ext) : (p.endsWith(ext) ? [p] : []);
});
const SLOW = +(process.env.SLOW || 0);
const PW = 'password';
const ACC = { admin: 'admin@warninginfo.com', officer: 'officer@warninginfo.com', citizen: 'an.demo@warninginfo.local' };

const state = { total: 0, finished: false, started: new Date().toISOString(), tests: [] };
const save = () => fs.writeFileSync(path.join(LIVE, 'results.json'), JSON.stringify(state, null, 1));
const tests = [];
let group = '';
const G = (g) => (group = g);
const T = (name, fn) => tests.push({ group, name, fn });
const assert = (c, m) => { if (!c) throw new Error(m); };
// Truy vấn CSDL để đối chiếu kết quả (cần mysql CLI; đổi lệnh bằng biến MYSQL_CMD, vd: "C:/xampp/mysql/bin/mysql.exe -uroot")
const MYSQL = process.env.MYSQL_CMD || 'mysql -uroot';
const DBNAME = process.env.DB_NAME || 'warninginfo_db';
const sql = (q) => execSync(`${MYSQL} -N --default-character-set=utf8mb4 ${DBNAME} -e ${JSON.stringify(q)}`).toString().trim();

let browser, guest, page, ctxs = {}, consoleErrors = [], shotN = 0, cur;
let lastPage;
async function shot(p) { lastPage = p; const f = `${String(++shotN).padStart(3, '0')}.png`; await p.waitForLoadState('load').catch(() => {});
  try { await p.screenshot({ path: path.join(SHOTS, f) }); } catch { await p.waitForTimeout(500); await p.screenshot({ path: path.join(SHOTS, f) }); } cur.shot = f; save(); }
async function newCtx(opts = {}) {
  const c = await browser.newContext({ viewport: { width: 1280, height: 800 }, ignoreHTTPSErrors: true, locale: 'vi-VN', ...opts });
  // OFFLINE_CDN=1: máy không ra được CDN → phục vụ thư viện từ node_modules (cùng phiên bản)
  if (process.env.OFFLINE_CDN === '1') await c.route(/cdn\.jsdelivr\.net\/npm\/|code\.jquery\.com\//, (route) => {
    const u = new URL(route.request().url()); let f;
    if (u.host === 'code.jquery.com') f = 'jquery/dist/jquery.min.js';
    else f = u.pathname.replace(/^\/npm\//, '').replace(/^(@?[^@\/]+)@[^\/]+/, '$1').replace('chart.umd.min.js', 'chart.umd.js');
    const full = path.join(process.env.CDN_DIR || path.join(__dirname, 'node_modules'), f);
    if (!fs.existsSync(full)) return route.fulfill({ status: 404, body: '' });
    const ct = { css: 'text/css', js: 'application/javascript', woff2: 'font/woff2', woff: 'font/woff', png: 'image/png' }[f.split('.').pop()] || 'application/octet-stream';
    route.fulfill({ status: 200, contentType: ct, body: fs.readFileSync(full), headers: { 'Access-Control-Allow-Origin': '*' } });
  });
  if (process.env.OFFLINE_CDN === '1') await c.route(/fonts\.(googleapis|gstatic)\.com|tile\.openstreetmap\.org/, (route) => route.fulfill({ status: 204, body: '' }));
  return c;
}
function track(p, label) {
  p.on('dialog', (d) => d.accept());
  p.on('framenavigated', (f) => { if (f === p.mainFrame()) lastPage = p; });
  p.on('pageerror', (e) => consoleErrors.push(`${label} ${p.url()}: ${e.message}`));
  p.on('console', (m) => { if (m.type() === 'error' && !/favicon|ERR_|Failed to load resource/.test(m.text())) consoleErrors.push(`${label} ${p.url()}: ${m.text()}`); });
  return p;
}
async function login(role) {
  const c = await newCtx(); const p = track(await c.newPage(), role);
  await p.goto(`${BASE}/auth/login`);
  await p.fill('input[name=email]', ACC[role]); await p.fill('input[name=password]', PW);
  await Promise.all([p.waitForNavigation(), p.click('form[action*="auth/login"] button[type=submit]')]);
  await p.waitForLoadState('load'); ctxs[role] = { c, p }; return p;
}
const csrf = (p) => p.locator('meta[name=csrf-token]').getAttribute('content');
const pngFile = path.join(LIVE, 'evidence.png');
// 1x1 PNG with valid header
fs.writeFileSync(pngFile, Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', 'base64'));

let newReportId, newReportCode, catId;
const STAMP = Date.now().toString().slice(-6);

// ---------------- Static checks ----------------
G('1. Kiểm tra tĩnh & CSDL');
T('PHP lint toàn bộ file .php (php -l)', async () => {
  const files = walk(ROOT, '.php'); const PHP = process.env.PHP_BIN || 'php';
  const bad = files.filter((f) => { try { execSync(`${PHP} -l "${f}"`, { stdio: 'pipe' }); return false; } catch { return true; } });
  assert(!bad.length, 'Lỗi cú pháp: ' + bad.join(', '));
  return `${files.length} file, 0 lỗi`;
});
T('CSDL import đủ 10 bảng + dữ liệu mẫu', async () => {
  const n = +sql("select count(*) from information_schema.tables where table_schema='warninginfo_db'");
  assert(n === 10, `có ${n} bảng`);
  return `${n} bảng, ${sql('select count(*) from reports')} sự cố, ${sql('select count(*) from users')} tài khoản`;
});
T('JS assets không lỗi cú pháp (node --check)', async () => {
  const files = walk(path.join(ROOT, 'public/assets/js'), '.js');
  files.forEach((f) => execSync(`node --check "${f}"`, { stdio: 'pipe' }));
  return `${files.length} file`;
});

// ---------------- Guest pages ----------------
G('2. Trang công khai (khách)');
for (const [name, url, sel] of [
  ['Trang chủ', '', 'body'],
  ['Danh sách sự cố', '/report', 'body'],
  ['Bản đồ Leaflet', '/report/map', '.leaflet-container'],
  ['Chi tiết sự cố #1', '/report/detail/1', 'body'],
  ['Trang đăng nhập', '/auth/login', 'input[name=email]'],
  ['Trang đăng ký', '/auth/register', 'input[name=full_name]'],
]) T(name, async () => {
  const r = await page.goto(BASE + url); assert(r.status() === 200, 'HTTP ' + r.status());
  await page.waitForSelector(sel, { timeout: 15000 });
  const html = await page.content();
  assert(!/Fatal error|Warning:|Notice:|Deprecated:|Uncaught/.test(html), 'Trang có lỗi PHP hiển thị');
  await page.waitForTimeout(400); await shot(page);
  return 'HTTP 200';
});
T('Bản đồ hiển thị marker từ GeoJSON', async () => {
  await page.goto(BASE + '/report/map');
  await page.waitForSelector('.leaflet-marker-icon, .leaflet-interactive', { timeout: 15000 });
  const n = await page.locator('.leaflet-marker-icon, path.leaflet-interactive').count();
  assert(n > 0, 'không có marker'); await shot(page); return `${n} marker`;
});
T('Lọc AJAX danh sách sự cố (không tải lại trang)', async () => {
  await page.goto(BASE + '/report');
  const sel = page.locator('select').first();
  const [resp] = await Promise.all([
    page.waitForResponse((r) => r.url().includes('report/api_list'), { timeout: 10000 }),
    sel.selectOption({ index: 1 }),
  ]);
  assert(resp.status() === 200, 'api_list ' + resp.status()); await page.waitForTimeout(500); await shot(page);
  return 'gọi api_list ' + resp.status();
});
T('URL tới hàm private (report/collectUploads) không gây Fatal', async () => {
  const r = await page.goto(BASE + '/report/collectUploads'); const h = await page.content();
  assert(r.status() < 500 && !/Fatal error/.test(h), 'lộ lỗi'); return 'HTTP ' + r.status();
});
T('Đường dẫn không tồn tại → trang 404 / trang chủ (không lỗi 500)', async () => {
  const r = await page.goto(BASE + '/khongtontai/abc'); assert([200, 404].includes(r.status()), 'HTTP ' + r.status()); await shot(page); return 'HTTP ' + r.status();
});

// ---------------- REST API ----------------
G('3. WebService RESTful API');
const api = (u, o) => guest.request.fetch(BASE + u, o);
T('GET report/api_list → JSON success', async () => {
  const r = await api('/report/api_list'); const j = await r.json();
  assert(r.status() === 200 && j.status === 'success' && Array.isArray(j.data), 'sai định dạng'); return `${j.data.length} bản ghi`;
});
T('api_list lọc priority sai → 422', async () => { const r = await api('/report/api_list?priority=xyz'); assert(r.status() === 422, 'HTTP ' + r.status()); return '422'; });
T('api_list ngày sai định dạng → 422', async () => { const r = await api('/report/api_list?from_date=2026-13-99'); assert(r.status() === 422, 'HTTP ' + r.status()); return '422'; });
T('api_list from > to → 422', async () => { const r = await api('/report/api_list?from_date=2026-10-10&to_date=2026-01-01'); assert(r.status() === 422, 'HTTP ' + r.status()); return '422'; });
T('POST api_list → 405 (hoặc 403 CSRF)', async () => { const r = await api('/report/api_list', { method: 'POST' }); assert([403, 405].includes(r.status()), 'HTTP ' + r.status()); return 'HTTP ' + r.status(); });
T('GET report/api_geojson → FeatureCollection', async () => {
  const j = await (await api('/report/api_geojson')).json();
  const fc = j.type === 'FeatureCollection' ? j : j.data;
  assert(fc && fc.type === 'FeatureCollection' && fc.features.length > 0, 'không phải FeatureCollection'); return `${fc.features.length} feature`;
});
T('api_geojson toạ độ ngoài phạm vi → 422', async () => { const r = await api('/report/api_geojson?lat=200&lng=10&radius=5'); assert(r.status() === 422, 'HTTP ' + r.status()); return '422'; });
T('GET report/api_xml_feed → XML hợp lệ', async () => {
  const r = await api('/report/api_xml_feed'); const t = await r.text();
  assert(r.status() === 200 && /xml/.test(r.headers()['content-type']), 'content-type ' + r.headers()['content-type']);
  assert(/^<\?xml[^>]*\?>\s*<reports>[\s\S]*<\/reports>\s*$/.test(t), 'XML không hợp lệ');
  return `${t.length} byte`;
});
T('GET report/api_comments/1 → JSON', async () => { const r = await api('/report/api_comments/1'); const j = await r.json(); assert(r.status() === 200 && j.status === 'success', 'HTTP ' + r.status()); return 'OK'; });
T('api_comments id không tồn tại → 404', async () => { const r = await api('/report/api_comments/999999'); assert(r.status() === 404, 'HTTP ' + r.status()); return '404'; });
T('notification/latest khi chưa đăng nhập → 401', async () => { const r = await api('/notification/latest', { headers: { Accept: 'application/json' } }); assert(r.status() === 401, 'HTTP ' + r.status()); return '401'; });

// ---------------- Security ----------------
G('4. Bảo mật & phân quyền');
T('POST không có CSRF token bị chặn', async () => {
  const r = await api('/auth/login', { method: 'POST', form: { email: ACC.admin, password: PW }, maxRedirects: 0 });
  const t = await r.text(); assert(r.status() === 403 || /hết hạn|không hợp lệ/i.test(t), 'không bị chặn, HTTP ' + r.status()); return 'HTTP ' + r.status();
});
T('AJAX upvote khi chưa đăng nhập → 401', async () => {
  await page.goto(BASE + '/report/detail/1'); const tok = await csrf(page);
  const r = await page.request.post(BASE + '/report/api_upvote/1', { headers: { 'X-CSRF-Token': tok, Accept: 'application/json' } });
  assert(r.status() === 401, 'HTTP ' + r.status()); return '401';
});
T('Khách truy cập /admin bị chuyển hướng', async () => { await page.goto(BASE + '/admin'); assert(!/\/admin/.test(page.url()), 'vẫn ở ' + page.url()); return '→ ' + page.url().replace(BASE, ''); });
T('Khách truy cập /officer → trang đăng nhập', async () => { await page.goto(BASE + '/officer'); assert(/auth\/login/.test(page.url()), page.url()); return '→ /auth/login'; });
T('Khách truy cập /admin/export_csv bị chặn', async () => { const r = await api('/admin/export_csv', { maxRedirects: 0 }); assert(r.status() === 302, 'HTTP ' + r.status()); return '302'; });
T('Đăng nhập sai mật khẩu hiển thị lỗi', async () => {
  await page.goto(BASE + '/auth/login'); await page.fill('input[name=email]', ACC.admin); await page.fill('input[name=password]', 'sai-mat-khau');
  await Promise.all([page.waitForNavigation(), page.click('form[action*="auth/login"] button[type=submit]')]);
  assert(/không đúng/.test(await page.content()), 'không thấy thông báo lỗi'); await shot(page); return 'báo lỗi đúng';
});

// ---------------- Register / citizen ----------------
G('5. Người dân: đăng ký, gửi báo cáo, tương tác');
T('Đăng ký tài khoản mới', async () => {
  await page.goto(BASE + '/auth/register');
  await page.fill('input[name=full_name]', 'Người Kiểm Thử ' + STAMP);
  await page.fill('input[name=email]', `tester${STAMP}@example.test`);
  await page.fill('input[name=password]', 'matkhau123'); await page.fill('input[name=password_confirm]', 'matkhau123');
  const terms = page.locator('input[type=checkbox]'); if (await terms.count()) await terms.first().check();
  await Promise.all([page.waitForNavigation(), page.click('form:not([action*=logout]) button[type=submit]')]);
  await page.waitForLoadState('load'); await shot(page);
  assert(sql(`select count(*) from users where email='tester${STAMP}@example.test'`) === '1', 'không tạo được user'); return 'đã lưu vào CSDL';
});
T('Đăng nhập người dân', async () => {
  const p = await login('citizen'); assert(!/auth\/login/.test(p.url()), 'vẫn ở trang login'); await shot(p); return '→ ' + p.url().replace(BASE, '') || '/';
});
T('Gửi báo cáo mới kèm ảnh + toạ độ', async () => {
  const p = ctxs.citizen.p; await p.goto(BASE + '/report/create');
  await p.fill('input[name=title]', `[E2E ${STAMP}] Rác tồn đọng <script>alert(1)</script>`);
  await p.selectOption('select[name=category_id]', '1');
  await p.selectOption('select[name=priority]', 'high');
  await p.fill('input[name=address]', '59C Nguyễn Đình Chiểu, Quận 3, TP.HCM');
  await p.fill('#latInput', '10.7835'); await p.fill('#lngInput', '106.6948');
  await p.fill('textarea[name=description]', 'Kiểm thử tự động: rác tồn đọng nhiều ngày gây mùi hôi.');
  await p.setInputFiles('input[name="images[]"]', pngFile);
  await shot(p);
  await Promise.all([p.waitForURL(/report\/detail\/\d+/, { timeout: 15000 }), p.click('form:not([action*=logout]) button[type=submit]')]);
  newReportId = +p.url().match(/detail\/(\d+)/)[1];
  newReportCode = sql(`select report_code from reports where id=${newReportId}`);
  await shot(p); return `#${newReportId} ${newReportCode}`;
});
T('Ảnh minh chứng được lưu (report_images + file)', async () => {
  const f = sql(`select file_path from report_images where report_id=${newReportId} limit 1`); assert(f, 'không có bản ghi ảnh');
  const r = await guest.request.get(BASE + '/' + f.replace(/^\/?(public\/)?/, '')); assert(r.status() === 200, 'ảnh HTTP ' + r.status() + ' ' + f); return f;
});
T('Tiêu đề chứa <script> được escape (chống XSS)', async () => {
  const p = ctxs.citizen.p; const h = await p.content();
  assert(!h.includes('<script>alert(1)</script>') && h.includes('&lt;script&gt;'), 'không escape'); return 'đã escape';
});
T('Trang "Báo cáo của tôi" có báo cáo vừa gửi', async () => {
  const p = ctxs.citizen.p; await p.goto(BASE + '/report/my_reports'); assert((await p.content()).includes(newReportCode), 'không thấy ' + newReportCode); await shot(p); return 'OK';
});
T('Upvote AJAX (Tôi cũng thấy sự cố này)', async () => {
  const p = ctxs.citizen.p; await p.goto(BASE + '/report/detail/1');
  const before = +sql('select count(*) from upvotes where report_id=1');
  const [r] = await Promise.all([p.waitForResponse((x) => x.url().includes('api_upvote')), p.click('#btnUpvote')]);
  assert(r.status() === 200, 'HTTP ' + r.status()); await p.waitForTimeout(400); await shot(p);
  const after = +sql('select count(*) from upvotes where report_id=1'); assert(after !== before, 'CSDL không đổi'); return `${before} → ${after}`;
});
T('Bình luận AJAX', async () => {
  const p = ctxs.citizen.p; const tok = await csrf(p);
  const r = await p.request.post(BASE + '/report/api_add_comment', { headers: { 'X-CSRF-Token': tok, 'Content-Type': 'application/json', Accept: 'application/json' }, data: { report_id: 1, content: 'Bình luận E2E ' + STAMP } });
  assert(r.status() === 200 || r.status() === 201, 'HTTP ' + r.status() + ' ' + (await r.text()).slice(0, 120));
  await p.reload(); await shot(p); return 'HTTP ' + r.status();
});
T('Bình luận rỗng → 422', async () => {
  const p = ctxs.citizen.p; const tok = await csrf(p);
  const r = await p.request.post(BASE + '/report/api_add_comment', { headers: { 'X-CSRF-Token': tok, 'Content-Type': 'application/json', Accept: 'application/json' }, data: { report_id: 1, content: '   ' } });
  assert(r.status() === 422, 'HTTP ' + r.status()); return '422';
});
T('Trang hồ sơ cá nhân', async () => { const p = ctxs.citizen.p; const r = await p.goto(BASE + '/auth/profile'); assert(r.status() === 200, 'HTTP ' + r.status()); await shot(p); return 'OK'; });
T('Người dân không vào được /admin', async () => { const p = ctxs.citizen.p; await p.goto(BASE + '/admin'); assert(!/\/admin/.test(p.url()), p.url()); return 'bị chặn'; });
T('Người dân không vào được /officer', async () => { const p = ctxs.citizen.p; await p.goto(BASE + '/officer'); assert(!/\/officer/.test(p.url()), p.url()); return 'bị chặn'; });

// ---------------- Admin ----------------
G('6. Quản trị viên (Admin)');
T('Đăng nhập admin → Dashboard', async () => { const p = await login('admin'); assert(/\/admin/.test(p.url()), p.url()); return p.url().replace(BASE, ''); });
T('Biểu đồ Chart.js được vẽ', async () => {
  const p = ctxs.admin.p; await p.waitForSelector('canvas', { timeout: 15000 }); await p.waitForTimeout(1200);
  const n = await p.locator('canvas').count(); await shot(p); assert(n >= 1, 'không có canvas'); return `${n} biểu đồ`;
});
T('admin/api_statistics → JSON', async () => { const r = await ctxs.admin.p.request.get(BASE + '/admin/api_statistics'); const j = await r.json(); assert(r.status() === 200 && j, 'HTTP ' + r.status()); return Object.keys(j).slice(0, 4).join(', '); });
T('Xuất CSV UTF-8 (có BOM)', async () => {
  const r = await ctxs.admin.p.request.get(BASE + '/admin/export_csv'); const b = await r.body();
  assert(r.status() === 200 && /csv/.test(r.headers()['content-type']), 'HTTP ' + r.status());
  assert(b[0] === 0xef && b[1] === 0xbb && b[2] === 0xbf, 'thiếu BOM UTF-8');
  return `${b.toString('utf8').split('\n').length - 1} dòng`;
});
T('Danh sách sự cố quản trị', async () => { const p = ctxs.admin.p; await p.goto(BASE + '/admin/reports'); assert((await p.content()).includes(newReportCode), 'thiếu báo cáo mới'); await shot(p); return 'OK'; });
T('Cập nhật trạng thái → "verified"', async () => {
  const p = ctxs.admin.p; const form = p.locator(`form[action$="admin/updateReportStatus/${newReportId}"]`);
  await form.locator('select[name=status]').selectOption('verified');
  await Promise.all([p.waitForLoadState('load'), form.locator('button[type=submit]').click()]);
  await shot(p);
  const st = sql(`select status from reports where id=${newReportId}`); assert(st === 'verified', 'status=' + st); return 'status=verified';
});
T('Phân công cán bộ xử lý', async () => {
  const p = ctxs.admin.p; const tok = await csrf(p);
  const offId = sql(`select id from users where email='${ACC.officer}'`);
  const r = await p.request.post(BASE + '/admin/assignOfficer/' + newReportId, { form: { csrf_token: tok, officer_id: offId, note: 'Xử lý gấp (E2E)', deadline: '' } });
  assert(r.status() === 200, 'HTTP ' + r.status());
  const n = sql(`select count(*) from assignments where report_id=${newReportId} and officer_id=${offId}`); assert(n === '1', 'không có assignment');
  await p.goto(BASE + '/admin/reports'); await shot(p); return `officer #${offId}`;
});
T('Quản lý người dùng', async () => { const p = ctxs.admin.p; const r = await p.goto(BASE + '/admin/users'); assert(r.status() === 200 && (await p.content()).includes(ACC.officer), 'thiếu dữ liệu'); await shot(p); return 'OK'; });
T('Danh mục: thêm mới', async () => {
  const p = ctxs.admin.p; await p.goto(BASE + '/admin/createCategory');
  await p.fill('input[name=name]', 'Danh mục E2E ' + STAMP); await p.fill('textarea[name=description]', 'tạo bởi kiểm thử');
  await Promise.all([p.waitForLoadState('load'), p.click('form:not([action*=logout]) button[type=submit]')]);
  catId = sql(`select id from categories where name='Danh mục E2E ${STAMP}'`); assert(catId, 'không tạo được'); await shot(p); return '#' + catId;
});
T('Danh mục: sửa', async () => {
  const p = ctxs.admin.p; await p.goto(BASE + '/admin/editCategory/' + catId);
  await p.fill('input[name=name]', 'Danh mục E2E sửa ' + STAMP);
  await Promise.all([p.waitForLoadState('load'), p.click('form:not([action*=logout]) button[type=submit]')]);
  assert(sql(`select name from categories where id=${catId}`).includes('sửa'), 'không đổi tên'); return 'OK';
});
T('Danh mục: xoá', async () => {
  const p = ctxs.admin.p; const tok = await csrf(p);
  await p.request.post(BASE + '/admin/deleteCategory/' + catId, { form: { csrf_token: tok } });
  assert(sql(`select count(*) from categories where id=${catId}`) === '0', 'chưa xoá'); await p.goto(BASE + '/admin/categories'); await shot(p); return 'OK';
});

// ---------------- Officer ----------------
G('7. Cán bộ xử lý (Officer)');
T('Đăng nhập cán bộ → bàn làm việc', async () => { const p = await login('officer'); assert(/\/officer/.test(p.url()), p.url()); await shot(p); return 'OK'; });
T('Thấy sự cố được giao', async () => { const p = ctxs.officer.p; assert((await p.content()).includes(newReportCode), 'không thấy ' + newReportCode); return newReportCode; });
T('Nhận việc → "in_progress"', async () => {
  const p = ctxs.officer.p; await p.goto(BASE + '/officer/report/' + newReportId); await shot(p);
  await Promise.all([p.waitForLoadState('load'), p.locator(`form[action$="officer/accept/${newReportId}"] button`).click()]);
  const st = sql(`select status from reports where id=${newReportId}`); assert(st === 'in_progress', 'status=' + st); await shot(p); return st;
});
T('Ghi chú nội bộ hiện trường', async () => {
  const p = ctxs.officer.p; await p.fill('#ghi-chu [name=content]', 'Đã khảo sát hiện trường (E2E)');
  await Promise.all([p.waitForLoadState('load'), p.locator('#ghi-chu button[type=submit]').click()]); await shot(p); return 'OK';
});
T('Cập nhật "resolved" kèm biên bản', async () => {
  const p = ctxs.officer.p; await p.selectOption('#officerStatus', 'resolved');
  await p.locator('#officerUpdateForm [name=note]').fill('Đã thu gom toàn bộ rác (E2E).');
  await Promise.all([p.waitForLoadState('load'), p.locator('#officerUpdateForm button[type=submit]').click()]);
  const st = sql(`select status from reports where id=${newReportId}`); assert(st === 'resolved', 'status=' + st); await shot(p); return st;
});
T('Lịch sử trạng thái ghi đầy đủ', async () => { const n = +sql(`select count(*) from report_status_history where report_id=${newReportId}`); assert(n >= 3, `chỉ ${n} dòng`); return `${n} bước`; });

// ---------------- Notifications ----------------
G('8. Thông báo real-time');
T('Người dân nhận thông báo thay đổi trạng thái', async () => {
  const p = ctxs.citizen.p; await p.goto(BASE + '/notification');
  assert((await p.content()).includes(newReportCode), 'không có thông báo cho ' + newReportCode); await shot(p); return 'OK';
});
T('notification/latest (AJAX polling) → JSON', async () => {
  const r = await ctxs.citizen.p.request.get(BASE + '/notification/latest', { headers: { Accept: 'application/json' } });
  const j = await r.json(); assert(r.status() === 200, 'HTTP ' + r.status()); return JSON.stringify(j).slice(0, 80);
});
T('Đánh dấu tất cả đã đọc', async () => {
  const p = ctxs.citizen.p; const tok = await csrf(p); const uid = sql(`select id from users where email='${ACC.citizen}'`);
  await p.request.post(BASE + '/notification/readAll', { form: { csrf_token: tok } });
  const n = sql(`select count(*) from notifications where user_id=${uid} and is_read=0`); assert(n === '0', `${n} chưa đọc`); return 'OK';
});
T('Admin nhận thông báo cán bộ đã nhận việc', async () => { const p = ctxs.admin.p; await p.goto(BASE + '/notification'); await shot(p); assert((await p.content()).includes(newReportCode), 'không có'); return 'OK'; });

// ---------------- Responsive & console ----------------
G('9. Giao diện di động & lỗi JS');
for (const [n, u] of [['Trang chủ (iPhone 13)', ''], ['Bản đồ (iPhone 13)', '/report/map'], ['Gửi báo cáo (iPhone 13)', '/report/create']]) T(n, async () => {
  const c = await newCtx({ ...require('playwright').devices['iPhone 13'] }); const p = track(await c.newPage(), 'mobile');
  await p.goto(BASE + u); await p.waitForTimeout(800);
  const ow = await p.evaluate(() => document.documentElement.scrollWidth - window.innerWidth); await shot(p); await c.close();
  assert(ow <= 1, `tràn ngang ${ow}px`); return 'không tràn ngang';
});
T('Không có lỗi JavaScript trên các trang đã duyệt', async () => { assert(!consoleErrors.length, consoleErrors.slice(0, 3).join(' | ')); return '0 lỗi'; });
T('Đăng xuất', async () => { const p = ctxs.citizen.p; await p.goto(BASE + '/'); await Promise.all([p.waitForNavigation(), p.locator('form[action*=logout] button').first().evaluate((b) => b.click())]); await shot(p); await p.goto(BASE + '/notification'); assert(/auth\/login/.test(p.url()), p.url()); return 'OK'; });

// ---------------- Deploy readiness (Milestone 4) ----------------
G('10. Sẵn sàng deploy & bảo mật production');
const ROOT_BASE = process.env.ROOT_BASE || 'http://localhost:8080';
T('Chạy ở gốc domain: link CSS/JS theo đúng tên miền đang truy cập', async () => {
  const c = await newCtx(); const p = track(await c.newPage(), 'root');
  const bad = [];
  p.on('response', (r) => { if (r.url().startsWith(ROOT_BASE) && r.status() >= 400) bad.push(r.status() + ' ' + r.url()); });
  await p.goto(ROOT_BASE + '/'); await p.waitForLoadState('load');
  const css = await p.locator('link[rel=stylesheet][href*="assets/css"]').first().getAttribute('href');
  const loginHref = await p.locator('a[href*="auth/login"]').first().getAttribute('href');
  await shot(p); await c.close();
  assert(css && css.startsWith(ROOT_BASE + '/assets/'), 'CSS trỏ tới ' + css);
  assert(loginHref && loginHref.startsWith(ROOT_BASE + '/auth/login'), 'link đăng nhập trỏ tới ' + loginHref);
  assert(!bad.length, bad.slice(0, 2).join(' | '));
  return css;
});
T('Header bảo mật HTTP (nosniff, chống clickjacking, Referrer-Policy)', async () => {
  const h = (await guest.request.get(BASE + '/')).headers();
  const miss = ['x-content-type-options', 'x-frame-options', 'referrer-policy'].filter((k) => !h[k]);
  assert(!miss.length, 'thiếu ' + miss.join(', ')); return `${h['x-frame-options']}, ${h['referrer-policy']}`;
});
T('Cookie phiên có HttpOnly + SameSite', async () => {
  const c = await newCtx(); const p = await c.newPage(); await p.goto(BASE + '/auth/login');
  const ck = (await c.cookies()).find((x) => x.name === 'PHPSESSID' || /sess/i.test(x.name)); await c.close();
  assert(ck, 'không có cookie phiên'); assert(ck.httpOnly, 'thiếu HttpOnly'); assert(['Lax', 'Strict'].includes(ck.sameSite), 'SameSite=' + ck.sameSite);
  return `HttpOnly, SameSite=${ck.sameSite}`;
});
T('Giờ sự cố khớp giờ tạo khi MySQL chạy múi giờ UTC (như hosting)', async () => {
  assert(newReportId, 'chưa có báo cáo mới');
  // Lấy đúng giá trị ứng dụng đọc ra (qua API), vì cột TIMESTAMP hiển thị theo múi giờ của phiên kết nối
  const j = await (await guest.request.get(BASE + '/report/api_list?limit=100')).json();
  const r = j.data.find((x) => x.id === newReportId); assert(r, 'API không trả báo cáo mới');
  const d = Math.abs(new Date(r.incident_time.replace(' ', 'T')) - new Date(r.created_at.replace(' ', 'T'))) / 1000;
  const now = new Date(Date.now() + 7 * 3600e3).toISOString().slice(0, 16).replace('T', ' ');
  assert(Math.abs(new Date(r.created_at.replace(' ', 'T')) - new Date(now.replace(' ', 'T'))) < 600e3, `created_at=${r.created_at}, giờ VN hiện tại=${now}`);
  assert(d < 120, `lệch ${Math.round(d / 3600)} giờ (${d}s)`); return `lệch ${d}s`;
});
T('Gửi bình luận (form) vào sự cố không tồn tại không gây lỗi 500', async () => {
  const p = ctxs.admin.p; await p.goto(BASE + '/report/detail/1'); const tok = await csrf(p);
  const r = await p.request.post(BASE + '/report/comment/999999', { form: { csrf_token: tok, content: 'x' }, maxRedirects: 0 });
  assert(r.status() < 500, 'HTTP ' + r.status());
  assert(sql('select count(*) from comments where report_id=999999') === '0', 'đã lưu bình luận rác'); return 'HTTP ' + r.status();
});
T('Không bình luận được vào sự cố không công khai', async () => {
  sql(`update reports set is_public=0 where id=2`);
  try {
    const p = ctxs.admin.p; const tok = await csrf(p); const before = sql('select count(*) from comments where report_id=2');
    // dùng tài khoản người dân mới đăng nhập lại
    const pc = await login('citizen'); const tk = await csrf(pc);
    await pc.request.post(BASE + '/report/comment/2', { form: { csrf_token: tk, content: 'lén bình luận' }, maxRedirects: 0 });
    const after = sql('select count(*) from comments where report_id=2');
    assert(after === before, `đã lưu (${before} → ${after})`); return 'bị chặn';
  } finally { sql(`update reports set is_public=1 where id=2`); }
});
T('Admin không tự hạ quyền của chính mình', async () => {
  const p = ctxs.admin.p; await p.goto(BASE + '/admin/users'); const tok = await csrf(p);
  const id = sql(`select id from users where email='${ACC.admin}'`);
  await p.request.post(BASE + '/admin/updateUser/' + id, { form: { csrf_token: tok, role: 'user', account_status: 'active' } });
  const role = sql(`select role from users where id=${id}`); if (role !== 'admin') sql(`update users set role='admin' where id=${id}`);
  assert(role === 'admin', 'admin đã tự mất quyền'); return 'giữ quyền admin';
});
T('Admin khoá tài khoản → người đó không đăng nhập được', async () => {
  const p = ctxs.admin.p; await p.goto(BASE + '/admin/users'); const tok = await csrf(p);
  const email = `tester${STAMP}@example.test`; const id = sql(`select id from users where email='${email}'`);
  assert(id, 'không có user test');
  assert(await p.locator(`form[action$="admin/updateUser/${id}"] [name=account_status]`).count() === 1, 'trang quản lý chưa có ô trạng thái tài khoản');
  await p.request.post(BASE + '/admin/updateUser/' + id, { form: { csrf_token: tok, role: 'user', account_status: 'locked' } });
  assert(sql(`select account_status from users where id=${id}`) === 'locked', 'chưa khoá');
  const c = await newCtx(); const q = await c.newPage(); await q.goto(BASE + '/auth/login');
  await q.fill('input[name=email]', email); await q.fill('input[name=password]', 'matkhau123');
  await Promise.all([q.waitForLoadState('load'), q.click('form[action*="auth/login"] button[type=submit]')]); await q.waitForLoadState('load');
  const html = await q.content(); await shot(q); await c.close();
  assert(/auth\/login/.test(q.url()) && /khoá|khóa|vô hiệu/i.test(html), 'vẫn đăng nhập được: ' + q.url()); return 'bị từ chối';
});
T('Khoá tài khoản đang đăng nhập → bị đăng xuất ngay', async () => {
  const pc = ctxs.citizen.p; const uid = sql(`select id from users where email='${ACC.citizen}'`);
  sql(`update users set account_status='locked' where id=${uid}`);
  try { await pc.goto(BASE + '/report/my_reports'); assert(/auth\/login/.test(pc.url()), 'vẫn vào được: ' + pc.url()); }
  finally { sql(`update users set account_status='active' where id=${uid}`); }
  return 'đã đăng xuất';
});
T('Không lộ mã nguồn / CSDL / .git qua URL', async () => {
  const leaks = [];
  for (const u of ['/config/config.php', '/database/database_init.sql', '/.git/config', '/core/App.php', '/README.md', '/app/views/layouts/header.php']) {
    const t = await (await guest.request.get(ROOT_BASE + u)).text();
    if (/define\(|CREATE TABLE|\[core\]|class App|<\?php|# 🌍/.test(t)) leaks.push(u);
  }
  assert(!leaks.length, 'lộ: ' + leaks.join(', ')); return '6 đường dẫn an toàn';
});

(async () => {
  fs.rmSync(SHOTS, { recursive: true, force: true }); fs.mkdirSync(SHOTS, { recursive: true });
  state.total = tests.length; save();
  browser = await chromium.launch({ slowMo: SLOW, headless: process.env.HEADED !== '1' });
  guest = await newCtx(); page = track(await guest.newPage(), 'guest');
  for (const t of tests) {
    cur = { group: t.group, name: t.name, status: 'running' }; state.tests.push(cur); save();
    const t0 = Date.now();
    try { cur.detail = (await t.fn()) || ''; cur.status = 'pass'; }
    catch (e) { cur.status = 'fail'; cur.error = String(e.message || e).split('\n')[0].slice(0, 300); try { cur.error += ' @ ' + (lastPage || page).url().replace(BASE, ''); await shot(lastPage || page); } catch {} }
    cur.ms = Date.now() - t0; save();
    console.log(`${cur.status === 'pass' ? 'PASS' : 'FAIL'}  [${t.group}] ${t.name}  ${cur.status === 'pass' ? cur.detail : cur.error}`);
  }
  state.finished = true; state.ended = new Date().toISOString(); save();
  const f = state.tests.filter((t) => t.status === 'fail').length;
  console.log(`\n${state.tests.length - f}/${state.tests.length} passed`);
  await browser.close(); process.exit(f ? 1 : 0);
})();
