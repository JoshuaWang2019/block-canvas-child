<?php

/**
 * Block Canvas Child Theme functions and definitions
 *
 * @package     block-canvas Child
 * @author      JoshuaWang2019
 * @version     1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 注册并加载主题样式和脚本
 */
function child_theme_enqueue_styles()
{
    $theme = wp_get_theme();

    // 加载父主题样式
    wp_enqueue_style(
        'block-canvas-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme('block-canvas')->get('Version')
    );

    // 加载子主题样式
    wp_enqueue_style(
        'block-canvas-child-style',
        get_stylesheet_uri(),
        array('block-canvas-style'),
        $theme->get('Version')
    );

    // 确保jQuery加载
    // wp_enqueue_script('jquery');
    // // 添加自定义JavaScript
    // wp_enqueue_script(
    //     'twenty-twenty-four-child-navigation',
    //     get_stylesheet_directory_uri() . '/js/navigation.js',
    //     array('jquery'),
    //     filemtime(get_stylesheet_directory() . '/js/navigation.js'),
    //     true
    // );

    // 加载电子书列表样式
    wp_enqueue_style(
        'ebooks-style',
        get_stylesheet_directory_uri() . '/assets/css/ebooks.css',
        array('block-canvas-child-style'),
        $theme->get('Version')
    );

    // 加载电子书搜索脚本
    wp_enqueue_script(
        'ebooks-search',
        get_stylesheet_directory_uri() . '/assets/js/ebooks-search.js',
        array('jquery'),
        $theme->get('Version'),
        true
    );

    // 添加必要的JavaScript变量
    wp_localize_script('ebooks-search', 'ebooksSearch', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ebooks_search_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'child_theme_enqueue_styles');

/**
 * 注册自定义区块
 */
function register_ebooks_blocks()
{
    if (!function_exists('register_block_type')) {
        return;
    }

    register_block_type('block-canvas-child/ebooks-grid', array(
        'render_callback' => function ($attributes) {
            ob_start();

            // 执行电子书查询
            $args = array(
                'post_type' => 'ebook',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            );

            $query = new WP_Query($args);

            if ($query->have_posts()) {
                echo '<div class="ebooks-grid">';
                while ($query->have_posts()) {
                    $query->the_post();
                    // 获取文章标题作为后备
                    $title = get_the_title();

                    // 获取ACF字段
                    $book_name = get_field('book_name');
                    $author = get_field('author');
                    $cover_image = get_field('cover_image');
                    $book_file = get_field('book_file');

                    // 如果ACF书名为空，使用文章标题
                    $display_name = !empty($book_name) ? $book_name : $title;
?>
                <div class="ebook-item">
                    <?php if ($cover_image) : ?>
                        <img src="<?php echo esc_url($cover_image['url']); ?>"
                            alt="<?php echo esc_attr($display_name); ?>"
                            class="ebook-cover"
                            loading="lazy">
                    <?php endif; ?>

                    <h3 class="ebook-title"><?php echo esc_html($display_name); ?></h3>
                    <?php if ($author) : ?>
                        <p class="ebook-author"><?php echo esc_html($author); ?></p>
                    <?php endif; ?>

                    <?php if ($book_file) : ?>
                        <a href="<?php echo esc_url($book_file['url']); ?>"
                            class="ebook-download"
                            download
                            aria-label="下载《<?php echo esc_attr($display_name); ?>》">
                            下载电子书</a>
                    <?php endif; ?>
                </div>
<?php
                }
                echo '</div>';
            } else {
                echo '<p class="no-books">暂无电子书</p>';
            }

            wp_reset_postdata();

            return ob_get_clean();
        }
    ));
}
add_action('init', 'register_ebooks_blocks');

/**
 * AJAX搜索处理函数
 */
function handle_ebooks_search()
{
    check_ajax_referer('ebooks_search_nonce', 'nonce');

    $search_term = sanitize_text_field($_POST['search']);

    $args = array(
        'post_type' => 'ebook',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    );

    if (!empty($search_term)) {
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key' => 'book_name',
                'value' => $search_term,
                'compare' => 'LIKE'
            ),
            array(
                'key' => 'author',
                'value' => $search_term,
                'compare' => 'LIKE'
            )
        );
    }

    $query = new WP_Query($args);
    $results = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $book_name = get_field('book_name');
            $author = get_field('author');
            $cover_image = get_field('cover_image');
            $book_file = get_field('book_file');

            // 如果ACF书名为空，使用文章标题
            $display_name = !empty($book_name) ? $book_name : get_the_title();

            $results[] = array(
                'id' => get_the_ID(),
                'book_name' => $display_name,
                'author' => $author,
                'cover_image' => $cover_image,
                'book_file' => $book_file
            );
        }
    }
    wp_reset_postdata();

    wp_send_json_success($results);
}
add_action('wp_ajax_ebooks_search', 'handle_ebooks_search');
add_action('wp_ajax_nopriv_ebooks_search', 'handle_ebooks_search');
