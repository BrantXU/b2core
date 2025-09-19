/**
 * KanbanRender类 - 看板渲染器，将列表数据按照看板方式进行渲染
 * 支持按指定字段分类，状态存储到浏览器缓存
 */
class KanbanRender {
    /**
     * 构造函数
     * @param {string} containerId - 容器元素的ID
     * @param {Object} config - 配置对象，包含实体数据和配置信息
     */
    constructor(containerId, config = {}) {
        // 容器引用
        this.container = document.getElementById(containerId);
        if (!this.container) {
            throw new Error(`Element with id "${containerId}" not found`);
        }
        
        // 配置信息
        this.config = config;
        this.entities = config.entities || [];
        this.item = config.item || {};
        // 从item中筛选出select和select_new类型的控件作为statusFields
        this.statusFields = this._filterStatusFields(config.statusFields || {}, this.item);
        this.selectedStatusField = config.selectedStatusField || '';
        this.entityType = config.entityType || '';
        this.baseUrl = config.baseUrl || '';
        this.objectMenuKey = config.objectMenuKey || '';
        this.objectId = config.objectId || '';
        
        // 选项配置
        this.options = this._getDefaultOptions(config);
        
        // 数据存储
        this.originalData = []; // 原始数据
        this.filteredData = []; // 过滤后的数据
        this.kanbanData = {}; // 按分类分组的数据
        
        // 分类字段
        this.categoryField = this.selectedStatusField || 'status';
        
        // 初始化
        this.init();
    }

    /**
     * 从item中筛选出select和select_new类型的控件
     * @param {Object} statusFields - 原始状态字段
     * @param {Object} item - 项目配置
     * @returns {Object} 筛选后的状态字段
     * @private
     */
    _filterStatusFields(statusFields, item) {
        // 如果已经提供了statusFields（后端已经筛选过了），直接使用
        if (statusFields && Object.keys(statusFields).length > 0) {
            return statusFields;
        }
        
        // 否则从item中筛选select和select_new类型的控件
        const filteredFields = {};
        // 根据用户提供的item结构，item本身就是一个包含字段的对象，而不是有fields属性的对象
        if (item) {
            Object.entries(item).forEach(([fieldName, fieldConfig]) => {
                if (fieldConfig && (fieldConfig.type === 'select' || fieldConfig.type === 'select_new')) {
                    filteredFields[fieldName] = fieldConfig;
                }
            });
        }
        return filteredFields;
    }

    /**
     * 获取默认选项配置
     * @param {Object} customOptions - 自定义选项
     * @returns {Object} 合并后的选项配置
     * @private
     */
    _getDefaultOptions(customOptions) {
        return {
            defaultCategories: customOptions.defaultCategories || this._getDefaultCategories(),
            ...customOptions
        };
    }

    /**
     * 获取默认分类
     * @returns {Array} 默认分类数组
     * @private
     */
    _getDefaultCategories() {
        // 如果没有指定defaultCategories，则使用第一个select或select_new字段的值作为defaultCategories
        if (this.statusFields && Object.keys(this.statusFields).length > 0) {
            // 查找第一个select或select_new类型的字段
            const firstSelectField = Object.values(this.statusFields).find(field => 
                field && (field.type === 'select' || field.type === 'select_new') && field.options
            );
            
            if (firstSelectField) {
                return Object.keys(firstSelectField.options);
            }
        }
        return [];
    }

    /**
     * 初始化看板渲染器
     */
    init() {
        this.extractData(); // 提取数据
        this.createControls(); // 创建控制面板
        this.bindEvents(); // 绑定事件处理
        
        // 如果有预设的selectedStatusField，设置为当前分类字段
        if (this.selectedStatusField) {
            this.setCategoryField(this.selectedStatusField);
        } else 
        {
            const firstStatusField = Object.keys(this.statusFields)[0];
            this.setCategoryField(firstStatusField);
        }
        
        this.loadFromCache(); // 从缓存加载状态
        this.render(); // 首次渲染看板
    }

    /**
     * 从配置中提取数据
     */
    extractData() {
        if (this.entities && Array.isArray(this.entities)) {
            // 使用提供的实体数据
            this.originalData = this.entities.map((item, index) => ({
                id: item.id || `item_${index}`,
                ...item
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
            // 兼容不同的数据结构，支持 item.fields 和直接属性访问
            const category = (item.fields && item.fields[this.categoryField]) || item[this.categoryField] || '未分类';
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
        
        // 创建控制面板容器
        const controlPanel = document.createElement('div');
        controlPanel.className = 'uk-margin-bottom';
        controlPanel.setAttribute('uk-grid', '');
        
        // 创建卡片容器
        const card = document.createElement('div');
        card.className = 'uk-width-1-1';
        
        const cardBody = document.createElement('div');
        cardBody.className = 'uk-card uk-card-default uk-card-body';
        
        // 创建内部网格
        const innerGrid = document.createElement('div');
        innerGrid.setAttribute('uk-grid', '');
        
        // 创建分类字段选择器
        const statusFieldColumn = document.createElement('div');
        statusFieldColumn.className = 'uk-width-1-3@m';
        
        const statusFieldSelect = document.createElement('select');
        statusFieldSelect.id = 'statusFieldSelect';
        statusFieldSelect.className = 'uk-select';
        
        // 添加分类字段选项
        if (this.statusFields && Object.keys(this.statusFields).length > 0) {
            Object.entries(this.statusFields).forEach(([fieldName, fieldConfig]) => {
                const option = document.createElement('option');
                option.value = fieldName;
                option.textContent = fieldConfig.name || fieldName;
                if (fieldName === this.selectedStatusField) {
                    option.selected = true;
                }
                statusFieldSelect.appendChild(option);
            });
        }
        
        statusFieldColumn.appendChild(statusFieldSelect);
        
        // 创建搜索框
        const searchColumn = document.createElement('div');
        searchColumn.className = 'uk-width-1-3@m';
        
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.id = 'searchInput';
        searchInput.className = 'uk-input';
        searchInput.placeholder = '搜索...';
        
        searchColumn.appendChild(searchInput);
        
        // 创建按钮组
        const buttonColumn = document.createElement('div');
        buttonColumn.className = 'uk-width-1-3@m uk-text-right';
        
        // 刷新按钮
        const refreshBtn = document.createElement('button');
        refreshBtn.id = 'refreshBtn';
        refreshBtn.className = 'uk-button uk-button-primary';
        refreshBtn.innerHTML = '<i class="icon ion-md-refresh"></i>';
        
        // 添加项目按钮
        const addItemBtn = document.createElement('button');
        addItemBtn.id = 'addItemBtn';
        addItemBtn.className = 'uk-button uk-button-primary';
        addItemBtn.innerHTML = '<i class="icon ion-md-add"></i>';
        
        buttonColumn.appendChild(refreshBtn);
        buttonColumn.appendChild(addItemBtn);
        
        // 组装控制面板
        innerGrid.appendChild(statusFieldColumn);
        innerGrid.appendChild(searchColumn);
        innerGrid.appendChild(buttonColumn);
        
        cardBody.appendChild(innerGrid);
        card.appendChild(cardBody);
        controlPanel.appendChild(card);
        
        // 将控制面板插入到容器中
        container.appendChild(controlPanel);
    }

    /**
     * 绑定事件处理
     */
    bindEvents() {
        // 绑定分类字段切换事件
        const statusFieldSelect = document.getElementById('statusFieldSelect');
        if (statusFieldSelect) {
            statusFieldSelect.addEventListener('change', (e) => {
                this.setCategoryField(e.target.value);
            });
        }
        
        // 绑定搜索事件
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.search(e.target.value);
            });
        }
        
        // 绑定刷新按钮事件
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refresh();
            });
        }
        
        // 绑定添加项目按钮事件
        const addItemBtn = document.getElementById('addItemBtn');
        if (addItemBtn) {
            addItemBtn.addEventListener('click', () => {
                // 这里可以添加创建新项目的逻辑
                alert('添加新项目功能待实现');
            });
        }
        
        // 绑定拖拽事件
        // 拖拽功能将在渲染后通过Sortable.js实现
    }

    /**
     * 保存看板状态到浏览器缓存
     */
    saveToCache() {
        // 默认启用缓存
        const cacheData = {
            kanbanData: this.kanbanData,
            categoryField: this.categoryField,
            timestamp: new Date().toISOString()
        };
        
        const cacheKey = `kanban_${this.entityType}_state`;
        localStorage.setItem(cacheKey, JSON.stringify(cacheData));
    }

    /**
     * 从浏览器缓存加载看板状态
     */
    loadFromCache() {
        // 默认启用缓存
        const cacheKey = `kanban_${this.entityType}_state`;
        const cachedData = localStorage.getItem(cacheKey);
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
        // 默认启用缓存
        const cacheKey = `kanban_${this.entityType}_state`;
        localStorage.removeItem(cacheKey);
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
                Object.values(item).some(value => 
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
        kanbanContainer.id = 'kanbanContainer';
        
        // 渲染每个分类列
        Object.entries(this.kanbanData).forEach(([category, items]) => {
            const column = this._createKanbanColumn(category, items);
            kanbanContainer.appendChild(column);
        });
        
        // 将看板容器插入到控制面板之后
        this.container.appendChild(kanbanContainer);
        
        // 初始化拖拽功能
        // 默认启用拖拽
        this._initDragAndDrop();
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
        column.className = 'kanban-column uk-width-1-4@m uk-width-1-2@s';
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
        // 默认启用拖拽
        card.draggable = true;
        
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
        const title = item.title || item.name || '未命名项目';
        
        return `
            <div class="uk-card-body">
                <h4 class="uk-card-title uk-margin-remove">${title}</h4>
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
        const meta = [];
        
        // 添加优先级
        if (item.priority) {
            meta.push(`<span class="uk-label">${item.priority}</span>`);
        }
        
        // 添加截止日期
        if (item.dueDate) {
            meta.push(`<span>${item.dueDate}</span>`);
        }
        
        // 添加负责人
        if (item.assignee) {
            meta.push(`<span>${item.assignee}</span>`);
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
        this.saveToCache();
        
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
                originalItem[this.categoryField] = toCategory;
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
                `${this.baseUrl}view/about/${itemId}` :
                `${this.baseUrl}/view/about/${itemId}`;
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
        // 清除缓存
        this.clearCache();
        
        // 重新加载数据
        this.extractData();
        
        // 重新渲染
        this.render();
    }

    /**
     * 获取看板数据
     * @returns {Object} 看板数据
     */
    getData() {
        return this.kanbanData;
    }

    /**
     * 设置分类字段
     * @param {string} field - 分类字段名
     */
    setCategoryField(field) {
        this.categoryField = field;
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
     * 删除分类
     * @param {string} categoryName - 分类名称
     */
    removeCategory(categoryName) {
        if (this.kanbanData[categoryName]) {
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