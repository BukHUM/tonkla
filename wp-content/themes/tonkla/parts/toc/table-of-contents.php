<?php
/**
 * Table of Contents Template Part
 */

defined( 'ABSPATH' ) || exit;

$enable_toc = tonkla_is_toc_enabled();
if (!$enable_toc) {
    return;
}

// Get items
$items = tonkla_get_toc_items();
if (empty($items)) {
    return;
}

$title = carbon_get_theme_option('toc_title') ?: 'สารบัญ';
?>

<!-- Table of Contents -->
<div x-data="toc()" x-init="init()"
    class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 md:p-5 sticky top-24 transition-all duration-300">

    <div class="flex items-center justify-between gap-3 cursor-pointer" :class="expanded ? 'mb-3 md:mb-4' : 'mb-0'" @click="toggle()">
        <h2 class="font-heading font-bold text-slate-800 text-base md:text-lg flex items-center gap-2">
            <i class="fa-solid fa-list-ul text-brand" aria-hidden="true"></i>
            <?php echo esc_html($title); ?>
        </h2>
        <button type="button" class="text-slate-400 hover:text-brand transition transform duration-200"
            :class="{ 'rotate-180': !expanded }"
            :aria-expanded="expanded ? 'true' : 'false'"
            aria-label="<?php echo esc_attr__( 'Toggle table of contents', 'tonkla' ); ?>">
            <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
        </button>
    </div>

    <div x-show="expanded" x-collapse class="overflow-hidden transition-all duration-300">
        <nav class="toc-nav relative">
            <ul class="space-y-1 relative">
                <?php foreach ($items as $item):
                    $indent = ($item['level'] - 2) * 12; // Base indent for H2 is 0
                    if ($indent < 0) {
                        $indent = 0;
                    }
                    ?>
                    <li style="padding-left: <?php echo (int) $indent; ?>px;">
                        <a href="#<?php echo esc_attr($item['id']); ?>"
                            @click.prevent="scrollTo('<?php echo esc_js($item['id']); ?>')"
                            class="block py-1.5 pl-4 text-sm border-l-2 transition-all duration-200 hover:text-brand hover:border-brand-light"
                            :class="activeId === '<?php echo esc_js($item['id']); ?>' 
                                ? 'text-brand font-medium border-brand bg-brand/5 rounded-r' 
                                : 'text-slate-500 border-transparent hover:bg-slate-50 rounded-r'">
                            <?php echo esc_html($item['text']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('toc', () => ({
            expanded: true,
            activeId: '',
            headings: [],

            init() {
                // Check screen size for initial state
                if (window.innerWidth < 768) {
                    this.expanded = false;
                }

                // Populate headings list
                this.headings = <?php echo wp_json_encode(array_values(array_column($items, 'id'))); ?>;

                // Setup Intersection Observer
                this.setupObserver();
            },

            toggle() {
                this.expanded = !this.expanded;
            },

            scrollTo(id) {
                const element = document.getElementById(id);
                if (element) {
                    // Offset for sticky header (presumed ~80px)
                    const offset = 80;
                    const elementPosition = element.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: "smooth"
                    });

                    // Manually set active ID for immediate feedback
                    this.activeId = id;
                }
            },

            setupObserver() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && entry.intersectionRatio > 0) {
                            this.activeId = entry.target.id;
                        }
                    });
                }, {
                    rootMargin: '-100px 0px -60% 0px', // Adjust "active" zone to top part of screen
                    threshold: 0.1
                });

                this.headings.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) observer.observe(el);
                });
            }
        }));
    });
</script>