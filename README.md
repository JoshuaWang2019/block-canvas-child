# Block Canvas Child Theme

Block Canvas 子主题，用于甘泉网站的自定义功能开发。

## 项目结构

```
block-canvas-child/
├── assets/                  # 静态资源文件
│   ├── css/                # CSS 样式文件
│   │   └── ebooks.css      # 电子书列表页面样式
│   └── js/                 # JavaScript 文件
│       └── ebooks-search.js # 电子书搜索功能
├── includes/               # PHP 类和函数文件
│   ├── blocks/            # 自定义区块类
│   │   └── class-ebooks-grid.php  # 电子书网格区块
│   ├── core/             # 核心功能类
│   │   └── class-assets.php       # 资源管理类
│   └── init.php          # 初始化文件
├── templates/            # 页面模板
│   └── ebooks-list.html  # 电子书列表页面模板
├── functions.php        # 主题函数文件
├── style.css           # 主题样式文件
└── README.md           # 本文档
```

## 添加新页面模板

1. 创建页面模板

   ```html
   <!-- 在 templates/ 目录下创建新的 .html 文件 -->
   <!-- 例如: templates/new-template.html -->
   <!-- wp:template-part {"slug":"header","tagName":"header"} /-->

   <!-- wp:group {"tagName":"main","className":"your-container","layout":{"type":"constrained","contentSize":"1200px"}} -->
   <main class="wp-block-group your-container">
     <!-- 添加你的区块内容 -->
   </main>
   <!-- /wp:group -->

   <!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
   ```

2. 添加自定义区块类（如果需要）

   ```php
   // 在 includes/blocks/ 目录下创建新的类文件
   // 例如: includes/blocks/class-your-block.php

   namespace BlockCanvasChild\Blocks;

   class Your_Block {
       public function __construct() {
           $this->register();
       }

       private function register() {
           register_block_type('block-canvas-child/your-block', array(
               'render_callback' => array($this, 'render')
           ));
       }

       public function render($attributes) {
           // 实现渲染逻辑
       }
   }
   ```

3. 添加样式文件

   ```css
   /* 在 assets/css/ 目录下创建新的 CSS 文件 */
   /* 例如: assets/css/your-styles.css */

   .your-container {
     /* 添加样式 */
   }
   ```

4. 添加 JavaScript 文件（如果需要）

   ```javascript
   // 在 assets/js/ 目录下创建新的 JS 文件
   // 例如: assets/js/your-script.js

   document.addEventListener('DOMContentLoaded', function () {
     // 实现功能
   });
   ```

5. 注册资源

   ```php
   // 在 includes/core/class-assets.php 中添加

   public function enqueue_styles() {
       // ... 现有代码 ...

       // 添加新样式
       if (is_page_template('templates/new-template.html')) {
           wp_enqueue_style(
               'your-style',
               get_stylesheet_directory_uri() . '/assets/css/your-styles.css',
               array('block-canvas-child-style'),
               $theme->get('Version')
           );
       }
   }

   public function enqueue_scripts() {
       // ... 现有代码 ...

       // 添加新脚本
       wp_enqueue_script(
           'your-script',
           get_stylesheet_directory_uri() . '/assets/js/your-script.js',
           array(),
           $theme->get('Version'),
           true
       );
   }
   ```

6. 初始化新区块（如果有）

   ```php
   // 在 includes/init.php 中添加

   // 加载区块类
   require_once get_stylesheet_directory() . '/includes/blocks/class-your-block.php';

   // 初始化区块
   new \BlockCanvasChild\Blocks\Your_Block();
   ```

## 代码规范

1. 文件命名

   - PHP 类文件：`class-{name}.php`
   - CSS 文件：`{name}.css`
   - JS 文件：`{name}.js`
   - 模板文件：`{name}.html`

2. 命名空间

   - 所有 PHP 类都使用 `BlockCanvasChild` 命名空间
   - 区块类放在 `BlockCanvasChild\Blocks` 命名空间下
   - 核心类放在 `BlockCanvasChild\Core` 命名空间下

3. 注释规范
   ```php
   /**
    * 类/函数的描述
    *
    * @package     block-canvas Child
    * @subpackage  区块/核心
    * @author      JoshuaWang2019
    * @version     1.0.0
    * @since       2025-06-04
    */
   ```

## 开发注意事项

1. 所有自定义区块都需要使用 `block-canvas-child` 作为命名空间前缀
2. 样式文件中使用 `!important` 确保优先级
3. JavaScript 文件需要等待 `DOMContentLoaded` 事件
4. 所有文件权限设置为 644
5. 目录权限设置为 755

## 部署步骤

1. 上传所有文件到对应目录
2. 清除浏览器缓存
3. 清除 WordPress 缓存
4. 刷新固定链接

## 常见问题

1. 样式不生效

   - 检查文件路径
   - 检查样式优先级
   - 清除缓存

2. JavaScript 不工作
   - 检查控制台错误
   - 确认文件正确加载
   - 验证代码语法

## 版本历史

- 1.0.0 (2025-06-04)
  - 初始版本
  - 添加电子书列表功能
