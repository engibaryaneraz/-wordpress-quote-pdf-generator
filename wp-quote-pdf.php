<?php
/**
 * Plugin Name: WP Quote to PDF
 * Description: Get a Quote form → generate PDF → send email → save in admin.
 * Version: 1.0.0
 * Author: Eraz
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Load Composer autoload (Dompdf and other libraries)
 */
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

/**
 * Register custom post type for quotes
 */
add_action('init', function () {
    $labels = [
        'name'               => 'Quotes',
        'singular_name'      => 'Quote',
        'menu_name'          => 'Quotes',
        'name_admin_bar'     => 'Quote',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Quote',
        'new_item'           => 'New Quote',
        'edit_item'          => 'Edit Quote',
        'view_item'          => 'View Quote',
        'all_items'          => 'All Quotes',
        'search_items'       => 'Search Quotes',
        'not_found'          => 'No quotes found.',
        'not_found_in_trash' => 'No quotes found in Trash.',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'supports'           => ['title', 'custom-fields'],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'menu_position'      => 25,
        'menu_icon'          => 'dashicons-media-document',
    ];

    register_post_type('quote_request', $args);
});

/**
 * Shortcode to display "Get a Quote" form
 * Usage: [quote_form]
 */
add_shortcode('quote_form', function () {
    $nonce = wp_create_nonce('quote_form_nonce');

    ob_start(); ?>

    <style>
        .quote-form-wrapper {
            max-width: 620px;
            margin: 40px auto;
            padding: 35px;
            background: #fafafa;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Helvetica Neue", Arial, sans-serif;
        }

        .quote-form-wrapper h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 600;
            color: #1c1c1e;
            letter-spacing: -0.5px;
        }

        .quote-field {
            margin-bottom: 22px;
        }

        .quote-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #3a3a3c;
            font-size: 15px;
        }

        .quote-field input[type="text"],
        .quote-field input[type="email"],
        .quote-field input[type="date"],
        .quote-field select,
        .quote-field textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d2d2d7;
            border-radius: 12px;
            background: #ffffff;
            font-size: 16px;
            color: #1c1c1e;
            transition: all 0.25s ease;
            appearance: none;
        }

        .quote-field input:focus,
        .quote-field select:focus,
        .quote-field textarea:focus {
            border-color: #007aff;
            box-shadow: 0 0 0 4px rgba(0,122,255,0.15);
            outline: none;
        }

        .quote-options {
            display: flex;
            gap: 18px;
            margin-top: 8px;
        }

        .quote-options label {
            font-weight: 500;
            color: #3a3a3c;
            cursor: pointer;
        }

        .quote-submit-btn {
            width: 100%;
            padding: 15px;
            background: #007aff;
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.25s ease, transform 0.15s ease;
        }

        .quote-submit-btn:hover {
            background: #0066d6;
        }

        .quote-submit-btn:active {
            transform: scale(0.98);
        }

        /* Checkbox & radio Apple style */
        input[type="checkbox"],
        input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: #007aff;
            cursor: pointer;
        }
    </style>

    <div class="quote-form-wrapper">
        <h2>Get a Quote</h2>

        <form method="post">
            <input type="hidden" name="quote_form_nonce" value="<?php echo esc_attr($nonce); ?>">

            <div class="quote-field">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="quote-field">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="quote-field">
                <label>Service</label>
                <select name="service">
                    <option value="Website">Website</option>
                    <option value="SEO">SEO</option>
                    <option value="Marketing">Marketing</option>
                </select>
            </div>

            <div class="quote-field">
                <label>Budget</label>
                <div class="quote-options">
                    <label><input type="radio" name="budget" value="Low"> Low</label>
                    <label><input type="radio" name="budget" value="Medium"> Medium</label>
                    <label><input type="radio" name="budget" value="High"> High</label>
                </div>
            </div>

            <div class="quote-field">
                <label><input type="checkbox" name="urgent" value="Yes"> Urgent?</label>
            </div>

            <div class="quote-field">
                <label>Start Date</label>
                <input type="date" name="start_date">
            </div>

            <div class="quote-field">
                <label>Project Details</label>
                <textarea name="details" rows="4"></textarea>
            </div>

            <button type="submit" name="send_quote" class="quote-submit-btn">Get a Quote</button>
        </form>
    </div>

    <?php
    return ob_get_clean();
});



/**
 * Handle form submission: save data, generate PDF, send email
 */
add_action('init', function () {
    if (!isset($_POST['send_quote'])) {
        return;
    }

    // Check nonce
    if (!isset($_POST['quote_form_nonce']) || !wp_verify_nonce($_POST['quote_form_nonce'], 'quote_form_nonce')) {
        return;
    }

    // Collect and sanitize form data
    $data = [];

    foreach ($_POST as $key => $value) {
        if (in_array($key, ['send_quote', 'quote_form_nonce'], true)) {
            continue;
        }

        // Handle checkbox (if not set)
        if ($key === 'urgent') {
            $data[$key] = $value === 'Yes' ? 'Yes' : 'No';
            continue;
        }

        $data[$key] = is_array($value)
            ? array_map('sanitize_text_field', $value)
            : sanitize_text_field($value);
    }

    // Create admin post (CPT)
    $post_title = !empty($data['full_name']) ? 'Quote from ' . $data['full_name'] : 'Quote Request';

    $post_id = wp_insert_post([
        'post_type'   => 'quote_request',
        'post_title'  => $post_title,
        'post_status' => 'publish',
    ]);

    if (is_wp_error($post_id)) {
        return;
    }

    // Save meta
    foreach ($data as $k => $v) {
        update_post_meta($post_id, $k, $v);
    }

    // Prepare HTML for PDF
    $template_file = __DIR__ . '/templates/pdf-template.php';

    if (!file_exists($template_file)) {
        return;
    }

    // Make $data available inside template
    ob_start();
    $quote_data = $data; // pass as $quote_data to avoid conflicts
    $quote_id   = $post_id;
    include $template_file;
    $html = ob_get_clean();

    // Generate PDF
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdf_output = $dompdf->output();

    // Save PDF to uploads
    $upload_dir = wp_upload_dir();
    $pdf_dir    = trailingslashit($upload_dir['path']);
    $pdf_name   = 'quote-' . $post_id . '.pdf';
    $pdf_path   = $pdf_dir . $pdf_name;

    file_put_contents($pdf_path, $pdf_output);

    // Send email with PDF attachment
    $admin_email = get_option('admin_email');
    $to          = !empty($data['email']) ? $data['email'] : $admin_email;

    $subject = 'Your Quote Request';
    $message = "Thank you for your quote request.\nThe PDF with your details is attached.";

    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    wp_mail($to, $subject, $message, $headers, [$pdf_path]);

    // Redirect back with success flag
    $redirect_url = add_query_arg('quote_sent', '1', wp_get_referer());
    wp_safe_redirect($redirect_url);
    exit;
});
