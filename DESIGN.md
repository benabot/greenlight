# Design System Document: High-End Eco-Editorial

## 1. Overview & Creative North Star
**Creative North Star: "The Digital Lithograph"**
This design system moves away from the "disposable" nature of the modern web toward the permanence and intentionality of high-end print editorial. By stripping away heavy assets, we embrace a "Low-Carbon, High-Couture" philosophy. The aesthetic is defined by **Organic Minimalism**: a rejection of the rigid, boxy bootstrap-standard in favor of expansive whitespace, intentional asymmetry, and a tactile, layered depth. We are not just building a WordPress theme; we are creating a digital environment that breathes.

## 2. Colors & Tonal Architecture
The palette is rooted in the natural world—off-whites that mimic recycled heavy-stock paper and deep charcoals that feel like dried ink.

### The "No-Line" Rule
Traditional 1px borders are strictly prohibited for sectioning. They create visual noise and "trap" the eye. Instead, define boundaries through **Chromatic Shifts**. Use `surface-container-low` (#f4f4ee) against the main `background` (#faf9f4) to denote a change in content.

### Surface Hierarchy & Nesting
Treat the UI as a physical stack of paper.
*   **Base Layer:** `surface` (#faf9f4) — The canvas.
*   **Secondary Layer:** `surface-container-low` (#f4f4ee) — For subtle sectioning (e.g., a newsletter sign-up block).
*   **Elevated Components:** `surface-container-lowest` (#ffffff) — Reserved for cards or interactive modules to create a "bleached" highlight effect.

### Glass & Gradient (The "Digital Dew")
To add soul without weight, use **Glassmorphism** for floating elements (like navigation bars). Use `surface` at 80% opacity with a `backdrop-blur` of 12px. For primary CTAs, apply a subtle linear gradient from `primary` (#4c6547) to `primary_dim` (#41593c) at a 135-degree angle. This provides a soft, satin-like finish that feels premium rather than flat.

## 3. Typography: System-Centric Authority
We utilize the `inter` system font stack to ensure zero-latency loading while maintaining a professional, architectural feel.

*   **Display (lg/md):** Used for "Statement Headlines." These should be set with a tighter letter-spacing (-0.02em) to mimic high-end fashion mastheads.
*   **Headline (lg/md/sm):** Your primary navigational markers. Ensure a generous `margin-bottom` (using Spacing `8` or `10`) to let the "ink" breathe on the "paper."
*   **Body (lg/md):** The workhorse. `body-lg` (1rem) is the standard. Use `on_surface_variant` (#5c6058) for secondary body text to reduce visual weight and carbon footprint on OLED screens.
*   **Label (md/sm):** All-caps, tracked out (+0.05em), used for categories and metadata.

## 4. Elevation & Depth
Depth is a matter of light and shadow, not lines and boxes.

*   **Tonal Layering:** Avoid shadows for static elements. A `surface-container-lowest` card sitting on a `surface-container` background provides sufficient contrast.
*   **Ambient Shadows:** For interactive "floating" states (like a dropdown), use a "Whisper Shadow": `0 20px 40px rgba(47, 52, 45, 0.04)`. The color is derived from `on_surface` (#2f342d), not pure black, ensuring the shadow feels like a natural obstruction of light.
*   **The Ghost Border:** If accessibility requires a stroke, use `outline_variant` (#afb3aa) at 15% opacity. It should be felt, not seen.

## 5. Components & Primitive Styling

### Buttons: The "Satin" Touch
*   **Primary:** Background: `primary` (#4c6547), Text: `on_primary`. Roundedness: `md` (0.375rem). No shadow.
*   **Secondary:** Background: `secondary_container` (#e0e3df), Text: `on_secondary_container`.
*   **Tertiary:** Text-only with an underline that matches `primary` at 20% opacity, thickening to 100% on hover.

### Cards & Lists: Editorial Flow
*   **Rule:** Forbid the use of divider lines.
*   **Implementation:** Use Spacing `12` (4rem) to separate list items. For cards, use a background shift to `surface-container-low`.
*   **Eco-Feature:** Image placeholders should use a CSS-based `surface_variant` (#e0e4d9) color block while loading to prevent layout shift (CLS).

### Input Fields: The Minimalist Ledger
*   **Default:** A bottom-only border using `outline_variant`.
*   **Focus:** Transition the bottom border to `primary` (#4c6547) and shift the background to `surface-container-lowest` (#ffffff).

### Signature Component: "The Carbon Badge"
A specialized `chip` using `tertiary_container` (#e5f4c9) and `on_tertiary_container` (#505d3c). This high-contrast, low-saturation pill should be used to highlight eco-metrics (e.g., "0.2g CO2 per page view").

## 6. Do’s and Don’ts

### Do:
*   **Do** embrace asymmetry. Align a headline to the left and body text to a 60% width container on the right to create editorial tension.
*   **Do** use the Spacing Scale religiously. If a gap looks "okay," double it.
*   **Do** use `primary` sparingly. It is a highlighter, not a bucket of paint.

### Don’t:
*   **Don’t** use 100% black (#000000). Use `on_background` (#2f342d) to keep the contrast soft and readable.
*   **Don’t** use "Drop Shadows" on buttons. Use a slight color shift to `primary_dim` on hover.
*   **Don’t** use icons where text will suffice. An icon is a heavy asset; a well-chosen word in `label-md` is eco-conception.