/**
 * aPaas平台Layout配置编辑功能
 * 提供完整的控件管理功能，包括拖拽排序、属性编辑、AJAX数据交互
 */

class LayoutEditor {
    constructor(options = {}) {
        this.configId = options.configId || '';
        this.tenantId = options.tenantId || 'default';
        this.selectedWidgetIndex = null;
        this.widgetTypes = this.getWidgetTypesConfig();
        this.init();
    }

    /**
     * 初始化编辑器
     */
    init() {
        this.bindEvents();
        this.loadInitialData();
    }

    /**
     * 获取控件类型配置
     */
    getWidgetTypesConfig() {
        return {
            'text': {
                name: '文本控件',
                icon: '<i class="icon ion-ios-document"></i>',
                properties: [
                    {name: 'text', label: '显示文本', type: 'textarea', placeholder: '输入要显示的文本内容'}
                ]
            },
            'chart': {
                name: '图表控件',
                icon: '<i class="icon ion-ios-pie"></i>',
                properties: [
                    {name: 'type', label: '图表类型', type: 'select', 
                     options: [
                         {value: 'line', text: '折线图'},
                         {value: 'bar', text: '柱状图'},
                         {value: 'pie', text: '饼图'}
                     ]},
                    {name: 'data', label: '图表数据', type: 'textarea', placeholder: '输入图表数据（JSON格式）'}
                ]
            },
            'table': {
                name: '表格控件',
                icon: '<i class="icon ion-ios-grid"></i>',
                properties: [
                    {name: 'columns', label: '列配置', type: 'textarea', placeholder: '输入表格列配置（JSON格式）'},
                    {name: 'data', label: '表格数据', type: 'textarea', placeholder: '输入表格数据（JSON格式）'}
                ]
            }
        };
    }

    /**
     * 初始化拖拽排序
     */
    initSortable() {
        // 初始化UIkit拖拽排序
        const sortable = UIkit.sortable(document.getElementById('widgetList'), {
            handle: '.uk-sortable-handle',
            animation: 150
        });
        
        // 直接绑定拖拽结束事件
        if (sortable) {
            sortable.$el.addEventListener('stop', () => {
                this.saveWidgetOrder();
            });
        }
    }

    /**
     * 绑定事件处理
     */
    bindEvents() {
        // 实时属性变更事件
        document.addEventListener('change', (e) => {
            if (e.target.closest('#propertyPanel')) {
                this.handlePropertyChange(e.target);
            }
            
            if (e.target.name === 'type') {
                this.updatePropertyFields();
            }
        });

        // 输入框实时更新事件
        document.addEventListener('input', (e) => {
            if (e.target.closest('#propertyPanel') && 
                (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
                this.handlePropertyChange(e.target);
            }
        });
    }

    /**
     * 获取控件类型的中文名称
     */
    getWidgetTypeName(type) {
        return this.widgetTypes[type]?.name || type;
    }

    /**
     * 获取控件类型的图标
     */
    getWidgetTypeIcon(type) {
        return this.widgetTypes[type]?.icon || '<i class="icon ion-ios-help"></i>';
    }

    /**
     * 渲染控件列表
     */
    renderWidgetList(widgets) {
        const widgetList = document.getElementById('widgetList');
        if (!widgets || widgets.length === 0) {
            widgetList.innerHTML = '<div class="uk-width-1-1"><div class="uk-alert uk-alert-warning">暂无配置数据</div></div>';
            return;
        }

        let html = '';
        widgets.forEach((widget, index) => {
            // 根据宽度设置对应的网格类（6列布局）
            const width = widget.width || 2;
            let widthClass = 'uk-width-1-3@m'; // 默认2列宽度
            if (width == 1) {
                widthClass = 'uk-width-1-6@m';
            } else if (width == 2) {
                widthClass = 'uk-width-1-3@m';
            } else if (width == 3) {
                widthClass = 'uk-width-1-2@m';
            } else if (width == 4) {
                widthClass = 'uk-width-2-3@m';
            } else if (width == 5) {
                widthClass = 'uk-width-5-6@m';
            } else {
                widthClass = 'uk-width-1-1@m';
            }
            
            const typeName = this.getWidgetTypeName(widget.type);
            const typeIcon = this.getWidgetTypeIcon(widget.type);
            
            html += `
                <div class="${widthClass} uk-sortable-item" data-widget-index="${index}">
                    <div class="uk-card uk-card-small uk-card-default uk-card-body widget-item" 
                         onclick="layoutEditor.selectWidget(${index})">
                        <div class="uk-flex uk-flex-middle">
                            <div class="uk-sortable-handle uk-margin-small-right">☰</div>
                            <div>
                                <div class="uk-text-bold">${widget.title || '未命名'}</div>
                                <div class="uk-margin-small-right">${typeIcon} ${typeName}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        widgetList.innerHTML = html;
        
        // 重新初始化拖拽排序功能
        this.initSortable();
    }

    /**
     * 选择控件
     */
    selectWidget(index) {
        this.selectedWidgetIndex = index;
        
        // 高亮选中的控件
        document.querySelectorAll('.widget-item').forEach(item => {
            item.classList.remove('uk-card-primary');
        });
        document.querySelector(`[data-widget-index="${index}"] .widget-item`).classList.add('uk-card-primary');
        
        // 加载控件属性
        this.loadWidgetProperties(index);
    }

    /**
     * 加载控件属性
     */
    async loadWidgetProperties(index) {
        try {
            // 直接从全局config对象获取数据
            if (typeof config !== 'undefined' && config.layout && config.layout[index]) {
                this.renderPropertyPanel(config.layout[index], index);
            }
        } catch (error) {
            console.error('加载属性失败:', error);
            UIkit.notification('加载控件属性失败', {status: 'danger'});
        }
    }

    /**
     * 渲染属性面板
     */
    renderPropertyPanel(widget, index) {
        const propertyPanel = document.getElementById('propertyPanel');
        
        const template = `
            <div class="uk-card uk-card-default uk-card-body">
                <h3 class="uk-card-title">${widget.title || '未命名'} 属性</h3>
                
                <form id="propertyForm" class="uk-form-stacked" onsubmit="return false;">
                    <input type="hidden" name="config_id" value="${this.configId}">
                    <input type="hidden" name="widget_index" value="${index}">
                    
                    <div class="uk-margin">
                        <label class="uk-form-label">控件标题</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="title" value="${widget.title || ''}" required>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">控件类型</label>
                        <div class="uk-form-controls">
                            <select class="uk-select" name="type">
                                ${Object.entries(this.widgetTypes).map(([type, config]) => 
                                    `<option value="${type}" ${widget.type === type ? 'selected' : ''}>${config.name}</option>`
                                ).join('')}
                            </select>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">宽度 (1-6列)</label>
                        <div class="uk-form-controls">
                            <select class="uk-select" name="width">
                                <option value="1" ${widget.width == 1 ? 'selected' : ''}>1列</option>
                                <option value="2" ${widget.width == 2 || !widget.width ? 'selected' : ''}>2列</option>
                                <option value="3" ${widget.width == 3 ? 'selected' : ''}>3列</option>
                                <option value="4" ${widget.width == 4 ? 'selected' : ''}>4列</option>
                                <option value="5" ${widget.width == 5 ? 'selected' : ''}>5列</option>
                                <option value="6" ${widget.width == 6 ? 'selected' : ''}>6列</option>
                            </select>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">高度 (像素)</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="number" name="height" min="1" value="${widget.height || 300}" required>
                        </div>
                    </div>

                    <!-- 动态属性区域 -->
                    <div id="dynamicProperties">
                        ${this.renderDynamicProperties(widget)}
                    </div>

                    <div class="uk-margin">
                        <button type="button" class="uk-button uk-button-danger" onclick="layoutEditor.deleteWidget()">删除控件</button>
                    </div>
                </form>
            </div>
        `;

        propertyPanel.innerHTML = template;
        
        // 绑定类型变更事件
        document.querySelector('select[name="type"]').addEventListener('change', (e) => {
            this.updatePropertyFields();
        });
    }

    /**
     * 渲染动态属性
     */
    renderDynamicProperties(widget) {
        const type = widget.type || 'text';
        const typeConfig = this.widgetTypes[type] || {properties: []};
        const properties = typeConfig.properties || [];
        
        if (properties.length === 0) {
            return '<div class="uk-text-meta">该控件类型没有额外属性</div>';
        }

        let html = '<h4>高级属性</h4>';
        properties.forEach(prop => {
            const value = widget.options?.[prop.name] || prop.value || '';
            html += `
                <div class="uk-margin">
                    <label class="uk-form-label">${prop.label}</label>
                    <div class="uk-form-controls">
                        ${this.getPropertyInput(prop, value)}
                    </div>
                </div>
            `;
        });

        return html;
    }

    /**
     * 获取属性输入字段
     */
    getPropertyInput(prop, value) {
        switch (prop.type) {
            case 'select':
                let options = prop.options.map(opt => 
                    `<option value="${opt.value}" ${value == opt.value ? 'selected' : ''}>${opt.text}</option>`
                ).join('');
                return `<select class="uk-select" name="options[${prop.name}]">${options}</select>`;
            case 'textarea':
                return `<textarea class="uk-textarea" name="options[${prop.name}]" 
                          placeholder="${prop.placeholder || ''}" rows="4">${value}</textarea>`;
            default:
                return `<input class="uk-input" type="${prop.type}" name="options[${prop.name}]" 
                         value="${value}" placeholder="${prop.placeholder || ''}">`;
        }
    }

    /**
     * 处理属性变更 - 实时更新
     */
    async handlePropertyChange(inputElement) {
        if (this.selectedWidgetIndex === null) return;
        
        try {
            const form = document.getElementById('propertyForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // 处理options
            const options = {};
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('options[')) {
                    const optionName = key.match(/options\[(.*?)\]/)[1];
                    options[optionName] = value;
                }
            }

            // 立即更新本地config对象
            if (typeof config !== 'undefined' && config.layout && data.widget_index) {
                const index = parseInt(data.widget_index);
                config.layout[index] = {
                    ...config.layout[index],
                    title: data.title,
                    type: data.type,
                    width: parseInt(data.width),
                    height: parseInt(data.height),
                    options: options
                };
                
                // 实时刷新左侧控件列表
                this.renderWidgetList(config.layout);
            }

            // 不再自动保存到服务器，只在点击"更新配置"按钮时保存
        } catch (error) {
            console.error('实时保存失败:', error);
        }
    }

    /**
     * 更新属性字段
     */
    updatePropertyFields() {
        const type = document.querySelector('select[name="type"]').value;
        const typeConfig = this.widgetTypes[type] || {properties: []};
        const properties = typeConfig.properties || [];
        
        let html = properties.length > 0 ? '<h4>高级属性</h4>' : '<div class="uk-text-meta">该控件类型没有额外属性</div>';
        
        properties.forEach(prop => {
            html += `
                <div class="uk-margin">
                    <label class="uk-form-label">${prop.label}</label>
                    <div class="uk-form-controls">
                        ${this.getPropertyInput(prop, '')}
                    </div>
                </div>
            `;
        });

        document.getElementById('dynamicProperties').innerHTML = html;
    }

    /**
     * 加载初始数据
     */
    async loadInitialData() {
        try {
            // 直接从全局config对象获取数据
            if (typeof config !== 'undefined' && config.layout) {
                this.renderWidgetList(config.layout);
            } 
        } catch (error) {
            console.error('加载数据失败:', error);
            UIkit.notification('加载数据失败', {status: 'danger'});
        }
    }

    /**
     * 保存控件排序
     */
    async saveWidgetOrder() {
        try {
            const widgetIndices = Array.from(document.querySelectorAll('.uk-sortable-item')).map(
                item => parseInt(item.dataset.widgetIndex)
            );
            
            // 更新全局config对象的排序
            if (typeof config !== 'undefined' && config.layout) {
                const sortedWidgets = widgetIndices.map(index => config.layout[index]);
                config.layout = sortedWidgets;
                console.log(config.layout);
            }
        } catch (error) {
            console.error('更新排序失败:', error);
            UIkit.notification('更新排序失败', {status: 'danger'});
        }
    }

    /**
     * 添加新控件
     */
    async addWidget() {
        try {
            // 创建默认控件数据
            const newWidget = {
                type: 'text',
                title: '新控件',
                width: 2,
                height: 300,
                options: {}
            };
            
            // 实时更新左侧页面
            if (typeof config !== 'undefined') {
                if (!config.layout) {
                    config.layout = [];
                }
                
                // 添加到全局config对象
                config.layout.push(newWidget);
                // 实时刷新左侧控件列表
                this.renderWidgetList(config.layout);
                // 自动选择新添加的控件
                this.selectWidget(config.layout.length - 1);
            } else {
                UIkit.notification('无法添加控件：配置数据未初始化', {status: 'danger'});
            }
        } catch (error) {
            console.error('添加控件失败:', error);
            UIkit.notification('添加控件失败', {status: 'danger'});
        }
    }

    /**
     * 删除控件
     */
    async deleteWidget() {
        if (this.selectedWidgetIndex === null) return;

        try {
            UIkit.modal.confirm('确定要删除这个控件吗？此操作不可恢复。').then(async () => {
                // 实时更新左侧页面
                if (typeof config !== 'undefined' && config.layout && this.selectedWidgetIndex !== null) {
                    // 从全局config对象中删除控件数据
                    config.layout.splice(this.selectedWidgetIndex, 1);
                    
                    // 实时刷新左侧控件列表
                    this.renderWidgetList(config.layout);
                }
                this.selectedWidgetIndex = null;
                
                // 清空属性面板
                document.getElementById('propertyPanel').innerHTML = `
                    <div class="uk-alert uk-alert-primary">
                        <p>请从左侧选择一个控件来编辑其属性</p>
                    </div>
                `;
            });
        } catch (error) {
            console.error('删除控件失败:', error);
            UIkit.notification('删除失败', {status: 'danger'});
        }
    }

    /**
     * API请求
     */
    async apiRequest(method, endpoint, data = null) {
        const url = `/${this.tenantId}/${endpoint}`;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        // 如果有数据，将其转换为JSON字符串
        if (data !== null) {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            return await response.json();
        } catch (error) {
            throw new Error(`API请求失败: ${error.message}`);
        }
    }
}

// 全局实例
let layoutEditor = null;

// 初始化函数
document.addEventListener('DOMContentLoaded', function() {
    // 从URL获取configId
    const pathParts = window.location.pathname.split('/');
    const configId = pathParts[pathParts.length - 1];
    
    if (configId) {
        layoutEditor = new LayoutEditor({
            configId: configId,
            tenantId: 'default' // 可以从全局变量或URL获取
        });
    }
});

// 全局函数供模板调用
window.selectWidget = function(index) {
    if (layoutEditor) {
        layoutEditor.selectWidget(index);
    }
};

window.deleteWidget = function() {
    if (layoutEditor) {
        layoutEditor.deleteWidget();
    }
};

// 添加控件函数
window.addWidget = function() {
    if (layoutEditor) {
        layoutEditor.addWidget();
    }
}

// 更新配置函数 - 将所有配置数据提交到服务器
window.updateConfig = async function() {
    if (!layoutEditor) return;
    
    try {
        config.config_id = layoutEditor.configId;
        
        // 发送到服务器
        const response = await layoutEditor.apiRequest('POST', 'config/update', config);
        
        if (response.success) {
            UIkit.notification('配置更新成功', {status: 'success'});
        } else {
            UIkit.notification(response.message || '更新失败', {status: 'danger'});
        }
    } catch (error) {
        console.error('更新配置失败:', error);
        UIkit.notification('更新配置失败', {status: 'danger'});
    }
};