<?php

/**
 * 资源管理类
 *
 * @package     block-canvas Child
 * @subpackage  Core
 * @author      JoshuaWang2019
 * @version     1.0.0
 * @since       2025-06-04
 */

namespace BlockCanvasChild\Core;

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class Assets
{
    /**
     * 单例实例
     *
     * @var Assets
     */
    private static $instance = null;

    /**
     * 获取单例实例
     *
     * @return Assets
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 构造函数
     */
    private function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * 注册和加载样式
     */
    public function enqueue_styles()
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

        // 获取当前模板信息
        $current_template = get_page_template_slug();

        // 加载电子书列表样式
        $is_ebooks_page = (
            $current_template === 'ebooks-list' ||
            $current_template === 'templates/ebooks-list' ||
            is_page('ebooks') ||
            is_page('ebook-list')
        );
        if ($is_ebooks_page) {
            wp_enqueue_style(
                'ebooks-style',
                get_stylesheet_directory_uri() . '/assets/css/ebooks.css',
                array('block-canvas-child-style'),
                $theme->get('Version'),
                'all'
            );
        }

        // 加载 Message 页面样式
        if (is_singular('message')) {
            wp_enqueue_style(
                'message-style',
                get_stylesheet_directory_uri() . '/assets/css/message.css',
                array('block-canvas-child-style'),
                $theme->get('Version'),
                'all'
            );
        }

        // 加载 Messages List 页面样式
        $is_messages_page = (
            $current_template === 'messages-list' ||
            $current_template === 'templates/messages-list' ||
            is_page('messages-list') ||
            is_page('messages')
        );

        if ($is_messages_page) {
            wp_enqueue_style(
                'messages-style',
                get_stylesheet_directory_uri() . '/assets/css/messages-list.css',
                array('block-canvas-child-style'),
                $theme->get('Version')
            );
        }
    }

    /**
     * 注册和加载脚本
     */
    public function enqueue_scripts()
    {
        $theme = wp_get_theme();

        // 获取当前模板信息
        $current_template = get_page_template_slug();
        $is_ebooks_page = (
            $current_template === 'ebooks-list' ||
            $current_template === 'templates/ebooks-list' ||
            is_page('ebooks') ||
            is_page('ebook-list')
        );
        if ($is_ebooks_page) {
            // 电子书搜索脚本
            wp_enqueue_script(
                'ebooks-search',
                get_stylesheet_directory_uri() . '/assets/js/ebooks-search.js',
                array(),  // 不需要 jQuery 依赖
                $theme->get('Version'),
                true  // 在页面底部加载
            );
        }
    }
}
