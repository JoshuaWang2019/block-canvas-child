<?php

/**
 * 电子书网格区块渲染文件
 * 
 * @package     block-canvas Child
 * @subpackage  Blocks
 * @author      JoshuaWang2019
 * @version     1.0.0
 * @since       2025-05-04
 */

// 防止直接访问此文件
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 渲染电子书网格区块
 */
function render_ebooks_grid()
{
    // 查询参数
    $args = array(
        'post_type' => 'ebook',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'meta_key' => 'book_order',
        'order' => 'ASC'
    );

    // 执行查询
    $query = new WP_Query($args);

    // 开始输出
    ob_start();
?>
    <div class="wp-block-block-canvas-child-ebooks-grid">
        <?php if ($query->have_posts()) : ?>
            <div class="ebooks-grid">
                <?php while ($query->have_posts()) :
                    $query->the_post();

                    // 获取ACF字段
                    $book_name = get_field('book_name');
                    $author = get_field('author');
                    $cover_image = get_field('cover_image');
                    $book_file = get_field('book_file');
                ?>
                    <div class="ebook-item">
                        <?php if ($cover_image) : ?>
                            <img src="<?php echo esc_url($cover_image['url']); ?>"
                                alt="<?php echo esc_attr($book_name); ?>"
                                class="ebook-cover"
                                loading="lazy">
                        <?php endif; ?>

                        <h3 class="ebook-title"><?php echo esc_html($book_name); ?></h3>
                        <p class="ebook-author"><?php echo esc_html($author); ?></p>

                        <?php if ($book_file) : ?>
                            <a href="<?php echo esc_url($book_file['url']); ?>"
                                class="ebook-download"
                                download
                                aria-label="下载《<?php echo esc_attr($book_name); ?>》">
                                下载电子书
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p class="no-books">暂无电子书</p>
        <?php endif;

        wp_reset_postdata();
        ?>
    </div>
<?php
    return ob_get_clean();
}

// 返回渲染结果
echo render_ebooks_grid();
