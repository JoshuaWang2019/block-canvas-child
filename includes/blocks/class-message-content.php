<?php

/**
 * Message 内容区块类
 *
 * @package     block-canvas Child
 * @subpackage  Blocks
 * @author      JoshuaWang2019
 * @version     1.0.0
 * @since       2025-06-10
 */

namespace BlockCanvasChild\Blocks;

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class Message_Content
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
        register_block_type('block-canvas-child/message-content', array(
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
        // 只在 message post type 的单页面显示
        if (!is_singular('message')) {
            return '';
        }

        ob_start();

        // 获取当前 post
        global $post;

        // 获取 ACF 字段
        $message_title = get_field('message_title');
        $message_author = get_field('message_author');
        $message_category = get_field('message_category');
        $pc_transcript = get_field('pc_transcript');
        $mobile_transcript = get_field('mobile_transcript');
        $message_audio = get_field('message_audio');
        $message_video = get_field('message_video');
        $message_content = get_field('message_content');
        $message_feature_image = get_field('message_feature_image');

        $this->render_message_content(
            $message_title,
            $message_author,
            $message_category,
            $pc_transcript,
            $mobile_transcript,
            $message_audio,
            $message_video,
            $message_content,
            $message_feature_image
        );

        return ob_get_clean();
    }

    /**
     * 渲染 Message 内容
     */
    private function render_message_content($title, $author, $category, $pc_transcript, $mobile_transcript, $audio, $video, $content, $feature_image)
    {
?>
        <div class="message-content-wrapper">
            <?php if ($feature_image): ?>
                <!-- 封面图片 -->
                <div class="message-feature-image">
                    <img src="<?php echo esc_url($feature_image['url']); ?>"
                        alt="<?php echo esc_attr($title ?: get_the_title()); ?>"
                        class="message-cover-img"
                        loading="lazy">
                </div>
            <?php endif; ?>

            <!-- 信息标题 -->
            <div class="message-header">
                <h1 class="message-title">
                    <?php echo esc_html($title ?: get_the_title()); ?>
                </h1>

                <?php if ($author): ?>
                    <!-- 作者信息 -->
                    <div class="message-author">
                        <span class="author-label">作者：</span>
                        <span class="author-name"><?php echo esc_html($author); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($category): ?>
                    <!-- 分类信息 -->
                    <div class="message-categories">
                        <span class="category-label">分类：</span>
                        <?php
                        $category_names = array();
                        foreach ($category as $cat_id) {
                            $cat = get_term($cat_id);
                            if ($cat && !is_wp_error($cat)) {
                                $category_names[] = $cat->name;
                            }
                        }
                        echo esc_html(implode(', ', $category_names));
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($audio): ?>
                <!-- 信息音频 -->
                <div class="message-audio-section">
                    <h3 class="section-title">信息音频</h3>
                    <div class="audio-player">
                        <audio controls preload="metadata" class="message-audio">
                            <source src="<?php echo esc_url($audio['url']); ?>" type="<?php echo esc_attr($audio['mime_type']); ?>">
                            您的浏览器不支持音频播放。
                            <a href="<?php echo esc_url($audio['url']); ?>" download>下载音频文件</a>
                        </audio>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($video): ?>
                <!-- 信息视频 -->
                <div class="message-video-section">
                    <h3 class="section-title">信息视频</h3>
                    <div class="video-player">
                        <video controls preload="metadata" class="message-video">
                            <source src="<?php echo esc_url($video['url']); ?>" type="<?php echo esc_attr($video['mime_type']); ?>">
                            您的浏览器不支持视频播放。
                            <a href="<?php echo esc_url($video['url']); ?>" download>下载视频文件</a>
                        </video>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($content): ?>
                <!-- 信息HTML文本 -->
                <div class="message-text-content">
                    <h3 class="section-title">信息内容</h3>
                    <div class="message-content-text">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 文档下载区域 -->
            <?php if ($pc_transcript || $mobile_transcript): ?>
                <div class="message-documents">
                    <h3 class="section-title">相关文档</h3>
                    <div class="documents-grid">
                        <?php if ($pc_transcript): ?>
                            <div class="document-item pc-document">
                                <div class="document-icon">
                                    <span class="doc-icon">📄</span>
                                </div>
                                <div class="document-info">
                                    <h4 class="document-title">电脑端文档</h4>
                                    <p class="document-filename"><?php echo esc_html($pc_transcript['filename']); ?></p>
                                    <a href="<?php echo esc_url($pc_transcript['url']); ?>"
                                        download
                                        class="document-download-btn">
                                        <span class="download-icon">⬇</span>
                                        下载文档
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($mobile_transcript): ?>
                            <div class="document-item mobile-document">
                                <div class="document-icon">
                                    <span class="doc-icon">📱</span>
                                </div>
                                <div class="document-info">
                                    <h4 class="document-title">手机端文档</h4>
                                    <p class="document-filename"><?php echo esc_html($mobile_transcript['filename']); ?></p>
                                    <a href="<?php echo esc_url($mobile_transcript['url']); ?>"
                                        download
                                        class="document-download-btn">
                                        <span class="download-icon">⬇</span>
                                        下载文档
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
<?php
    }
}
