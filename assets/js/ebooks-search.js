/**
 * 电子书搜索功能
 * @package     block-canvas Child
 * @author      JoshuaWang2019
 * @version     1.0.0
 * @since       2025-05-04
 */

(function ($) {
  'use strict';

  $(document).ready(function () {
    // 缓存DOM元素
    const searchInput = $('#ebooks-search-input');
    const searchButton = $('#ebooks-search-button');
    const ebooksGrid = $('.ebooks-grid');

    // 防抖定时器
    let searchTimeout = null;

    /**
     * 更新网格显示
     * @param {Array} books - 电子书数据数组
     */
    function updateGrid(books) {
      // 只更新网格部分，不更新搜索框
      ebooksGrid.empty();

      // 如果没有搜索结果
      if (!Array.isArray(books) || books.length === 0) {
        ebooksGrid.html('<p class="no-books">未找到相关电子书</p>');
        return;
      }

      // 遍历书籍数据并创建元素
      books.forEach(function (book) {
        const bookHtml = `
                    <div class="ebook-item">
                        ${
                          book.cover_image
                            ? `<img src="${book.cover_image.url}" 
                                 alt="${book.book_name}" 
                                 class="ebook-cover"
                                 loading="lazy">`
                            : ''
                        }
                        <h3 class="ebook-title">${book.book_name}</h3>
                        ${
                          book.author
                            ? `<p class="ebook-author">${book.author}</p>`
                            : ''
                        }
                        ${
                          book.book_file
                            ? `<a href="${book.book_file.url}" 
                                class="ebook-download" 
                                download
                                aria-label="下载《${book.book_name}》">
                                下载电子书
                            </a>`
                            : ''
                        }
                    </div>
                `;
        ebooksGrid.append(bookHtml);
      });
    }

    /**
     * 执行搜索
     */
    function performSearch() {
      const searchTerm = searchInput.val().trim();

      // 如果搜索词为空，恢复原始列表
      if (!searchTerm) {
        location.reload();
        return;
      }

      // 发送AJAX请求
      $.ajax({
        url: ebooksSearch.ajaxurl,
        type: 'POST',
        data: {
          action: 'ebooks_search',
          nonce: ebooksSearch.nonce,
          search: searchTerm,
        },
        beforeSend: function () {
          // 添加加载状态
          ebooksGrid.addClass('loading');
        },
        success: function (response) {
          if (response.success && Array.isArray(response.data)) {
            // 更新显示
            updateGrid(response.data);
          } else {
            console.error('搜索响应格式错误:', response);
            ebooksGrid.html(
              '<p class="no-books">搜索结果格式错误，请刷新页面重试</p>',
            );
          }
        },
        error: function (xhr, status, error) {
          // 错误处理
          console.error('搜索请求失败:', error);
          ebooksGrid.html('<p class="no-books">搜索出错，请稍后重试</p>');
        },
        complete: function () {
          // 移除加载状态
          ebooksGrid.removeClass('loading');
        },
      });
    }

    /**
     * 事件监听器
     */

    // 搜索按钮点击
    searchButton.on('click', function (e) {
      e.preventDefault();
      performSearch();
    });

    // 输入框防抖
    searchInput.on('input', function () {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(performSearch, 500);
    });

    // 回车键触发搜索
    searchInput.on('keypress', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        performSearch();
      }
    });
  });
})(jQuery);
