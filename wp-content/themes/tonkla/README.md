# Tonkla IT WordPress Theme

Custom WordPress theme for Tonkla IT, built with Tailwind CSS and Carbon Fields.

**Author:** [Tonkla IT Team](https://www.konderntang.com)

## Requirements

-   **WordPress**: 6.0+
-   **PHP**: 7.4+
-   **Plugins Required**:
    -   [Carbon Fields](https://carbonfields.net/) (Essential for Theme Options & Page Builder)
    -   [Polylang](https://wordpress.org/plugins/polylang/) (Optional, for multi-language support)

## Development Setup

This theme uses Tailwind CSS for styling. To make changes to the design, you need Node.js installed.

1.  **Install Dependencies**
    ```bash
    npm install
    ```

2.  **Start Development Server (Watch Mode)**
    Refreshes CSS on file changes.
    ```bash
    npm run dev
    ```

3.  **Build for Production**
    Minifies CSS for live site.
    ```bash
    npm run build
    ```

## Theme Structure

-   `assets/` - Static assets (CSS, JS, Images, builder wireframes, FA icon catalog).
-   `inc/` - PHP includes (Carbon Fields, Page Builder helpers, SVG upload).
-   `inc/builder/` - Page Builder factories, module registry, icon helper, admin assets.
-   `parts/` - Template parts for different modules (Hero, Features, FAQ, etc.).
-   `page-flexible.php` - The main template for the Page Builder.
-   `functions.php` - Theme bootstrapping and configuration.
-   `vendor/` - Composer deps (`enshrined/svg-sanitize` for SVG icon uploads).

## Page Builder (UX)

Admin editor enhancements (template **Tonkla Page Builder**):

-   **Module picker** with wireframe previews (`assets/img/builder/*.svg`).
-   **Icon browser** — browse/search FontAwesome instead of typing class names.
-   **Custom icon upload** — PNG / WebP / SVG (SVG sanitized; admins with `manage_options` only).
-   Module tabs show content titles via header templates.

Registry / factories live in `inc/builder/`. Front-end icons render via `tonkla_render_icon()`.

## Page Builder Modules

-   **Hero Section**: Main landing banners (Home, Service, Alt).
-   **Service Grid**: Grid layout for features/services with tech stack icons.
-   **Tech Stack**: Technology / logo grid.
-   **Portfolio**: Project / case-study grid with image, category, client, link.
-   **Pricing Table**: Comparative pricing plans.
-   **Feature Split**: Left/Right content split (great for software showcases).
-   **Content Block**: Flexible text and media block (Image or Code).
-   **Call to Action (CTA)**: Prominent buttons and text.
-   **n8n Workflow**: Visualization for automation steps.
-   **Stats / Counter**: Display key metrics (e.g. Uptime).
-   **Process Timeline**: Step-by-step workflow guide.
-   **FAQ**: Accordion style questions.
-   **Testimonials**: Customer reviews grid.
-   **Contact Section**: Form + company info.
-   **Map & FAQ**: Google Map embed + FAQ.
