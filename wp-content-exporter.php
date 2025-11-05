<?php
/**
 * Plugin Name: WP Content Exporter
 * Description: Lists posts from selected post types with ID, Title, URL, and Status; includes paging and CSV export.
 * Version: 0.0.3
 * Author: Amr Shah
 */

if (!defined('ABSPATH')) exit;

// ─────────────────────────────
// Add Admin Page
// ─────────────────────────────
add_action('admin_menu', function() {
    add_management_page(
        'Content Exporter',
        'Content Exporter',
        'manage_options',
        'wp-content-exporter',
        'wp_content_exporter_render_page'
    );
});

// ─────────────────────────────
// Render Main Admin Page
// ─────────────────────────────
function wp_content_exporter_render_page() { ?>
    <div class="wrap" style="background:#fff;padding:20px;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <h1 style="margin-bottom:15px;">SAM Content Exporter</h1>
        
        <p>Select which post types to include and click <strong>"Generate List"</strong>.</p>

        <form id="wp-content-exporter-form" style="margin-bottom:20px;">
            <div class="post-type-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:8px;">
                <?php
                $post_types = get_post_types(['public' => true], 'objects');
                foreach ($post_types as $type) {
                    echo '<label style="display:flex;align-items:center;gap:6px;background:#f9f9f9;border:1px solid #ddd;padding:6px 10px;border-radius:6px;">
                            <input type="checkbox" name="post_types[]" value="' . esc_attr($type->name) . '"> 
                            <span>' . esc_html($type->labels->singular_name) . ' <small>(' . esc_html($type->name) . ')</small></span>
                          </label>';
                }
                ?>
            </div>
            <br>
            <button type="submit" class="button button-primary">Generate List</button>
        </form>

        <div id="wp-content-exporter-results" style="margin-top:30px;"></div>
    </div>

    <script type="text/javascript">
jQuery(document).ready(function($){
    let currentPage = 1;
    let currentTypes = [];

    function loadResults(page = 1, exportAll = false) {
        currentTypes = [];
        $('input[name="post_types[]"]:checked').each(function() {
            currentTypes.push($(this).val());
        });

        if (currentTypes.length === 0) {
            alert('Please select at least one post type.');
            return;
        }

        $('#wp-content-exporter-results').html('<p><em>Loading...</em></p>');

        $.post(ajaxurl, {
            action: 'wp_content_exporter_get_data',
            post_types: currentTypes,
            paged: page,
            export_all: exportAll ? 1 : 0
        }, function(response) {
            if (exportAll) {
                // Create CSV download
                const blob = new Blob([response], { type: 'text/csv' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'content-export.csv';
                link.click();
                $('#wp-content-exporter-results').html('');
            } else {
                $('#wp-content-exporter-results').html(response);
            }
        });
    }

    $('#wp-content-exporter-form').on('submit', function(e){
        e.preventDefault();
        currentPage = 1;
        loadResults();
    });

    $(document).on('click', '.wpce-page', function(e){
        e.preventDefault();
        currentPage = $(this).data('page');
        loadResults(currentPage);
    });

    $(document).on('click', '#export-csv-all', function(e){
        e.preventDefault();
        loadResults(currentPage, true);
    });
});
</script>

<?php }

// ─────────────────────────────
// AJAX Handler for Data Listing / Export
// ─────────────────────────────

// Helper function: Always return clean, expected public permalink
function get_pretty_permalinks($id)
{
    $url = get_permalink($id);

    // If permalink looks normal (no query params), return it
    if ($url && strpos($url, '?') === false) {
        return $url;
    }

    $post = get_post($id);
    if (!$post) {
        return '';
    }

    $slug = sanitize_title($post->post_name);
    $type = get_post_type_object($post->post_type);

    // Handle empty or missing slug (common in drafts)
    if (empty($slug)) {
        // fallback: generate slug from post title
        $slug = sanitize_title($post->post_title ?: 'untitled');
    }

    // Determine base slug (rewrite or fallback)
    if ($type && !empty($type->rewrite['slug'])) {
        $base_slug = trim($type->rewrite['slug'], '/');
    } else {
        $base_slug = $post->post_type;
    }

    // Construct pretty URL safely
    $url = trailingslashit(home_url("/{$base_slug}/{$slug}"));

    return esc_url($url);
}

add_action('wp_ajax_wp_content_exporter_get_data', function() {
    if (!current_user_can('manage_options')) wp_die('Unauthorized');

    $post_types = isset($_POST['post_types']) ? (array) $_POST['post_types'] : [];
    $paged      = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $export_all = !empty($_POST['export_all']);
    $per_page   = 50;

    if (empty($post_types)) wp_die('No post types selected.');

    // When exporting all, get everything (ignore pagination)
    $args = [
        'post_type'      => $post_types,
        'post_status'    => ['publish','draft','pending','private'],
        'posts_per_page' => $export_all ? -1 : $per_page,
        'paged'          => $export_all ? 1 : $paged,
    ];

    $query = new WP_Query($args);

    // Export ALL as CSV
    if ($export_all) {
        $csv = "ID,Post Type,Title,URL,Status\n";
        while ($query->have_posts()) {
            $query->the_post();
            $id     = get_the_ID();
            $type   = get_post_type();
            $title  = str_replace('"','""',get_the_title());
            $url    = get_pretty_permalinks($id);
            $status = get_post_status();
            $csv .= "\"$id\",\"$type\",\"$title\",\"$url\",\"$status\"\n";
        }
        wp_reset_postdata();
        echo $csv;
        wp_die();
    }

    // Otherwise: HTML Table Output
    if ($query->have_posts()) {
        echo '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">';
        echo '<h2 style="margin:0;">Results</h2>';
        echo '<button id="export-csv-all" class="button">Export All as CSV</button>';
        echo '</div>';

        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>
                <th style="width:40px;"><input type="checkbox" id="select-all"></th>
                <th width="60">ID</th>
                <th width="120">Type</th>
                <th>Title</th>
                <th>URL</th>
                <th width="100">Status</th>
              </tr></thead><tbody>';

        while ($query->have_posts()) {
            $query->the_post();
            $id     = get_the_ID();
            $type   = get_post_type();
            $title  = get_the_title();
            $url    = get_pretty_permalinks($id);
            $status = get_post_status();
            $edit   = get_edit_post_link($id);

            echo "<tr>
                    <td><input type='checkbox' class='row-select' data-id='{$id}'></td>
                    <td><a href='{$edit}' target='_blank'>{$id}</a></td>
                    <td>{$type}</td>
                    <td>{$title}</td>
                    <td><a href='{$url}' target='_blank'>{$url}</a></td>
                    <td>{$status}</td>
                  </tr>";
        }

        echo '</tbody></table>';

        // Pagination
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) {
            echo '<div class="tablenav" style="margin-top:10px;"><div class="tablenav-pages">';
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $paged) ? 'style="font-weight:bold;"' : '';
                echo "<a href='#' class='wpce-page' data-page='{$i}' {$active}>[{$i}]</a> ";
            }
            echo '</div></div>';
        }

        // JS for select-all behavior
        echo "<script>
        jQuery(document).ready(function($){
            $('#select-all').on('click', function(){
                $('.row-select').prop('checked', this.checked);
            });
        });
        </script>";

    } else {
        echo '<p>No posts found for the selected post types.</p>';
    }

    wp_reset_postdata();
    wp_die();
});