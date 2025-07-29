<?php

// Showing price in EUR next to BGN
add_filter('woocommerce_get_price_html', 'add_euro_discount_prices', 100, 2);

function add_euro_discount_prices($price_html, $product) {
    $regular_price = $product->get_regular_price();
    $sale_price = $product->get_sale_price();

    $euro_rate = 1.95583;

    if ($sale_price && $sale_price < $regular_price) {
        $regular_euro = number_format($regular_price / $euro_rate, 2);
        $sale_euro = number_format($sale_price / $euro_rate, 2);

        $price_html .= '<br><span style="font-size: 100%;">';
        $price_html .= '<del>' . $regular_euro . ' €</del> ';
        $price_html .= '<strong>' . $sale_euro . ' €</strong>';
        $price_html .= '</span>';
    } else {
        $price = $product->get_price();
        $euro_price = number_format($price / $euro_rate, 2);
        $price_html .= '<br><span style="font-size: 100%;">' . $euro_price . ' €</span>';
    }

    return $price_html;
}

// Adding an euro amount to a product unit price
add_filter('woocommerce_cart_item_price', 'add_euro_cart_prices', 100, 3);
function add_euro_cart_prices($price_html, $cart_item, $cart_item_key) {
    $price = $cart_item['data']->get_price();
    $euro_rate = 1.95583;
    $euro_price = number_format($price / $euro_rate, 2);

    $price_html .= '<br><span class="euro-price">(' . $euro_price . ' €)</span>';

    return $price_html;
}

// Display Cart Item Subtotal with Coupon Discount in Bulgarian lev and Euro

add_filter( 'woocommerce_cart_item_subtotal', 'show_coupon_item_subtotal_discount_conditional_with_euro_classes', 100, 3 );
function show_coupon_item_subtotal_discount_conditional_with_euro_classes( $subtotal, $cart_item, $cart_item_key ) {
    $_product  = $cart_item['data'];
    $quantity  = isset( $cart_item['quantity'] ) ? intval( $cart_item['quantity'] ) : 1;

    $regular_price = floatval( $_product->get_regular_price() );
    $sale_price    = floatval( $_product->get_sale_price() );

    $line_subtotal = isset( $cart_item['line_subtotal'] ) ? floatval( $cart_item['line_subtotal'] ) : 0;
    $line_total    = isset( $cart_item['line_total'] ) ? floatval( $cart_item['line_total'] ) : 0;
    $line_tax      = isset( $cart_item['line_tax'] ) ? floatval( $cart_item['line_tax'] ) : 0;

    $euro_rate = 1.95583;

    // Getting applied coupons
    $applied_coupons = WC()->cart->get_applied_coupons();

    // Check if there are any applicable coupons for the product
    $applicable_coupons = [];
    foreach ( $applied_coupons as $coupon_code ) {
        $coupon = new WC_Coupon( $coupon_code );
        $product_ids = $coupon->get_product_ids();
        $excluded_product_ids = $coupon->get_excluded_product_ids();
        $exclude_sale_items = $coupon->get_exclude_sale_items();

        $is_on_sale = $_product->is_on_sale();

        if ( 
            ( empty( $product_ids ) || in_array( $cart_item['product_id'], $product_ids ) ) &&
            ! in_array( $cart_item['product_id'], $excluded_product_ids ) &&
            ( ! $exclude_sale_items || ! $is_on_sale )
        ) {
            $applicable_coupons[] = $coupon_code;
        }
    }

    $coupon_label = '';
    if ( ! empty( $applicable_coupons ) ) {
        $coupon_label = __( 'Код: ', 'textdomain' ) . esc_html( implode( ', ', $applicable_coupons ) );
    }

    if ( $line_subtotal !== $line_total ) {
        $discount_raw = max( 0, ( $regular_price * $quantity - $line_subtotal - $line_tax ) + ( $line_subtotal - $line_total ) );
        $discountAmt  = wc_price( $discount_raw );

        $euro_regular     = number_format( ( $regular_price * $quantity ) / $euro_rate, 2 );
        $euro_discounted  = number_format( ( $line_total + $line_tax ) / $euro_rate, 2 );
        $euro_discount    = number_format( $discount_raw / $euro_rate, 2 );

        $subtotal = sprintf(
            '<del class="subtotal-regular">%s</del> <ins class="subtotal-discounted">%s</ins>
            <br><span class="subtotal-euro-text"><del class="euro-regular">%s €</del> <strong class="euro-discounted">%s €</strong></span>
            <p class="coupon-discount-info"><span class="coupon-code"><i class="fa fa-tags" aria-hidden="true"></i> %s</span><br><span class="save-label">Спестявате:</span> <strong class="save-amount">%s / %s €</strong></p>',
            wc_price( $regular_price * $quantity ),
            wc_price( $line_total + $line_tax ),
            $euro_regular,
            $euro_discounted,
            $coupon_label,
            $discountAmt,
            $euro_discount
        );

    } elseif ( $sale_price > 0 && $regular_price > $sale_price ) {
        $discount_raw     = max( 0, $regular_price * $quantity - $sale_price * $quantity );
        $discountAmt      = wc_price( $discount_raw );
        $euro_regular     = number_format( ( $regular_price * $quantity ) / $euro_rate, 2 );
        $euro_discounted  = number_format( ( $sale_price * $quantity ) / $euro_rate, 2 );
        $euro_discount    = number_format( $discount_raw / $euro_rate, 2 );

        $subtotal = sprintf(
            '<del class="subtotal-regular">%s</del> <ins class="subtotal-discounted">%s</ins>
            <br><span class="subtotal-euro-text"><del class="euro-regular">%s €</del> <strong class="euro-discounted">%s €</strong></span>
            <p class="coupon-discount-info"><span class="save-label">Спестявате:</span> <strong class="save-amount">%s / %s €</strong></p>',
            wc_price( $regular_price * $quantity ),
            wc_price( $sale_price * $quantity ),
            $euro_regular,
            $euro_discounted,
            $discountAmt,
            $euro_discount
        );

    } else {
        $line_total_with_tax = $line_total + $line_tax;
        $euro_total = number_format( $line_total_with_tax / $euro_rate, 2 );

        $subtotal = sprintf(
            '%s<span class="subtotal-euro-text"> / %s €</span>',
            wc_price( $line_total_with_tax ),
            $euro_total
        );
    }

    return $subtotal;
}


// Adding euros only in the cart, not on the checkout page
add_filter('woocommerce_cart_subtotal', 'add_euro_to_cart_subtotal', 100, 1);
function add_euro_to_cart_subtotal($subtotal_html) {
    if (is_checkout()) return $subtotal_html; // Do not add to checkout

    $euro_rate = 1.95583;
    $subtotal = WC()->cart->get_subtotal() + WC()->cart->get_subtotal_tax();
    $euro = number_format($subtotal / $euro_rate, 2);

    return $subtotal_html . '<span class="euro-subtotal" style="font-size:1em;"> / ' . $euro . ' €</span>';
}


// Adding euros to the coupon rows
add_filter('woocommerce_cart_totals_coupon_html', 'add_euro_to_coupon_discount', 100, 2);
function add_euro_to_coupon_discount($discount_html, $coupon) {
    $euro_rate = 1.95583;
    $amount = WC()->cart->get_coupon_discount_amount($coupon->get_code(), WC()->cart->display_cart_ex_tax);
    $amount += WC()->cart->get_coupon_discount_tax_amount($coupon->get_code());

    $euro = number_format($amount / $euro_rate, 2);
    return $discount_html . '<br><span class="euro-coupon">(' . $euro . ' €)</span>';
}

// Euro value to the total amount in the cart
add_filter('woocommerce_cart_totals_order_total_html', 'add_euro_total_to_cart_only', 100);
function add_euro_total_to_cart_only($value) {
    if (!is_cart()) return $value;
    $euro_rate = 1.95583;
    $cart = WC()->cart;
    $grand_total = $cart->get_cart_contents_total() + $cart->get_shipping_total() + $cart->get_fee_total() + $cart->get_total_tax();
    $euro_total = number_format($grand_total / $euro_rate, 2);

    $value .= '<span class="euro-cart-total"><strong> / ' . $euro_total . ' €</strong></span>';

    return $value;
}

// Euro shipping price and final price (checkout)
add_action('wp_footer', 'add_euro_shipping_and_total_display');
function add_euro_shipping_and_total_display() {
    if (is_checkout()) {
        ?>
        <script type="text/javascript">
        jQuery(function($) {
            const euroRate = 1.95583;

            function parsePrice(text) {
                return parseFloat(text.replace(/[^\d.,]/g, '').replace(',', '.'));
            }

            function updateEuroValues() {
                const totalEl = $('#custom_price');
                if (totalEl.length) {
                    const totalVal = parsePrice(totalEl.text());
                    const euroTotal = (totalVal / euroRate).toFixed(2);
                    if (!$('.euro-grand-total').length) {
                        totalEl.after('<span class="euro-grand-total"><strong>(' + euroTotal + ' €)</strong></span>');
                    } else {
                        $('.euro-grand-total strong').text(' / ' + euroTotal + ' €');
                    }
                }

                let shippingRow = null;
                $('.cart_totals tr, .woocommerce-checkout-review-order-table tr').each(function() {
                    const label = $(this).find('th, td').first().text().toLowerCase();
                    if (label.includes('доставка')) {
                        shippingRow = $(this);
                        return false;
                    }
                });

                if (shippingRow) {
                    const shippingEl = shippingRow.find('.woocommerce-Price-amount').first();
                    const shippingVal = parsePrice(shippingEl.text());
                    const euroShipping = (shippingVal / euroRate).toFixed(2);

                    if (!shippingRow.find('.euro-shipping').length) {
                        shippingEl.after('<span class="euro-shipping"><strong>(' + euroShipping + ' €)</strong></span>');
                    } else {
                        shippingRow.find('.euro-shipping strong').text(' / ' + euroShipping + ' €');
                    }
                }
            }

            updateEuroValues();

            const observer = new MutationObserver(() => {
                setTimeout(updateEuroValues, 100);
            });

            observer.observe(document.body, { childList: true, subtree: true });

            $('body').on('updated_checkout updated_shipping_method', function() {
                setTimeout(updateEuroValues, 300);
            });
        });
        </script>
        <?php
    }
}


// Euro amount to all totals in emails
add_filter('woocommerce_get_order_item_totals', 'add_euro_to_all_email_totals_final', 100, 3);
function add_euro_to_all_email_totals_final($totals, $order, $tax_display) {
    $euro_rate = 1.95583;

    foreach ($totals as $key => $total) {
        if (empty($total['value'])) continue;

        $matches = [];
        if (preg_match('/([\d,.]+)/', $total['value'], $matches)) {
            $value_bgn = floatval(str_replace(',', '.', $matches[1]));
            $euro_value = number_format($value_bgn / $euro_rate, 2);

            $totals[$key]['value'] .= '<br><span class="euro-email-total">(' . $euro_value . ' €)</span>';
        }
    }

    return $totals;
}


// Euro to subtotal
add_filter('woocommerce_order_formatted_line_subtotal', 'add_euro_to_line_subtotal_final', 100, 3);
function add_euro_to_line_subtotal_final($subtotal, $item, $order) {
    $euro_rate = 1.95583;
    $subtotal_bgn = $item->get_total();
    $euro_subtotal = number_format($subtotal_bgn / $euro_rate, 2);

    $subtotal .= '<br><span class="euro-product-subtotal">(' . $euro_subtotal . ' €)</span>';

    return $subtotal;
}
