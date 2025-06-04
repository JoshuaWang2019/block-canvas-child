<?php

/**
 * 电子书网格区块类
 *
 * @package     block-canvas Child
 * @subpackage  Blocks
 * @author      JoshuaWang2019
 * @version     1.0.0
 * @since       2025-06-04
 */

namespace BlockCanvasChild\Blocks;

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class Ebooks_Grid
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
        register_block_type('block-canvas-child/ebooks-grid', array(
            'render_callback' => array($this, 'render')
        ));
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

        $query = $this->get_ebooks_query();

        if ($query->have_posts()) {
            echo '<div class="ebooks-grid">';
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_ebook_item();
            }
            echo '</div>';
        } else {
            echo '<p class="no-books">暂无电子书</p>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * 获取电子书查询
     *
     * @return \WP_Query
     */
    private function get_ebooks_query()
    {
        return new \WP_Query(array(
            'post_type' => 'ebook',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
    }

    /**
     * 渲染单个电子书项目
     */
    private function render_ebook_item()
    {
        $title = get_the_title();
        $book_name = get_field('book_name');
        $author = get_field('author');
        $cover_image = get_field('cover_image');
        $book_file = get_field('book_file');
        $book_description = get_field('book_description');

        $display_name = !empty($book_name) ? $book_name : $title;

        // 处理描述文字，限制为100字
        $truncated_description = '';
        if (!empty($book_description)) {
            $truncated_description = mb_strlen($book_description) > 100
                ? mb_substr($book_description, 0, 100) . '...'
                : $book_description;
        }
?>
        <div class="ebook-item">
            <?php if ($book_file) : ?>
                <a href="<?php echo esc_url($book_file['url']); ?>"
                    download
                    class="ebook-card-link"
                    aria-label="下载《<?php echo esc_attr($display_name); ?>》">
                <?php endif; ?>

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

                <?php if ($truncated_description) : ?>
                    <p class="ebook-description"><?php echo esc_html($truncated_description); ?></p>
                <?php endif; ?>

                <?php if ($book_file) : ?>
                </a>
            <?php endif; ?>
        </div>
<?php
    }
}
