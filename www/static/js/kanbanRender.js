/**
 * KanbanRender类 - 看板渲染器，将列表数据按照看板方式进行渲染
 * 支持按指定字段分类，状态存储到浏览器缓存
 */
class KanbanRender {
    /**
     * 构造函数
     * @param {string} containerId - 容器元素的ID
     * @param {Object} options - 配置选项
     * @param {string} baseUrl - 基础URL，所有操作URL都基于此生成
     */
    constructor(containerId, options = {}, baseUrl = '') {
        // 容器引用
        this.container = dom.gid(containerId);
        if (!this.container) {
            throw new Error(`Element with id "${containerId}" not found`);
        }
        
        // 基础URL
        this.baseUrl = baseUrl;
        
        // 配置选项
        this.options = this._getDefaultOptions(options);
        
        // 数据存储
        this.originalData = []; // 原始数据
        this.filteredData = []; // 过滤后的数据
        this.kanbanData = {}; // 按分类分组的数据
        
        // 分类字段
        this.categoryField = this.options.categoryField || 'status';
        
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
            categoryField: 'status', // 分类字段名
            defaultCategories: ['待处理', '进行中', '已完成'], // 默认分类
            showSearch: true, // 是否显示搜索框
            showCreate: true, // 是否显示创建按钮
            searchPlaceholder: '搜索看板内容...', // 搜索框占位符
            enableDrag: true, // 是否启用拖拽功能
            enableCache: true, // 是否启用浏览器缓存
            cacheKey: 'kanban_state', // 缓存键名
            ...customOptions
        };
    }

    /**
     * 初始化看板渲染器
     */
    init() {
        this.extractData(); // 提取数据
        this.createControls(); // 创建控制面板
        this.bindEvents(); // 绑定事件处理
        this.loadFromCache(); // 从缓存加载状态
        this.render(); // 首次渲染看板
    }

    /**
     * 从表格中提取数据或使用提供的JS数组数据
     */
    extractData() {
        if (this.options.data && Array.isArray(this.options.data)) {
            // 使用提供的JS数组数据
            this.originalData = this.options.data.map((item, index) => ({
                id: item.id || `item_${index}`,
                element: null,
                fields: this.options.fields ? 
                    Object.keys(this.options.fields).reduce((acc, fieldName) => {
                        acc[fieldName] = item[fieldName] || '';
                        return acc;
                    }, {}) :
                    item
            }));
        }
        
        // 初始化过滤数据为原始数据
        this.filteredData = [...this.originalData];
        
        // 按分类分组数据
        this.groupDataByCategory();
    }

    /**
     * 按分类字段分组数据
     */
    groupDataByCategory() {
        this.kanbanData = {};
        
        // 初始化默认分类
        if (this.options.defaultCategories) {
            this.options.defaultCategories.forEach(category => {
                this.kanbanData[category] = [];
            });
        }
        
        // 分组数据
        this.filteredData.forEach(item => {
            const category = item.fields[this.categoryField] || item[this.categoryField] || '未分类';
            if (!this.kanbanData[category]) {
                this.kanbanData[category] = [];
            }
            this.kanbanData[category].push(item);
        });
    }

    /**
     * 创建看板上方的控制面板
     */
    createControls() {
        const container = this.container;
        
        // 创建控制面板
        const controlPanel = document.createElement('div');
        controlPanel.className = 'kanban-controls uk-margin';
        controlPanel.innerHTML = this._getControlPanelHtml();
        
        // 将控制面板插入到容器中
        container.appendChild(controlPanel);
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
                    ${this._getCreateButtonHtml()}
                </div>
                
                ${this._getCacheControlsHtml()}
            </div>
        `;
    }
    
    /**
     * 获取搜索框HTML
     * @returns {string} 搜索框HTML或空字符串
     * @private
     */
    _getSearchHtml() {
        return this.options.showSearch ? `
            <div class="uk-margin-right">
                <i class="icon ion-md-search"></i>
            </div>
            <div class="uk-search uk-search-default uk-margin-right">
                <input class="uk-search-input" type="search" placeholder="${this.options.searchPlaceholder}" id="${this.container.id}-search">
            </div>
        ` : '';
    }
    
    /**
     * 获取创建按钮HTML
     * @returns {string} 创建按钮HTML或空字符串
     * @private
     */
    _getCreateButtonHtml() {
        return this.options.showCreate && this.baseUrl ? `
            <div class="uk-margin-right">
                <a href="${this.baseUrl.endsWith('/') ? this.baseUrl + 'add' : this.baseUrl + '/add'}" class="uk-button uk-button-primary uk-button-small" uk-tooltip="title: 创建新项目; pos: bottom;">
                    <i class="icon ion-md-add"></i>
                    新建
                </a>
            </div>
        ` : '';
    }
    
    /**
     * 获取缓存控制按钮HTML
     * @returns {string} 缓存控制按钮HTML或空字符串
     * @private
     */
    _getCacheControlsHtml() {
        return this.options.enableCache ? `
            <div class="uk-flex uk-flex-middle uk-button-group">
                <button id="${this.container.id}-saveCache" class="uk-button uk-button-default uk-button-small" uk-tooltip="title: 保存看板状态; pos: bottom;">
                    <i class="icon ion-md-save"></i>
                </button>
                <button id="${this.container.id}-loadCache" class="uk-button uk-button-default uk-button-small" uk-tooltip="title: 加载看板状态; pos: bottom;">
                    <i class="icon ion-md-refresh"></i>
                </button>
                <button id="${this.container.id}-clearCache" class="uk-button uk-button-default uk-button-small" uk-tooltip="title: 清除看板状态; pos: bottom;">
                    <i class="icon ion-md-close-circle"></i>
                </button>
            </div>
        ` : '';
    }

    /**
     * 绑定事件处理
     */
    bindEvents() {
        this._bindSearchEvent(); // 绑定搜索事件
        this._bindCacheEvents(); // 绑定缓存事件
        this._bindDragEvents(); // 绑定拖拽事件
    }
    
    /**
     * 绑定搜索事件
     * @private
     */
    _bindSearchEvent() {
        if (this.options.showSearch) {
            const searchInput = dom.gid(`${this.container.id}-search`);
            searchInput.addEventListener('input', (e) => {
                this.search(e.target.value);
            });
        }
    }
    
    /**
     * 绑定缓存事件
     * @private
     */
    _bindCacheEvents() {
        if (this.options.enableCache) {
            // 保存缓存
            const saveCacheBtn = dom.gid(`${this.container.id}-saveCache`);
            if (saveCacheBtn) {
                dom.on(saveCacheBtn, 'click', () => {
                    this.saveToCache();
                    UIkit.notification('看板状态已保存', {status: 'success'});
                });
            }
            
            // 加载缓存
            const loadCacheBtn = dom.gid(`${this.container.id}-loadCache`);
            if (loadCacheBtn) {
                dom.on(loadCacheBtn, 'click', () => {
                    this.loadFromCache();
                    this.render();
                    UIkit.notification('看板状态已加载', {status: 'success'});
                });
            }
            
            // 清除缓存
            const clearCacheBtn = dom.gid(`${this.container.id}-clearCache`);
            if (clearCacheBtn) {
                dom.on(clearCacheBtn, 'click', () => {
                    this.clearCache();
                    this.render();
                    UIkit.notification('看板状态已清除', {status: 'success'});
                });
            }
        }
    }
    
    /**
     * 绑定拖拽事件
     * @private
     */
    _bindDragEvents() {
        if (this.options.enableDrag) {
            // 拖拽功能将在渲染后通过Sortable.js实现
        }
    }

    /**
     * 保存看板状态到浏览器缓存
     */
    saveToCache() {
        if (!this.options.enableCache) return;
        
        const cacheData = {
            kanbanData: this.kanbanData,
            categoryField: this.categoryField,
            timestamp: new Date().toISOString()
        };
        
        localStorage.setItem(this.options.cacheKey, JSON.stringify(cacheData));
    }

    /**
     * 从浏览器缓存加载看板状态
     */
    loadFromCache() {
        if (!this.options.enableCache) return;
        
        const cachedData = localStorage.getItem(this.options.cacheKey);
        if (cachedData) {
            try {
                const parsedData = JSON.parse(cachedData);
                if (parsedData.kanbanData && parsedData.categoryField === this.categoryField) {
                    this.kanbanData = parsedData.kanbanData;
                }
            } catch (error) {
                console.warn('Failed to load kanban state from cache:', error);
            }
        }
    }

    /**
     * 清除浏览器缓存
     */
    clearCache() {
        if (!this.options.enableCache) return;
        
        localStorage.removeItem(this.options.cacheKey);
        // 重新分组数据
        this.groupDataByCategory();
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
                Object.values(item.fields || item).some(value => 
                    value && value.toString().toLowerCase().includes(searchTerm)
                )
            );
        }
        
        // 重新分组数据
        this.groupDataByCategory();
        this.render();
    }

    /**
     * 渲染看板
     */
    render() {
        // 只清空看板内容部分，保留控制面板
        const existingKanbanContainer = this.container.querySelector('.kanban-container');
        if (existingKanbanContainer) {
            existingKanbanContainer.remove();
        }
        
        // 创建看板容器
        const kanbanContainer = document.createElement('div');
        kanbanContainer.className = 'kanban-container uk-grid uk-grid-small uk-grid-match';
        kanbanContainer.setAttribute('uk-grid', '');
        
        // 渲染每个分类列
        Object.entries(this.kanbanData).forEach(([category, items]) => {
            const column = this._createKanbanColumn(category, items);
            kanbanContainer.appendChild(column);
        });
        
        // 将看板容器插入到控制面板之后
        const controlPanel = this.container.querySelector('.kanban-controls');
        if (controlPanel && controlPanel.nextSibling) {
            this.container.insertBefore(kanbanContainer, controlPanel.nextSibling);
        } else {
            this.container.appendChild(kanbanContainer);
        }
        
        // 初始化拖拽功能
        if (this.options.enableDrag) {
            this._initDragAndDrop();
        }
    }
    
    /**
     * 创建看板列
     * @param {string} category - 分类名称
     * @param {Array} items - 该分类下的项目
     * @returns {HTMLElement} 看板列元素
     * @private
     */
    _createKanbanColumn(category, items) {
        const column = document.createElement('div');
        column.className = 'kanban-column uk-width-1-3@m uk-width-1-1@s';
        column.dataset.category = category;
        
        const columnHeader = document.createElement('div');
        columnHeader.className = 'kanban-column-header uk-card uk-card-default uk-card-small';
        columnHeader.innerHTML = `
            <div class="uk-card-header">
                <h3 class="uk-card-title uk-margin-remove">${category}</h3>
                <span class="uk-badge">${items.length}</span>
            </div>
        `;
        
        const columnBody = document.createElement('div');
        columnBody.className = 'kanban-column-body uk-card-body';
        
        // 渲染项目卡片
        items.forEach(item => {
            const card = this._createKanbanCard(item);
            columnBody.appendChild(card);
        });
        
        // 添加空状态提示
        if (items.length === 0) {
            columnBody.innerHTML = '<div class="uk-text-muted uk-text-center">暂无项目</div>';
        }
        
        column.appendChild(columnHeader);
        column.appendChild(columnBody);
        
        return column;
    }
    
    /**
     * 创建看板卡片
     * @param {Object} item - 项目数据
     * @returns {HTMLElement} 看板卡片元素
     * @private
     */
    _createKanbanCard(item) {
        const card = document.createElement('div');
        card.className = 'kanban-card uk-card uk-card-default uk-card-small uk-card-hover';
        card.dataset.itemId = item.id;
        card.draggable = this.options.enableDrag;
        
        // 卡片内容
        const cardContent = this._getCardContent(item);
        card.innerHTML = cardContent;
        
        // 绑定点击事件
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.kanban-card-actions')) {
                this.handleCardClick(item.id);
            }
        });
        
        return card;
    }
    
    /**
     * 获取卡片内容HTML
     * @param {Object} item - 项目数据
     * @returns {string} 卡片HTML内容
     * @private
     */
    _getCardContent(item) {
        const fields = item.fields || item;
        const title = fields.title || fields.name || '未命名项目';
        const description = fields.description || fields.content || '';
        
        return `
            <div class="uk-card-body">
                <h4 class="uk-card-title uk-margin-remove">${title}</h4>
                ${description ? `<p class="uk-text-muted uk-margin-small-top">${description.substring(0, 100)}${description.length > 100 ? '...' : ''}</p>` : ''}
                
                <div class="kanban-card-meta uk-flex uk-flex-between uk-flex-middle uk-margin-top">
                    <div class="uk-text-small uk-text-muted">
                        ${this._getCardMeta(item)}
                    </div>
                    
                    <div class="kanban-card-actions">
                        <button class="uk-button uk-button-default uk-button-small" onclick="event.stopPropagation();" uk-tooltip="title: 编辑; pos: top;">
                            <i class="icon ion-md-create"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * 获取卡片元数据
     * @param {Object} item - 项目数据
     * @returns {string} 元数据HTML
     * @private
     */
    _getCardMeta(item) {
        const fields = item.fields || item;
        const meta = [];
        
        // 添加优先级
        if (fields.priority) {
            meta.push(`<span class="uk-label">${fields.priority}</span>`);
        }
        
        // 添加截止日期
        if (fields.dueDate) {
            meta.push(`<span>${fields.dueDate}</span>`);
        }
        
        // 添加负责人
        if (fields.assignee) {
            meta.push(`<span>${fields.assignee}</span>`);
        }
        
        return meta.join(' • ');
    }

    /**
     * 初始化拖拽功能
     * @private
     */
    _initDragAndDrop() {
        // 检查是否已加载Sortable.js
        if (typeof Sortable === 'undefined') {
            console.warn('Sortable.js is not loaded. Drag and drop functionality will not work.');
            return;
        }
        
        const columns = this.container.querySelectorAll('.kanban-column-body');
        
        columns.forEach(column => {
            new Sortable(column, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'kanban-ghost',
                chosenClass: 'kanban-chosen',
                dragClass: 'kanban-drag',
                onEnd: (evt) => {
                    this._handleDragEnd(evt);
                }
            });
        });
    }
    
    /**
     * 处理拖拽结束事件
     * @param {Object} evt - 拖拽事件
     * @private
     */
    _handleDragEnd(evt) {
        const itemId = evt.item.dataset.itemId;
        const fromCategory = evt.from.parentElement.dataset.category;
        const toCategory = evt.to.parentElement.dataset.category;
        
        // 更新数据
        this._moveItemToCategory(itemId, fromCategory, toCategory);
        
        // 保存到缓存
        if (this.options.enableCache) {
            this.saveToCache();
        }
        
        // 重新渲染看板
        this.render();
    }
    
    /**
     * 将项目移动到指定分类
     * @param {string} itemId - 项目ID
     * @param {string} fromCategory - 原分类
     * @param {string} toCategory - 目标分类
     * @private
     */
    _moveItemToCategory(itemId, fromCategory, toCategory) {
        // 查找项目
        let item = null;
        if (this.kanbanData[fromCategory]) {
            const index = this.kanbanData[fromCategory].findIndex(i => i.id === itemId);
            if (index !== -1) {
                item = this.kanbanData[fromCategory][index];
                this.kanbanData[fromCategory].splice(index, 1);
            }
        }
        
        if (item) {
            // 添加到目标分类
            if (!this.kanbanData[toCategory]) {
                this.kanbanData[toCategory] = [];
            }
            this.kanbanData[toCategory].push(item);
            
            // 更新原始数据中的分类字段
            const originalItem = this.originalData.find(i => i.id === itemId);
            if (originalItem) {
                if (originalItem.fields) {
                    originalItem.fields[this.categoryField] = toCategory;
                } else {
                    originalItem[this.categoryField] = toCategory;
                }
            }
        }
    }

    /**
     * 处理卡片点击操作
     * @param {string} itemId - 项目ID
     */
    handleCardClick(itemId) {
        if (this.baseUrl) {
            // 基于baseUrl生成查看URL
            const viewPath = this.baseUrl.endsWith('/') ?
                `${this.baseUrl}view/about/`+ itemId :
                `${this.baseUrl}/view/about/`+ itemId;
            window.location.href = viewPath;
        } else {
            // 根据系统URI规则生成查看URL
            const currentPath = window.location.pathname;
            const viewUrl = currentPath.endsWith('/') ? 
                `${currentPath}view/${itemId}` : 
                `${currentPath}/view/${itemId}`;
            window.location.href = viewUrl;
        }
    }

    // 公共方法
    /**
     * 刷新看板数据
     */
    refresh() {
        this.extractData();
        this.filteredData = [...this.originalData];
        this.groupDataByCategory();
        this.render();
    }

    /**
     * 获取当前看板数据
     * @returns {Object} 看板数据对象
     */
    getData() {
        return this.kanbanData;
    }

    /**
     * 设置分类字段
     * @param {string} fieldName - 分类字段名
     */
    setCategoryField(fieldName) {
        this.categoryField = fieldName;
        this.groupDataByCategory();
        this.render();
    }

    /**
     * 添加新分类
     * @param {string} categoryName - 分类名称
     */
    addCategory(categoryName) {
        if (!this.kanbanData[categoryName]) {
            this.kanbanData[categoryName] = [];
            this.render();
        }
    }

    /**
     * 移除分类
     * @param {string} categoryName - 分类名称
     */
    removeCategory(categoryName) {
        if (this.kanbanData[categoryName]) {
            // 将分类中的项目移动到默认分类
            const defaultCategory = this.options.defaultCategories[0] || '未分类';
            this.kanbanData[categoryName].forEach(item => {
                this._moveItemToCategory(item.id, categoryName, defaultCategory);
            });
            
            delete this.kanbanData[categoryName];
            this.render();
        }
    }
}

// 全局注册
if (typeof window !== 'undefined') {
    window.KanbanRender = KanbanRender;
}

// 使用示例：
// const kanban = new KanbanRender('myKanban', {
//     data: [],
//     categoryField: 'status',
//     defaultCategories: ['待处理', '进行中', '已完成'],
//     enableDrag: true,
//     enableCache: true
// });