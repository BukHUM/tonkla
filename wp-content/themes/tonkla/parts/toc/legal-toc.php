<?php
/**
 * Manual Legal Page ToC
 */

defined( 'ABSPATH' ) || exit;
$slug = get_post_field('post_name', get_queried_object_id());
$items = array();

if ($slug === 'privacy-policy' || $slug === 'privacy') {
    $items = array(
        array('id' => 'intro', 'text' => 'บทนำ'),
        array('id' => 'collection', 'text' => '1. ข้อมูลที่จัดเก็บ'),
        array('id' => 'usage', 'text' => '2. วัตถุประสงค์'),
        array('id' => 'disclosure', 'text' => '3. การเปิดเผยข้อมูล'),
        array('id' => 'retention', 'text' => '4. ระยะเวลาจัดเก็บ'),
        array('id' => 'rights', 'text' => '5. สิทธิของท่าน'),
        array('id' => 'cookies', 'text' => '6. คุกกี้ (Cookies)'),
        array('id' => 'contact', 'text' => '7. ช่องทางติดต่อ'),
    );
} elseif ($slug === 'terms-of-service' || $slug === 'terms') {
    $items = array(
        array('id' => 'agreement', 'text' => 'ข้อตกลงและคำนิยาม'),
        array('id' => 'usage', 'text' => '1. การใช้งานบริการ'),
        array('id' => 'content', 'text' => '2. เนื้อหาที่ต้องห้าม'),
        array('id' => 'payment', 'text' => '3. การชำระเงินและต่ออายุ'),
        array('id' => 'security', 'text' => '4. ความปลอดภัยของข้อมูล'),
        array('id' => 'termination', 'text' => '5. การยกเลิกบริการ'),
        array('id' => 'liability', 'text' => '6. ข้อจำกัดความรับผิดชอบ'),
    );
} else {
    return;
}
?>
<div x-data="toc()" x-init="init()"
    class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 sticky top-24 transition-all duration-300">
    <div class="flex items-center justify-between mb-4 cursor-pointer" @click="toggle()">
        <h3 class="font-heading font-bold text-slate-800 text-lg flex items-center gap-2">
            <i class="fa-solid fa-list-ul text-brand"></i>
            สารบัญเนื้อหา
        </h3>
        <button class="text-slate-400 hover:text-brand transition transform duration-200"
            :class="{ 'rotate-180': !expanded }">
            <i class="fa-solid fa-chevron-up"></i>
        </button>
    </div>

    <div x-show="expanded" x-collapse class="overflow-hidden transition-all duration-300">
        <nav class="toc-nav relative">
            <ul class="space-y-1 relative">
                <?php foreach ($items as $item): ?>
                    <li>
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
            headings: <?php echo wp_json_encode(array_values(array_column($items, 'id'))); ?>,

            init() {
                if (window.innerWidth < 768) { this.expanded = false; }
                this.setupObserver();
            },

            toggle() { this.expanded = !this.expanded; },

            scrollTo(id) {
                const element = document.getElementById(id);
                if (element) {
                    const offset = 80;
                    const elementPosition = element.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    window.scrollTo({ top: offsetPosition, behavior: "smooth" });
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
                }, { rootMargin: '-100px 0px -60% 0px', threshold: 0.1 });

                this.headings.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) observer.observe(el);
                });
            }
        }));
    });
</script>