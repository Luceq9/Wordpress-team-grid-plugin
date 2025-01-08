<?php
/*
Plugin Name: Team grid plugin
Plugin URI: http://luckadomena.pl
Description: A custom plugin for WordPress to create a stylish and performant team view.
Version: 1.0
Author: Luceq
License: GPL2
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue the plugin styles
function my_custom_plugin_admin_enqueue_styles() {
    wp_enqueue_style('my-custom-plugin-style', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('admin_enqueue_scripts', 'my_custom_plugin_admin_enqueue_styles');

// Enqueue the plugin styles
function my_custom_plugin_enqueue_styles() {
    wp_enqueue_style('my-custom-plugin-style', plugin_dir_url(__FILE__) . 'shortcode-style.css');
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_enqueue_styles');


// Add a custom menu item to the admin dashboard
function my_custom_plugin_menu() {
    add_menu_page(
        'My Custom Plugin', // Page title
        'Custom Plugin',    // Menu title
        'manage_options',   // Capability
        'my-custom-plugin', // Menu slug
        'my_custom_plugin_page', // Function to display the page content
        'dashicons-admin-generic', // Icon
        6 // Position
    );
}
add_action('admin_menu', 'my_custom_plugin_menu');

// Display the content of the custom plugin page
function my_custom_plugin_page() {
    // Obsługa usuwania
    if (isset($_POST['delete'])) {
        $items = get_option('my_custom_plugin_items', array());
        if (!is_array($items)) {
            $items = array();
        }
        if (isset($_POST['delete_index'])) {
            $delete_index = intval($_POST['delete_index']);
            if (isset($items[$delete_index])) {
                unset($items[$delete_index]);
                $items = array_values($items); // Reindeksuj tablicę
                update_option('my_custom_plugin_items', $items);
            }
        }
    }

    // Obsługa dodawania i edycji
    if (isset($_POST['submit'])) {
        $items = get_option('my_custom_plugin_items', array());
        if (!is_array($items)) {
            $items = array();
        }
        $new_item = array(
            'firstname' => sanitize_text_field($_POST['my_custom_plugin_firstname']),
            'lastname' => sanitize_text_field($_POST['my_custom_plugin_lastname']),
            'profession' => sanitize_text_field($_POST['my_custom_plugin_profession']),
            'photo' => '' // Placeholder for photo URL
        );

        // Handle file upload
        if (isset($_FILES['my_custom_plugin_photo']) && !empty($_FILES['my_custom_plugin_photo']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            $uploaded = media_handle_upload('my_custom_plugin_photo', 0);
            if (is_wp_error($uploaded)) {
                echo "Error uploading file: " . $uploaded->get_error_message();
            } else {
                $new_item['photo'] = wp_get_attachment_url($uploaded);
            }
        }

        if (isset($_POST['edit_index'])) {
            $items[intval($_POST['edit_index'])] = $new_item;
        } else {
            $items[] = $new_item;
        }
        update_option('my_custom_plugin_items', $items);
    }

    // Get the current list of items from the database
    $items = get_option('my_custom_plugin_items', array());
    if (!is_array($items)) {
        $items = array();
    }
    ?>
    <div class="wrap">
        <h1>Team grid plugin</h1>
        <form method="post" action="" enctype="multipart/form-data">
            <label for="my_custom_plugin_firstname">Imię:</label>
            <input type="text" id="my_custom_plugin_firstname" name="my_custom_plugin_firstname" value="">
            <label for="my_custom_plugin_lastname">Nazwisko:</label>
            <input type="text" id="my_custom_plugin_lastname" name="my_custom_plugin_lastname" value="">
            <label for="my_custom_plugin_profession">Profesja:</label>
            <input type="text" id="my_custom_plugin_profession" name="my_custom_plugin_profession" value="">
            <label for="my_custom_plugin_photo">Zdjęcie:</label>
            <input type="file" id="my_custom_plugin_photo" name="my_custom_plugin_photo">
            <input type="submit" name="submit" value="Dodaj" class="button button-primary">
        </form>
        <h2>Lista dodanych</h2>
        <ul class="custom-plugin-list">
            <?php foreach ($items as $index => $item) : ?>
                <li>
                    <?php if(is_array($item)) : ?>
                        <div class="item-details">
                            <span><strong>Imię:</strong> <?php echo esc_html($item['firstname']); ?></span>
                            <span><strong>Nazwisko:</strong> <?php echo esc_html($item['lastname']); ?></span>
                            <span><strong>Profesja:</strong> <?php echo esc_html($item['profession']); ?></span>
                            <?php if (!empty($item['photo'])) : ?>
                                <img src="<?php echo esc_url($item['photo']); ?>" alt="<?php echo esc_attr($item['firstname'] . ' ' . $item['lastname']); ?>">
                                <span><?php echo basename($item['photo']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="item-actions">
                            <form method="post" action="" style="display:inline;">
                                <input type="hidden" name="delete_index" value="<?php echo $index; ?>">
                                <input type="submit" name="delete" value="Delete" class="button button-secondary">
                            </form>
                            <button class="button button-secondary" onclick="document.getElementById('edit-form-<?php echo $index; ?>').style.display='block'; this.style.display='none';">Edit</button>
                        </div>
                    <form id="edit-form-<?php echo $index; ?>" method="post" action="" enctype="multipart/form-data" style="display:none; margin-top: 10px;">
                        <input type="hidden" name="edit_index" value="<?php echo $index; ?>">
                        <label for="my_custom_plugin_firstname_<?php echo $index; ?>">Imię:</label>
                        <input type="text" id="my_custom_plugin_firstname_<?php echo $index; ?>" name="my_custom_plugin_firstname" value="<?php echo esc_attr($item['firstname']); ?>">
                        <label for="my_custom_plugin_lastname_<?php echo $index; ?>">Nazwisko:</label>
                        <input type="text" id="my_custom_plugin_lastname_<?php echo $index; ?>" name="my_custom_plugin_lastname" value="<?php echo esc_attr($item['lastname']); ?>">
                        <label for="my_custom_plugin_profession_<?php echo $index; ?>">Profesja:</label>
                        <input type="text" id="my_custom_plugin_profession_<?php echo $index; ?>" name="my_custom_plugin_profession" value="<?php echo esc_attr($item['profession']); ?>">
                        <label for="my_custom_plugin_photo_<?php echo $index; ?>">Zdjęcie:</label>
                        <input type="file" id="my_custom_plugin_photo_<?php echo $index; ?>" name="my_custom_plugin_photo">
                        <input type="submit" name="submit" value="Save" class="button button-primary">
                    </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}


function my_custom_plugin_shortcode($atts) {
    $items = get_option('my_custom_plugin_items', array());
    if (!is_array($items)) {
        $items = array();
    }

    ob_start();
    ?>
    <div class="my-custom-plugin__shortcode">
        <ul class="custom-plugin-list__shortcode">
            <?php foreach ($items as $item) : ?>
                <li>
                 <?php if(is_array($item)) : ?>
                <div class="item-details__shortcode">
                 <?php if (!empty($item['photo'])) : ?>
                <img src="<?php echo esc_url($item['photo']); ?>" alt="<?php echo esc_attr($item['firstname'] . ' ' . $item['lastname']); ?>">
                <?php endif; ?>
                <span><?php echo esc_html($item['firstname']); ?> <?php echo esc_html($item['lastname']); ?></span>
                <span><?php echo esc_html($item['profession']); ?></span>
                 </div>
            <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('my_custom_plugin', 'my_custom_plugin_shortcode');
?>
