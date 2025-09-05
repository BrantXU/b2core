/**
 * TableRender类 - 表格渲染器，提供分页、搜索、排序、复选框选择和批量操作功能
 */
class TableRender {
    /**
     * 构造函数
     * @param {string} tableId - 表格元素的ID
     * @param {Object} options - 配置选项
     * @param {string} baseUrl - 基础URL，所有操作URL都基于此生成
     */
    constructor(tableId, options = {}, baseUrl = '') {
        // 表格引用
        this.container = dom.gid(tableId);
        if (!this.container) {
            throw new Error(`Element with id "${tableId}" not found`);
        }
        
        // 检查是否是表格元素，如果不是则创建表格
        if (this.container.tagName === 'TABLE') {
            this.table = this.container;
        } else {
            // 创建表格元素
            this.table = document.createElement('table');
            this.table.className = 'uk-table uk-table-small uk-table-striped uk-table-hover';
            this.table.id = tableId + '-table';
            
            // 创建thead和tbody
            this.table.innerHTML = '<thead></thead><tbody></tbody>';
            this.container.appendChild(this.table);
        }
        
        // 基础URL
        this.baseUrl = baseUrl;
        
        // 配置选项
        this.options = this._getDefaultOptions(options);
        
        // 数据存储
        this.originalData = []; // 原始数据
        this.filteredData = []; // 过滤后的数据
        
        // 分页相关
        this.currentPage = 1; // 当前页码
        
        // 排序相关
        this.sortColumn = null; // 当前排序列索引
        this.sortDirection = 'asc'; // 排序方向：'asc'升序, 'desc'降序
        
        // 复选框相关属性
        this.selectedRowIds = new Set(); // 存储所有选中的行ID
        this.allSelected = false; // 是否全选所有数据
        this.currentPageAllSelected = false; // 是否全选当前页数据
        
        // UI元素引用
        this.editBtn = null; // 编辑按钮引用
        this.deleteBtn = null; // 删除按钮引用
        
        // 初始化
        this.init();
    }
    
    /**
     * 获取默认配置选项
     * @param {Object} customOptions - 自定义配置
     * @returns {Object} 合并后的配置
     * @private
     */
    _getDefaultOptions(customOptions) {
        return {
            pageSize: 10, // 每页显示条数
            pageSizes: [10, 20, 50, 100], // 可选的每页显示条数
            showInfo: true, // 是否显示信息面板
            showPagination: true, // 是否显示分页控件
            searchable: true, // 是否启用搜索功能
            sortable: true, // 是否启用排序功能
            searchPlaceholder: '搜索...', // 搜索框占位符
            enableCheckbox: true, // 是否启用复选框
            showExport: true, // 是否显示导出按钮
            showImport: true, // 是否显示导入按钮
            showCreate: true, // 是否显示创建按钮
            showEdit: true, // 是否显示编辑按钮
            showDelete: true, // 是否显示删除按钮
            ...customOptions
        };
    }

    /**
     * 初始化表格增强器
     */
    init() {
        this.extractData(); // 提取表格数据
        this.createControls(); // 创建控制面板
        this.bindEvents(); // 绑定事件处理
        this.render(); // 首次渲染表格
        this.updateEditDeleteButtons(); // 更新编辑删除按钮状态
    }

    /**
     * 从表格中提取数据或使用提供的JS数组数据
     */
    extractData() {
        if (this.options.data && Array.isArray(this.options.data)) {
            // 使用提供的JS数组数据
            this.originalData = this.options.data.map((item, index) => ({
                id: item.id || `row_${index}`,
                element: null,
                cells: this.options.fields ? 
                    Object.keys(this.options.fields).map(fieldName => item[fieldName] || '') :
                    Object.values(item)
            }));
        } 
        // 初始化过滤数据为原始数据
        this.filteredData = [...this.originalData];
    }

    /**
     * 创建表格上方的控制面板
     */
    createControls() {
        const container = this.container;
        
        // 创建控制面板
        const controlPanel = document.createElement('div');
        controlPanel.className = 'table-enhancer-controls uk-margin';
        controlPanel.innerHTML = this._getControlPanelHtml();
        
        // 将控制面板插入到容器中表格之前
        if (this.table.parentNode === container) {
            container.insertBefore(controlPanel, this.table);
        } else {
            // 如果表格还没有添加到容器，先添加控制面板
            container.appendChild(controlPanel);
        }
        
        // 创建信息面板
        if (this.options.showInfo) {
            this._createInfoPanel(container);
        }
        
        // 创建分页控件
        if (this.options.showPagination) {
            this._createPagination(container);
        }
    }
    
    /**
     * 获取控制面板HTML内容
     * @returns {string} 控制面板HTML
     * @private
     */
    _getControlPanelHtml() {
        return `
            <div class="uk-flex uk-flex-between uk-flex-middle">
                <div class="uk-flex uk-flex-middle">
                    ${this._getSearchHtml()}
                    ${this._getCheckboxButtonsHtml()}
                    ${this._getImportExportButtonsHtml()}
                    ${this._getEditDeleteButtonsHtml()}
                </div>
                
                ${this._getPageSizeSelectorHtml()}
            </div>
        `;
    }
    
    /**
     * 获取搜索框HTML
     * @returns {string} 搜索框HTML或空字符串
     * @private
     */
    _getSearchHtml() {
        return this.options.searchable ? `
            <div class="uk-margin-right">
                <i class="icon ion-md-search"></i>
            </div>
            <div class="uk-search uk-search-default uk-margin-right">
                <input class="uk-search-input" type="search" placeholder="${this.options.searchPlaceholder}" id="${this.table.id}-search">
            </div>
        ` : '';
    }
    
    /**
     * 获取复选框控制按钮组HTML
     * @returns {string} 按钮组HTML或空字符串
     * @private
     */
    _getCheckboxButtonsHtml() {
        return this.options.enableCheckbox ? `
            <div class="uk-flex uk-flex-middle uk-button-group">
                <button id="${this.table.id}-selectCurrentPage" class="uk-button uk-button-default uk-button-small" uk-tooltip="title: 当前页全选; pos: bottom;">
                    <i class="icon ion-md-checkmark"></i>
                </button>
                <button id="${this.table.id}-selectAll" class="uk-button uk-button-default uk-button-small" uk-tooltip="title: 全选所有; pos: bottom;">
                    <i class="icon ion-md-done-all"></i>
                </button>
                <button id="${this.table.id}-clearSelection" class="uk-button uk-button-default uk-button-small" uk-tooltip="title: 清除选择; pos: bottom;">
                    <i class="icon ion-md-close-circle"></i>
                </button>
            </div>
        ` : '';
    }
    
    /**
     * 获取导入导出创建按钮组HTML
     * @returns {string} 按钮组HTML或空字符串
     * @private
     */
    _getImportExportButtonsHtml() {
        return (this.options.showExport || this.options.showImport || this.options.showCreate) ? `
            <div class="uk-flex uk-flex-middle uk-button-group uk-margin-left">
                ${this.options.showExport && this.baseUrl ? `
                    <a href="${this.baseUrl.endsWith('/') ? this.baseUrl + 'export' : this.baseUrl + '/export'}" class="uk-button uk-button-secondary uk-button-small" uk-tooltip="title: 导出数据; pos: bottom;">
                        <i class="icon ion-md-download"></i>
                    </a>
                    ` : ''}
                    ${this.options.showImport && this.baseUrl ? `
                    <a href="${this.baseUrl.endsWith('/') ? this.baseUrl + 'import' : this.baseUrl + '/import'}" class="uk-button uk-button-primary uk-button-small" uk-tooltip="title: 导入数据; pos: bottom;">
                        <i class="icon ion-md-cloud-upload"></i>
                    </a>
                    ` : ''}
                    ${this.options.showCreate && this.baseUrl ? `
                    <a href="${this.baseUrl.endsWith('/') ? this.baseUrl + 'add' : this.baseUrl + '/add'}" class="uk-button uk-button-default uk-button-small" uk-tooltip="title: 创建新记录; pos: bottom;">
                        <i class="icon ion-md-add"></i>
                    </a>
                    ` : ''}
            </div>
        ` : '';
    }
    
    /**
     * 获取编辑删除按钮组HTML
     * @returns {string} 按钮组HTML或空字符串
     * @private
     */
    _getEditDeleteButtonsHtml() {
        return (this.options.showEdit || this.options.showDelete) && this.options.enableCheckbox ? `
            <div class="uk-flex uk-flex-middle uk-button-group uk-margin-left">
                <button id="${this.table.id}-edit" class="uk-button uk-button-primary uk-button-small" style="display: none;" uk-tooltip="title: 编辑选中记录; pos: bottom;">
                    <i class="icon ion-md-create"></i>
                    编辑
                </button>
                <button id="${this.table.id}-delete" class="uk-button uk-button-danger uk-button-small" style="display: none;" uk-tooltip="title: 删除选中记录; pos: bottom;">
                    <i class="icon ion-md-trash"></i>
                    删除
                </button>
            </div>
        ` : '';
    }
    
    /**
     * 获取页面大小选择器HTML
     * @returns {string} 选择器HTML或空字符串
     * @private
     */
    _getPageSizeSelectorHtml() {
        return this.options.showPagination ? `
            <div class="uk-flex uk-flex-middle">
                <select class="uk-select uk-form-small" id="${this.table.id}-pageSize" style="width: auto;" uk-tooltip="title: 每页显示条数; pos: top;">
                    ${this.options.pageSizes.map(size => 
                        `<option value="${size}" ${size === this.options.pageSize ? 'selected' : ''}>${size}条/页</option>`
                    ).join('')}
                </select>
            </div>
        ` : '';
    }
    
    /**
     * 创建信息面板
     * @param {HTMLElement} container - 容器元素
     * @private
     */
    _createInfoPanel(container) {
        const infoPanel = document.createElement('div');
        infoPanel.className = 'table-enhancer-info uk-margin uk-text-center';
        infoPanel.innerHTML = `<span id="${this.table.id}-info"></span>`;
        
        if (this.table.parentNode === container && this.table.nextSibling) {
            container.insertBefore(infoPanel, this.table.nextSibling);
        } else {
            container.appendChild(infoPanel);
        }
    }
    
    /**
     * 创建分页控件
     * @param {HTMLElement} container - 容器元素
     * @private
     */
    _createPagination(container) {
        const pagination = document.createElement('div');
        pagination.className = 'table-enhancer-pagination uk-margin uk-flex uk-flex-center';
        pagination.innerHTML = `<ul class="uk-pagination" id="${this.table.id}-pagination"></ul>`;
        
        if (this.table.parentNode === container && this.table.nextSibling) {
            container.insertBefore(pagination, this.table.nextSibling);
        } else {
            container.appendChild(pagination);
        }
    }

    /**
     * 绑定事件处理
     */
    bindEvents() {
        this._bindSearchEvent(); // 绑定搜索事件
        this._bindPageSizeEvent(); // 绑定分页大小事件
        this._bindSortEvent(); // 绑定排序事件
        this._bindCheckboxEvents(); // 绑定复选框相关事件
        this._bindEditDeleteEvents(); // 绑定编辑删除按钮事件
        this._bindRowClickEvents(); // 绑定行点击事件
    }
    
    /**
     * 绑定搜索事件
     * @private
     */
    _bindSearchEvent() {
        if (this.options.searchable) {
            const searchInput = dom.gid(`${this.table.id}-search`);
            searchInput.addEventListener('input', (e) => {
                this.search(e.target.value);
            });
        }
    }
    
    /**
     * 绑定分页大小事件
     * @private
     */
    _bindPageSizeEvent() {
        if (this.options.showPagination) {
            const pageSizeSelect = dom.gid(`${this.table.id}-pageSize`);
            pageSizeSelect.addEventListener('change', (e) => {
                this.options.pageSize = parseInt(e.target.value);
                this.currentPage = 1;
                this.render();
            });
        }
    }
    
    /**
     * 绑定排序事件
     * @private
     */
    _bindSortEvent() {
        if (this.options.sortable) {
            const headers = this.table.querySelectorAll('thead th');
            let dataColumnIndex = 0; // 数据列索引，不包含复选框列
            
            headers.forEach((header, index) => {
                // 跳过复选框列
                if (header.className === 'checkbox-header') {
                    header.style.cursor = 'default'; // 复选框列不可点击
                    return;
                }
                
                header.style.cursor = 'pointer';
                
                // 初始化时不添加图标，只在排序时添加
                dom.off(header, 'click'); // 先移除旧事件
                dom.on(header, 'click', () => {
                    this.sort(dataColumnIndex); // 传递数据列索引，不包含复选框列
                });
                
                dataColumnIndex++; // 只增加数据列的索引
            });
        }
    }
    
    /**
     * 绑定复选框相关事件
     * @private
     */
    _bindCheckboxEvents() {
        if (this.options.enableCheckbox) {
            // 全选所有数据
            const selectAllBtn = dom.gid(`${this.table.id}-selectAll`);
            if (selectAllBtn) {
                dom.on(selectAllBtn, 'click', () => {
                    this.toggleSelectAll();
                });
            }
            
            // 当前页全选
            const selectCurrentPageBtn = dom.gid(`${this.table.id}-selectCurrentPage`);
            if (selectCurrentPageBtn) {
                dom.on(selectCurrentPageBtn, 'click', () => {
                    this.toggleSelectCurrentPage();
                });
            }
            
            // 清除选择
            const clearSelectionBtn = dom.gid(`${this.table.id}-clearSelection`);
            if (clearSelectionBtn) {
                dom.on(clearSelectionBtn, 'click', () => {
                    this.clearSelection();
                });
            }
        }
    }
    
    /**
     * 绑定编辑删除按钮事件
     * @private
     */
    _bindEditDeleteEvents() {
        if (this.options.enableCheckbox) {
            // 编辑按钮
            this.editBtn = dom.gid(`${this.table.id}-edit`);
            if (this.editBtn) {
                dom.on(this.editBtn, 'click', () => {
                    this.handleEdit();
                });
            }
            
            // 删除按钮
            this.deleteBtn = dom.gid(`${this.table.id}-delete`);
            if (this.deleteBtn) {
                dom.on(this.deleteBtn, 'click', () => {
                    this.handleDelete();
                });
            }
        }
    }

    /**
     * 绑定行点击事件
     * 当用户点击表格行时跳转到展示页面
     * 根据系统URI规则：{tenant_id}/{entity_type}/view/{entity_id}
     * @private
     */
    _bindRowClickEvents() {
        const tbody = this.table.querySelector('tbody');
        if (tbody) {
            tbody.addEventListener('click', (e) => {
                // 找到被点击的行元素
                let row = e.target;
                while (row && row.tagName !== 'TR') {
                    row = row.parentElement;
                }
                
                if (row && row.tagName === 'TR') {
                    // 获取行ID
                    const rowId = row.getAttribute('data-row-id');
                    if (rowId) {
                        // 阻止事件冒泡，避免与复选框点击冲突
                        if (e.target.tagName !== 'INPUT' && e.target.type !== 'checkbox') {
                            this.handleRowClick(rowId);
                        }
                    }
                }
            });
        }
    }

    /**
     * 切换全选所有数据
     */
    toggleSelectAll() {
        this.allSelected = !this.allSelected;
        this.selectedRowIds.clear();
        
        if (this.allSelected) {
            // 全选所有数据
            this.filteredData.forEach(row => {
                this.selectedRowIds.add(row.id);
            });
        }
        
        this.render();
        this.updateEditDeleteButtons();
    }

    /**
     * 切换全选当前页数据
     */
    toggleSelectCurrentPage() {
        this.currentPageAllSelected = !this.currentPageAllSelected;
        
        // 获取当前页的数据
        const startIndex = (this.currentPage - 1) * this.options.pageSize;
        const endIndex = Math.min(startIndex + this.options.pageSize, this.filteredData.length);
        const currentPageData = this.filteredData.slice(startIndex, endIndex);
        
        if (this.currentPageAllSelected) {
            // 全选当前页
            currentPageData.forEach(row => {
                this.selectedRowIds.add(row.id);
            });
        } else {
            // 取消选中当前页
            currentPageData.forEach(row => {
                this.selectedRowIds.delete(row.id);
            });
        }
        
        this.render();
        this.updateEditDeleteButtons();
    }

    /**
     * 切换单行选择状态
     * @param {string} rowId - 行ID
     */
    toggleRowSelection(rowId) {
        if (this.selectedRowIds.has(rowId)) {
            this.selectedRowIds.delete(rowId);
            this.allSelected = false; // 如果取消选择任何行，取消全选状态
        } else {
            this.selectedRowIds.add(rowId);
            
            // 检查是否所有数据都被选中
            let allChecked = true;
            this.filteredData.forEach(row => {
                if (!this.selectedRowIds.has(row.id)) {
                    allChecked = false;
                }
            });
            this.allSelected = allChecked;
        }
        
        // 检查当前页是否全选
        const startIndex = (this.currentPage - 1) * this.options.pageSize;
        const endIndex = Math.min(startIndex + this.options.pageSize, this.filteredData.length);
        const currentPageData = this.filteredData.slice(startIndex, endIndex);
        
        let currentPageAllChecked = true;
        currentPageData.forEach(row => {
            if (!this.selectedRowIds.has(row.id)) {
                currentPageAllChecked = false;
            }
        });
        this.currentPageAllSelected = currentPageAllChecked;
        
        this.updateEditDeleteButtons();
    }

    /**
     * 获取选中的行数据
     * @returns {Array} 选中的行数据数组
     */
    getSelectedRows() {
        return this.filteredData.filter(row => this.selectedRowIds.has(row.id));
    }

    /**
     * 处理编辑操作
     * 当选中一行时触发编辑功能，跳转到编辑页面
     * 根据系统URI规则：{tenant_id}/{entity_type}/edit/{entity_id}
     */
    handleEdit() {
        const selectedCount = this.selectedRowIds.size;
        if (selectedCount === 1) {
            const rowId = Array.from(this.selectedRowIds)[0];
            if (this.baseUrl) {
            // 基于baseUrl生成编辑URL
            const editPath = this.baseUrl.endsWith('/') ?
                `${this.baseUrl}edit/` :
                `${this.baseUrl}/edit/`;
                window.location.href = editPath + rowId;
            } else {
                // 根据系统URI规则生成编辑URL
                // 格式：当前路径/edit/{entity_id}
                const currentPath = window.location.pathname;
                const editUrl = currentPath.endsWith('/') ? 
                    `${currentPath}edit/${rowId}` : 
                    `${currentPath}/edit/${rowId}`;
                window.location.href = editUrl;
            }
        }
    }

    /**
     * 处理删除操作
     * 当选中一行或多行时触发删除功能，显示确认对话框后执行删除
     * 根据系统URI规则：{tenant_id}/{entity_type}/delete?ids={entity_ids}
     */
    handleDelete() {
        const selectedCount = this.selectedRowIds.size;
        if (selectedCount > 0) {
            if (confirm(`确定要删除选中的 ${selectedCount} 条记录吗？`)) {
                const rowIds = Array.from(this.selectedRowIds).join(',');
                if (this.baseUrl) {
            // 基于baseUrl生成删除URL
            const deletePath = this.baseUrl.endsWith('/') ?
                `${this.baseUrl}delete` :
                `${this.baseUrl}/delete`;
                    window.location.href = `${deletePath}/${rowIds}`;
                } else {
                    // 根据系统URI规则生成删除URL
                    // 格式：当前路径/delete?ids={entity_ids}
                    const currentPath = window.location.pathname;
                    const deleteUrl = currentPath.endsWith('/') ? 
                        `${currentPath}delete/${rowIds}` : 
                        `${currentPath}/delete/${rowIds}`;
                    window.location.href = deleteUrl;
                }
            }
        }
    }

    /**
     * 处理行点击操作
     * 当用户点击表格行时跳转到展示页面
     * 根据系统URI规则：{tenant_id}/{entity_type}/view/{entity_id}
     * @param {string} rowId - 行ID
     */
    handleRowClick(rowId) {
        if (this.baseUrl) {
            // 基于baseUrl生成查看URL
            const viewPath = this.baseUrl.endsWith('/') ?
                `${this.baseUrl}view/about/`+ rowId :
                `${this.baseUrl}/view/about/`+ rowId;
            window.location.href = viewPath ;
        } else {
            // 根据系统URI规则生成查看URL
            // 格式：当前路径/view/{entity_id}
            const currentPath = window.location.pathname;
            const viewUrl = currentPath.endsWith('/') ? 
                `${currentPath}view/${rowId}` : 
                `${currentPath}/view/${rowId}`;
            window.location.href = viewUrl;
        }
    }

    /**
     * 更新编辑和删除按钮的显示状态
     * 根据选中行的数量动态显示/隐藏按钮
     */
    updateEditDeleteButtons() {
        const selectedCount = this.selectedRowIds.size;
        
        // 更新编辑按钮：选中单行时显示
        if (this.editBtn) {
            if (selectedCount === 1 && this.options.showEdit) {
                this.editBtn.style.display = '';
            } else {
                this.editBtn.style.display = 'none';
            }
        }
        
        // 更新删除按钮：选中一行或多行时显示
        if (this.deleteBtn) {
            if (selectedCount > 0 && this.options.showDelete) {
                this.deleteBtn.style.display = '';
            } else {
                this.deleteBtn.style.display = 'none';
            }
        }
    }

    /**
     * 清除所有选择
     */
    clearSelection() {
        this.selectedRowIds.clear();
        this.allSelected = false;
        this.currentPageAllSelected = false;
        this.render();
        this.updateEditDeleteButtons();
    }

    /**
     * 更新排序图标的状态
     * 移除所有已有的排序图标，并为当前排序列添加对应方向的图标
     */
    updateSortIcons() {
        const headers = this.table.querySelectorAll('thead th');
        
        // 移除所有已有的排序图标
        headers.forEach(header => {
            const existingIcon = header.querySelector('.table-sort-icon');
            if (existingIcon) {
                existingIcon.remove();
            }
        });
        
        // 只为当前排序的列添加图标
        if (this.sortColumn !== null && this.sortColumn >= 0) {
            // 计算实际的表头索引（考虑复选框列）
            let headerIndex = this.sortColumn;
            
            // 遍历表头，跳过复选框列，找到对应的数据列索引
            let dataColumnIndex = 0;
            for (let i = 0; i < headers.length && dataColumnIndex <= this.sortColumn; i++) {
                if (headers[i].className !== 'checkbox-header') {
                    if (dataColumnIndex === this.sortColumn) {
                        headerIndex = i;
                        break;
                    }
                    dataColumnIndex++;
                }
            }
            
            // 确保索引在有效范围内
            if (headerIndex < headers.length) {
                const header = headers[headerIndex];
                const sortIcon = document.createElement('span');
                sortIcon.className = 'table-sort-icon uk-margin-left';
                
                // 根据排序方向设置图标和提示
                if (this.sortDirection === 'asc') {
                    // 升序图标 (向上的箭头)
                    sortIcon.innerHTML = '<i class="icon ion-md-arrow-up" style="font-size: 0.7em;"></i>';
                    sortIcon.setAttribute('uk-tooltip', 'title: 升序; pos: bottom;');
                } else {
                    // 降序图标 (向下的箭头)
                    sortIcon.innerHTML = '<i class="icon ion-md-arrow-down" style="font-size: 0.7em;"></i>';
                    sortIcon.setAttribute('uk-tooltip', 'title: 降序; pos: bottom;');
                }
                header.appendChild(sortIcon);
            }
        }
    }

    /**
     * 根据查询条件搜索数据
     * @param {string} query - 搜索查询字符串
     */
    search(query) {
        const searchTerm = query.toLowerCase().trim();
        
        if (!searchTerm) {
            this.filteredData = [...this.originalData];
        } else {
            this.filteredData = this.originalData.filter(item => 
                item.cells.some(cell => cell.toLowerCase().includes(searchTerm))
            );
        }
        
        this.currentPage = 1; // 搜索后重置为第一页
        this.render();
    }

    /**
     * 对指定列进行排序
     * @param {number} columnIndex - 列索引
     */
    sort(columnIndex) {
        // 如果点击的是当前排序列，则切换排序方向；否则设置为升序
        if (this.sortColumn === columnIndex) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = columnIndex;
            this.sortDirection = 'asc';
        }
        
        // 执行排序
        this.filteredData.sort((a, b) => {
            const aVal = a.cells[columnIndex] || '';
            const bVal = b.cells[columnIndex] || '';
            
            // 尝试按数字排序
            const aNum = parseFloat(aVal);
            const bNum = parseFloat(bVal);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return this.sortDirection === 'asc' ? aNum - bNum : bNum - aNum;
            }
            
            // 按字符串排序
            if (this.sortDirection === 'asc') {
                return aVal.localeCompare(bVal);
            } else {
                return bVal.localeCompare(aVal);
            }
        });
        
        this.updateSortIcons();
        this.render();
    }

    /**
     * 渲染表格数据
     */
    render() {
        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';
        
        // 处理空数据情况
        if (this.filteredData.length === 0) {
            this._renderEmptyData(tbody);
            this.updateInfo();
            this.renderPagination();
            return;
        }
        
        // 处理表头（添加/移除复选框列，设置样式）
        this._updateTableHeader();
        
        // 渲染数据行
        this._renderTableRows(tbody);
        
        // 更新信息和分页
        this.updateInfo();
        this.renderPagination();
        
        // 更新排序图标，确保表头更新后排序图标也能正确显示
        this.updateSortIcons();
    }
    
    /**
     * 渲染空数据状态
     * @param {HTMLElement} tbody - 表格tbody元素
     * @private
     */
    _renderEmptyData(tbody) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="${this.options.enableCheckbox ? this.table.querySelectorAll('thead th').length + 1 : this.table.querySelectorAll('thead th').length}" class="uk-text-center">没有找到匹配的数据</td>`;
        tbody.appendChild(row);
    }
    
    /**
     * 更新表格表头
     * @private
     */
    _updateTableHeader() {
        const thead = this.table.querySelector('thead');
        let headerRow = thead.querySelector('tr');

        // 如果表头行不存在，则创建
        if (!headerRow) {
            headerRow = document.createElement('tr');
            thead.appendChild(headerRow);
        }

        // 清空现有表头内容（保留复选框列）
        const existingCheckboxHeader = headerRow.querySelector('.checkbox-header');
        headerRow.innerHTML = '';
        if (existingCheckboxHeader) {
            headerRow.appendChild(existingCheckboxHeader);
        }

        // 如果提供了fields配置，则从数组数据渲染表头
        if (this.options.fields) {
            // 添加复选框列（如果启用）
            if (this.options.enableCheckbox && !headerRow.querySelector('.checkbox-header')) {
                const checkboxHeader = document.createElement('th');
                checkboxHeader.className = 'checkbox-header';
                checkboxHeader.style.width = '40px';
                headerRow.appendChild(checkboxHeader);
            }

            // 添加数据列表头
            Object.values(this.options.fields).forEach(fieldConfig => {
                const th = document.createElement('th');
                th.textContent = fieldConfig.label || fieldConfig.name || '';
                th.style.whiteSpace = 'nowrap';
                th.style.overflow = 'visible';
                th.style.minWidth = '100px';
                headerRow.appendChild(th);
            });
        } else {
            // 从DOM表格中提取表头
            const headerCells = thead.querySelectorAll('th');
            headerCells.forEach(cell => {
                // 跳过复选框列
                if (cell.className !== 'checkbox-header') {
                    cell.style.whiteSpace = 'nowrap'; // 防止文本换行
                    cell.style.overflow = 'visible'; // 确保内容完全显示
                    cell.style.minWidth = '100px'; // 设置最小宽度
                }
            });

            // 如果启用了复选框，但表头还没有复选框列，则添加
            if (this.options.enableCheckbox && !headerRow.querySelector('.checkbox-header')) {
                const checkboxHeader = document.createElement('th');
                checkboxHeader.className = 'checkbox-header';
                checkboxHeader.style.width = '40px';
                headerRow.insertBefore(checkboxHeader, headerRow.firstChild);
            }

            // 如果禁用了复选框，但表头有复选框列，则移除
            if (!this.options.enableCheckbox) {
                const checkboxHeader = headerRow.querySelector('.checkbox-header');
                if (checkboxHeader) {
                    checkboxHeader.remove();
                }
            }
        }
        
        // 重新绑定排序事件，确保表头更新后排序功能依然有效
        this._bindSortEvent();
    }
    
    /**
     * 渲染表格数据行
     * @param {HTMLElement} tbody - 表格tbody元素
     * @private
     */
    _renderTableRows(tbody) {
        const startIndex = (this.currentPage - 1) * this.options.pageSize;
        const endIndex = Math.min(startIndex + this.options.pageSize, this.filteredData.length);
        
        // 渲染当前页的数据行
        for (let i = startIndex; i < endIndex; i++) {
            const rowData = this.filteredData[i];
            let row;
            
            if (rowData.element) {
                // 使用现有的DOM元素
                row = rowData.element.cloneNode(true);
            } else {
                // 创建新的行元素
                row = document.createElement('tr');
                row.className = 'clickable-row';
                
                // 创建单元格 - 支持两种数据格式：
                // 1. 旧格式：包含cells数组的对象
                // 2. 新格式：包含字段名和对应值的对象
                if (rowData.cells) {
                    // 旧格式：使用cells数组
                    rowData.cells.forEach(cellContent => {
                        const cell = document.createElement('td');
                        // 处理HTML内容
                        if (typeof cellContent === 'string' && cellContent.includes('<')) {
                            cell.innerHTML = cellContent;
                        } else {
                            cell.textContent = cellContent;
                        }
                        row.appendChild(cell);
                    });
                } else if (this.options.fields) {
                    // 新格式：根据fields配置提取值
                    Object.values(this.options.fields).forEach(fieldConfig => {
                        const fieldName = fieldConfig.name || '';
                        const cellValue = rowData[fieldName] || '';
                        const cell = document.createElement('td');
                        
                        // 处理HTML内容
                        if (typeof cellValue === 'string' && cellValue.includes('<')) {
                            cell.innerHTML = cellValue;
                        } else {
                            cell.textContent = cellValue;
                        }
                        row.appendChild(cell);
                    });
                }
            }
            
            row.dataset.rowId = rowData.id;
            
            // 如果启用了复选框，为每行添加复选框
            if (this.options.enableCheckbox) {
                const checkboxCell = document.createElement('td');
                checkboxCell.style.width = '40px';
                checkboxCell.innerHTML = `
                    <input type="checkbox" class="uk-checkbox table-checkbox" data-row-id="${rowData.id}" ${this.selectedRowIds.has(rowData.id) ? 'checked' : ''} uk-tooltip="title: 选择行; pos: right;">
                `;
                row.insertBefore(checkboxCell, row.firstChild);
            }
            
            tbody.appendChild(row);
        }
        
        // 绑定复选框事件
        if (this.options.enableCheckbox) {
            const checkboxes = tbody.querySelectorAll('.table-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const rowId = checkbox.getAttribute('data-row-id');
                    this.toggleRowSelection(rowId);
                });
            });
        }
    }

    /**
     * 更新信息面板显示
     */
    updateInfo() {
        const infoElement = dom.gid(`${this.table.id}-info`);
        if (infoElement) {
            const start = (this.currentPage - 1) * this.options.pageSize + 1;
            const end = Math.min(this.currentPage * this.options.pageSize, this.filteredData.length);
            infoElement.textContent = `显示 ${start}-${end} 条，共 ${this.filteredData.length} 条记录`;
        }
    }

    /**
     * 渲染分页控件
     */
    renderPagination() {
        if (!this.options.showPagination) return;
        
        const pagination = dom.gid(`${this.table.id}-pagination`);
        if (!pagination) return;
        
        const totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
        
        // 如果只有一页，不显示分页控件
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }
        
        // 生成分页HTML
        pagination.innerHTML = this._getPaginationHtml(totalPages);
        
        // 绑定分页事件
        this._bindPaginationEvents(pagination, totalPages);
    }
    
    /**
     * 获取分页控件HTML
     * @param {number} totalPages - 总页数
     * @returns {string} 分页HTML
     * @private
     */
    _getPaginationHtml(totalPages) {
        let html = '';
        
        // 上一页
        const prevDisabled = this.currentPage === 1 ? 'uk-disabled' : '';
        html += `<li class="${prevDisabled}"><a href="#" data-page="${this.currentPage - 1}" uk-tooltip="title: 上一页; pos: top;"><i class="icon ion-md-arrow-back"></i></a></li>`;
        
        // 页码
        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(totalPages, this.currentPage + 2);
        
        // 添加首页
        if (startPage > 1) {
            html += `<li><a href="#" data-page="1" uk-tooltip="title: 第 1 页; pos: top;">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="uk-disabled"><span>...</span></li>`;
            }
        }
        
        // 添加中间页码
        for (let i = startPage; i <= endPage; i++) {
            const active = i === this.currentPage ? 'uk-active' : '';
            html += `<li class="${active}"><a href="#" data-page="${i}" uk-tooltip="title: 第 ${i} 页; pos: top;">${i}</a></li>`;
        }
        
        // 添加末页
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="uk-disabled"><span>...</span></li>`;
            }
            html += `<li><a href="#" data-page="${totalPages}" uk-tooltip="title: 第 ${totalPages} 页; pos: top;">${totalPages}</a></li>`;
        }
        
        // 下一页
        const nextDisabled = this.currentPage === totalPages ? 'uk-disabled' : '';
        html += `<li class="${nextDisabled}"><a href="#" data-page="${this.currentPage + 1}" uk-tooltip="title: 下一页; pos: top;"><i class="icon ion-md-arrow-forward"></i></a></li>`;
        
        return html;
    }
    
    /**
     * 绑定分页控件事件
     * @param {HTMLElement} pagination - 分页元素
     * @param {number} totalPages - 总页数
     * @private
     */
    _bindPaginationEvents(pagination, totalPages) {
        pagination.querySelectorAll('a').forEach(link => {
            dom.on(link, 'click', (e) => {
                e.preventDefault();
                const page = parseInt(e.target.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages) {
                    this.currentPage = page;
                    this.render();
                }
            });
        });
    }

    // 公共方法
    /**
     * 刷新表格数据
     */
    refresh() {
        this.extractData();
        this.filteredData = [...this.originalData];
        this.currentPage = 1;
        // 刷新时保持选中状态
        this.render();
    }

    /**
     * 获取当前过滤后的数据
     * @returns {Array} 数据数组
     */
    getData() {
        return this.filteredData;
    }

    /**
     * 跳转到指定页码
     * @param {number} page - 目标页码
     */
    goToPage(page) {
        const totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.render();
        }
    }
}

// 使用示例：
// const enhancer = new TableRender('myTable', {
//     pageSize: 20,
//     searchable: true,
//     sortable: true
// });

// 全局注册
if (typeof window !== 'undefined') {
    window.TableRender = TableRender;
}