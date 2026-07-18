# Design System: VOC Atelier — Perfumer Workspace

## 1. Visual Theme & Atmosphere

A restrained, atelier-spacious interface with asymmetric layouts and deliberate emptiness. The atmosphere is warm, editorial, and material — like a perfumer's studio journal or a Le Labo boutique. Density is low (2-3), variance is high (7-8) — asymmetric whitespace, off-center focal points, and generous breathing room between every element. Motion is restrained but considered: subtle fades, no gratuitous transitions.

## 2. Color Palette & Roles

- **Cream Paper** (#F5F1EA) — Primary background, warm alternative to white
- **Pure Surface** (#FBF9F5) — Card and container fill, subtle brightness
- **Deep Ink** (#2B2622) — Primary text, warm off-black
- **Muted Ink** (#6B6258) — Secondary text, descriptions, labels
- **Warm Amber** (#B8823D) — Single accent for CTAs, active states, focus rings
- **Soft Border** (#E3DDD2) — Card borders, 1px structural lines
- **Hairline Border** (#D4CCBE) — Divider lines, hairline-weight
- **Sage Green** (#8A9483) — Alt accent (unused unless needed)
- **Terracotta** (#B5654A) — Alt accent (unused unless needed)

*Max 1 active accent (Amber). No purple. No neon. No pure black.*

## 3. Typography Rules

- **Display/Headlines:** `Cormorant Garamond` — Serif, 500-600 weight, `letter-spacing: 0.02em`. Generous size but controlled. Hierarchy through weight and color, not massive scale jumps.
- **Body/UI:** `Inter` — Sans-serif, 400 weight, relaxed leading (1.6). Max 65ch for reading blocks.
- **Numbers/Tabular:** `Inter` with `font-variant-numeric: tabular-nums` — for price columns, stock counts.
- **Banned:** Times New Roman, Georgia, Garamond (generic), system-ui fallback for premium text contexts.

## 4. Component Stylings

- **Buttons:** Flat with 1px hairline border. Amber fill for primary, transparent with border for secondary/ghost. `border-radius: 2-4px` (not fully rounded). No outer glow. No custom cursors.
- **Cards:** White/paper fill. Never shadowed — use `border: 1px solid var(--border-hair)` instead of drop shadows. Subtle `border-radius: 2-4px`. Used only when elevation serves hierarchy.
- **Inputs/Forms:** Label above input in 11px uppercase micro-label style. `letter-spacing: 0.1em`. `color: var(--ink-muted)`. Focus ring in amber with subtle offset. No floating labels.
- **Loaders:** Gentle amber pulse shimmer matching layout contours. No circular spinners.
- **Empty States:** Composed with typographic balance — "no materials yet" treated as negative space design, not error.
- **Dividers:** 1px solid `--border-hair`. Never thick or dark.

## 5. Layout Principles

- CSS Grid over Flexbox math. No `calc()` percentage hacks.
- Max-width 1400px centered.
- Whitespace: minimum 48-64px between sections. Never crowded.
- Asymmetric grid for dashboard — 1 large "summary" pane + smaller supporting panes. Never identical 3-column grids.
- Auth pages: centered card (max 400px) on cream canvas. No heavy shadows.
- Sidebar app layout: thin border-right divider, dark ink sidebar, cream main area.
- Mobile: single-column collapse below 768px. Touch targets minimum 44px.
- Centered hero sections banned for high-variance contexts.

## 6. Motion & Interaction

- Spring physics for interactive elements: `stiffness: 100, damping: 20`
- Perpetual micro-interactions: subtle amber pulse on active CTA, gentle opacity shift on hover
- No linear easing anywhere
- Animate via `transform` and `opacity` only. Never `top`, `left`, `width`, `height`.
- Staggered reveals on list mounts (50ms cascade delay)
- Page transitions: `opacity 0.3s ease`

## 7. Anti-Patterns (Banned)

- No emojis anywhere
- No pure black (`#000000`)
- No neon/outer glow shadows
- No oversaturated accent colors
- No 3-column equal card grids
- No centered hero sections
- No placeholder names ("John Doe", "Acme Corp")
- No AI copywriting clichés ("Elevate", "Seamless", "Unleash", "Next-Gen")
- No filler UI text ("Scroll to explore", "Swipe down")
- No broken image links — use inline SVG or picsum.photos
- No floating labels in forms — label always above input
- No custom mouse cursors
