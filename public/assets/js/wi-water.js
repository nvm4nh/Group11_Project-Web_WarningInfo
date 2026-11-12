/* =========================================================
   WarningInfo – Nền "mặt nước" bằng shader WebGL (Milestone 4 – giao diện)
   Phong cách lấy cảm hứng từ component "Water" của Paper Shaders trên 21st.dev
   (https://21st.dev/@paper-design/components/water, Apache-2.0). Mã shader ở đây viết mới,
   không sao chép mã của component đó.

   Cách dùng: <canvas class="wi-water" data-water="dark|light|auto" data-sun="0.72,0.22" aria-hidden="true">
     - Đặt canvas là con đầu tiên của khối cần làm nền (khối đó cần position: relative)
     - data-water="auto": khối nền tối (.hero, .page-header, .cta-band, .auth-aside) → bảng màu tối, còn lại → sáng
     - data-sun: vị trí đốm nắng (0–1 theo chiều ngang / dọc của khối), đốm nắng đi theo con trỏ chuột

   Hành vi:
     - Máy có chuột: mặt nước chuyển động liên tục, rê chuột tạo vệt sóng lan, nhấp chuột tạo sóng lớn
     - Màn hình cảm ứng: vẽ 1 khung tĩnh; chạm vào thì gợn sóng chạy trong ~4 giây rồi dừng (tiết kiệm pin)
     - "Giảm chuyển động": chỉ vẽ 1 khung tĩnh
     - Trình duyệt không có WebGL: giữ nguyên nền CSS sẵn có (không có lỗi)
     - Chỉ vẽ khi khối đang nằm trong màn hình
   ========================================================= */
(function () {
  'use strict';

  var canvases = Array.prototype.slice.call(document.querySelectorAll('canvas.wi-water'));
  if (!canvases.length) return;

  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  var live = fine && !reduce;          // chuyển động liên tục
  var DPR = Math.min(window.devicePixelRatio || 1, 1.25);
  var MAX_RIPPLES = 8;

  // Bảng màu (RGB 0–1): nước sâu, nước giữa, vệt sáng đáy (caustic), nắng; amp = độ mạnh vệt sáng
  var PALETTES = {
    dark:  { deep: [0.016, 0.118, 0.078], mid: [0.055, 0.290, 0.188], light: [0.420, 0.760, 0.470], hi: [1.0, 0.925, 0.640], amp: 1.0 },
    light: { deep: [0.800, 0.890, 0.780], mid: [0.875, 0.940, 0.850], light: [0.985, 1.000, 0.955], hi: [1.0, 0.980, 0.860], amp: 0.75 },
  };
  var DARK_HOSTS = '.hero, .page-header, .cta-band, .auth-aside';

  var VERT = 'attribute vec2 aPos; varying vec2 vUv; void main(){ vUv = aPos * 0.5 + 0.5; gl_Position = vec4(aPos, 0.0, 1.0); }';
  var FRAG = [
    'precision mediump float;',
    'varying vec2 vUv;',
    'uniform vec2 uRes; uniform float uTime; uniform vec2 uSun; uniform float uAmp;',
    'uniform vec3 uDeep; uniform vec3 uMid; uniform vec3 uLight; uniform vec3 uHi;',
    'uniform vec4 uRip[' + MAX_RIPPLES + '];',
    // nhiễu giá trị (value noise) 2D
    'float hash(vec2 p){ return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }',
    'float noise(vec2 p){ vec2 i = floor(p); vec2 f = fract(p); vec2 u = f * f * (3.0 - 2.0 * f);',
    '  return mix(mix(hash(i), hash(i + vec2(1.0, 0.0)), u.x), mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), u.x), u.y); }',
    // vệt sáng đáy nước: nhiễu bị bẻ cong (domain warp) rồi lấy "sống" (ridge) → các đường sáng uốn lượn
    'float caustic(vec2 p, float t){',
    '  vec2 w = vec2(noise(p + vec2(0.0, t)), noise(p + vec2(5.2, -t * 0.8)));',
    '  p += (w - 0.5) * 1.7;',
    '  float n = noise(p + vec2(t * 0.35, -t * 0.25));',
    '  float r = 1.0 - abs(n * 2.0 - 1.0);',
    '  return pow(r, 10.0);',
    '}',
    'void main(){',
    // toạ độ tính theo cạnh ngắn của khối → vân nước, quầng nắng có cùng cỡ trên máy tính lẫn điện thoại
    '  vec2 sc = uRes / min(uRes.x, uRes.y);',
    '  vec2 p = vUv * sc;',
    // sóng lan từ các điểm con trỏ đi qua / nhấp vào
    '  vec2 disp = vec2(0.0); float ring = 0.0;',
    '  for (int i = 0; i < ' + MAX_RIPPLES + '; i++) {',
    '    vec4 r = uRip[i];',
    '    float age = uTime - r.z;',
    '    if (r.w <= 0.0 || age < 0.0 || age > 4.0) continue;',
    '    vec2 d = p - r.xy * sc;',
    '    float dist = length(d);',
    '    float k = dist - age * 0.30;',
    '    float env = exp(-k * k * 70.0) * exp(-age * 1.15) * r.w;',
    '    float s = sin(k * 64.0);',
    '    disp += (d / (dist + 0.0001)) * s * env * 0.014;',
    '    ring += s * env;',
    '  }',
    '  vec2 q = p + disp;',
    '  float t = uTime * 0.32;',
    '  float c = caustic(q * 6.0, t) + 0.55 * caustic(q * 10.5 + 3.7, t * 1.3) + 0.3 * caustic(q * 2.2 + 9.1, t * 0.6);',
    // nền: sâu ở dưới, sáng dần lên trên
    '  vec3 col = mix(uDeep, uMid, smoothstep(0.0, 1.0, vUv.y * 0.85 + 0.1));',
    '  col = mix(col, uLight, clamp(c * (0.13 + 0.15 * vUv.y) * uAmp, 0.0, 1.0));',
    // đốm nắng (theo con trỏ): làm vệt sáng quanh đó rực hơn
    '  vec2 s = uSun * sc;',
    '  float g = exp(-length(q - s) * 2.6);',
    '  col += uHi * (g * 0.22 + c * g * 0.55) * uAmp;',
    '  col += uHi * ring * 0.09 * uAmp;',
    '  gl_FragColor = vec4(col, 1.0);',
    '}',
  ].join('\n');

  function compile(gl, type, src) {
    var sh = gl.createShader(type);
    gl.shaderSource(sh, src);
    gl.compileShader(sh);
    if (!gl.getShaderParameter(sh, gl.COMPILE_STATUS)) { gl.deleteShader(sh); return null; }
    return sh;
  }

  function Water(canvas) {
    this.canvas = canvas;
    this.host = canvas.parentElement;
    var mode = canvas.dataset.water || 'auto';
    if (mode === 'auto') mode = this.host.closest(DARK_HOSTS) ? 'dark' : 'light';
    this.pal = PALETTES[mode] || PALETTES.dark;
    var sun = (canvas.dataset.sun || '0.82,0.18').split(',').map(parseFloat);
    // Khối chứa có khai báo --ax / --ay (vd hero: màn hình hẹp neo nắng sát mép trên) thì theo CSS
    var cs = getComputedStyle(this.host);
    var ax = parseFloat(cs.getPropertyValue('--ax')), ay = parseFloat(cs.getPropertyValue('--ay'));
    if (!isNaN(ax) && !isNaN(ay)) sun = [ax, ay];
    this.anchor = { x: sun[0], y: 1 - sun[1] };          // WebGL: trục y hướng lên
    this.sun = { x: this.anchor.x, y: this.anchor.y };
    this.sunTarget = { x: this.anchor.x, y: this.anchor.y };
    this.ripples = [];
    for (var i = 0; i < MAX_RIPPLES; i++) this.ripples.push([0, 0, -10, 0]);
    this.nextRipple = 0;
    this.lastRipplePos = null;
    this.visible = false;
    this.activeUntil = 0;                                 // cảm ứng: chạy tới thời điểm này rồi dừng
    this.ok = this.init();
  }

  Water.prototype.init = function () {
    var gl = this.canvas.getContext('webgl', { antialias: false, alpha: false, depth: false, powerPreference: 'low-power' });
    if (!gl) return false;
    var vs = compile(gl, gl.VERTEX_SHADER, VERT), fs = compile(gl, gl.FRAGMENT_SHADER, FRAG);
    if (!vs || !fs) return false;
    var prog = gl.createProgram();
    gl.attachShader(prog, vs); gl.attachShader(prog, fs); gl.linkProgram(prog);
    if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) return false;
    gl.useProgram(prog);
    var buf = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buf);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, -1, 1, 1, 1]), gl.STATIC_DRAW);
    var loc = gl.getAttribLocation(prog, 'aPos');
    gl.enableVertexAttribArray(loc);
    gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0);
    var u = {};
    ['uRes', 'uTime', 'uSun', 'uAmp', 'uDeep', 'uMid', 'uLight', 'uHi', 'uRip'].forEach(function (n) { u[n] = gl.getUniformLocation(prog, n); });
    gl.uniform3fv(u.uDeep, this.pal.deep); gl.uniform3fv(u.uMid, this.pal.mid);
    gl.uniform3fv(u.uLight, this.pal.light); gl.uniform3fv(u.uHi, this.pal.hi);
    gl.uniform1f(u.uAmp, this.pal.amp);
    this.gl = gl; this.u = u;
    var self = this;
    this.canvas.addEventListener('webglcontextlost', function (e) { e.preventDefault(); self.ok = false; self.host.classList.remove('has-water'); });
    return true;
  };

  Water.prototype.resize = function () {
    var r = this.host.getBoundingClientRect();
    this.rect = r;
    var w = Math.max(1, Math.round(r.width * DPR)), h = Math.max(1, Math.round(r.height * DPR));
    if (this.canvas.width !== w || this.canvas.height !== h) { this.canvas.width = w; this.canvas.height = h; }
  };

  Water.prototype.draw = function (time, dt) {
    if (!this.ok) return;
    var gl = this.gl, u = this.u;
    // đốm nắng đuổi theo vị trí đích (làm mượt)
    var k = 1 - Math.pow(0.02, dt || 0.016);
    this.sun.x += (this.sunTarget.x - this.sun.x) * k;
    this.sun.y += (this.sunTarget.y - this.sun.y) * k;
    gl.viewport(0, 0, this.canvas.width, this.canvas.height);
    gl.uniform2f(u.uRes, this.canvas.width, this.canvas.height);
    gl.uniform1f(u.uTime, time);
    gl.uniform2f(u.uSun, this.sun.x, this.sun.y);
    gl.uniform4fv(u.uRip, new Float32Array([].concat.apply([], this.ripples)));
    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
    if (!this.drawn) { this.drawn = true; this.host.classList.add('has-water'); }
  };

  /** Thêm sóng tại toạ độ màn hình (clientX, clientY) nếu nằm trong khối; trả về true nếu có thêm */
  Water.prototype.addRipple = function (cx, cy, strength, time) {
    var r = this.rect;
    if (!r || cx < r.left || cx > r.right || cy < r.top || cy > r.bottom) return false;
    var x = (cx - r.left) / r.width, y = 1 - (cy - r.top) / r.height;
    this.ripples[this.nextRipple] = [x, y, time, strength];
    this.nextRipple = (this.nextRipple + 1) % MAX_RIPPLES;
    return true;
  };

  var waters = canvases.map(function (c) { return new Water(c); }).filter(function (w) { return w.ok; });
  if (!waters.length) return;

  var t0 = performance.now();
  var now = function () { return (performance.now() - t0) / 1000; };
  var running = false, last = now();

  function frame() {
    var t = now(), dt = Math.min(t - last, 0.05);
    last = t;
    var more = false;
    waters.forEach(function (w) {
      if (!w.visible) return;
      if (live || t < w.activeUntil) { w.draw(t, dt); more = true; }
    });
    if (more && !document.hidden) requestAnimationFrame(frame); else running = false;
  }
  function kick() { if (!running) { running = true; last = now(); requestAnimationFrame(frame); } }

  function measureAll() { waters.forEach(function (w) { w.resize(); if (w.visible) w.draw(now(), 0.016); }); }

  // Chỉ vẽ khối đang hiện trong màn hình
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        var w = waters.filter(function (x) { return x.host === e.target; })[0];
        if (!w) return;
        w.visible = e.isIntersecting;
        if (w.visible) { w.resize(); w.draw(now(), 0.016); kick(); }
      });
    });
    waters.forEach(function (w) { io.observe(w.host); });
  } else {
    waters.forEach(function (w) { w.visible = true; });
    measureAll(); kick();
  }
  if ('ResizeObserver' in window) {
    var ro = new ResizeObserver(function () { measureAll(); });
    waters.forEach(function (w) { ro.observe(w.host); });
  }
  window.addEventListener('scroll', function () { waters.forEach(function (w) { if (w.visible) w.rect = w.host.getBoundingClientRect(); }); }, { passive: true });
  document.addEventListener('visibilitychange', function () { if (!document.hidden) kick(); });

  if (reduce) return;   // giảm chuyển động: chỉ có khung tĩnh, không gợn sóng

  if (live) {
    // Rê chuột: thêm sóng nhỏ khi con trỏ đi đủ xa; đốm nắng đi theo con trỏ
    window.addEventListener('pointermove', function (e) {
      if (e.pointerType !== 'mouse') return;
      var t = now();
      waters.forEach(function (w) {
        if (!w.visible || !w.rect) return;
        var r = w.rect;
        var inside = e.clientX >= r.left && e.clientX <= r.right && e.clientY >= r.top && e.clientY <= r.bottom;
        if (!inside) { w.sunTarget.x = w.anchor.x; w.sunTarget.y = w.anchor.y; return; }
        w.sunTarget.x = (e.clientX - r.left) / r.width;
        w.sunTarget.y = 1 - (e.clientY - r.top) / r.height;
        var lp = w.lastRipplePos;
        if (!lp || Math.hypot(e.clientX - lp.x, e.clientY - lp.y) > 70 || t - lp.t > 0.5) {
          if (w.addRipple(e.clientX, e.clientY, 0.55, t)) w.lastRipplePos = { x: e.clientX, y: e.clientY, t: t };
        }
      });
    }, { passive: true });
  }
  // Nhấp chuột / chạm: sóng lớn
  window.addEventListener('pointerdown', function (e) {
    if (e.target.closest && e.target.closest('.leaflet-container')) return;
    var t = now();
    waters.forEach(function (w) {
      if (w.visible && w.addRipple(e.clientX, e.clientY, 1.4, t) && !live) w.activeUntil = t + 4;
    });
    kick();
  }, { passive: true });
})();
