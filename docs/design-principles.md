# AetherCore Design Principles

## Iconography
- ❌ NO emojis in UI
- ✅ Use inline SVG icons (Feather/Lucide style)
- ✅ Icons must inherit `currentColor` for theme compatibility
- ✅ Stroke width: 2px for small icons, 1.6-1.8px for larger
- ✅ Size: 12-16px for inline, 20-24px for feature icons

## Why
- Emojis render differently across OS/browsers (breaks consistency)
- Emojis don't respect theme colors
- SVG matches the XP/early-2000s aesthetic (icons were vector images)
- SVG scales cleanly on all screen densities

## Icon Set Preference
Feather Icons / Lucide style:
- Thin strokes
- Minimal detail
- Round line caps
- 24x24 base viewBox