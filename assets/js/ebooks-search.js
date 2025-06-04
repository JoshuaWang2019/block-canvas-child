/**
 * 电子书搜索功能
 *
 * @package     block-canvas Child
 * @subpackage  Scripts
 * @author      JoshuaWang2019
 * @version     1.0.0
 * @since       2025-06-04
 */

document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('ebooks-search-input');

  if (!searchInput) {
    console.error('搜索输入框未找到');
    return;
  }

  // 即时搜索（输入时触发）
  searchInput.addEventListener('input', function () {
    filterBooks(this.value);
  });

  // 回车搜索
  searchInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      filterBooks(this.value);
    }
  });

  /**
   * 过滤电子书
   * @param {string} searchTerm - 搜索关键词
   */
  function filterBooks(searchTerm) {
    searchTerm = searchTerm.toLowerCase().trim();
    const ebookItems = document.querySelectorAll('.ebook-item');
    let hasResults = false;

    ebookItems.forEach((item) => {
      const titleElement = item.querySelector('.ebook-title');
      const authorElement = item.querySelector('.ebook-author');

      const title = titleElement ? titleElement.textContent.toLowerCase() : '';
      const author = authorElement
        ? authorElement.textContent.toLowerCase()
        : '';

      // 只搜索书名和作者
      const isMatch = title.includes(searchTerm) || author.includes(searchTerm);

      // 显示或隐藏电子书项
      item.style.display = isMatch || searchTerm === '' ? '' : 'none';

      if (isMatch) {
        hasResults = true;
      }
    });

    // 处理无结果的情况
    handleNoResults(hasResults);
  }

  /**
   * 处理无搜索结果的显示
   * @param {boolean} hasResults - 是否有搜索结果
   */
  function handleNoResults(hasResults) {
    let noResultsElement = document.querySelector('.no-search-results');
    const ebooksGrid = document.querySelector('.ebooks-grid');

    if (!hasResults) {
      if (!noResultsElement) {
        noResultsElement = document.createElement('div');
        noResultsElement.className = 'no-search-results';
        noResultsElement.textContent = '没有找到相关电子书';
        ebooksGrid.parentNode.insertBefore(
          noResultsElement,
          ebooksGrid.nextSibling,
        );
      }
      noResultsElement.style.display = 'block';
    } else if (noResultsElement) {
      noResultsElement.style.display = 'none';
    }
  }
});
