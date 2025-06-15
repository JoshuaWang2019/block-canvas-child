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
        // 添加钩子来处理 404 情况
        add_action('template_redirect', array($this, 'handle_invalid_category'));
    }

    /**
     * 处理无效分类的情况
     */
    public function handle_invalid_category()
    {
        // 只在 messages 页面处理
        if (!$this->is_messages_page()) {
            return;
        }

        $url_params = $this->parse_url_params();
        $category_slug = $url_params['category_slug'];

        // 如果没有分类 slug，重定向到主页或分类列表页
        if (!$category_slug) {
            wp_redirect(home_url('/'));
            exit;
        }

        // 检查分类是否存在
        $term = get_term_by('slug', $category_slug, 'message_taxonomy');
        if (!$term || is_wp_error($term)) {
            // 设置 404 状态
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            return;
        }
    }

    /**
     * 检查是否是 messages 页面
     */
    private function is_messages_page()
    {
        $request_uri = $_SERVER['REQUEST_URI'];
        $parsed_url = parse_url($request_uri);
        $path = trim($parsed_url['path'], '/');

        return preg_match('#^messages(/|$)#', $path);
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
     * 获取分类下的信息总数
     *
     * @param int $term_id 分类术语ID
     * @return int 信息总数
     */
    private function get_messages_count($term_id)
    {
        $count_args = array(
            'post_type' => 'message',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'message_taxonomy',
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                ),
            ),
            'fields' => 'ids'
        );

        $count_query = new \WP_Query($count_args);
        return $count_query->found_posts;
    }

    /**
     * 渲染区块
     *
     * @param array $attributes 区块属性
     * @return string 渲染的HTML
     */
    public function render($attributes)
    {
        // 如果是 404 页面，不渲染内容
        if (is_404()) {
            return '';
        }

        ob_start();

        $url_params = $this->parse_url_params();
        $category_slug = $url_params['category_slug'];
        $paged = $url_params['page'];

        if (!$category_slug) {
            // 显示分类列表或重定向提示
            $this->render_category_redirect();
            return ob_get_clean();
        }

        // 获取分类信息
        $term = get_term_by('slug', $category_slug, 'message_taxonomy');
        if (!$term || is_wp_error($term)) {
            // 显示分类不存在的友好提示
            $this->render_category_not_found($category_slug);
            return ob_get_clean();
        }

        // 获取该分类下的信息总数
        $total_messages = $this->get_messages_count($term->term_id);

        // 更新页面标题
        echo '<script>document.title = "' . esc_js($term->name) . ' - 甘泉网站";</script>';

        // 显示分类名称和信息总数
        echo '<div class="category-header">';
        echo '<h1 class="category-title">' . esc_html($term->name) . '</h1>';
        echo '<div class="messages-count">共 <span class="count-number">' . $total_messages . '</span> 篇信息</div>';
        echo '</div>';

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
     * 渲染分类重定向提示
     */
    private function render_category_redirect()
    {
        echo '<div class="category-redirect-notice">';
        echo '<div class="notice-icon">ℹ️</div>';
        echo '<h2 class="notice-title">请选择信息分类</h2>';
        echo '<p class="notice-message">您访问的链接缺少分类信息，请选择您要查看的信息分类。</p>';

        // 获取所有可用的分类
        $terms = get_terms(array(
            'taxonomy' => 'message_taxonomy',
            'hide_empty' => true,
        ));

        if (!empty($terms) && !is_wp_error($terms)) {
            echo '<div class="category-list">';
            echo '<h3>可用分类：</h3>';
            echo '<ul class="category-links">';
            foreach ($terms as $term) {
                $term_url = home_url('/messages/' . $term->slug . '/');
                echo '<li><a href="' . esc_url($term_url) . '" class="category-link">' . esc_html($term->name) . '</a></li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        echo '<div class="back-to-home">';
        echo '<a href="' . esc_url(home_url('/')) . '" class="back-link">← 返回首页</a>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * 渲染分类不存在的提示
     */
    private function render_category_not_found($category_slug)
    {
        echo '<div class="category-not-found">';
        echo '<div class="notice-icon">❌</div>';
        echo '<h2 class="notice-title">分类不存在</h2>';
        echo '<p class="notice-message">抱歉，分类 "<strong>' . esc_html($category_slug) . '</strong>" 不存在或已被删除。</p>';

        // 获取所有可用的分类
        $terms = get_terms(array(
            'taxonomy' => 'message_taxonomy',
            'hide_empty' => true,
        ));

        if (!empty($terms) && !is_wp_error($terms)) {
            echo '<div class="category-list">';
            echo '<h3>您可以查看以下分类：</h3>';
            echo '<ul class="category-links">';
            foreach ($terms as $term) {
                $term_url = home_url('/messages/' . $term->slug . '/');
                echo '<li><a href="' . esc_url($term_url) . '" class="category-link">' . esc_html($term->name) . '</a></li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        echo '<div class="back-to-home">';
        echo '<a href="' . esc_url(home_url('/')) . '" class="back-link">← 返回首页</a>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * 获取信息查询
     *
     * @param int $term_id 分类术语ID
     * @param int $paged 页码
     * @return \WP_Query 查询对象
     */
    private function get_messages_query($term_id, $paged)
    {
        $args = array(
            'post_type' => 'message',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'paged' => $paged,
            'tax_query' => array(
                array(
                    'taxonomy' => 'message_taxonomy',
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                ),
            ),
            'meta_key' => 'message_order',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        );

        return new \WP_Query($args);
    }

    /**
     * 渲染单个信息项目
     */
    private function render_message_item()
    {
        $title = get_field('message_title');
        $author = get_field('message_author');
        $feature_image = get_field('message_feature_image');

        // 如果没有自定义标题，使用文章标题
        $display_title = !empty($title) ? $title : get_the_title();

        // 默认图片路径
        $default_image = get_stylesheet_directory_uri() . '/assets/images/default_message_feature.png';
        $image_url = $feature_image ? $feature_image['url'] : $default_image;
        $image_alt = $feature_image ? $feature_image['alt'] : '默认信息图片';

?>
        <div class="message-item">
            <a href="<?php echo esc_url(get_permalink()); ?>" class="message-link">
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
                    </div>
                </div>
            </a>
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
