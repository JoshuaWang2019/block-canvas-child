<?php

/**
 * 区块管理类
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

class Blocks
{
    /**
     * 单例实例
     *
     * @var Blocks
     */
    private static $instance = null;

    /**
     * 获取单例实例
     *
     * @return Blocks
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
        add_action('init', array($this, 'register_blocks'));
    }

    /**
     * 注册所有区块
     */
    public function register_blocks()
    {
        if (!function_exists('register_block_type')) {
            return;
        }

        // 加载并初始化电子书网格区块
        require_once get_stylesheet_directory() . '/includes/blocks/class-ebooks-grid.php';
        new \BlockCanvasChild\Blocks\Ebooks_Grid();

        // 在这里加载其他区块...
    }
}
