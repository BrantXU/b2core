/**
 * 创建一个模态框
 * @param {string} content - 模态框内容
 * @param {Object} options - 可选参数
 * @param {boolean} options.showOverlay - 是否显示遮罩层（默认：true）
 * @param {Object} options.style - 自定义模态框样式（对象形式）
 */
function createModal(content, options = {}) {
  const { showOverlay = true, style = {} } = options;

  // 创建遮罩层
  const overlay = document.createElement('div');
  overlay.className = 'modal-overlay';
  overlay.style.position = 'fixed';
  overlay.style.top = '0';
  overlay.style.left = '0';
  overlay.style.width = '100%';
  overlay.style.height = '100%';
  overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
  overlay.style.display = 'flex';
  overlay.style.justifyContent = 'center';
  overlay.style.alignItems = 'center';
  overlay.style.zIndex = '1000';

  // 创建模态内容区域
  const modal = document.createElement('div');
  modal.className = 'modal-content';
  modal.style.position = 'relative';
  modal.style.backgroundColor = '#fff';
  modal.style.padding = '20px';
  modal.style.borderRadius = '8px';
  modal.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
  modal.style.zIndex = '1001';

  // 合并自定义样式
  Object.keys(style).forEach(key => {
    modal.style[key] = style[key];
  });

  // 添加关闭按钮
  const closeBtn = document.createElement('button');
  closeBtn.textContent = '×';
  closeBtn.style.position = 'absolute';
  closeBtn.style.top = '10px';
  closeBtn.style.right = '10px';
  closeBtn.style.fontSize = '18px';
  closeBtn.style.cursor = 'pointer';

  // 添加关闭事件
  closeBtn.addEventListener('click', () => {
    document.body.removeChild(overlay);
  });

  // 添加内容
  modal.innerHTML = content;
  modal.appendChild(closeBtn);

  // 添加到遮罩层
  overlay.appendChild(modal);

  // 添加到页面
  document.body.appendChild(overlay);
}
 