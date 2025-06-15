<?php

/**
 * 信息网格区块类
 *
 * @package     block-canvas Child
 * @subpackage  Blocks
 * @author      JoshuaWang2019
 * @version     1.0.0
 * @since       2025-06-15
 */

namespace BlockCanvasChild\Blocks;

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class Messages_Grid
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->register();
    }

    /**
     * 注册区块
     */
    private function register()
    {
        register_block_type('block-canvas-child/messages-grid', array(
            'render_callback' => array($this, 'render')
        ));
    }

    /**
     * 从URL中提取分类slug
     *
     * @return array 包含category_slug和page的数组
     */
    private function parse_url_params()
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        $parsed_url = parse_url($request_uri);
        $path = trim($parsed_url['path'], '/');

        // 匹配 messages/category-slug 或 messages/category-slug/page/2
        if (preg_match('#^messages/([^/]+)(?:/page/(\d+))?/?$#', $path, $matches)) {
            return array(
                'category_slug' => $matches[1],
                'page' => isset($matches[2]) ? intval($matches[2]) : 1
            );
        }

        return array(
            'category_slug' => null,
            'page' => 1
        );
    }

    /**
     * 渲染区块
     *
     * @param array $attributes 区块属性
     * @return string 渲染的HTML
     */
    public function render($attributes)
    {
        ob_start();

        $url_params = $this->parse_url_params();
        $category_slug = $url_params['category_slug'];
        $paged = $url_params['page'];

        if (!$category_slug) {
            echo '<p class="no-messages">未指定信息分类</p>';
            return ob_get_clean();
        }

        // 获取分类信息
        $term = get_term_by('slug', $category_slug, 'message_taxonomy');
        if (!$term) {
            echo '<p class="no-messages">分类不存在: ' . esc_html($category_slug) . '</p>';
            return ob_get_clean();
        }

        // 更新页面标题
        echo '<script>document.title = "' . esc_js($term->name) . ' - 信息列表";</script>';
        echo '<h2 class="category-title">' . esc_html($term->name) . '</h2>';

        $query = $this->get_messages_query($term->term_id, $paged);

        if ($query->have_posts()) {
            echo '<div class="messages-grid">';
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_message_item();
            }
            echo '</div>';

            // 分页导航
            $this->render_pagination($query, $category_slug, $paged);
        } else {
            echo '<p class="no-messages">该分类下暂无信息</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * 获取信息查询
     *
     * @param int $term_id 分类ID
     * @param int $paged 页码
     * @return \WP_Query
     */
    private function get_messages_query($term_id, $paged)
    {
        $meta_query = array(
            array(
                'key' => 'message_category',
                'value' => $term_id,
                'compare' => 'LIKE'
            )
        );

        return new \WP_Query(array(
            'post_type' => 'message',
            'posts_per_page' => 20,
            'paged' => $paged,
            'post_status' => 'publish',
            'meta_query' => $meta_query,
            'meta_key' => 'message_order',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ));
    }

    /**
     * 渲染单个信息项目
     */
    private function render_message_item()
    {
        $title = get_field('message_title');
        $author = get_field('message_author');
        $feature_image = get_field('message_feature_image');
        $order = get_field('message_order');

        // 如果没有自定义标题，使用文章标题
        $display_title = !empty($title) ? $title : get_the_title();

        // 默认图片路径
        $default_image = get_stylesheet_directory_uri() . '/assets/images/default_message_feature.png';
        $image_url = $feature_image ? $feature_image['url'] : $default_image;
        $image_alt = $feature_image ? $feature_image['alt'] : '默认信息图片';

?>
        <div class="message-item">
            <div class="message-card">
                <div class="message-image-wrapper">
                    <img src="<?php echo esc_url($image_url); ?>"
                        alt="<?php echo esc_attr($image_alt); ?>"
                        class="message-feature-image"
                        loading="lazy">
                </div>

                <div class="message-content">
                    <h3 class="message-title"><?php echo esc_html($display_title); ?></h3>

                    <?php if ($author) : ?>
                        <p class="message-author">作者：<?php echo esc_html($author); ?></p>
                    <?php endif; ?>

                    <?php if ($order) : ?>
                        <span class="message-order">序号：<?php echo esc_html($order); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<?php
    }

    /**
     * 渲染分页导航
     *
     * @param \WP_Query $query 查询对象
     * @param string $category_slug 分类slug
     * @param int $current_page 当前页码
     */
    private function render_pagination($query, $category_slug, $current_page)
    {
        $total_pages = $query->max_num_pages;

        if ($total_pages <= 1) {
            return;
        }

        echo '<nav class="messages-pagination" aria-label="信息列表分页导航">';
        echo '<ul class="pagination-list">';

        // 上一页
        if ($current_page > 1) {
            $prev_url = $current_page == 2
                ? home_url('/messages/' . $category_slug . '/')
                : home_url('/messages/' . $category_slug . '/page/' . ($current_page - 1) . '/');
            echo '<li><a href="' . esc_url($prev_url) . '" class="pagination-link prev">« 上一页</a></li>';
        }

        // 页码链接
        for ($i = 1; $i <= $total_pages; $i++) {
            $page_url = $i == 1
                ? home_url('/messages/' . $category_slug . '/')
                : home_url('/messages/' . $category_slug . '/page/' . $i . '/');

            $class = $i == $current_page ? 'pagination-link current' : 'pagination-link';
            echo '<li><a href="' . esc_url($page_url) . '" class="' . $class . '">' . $i . '</a></li>';
        }

        // 下一页
        if ($current_page < $total_pages) {
            $next_url = home_url('/messages/' . $category_slug . '/page/' . ($current_page + 1) . '/');
            echo '<li><a href="' . esc_url($next_url) . '" class="pagination-link next">下一页 »</a></li>';
        }

        echo '</ul>';
        echo '</nav>';
    }
}
