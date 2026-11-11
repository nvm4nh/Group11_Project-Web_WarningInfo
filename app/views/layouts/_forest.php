<?php
/* =========================================================
   Hoạ tiết "tán rừng giữa thành phố" dùng chung cho mọi trang.
   Toàn bộ cây, lá, đường chân trời đều là SVG vẽ bằng mã (không dùng ảnh ngoài):
     - wi_treeline()  : dải cây (cây tán tròn + dừa) sinh từ một seed cố định → mỗi lần tải trang ra cùng một khu rừng
     - wi_skyline()   : đường chân trời thành phố phía xa
     - wi_sprig_svg() : cành lá đặt ở góc các khối (lắc nhẹ theo gió khi cuộn trang – forest.js)
   Tệp này được nạp 1 lần trong layouts/header.php, ngay sau thẻ <body>.
   ========================================================= */

if (!function_exists('wi_rng')) {

    /** Bộ sinh số giả ngẫu nhiên riêng (LCG): cùng seed → cùng kết quả, không đụng tới mt_rand() của hệ thống */
    function wi_rng($seed) {
        $s = (int)$seed % 2147483647;
        if ($s <= 0) $s += 2147483646;
        return function ($min = 0.0, $max = 1.0) use (&$s) {
            $s = ($s * 48271) % 2147483647;
            return $min + ($max - $min) * (($s - 1) / 2147483646);
        };
    }

    /** Làm gọn số cho thuộc tính SVG: 12.50 → 12.5, 7.00 → 7 */
    function wi_n($v) {
        return rtrim(rtrim(number_format((float)$v, 1, '.', ''), '0'), '.');
    }

    /** Cây tán tròn: thân thuôn + 4 cụm lá */
    function wi_round_tree($x, $base, $h, $r) {
        $tw  = max(3, $h * .07);
        $cy  = $base - $h * .66;
        $rad = $h * .33;
        $s  = '<path d="M' . wi_n($x - $tw / 2) . ' ' . wi_n($base + 2) . 'L' . wi_n($x - $tw / 3) . ' ' . wi_n($cy)
            . 'H' . wi_n($x + $tw / 3) . 'L' . wi_n($x + $tw / 2) . ' ' . wi_n($base + 2) . 'Z"/>';
        $lobes = [
            [0, 0, 1],
            [-.72 + $r(-.1, .1), .34, .68 + $r(-.08, .08)],
            [.76 + $r(-.1, .1), .3, .64 + $r(-.08, .08)],
            [$r(-.25, .25), -.52, .7 + $r(-.08, .08)],
        ];
        foreach ($lobes as [$dx, $dy, $k]) {
            $s .= '<circle cx="' . wi_n($x + $dx * $rad) . '" cy="' . wi_n($cy + $dy * $rad) . '" r="' . wi_n($rad * $k) . '"/>';
        }
        return $s;
    }

    /** Cây dừa: thân cong + 7 tàu lá rủ */
    function wi_palm($x, $base, $h, $r) {
        $lean = $r(-.22, .22) * $h;
        $tx = $x + $lean;
        $ty = $base - $h;
        $tw = max(2.5, $h * .045);
        $s = '<path d="M' . wi_n($x) . ' ' . wi_n($base + 2) . 'Q' . wi_n($x + $lean * .15) . ' ' . wi_n($base - $h * .55) . ' ' . wi_n($tx) . ' ' . wi_n($ty)
           . '" fill="none" stroke="currentColor" stroke-width="' . wi_n($tw) . '" stroke-linecap="round"/>';
        $L = $h * .44;
        $frond = 'M0 0C' . wi_n($L * .3) . ' ' . wi_n(-$L * .3) . ' ' . wi_n($L * .72) . ' ' . wi_n(-$L * .26) . ' ' . wi_n($L) . ' ' . wi_n($L * .16)
               . 'C' . wi_n($L * .72) . ' ' . wi_n(-$L * .1) . ' ' . wi_n($L * .34) . ' ' . wi_n(-$L * .12) . ' 0 0Z';
        foreach ([1, -1] as $side) {
            foreach ([-74, -42, -12, 20] as $i => $deg) {
                if ($side === -1 && $i === 3) continue;   // bên trái 3 tàu, bên phải 4 tàu cho tự nhiên
                $a = $deg + $r(-8, 8);
                $s .= '<path d="' . $frond . '" transform="translate(' . wi_n($tx) . ' ' . wi_n($ty) . ')'
                    . ($side === -1 ? ' scale(-1 1)' : '') . ' rotate(' . wi_n($a) . ')"/>';
            }
        }
        return $s;
    }

    /**
     * Dải cây chạy ngang: trả về các phần tử SVG (tô bằng currentColor của thẻ cha).
     * $base là cao độ mặt đất; phần dưới mặt đất được tô kín tới $base + fill.
     */
    function wi_treeline($seed, $width = 1440, $base = 200, array $opts = []) {
        $o = $opts + ['count' => 26, 'min' => 60, 'max' => 130, 'palms' => .22, 'fill' => 80, 'bump' => 10];
        $r = wi_rng($seed);
        $step = $width / $o['count'];
        $out = '';
        for ($i = 0; $i <= $o['count']; $i++) {
            $x = $i * $step + $r(-.36, .36) * $step;
            $h = $r($o['min'], $o['max']);
            $out .= $r() < $o['palms'] ? wi_palm($x, $base, $h * 1.18, $r) : wi_round_tree($x, $base, $h, $r);
        }
        // Mặt đất nhấp nhô nối các gốc cây
        $d = 'M0 ' . wi_n($base);
        $seg = $width / 6;
        for ($i = 0; $i < 6; $i++) {
            $d .= 'Q' . wi_n($i * $seg + $seg / 2) . ' ' . wi_n($base - $r(2, $o['bump'])) . ' ' . wi_n(($i + 1) * $seg) . ' ' . wi_n($base);
        }
        $d .= 'V' . wi_n($base + $o['fill']) . 'H0Z';
        return $out . '<path d="' . $d . '"/>';
    }

    /** Đường chân trời thành phố: các khối nhà cao thấp + một toà tháp thuôn đỉnh */
    function wi_skyline($seed, $width = 1440, $base = 200, $maxH = 150) {
        $r = wi_rng($seed);
        $out = '';
        $x = -10;
        $towerAt = $width * .64;
        $towerDone = false;
        while ($x < $width + 10) {
            $w = $r(26, 58);
            if (!$towerDone && $x >= $towerAt) {
                $h = $maxH * 1.5;
                $out .= '<path d="M' . wi_n($x) . ' ' . $base . 'L' . wi_n($x + 6) . ' ' . wi_n($base - $h * .72) . 'L' . wi_n($x + 15) . ' ' . wi_n($base - $h)
                      . 'L' . wi_n($x + 24) . ' ' . wi_n($base - $h * .72) . 'L' . wi_n($x + 30) . ' ' . $base . 'Z"/>';
                $x += 36;
                $towerDone = true;
                continue;
            }
            $h = $r($maxH * .25, $maxH);
            $out .= '<rect x="' . wi_n($x) . '" y="' . wi_n($base - $h) . '" width="' . wi_n($w) . '" height="' . wi_n($h + 4) . '"/>';
            if ($r() < .3) {   // mái tầng thượng
                $out .= '<rect x="' . wi_n($x + $w * .3) . '" y="' . wi_n($base - $h - 8) . '" width="' . wi_n($w * .4) . '" height="9"/>';
            }
            $x += $w + $r(2, 9);
        }
        return $out;
    }

    /**
     * Một cành lá hoàn chỉnh (thẻ <svg>) – đặt bằng class vị trí trong CSS.
     * $kind: 'sprig' (cành lá bầu) hoặc 'frond' (tàu lá dài kiểu dương xỉ / dừa)
     */
    function wi_sprig_svg($class = '', $kind = 'sprig') {
        $box = $kind === 'frond' ? '0 0 360 200' : '0 0 320 230';
        return '<svg class="wi-sprig ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" viewBox="' . $box
             . '" aria-hidden="true" focusable="false"><use href="#wi-' . $kind . '"/></svg>';
    }

    /** Hai cành lá rủ ở góc phải dải đầu trang (.page-canopy / .page-header) của các trang trong */
    function wi_canopy() {
        return wi_sprig_svg('canopy-sprig canopy-sprig-b sway', 'frond') . wi_sprig_svg('canopy-sprig canopy-sprig-a sway');
    }

    /** Lá mọc so le dọc theo một đường cong Bézier bậc 3 (dùng để dựng symbol cành lá) */
    function wi_leaves_on_curve(array $p, $count, $leafId, $sizeFrom, $sizeTo, $ratio, $spread, $seed) {
        $r = wi_rng($seed);
        $out = '';
        for ($i = 0; $i < $count; $i++) {
            $t = .12 + .86 * ($i / max(1, $count - 1));
            $mt = 1 - $t;
            $x = $mt ** 3 * $p[0] + 3 * $mt ** 2 * $t * $p[2] + 3 * $mt * $t ** 2 * $p[4] + $t ** 3 * $p[6];
            $y = $mt ** 3 * $p[1] + 3 * $mt ** 2 * $t * $p[3] + 3 * $mt * $t ** 2 * $p[5] + $t ** 3 * $p[7];
            $dx = 3 * $mt ** 2 * ($p[2] - $p[0]) + 6 * $mt * $t * ($p[4] - $p[2]) + 3 * $t ** 2 * ($p[6] - $p[4]);
            $dy = 3 * $mt ** 2 * ($p[3] - $p[1]) + 6 * $mt * $t * ($p[5] - $p[3]) + 3 * $t ** 2 * ($p[7] - $p[5]);
            $tan = rad2deg(atan2($dy, $dx));
            $last = $i === $count - 1;
            $side = $i % 2 === 0 ? -1 : 1;
            $ang = $last ? $tan : $tan + $side * ($spread + $r(-7, 7));
            $w = $sizeFrom + ($sizeTo - $sizeFrom) * $t;
            $h = $w * $ratio;
            $out .= '<g transform="translate(' . wi_n($x) . ' ' . wi_n($y) . ') rotate(' . wi_n($ang) . ')"'
                  . ($i % 3 === 1 ? ' class="lf-light"' : '') . '>'
                  . '<use href="#' . $leafId . '" width="' . wi_n($w) . '" height="' . wi_n($h) . '" y="' . wi_n(-$h / 2) . '"/></g>';
        }
        return $out;
    }
}
?>
<!-- Bộ ký hiệu SVG dùng lại (lá, cành, tàu lá) – các trang gọi bằng <use href="#wi-..."> -->
<svg width="0" height="0" class="wi-defs" aria-hidden="true" focusable="false">
  <defs>
    <symbol id="wi-leaf" viewBox="0 0 100 48">
      <path d="M1 24C20 3 64-3 99 24 64 51 20 45 1 24Z" fill="currentColor"/>
      <path d="M2 24H95M24 24l11-10M24 24l11 10M44 24l12-12M44 24l12 12M64 24l11-10M64 24l11 10" fill="none" stroke="var(--wi-vein, rgba(255,255,255,.22))" stroke-width="1.3" stroke-linecap="round"/>
    </symbol>
    <symbol id="wi-blade" viewBox="0 0 100 18">
      <path d="M0 9C22 0 70-1 100 9 70 19 22 18 0 9Z" fill="currentColor"/>
    </symbol>
    <symbol id="wi-sprig" viewBox="0 0 320 230">
      <path d="M8 222C70 176 160 128 312 22" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
      <?= wi_leaves_on_curve([8, 222, 70, 176, 160, 128, 312, 22], 9, 'wi-leaf', 118, 62, .48, 46, 11) ?>
    </symbol>
    <symbol id="wi-frond" viewBox="0 0 360 200">
      <path d="M6 190C90 150 210 80 352 14" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"/>
      <?= wi_leaves_on_curve([6, 190, 90, 150, 210, 80, 352, 14], 22, 'wi-blade', 96, 34, .18, 58, 29) ?>
    </symbol>
  </defs>
</svg>
