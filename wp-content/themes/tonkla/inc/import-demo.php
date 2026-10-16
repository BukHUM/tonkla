<?php
/**
 * Demo Importer for Tonkla IT
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_tonkla_import_demo', 'tonkla_process_demo_import' );

/**
 * Create or update a flexible demo page.
 *
 * @param string $title   Page title.
 * @param string $slug    Page slug.
 * @param array  $modules Carbon Fields page modules.
 * @return int Page ID or 0.
 */
function tonkla_create_demo_page( $title, $slug, $modules ) {
	$page    = get_page_by_path( $slug );
	$page_id = 0;

	if ( $page ) {
		$page_id = (int) $page->ID;
	} else {
		$page_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);
	}

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-flexible.php' );
		carbon_set_post_meta( $page_id, 'page_modules', $modules );
		return (int) $page_id;
	}

	return 0;
}

/**
 * Create or update a standard content page.
 *
 * @param string $title        Page title.
 * @param string $slug         Page slug.
 * @param string $html_content Page HTML content.
 * @return int Page ID or 0.
 */
function tonkla_create_standard_page( $title, $slug, $html_content ) {
	$page      = get_page_by_path( $slug );
	$page_args = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $html_content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
	);

	if ( $page ) {
		$page_args['ID'] = $page->ID;
		$page_id         = wp_update_post( $page_args );
	} else {
		$page_id = wp_insert_post( $page_args );
	}

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'default' );
		carbon_set_post_meta( $page_id, 'page_modules', array() );
		return (int) $page_id;
	}

	return 0;
}

/**
 * Process demo content import from Tools screen.
 */
function tonkla_process_demo_import() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized', 'tonkla' ) );
	}

	check_admin_referer( 'tonkla_import_demo', '_tonkla_import_nonce' );

	$allowed_pages = array(
		'home',
		'hosting',
		'n8n',
		'domains',
		'contact',
		'about',
		'pentest',
		'proxmox',
		'web_dev',
		'works',
		'ssl',
		'system_impl',
		'privacy',
		'solutions',
		'legal',
		'mobile_app',
	);

	$raw_pages       = isset( $_POST['pages'] ) ? (array) wp_unslash( $_POST['pages'] ) : array();
	$selected_pages  = array_values( array_intersect( array_map( 'sanitize_key', $raw_pages ), $allowed_pages ) );
	$tools_admin_url = admin_url( 'admin.php?page=crb_carbon_fields_container_tools' );

	if ( empty( $selected_pages ) ) {
		wp_safe_redirect( add_query_arg( 'import_status', 'empty', $tools_admin_url ) );
		exit;
	}

    /* -------------------------------------------------------------------------
                                 1. HOME
       ------------------------------------------------------------------------- */
    if (in_array('home', $selected_pages)) {
        $home_modules = array(
            // 1. HERO SECTION
            array(
                '_type' => 'hero_module',
                'hero_type' => 'home',
                'badge' => '🚀 One-Stop Service for Digital Business',
                'headline' => 'ครบจบทุกเรื่อง <span class="text-brand">ไอที</span> <br class="hidden md:block" /> ตั้งแต่ <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-brand-light">โครงสร้างพื้นฐาน</span> จนถึง <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-light to-yellow-300">ซอฟต์แวร์</span>',
                'description' => 'บริการจดโดเมน Cloud Hosting อีเมลองค์กร ไปจนถึงการพัฒนา Application และระบบ Automation พร้อมระบบจัดการหลังบ้านที่ทันสมัยสำหรับธุรกิจและ Reseller',
                'bg_image' => '', // Default dark bg via CSS if empty, or can add a specific URL
                'bg_overlay' => '',
                'enable_domain_search' => true,
                'buttons' => array(
                    array('text' => 'ดูแพ็กเกจทั้งหมด', 'link' => 'domains', 'style' => 'dark', 'icon' => 'fa-solid fa-list-ul', 'icon_color' => ''),
                    array('text' => 'บริการ n8n Automation', 'link' => 'n8n-automation', 'style' => 'secondary', 'icon' => 'fa-solid fa-share-nodes', 'icon_color' => 'text-n8n')
                )
            ),

            // 2. STATS MODULE
            array(
                '_type' => 'stats_module',
                'items' => array(
                    array('stat_value' => '99.9%', 'label' => 'Uptime Guarantee'),
                    array('stat_value' => '500+', 'label' => 'Active Servers'),
                    array('stat_value' => '24/7', 'label' => 'Support Team'),
                    array('stat_value' => '100%', 'label' => 'Thai Ownership'),
                )
            ),

            // 3. SERVICE GRID (Infrastructure)
            array(
                '_type' => 'service_grid',
                'section_title' => 'บริการโดเมนและโฮสติ้ง',
                'section_subtitle' => 'Infrastructure',
                'layout' => 'cols-3',
                'items' => array(
                    array(
                        'icon' => 'fa-solid fa-globe',
                        'title' => 'จดโดเมน',
                        'description' => 'จดทะเบียนโดเมนใหม่ ย้ายโดเมน (Transfer) และต่ออายุ จัดการ DNS ได้ด้วยตัวเอง',
                        'link' => 'domains'
                    ),
                    array(
                        'icon' => 'fa-solid fa-server',
                        'title' => 'Web Hosting',
                        'description' => 'Hosting คุณภาพสูง NVMe SSD รองรับ WordPress และ Laravel ติดตั้ง SSL ฟรี',
                        'link' => 'hosting'
                    ),
                    array(
                        'icon' => 'fa-solid fa-cloud',
                        'title' => 'Cloud VPS',
                        'description' => 'Private Cloud สำหรับงานที่ต้องการทรัพยากรสูง ปรับแต่งได้ตามใจ (Root Access)',
                        'link' => 'proxmox'
                    )
                )
            ),

            // 4. DEVELOPMENT SECTION (Content Block with Code/HTML)
            array(
                '_type' => 'content_block',
                'layout_align' => 'left',
                'media_type' => 'code',
                'image' => '',
                'content' => '<span class="text-n8n font-bold tracking-wider uppercase text-sm">Software & Automation</span>
                <h2 class="text-3xl md:text-4xl font-heading font-bold text-slate-900 mt-2 mb-6">รับเขียนระบบ และทำ Automation</h2>
                <p class="text-slate-600 mb-8 leading-relaxed">เราเชี่ยวชาญการพัฒนา Web Application ด้วย Laravel และสร้างระบบ Workflow อัตโนมัติด้วย n8n เพื่อช่วยลดต้นทุนและเวลาในการทำงานของธุรกิจคุณ</p>
                <div class="space-y-4">
                    <a href="n8n-automation" class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200 hover:border-n8n hover:shadow-md transition group">
                        <div class="w-12 h-12 bg-n8n/10 text-n8n rounded-full flex items-center justify-center text-xl group-hover:bg-n8n group-hover:text-white transition"><i class="fa-solid fa-share-nodes"></i></div>
                        <div><h4 class="font-bold text-slate-800">n8n Automation Service</h4><p class="text-xs text-slate-500">เชื่อมต่อ API, Line Notify, Auto Report</p></div>
                        <div class="ml-auto text-slate-300 group-hover:text-n8n"><i class="fa-solid fa-chevron-right"></i></div>
                    </a>
                    <a href="web-dev" class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200 hover:border-red-500 hover:shadow-md transition group">
                        <div class="w-12 h-12 bg-red-100 text-red-500 rounded-full flex items-center justify-center text-xl group-hover:bg-red-500 group-hover:text-white transition"><i class="fa-brands fa-laravel"></i></div>
                        <div><h4 class="font-bold text-slate-800">Custom Web Application</h4><p class="text-xs text-slate-500">ระบบ ERP, CRM, E-commerce ตามสั่ง</p></div>
                        <div class="ml-auto text-slate-300 group-hover:text-red-500"><i class="fa-solid fa-chevron-right"></i></div>
                    </a>
                </div>',
                'code_content' => '<div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-tr from-brand to-blue-500 rounded-3xl transform rotate-3 opacity-20">
                    </div>
                    <div class="bg-white rounded-3xl p-8 shadow-xl relative z-10 border border-slate-100">
                        <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-xs text-slate-400 ml-auto">automation-workflow.json</span>
                        </div>
                        <!-- Mockup Code/Flow -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="bg-yellow-100 text-yellow-600 p-3 rounded-lg"><i class="fa-solid fa-bolt"></i></div>
                                <div class="h-1 flex-grow bg-slate-100 rounded relative overflow-hidden">
                                    <div class="absolute inset-0 bg-slate-300 w-1/2 animate-pulse"></div>
                                </div>
                                <div class="bg-green-100 text-green-600 p-3 rounded-lg"><i class="fa-brands fa-line"></i></div>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-lg text-xs font-mono text-slate-600">
                                <span class="text-blue-600">if</span> (order.status === <span class="text-green-600">\'paid\'</span>) {<br>
                                &nbsp;&nbsp;sendLineNotify(<span class="text-orange-500">"New Order: "</span> +
                                order.id);<br>
                                }
                            </div>
                            <div class="text-center mt-4">
                                <a href="n8n-automation" class="inline-block px-6 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 transition">ดูรายละเอียดบริการ n8n</a>
                            </div>
                        </div>
                    </div>
                </div>'
            ),

            // 5. TESTIMONIALS (Simulated with Service Grid for now, or Custom HTML block if prefer exact match. Using Service Grid as closest semantic match available or just Content Block)
            // Let's use Content Block for pure HTML to match the mockup perfectly
            array(
                '_type' => 'content_block',
                'layout_align' => 'center',
                'media_type' => 'code', // Dummy
                'content' => '<div class="text-center mb-16">
                    <h2 class="text-3xl font-heading font-bold text-slate-900 mb-4">เสียงจากลูกค้าของเรา</h2>
                    <div class="h-1 w-20 bg-brand mx-auto rounded-full"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                    <div class="bg-slate-50 p-8 rounded-2xl shadow-sm border border-slate-100 relative">
                        <div class="text-brand text-4xl absolute top-6 right-6 opacity-20"><i class="fa-solid fa-quote-right"></i></div>
                        <p class="text-slate-600 mb-6 italic">"ใช้บริการ Cloud VPS ที่นี่มา 2 ปีแล้วครับ Uptime ดีมาก แทบไม่เคยล่ม ทีมงานซัพพอร์ตก็ตอบไว"</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center font-bold text-slate-500">S</div>
                            <div><div class="font-bold text-slate-900">คุณสมชาย</div><div class="text-xs text-slate-500">CTO, Tech Startup</div></div>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-8 rounded-2xl shadow-sm border border-slate-100 relative">
                        <div class="text-brand text-4xl absolute top-6 right-6 opacity-20"><i class="fa-solid fa-quote-right"></i></div>
                        <p class="text-slate-600 mb-6 italic">"จ้างทำระบบ Automation ด้วย n8n ช่วยลดเวลางานเอกสารไปได้เยอะมาก คุ้มค่าการลงทุนสุดๆ ค่ะ"</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center font-bold text-slate-500">P</div>
                            <div><div class="font-bold text-slate-900">คุณพิม</div><div class="text-xs text-slate-500">Manager, Marketing Agency</div></div>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-8 rounded-2xl shadow-sm border border-slate-100 relative">
                        <div class="text-brand text-4xl absolute top-6 right-6 opacity-20"><i class="fa-solid fa-quote-right"></i></div>
                        <p class="text-slate-600 mb-6 italic">"ระบบ Reseller ใช้งานง่ายมากครับ ผมสามารถสร้างแพ็กเกจขายต่อให้ลูกค้าได้ทันที"</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center font-bold text-slate-500">K</div>
                            <div><div class="font-bold text-slate-900">คุณกานต์</div><div class="text-xs text-slate-500">Freelance Web Developer</div></div>
                        </div>
                    </div>
                </div>'
            ),

            // 6. FAQ (Content Block HTML)
            array(
                '_type' => 'content_block',
                'layout_align' => 'center',
                'media_type' => 'code',
                'content' => '<div class="max-w-3xl mx-auto text-left">
                    <div class="text-center mb-12"><h2 class="text-3xl font-heading font-bold text-slate-900 mb-4">คำถามที่พบบ่อย (FAQ)</h2></div>
                    <div class="space-y-4">
                        <details class="group bg-white p-6 rounded-xl cursor-pointer border border-slate-100"><summary class="flex justify-between items-center font-medium cursor-pointer list-none text-lg text-slate-800"><span>บริการย้ายเว็บไซต์ (Migration) ฟรีไหม?</span><span class="transition group-open:rotate-180"><i class="fa-solid fa-chevron-down text-brand"></i></span></summary><div class="text-slate-600 mt-4 leading-relaxed group-open:animate-fadeIn">เรามีบริการย้ายข้อมูลเว็บไซต์จากโฮสติ้งเดิมมาให้ฟรี สำหรับลูกค้าที่สมัครใช้บริการ Hosting รายปีขึ้นไปครับ</div></details>
                        <details class="group bg-white p-6 rounded-xl cursor-pointer border border-slate-100"><summary class="flex justify-between items-center font-medium cursor-pointer list-none text-lg text-slate-800"><span>เซิร์ฟเวอร์ตั้งอยู่ที่ไหน?</span><span class="transition group-open:rotate-180"><i class="fa-solid fa-chevron-down text-brand"></i></span></summary><div class="text-slate-600 mt-4 leading-relaxed group-open:animate-fadeIn">เซิร์ฟเวอร์หลักของเราตั้งอยู่ที่ Data Center มาตรฐาน Tier 3 ในกรุงเทพฯ (CAT Tower) ครับ</div></details>
                        <details class="group bg-white p-6 rounded-xl cursor-pointer border border-slate-100"><summary class="flex justify-between items-center font-medium cursor-pointer list-none text-lg text-slate-800"><span>ออกใบกำกับภาษีได้ไหม?</span><span class="transition group-open:rotate-180"><i class="fa-solid fa-chevron-down text-brand"></i></span></summary><div class="text-slate-600 mt-4 leading-relaxed group-open:animate-fadeIn">ได้ครับ สามารถขอใบกำกับภาษีเต็มรูปแบบได้ในขั้นตอนการสั่งซื้อเลยครับ</div></details>
                    </div>
                </div>'
            ),

            // 7. CTA
            array(
                '_type' => 'cta_module',
                'title' => 'พร้อมยกระดับธุรกิจของคุณหรือยัง?',
                'text' => "ไม่ว่าจะเป็นการจดโดเมน เช่าโฮสติ้ง หรือพัฒนาระบบซอฟต์แวร์\nทีมงานต้นกล้า ไอที พร้อมให้คำปรึกษาและดูแลคุณตลอด 24 ชม.",
                'bg_type' => 'dark',
                'buttons' => array(
                    array('text' => 'ติดต่อผ่าน LINE', 'link' => 'https://line.me/ti/p/@tonklait', 'style' => 'primary'),
                    array('text' => 'โทรปรึกษาฝ่ายขาย', 'link' => 'tel:021234567', 'style' => 'dark')
                )
            )
        );
        $home_id = tonkla_create_demo_page('Home', 'home', $home_modules);
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
    }


    /* -------------------------------------------------------------------------
                                 2. HOSTING
       ------------------------------------------------------------------------- */
    if (in_array('hosting', $selected_pages)) {
        $hosting_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'service',
                'badge' => 'Infrastructure Solutions',
                'headline' => 'โฮสติ้งและเซิร์ฟเวอร์ ประสิทธิภาพสูง เพื่อธุรกิจคุณ',
                'description' => 'รองรับทุกการเติบโตด้วย Web Hosting, VPS และ Dedicated Server บนโครงสร้างพื้นฐานระดับ Enterprise ที่มีความเสถียรและปลอดภัยสูงสุด',
                'buttons' => array(
                    array('text' => 'ดูแพ็คเกจโฮสติ้ง', 'link' => '#hosting-plans', 'style' => 'primary'),
                    array('text' => 'Cloud Server', 'link' => 'proxmox', 'style' => 'secondary')
                )
            ),
            array(
                '_type' => 'pricing_table',
                'section_title' => 'เว็บโฮสติ้งคุณภาพสูง',
                'section_description' => 'เริ่มต้นทำเว็บไซต์ด้วยโฮสติ้งที่เร็วและเสถียรที่สุด พร้อม DirectAdmin Control Panel',
                'plans' => array(
                    array(
                        'name' => 'Starter',
                        'price' => '฿590',
                        'period' => '/ปี',
                        'description' => 'เหมาะสำหรับเว็บไซต์ขนาดเล็ก หรือ Portfolio',
                        'recommended' => false,
                        'button_text' => 'สั่งซื้อเลย',
                        'button_link' => '#',
                        'features' => array(
                            array('text' => 'พื้นที่ 5 GB SSD'),
                            array('text' => 'Bandwidth Unlimited'),
                            array('text' => 'Free SSL'),
                        )
                    ),
                    array(
                        'name' => 'Business',
                        'price' => '฿1,490',
                        'period' => '/ปี',
                        'description' => 'สำหรับธุรกิจ SMEs องค์กร หรือร้านค้าออนไลน์',
                        'recommended' => true,
                        'button_text' => 'สั่งซื้อเลย',
                        'button_link' => '#',
                        'features' => array(
                            array('text' => 'พื้นที่ 20 GB NVMe SSD'),
                            array('text' => 'Bandwidth Unlimited'),
                            array('text' => 'Free Domain (.com)'),
                            array('text' => 'Daily Backup'),
                        )
                    ),
                    array(
                        'name' => 'Professional',
                        'price' => '฿3,500',
                        'period' => '/ปี',
                        'description' => 'สำหรับเว็บที่มีผู้เข้าชมสูง หรือ Web App',
                        'recommended' => false,
                        'button_text' => 'สั่งซื้อเลย',
                        'button_link' => '#',
                        'features' => array(
                            array('text' => 'พื้นที่ 50 GB NVMe SSD'),
                            array('text' => 'Unlimited Websites'),
                            array('text' => 'Priority Support'),
                        )
                    )
                )
            ),
            array(
                '_type' => 'content_block',
                'layout_align' => 'left',
                'media_type' => 'image',
                'image' => 'https://images.unsplash.com/photo-1558494949-efdeb6bf80a1?auto=format&fit=crop&q=80&w=1000',
                'content' => '<h2 class="text-3xl font-bold mb-4">Cloud VPS Server</h2><p>ยกระดับประสิทธิภาพด้วย Cloud VPS ที่คุณจัดการได้เองทั้งหมด (Root Access) พร้อมฮาร์ดแวร์ระดับ Enterprise และ Network 10 Gbps</p><ul class="list-disc pl-5"><li>High Performance CPU</li><li>NVMe SSD Storage</li><li>99.9% Uptime Guarantee</li></ul>'
            )
        );
        tonkla_create_demo_page('Web Hosting', 'hosting', $hosting_modules);
    }


    /* -------------------------------------------------------------------------
                                 3. n8n AUTOMATION
       ------------------------------------------------------------------------- */
    if (in_array('n8n', $selected_pages)) {
        $n8n_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'service_alt',
                'badge' => 'Automate Everything',
                'headline' => 'หยุดทำงานซ้ำซาก ให้ n8n Workflow ทำแทนคุณ',
                'description' => 'บริการออกแบบและติดตั้งระบบ Automation เชื่อมต่อ API ระหว่างแอปพลิเคชัน (Line, Google Sheets, CRM) ช่วยลดเวลาทำงาน ลดความผิดพลาด และเพิ่มประสิทธิภาพให้ธุรกิจ',
                'buttons' => array(
                    array('text' => 'ประเมินราคาฟรี', 'link' => 'contact', 'style' => 'primary')
                )
            ),
            array(
                '_type' => 'workflow_module',
                'headline' => 'ตัวอย่างการทำงานของระบบ',
                'nodes' => array(
                    array(
                        'icon' => 'fa-brands fa-facebook-f',
                        'title' => 'New Lead',
                        'description' => 'ลูกค้าทักแชท / กรอกฟอร์ม',
                        'color_class' => 'border-yellow-400'
                    ),
                    array(
                        'icon' => 'fa-brands fa-google-drive',
                        'title' => 'Save Data',
                        'description' => 'บันทึกลง Google Sheets',
                        'color_class' => 'border-green-500'
                    ),
                    array(
                        'icon' => 'fa-brands fa-line',
                        'title' => 'Notify Team',
                        'description' => 'แจ้งเตือนเข้ากลุ่ม LINE',
                        'color_class' => 'border-green-600'
                    ),
                    array(
                        'icon' => 'fa-solid fa-envelope',
                        'title' => 'Send Email',
                        'description' => 'ส่งใบเสนอราคาอัตโนมัติ',
                        'color_class' => 'border-blue-500'
                    )
                )
            ),
            array(
                '_type' => 'pricing_table',
                'section_title' => 'แพ็กเกจบริการ',
                'section_description' => 'เลือกรูปแบบบริการที่ตอบโจทย์ธุรกิจของคุณที่สุด',
                'plans' => array(
                    array(
                        'name' => 'n8n Cloud',
                        'price' => '990',
                        'period' => 'บาท/เดือน',
                        'description' => 'เช่าใช้ n8n บน Server ประสิทธิภาพสูงของเรา',
                        'recommended' => false,
                        'button_text' => 'สนใจ',
                        'button_link' => '#',
                        'features' => array(
                            array('text' => 'Ready to Use'),
                            array('text' => 'Daily Backup'),
                        )
                    ),
                    array(
                        'name' => 'Workflow Service',
                        'price' => '5,000',
                        'period' => 'บาท/Flow',
                        'description' => 'บริการรับทำ Workflow เชื่อมต่อระบบตามโจทย์',
                        'recommended' => true,
                        'button_text' => 'ประเมินราคาฟรี',
                        'button_link' => '#',
                        'features' => array(
                            array('text' => 'วิเคราะห์ Business Logic'),
                            array('text' => 'เชื่อมต่อ API / Webhook'),
                            array('text' => 'รับประกัน 3 เดือน'),
                        )
                    ),
                    array(
                        'name' => 'Installation',
                        'price' => '3,500',
                        'period' => 'บาท/ครั้ง',
                        'description' => 'รับติดตั้ง n8n บน Server ของคุณเอง',
                        'recommended' => false,
                        'button_text' => 'สั่งซื้อ',
                        'button_link' => '#',
                        'features' => array(
                            array('text' => 'ติดตั้งบน VPS ลูกค้า'),
                            array('text' => 'Setup Domain & SSL'),
                        )
                    )
                )
            ),
            array(
                '_type' => 'cta_module',
                'title' => 'เริ่มต้นทำ Automation วันนี้',
                'text' => 'ปรึกษาเราเพื่อประเมินความเป็นไปได้และราคา ฟรี! ไม่มีค่าใช้จ่าย',
                'bg_type' => 'dark',
                'buttons' => array(
                    array('text' => 'ส่งข้อมูลปรึกษาฟรี', 'link' => 'contact', 'style' => 'primary')
                )
            )
        );
        tonkla_create_demo_page('n8n Automation', 'n8n-automation', $n8n_modules);
    }


    /* -------------------------------------------------------------------------
                                 4. DOMAINS
       ------------------------------------------------------------------------- */
    if (in_array('domains', $selected_pages)) {
        $domain_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'home',
                'badge' => 'จดโดเมนวันนี้ เริ่มต้นเพียง 450 บาท',
                'headline' => 'ค้นหาชื่อที่ใช่ สำหรับธุรกิจของคุณ',
                'description' => 'ตรวจสอบสถานะโดเมนว่างได้ทันที รองรับนามสกุลยอดนิยมมากมาย พร้อมระบบจัดการ DNS เต็มรูปแบบ',
                'buttons' => array()
            ),
            array(
                '_type' => 'content_block',
                'layout_align' => 'center',
                'media_type' => 'code', // Using code to render table
                'image' => '',
                'content' => '<h3 class="text-xl font-bold mb-4">ตารางราคาโดเมน</h3>
                <div class="overflow-x-auto"><table class="w-full text-left border-collapse max-w-2xl mx-auto">
                    <thead><tr class="border-b-2 border-slate-100"><th class="py-2">TLD</th><th class="py-2">Price/Year</th></tr></thead>
                    <tbody>
                        <tr><td class="py-2 font-bold">.com</td><td class="py-2">฿450</td></tr>
                        <tr><td class="py-2 font-bold">.net</td><td class="py-2">฿550</td></tr>
                        <tr><td class="py-2 font-bold">.co.th</td><td class="py-2">฿800</td></tr>
                        <tr><td class="py-2 font-bold">.online</td><td class="py-2">฿150</td></tr>
                    </tbody>
                </table></div>'
            )
        );
        tonkla_create_demo_page('Domains', 'domains', $domain_modules);
    }


    /* -------------------------------------------------------------------------
                                 5. CONTACT
       ------------------------------------------------------------------------- */
    if (in_array('contact', $selected_pages)) {
        $contact_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'home',
                'badge' => 'GET IN TOUCH',
                'headline' => 'ติดต่อเรา',
                'description' => 'พร้อมให้คำปรึกษาและบริการด้าน IT Solutions ครบวงจร ทักหาเราได้ตลอด 24 ชั่วโมง เราพร้อมดูแลคุณ',
                'buttons' => array()
            ),
            array(
                '_type' => 'content_block',
                'layout_align' => 'center',
                'media_type' => 'code',
                'content' => '
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden flex flex-col lg:flex-row text-left">
                        <!-- Contact Form -->
                        <div class="lg:w-3/5 p-8 md:p-12">
                            <h2 class="text-3xl font-heading font-bold text-slate-900 mb-6">ส่งข้อความถึงเรา</h2>
                            <form onsubmit="event.preventDefault(); alert(\'ขอบคุณสำหรับข้อความ เราจะติดต่อกลับโดยเร็วที่สุด\');" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div><label class="block text-sm font-bold text-slate-700 mb-2">ชื่อ - นามสกุล *</label><input type="text" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-brand focus:ring-1 focus:ring-brand outline-none bg-slate-50"></div>
                                    <div><label class="block text-sm font-bold text-slate-700 mb-2">เบอร์โทรศัพท์ *</label><input type="tel" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-brand focus:ring-1 focus:ring-brand outline-none bg-slate-50"></div>
                                </div>
                                <div><label class="block text-sm font-bold text-slate-700 mb-2">รายละเอียด *</label><textarea rows="4" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-brand outline-none bg-slate-50"></textarea></div>
                                <button type="submit" class="px-8 py-4 bg-brand text-white font-bold rounded-xl hover:bg-brand-dark transition">ส่งข้อความ</button>
                            </form>
                        </div>
                        <!-- Info -->
                        <div class="lg:w-2/5 bg-slate-900 p-8 md:p-12 text-white relative overflow-hidden">
                            <h3 class="text-2xl font-bold mb-8">ข้อมูลการติดต่อ</h3>
                            <ul class="space-y-6">
                                <li class="flex gap-4"><i class="fa-solid fa-location-dot mt-1 text-brand"></i> <div><h4 class="font-bold">ที่อยู่บริษัท</h4><p class="text-sm text-slate-300">123/45 อาคารต้นกล้าทาวเวอร์ ชั้น 15<br>จตุจักร กรุงเทพฯ 10900</p></div></li>
                                <li class="flex gap-4"><i class="fa-solid fa-phone mt-1 text-brand"></i> <div><h4 class="font-bold">เบอร์โทรศัพท์</h4><p class="text-sm text-slate-300">02-123-4567</p></div></li>
                                <li class="flex gap-4"><i class="fa-brands fa-line mt-1 text-brand"></i> <div><h4 class="font-bold">LINE Official</h4><p class="text-sm text-slate-300">@tonklait</p></div></li>
                            </ul>
                        </div>
                    </div>
                </div>'
            ),
            array(
                '_type' => 'content_block',
                'layout_align' => 'center',
                'content' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3874.529881643961!2d100.55998187595305!3d13.807185297159784!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!5e0!3m2!1sth!2sth!4v1706070000000!5m2!1sth!2sth" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy" class="rounded-xl shadow-sm"></iframe>'
            )
        );
        tonkla_create_demo_page('Contact Us', 'contact', $contact_modules);
    }


    /* -------------------------------------------------------------------------
                                 6. ABOUT
       ------------------------------------------------------------------------- */
    if (in_array('about', $selected_pages)) {
        $about_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'home',
                'badge' => 'WHO WE ARE',
                'headline' => 'เบื้องหลังความสำเร็จ คือทีมงานที่ใส่ใจ',
                'description' => 'Tonkla IT ก่อตั้งขึ้นด้วยความตั้งใจที่จะยกระดับมาตรฐาน IT Infrastructure ของไทย เราไม่ใช่แค่ผู้ให้บริการ Server แต่เราคือ Partner ที่พร้อมเติบโตไปกับธุรกิจของคุณ',
                'buttons' => array()
            ),
            array(
                '_type' => 'content_block',
                'layout_align' => 'left',
                'media_type' => 'image',
                'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
                'content' => '<h2 class="text-3xl font-bold mb-4">เรื่องราวของเรา</h2><p class="mb-4">Tonkla IT เริ่มต้นจากกลุ่มวิศวกรคอมพิวเตอร์ตัวเล็กๆ ในปี 2015 ที่มีความหลงใหลในเทคโนโลยี Opensource และ Linux Server</p><p>ปัจจุบัน เราให้บริการครอบคลุมตั้งแต่ Domain Name, Cloud Hosting, Dedicated Server ไปจนถึงการวางระบบ Security และทำ Web Application ครบวงจร โดยยึดมั่นในหลักการ <strong>"จริงใจ ไม่ทิ้งงาน"</strong> เสมอมา</p>'
            ),
            array(
                '_type' => 'service_grid',
                'section_title' => 'ค่านิยมองค์กร (Core Values)',
                'section_subtitle' => 'สิ่งที่ยึดเหนี่ยวให้เราทำงานอย่างมีคุณภาพ',
                'layout' => 'cols-3',
                'items' => array(
                    array(
                        'icon' => 'fa-regular fa-handshake',
                        'title' => 'Integrity',
                        'description' => 'ความซื่อสัตย์จริงใจ เราให้ข้อมูลตรงไปตรงมา ไม่หมกเม็ด และแนะนำสิ่งที่คุ้มค่าที่สุดให้ลูกค้า',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-lightbulb',
                        'title' => 'Innovation',
                        'description' => 'ไม่หยุดเรียนรู้ เราหมั่นอัปเดตเทคโนโลยีใหม่ๆ เพื่อนำมาประยุกต์ใช้แก้ปัญหาให้ลูกค้าได้ดียิ่งขึ้น',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-regular fa-heart',
                        'title' => 'Service Mind',
                        'description' => 'บริการด้วยใจ ทีม Support ของเราพร้อมช่วยเหลือเหมือนเพื่อนคู่คิด ไม่ใช่แค่คู่ค้า',
                        'link' => '#'
                    )
                )
            ),
            array(
                '_type' => 'cta_module',
                'title' => 'ร่วมงานกับเรา',
                'text' => 'เรากำลังมองหาคนรุ่นใหม่ไฟแรง ที่พร้อมจะเติบโตไปพร้อมกับเรา หากคุณเป็นคนที่หลงใหลในเทคโนโลยี ส่ง Resume มาคุยกันได้เลย',
                'bg_type' => 'dark',
                'buttons' => array(
                    array('text' => 'ส่งประวัติการทำงาน', 'link' => 'contact', 'style' => 'primary')
                )
            )
        );
        tonkla_create_demo_page('About Us', 'about', $about_modules);
    }


    /* -------------------------------------------------------------------------
                                 7. PENTEST
       ------------------------------------------------------------------------- */
    if (in_array('pentest', $selected_pages)) {
        $pentest_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'service',
                'badge' => 'Cybersecurity Solutions',
                'headline' => 'ค้นหาช่องโหว่ ก่อนแฮกเกอร์จะเจอ',
                'description' => 'บริการ Penetration Testing (Pentest) และแก้ไขช่องโหว่ (Fix) สำหรับเว็บไซต์และเซิร์ฟเวอร์ โดยทีมงานผู้เชี่ยวชาญ พร้อมออกรายงานสรุปผลตามมาตรฐานสากล OWASP',
                'buttons' => array(
                    array('text' => 'ขอใบเสนอราคา', 'link' => 'contact', 'style' => 'primary')
                )
            ),
            array(
                '_type' => 'service_grid',
                'section_title' => 'บริการของเรา',
                'layout' => 'cols-3',
                'items' => array(
                    array(
                        'icon' => 'fa-solid fa-magnifying-glass-chart',
                        'title' => 'Vulnerability Assessment',
                        'description' => 'การตรวจหาช่องโหว่ด้วยเครื่องมืออัตโนมัติ (Automated Scan) เพื่อค้นหาจุดอ่อนเบื้องต้นอย่างรวดเร็ว',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-user-secret',
                        'title' => 'Penetration Testing',
                        'description' => 'การจำลองการโจมตีเสมือนจริง (Manual Test) โดยผู้เชี่ยวชาญ เจาะลึก Logic ของระบบ',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-hammer',
                        'title' => 'Fix & Hardening',
                        'description' => 'บริการปิดช่องโหว่ แก้ไขโค้ด (Patching) และปรับแต่งความปลอดภัยของ Server ให้แข็งแกร่ง',
                        'link' => '#'
                    )
                )
            ),
            array(
                '_type' => 'pricing_table',
                'section_title' => 'แพ็กเกจค่าบริการ',
                'plans' => array(
                    array(
                        'name' => 'Basic Scan',
                        'price' => '3,900',
                        'period' => 'บาท',
                        'description' => 'สำหรับเว็บไซต์ทั่วไป ตรวจเช็คความปลอดภัยเบื้องต้น',
                        'features' => array(
                            array('text' => 'Automated Scan'),
                            array('text' => 'Basic Report'),
                            array('text' => 'OWASP Top 10 Check')
                        )
                    ),
                    array(
                        'name' => 'Standard Pentest',
                        'price' => '12,000',
                        'period' => 'บาท',
                        'description' => 'สำหรับ E-commerce หรือเว็บที่มีข้อมูลลูกค้า',
                        'recommended' => true,
                        'features' => array(
                            array('text' => 'Manual Penetration Testing'),
                            array('text' => 'Business Logic Testing'),
                            array('text' => 'Detailed Report & PoC'),
                            array('text' => 'Free 1 Re-test')
                        )
                    )
                )
            )
        );
        tonkla_create_demo_page('Pentest Service', 'pentest', $pentest_modules);
    }


    /* -------------------------------------------------------------------------
                                 8. PROXMOX
       ------------------------------------------------------------------------- */
    if (in_array('proxmox', $selected_pages)) {
        $proxmox_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'service_alt',
                'badge' => 'Enterprise Virtualization',
                'headline' => 'บริหารจัดการเซิร์ฟเวอร์ด้วย Proxmox VE',
                'description' => 'เปลี่ยน Server ธรรมดาให้กลายเป็น Cloud Private คุณภาพสูง ด้วย Proxmox Virtual Environment รองรับทั้ง VMs และ LXC Containers พร้อมระบบ Backup และ Cluster',
                'buttons' => array(
                    array('text' => 'ปรึกษาผู้เชี่ยวชาญ', 'link' => 'contact', 'style' => 'primary')
                )
            ),
            array(
                '_type' => 'service_grid',
                'section_title' => 'ทำไมต้อง Proxmox VE?',
                'layout' => 'cols-3',
                'items' => array(
                    array(
                        'icon' => 'fa-solid fa-layer-group',
                        'title' => 'VM & Container',
                        'description' => 'รองรับทั้ง KVM สำหรับ Windows/Linux และ LXC ที่เบาและรวดเร็ว',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-network-wired',
                        'title' => 'Cluster Management',
                        'description' => 'จัดการเซิร์ฟเวอร์หลายเครื่องได้จากหน้าเว็บเดียว ด้วยระบบ Cluster ที่เสถียร',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-floppy-disk',
                        'title' => 'Backup & Restore',
                        'description' => 'ระบบสำรองข้อมูลในตัว รองรับทั้ง Snapshot และ Full Backup',
                        'link' => '#'
                    )
                )
            ),
            array(
                '_type' => 'content_block',
                'layout_align' => 'center',
                'media_type' => 'code',
                'content' => '<h2 class="text-3xl font-bold mb-8">Packages</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                    <div class="bg-white p-6 rounded-xl border border-slate-200">
                        <h4 class="font-bold text-lg">Basic Setup</h4>
                        <p class="text-slate-500 text-sm mb-4">ติดตั้ง OS, Network, ZFS Storage</p>
                        <div class="text-2xl font-bold text-brand">฿2,500</div>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-md">
                        <h4 class="font-bold text-lg">Cluster 3 Nodes</h4>
                        <p class="text-slate-500 text-sm mb-4">ติดตั้ง Cluster + HA + Ceph Storage</p>
                        <div class="text-2xl font-bold text-brand">฿7,500</div>
                    </div>
                    <div class="bg-white p-6 rounded-xl border border-slate-200">
                        <h4 class="font-bold text-lg">Monthly Support</h4>
                        <p class="text-slate-500 text-sm mb-4">ดูแลระบบ, อัปเดตแพทช์, ตรวจสอบ Log</p>
                        <div class="text-2xl font-bold text-brand">฿1,500 <span class="text-sm text-slate-400">/เดือน</span></div>
                    </div>
                </div>'
            )
        );
        tonkla_create_demo_page('Proxmox Solutions', 'proxmox', $proxmox_modules);
    }


    /* -------------------------------------------------------------------------
                                 9. WEB DEV
       ------------------------------------------------------------------------- */
    if (in_array('web_dev', $selected_pages)) {
        $webdev_modules = array(
            // 1. Hero Section
            array(
                '_type' => 'hero_module',
                'hero_type' => 'service_alt',
                'bg_type' => 'pattern',
                'badge' => 'Professional Web Development',
                'headline' => 'เปลี่ยนไอเดียธุรกิจ<br><strong>ด้วยซอฟต์แวร์คุณภาพสูง</strong>',
                'description' => 'รับพัฒนา Web Application และระบบบริหารจัดการองค์กร (ERP/CRM) ด้วยเทคโนโลยีที่ทันสมัย รองรับการขยายตัว (Scalable) และมีความปลอดภัยสูง ออกแบบให้ตอบโจทย์การใช้งานจริงของคุณ',
                'buttons' => array(
                    array('text' => 'ปรึกษาโปรเจกต์ฟรี', 'icon' => 'fa-regular fa-comment-dots', 'link' => 'contact', 'style' => 'primary'),
                    array('text' => 'ดู Tech Stack', 'link' => '#our-stack', 'style' => 'secondary')
                )
            ),

            // 2. Services Section
            array(
                '_type' => 'service_grid',
                'section_title' => 'บริการของเรา',
                'section_subtitle' => 'เราเชี่ยวชาญด้านการพัฒนา Web Technologies ครอบคลุมทุกความต้องการ',
                'layout' => 'cols-4',
                'items' => array(
                    // Service 1: Custom Web Application
                    array(
                        'icon' => 'fa-solid fa-code',
                        'icon_color' => 'brand',
                        'title' => 'Custom Web Application',
                        'description' => 'พัฒนาระบบซอฟต์แวร์ตามความต้องการ (Tailor-made) เช่น ระบบ ERP, CRM, HRM หรือระบบ Workflow ภายในองค์กร ที่โปรแกรมสำเร็จรูปไม่ตอบโจทย์',
                        'link_type' => 'custom',
                        'link_url' => '#',
                        'stack_icons' => array(
                            array('icon_class' => 'fa-brands fa-laravel', 'icon_color' => 'red-500', 'title' => 'Laravel'),
                            array('icon_class' => 'fa-brands fa-node-js', 'icon_color' => 'green-500', 'title' => 'Node.js'),
                            array('icon_class' => 'fa-brands fa-vuejs', 'icon_color' => 'green-400', 'title' => 'Vue.js')
                        )
                    ),

                    // Service 2: API Development
                    array(
                        'icon' => 'fa-solid fa-network-wired',
                        'icon_color' => 'blue',
                        'title' => 'API Development',
                        'description' => 'บริการออกแบบและพัฒนา RESTful API หรือ GraphQL เพื่อเชื่อมต่อข้อมูลระหว่างระบบ (System Integration) หรือเป็น Backend ให้กับ Mobile App',
                        'link_type' => 'custom',
                        'link_url' => '#',
                        'stack_icons' => array(
                            array('icon_class' => 'fa-solid fa-database', 'icon_color' => 'blue-500', 'title' => 'SQL'),
                            array('icon_class' => 'fa-brands fa-python', 'icon_color' => 'yellow-500', 'title' => 'Python'),
                            array('icon_class' => 'fa-brands fa-docker', 'icon_color' => 'blue-400', 'title' => 'Docker')
                        )
                    ),

                    // Service 3: Modern Corporate Website
                    array(
                        'icon' => 'fa-solid fa-laptop-code',
                        'icon_color' => 'indigo',
                        'title' => 'Modern Corporate Website',
                        'description' => 'เว็บไซต์องค์กรภาพลักษณ์สูง เน้นดีไซน์ที่สวยงาม ทันสมัย รองรับ SEO และการแสดงผลบนทุกอุปกรณ์ พร้อมระบบจัดการเนื้อหา (CMS)',
                        'link_type' => 'custom',
                        'link_url' => '#',
                        'stack_icons' => array(
                            array('icon_class' => 'fa-brands fa-wordpress', 'icon_color' => 'blue-700', 'title' => 'WordPress'),
                            array('icon_class' => 'fa-brands fa-elementor', 'icon_color' => 'pink-600', 'title' => 'Elementor'),
                            array('icon_class' => 'fa-brands fa-html5', 'icon_color' => 'orange-500', 'title' => 'HTML5')
                        )
                    ),

                    // Service 4: WordPress Development
                    array(
                        'icon' => 'fa-brands fa-wordpress',
                        'icon_color' => 'indigo',
                        'title' => 'WordPress Development',
                        'description' => 'บริการรับทำเว็บไซต์ด้วย WordPress แบบมืออาชีพ พัฒนา Theme และ Plugins เฉพาะทาง เน้นความเร็ว ความปลอดภัย และปรับแต่งได้ตามต้องการ',
                        'link_type' => 'custom',
                        'link_url' => '#',
                        'stack_icons' => array(
                            array('icon_class' => 'fa-brands fa-wordpress', 'icon_color' => 'blue-600', 'title' => 'WordPress Theme'),
                            array('icon_class' => 'fa-solid fa-plug', 'icon_color' => 'indigo-500', 'title' => 'WordPress Plugins'),
                            array('icon_class' => 'fa-brands fa-php', 'icon_color' => 'indigo-400', 'title' => 'PHP')
                        )
                    ),


                )
            ),

            // 3. Tech Stack Section
            array(
                '_type' => 'content_block',
                'layout_align' => 'center',
                'media_type' => 'code',
                'content' => '<section id="our-stack" class="py-20 bg-slate-900 text-white relative overflow-hidden">
                    <div class="absolute inset-0 bg-brand/5 skew-y-3 transform origin-bottom-left scale-150"></div>
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                        <div class="lg:flex gap-16 items-center">
                            <div class="lg:w-1/3 mb-10 lg:mb-0">
                                <h2 class="text-3xl font-heading font-bold mb-6">Tools & Technology <br><span class="text-brand">We Master</span></h2>
                                <p class="text-slate-300 mb-8">เราเลือกใช้เครื่องมือและเฟรมเวิร์กที่เป็นมาตรฐานสากล มีความเสถียร มีชุมชนนักพัฒนาขนาดใหญ่ เพื่อให้มั่นใจว่าซอฟต์แวร์ของคุณจะดูแลรักษาได้ง่ายในระยะยาว</p>
                                <a href="works" class="inline-flex items-center text-brand font-bold hover:text-brand-light transition">ดูตัวอย่างผลงาน <i class="fa-solid fa-arrow-right ml-2"></i></a>
                            </div>
                            <div class="lg:w-2/3">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                                    <div class="bg-slate-800 p-6 rounded-xl text-center hover:bg-slate-700 transition border border-slate-700">
                                        <i class="fa-brands fa-laravel text-5xl text-red-500 mb-4"></i>
                                        <div class="font-bold">Laravel</div>
                                        <div class="text-xs text-slate-400">Backend Framework</div>
                                    </div>
                                    <div class="bg-slate-800 p-6 rounded-xl text-center hover:bg-slate-700 transition border border-slate-700">
                                        <i class="fa-brands fa-vuejs text-5xl text-green-400 mb-4"></i>
                                        <div class="font-bold">Vue.js</div>
                                        <div class="text-xs text-slate-400">Frontend Framework</div>
                                    </div>
                                    <div class="bg-slate-800 p-6 rounded-xl text-center hover:bg-slate-700 transition border border-slate-700">
                                        <i class="fa-brands fa-react text-5xl text-blue-400 mb-4"></i>
                                        <div class="font-bold">React</div>
                                        <div class="text-xs text-slate-400">Frontend Library</div>
                                    </div>
                                    <div class="bg-slate-800 p-6 rounded-xl text-center hover:bg-slate-700 transition border border-slate-700">
                                        <i class="fa-brands fa-node-js text-5xl text-green-500 mb-4"></i>
                                        <div class="font-bold">Node.js</div>
                                        <div class="text-xs text-slate-400">Runtime</div>
                                    </div>
                                    <div class="bg-slate-800 p-6 rounded-xl text-center hover:bg-slate-700 transition border border-slate-700">
                                        <i class="fa-solid fa-database text-5xl text-yellow-500 mb-4"></i>
                                        <div class="font-bold">SQL / NoSQL</div>
                                        <div class="text-xs text-slate-400">Database</div>
                                    </div>
                                    <div class="bg-slate-800 p-6 rounded-xl text-center hover:bg-slate-700 transition border border-slate-700">
                                        <i class="fa-brands fa-docker text-5xl text-blue-500 mb-4"></i>
                                        <div class="font-bold">Docker</div>
                                        <div class="text-xs text-slate-400">Containerization</div>
                                    </div>
                                    <div class="bg-slate-800 p-6 rounded-xl text-center hover:bg-slate-700 transition border border-slate-700">
                                        <i class="fa-brands fa-aws text-5xl text-orange-400 mb-4"></i>
                                        <div class="font-bold">Cloud</div>
                                        <div class="text-xs text-slate-400">Infrastructure</div>
                                    </div>
                                    <div class="bg-slate-800 p-6 rounded-xl text-center hover:bg-slate-700 transition border border-slate-700">
                                        <i class="fa-brands fa-git-alt text-5xl text-red-400 mb-4"></i>
                                        <div class="font-bold">Git</div>
                                        <div class="text-xs text-slate-400">Version Control</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>'
            ),

            // 4. Process Timeline
            array(
                '_type' => 'process_module',
                'section_title' => 'ขั้นตอนการทำงานของเรา',
                'section_subtitle' => 'กระบวนการที่เป็นระบบ เพื่อให้ได้ผลลัพธ์ที่ตรงใจและส่งมอบงานได้ตรงเวลา',
                'bg_color' => 'slate-50',
                'steps' => array(
                    array(
                        'title' => 'Requirement',
                        'description' => 'พูดคุย วิเคราะห์ความต้องการ และสรุปขอบเขตงาน (Scope of Work)'
                    ),
                    array(
                        'title' => 'Design & UX/UI',
                        'description' => 'ออกแบบโครงสร้าง (Wireframe) และหน้าตาเว็บไซต์ (Mockup)'
                    ),
                    array(
                        'title' => 'Development',
                        'description' => 'ลงมือพัฒนา Coding และเชื่อมต่อฐานข้อมูลตามที่ออกแบบไว้'
                    ),
                    array(
                        'title' => 'Test & Launch',
                        'description' => 'ทดสอบระบบ (UAT) ฝึกอบรมการใช้งาน และนำขึ้น Server จริง (Deploy)'
                    )
                )
            ),

            // 5. CTA
            array(
                '_type' => 'cta_module',
                'title' => 'พร้อมยกระดับธุรกิจของคุณหรือยัง?',
                'text' => 'อย่าปล่อยให้เทคโนโลยีเป็นเรื่องไกลตัว ปรึกษาเราเพื่อหาโซลูชันที่เหมาะสมกับงบประมาณและเป้าหมายธุรกิจของคุณ',
                'bg_type' => 'dark',
                'buttons' => array(
                    array('text' => 'ประเมินราคาโปรเจกต์', 'icon' => 'fa-regular fa-paper-plane', 'link' => 'contact', 'style' => 'primary')
                )
            )
        );
        tonkla_create_demo_page('Web Application', 'web-dev', $webdev_modules);
    }


    /* -------------------------------------------------------------------------
                                 10. WORKS
       ------------------------------------------------------------------------- */
    if (in_array('works', $selected_pages)) {
        $works_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'home',
                'badge' => 'OUR PORTFOLIO',
                'headline' => 'ผลงานและความสำเร็จ ที่เราภูมิใจ',
                'description' => 'ตัวอย่างโปรเจกต์ที่เราได้ส่งมอบให้กับลูกค้า ตั้งแต่เว็บไซต์องค์กร ระบบบริหารจัดการภายใน ไปจนถึงระบบ Automation',
                'buttons' => array()
            ),
            // Simplification: Using Service Grid as Portfolio Grid for now
            array(
                '_type' => 'service_grid',
                'section_title' => 'Selected Projects',
                'layout' => 'cols-3',
                'items' => array(
                    array(
                        'icon' => 'fa-solid fa-chart-pie',
                        'title' => 'ระบบบริหารจัดการคลังสินค้า (ERP)',
                        'description' => 'Web App สำหรับจัดการสต็อกสินค้า เชื่อมต่อหลายสาขา Real-time พร้อมออกใบกำกับภาษีเต็มรูปแบบ (Laravel + Vue.js)',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-share-nodes',
                        'title' => 'ระบบแจ้งเตือน Order (Automation)',
                        'description' => 'เชื่อมต่อ WooCommerce เข้ากับ LINE OA แจ้งเตือนแอดมินทันทีที่มีคำสั่งซื้อใหม่ (n8n)',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-building',
                        'title' => 'เว็บไซต์บริษัทรับเหมาก่อสร้าง',
                        'description' => 'เว็บไซต์องค์กรภาพลักษณ์สูง แสดงผลงานโครงการ และรองรับ SEO (WordPress)',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-regular fa-calendar-check',
                        'title' => 'ระบบจองคิวคลินิกทันตกรรม',
                        'description' => 'ระบบจองคิวออนไลน์ เชื่อมต่อ Google Calendar ของหมอ พร้อมระบบ SMS แจ้งเตือน',
                        'link' => '#'
                    )
                )
            ),
            array(
                '_type' => 'cta_module',
                'title' => 'พร้อมเริ่มโปรเจกต์ของคุณหรือยัง?',
                'text' => 'ปรึกษาเราเพื่อประเมินราคาและระยะเวลา ฟรี!',
                'bg_type' => 'dark',
                'buttons' => array(
                    array('text' => 'ขอใบเสนอราคา', 'link' => 'contact', 'style' => 'primary')
                )
            )
        );
        tonkla_create_demo_page('Our Works', 'works', $works_modules);
    }


    /* -------------------------------------------------------------------------
                                 11. SSL
       ------------------------------------------------------------------------- */
    if (in_array('ssl', $selected_pages)) {
        $ssl_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'service',
                'badge' => 'Web Security',
                'headline' => 'สร้างความน่าเชื่อถือ ด้วย SSL Certificate',
                'description' => 'ปกป้องข้อมูลลูกค้าของคุณด้วยการเข้ารหัส HTTPS เพิ่มคะแนน SEO ให้เว็บไซต์ติดอันดับ Google ได้ง่ายขึ้น พร้อมติดตั้งฟรี',
                'buttons' => array(
                    array('text' => 'ดูราคาแพ็คเกจ', 'link' => '#plans', 'style' => 'primary')
                )
            ),
            array(
                '_type' => 'pricing_table',
                'section_title' => 'ใบรับรองความปลอดภัย SSL',
                'plans' => array(
                    array(
                        'name' => 'PositiveSSL',
                        'price' => '450',
                        'period' => 'บาท/ปี',
                        'description' => 'Domain Validation (DV)',
                        'features' => array(
                            array('text' => '$10,000 Warranty'),
                            array('text' => 'Free Re-issues'),
                            array('text' => 'Site Seal Logo')
                        )
                    ),
                    array(
                        'name' => 'Sectigo Wildcard',
                        'price' => '2,500',
                        'period' => 'บาท/ปี',
                        'description' => 'Protect Subdomains (*.domain.com)',
                        'recommended' => true,
                        'features' => array(
                            array('text' => 'Unlimited Subdomains'),
                            array('text' => 'Fast Issuance')
                        )
                    ),
                    array(
                        'name' => 'EV SSL',
                        'price' => '4,500',
                        'period' => 'บาท/ปี',
                        'description' => 'Extended Validation (Green Bar)',
                        'features' => array(
                            array('text' => 'Green Address Bar'),
                            array('text' => '$1,750,000 Warranty')
                        )
                    )
                )
            )
        );
        tonkla_create_demo_page('SSL Certificates', 'ssl', $ssl_modules);
    }


    /* -------------------------------------------------------------------------
                                 12. SYSTEM IMP
       ------------------------------------------------------------------------- */
    if (in_array('system_impl', $selected_pages)) {
        $sysimpl_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'service',
                'badge' => 'IT Infrastructure Services',
                'headline' => 'ออกแบบและติดตั้งระบบ IT Infrastructure',
                'description' => 'บริการวางระบบ Server, Network และ Security สำหรับองค์กร ด้วยมาตรฐานวิศวกรรมระดับมืออาชีพ',
                'buttons' => array(
                    array('text' => 'ขอคำปรึกษาฟรี', 'link' => 'contact', 'style' => 'primary')
                )
            ),
            array(
                '_type' => 'service_grid',
                'section_title' => 'Solutions',
                'layout' => 'cols-3',
                'items' => array(
                    array(
                        'icon' => 'fa-brands fa-linux',
                        'title' => 'OS Installation',
                        'description' => 'ติดตั้งและตั้งค่า Linux (Ubuntu, CentOS) และ Windows Server พร้อม Hardening',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-diagram-project',
                        'title' => 'Network Design',
                        'description' => 'ออกแบบระบบเครือข่าย LAN/VLAN, VPN, Firewall และ Load Balancing',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-database',
                        'title' => 'Backup & DR',
                        'description' => 'วางแผนระบบสำรองข้อมูลอัตโนมัติ และแผนกู้คืนระบบ (Disaster Recovery)',
                        'link' => '#'
                    )
                )
            )
        );
        tonkla_create_demo_page('System Implementation', 'system-implementation', $sysimpl_modules);
    }


    /* -------------------------------------------------------------------------
                                 13. PRIVACY POLICY
       ------------------------------------------------------------------------- */
    if (in_array('privacy', $selected_pages)) {
        $privacy_modules = array(
            array(
                '_type' => 'hero_module',
                'hero_type' => 'home',
                'badge' => 'Legal',
                'headline' => 'นโยบายความเป็นส่วนตัว',
                'description' => 'เพื่อให้สอดคล้องกับพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 (PDPA)',
                'buttons' => array()
            ),
            array(
                '_type' => 'content_block',
                'layout_align' => 'left',
                'media_type' => 'code',
                'content' => '<div class="prose max-w-none">
                    <h3>1. ข้อมูลที่เราจัดเก็บ</h3>
                    <p>เราอาจเก็บรวบรวมข้อมูลส่วนบุคคลของท่านได้จากหลายช่องทาง เช่น เมื่อท่านสมัครสมาชิก, กรอกแบบฟอร์มขอใบเสนอราคา หรือติดต่อเราทาง LINE โดยข้อมูลรวมถึง ชื่อ-นามสกุล, ที่อยู่, อีเมล, เบอร์โทรศัพท์</p>
                    <h3>2. วัตถุประสงค์</h3>
                    <p>เพื่อให้บริการตามสัญญา, เพื่อออกใบกำกับภาษี, และเพื่อส่งข่าวสารโปรโมชั่น (หากท่านยินยอม)</p>
                    <h3>3. การเปิดเผยข้อมูล</h3>
                    <p>เราไม่ขายข้อมูลของท่านแก่บุคคลภายนอก ยกเว้นกรณีจำเป็นต้องส่งต่อให้ Registrar (สำหรับโดเมน) หรือหน่วยงานรัฐตามกฎหมาย</p>
                    <h3>4. สิทธิของท่าน</h3>
                    <p>ท่านมีสิทธิขอเข้าถึง, แก้ไข, หรือลบข้อมูลส่วนบุคคลของท่านได้ โดยติดต่อเจ้าหน้าที่ DPO ของเรา</p>
                    <hr>
                    <p><strong>ติดต่อเรา:</strong> dpo@tonklait.com | 02-123-4567</p>
                </div>'
            )
        );
        tonkla_create_demo_page('Privacy Policy', 'privacy-policy', $privacy_modules);
    }


    /* -------------------------------------------------------------------------
                                 14. SOLUTIONS (HUB)
       ------------------------------------------------------------------------- */
    if (in_array('solutions', $selected_pages)) {
        $solutions_modules = array(
            // 1. Hero
            array(
                '_type' => 'hero_module',
                'hero_type' => 'home',
                'badge' => 'Our Services',
                'headline' => 'บริการของเรา',
                'description' => 'บริการครบวงจรด้าน IT และ Web Solutions เพื่อธุรกิจของคุณ',
                'buttons' => array()
            ),

            // 2. Infrastructure & Enterprise
            array(
                '_type' => 'service_grid',
                'section_title' => 'Infrastructure & Enterprise Solutions',
                'layout' => 'cols-3',
                'items' => array(
                    array(
                        'icon' => 'fa-solid fa-cloud',
                        'icon_color' => 'cyan-500',
                        'title' => 'Cloud Server',
                        'description' => 'บริการ Cloud Server ประสิทธิภาพสูง รองรับทุกขนาดธุรกิจ พร้อม SSD NVMe และ Uptime 99.9%',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-server',
                        'icon_color' => 'amber-500',
                        'title' => 'Proxmox VE',
                        'description' => 'บริการติดตั้งและดูแล Proxmox Virtualization Platform สำหรับจัดการ Server องค์กรอย่างมืออาชีพ',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-network-wired',
                        'icon_color' => 'teal-500',
                        'title' => 'System Implementation',
                        'description' => 'วางระบบ IT Infrastructure, Network และ Storage ครบวงจร โดยทีมวิศวกรผู้เชี่ยวชาญ',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-shield-halved',
                        'icon_color' => 'red-500',
                        'title' => 'Cyber Security',
                        'description' => 'Pentest หาช่องโหว่, Vulnerability Assessment และบริการ SSL Certificate เพื่อความปลอดภัยสูงสุด',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-robot',
                        'icon_color' => 'orange-500',
                        'title' => 'n8n Automation',
                        'description' => 'ลดงานซ้ำซ้อนด้วยระบบ Workflow Automation เชื่อมต่อ API และจัดการ Data Flow อัตโนมัติ',
                        'link' => '#'
                    )
                )
            ),

            // 3. Web & CMS Solutions
            array(
                '_type' => 'service_grid',
                'section_title' => 'Web & CMS Solutions',
                'layout' => 'cols-3',
                'items' => array(
                    array(
                        'icon' => 'fa-solid fa-globe',
                        'icon_color' => 'blue-600',
                        'title' => 'จดโดเมน',
                        'description' => 'บริการจดทะเบียนโดเมน .com, .co.th, .in.th และอื่นๆ พร้อมระบบจัดการ DNS และต่ออายุอัตโนมัติ',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-code',
                        'icon_color' => 'purple-500',
                        'title' => 'Web Application',
                        'description' => 'รับพัฒนา Web Application เฉพาะทางสำหรับธุรกิจ (Tailor-made) เชื่อมต่อ API และ Database รองรับการเติบโต',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-brands fa-wordpress',
                        'icon_color' => 'indigo-500',
                        'title' => 'WordPress Theme',
                        'description' => 'บริการรับทำ Theme WordPress ดีไซน์สวย ทันสมัย รองรับ SEO และ Mobile Friendly ปรับแต่งได้ดั่งใจ',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-plug',
                        'icon_color' => 'indigo-500',
                        'title' => 'WordPress Plugin',
                        'description' => 'รับพัฒนา WordPress Plugin เพิ่มฟีเจอร์พิเศษให้เว็บไซต์ของคุณ เชื่อมต่อระบบภายนอก หรือแก้ไขส่วนเสริมเดิม',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-brands fa-joomla',
                        'icon_color' => 'orange-500',
                        'title' => 'Joomla Template',
                        'description' => 'รับออกแบบและพัฒนา Joomla Template ให้มีเอกลักษณ์ ใช้งานง่าย รองรับ Joomla เวอร์ชั่นล่าสุด',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-solid fa-puzzle-piece',
                        'icon_color' => 'amber-500',
                        'title' => 'Joomla Extensions',
                        'description' => 'รับพัฒนา Joomla Component, Module และ Plugin เพื่อเพิ่มฟังก์ชันการทำงานให้เว็บไซต์ Joomla ของคุณ',
                        'link' => '#'
                    )
                )
            ),

            // 4. CTA
            array(
                '_type' => 'cta_module',
                'title' => 'ยังไม่แน่ใจว่าบริการไหนเหมาะกับคุณ?',
                'text' => 'ปรึกษาผู้เชี่ยวชาญของเราได้ฟรี เพื่อค้นหาโซลูชันที่ดีที่สุดสำหรับธุรกิจของคุณ',
                'bg_type' => 'brand',
                'buttons' => array(
                    array('text' => 'ติดต่อเราทันที', 'link' => 'contact', 'style' => 'light')
                )
            )
        );
        tonkla_create_demo_page('Solutions', 'solutions', $solutions_modules);
    }


    /* -------------------------------------------------------------------------
                                 15. LEGAL PAGES (Full Content from Mockups)
       ------------------------------------------------------------------------- */
    if (in_array('legal', $selected_pages)) {

        // 1. Privacy Policy
        $privacy_html = <<<HTML
<div id="intro" class="scroll-mt-32">
    <p class="lead text-xl text-slate-600 leading-relaxed font-light mb-6">
        <strong>บริษัท ต้นกล้า ไอที จำกัด</strong> ("บริษัท")
        ตระหนักถึงความสำคัญของการคุ้มครองข้อมูลส่วนบุคคลของท่าน
        เรามุ่งมั่นที่จะรักษาความปลอดภัยและเคารพสิทธิความเป็นส่วนตัวของท่านอย่างสูงสุด
    </p>
    <p>
        นโยบายความเป็นส่วนตัวฉบับนี้อธิบายถึงวิธีการที่บริษัทปฏิบัติต่อข้อมูลส่วนบุคคลของท่าน
        ตั้งแต่การเก็บรวบรวม ใช้ เปิดเผย ไปจนถึงการทำลายข้อมูล เมื่อท่านเข้าชมเว็บไซต์
        หรือใช้บริการของเรา เช่น <em>Web Hosting, จดโดเมน, หรือบริการทำเว็บไซต์</em>
    </p>
</div>

<hr class="border-slate-100 my-10">

<div id="collection" class="scroll-mt-32">
    <h2 class="text-2xl text-slate-900 group flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">1</span>
        ข้อมูลส่วนบุคคลที่เราเก็บรวบรวม
    </h2>
    <p class="mb-6">เราอาจเก็บรวบรวมข้อมูลส่วนบุคคลของท่านได้จากหลายช่องทาง เช่น เมื่อท่านสมัครสมาชิก,
        กรอกแบบฟอร์มขอใบเสนอราคา หรือติดต่อเราทาง LINE โดยข้อมูลรวมถึงแต่ไม่จำกัดเพียง:</p>
    <ul class="grid md:grid-cols-2 gap-4 list-none pl-0">
        <li class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex items-start gap-3 m-0">
            <i class="fa-solid fa-id-card text-brand mt-1"></i>
            <div>
                <strong class="block text-slate-900">ข้อมูลระบุตัวตน</strong>
                <span class="text-sm">ชื่อ-นามสกุล, เลขบัตรประชาชน (เฉพาะการจดโดเมน .th)</span>
            </div>
        </li>
        <li class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex items-start gap-3 m-0">
            <i class="fa-solid fa-address-book text-brand mt-1"></i>
            <div>
                <strong class="block text-slate-900">ข้อมูลการติดต่อ</strong>
                <span class="text-sm">ที่อยู่, อีเมล, เบอร์โทรศัพท์, LINE ID</span>
            </div>
        </li>
        <li class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex items-start gap-3 m-0">
            <i class="fa-solid fa-file-invoice-dollar text-brand mt-1"></i>
            <div>
                <strong class="block text-slate-900">ข้อมูลธุรกรรม</strong>
                <span class="text-sm">ประวัติการสั่งซื้อ, หลักฐานการโอนเงิน, ข้อมูลภาษี</span>
            </div>
        </li>
        <li class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex items-start gap-3 m-0">
            <i class="fa-solid fa-laptop-code text-brand mt-1"></i>
            <div>
                <strong class="block text-slate-900">ข้อมูลเทคนิค</strong>
                <span class="text-sm">IP Address, Cookie ID, Log Files</span>
            </div>
        </li>
    </ul>
</div>

<div id="usage" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">2</span>
        วัตถุประสงค์การเก็บรวบรวมข้อมูล
    </h2>
    <p class="mb-6">บริษัทประมวลผลข้อมูลส่วนบุคคลของท่านเพื่อวัตถุประสงค์ดังต่อไปนี้ โดยอาศัยฐานทางกฎหมาย (Legal Basis) ที่เหมาะสม:</p>
    <ul class="list-disc pl-6 space-y-4 marker:text-brand">
        <li><strong>การให้บริการตามสัญญา (Contractual Basis):</strong> เพื่อเปิดใช้งานโฮสติ้ง, จดทะเบียนโดเมนเนม, และให้บริการหลังการขาย</li>
        <li><strong>หน้าที่ตามกฎหมาย (Legal Obligation):</strong> เพื่อออกใบกำกับภาษี และจัดทำบัญชีตามกฎหมายที่เกี่ยวข้อง</li>
        <li><strong>ความยินยอม (Consent):</strong> เพื่อส่งข่าวสารโปรโมชั่น (Marketing) ในกรณีที่ท่านได้ให้ความยินยอมไว้</li>
        <li><strong>ประโยชน์โดยชอบด้วยกฎหมาย (Legitimate Interest):</strong> เพื่อรักษาความปลอดภัยของระบบ และป้องกันการทุจริต</li>
    </ul>
</div>

<div id="disclosure" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">3</span>
        การเปิดเผยข้อมูลส่วนบุคคล
    </h2>
    <p class="mb-6">
        เรามีความเคร่งครัดเรื่องการไม่ขายข้อมูลลูกค้าให้กับบุคคลภายนอก อย่างไรก็ตาม ในบางกรณี
        เราอาจมีความจำเป็นต้องส่งต่อข้อมูลของท่านให้แก่หน่วยงานที่เกี่ยวข้อง ได้แก่:
    </p>
    <ul class="list-disc pl-6 space-y-4 marker:text-brand">
        <li>
            <strong class="text-slate-900">ผู้รับจดเบียนโดเมน (Registrar):</strong>
            <span class="text-slate-600">เช่น THNIC (สำหรับ .th) หรือ GoDaddy/ResellerClub (สำหรับ .com)</span>
        </li>
        <li>
            <strong class="text-slate-900">ผู้ให้บริการชำระเงิน (Payment Gateway):</strong>
            <span class="text-slate-600">กรณีท่านชำระผ่านบัตรเครดิต หรือ QR Payment</span>
        </li>
        <li>
            <strong class="text-slate-900">หน่วยงานรัฐ:</strong>
            <span class="text-slate-600">กรมสรรพากร, สำนักงานตำรวจแห่งชาติ (ในกรณีมีหมายเรียกตามกฎหมาย)</span>
        </li>
    </ul>
</div>

<div id="retention" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">4</span>
        ระยะเวลาการจัดเก็บข้อมูล
    </h2>
    <p class="mb-6">
        เราจะจัดเก็บข้อมูลส่วนบุคคลของท่านเป็นระยะเวลาเท่าที่จำเป็นต่อการให้บริการ หรือตามที่กฎหมายกำหนด
        โดยทั่วไปเราจะเก็บข้อมูลทางบัญชีและภาษีไว้เป็นเวลา <strong>10 ปี</strong> นับจากวันที่ปิดบัญชีผู้ใช้งาน
        หลังจากนั้นข้อมูลจะถูกลบหรือทำลายอย่างปลอดภัย
    </p>
</div>

<div id="rights" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">5</span>
        สิทธิของท่าน (User Rights)
    </h2>
    <p class="mb-6">ภายใต้ พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล ท่านมีสิทธิดังนี้:</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 not-prose">
        <div class="p-4 border border-slate-200 rounded-lg hover:border-brand transition">
            <h5 class="font-bold text-slate-800 mb-1">สิทธิขอเข้าถึงข้อมูล</h5>
            <p class="text-xs text-slate-500">ขอรับสำเนาข้อมูลที่บริษัทเก็บรักษาไว้</p>
        </div>
        <div class="p-4 border border-slate-200 rounded-lg hover:border-brand transition">
            <h5 class="font-bold text-slate-800 mb-1">สิทธิขอแก้ไขข้อมูล</h5>
            <p class="text-xs text-slate-500">แก้ไขข้อมูลให้ถูกต้องและเป็นปัจจุบัน</p>
        </div>
        <div class="p-4 border border-slate-200 rounded-lg hover:border-brand transition">
            <h5 class="font-bold text-slate-800 mb-1">สิทธิขอลบข้อมูล</h5>
            <p class="text-xs text-slate-500">ขอให้ลบข้อมูลเมื่อหมดความจำเป็น</p>
        </div>
        <div class="p-4 border border-slate-200 rounded-lg hover:border-brand transition">
            <h5 class="font-bold text-slate-800 mb-1">สิทธิขอคัดค้าน</h5>
            <p class="text-xs text-slate-500">ระงับหรือคัดค้านการประมวลผลข้อมูล</p>
        </div>
    </div>
</div>

<div id="cookies" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">6</span>
        การใช้คุกกี้ (Cookies)
    </h2>
    <p class="mb-6">
        เราใช้คุกกี้เพื่อจดจำการตั้งค่าการใช้งานของท่าน และเก็บรวบรวมสถิติ เพื่อพัฒนาประสบการณ์การใช้งานเว็บไซต์ให้ดียิ่งขึ้น
        ท่านสามารถเลือก <strong>"ปฏิเสธ"</strong> คุกกี้ได้ผ่านแถบการตั้งค่าคุกกี้ หรือการตั้งค่าในเบราว์เซอร์ของท่าน
    </p>
</div>

<div id="contact" class="scroll-mt-32 mt-12 mb-12">
    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-8 text-white relative overflow-hidden shadow-xl">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
        <h3 class="text-2xl font-bold text-white mb-4 !mt-0 flex items-center gap-2">
            <i class="fa-solid fa-shield-cat text-brand"></i> ติดต่อเจ้าหน้าที่คุ้มครองข้อมูล (DPO)
        </h3>
        <p class="text-slate-300 mb-6">
            หากท่านมีข้อสงสัย หรือต้องการใช้สิทธิเกี่ยวกับข้อมูลส่วนบุคคล สามารถติดต่อเราได้ที่:
        </p>
        <div class="not-prose space-y-2 text-sm text-slate-200">
            <p><strong class="text-white">บริษัท ต้นกล้า ไอที จำกัด</strong></p>
            <p><i class="fa-solid fa-location-dot w-6 text-center"></i> 123/45 อาคารต้นกล้าทาวเวอร์ ชั้น 15 เขตจตุจักร กรุงเทพฯ 10900</p>
            <p><i class="fa-solid fa-envelope w-6 text-center"></i> <a href="mailto:dpo@tonklait.com" class="text-brand hover:text-white transition underline">dpo@tonklait.com</a></p>
            <p><i class="fa-solid fa-phone w-6 text-center"></i> 02-123-4567</p>
        </div>
    </div>
</div>
HTML;
        tonkla_create_standard_page('นโยบายความเป็นส่วนตัว (Privacy Policy)', 'privacy-policy', $privacy_html);


        // 2. Terms of Service
        $terms_html = <<<HTML
<div id="agreement" class="scroll-mt-32">
    <p class="lead text-xl text-slate-600 leading-relaxed font-light mb-6">
        ยินดีต้อนรับสู่ <strong>Tonkla IT</strong>
        บริการของเรามีเป้าหมายเพื่อให้ท่านได้รับประสบการณ์การใช้งานระบบ IT และ Hosting
        ที่ดีที่สุด เมื่อท่านเริ่มใช้บริการ ถือว่าท่านยอมรับข้อตกลงเหล่านี้
    </p>
</div>

<hr class="border-slate-100 my-10">

<div id="usage" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">1</span>
        การใช้งานบริการ
    </h2>
    <p class="mb-6">ผู้ใช้บริการตกลงที่จะใช้บริการตามวัตถุประสงค์ที่ถูกต้องตามกฎหมาย และไม่ละเมิดสิทธิของบุคคลอื่น ดังนี้:</p>
    <ul class="list-disc pl-6 space-y-4 marker:text-brand">
        <li><strong>บัญชีผู้ใช้งาน:</strong> ผู้ใช้บริการต้องมีหน้าที่ดูแลรักษารหัสผ่านให้เป็นความลับ</li>
        <li><strong>ทรัพยากรระบบ:</strong> ไม่ใช้งานทรัพยากรที่ส่งผลกระทบต่อเสถียรภาพของเซิร์ฟเวอร์ส่วนกลาง</li>
        <li><strong>การอัปเดตข้อมูล:</strong> ผู้ใช้บริการควรอัปเดตข้อมูลติดต่อให้เป็นปัจจุบันเสมอ</li>
    </ul>
</div>

<div id="content" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">2</span>
        เนื้อหาที่ต้องห้าม (Prohibited Content)
    </h2>
    <p class="mb-6">บริษัทขอสงวนสิทธิ์ในการระงับบริการทันที หากพบว่าผู้ใช้บริการนำระบบไปใช้ในสิ่งที่ผิดกฎหมายหรือผิดศีลธรรม เช่น:</p>
    <ul class="list-disc pl-6 space-y-4 marker:text-brand">
        <li>การกระทำผิดเกี่ยวกับ พ.ร.บ. คอมพิวเตอร์ แห่งราชอาณาจักรไทย</li>
        <li>การส่งอีเมลขยะ (Spam Email) หรือการพยายามเจาะระบบ (Hacking)</li>
        <li>การจัดทำเว็บไซต์ลามกอนาจาร หรือการพนันออนไลน์ทุกรูปแบบ</li>
        <li>การละเมิดลิขสิทธิ์ ทรัพย์สินทางปัญญา หรือเครื่องหมายการค้า</li>
    </ul>
</div>

<div id="payment" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">3</span>
        การชำระเงินและต่ออายุ (Payment & Renewal)
    </h2>
    <p class="mb-6">เพื่อให้การใช้งานเป็นไปอย่างต่อเนื่อง ผู้ใช้บริการควรดำเนินการชำระเงินตามเงื่อนไขดังนี้:</p>
    <ul class="list-disc pl-6 space-y-4 marker:text-brand">
        <li><strong>การต่ออายุ:</strong> บริษัทจะส่งการแจ้งเตือนก่อนวันหมดอายุ 7, 15 และ 30 วัน</li>
        <li><strong>การระงับบริการ:</strong> หากไม่มีการแจ้งชำระเงินหลังจากวันหมดอายุ ระบบจะถูกระงับ (Suspend) ทันที</li>
        <li><strong>การลบข้อมูล:</strong> ข้อมูลจะถูกลบถาวร (Delete) หลังจากพ้นกำหนดชำระเงิน 15 วัน (สำหรับโฮสติ้ง)</li>
    </ul>
</div>

<div id="security" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">4</span>
        ความปลอดภัยและความรับผิดชอบต่อข้อมูล
    </h2>
    <p class="mb-6">แม้บริษัทจะมีระบบสำรองข้อมูล แต่ผู้ใช้บริการมีหน้าที่รับผิดชอบข้อมูลของตนเองเป็นหลัก:</p>
    <ul class="list-disc pl-6 space-y-4 marker:text-brand">
        <li>ผู้ใช้บริการควรทำการสำรองข้อมูล (Backup) ด้วยตนเองสม่ำเสมอ</li>
        <li>บริษัทจะไม่รับผิดชอบต่อความสูญหายของข้อมูลในกรณีที่เกิดจากความประมาทของผู้ใช้ หรือเหตุสุดวิสัย</li>
    </ul>
</div>

<div id="termination" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">5</span>
        การยกเลิกบริการ (Termination)
    </h2>
    <p class="mb-6">ผู้ใช้บริการสามารถยกเลิกบริการได้ตลอดเวลา โดยมีเงื่อนไขการคืนเงินดังนี้:</p>
    <ul class="list-disc pl-6 space-y-4 marker:text-brand">
        <li><strong>รับประกันคืนเงิน:</strong> คืนเงินเต็มจำนวนกรณีแจ้งขอยกเลิกภายใน 30 วันแรก (ยกเว้นค่าจดโดเมน)</li>
        <li><strong>การกระทำผิดเงื่อนไข:</strong> บริษัทขอสงวนสิทธิ์ไม่คืนเงินในทุกกรณีหากผู้ใช้บริการละเมิดข้อตกลงในข้อ 2</li>
    </ul>
</div>

<div id="liability" class="scroll-mt-32 mt-12">
    <h2 class="text-2xl text-slate-900 flex items-center gap-3 mb-6">
        <span class="w-8 h-8 bg-brand/10 text-brand rounded-lg flex items-center justify-center text-lg shadow-sm">6</span>
        ข้อจำกัดความรับผิดชอบ (Limitation of Liability)
    </h2>
    <p class="mb-6">บริษัทจะไม่รับผิดชอบต่อความเสียหายทางธุรกิจ หรือผลกำไรที่สูญเสียไป อันเนื่องมาจากการขัดข้องของระบบ แต่บริษัทจะดำเนินการแก้ไขปัญหาอย่างสุดความสามารถเพื่อให้ระบบกลับมาใช้งานได้ตามปกติ (SLA 99.9%)</p>
</div>
HTML;
        tonkla_create_standard_page('เงื่อนไขการใช้บริการ (Terms of Service)', 'terms-of-service', $terms_html);
    }


    /* -------------------------------------------------------------------------
                                 16. MOBILE APP (NEW)
       ------------------------------------------------------------------------- */
    if (in_array('mobile_app', $selected_pages)) {
        $mobile_modules = array(
            // 1. Hero
            array(
                '_type' => 'hero_module',
                'hero_type' => 'service_alt',
                'bg_type' => 'pattern',
                'badge' => 'Mobile Application Development',
                'headline' => 'สร้างแอปพลิเคชันของคุณ <br><strong>บน iOS และ Android</strong>',
                'description' => 'บริการรับทำ Mobile App ครบวงจร ทั้ง Native และ Cross-Platform ออกแบบ UX/UI ที่ทันสมัย ใช้งานง่าย พร้อมเชื่อมต่อระบบ API อย่างสมบูรณ์',
                'buttons' => array(
                    array('text' => 'ขอคำปรึกษาฟรี', 'icon' => 'fa-regular fa-comment-dots', 'link' => 'contact', 'style' => 'primary')
                )
            ),

            // 2. Services Grid
            array(
                '_type' => 'service_grid',
                'section_title' => 'บริการ Mobile App ของเรา',
                'section_subtitle' => 'ตอบโจทย์ทุกความต้องการด้วยเทคโนโลยีที่เหมาะสม',
                'layout' => 'cols-3',
                'items' => array(
                    array(
                        'icon' => 'fa-brands fa-react',
                        'icon_color' => 'blue',
                        'title' => 'Cross-Platform App',
                        'description' => 'พัฒนาครั้งเดียวได้ทั้ง iOS และ Android ด้วย React Native หรือ Flutter ประหยัดงบและเวลาดูแลรักษา',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-brands fa-apple',
                        'icon_color' => 'brand',
                        'title' => 'Native iOS App',
                        'description' => 'พัฒนาแอปสำหรับ iPhone/iPad โดยเฉพาะ ด้วย Swift เพื่อประสิทธิภาพสูงสุดและการเข้าถึงฟีเจอร์เครื่อง',
                        'link' => '#'
                    ),
                    array(
                        'icon' => 'fa-brands fa-android',
                        'icon_color' => 'green',
                        'title' => 'Native Android App',
                        'description' => 'พัฒนาแอปสำหรับ Android โดยเฉพาะ ด้วย Kotlin เพื่อรองรับอุปกรณ์ที่หลากหลายในตลาด',
                        'link' => '#'
                    )
                )
            ),

            // 3. Tech Stack
            array(
                '_type' => 'service_grid',
                'section_title' => 'Tech Stack',
                'bg_color' => 'dark',
                'layout' => 'cols-4',
                'items' => array(
                    array('icon' => 'fa-brands fa-flutter', 'icon_color' => 'blue', 'title' => 'Flutter', 'description' => 'Google UI Toolkit'),
                    array('icon' => 'fa-brands fa-react', 'icon_color' => 'blue', 'title' => 'React Native', 'description' => 'Facebook Framework'),
                    array('icon' => 'fa-brands fa-swift', 'icon_color' => 'orange', 'title' => 'Swift', 'description' => 'iOS Native'),
                    array('icon' => 'fa-brands fa-google-play', 'icon_color' => 'green', 'title' => 'Kotlin', 'description' => 'Android Native'),
                )
            )
        );
        tonkla_create_demo_page('Mobile App Development', 'mobile-app', $mobile_modules);
    }

	wp_safe_redirect( add_query_arg( 'import_status', 'success', admin_url( 'admin.php?page=crb_carbon_fields_container_dashboard.php' ) ) );
	exit;
}
