<?php
/**
 * PDF template for Quote Request
 *
 * Available variables:
 * - $quote_data (array) — sanitized form data
 * - $quote_id (int)     — post ID of quote_request
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quote Request #<?php echo (int) $quote_id; ?></title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 15px;
        }
        .field {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
        }
    </style>
</head>
<body>
<h1>Quote Request #<?php echo (int) $quote_id; ?></h1>

<div class="section">
    <?php foreach ($quote_data as $key => $value): ?>
        <div class="field">
                <span class="label">
                    <?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?>:
                </span>
            <span class="value">
                    <?php
                    if (is_array($value)) {
                        echo esc_html(implode(', ', $value));
                    } else {
                        echo esc_html($value);
                    }
                    ?>
                </span>
        </div>
    <?php endforeach; ?>
</div>

<div class="section">
    <p>Generated at: <?php echo esc_html(date('Y-m-d H:i')); ?></p>
</div>
</body>
</html>
