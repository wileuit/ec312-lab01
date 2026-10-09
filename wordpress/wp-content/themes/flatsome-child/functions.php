<?php
/**
 * EC312 - LAB 02
 * Shop Dong Ho - Flatsome Child Theme
 *
 * Features:
 * 1. Custom Vietnamese footer
 * 2. WooCommerce catalog customization
 * 3. Shop banner
 * 4. Product grid configuration
 * 5. Product category sidebar filtering
 * 6. Vietnamese WooCommerce labels
 *
 * Preserves:
 * - Patek Philippe header
 * - Homepage V2
 * - WooCommerce products and database
 * - Existing email settings
 */

if (!defined('ABSPATH')) {
    exit;
}

/* =====================================================
   1. LOAD CHILD THEME CSS
===================================================== */

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'ec312-child-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
}, 20);


/* =====================================================
   2. DISABLE DEFAULT FOOTER WIDGETS
===================================================== */

add_filter('sidebars_widgets', function ($sidebars) {

    if (is_admin()) {
        return $sidebars;
    }

    $footer_sidebars = array(
        'sidebar-footer-1',
        'sidebar-footer-2'
    );

    foreach ($footer_sidebars as $id) {
        if (isset($sidebars[$id])) {
            $sidebars[$id] = array();
        }
    }

    return $sidebars;
});


/* =====================================================
   3. CUSTOM VIETNAMESE FOOTER
===================================================== */

function ec312_render_custom_footer() {

    $shop_url = function_exists('wc_get_page_permalink')
        ? wc_get_page_permalink('shop')
        : home_url('/shop/');

    ?>
    <div class="ec312-footer">
        <div class="ec312-footer-grid">

            <div class="ec312-footer-column">
                <h3>SHOP ĐỒNG HỒ</h3>

                <p>
                    Tinh hoa thời gian,
                    khẳng định phong cách.
                </p>

                <p>
                    Khám phá những mẫu đồng hồ
                    phù hợp với phong cách
                    và cá tính của bạn.
                </p>
            </div>

            <div class="ec312-footer-column">
                <h3>DANH MỤC SẢN PHẨM</h3>

                <ul>
                    <li>
                        <a href="<?php echo esc_url($shop_url); ?>">
                            Tất cả sản phẩm
                        </a>
                    </li>

                    <li>Đồng hồ nam</li>
                    <li>Đồng hồ nữ</li>
                    <li>Đồng hồ Unisex</li>
                </ul>
            </div>

            <div class="ec312-footer-column">
                <h3>HỖ TRỢ KHÁCH HÀNG</h3>

                <ul>
                    <li>Hướng dẫn mua hàng</li>
                    <li>Phương thức thanh toán</li>
                    <li>Chính sách giao hàng</li>
                    <li>Hỗ trợ khách hàng</li>
                </ul>
            </div>

            <div class="ec312-footer-column">
                <h3>THÔNG TIN LIÊN HỆ</h3>

                <p>Shop Đồng Hồ</p>
                <p>TP. Hồ Chí Minh, Việt Nam</p>

                <p>
                    Website:
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        Shop Đồng Hồ
                    </a>
                </p>
            </div>

        </div>

        <div class="ec312-footer-bottom">
            <p>
                &copy; <?php echo esc_html(wp_date('Y')); ?>
                Shop Đồng Hồ. All rights reserved.
            </p>
        </div>
    </div>
    <?php
}

add_action(
    'flatsome_footer',
    'ec312_render_custom_footer',
    20
);


/* =====================================================
   4. WOOCOMMERCE PRODUCT CATALOG
===================================================== */

/**
 * Set product columns to 4.
 * Flatsome may override this through theme settings.
 */
add_filter('loop_shop_columns', function ($columns) {
    if (is_shop() || is_product_category()) {
        return 4;
    }

    return $columns;
}, 20);

/**
 * Display 12 products per page.
 */
add_filter('loop_shop_per_page', function ($per_page) {
    if (is_shop() || is_product_category()) {
        return 12;
    }

    return $per_page;
}, 20);


/* =====================================================
   5. CUSTOM SHOP BANNER
===================================================== */

function ec312_render_shop_banner() {

    if (!function_exists('is_shop') || !is_shop()) {
        return;
    }

    ?>
    <div class="ec312-shop-banner">

        <span class="ec312-shop-eyebrow">
            THE WATCH COLLECTION
        </span>

        <h1>BỘ SƯU TẬP ĐỒNG HỒ</h1>

        <p>
            Khám phá những thiết kế đồng hồ
            dành cho phong cách của bạn.
        </p>

    </div>
    <?php
}

add_action(
    'woocommerce_before_main_content',
    'ec312_render_shop_banner',
    5
);


/* =====================================================
   6. CUSTOM SALE BADGE
===================================================== */

add_filter('woocommerce_sale_flash', function ($html) {

    if (
        function_exists('is_shop') &&
        (is_shop() || is_product_category())
    ) {
        return '<span class="onsale">Ưu đãi</span>';
    }

    return $html;

}, 20);


/* =====================================================
   7. FILTER PRODUCT CATEGORY SIDEBAR
===================================================== */

/**
 * Show watch-related categories in the standard
 * WooCommerce Product Categories widget.
 *
 * Categories that do not exist will be ignored.
 * This does not delete or modify product data.
 */

add_filter(
    'woocommerce_product_categories_widget_args',
    function ($args) {

        if (
            !function_exists('is_shop') ||
            (!is_shop() && !is_product_category())
        ) {
            return $args;
        }

        $allowed_slugs = array(
            'dong-ho-nam',
            'dong-ho-nu',
            'dong-ho-unisex',
            'watches',
            'men',
            'women',
            'casio',
            'seiko',
            'orient',
            'tissot'
        );

        $terms = get_terms(array(
            'taxonomy'   => 'product_cat',
            'slug'       => $allowed_slugs,
            'hide_empty' => false,
            'fields'     => 'ids'
        ));

        if (
            !is_wp_error($terms) &&
            !empty($terms)
        ) {
            $args['include'] = array_map('intval', $terms);
        }

        $args['hierarchical'] = false;
        $args['show_count'] = true;

        return $args;
    },
    20
);


/* =====================================================
   8. VIETNAMESE CATALOG LABELS
===================================================== */

add_filter('gettext', function (
    $translated,
    $original,
    $domain
) {

    if ($domain !== 'woocommerce') {
        return $translated;
    }

    $labels = array(
        'Default sorting'
            => 'Sắp xếp mặc định',

        'Sort by popularity'
            => 'Phổ biến nhất',

        'Sort by average rating'
            => 'Đánh giá cao nhất',

        'Sort by latest'
            => 'Sản phẩm mới nhất',

        'Sort by price: low to high'
            => 'Giá từ thấp đến cao',

        'Sort by price: high to low'
            => 'Giá từ cao đến thấp',

        'Add to cart'
            => 'Thêm vào giỏ hàng',

        'Read more'
            => 'Xem chi tiết',

        'Sale!'
            => 'Ưu đãi',

        'Shop'
            => 'Cửa hàng'
    );

    if (isset($labels[$original])) {
        return $labels[$original];
    }

    return $translated;

}, 20, 3);


/* =====================================================
   9. VIETNAMESE PRODUCT RESULT COUNT
===================================================== */

/**
 * Customize WooCommerce result count template text.
 * This filter works only when the theme applies it.
 */

add_filter(
    'woocommerce_result_count',
    function ($html) {

        if (
            !function_exists('is_shop') ||
            !is_shop()
        ) {
            return $html;
        }

        global $wp_query;

        if (!$wp_query) {
            return $html;
        }

        $total = (int) $wp_query->found_posts;

        return sprintf(
            '<p class="woocommerce-result-count">Có %s sản phẩm</p>',
            esc_html(number_format_i18n($total))
        );
    },
    20
);


/* =====================================================
   10. EC312 LAB 02 - END
===================================================== */

/**
 * Important:
 * - Do not edit Flatsome parent theme files.
 * - Do not remove WooCommerce product data.
 * - Do not change SMTP settings here.
 * - Keep the child theme active.
 * - Product grid styling is handled in style.css.
 */

/* =====================================================
   EC312 LAB 02 - PRODUCT DETAILS V2
===================================================== */

/**
 * Display purchase benefits under add-to-cart.
 * Keep WooCommerce purchase functionality unchanged.
 */
function ec312_product_purchase_benefits() {
    if (!is_product()) {
        return;
    }
    ?>
    <div class="ec312-purchase-benefits">
        <div class="ec312-benefit">
            <span class="ec312-benefit-icon">✓</span>
            <div>
                <strong>CAM KẾT CHẤT LƯỢNG</strong>
                <p>Thông tin sản phẩm rõ ràng, minh bạch.</p>
            </div>
        </div>

        <div class="ec312-benefit">
            <span class="ec312-benefit-icon">↗</span>
            <div>
                <strong>GIAO HÀNG TOÀN QUỐC</strong>
                <p>Hỗ trợ giao hàng đến nhiều khu vực tại Việt Nam.</p>
            </div>
        </div>

        <div class="ec312-benefit">
            <span class="ec312-benefit-icon">♧</span>
            <div>
                <strong>HỖ TRỢ KHÁCH HÀNG</strong>
                <p>Đồng hành cùng bạn trong quá trình mua sắm.</p>
            </div>
        </div>
    </div>
    <?php
}

add_action(
    'woocommerce_single_product_summary',
    'ec312_product_purchase_benefits',
    35
);

/**
 * Add a section heading before product tabs.
 */
function ec312_product_details_heading() {
    if (!is_product()) {
        return;
    }

    echo '<div class="ec312-detail-heading">';
    echo '<span>PRODUCT INFORMATION</span>';
    echo '<h2>THÔNG TIN SẢN PHẨM</h2>';
    echo '</div>';
}

add_action(
    'woocommerce_after_single_product_summary',
    'ec312_product_details_heading',
    5
);

/**
 * Keep four related products.
 */
add_filter(
    'woocommerce_output_related_products_args',
    function ($args) {
        $args['posts_per_page'] = 4;
        $args['columns'] = 4;
        return $args;
    },
    20
);

/**
 * Customize related products title.
 */
add_filter(
    'woocommerce_product_related_products_heading',
    function ($heading) {
        return 'CÓ THỂ BẠN SẼ THÍCH';
    }
);

/* =====================================================
   EC312 LAB 02 - CART PAGE V2
===================================================== */

/**
 * Cart page introduction.
 */
function ec312_cart_intro() {
    if (!function_exists('is_cart') || !is_cart()) {
        return;
    }

    echo '<div class="ec312-cart-intro">';
    echo '<span>YOUR SHOPPING BAG</span>';
    echo '<h2>GIỎ HÀNG CỦA BẠN</h2>';
    echo '<p>Kiểm tra sản phẩm và số lượng trước khi thanh toán.</p>';
    echo '</div>';
}

add_action(
    'woocommerce_before_cart',
    'ec312_cart_intro',
    5
);

/**
 * Translate shipping label on cart page.
 */
add_filter('woocommerce_shipping_package_name', function ($name) {
    if (function_exists('is_cart') && is_cart()) {
        return 'Vận chuyển';
    }

    return $name;
});

/**
 * Add customer reassurance below cart totals.
 */
function ec312_cart_support_message() {
    if (!function_exists('is_cart') || !is_cart()) {
        return;
    }

    echo '<div class="ec312-cart-support">';
    echo '<strong>THANH TOÁN AN TOÀN</strong>';
    echo '<p>Thông tin đơn hàng được xử lý qua hệ thống WooCommerce.</p>';
    echo '<p>Hỗ trợ khách hàng trong quá trình mua sắm.</p>';
    echo '</div>';
}

add_action(
    'woocommerce_after_cart_totals',
    'ec312_cart_support_message',
    15
);

/* =====================================================
   EC312 LAB 02 - CHECKOUT V2
   Flatsome Child Theme
===================================================== */

/**
 * Checkout introduction.
 * Only for classic WooCommerce checkout.
 */
function ec312_checkout_v2_intro() {

    if (
        !function_exists('is_checkout') ||
        !is_checkout() ||
        is_order_received_page()
    ) {
        return;
    }

    echo '<div class="ec312-checkout-intro">';
    echo '<span>SECURE CHECKOUT</span>';
    echo '<h2>THANH TOÁN ĐƠN HÀNG</h2>';
    echo '<p>Hoàn tất thông tin để đặt mua chiếc đồng hồ của bạn.</p>';
    echo '</div>';
}

add_action(
    'woocommerce_before_checkout_form',
    'ec312_checkout_v2_intro',
    5
);


/**
 * Customer reassurance.
 * Display inside the checkout payment section.
 */
function ec312_checkout_v2_reassurance() {

    if (
        !function_exists('is_checkout') ||
        !is_checkout() ||
        is_order_received_page()
    ) {
        return;
    }

    ?>
    <div class="ec312-checkout-reassurance">

        <div class="ec312-checkout-reassurance-item">
            <strong>THANH TOÁN KHI NHẬN HÀNG</strong>
            <p>
                Thanh toán bằng tiền mặt khi nhận hàng
                nếu lựa chọn phương thức COD.
            </p>
        </div>

        <div class="ec312-checkout-reassurance-item">
            <strong>THÔNG TIN ĐƠN HÀNG</strong>
            <p>
                Kiểm tra tổng tiền và phí vận chuyển
                trước khi xác nhận đặt hàng.
            </p>
        </div>

        <div class="ec312-checkout-reassurance-item">
            <strong>HỖ TRỢ KHÁCH HÀNG</strong>
            <p>
                Shop Đồng Hồ đồng hành cùng bạn
                trong quá trình mua sắm.
            </p>
        </div>

    </div>
    <?php
}

add_action(
    'woocommerce_review_order_after_submit',
    'ec312_checkout_v2_reassurance',
    15
);

/* =====================================================
   EC312 - ORDER COMPLETE V2
   Shop Dong Ho / Flatsome Child
===================================================== */

/**
 * Render the order confirmation hero.
 * Data is retrieved from the actual WooCommerce order.
 */
function ec312_order_complete_v2_hero($order_id) {

    if (!$order_id || !function_exists('wc_get_order')) {
        return;
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return;
    }

    $order_number = $order->get_order_number();
    $order_total  = $order->get_formatted_order_total();
    $payment      = $order->get_payment_method_title();
    $status       = wc_get_order_status_name(
        $order->get_status()
    );

    ?>
    <section class="ec312-order-success">

        <div class="ec312-order-success-hero">

            <span class="ec312-order-checkmark"
                  aria-hidden="true">&#10003;</span>

            <span class="ec312-order-eyebrow">
                ORDER CONFIRMED
            </span>

            <h1>ĐẶT HÀNG THÀNH CÔNG!</h1>

            <p>
                Cảm ơn bạn đã tin tưởng Shop Đồng Hồ.
                Đơn hàng của bạn đã được ghi nhận.
            </p>

        </div>

        <div class="ec312-order-success-details">

            <div class="ec312-order-info">
                <span>MÃ ĐƠN HÀNG</span>
                <strong>
                    #<?php echo esc_html($order_number); ?>
                </strong>
            </div>

            <div class="ec312-order-info">
                <span>TỔNG THANH TOÁN</span>
                <strong>
                    <?php echo wp_kses_post($order_total); ?>
                </strong>
            </div>

            <div class="ec312-order-info">
                <span>PHƯƠNG THỨC THANH TOÁN</span>
                <strong>
                    <?php echo esc_html($payment); ?>
                </strong>
            </div>

            <div class="ec312-order-info">
                <span>TRẠNG THÁI ĐƠN HÀNG</span>
                <strong>
                    <?php echo esc_html($status); ?>
                </strong>
            </div>

        </div>

        <div class="ec312-order-success-actions">

            <a class="ec312-order-shop-button"
               href="<?php echo esc_url(
                   wc_get_page_permalink('shop')
               ); ?>">
                TIẾP TỤC MUA SẮM
            </a>

        </div>

    </section>
    <?php
}

/**
 * Insert only on the WooCommerce thank-you page.
 * Do not replace the original order details.
 */
add_action(
    'woocommerce_thankyou',
    'ec312_order_complete_v2_hero',
    1
);
