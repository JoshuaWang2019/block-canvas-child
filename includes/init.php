<?php

/**
 * 初始化文件
 *
 * @package     block-canvas Child
 * @subpackage  Core
 * @author      JoshuaWang2019
 * @version     1.0.0
 * @since       2025-06-04
 */

namespace BlockCanvasChild;

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 自动加载类
spl_autoload_register(function ($class) {
    // 检查命名空间
    if (strpos($class, 'BlockCanvasChild\\') !== 0) {
        return;
    }

    // 移除命名空间前缀
    $class = str_replace('BlockCanvasChild\\', '', $class);

    // 将类名转换为文件路径
    $file = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($class));
    $file = str_replace('_', '-', $file);

    // 构建完整的文件路径
    $file = get_stylesheet_directory() . '/includes/' . $file . '.php';

    // 如果文件存在则加载它
    if (file_exists($file)) {
        require_once $file;
    }
});

// 初始化核心类
require_once get_stylesheet_directory() . '/includes/core/class-assets.php';
require_once get_stylesheet_directory() . '/includes/core/class-blocks.php';

// 启动插件
function init()
{
    // 初始化资源管理
    Core\Assets::get_instance();

    // 初始化区块管理
    Core\Blocks::get_instance();
}
add_action('after_setup_theme', __NAMESPACE__ . '\\init');
