# AetherCore Design Principles

## Iconography

### Rules
- ❌ NO emojis in UI — anywhere, ever
- ✅ Inline SVG icons only (Feather/Lucide style)
- ✅ Icons inherit `currentColor` for theme compatibility
- ✅ Stroke width: 2px for inline, 1.6-1.8px for larger
- ✅ Size: 12-16px inline next to text, 20-24px for feature icons
- ✅ Use flex or `vertical-align: -1px` for inline icon alignment

### Why
- Emojis render differently across OS/browsers (breaks consistency)
- Emojis don't respect theme colors (breaks custom themes)
- SVG matches the XP/early-2000s aesthetic
- SVG scales cleanly on all screen densities
- SVG can be animated with CSS (emojis can't)

### Icon Reference

All icons use this base:
```html
<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
	<!-- paths here -->
</svg>
```

**People / Members:**

```html
<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
<circle cx="9" cy="7" r="4"/>
<path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
```

**Send / Paper Plane:**

```html
<line x1="22" y1="2" x2="11" y2="13"/>
<polygon points="22 2 15 22 11 13 2 9 22 2"/>
```

**Bell / Notifications:**

```html
<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
<path d="M13.73 21a2 2 0 0 1-3.46 0"/>
```

**Plus / Add:**

```html
<line x1="12" y1="5" x2="12" y2="19"/>
<line x1="5" y1="12" x2="19" y2="12"/>
```

**More:** [Feather Icons](https://feathericons.com/) or [Lucide](https://lucide.dev/)

## Layout & Spacing
*(to be filled in as patterns emerge)*

## Color Palette
*(to be filled in — currently: #f0edd8 surface, #b0a8a0 border, #3a7bd5 accent)*

## Typography

- Font: `'Segoe UI', Tahoma, Geneva, Verdana, sans-serif`
- Sizes: 0.55rem - 0.9rem for UI, 1.5rem+ for headers
- Weight: 400 body, 600 labels, 700 headings

## Future Theme Compatibility
**Every new component must work with these themes:**

- `aethercore` (default XP)
- Future themes will override `--surface-primary`, `--accent`, etc.
**Rule:** Never hardcode a hex color in a Blade template. Always use CSS variables or existing classes.