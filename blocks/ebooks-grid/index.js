/**
 * WordPress区块编辑器相关依赖
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * 注册电子书网格区块
 */
registerBlockType('block-canvas-child/ebooks-grid', {
  /**
   * @see block.json for most metadata
   */

  /**
   * 编辑器中的区块展示
   *
   * @param {Object} props 区块属性
   * @return {WPElement} 编辑器中的区块元素
   */
  edit: function (props) {
    const blockProps = useBlockProps();

    return (
      <div {...blockProps}>
        <ServerSideRender
          block="block-canvas-child/ebooks-grid"
          attributes={props.attributes}
        />
      </div>
    );
  },

  /**
   * 前端保存方法
   * 返回null因为我们使用PHP来渲染前端内容
   *
   * @return {null} 空返回
   */
  save: function () {
    return null;
  },
});
