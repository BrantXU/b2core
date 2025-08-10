/**
 * 表格助手类 - 封装表格的全选、分页、查询等功能
 * 支持不同表头的表格复用
 */
class TableHelper {
  /**
   * 构造函数
   * @param {Object} options - 表格配置选项
   * @param {string} options.tableId - 表格ID
   * @param {string} options.paginationLinksId - 分页链接ID
   * @param {string} options.prevPageId - 上一页按钮ID
   * @param {string} options.nextPageId - 下一页按钮ID
   * @param {string} options.totalItemsId - 总记录数ID
   * @param {string} options.pageSizeId - 每页显示数量ID
   * @param {string} options.pageSizeSelectorId - 每页显示数量选择器ID
   * @param {string} options.searchInputId - 搜索输入框ID
   * @param {string} options.selectAllId - 全选复选框ID
   * @param {string} options.batchDeleteBtnId - 批量删除按钮ID
   * @param {Array} options.headers - 表头定义 [{title, field, render}]，render为可选的自定义渲染函数
   * @param {Array} options.data - 表格数据
   * @param {Function} options.onEdit - 编辑按钮点击回调函数
   * @param {Function} options.onDelete - 删除按钮点击回调函数
   */
  constructor(options) {
    // 初始化配置
    this.config = { ...options };
    // 分页参数
    this.currentPage = 1;
    this.pageSize = parseInt(document.getElementById(this.config.pageSizeSelectorId)?.value || 10);
    this.totalItems = this.config.data.length;
    this.totalPages = Math.max(1, Math.ceil(this.totalItems / this.pageSize));
    // 存储过滤后的数据
    this.filteredData = [...this.config.data];
    // 获取DOM元素
    this.initElements();
    // 绑定事件
    this.bindEvents();
    // 初始化表格
    this.renderTable();
    this.renderPagination();
  }

  /**
   * 初始化DOM元素
   */
  initElements() {
    this.tableBody = document.querySelector(`#${this.config.tableId} tbody`);
    this.paginationLinks = document.getElementById(this.config.paginationLinksId);
    this.prevPageBtn = document.getElementById(this.config.prevPageId);
    this.nextPageBtn = document.getElementById(this.config.nextPageId);
    this.totalItemsEl = document.getElementById(this.config.totalItemsId);
    this.pageSizeEl = document.getElementById(this.config.pageSizeId);
    this.pageSizeSelector = document.getElementById(this.config.pageSizeSelectorId);
    this.searchInput = document.getElementById(this.config.searchInputId);
    this.selectAll = document.getElementById(this.config.selectAllId);
    this.batchDeleteBtn = document.getElementById(this.config.batchDeleteBtnId);
  }

  /**
   * 绑定事件
   */
  bindEvents() {
    // 上一页按钮点击事件
    this.prevPageBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      if (this.currentPage > 1) {
        this.currentPage--;
        this.renderTable();
        this.renderPagination();
      }
    });

    // 下一页按钮点击事件
    this.nextPageBtn?.addEventListener('click', (e) => {
      e.preventDefault();
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
        this.renderTable();
        this.renderPagination();
      }
    });

    // 每页显示数量变更事件
    this.pageSizeSelector?.addEventListener('change', () => {
      this.pageSize = parseInt(this.pageSizeSelector.value);
      this.currentPage = 1;
      this.totalPages = Math.max(1, Math.ceil(this.filteredData.length / this.pageSize));
      this.pageSizeEl.textContent = this.pageSize;
      this.renderTable();
      this.renderPagination();
      console.log('每页显示数量已更改为: ' + this.pageSize);
    });

    // 搜索功能
    this.searchInput?.addEventListener('input', () => {
      const searchTerm = this.searchInput.value.toLowerCase().trim();

      // 过滤数据
      this.filteredData = searchTerm
        ? this.config.data.filter(item => {
            return Object.values(item).some(value => {
              if (value === null || value === undefined) return false;
              return String(value).toLowerCase().includes(searchTerm);
            });
          })
        : [...this.config.data];

      // 更新显示
      this.totalItemsEl.textContent = this.filteredData.length;
      this.totalPages = Math.max(1, Math.ceil(this.filteredData.length / this.pageSize));
      this.currentPage = 1;

      this.renderTable();
      this.renderPagination();
    });

    // 全选功能
    this.selectAll?.addEventListener('change', () => {
      const checkboxes = document.querySelectorAll('.table-checkbox');
      checkboxes.forEach(checkbox => {
        checkbox.checked = this.selectAll.checked;
      });
      this.updateBatchButtonState();
    });
  }

  /**
   * 渲染表格数据
   */
  renderTable() {
    // 清空表格
    this.tableBody.innerHTML = '';

    // 计算当前页的数据
    const startIndex = (this.currentPage - 1) * this.pageSize;
    const endIndex = Math.min(startIndex + this.pageSize, this.filteredData.length);
    const currentData = this.filteredData.slice(startIndex, endIndex);

    // 生成表格行
    currentData.forEach(item => {
      const row = document.createElement('tr');
      let cellsHtml = '';

      // 添加复选框列
      cellsHtml += `<td><input type="checkbox" name="ids[]" value="${item.id}" class="table-checkbox"></td>`;

      // 添加数据列
      this.config.headers.forEach(header => {
        if (header.render) {
          cellsHtml += `<td>${header.render(item)}</td>`;
        } else {
          cellsHtml += `<td>${item[header.field] || ''}</td>`;
        }
      });

      // 添加操作列
      cellsHtml += '<td class="uk-table-shrink">
                  <div class="uk-flex uk-flex-middle uk-gap-small">
                    <a href="javascript:void(0)" class="edit-btn" data-id="' + item.id + '"><span uk-icon="icon: pencil"></span></a>
                    <a href="javascript:void(0)" class="delete-btn" data-id="' + item.id + '"><span uk-icon="icon: trash"></span></a>
                  </div>
                </td>';

      row.innerHTML = cellsHtml;
      this.tableBody.appendChild(row);

      // 绑定行内按钮事件
      row.querySelector('.edit-btn')?.addEventListener('click', () => {
        if (this.config.onEdit) {
          this.config.onEdit(item);
        }
      });

      row.querySelector('.delete-btn')?.addEventListener('click', () => {
        if (this.config.onDelete) {
          this.config.onDelete(item);
        }
      });

      // 绑定复选框事件
      row.querySelector('.table-checkbox')?.addEventListener('change', () => {
        this.updateBatchButtonState();
        this.updateSelectAllState();
      });
    });

    // 更新批量操作按钮状态
    this.updateBatchButtonState();
  }

  /**
   * 渲染分页
   */
  renderPagination() {
    // 清空分页链接
    this.paginationLinks.innerHTML = '';

    // 更新分页信息
    this.totalItemsEl.textContent = this.filteredData.length;
    this.pageSizeEl.textContent = this.pageSize;

    // 更新上一页/下一页按钮状态
    this.prevPageBtn.disabled = this.currentPage === 1;
    this.nextPageBtn.disabled = this.currentPage === this.totalPages;

    // 生成分页链接
    if (this.totalPages <= 1) {
      return;
    }

    // 添加首页链接
    if (this.currentPage > 3) {
      this.addPageLink(1);
      if (this.currentPage > 4) {
        this.addEllipsis();
      }
    }

    // 添加当前页附近的链接
    const startPage = Math.max(1, this.currentPage - 2);
    const endPage = Math.min(this.totalPages, startPage + 4);

    for (let i = startPage; i <= endPage; i++) {
      this.addPageLink(i);
    }

    // 添加末页链接
    if (this.currentPage < this.totalPages - 2) {
      if (this.currentPage < this.totalPages - 3) {
        this.addEllipsis();
      }
      this.addPageLink(this.totalPages);
    }
  }

  /**
   * 添加分页链接
   * @param {number} pageNum - 页码
   */
  addPageLink(pageNum) {
    const link = document.createElement('li');
    link.className = pageNum === this.currentPage ? 'uk-active' : '';
    link.innerHTML = `<a href="#">${pageNum}</a>`;
    link.addEventListener('click', (e) => {
      e.preventDefault();
      this.currentPage = pageNum;
      this.renderTable();
      this.renderPagination();
    });
    this.paginationLinks.appendChild(link);
  }

  /**
   * 添加省略号
   */
  addEllipsis() {
    const ellipsis = document.createElement('li');
    ellipsis.className = 'uk-disabled';
    ellipsis.innerHTML = '<span>...</span>';
    this.paginationLinks.appendChild(ellipsis);
  }

  /**
   * 更新批量操作按钮状态
   */
  updateBatchButtonState() {
    const checkedBoxes = document.querySelectorAll('.table-checkbox:checked');
    if (this.batchDeleteBtn) {
      this.batchDeleteBtn.disabled = checkedBoxes.length === 0;
    }
  }

  /**
   * 更新全选框状态
   */
  updateSelectAllState() {
    const checkboxes = document.querySelectorAll('.table-checkbox');
    const checkedBoxes = document.querySelectorAll('.table-checkbox:checked');
    if (this.selectAll) {
      this.selectAll.checked = checkboxes.length > 0 && checkboxes.length === checkedBoxes.length;
    }
  }

  /**
   * 获取选中的ID
   * @returns {Array} 选中的ID数组
   */
  getSelectedIds() {
    const checkedBoxes = document.querySelectorAll('.table-checkbox:checked');
    return Array.from(checkedBoxes).map(checkbox => checkbox.value);
  }
}

// 自动初始化表格
document.addEventListener('DOMContentLoaded', function() {
  // 查找所有带有data-table-config属性的表格
  const tables = document.querySelectorAll('[data-table-config]');

  tables.forEach(table => {
    try {
      // 获取配置
      const config = JSON.parse(table.getAttribute('data-table-config'));
      // 设置表格ID
      config.tableId = table.id;
      // 创建表格助手实例
      new TableHelper(config);
    } catch (error) {
      console.error('表格初始化失败:', error);
    }
  });
});