#!/usr/bin/env python3
"""Chromatic Diffusion — Canvas Generation (Refined)
Scientific plate aesthetic. Single seed color → tonal system as diffraction plate.
Refined for museum-quality precision."""

import math, os
from PIL import Image, ImageDraw, ImageFont

W, H = 1200, 1600
# Palette — warm perfumery (VOC design system)
BG = (245, 241, 234)
INK = (43, 38, 34)
MUTED = (107, 98, 88)
MUTED_LIGHT = (160, 150, 140)
AMBER = (184, 130, 62)
AMBER_DIM = (150, 107, 50)
AMBER_PALE = (220, 198, 170)
AMBER_GLOW = (235, 215, 190)
BORDER = (227, 221, 210)
BORDER_HAIR = (212, 204, 194)
WHITE_CREAM = (251, 249, 245)

FONT_DIR = r'd:\php\parfume\.agents\skills\canvas-design\canvas-fonts'

def load_font(name, size):
    p = os.path.join(FONT_DIR, name)
    return ImageFont.truetype(p, size) if os.path.exists(p) else ImageFont.load_default()

def blend(c1, c2, t):
    return tuple(int(a * (1 - t) + b * t) for a, b in zip(c1, c2))

def draw_ring(draw, cx, cy, r, fill=None, outline=None, width=1):
    draw.ellipse([cx - r, cy - r, cx + r, cy + r], fill=fill, outline=outline, width=width)

def main():
    img = Image.new('RGB', (W, H), BG)
    draw = ImageDraw.Draw(img)

    # ── FONTS ──
    f_huge     = load_font('InstrumentSerif-Regular.ttf', 88)
    f_large    = load_font('InstrumentSerif-Regular.ttf', 60)
    f_med      = load_font('InstrumentSerif-Regular.ttf', 32)
    f_small    = load_font('InstrumentSerif-Regular.ttf', 18)
    f_xs       = load_font('InstrumentSerif-Regular.ttf', 14)
    f_mono     = load_font('DMMono-Regular.ttf', 10)
    f_mono_sm  = load_font('DMMono-Regular.ttf', 8)

    # ── RADIAL GRADIENT ──
    cx_g, cy_g = W // 2 - 30, int(H * 0.30)
    max_r = int(math.hypot(max(cx_g, W - cx_g), max(cy_g, H - cy_g))) + 50
    # Build smooth gradient ramp
    ramp = [(1 - (i / 300) ** 0.7) for i in range(300, 0, -1)]
    for i, t in enumerate(ramp):
        r = int(max_r * (i + 1) / 300)
        # Multi-stop blend: BG→AMBER_GLOW→AMBER_PALE
        if t < 0.6:
            u = t / 0.6
            col = blend(BG, AMBER_GLOW, u)
        else:
            u = (t - 0.6) / 0.4
            col = blend(AMBER_GLOW, AMBER_PALE, u)
        draw_ring(draw, cx_g, cy_g, r, fill=col)

    # ── GRID FRAME ──
    M = 50  # margin
    # Outer border (hairline)
    draw.rectangle([M - 1, M - 1, W - M, H - M], outline=BORDER_HAIR, width=1)
    # Inner border (hairline)
    draw.rectangle([M, M, W - M - 1, H - M - 1], outline=BORDER, width=1)

    # ── LEFT MEASURE AXIS ──
    ax_x = 56
    ax_y0, ax_y1 = M + 10, H - M - 10
    draw.line([ax_x, ax_y0, ax_x, ax_y1], fill=BORDER_HAIR, width=1)
    for y in range(ax_y0 + 20, ax_y1, 30):
        w = 8 if y % 120 < 4 else 4
        draw.line([ax_x, y, ax_x + w, y], fill=BORDER_HAIR, width=1)
        if y % 120 < 4:
            t = (y - ax_y0) / (ax_y1 - ax_y0)
            val = int((1 - t) * 100)
            draw.text((ax_x + 10, y - 5), f"{val}", fill=MUTED_LIGHT, font=f_mono_sm)

    # ── DIFFRACTION RING SYSTEM ──
    rx, ry = int(W * 0.72), int(H * 0.38)
    radii = [280, 240, 200, 168, 140, 116, 96, 78, 62, 48, 36, 26, 18, 12]
    for i, r in enumerate(radii):
        t = i / len(radii)
        w = 1 if r > 140 else (2 if r > 50 else 3)
        col = blend(AMBER_DIM, AMBER, t ** 0.6)
        draw_ring(draw, rx, ry, r, outline=col, width=w)

    # Ring fill: subtle translucent gradient (innermost rings)
    for i, r in enumerate(radii):
        if r > 140:
            continue
        t2 = 1.0 - i / 6
        gray = int(235 - t2 * 30)
        fill_c = (gray, gray - 5, gray - 12)
        draw_ring(draw, rx, ry, r, fill=fill_c)

    # Center calibration mark
    draw_ring(draw, rx, ry, 5, fill=AMBER)
    # Precision crosshair
    ch = 20
    draw.line([rx - ch, ry, rx + ch, ry], fill=WHITE_CREAM, width=1)
    draw.line([rx, ry - ch, rx, ry + ch], fill=WHITE_CREAM, width=1)
    # Thin diagonal cross
    for mult in [0.3, 0.6]:
        c2 = int(ch * mult)
        col = blend(AMBER, WHITE_CREAM, mult)
        draw.line([rx - c2, ry - c2, rx + c2, ry + c2], fill=col, width=1)
        draw.line([rx - c2, ry + c2, rx + c2, ry - c2], fill=col, width=1)

    # Measurement annotations
    for r, label in [(280, "R₁"), (200, "R₂"), (140, "R₃")]:
        angle = math.radians(-55)
        lx = rx + r * math.cos(angle)
        ly = ry + r * math.sin(angle)
        draw_ring(draw, int(lx), int(ly), 8, fill=WHITE_CREAM, outline=AMBER_DIM, width=1)
        draw.text((lx + 12, ly - 5), label, fill=AMBER_DIM, font=f_mono)

    # Measurement leader to outer ring
    lx0, ly0 = rx + 280, ry
    lx1, ly1 = lx0 + 50, ly0 - 60
    draw.line([lx0, ly0, lx1, ly1], fill=MUTED_LIGHT, width=1)
    draw.text((lx1 + 5, ly1 - 8), "R = 280", fill=MUTED_LIGHT, font=f_mono)

    # ── DOT FIELD (spectral cluster, lower-left zone) ──
    ox, oy = 100, 320
    spacing = 26
    rows, cols = 28, 16
    dot_data = []
    for row in range(rows):
        for col in range(cols):
            x = ox + col * spacing + ((row % 2) * spacing // 2)
            y = oy + row * spacing
            # Check distance from ring center
            dx, dy = x - rx, y - ry
            d2 = dx * dx + dy * dy
            if d2 < 200 ** 2 or d2 > 550 ** 2:
                continue
            # Deterministic size/density variation
            seed = math.sin(row * 0.37 + col * 0.73) * 0.5 + 0.5
            sz = 1.0 + seed ** 1.5 * 3.0
            # Cluster density: closer to ring = smaller/denser
            d = math.sqrt(d2)
            density = max(0, min(1, (d - 200) / 200))
            sz *= (0.7 + density * 0.3)
            gray = int(130 + seed * 70)
            col_c = max(0, min(255, gray)), max(0, min(255, gray - 3)), max(0, min(255, gray - 8))
            sz_i = max(1, int(sz))
            dot_data.append((x, y, sz_i, col_c))

    # Draw dots in order (back to front)
    for x, y, sz_i, col_c in sorted(dot_data, key=lambda d: d[2]):
        draw_ring(draw, x, y, sz_i, fill=col_c)

    # ── SWATCH TONAL GRADIENT ──
    sw_y = int(H * 0.88)
    sw_x0 = 75
    sw_n = 16
    sw_w = 28
    sw_gap = 4
    for i in range(sw_n):
        t = i / (sw_n - 1)
        # Curved progression: spring-like acceleration
        t_curve = t ** 0.75
        r = int(252 * (1 - t_curve) + AMBER[0] * t_curve)
        g = int(242 * (1 - t_curve) + AMBER[1] * t_curve - t_curve * 25)
        b = int(228 * (1 - t_curve) + AMBER[2] * t_curve - t_curve * 35)
        col = (r, g, b)
        x = sw_x0 + i * (sw_w + sw_gap)
        draw.rectangle([x, sw_y, x + sw_w, sw_y + 32], fill=col, outline=BORDER_HAIR, width=1)
        # Tone label
        label = f"{int(t * 100)}"
        draw.text((x + 3, sw_y + 34), label, fill=MUTED_LIGHT, font=f_mono_sm)
    # Swatch label
    draw.text((sw_x0, sw_y - 16), "TONAL PROGRESSION", fill=MUTED, font=f_mono_sm)

    # ── SPECTRAL BAR (top edge) ──
    bar_y = 58
    bar_x0 = 90
    for i in range(80):
        t = i / 79
        col = blend(BG, AMBER, t ** 0.5)
        draw.rectangle([bar_x0 + i * 5, bar_y, bar_x0 + i * 5 + 5, bar_y + 5], fill=col)

    # ── TYPOGRAPHY - MAIN TITLE ──
    tx, ty = 80, 115
    draw.text((tx, ty), "CHROMATIC", fill=INK, font=f_huge)
    draw.text((tx, ty + 96), "DIFFUSION", fill=INK, font=f_huge)
    # Decorative accent line after title
    draw.line([tx, ty + 92, tx + 360, ty + 92], fill=AMBER, width=1)

    # Subtitle
    draw.text((tx, ty + 198), "TONAL SYSTEM  ·  MATERIAL YOU  ·  v2.0", fill=MUTED, font=f_small)
    draw.line([tx, ty + 222, tx + 340, ty + 222], fill=BORDER, width=1)

    # ── SYSTEM PARAMETERS ──
    pm_x = W - 300
    pm_y = 100
    draw.text((pm_x, pm_y), "SYSTEM PARAMETERS", fill=MUTED, font=f_mono)
    draw.line([pm_x, pm_y + 14, pm_x + 200, pm_y + 14], fill=BORDER_HAIR, width=1)

    params = [
        ("SEED",      "#B8823D",    True),
        ("HUE",       "38 Amber",   True),
        ("SURFACE",   "Cream Paper", False),
        ("METHOD",    "Radial Diffusion", False),
        ("TONES",     "16-stop",    False),
        ("SCALE",     "1 : 4.5",    False),
        ("STATE",     "DIFFUSED",   False),
        ("GRID",      "Orthogonal", False),
        ("CLASS",     "Type III",   False),
    ]
    for i, (k, v, emph) in enumerate(params):
        y = pm_y + 24 + i * 20
        c = INK if emph else MUTED
        draw.text((pm_x, y), f"{k:<8}  {v}", fill=c, font=f_mono)

    # ── BOTTOM ZONE ──
    bq_y = H - 180
    draw.text((80, bq_y), "ONE SEED  ·  INFINITE TONES", fill=INK, font=f_med)
    draw.text((80, bq_y + 42), "A single origin wavelength generates", fill=MUTED, font=f_small)
    draw.text((80, bq_y + 64), "its own complete visual language.", fill=MUTED, font=f_small)

    # Spacer line
    draw.line([80, bq_y + 88, 400, bq_y + 88], fill=BORDER_HAIR, width=1)

    # ── ASTERISM / STAR CLUSTER ──
    sx, sy = 170, H - 95
    for angle in range(0, 360, 24):
        rad = math.radians(angle)
        r = 16 + math.sin(rad * 3) * 6
        dx, dy = math.cos(rad) * r, math.sin(rad) * r
        sz = 2.0 + math.sin(rad * 2 + 0.5) * 1.5
        sz_i = max(1, int(sz))
        col = blend(AMBER_DIM, AMBER, (math.sin(rad * 3) * 0.5 + 0.5))
        draw_ring(draw, int(sx + dx), int(sy + dy), sz_i, fill=col)
    draw_ring(draw, sx, sy, 3, fill=AMBER)

    # ── PLATE LABEL ──
    draw.text((80, H - 54), "PLATE 01  ·  CHROMATIC DIFFUSION  ·  v2.0", fill=MUTED, font=f_mono)
    draw.text((W - 250, H - 54), "SILLAGE ABSTRACTION  ·  2026", fill=MUTED_LIGHT, font=f_mono_sm)

    # ── MICRO MARKS (refined quiet zone detail) ──
    for i in range(30):
        angle = (i / 30) * math.pi * 2 + 0.3
        base_x = W - 120
        base_y = H - 110
        r = 18 + math.sin(i * 0.7) * 6
        x = base_x + math.cos(angle) * r
        y = base_y + math.sin(angle) * r
        sz = 1.0 + math.sin(i * 1.1) * 0.8
        draw_ring(draw, int(x), int(y), max(1, int(sz)), fill=MUTED_LIGHT)

    # ── SAVE ──
    out = r'd:\php\parfume\chromatic_diffusion.png'
    img.save(out, 'PNG', compress_level=0)
    print(f"Canvas saved: {out}  ({W}x{H}px)")

if __name__ == '__main__':
    main()
