const { chromium } = require('playwright'); const fs = require('fs'); const path = require('path');
// Kiểm tra truy cập WCAG 2 A/AA bằng axe-core trên các trang chính (3 vai trò). Thoát mã 1 nếu còn vi phạm.
const BASE = (process.env.BASE_URL || 'http://localhost/Group11_Project-Web_WarningInfo').replace(/\/$/, '');
const axe = fs.readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');
async function route(c) {
  if (process.env.OFFLINE_CDN !== '1') return;
  await c.route(/cdn\.jsdelivr\.net\/npm\/|code\.jquery\.com\//, (r) => { const u = new URL(r.request().url()); let f = u.host === 'code.jquery.com' ? 'jquery/dist/jquery.min.js' : u.pathname.replace(/^\/npm\//, '').replace(/^(@?[^@\/]+)@[^\/]+/, '$1').replace('chart.umd.min.js', 'chart.umd.js');
    const full = path.join(process.env.CDN_DIR || path.join(__dirname, 'node_modules'), f); if (!fs.existsSync(full)) return r.fulfill({ status: 404, body: '' });
    r.fulfill({ status: 200, contentType: { css: 'text/css', js: 'application/javascript', woff2: 'font/woff2', woff: 'font/woff' }[f.split('.').pop()] || 'application/octet-stream', body: fs.readFileSync(full) }); });
  await c.route(/fonts\.(googleapis|gstatic)\.com|tile\.openstreetmap\.org/, (r) => r.fulfill({ status: 204, body: '' }));
}
(async () => {
  const b = await chromium.launch(); const out = {};
  const pages = { guest: ['', '/report', '/report/map', '/report/detail/1', '/report/create', '/auth/login', '/auth/register'],
    admin: ['/admin', '/admin/reports', '/admin/users', '/admin/categories', '/notification'], officer: ['/officer', '/officer/report/4'], 'an.demo': ['/report/my_reports', '/auth/profile'] };
  for (const [who, urls] of Object.entries(pages)) {
    const c = await b.newContext({ viewport: { width: 1280, height: 900 } }); await route(c); const p = await c.newPage();
    if (who !== 'guest') { const email = who.includes('.') ? who + '@warninginfo.local' : who + '@warninginfo.com';
      await p.goto(BASE + '/auth/login'); await p.fill('input[name=email]', email); await p.fill('input[name=password]', 'password');
      await Promise.all([p.waitForNavigation(), p.click('form[action*="auth/login"] button[type=submit]')]); }
    for (const u of urls) {
      await p.goto(BASE + u); await p.waitForTimeout(600); await p.addScriptTag({ content: axe });
      const r = await p.evaluate(async () => (await axe.run(document, { runOnly: ['wcag2a', 'wcag2aa'] })).violations.map((v) => ({ id: v.id, impact: v.impact, n: v.nodes.length, help: v.help, sample: v.nodes[0].target.join(' ') + ' | ' + (v.nodes[0].failureSummary || '').split('\n')[1] })));
      out[u || '/'] = r;
    }
    await c.close();
  }
  await b.close();
  let total = 0;
  for (const [u, vs] of Object.entries(out)) { total += vs.length; console.log(`\n${u}: ${vs.length} loại vi phạm`); vs.forEach((v) => console.log(`  [${v.impact}] ${v.id} ×${v.n} – ${v.help}\n      ${v.sample}`)); }
  console.log(`\nTổng: ${total} loại vi phạm trên ${Object.keys(out).length} trang`); process.exit(total ? 1 : 0);
})();
