(function ($) {
  'use strict';

  $(document).ready(function () {
    const menuSelector = '.wp-block-navigation-item';
    const submenuSelector = '.wp-block-navigation__submenu-container';
    const submenuIconSelector = '.wp-block-navigation__submenu-icon';

    // 处理菜单项点击
    $(document).on(
      'click',
      `${menuSelector} > .wp-block-navigation-item__content, ${menuSelector} > ${submenuIconSelector}`,
      function (e) {
        const $menuItem = $(this).closest(menuSelector);

        // 只有当存在子菜单时才阻止默认行为
        if ($menuItem.find(submenuSelector).length > 0) {
          e.preventDefault();
          e.stopPropagation();

          // 切换当前菜单项的展开状态
          $menuItem.toggleClass('is-menu-open');

          // 关闭同级其他菜单项
          $menuItem.siblings(menuSelector).removeClass('is-menu-open');

          console.log(
            'Menu clicked:',
            $menuItem.find('> .wp-block-navigation-item__content').text(),
          );
        }
      },
    );

    // 初始化时关闭所有子菜单
    $(menuSelector).removeClass('is-menu-open');
  });
})(jQuery);
