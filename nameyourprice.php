<?php
/**
 * Plugin Name: Name Your Price
 * Description: This plugin allows you to have your customers name a price for your products.
 * Version: 0.0.7
 * Author: Aiirik
 */

// Add a backend settings page for a menu in the admin bar for Name Your Price
function nameyourprice_settings_page() {
    add_menu_page(
        'Name Your Price Settings',
        'Name Your Price',
        'manage_options',
        'nameyourprice',
        'nameyourprice_render_settings_page',
        'dashicons-tag' // Icon class
    );
}
add_action('admin_menu', 'nameyourprice_settings_page');


// Render the settings page
function nameyourprice_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Name Your Price Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('nameyourprice_settings');
            do_settings_sections('nameyourprice_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register the selected category and price range settings
function nameyourprice_register_settings() {
    register_setting('nameyourprice_settings', 'nameyourprice_selected_category');
    register_setting('nameyourprice_settings', 'nameyourprice_min_price');
    register_setting('nameyourprice_settings', 'nameyourprice_max_price');
    register_setting('nameyourprice_settings', 'nameyourprice_custom_text');
    register_setting('nameyourprice_settings', 'nameyourprice_currency_symbol'); // New currency symbol setting
    register_setting('nameyourprice_settings', 'nameyourprice_custom_placeholder_text');

    add_settings_section(
        'nameyourprice_section',
        'Name Your Price Settings',
        'nameyourprice_section_callback',
        'nameyourprice_settings'
    );

    add_settings_field(
        'nameyourprice_selected_category',
        'Selected Category',
        'nameyourprice_selected_category_callback',
        'nameyourprice_settings',
        'nameyourprice_section'
    );

    add_settings_field(
        'nameyourprice_currency_symbol',
        'Currency Symbol', // New currency symbol field
        'nameyourprice_currency_symbol_callback',
        'nameyourprice_settings',
        'nameyourprice_section'
    );

    add_settings_field(
        'nameyourprice_min_price',
        'Minimum Price',
        'nameyourprice_min_price_callback',
        'nameyourprice_settings',
        'nameyourprice_section'
    );

    add_settings_field(
        'nameyourprice_max_price',
        'Maximum Price',
        'nameyourprice_max_price_callback',
        'nameyourprice_settings',
        'nameyourprice_section'
    );
    
    add_settings_field(
        'nameyourprice_custom_text',
        'Custom Price Field Text',
        'nameyourprice_custom_text_callback',
        'nameyourprice_settings',
        'nameyourprice_section'
    );

    add_settings_field(
        'nameyourprice_custom_placeholder_text',
        'Custom Placeholder Text',
        'nameyourprice_custom_placeholder_text_callback',
        'nameyourprice_settings',
        'nameyourprice_section'
    );
}
add_action('admin_init', 'nameyourprice_register_settings');

// Callback function for the settings section
function nameyourprice_section_callback() {
    echo '<p>Configure the settings for the Name Your Price plugin.</p>';
}

// Callback function for the selected category field
function nameyourprice_selected_category_callback() {
    $selected_category = get_option('nameyourprice_selected_category', '');
    $args = array(
        'show_option_none' => 'Select a category',
        'option_none_value' => '',
        'name' => 'nameyourprice_selected_category',
        'selected' => $selected_category,
        'taxonomy' => 'product_cat', // Specify the taxonomy as product_cat
        'hierarchical' => true, // Display categories hierarchically
        'hide_empty' => false // Show empty categories
    );
    wp_dropdown_categories($args);
}

// Callback function for the currency symbol field
function nameyourprice_currency_symbol_callback() {
    $currency_symbol = get_option('nameyourprice_currency_symbol', '$'); // Get the currency symbol option
    
    $currency_symbols = array(
        '$' => 'CAD / USD ($)',
        '€' => 'EUR (€)',
        '£' => 'GBP (£)',
        '¥' => 'JPY (¥)',
        '₹' => 'INR (₹)',
        'other' => 'Other',
    );
    
    echo '<select name="nameyourprice_currency_symbol" id="nameyourprice_currency_symbol" style="vertical-align: middle;">';
    
    foreach ($currency_symbols as $symbol => $label) {
        $selected = ($currency_symbol === $symbol) ? 'selected' : '';
        echo '<option value="' . esc_attr($symbol) . '" ' . $selected . '>' . esc_html($label) . '</option>';
    }
    
    echo '</select>';
    
    // Show the custom currency symbol text field if "other" is selected
    $custom_currency_symbol = get_option('nameyourprice_custom_currency_symbol', '');
    $display = ($currency_symbol === 'other') ? 'inline-block' : 'none';
    
    echo '<input type="text" name="nameyourprice_custom_currency_symbol" id="nameyourprice_custom_currency_symbol" value="' . esc_attr($custom_currency_symbol) . '" placeholder="Enter currency symbol" style="display: ' . esc_attr($display) . '; width: auto; margin-left: 10px; vertical-align: middle;" />';
    
    // JavaScript to show/hide the custom currency symbol field based on selection
    ?>
    <script>
        jQuery(function($) {
            var currencySymbolDropdown = $('#nameyourprice_currency_symbol');
            var customSymbolField = $('#nameyourprice_custom_currency_symbol');
            
            currencySymbolDropdown.on('change', function() {
                var selectedValue = $(this).val();
                customSymbolField.toggle(selectedValue === 'other');
            }).trigger('change');
        });
    </script>
    <?php
}

// Callback function for the minimum price field
function nameyourprice_min_price_callback() {
    $min_price = get_option('nameyourprice_min_price', '0');
    echo '<input type="text" name="nameyourprice_min_price" value="' . esc_attr($min_price) . '" />';
}

// Callback function for the maximum price field
function nameyourprice_max_price_callback() {
    $max_price = get_option('nameyourprice_max_price', '9999');
    echo '<input type="text" name="nameyourprice_max_price" value="' . esc_attr($max_price) . '" />';
}

// Callback function for the custom price field text
function nameyourprice_custom_text_callback() {
    $custom_text = get_option('nameyourprice_custom_text', 'Enter Your Price');
    $currency_symbol = get_option('nameyourprice_currency_symbol', '$');

    echo '<style>
        .dynamic-input {
            width: auto;
            min-width: 100px; /* Adjust the minimum width of the input box as desired */
            max-width: 100%; /* Ensures the input box does not exceed the available width */
        }
    </style>';

    echo '<input type="text" name="nameyourprice_custom_text" value="' . esc_attr($custom_text) . '" class="dynamic-input" oninput="adjustInputWidth(this)" />';
    echo '<script>
        function adjustInputWidth(input) {
            input.style.width = "auto"; // Resets the width to auto before calculating the new width

            var minWidth = 100; // Adjust the minimum width of the input box as desired
            var emptySpace = 2; // Adjust the value to determine the empty space to the right of the text

            var scrollWidth = input.scrollWidth;
            var clientWidth = input.clientWidth;
            var newWidth = Math.max(minWidth, scrollWidth + emptySpace);

            input.style.width = newWidth + "px";
        }
    </script>';
}


// Callback function for the custom placeholder text
function nameyourprice_custom_placeholder_text_callback() {
    $custom_placeholder = get_option('nameyourprice_custom_placeholder_text', 'Enter your price here');

    echo '<style>
        .dynamic-input {
            width: auto;
            min-width: 100px; /* Adjust the minimum width of the input box as desired */
            max-width: 100%; /* Ensures the input box does not exceed the available width */
        }
    </style>';

    echo '<input type="text" name="nameyourprice_custom_placeholder_text" value="' . esc_attr($custom_placeholder) . '" class="dynamic-input" oninput="adjustInputWidth(this)" />';
    echo '<script>
        function adjustInputWidth(input) {
            input.style.width = "auto"; // Resets the width to auto before calculating the new width

            var minWidth = 100; // Adjust the minimum width of the input box as desired
            var emptySpace = 2; // Adjust the value to determine the empty space to the right of the text

            var scrollWidth = input.scrollWidth;
            var clientWidth = input.clientWidth;
            var newWidth = Math.max(minWidth, scrollWidth + emptySpace);

            input.style.width = newWidth + "px";
        }
    </script>';
}



/*******************************************************************
 * Add a custom price field to the product page for items in the selected category
 ********************************************************************/

// Add custom price field
function add_custom_price_field() {
    global $product;

    $selected_category = get_option('nameyourprice_selected_category', '');
    $min_price = get_option('nameyourprice_min_price', '0');
    $max_price = get_option('nameyourprice_max_price', '9999');
    $custom_text = get_option('nameyourprice_custom_text', 'Enter Your Price');
    $currency_symbol = get_option('nameyourprice_currency_symbol', '$'); // Get the currency symbol option
    $custom_placeholder = get_option('nameyourprice_custom_placeholder_text', 'Enter your price here');

    if (empty($selected_category)) {
        return; // Exit early if no category is selected
    }

    if (has_term($selected_category, 'product_cat', $product->get_id())) {
        echo '<div class="custom-price" style="display: flex; align-items: center;">';
        echo '<label for="custom_price" style="margin-right: 10px;">' . esc_html($custom_text) . ' (' . esc_html($currency_symbol) . ')</label>';
        echo '<div class="nyp-input-wrapper" style="padding-bottom: 10px; flex: 1;">'; // Updated CSS

        $max_price_parts = explode('.', $max_price);
        $max_int_length = strlen($max_price_parts[0]);
        $max_decimal_length = isset($max_price_parts[1]) ? strlen($max_price_parts[1]) : 0;

        echo '<input type="text" name="custom_price" id="custom_price" class="input-text" placeholder="'. esc_attr__($custom_placeholder) .'" value="'. esc_attr($min_price) .'" data-type="currency" data-step="0.01" data-min="'. esc_attr($min_price) .'" data-max="'. esc_attr($max_price) .'" onfocus="if (this.value === \''. esc_js($min_price) .'\') { this.value = \'\'; }" onblur="if (this.value === \'\') { this.value = \''. esc_js($min_price) .'\'; }" oninput="this.value = this.value.replace(/[^0-9.]/g, \'\');
            var parts = this.value.split(\'.\');
            if (parts[0].length > '. $max_int_length .') {
                parts[0] = parts[0].substr(0, '. $max_int_length .');
            }
            if (parts.length === 2 && parts[1].length > '. $max_decimal_length .') {
                parts[1] = parts[1].substr(0, '. $max_decimal_length .');
            }
            this.value = parts.join(\'.\');
            if (parseFloat(this.value) > '. $max_price .') {
                this.value = this.value.substr(0, '. ($max_int_length + $max_decimal_length + 1) .');
            }" style="width: 100%;" />';
        echo '</div>';
        echo '</div>';
    }
}

// Modify the displayed price of the cart item based on the custom price set for the product.
add_filter('woocommerce_cart_item_price', 'custom_price_display_cart_item_price', 10, 3);
function custom_price_display_cart_item_price($product_price, $cart_item, $cart_item_key) {
    if (isset($cart_item['custom_price'])) {
        $product_price = wc_price($cart_item['custom_price']);
    }
    return $product_price;
}

// Save the custom price value as meta data when a product is added to the cart
function save_custom_price($cart_item_data, $product_id) {
    if (isset($_POST['custom_price']) && !empty($_POST['custom_price'])) {
        $cart_item_data['custom_price'] = (float) wc_clean($_POST['custom_price']);
    }
    return $cart_item_data;
}

add_filter('woocommerce_add_cart_item_data', 'save_custom_price', 10, 2);

// Set the price of the cart item SUBTOTAL to the custom price value
function set_custom_price($cart_object) {
    foreach ($cart_object->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['custom_price'])) {
            $cart_item['data']->set_price($cart_item['custom_price']);
        }
    }
}

add_action('woocommerce_before_calculate_totals', 'set_custom_price');

// Add custom price validation - makes sure the price is between Min and Max value
function validate_custom_price($passed, $product_id, $quantity) {
    // Retrieve the selected category, minimum price, and maximum price from the options
    $selected_category = get_option('nameyourprice_selected_category', '');
    $min_price = (float) get_option('nameyourprice_min_price', '0');
    $max_price = (float) get_option('nameyourprice_max_price', '9999');

    if (has_term($selected_category, 'product_cat', $product_id) && isset($_POST['custom_price'])) {
        // Retrieve the custom price entered by the user
        $custom_price = (float) $_POST['custom_price'];

        // Check if the custom price is outside the allowed range
        if ($custom_price < $min_price || $custom_price > $max_price) {
            // Display an error message to the user if the price is outside the allowed ranges
            wc_add_notice(
                sprintf(__('Please enter a price between %s and %s.', 'woocommerce'), wc_price($min_price), wc_price($max_price)),
                'error'
            );

            // Redirect back to the product page
            wp_safe_redirect(get_permalink($product_id));
            exit;

            // Set the validation result to false
            $passed = false;
        }
    }

    // Return the validation result if within the allowed ranges
    return $passed;
}

// Hook the custom price validation function to the 'woocommerce_add_to_cart_validation' filter
add_filter('woocommerce_add_to_cart_validation', 'validate_custom_price', 10, 3);


// Display the custom price field on the product page
add_action('woocommerce_before_add_to_cart_button', 'add_custom_price_field');


/*******************************************************************
 * END Name Your Price field to the product page for items in the selected category
 ********************************************************************/
