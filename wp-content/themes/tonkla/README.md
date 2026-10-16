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

-   `assets/` - Static assets (CSS, JS, Images).
-   `inc/` - PHP includes (Carbon Fields definitions, helper classes).
-   `parts/` - Template parts for different modules (Hero, Features, FAQ, etc.).
-   `page-flexible.php` - The main template for the Page Builder.
-   `functions.php` - Theme bootstrapping and configuration.

## Page Builder Modules

The theme includes a powerful Flexible Page Builder with the following modules:
-   **Hero Section**: Main landing banners (Home, Service, Alt).
-   **Service Grid**: Grid layout for features/services with tech stack icons.
-   **Pricing Table**: Comparative pricing plans.
-   **Feature Split**: Left/Right content split (great for software showcases).
-   **Content Block**: Flexible text and media block (Image or Code).
-   **Call to Action (CTA)**: Prominent buttons and text.
-   **n8n Workflow**: Visualization for automation steps.
-   **Stats / Counter**: Display key metrics (e.g. Uptime).
-   **Process Timeline**: Step-by-step workflow guide.
-   **FAQ**: Accordion style questions.
-   **Testimonials**: Customer reviews grid.
