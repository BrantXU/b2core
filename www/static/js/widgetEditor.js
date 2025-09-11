/**
 * aPaas平台模块编辑功能 - 控件拖拽排序和属性菜单
 * 提供完整的控件管理功能，包括拖拽排序、属性编辑、AJAX数据交互
 */

class WidgetEditor {
    constructor(options = {}) {
        this.configId = options.configId || '';
        this.tenantId = options.tenantId || 'default';
        this.selectedWidgetId = null;
        this.widgetProperties = this.getWidgetPropertiesConfig();
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
     * 获取控件属性配置
     */

    getWidgetPropertiesConfig() {
        return {
            'text': [],
            'number': [
                {name: 'min', label: '最小值', type: 'number'},
                {name: 'max', label: '最大值', type: 'number'},
                {name: 'step', label: '步长', type: 'number', value: 1}
            ],
            'datepicker': [
                {name: 'minDate', label: '最小日期', type: 'date'},
                {name: 'maxDate', label: '最大日期', type: 'date'}
            ],
            'select': [
                {name: 'options', label: '选项列表', type: 'textarea', 
                 placeholder: '每行一个选项，格式：值|显示文本'}
            ],
            'textarea': [
                {name: 'rows', label: '行数', type: 'number', value: 4},
                {name: 'cols', label: '列数', type: 'number', value: 50}
            ],
            'section': [
                {name: 'level', label: '标题级别', type: 'select', 
                 options: [{value: 'h2', text: 'H2'}, {value: 'h3', text: 'H3'}, {value: 'h4', text: 'H4'}]}
            ],
            'tab': [],
            'yuan': [
                {name: 'precision', label: '小数精度', type: 'number', value: 2},
                {name: 'min', label: '最小值', type: 'number', value: 0},
                {name: 'max', label: '最大值', type: 'number'}
            ],
            'percent': [
                {name: 'precision', label: '小数精度', type: 'number', value: 2},
                {name: 'min', label: '最小值', type: 'number', value: 0},
                {name: 'max', label: '最大值', type: 'number', value: 100}
            ],
            'amount': [
                {name: 'precision', label: '小数精度', type: 'number', value: 2},
                {name: 'min', label: '最小值', type: 'number', value: 0}
            ]
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
        // 拖拽排序事件（现在在initSortable中直接绑定）

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
        const typeNames = {
            'text': '文本输入',
            'number': '数字输入',
            'datepicker': '日期选择',
            'select': '下拉选择',
            'textarea': '文本区域',
            'section': '分区标题',
            'tab': '标签页',
            'yuan': '金额(元)',
            'percent': '百分比',
            'amount': '数量'
        };
        return typeNames[type] || type;
    }

    getWidgetTypeIcon(type) {
        const typeIcons = {
            'text': '<input type="text" disabled class="uk-input uk-form-small" >',
            'number': '<input type="number" disabled class="uk-input uk-form-small">',
            'datepicker': '<input type="date" disabled class="uk-input uk-form-small">',
            'select': '<select class="uk-select uk-form-small" disabled></select>',
            'textarea': '<i class="icon ion-ios-document-text"></i>',
            'section': '---',
            'tab': '<i class="icon ion-ios-folder"></i>',
            'yuan': '<input class="uk-input uk-form-small" disabled> ¥',
            'percent': '<input class="uk-input uk-form-small" disabled> %',
            'amount': '<input class="uk-input uk-form-small" disabled> '
        };
        return typeIcons[type] || '<input class="uk-input uk-form-small" disabled>';
    }

    /**
     * 渲染控件列表
     */
    renderWidgetList(widgets) {
        const widgetList = document.getElementById('widgetList');
        if (!widgets || Object.keys(widgets).length === 0) {
            widgetList.innerHTML = '<div class="uk-width-1-1"><div class="uk-alert uk-alert-warning">暂无配置数据</div></div>';
            return;
        }

        let html = '';
        Object.entries(widgets).forEach(([widgetId, widget]) => {
            // 根据宽度设置对应的网格类
            let widthClass = 'uk-width-1-3@m'; // 默认1列宽度
            if (widget.width == 2) {
                widthClass = 'uk-width-2-3@m';
            } else if (widget.width == 3) {
                widthClass = 'uk-width-1-1@m';
            } else if (widget.width == 4) {
                widthClass = 'uk-width-1-1@m';
            }
            
            const typeName = this.getWidgetTypeName(widget.type);
            const typeIcon = this.getWidgetTypeIcon(widget.type);
            
            html += `
                <div class="${widthClass} uk-sortable-item" data-widget-id="${widgetId}">
                    <div class="uk-card uk-card-small uk-card-default uk-card-body widget-item" 
                         onclick="widgetEditor.selectWidget('${widgetId}')">
                        <div class="uk-flex uk-flex-middle">
                            <div class="uk-sortable-handle uk-margin-small-right">☰</div>
                            <div>
                                <div class="uk-text-bold">${widget.name || '未命名'}</div>
                                <div class="uk-margin-small-right">${typeIcon}</div>
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
    selectWidget(widgetId) {
        this.selectedWidgetId = widgetId;
        
        // 高亮选中的控件
        document.querySelectorAll('.widget-item').forEach(item => {
            item.classList.remove('uk-card-primary');
        });
        document.querySelector(`[data-widget-id="${widgetId}"] .widget-item`).classList.add('uk-card-primary');
        
        // 加载控件属性
        this.loadWidgetProperties(widgetId);
    }

    /**
     * 加载控件属性
     */
    async loadWidgetProperties(widgetId) {
        try {
            // 直接从全局config对象获取数据，避免Ajax请求
            if (typeof config !== 'undefined' && config.item && config.item[widgetId]) {
                this.renderPropertyPanel(config.item[widgetId], widgetId);
            }
        } catch (error) {
            console.error('加载属性失败:', error);
            UIkit.notification('加载控件属性失败', {status: 'danger'});
        }
    }

    /**
     * 渲染属性面板
     */
    renderPropertyPanel(widget, widgetId) {
        const propertyPanel = document.getElementById('propertyPanel');
        
        const template = `
            <div class="uk-card uk-card-default uk-card-body">
                <h3 class="uk-card-title">${widget.name || '未命名'} 属性</h3>
                
                <form id="propertyForm" class="uk-form-stacked" onsubmit="return false;">
                    <input type="hidden" name="config_id" value="${this.configId}">
                    <input type="hidden" name="widget_id" value="${widgetId}">
                    
                    <div class="uk-margin">
                        <label class="uk-form-label">控件ID</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" value="${widgetId}" readonly>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">控件名称</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="name" value="${widget.name || ''}" required>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">控件类型</label>
                        <div class="uk-form-controls">
                            <select class="uk-select" name="type">
                                <option value="text" ${widget.type === 'text' ? 'selected' : ''}>文本输入</option>
                                <option value="number" ${widget.type === 'number' ? 'selected' : ''}>数字输入</option>
                                <option value="datepicker" ${widget.type === 'datepicker' ? 'selected' : ''}>日期选择</option>
                                <option value="select" ${widget.type === 'select' ? 'selected' : ''}>下拉选择</option>
                                <option value="textarea" ${widget.type === 'textarea' ? 'selected' : ''}>文本区域</option>
                                <option value="section" ${widget.type === 'section' ? 'selected' : ''}>分区标题</option>
                                <option value="tab" ${widget.type === 'tab' ? 'selected' : ''}>标签页</option>
                                <option value="yuan" ${widget.type === 'yuan' ? 'selected' : ''}>金额(元)</option>
                                <option value="percent" ${widget.type === 'percent' ? 'selected' : ''}>百分比</option>
                                <option value="amount" ${widget.type === 'amount' ? 'selected' : ''}>数量</option>
                            </select>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">列宽</label>
                        <div class="uk-form-controls">
                            <select class="uk-select" name="width">
                                <option value="1" ${widget.width == 1 ? 'selected' : ''}>1列</option>
                                <option value="2" ${widget.width == 2 ? 'selected' : ''}>2列</option>
                                <option value="3" ${widget.width == 3 ? 'selected' : ''}>3列</option>
                                <option value="4" ${widget.width == 4 ? 'selected' : ''}>4列</option>
                            </select>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">是否在列表中显示</label>
                        <div class="uk-form-controls">
                            <input type="checkbox" name="listed" value="1" ${widget.listed ? 'checked' : ''}>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">是否必填</label>
                        <div class="uk-form-controls">
                            <input type="checkbox" name="required" value="1" ${widget.required ? 'checked' : ''}>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">只读</label>
                        <div class="uk-form-controls">
                            <input type="checkbox" name="readonly" value="1" ${widget.readonly ? 'checked' : ''}>
                        </div>
                    </div>

                    <div class="uk-margin">
                        <label class="uk-form-label">提示信息</label>
                        <div class="uk-form-controls">
                            <input class="uk-input" type="text" name="tips" value="${widget.tips || ''}" placeholder="输入提示信息">
                        </div>
                    </div>

                    <!-- 动态属性区域 -->
                    <div id="dynamicProperties">
                        ${this.renderDynamicProperties(widget)}
                    </div>

                    <div class="uk-margin">
                        <button type="button" class="uk-button uk-button-danger" onclick="widgetEditor.deleteWidget()">删除控件</button>
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
        const properties = this.widgetProperties[type] || [];
        
        if (properties.length === 0) {
            return '<div class="uk-text-meta">该控件类型没有额外属性</div>';
        }

        let html = '<h4>高级属性</h4>';
        properties.forEach(prop => {
            const value = widget[prop.name] || prop.value || '';
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
                return `<select class="uk-select" name="props[${prop.name}]">${options}</select>`;
            case 'textarea':
                return `<textarea class="uk-textarea" name="props[${prop.name}]" 
                          placeholder="${prop.placeholder || ''}" rows="4">${value}</textarea>`;
            default:
                return `<input class="uk-input" type="${prop.type}" name="props[${prop.name}]" 
                         value="${value}" placeholder="${prop.placeholder || ''}">`;
        }
    }

    /**
     * 处理属性变更 - 实时更新
     */
    async handlePropertyChange(inputElement) {
        if (!this.selectedWidgetId) return;
        
        try {
            const form = document.getElementById('propertyForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // 处理复选框值
            data.listed = formData.has('listed');
            data.required = formData.has('required');
            data.readonly = formData.has('readonly');
            
            // 处理props
            const props = {};
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('props[')) {
                    const propName = key.match(/props\[(.*?)\]/)[1];
                    props[propName] = value;
                }
            }
            data.props = props;

            // 立即更新本地config对象
            if (typeof config !== 'undefined' && config.item && data.widget_id) {
                config.item[data.widget_id] = {
                    ...config.item[data.widget_id],
                    name: data.name,
                    type: data.type,
                    width: parseInt(data.width),
                    listed: data.listed,
                    required: data.required,
                    readonly: data.readonly,
                    tips: data.tips,
                    ...props
                };
                
                // 实时刷新左侧控件列表
                this.renderWidgetList(config.item);
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
        const properties = this.widgetProperties[type] || [];
        
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
            // 直接从全局config对象获取数据，避免Ajax请求
            if (typeof config !== 'undefined' && config.item) {
                this.renderWidgetList(config.item);
            } 
        } catch (error) {
            console.error('加载数据失败:', error);
            UIkit.notification('加载数据失败', {status: 'danger'});
        }
    }

    /**
     * 保存属性 - 双向绑定实现
     * 在编辑控件属性时实时更新config对象和左侧渲染界面
     */
    async saveProperties(form) {
        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // 处理复选框值
            data.listed = formData.has('listed');
            data.required = formData.has('required');
            data.readonly = formData.has('readonly');
            
            // 处理props
            const props = {};
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('props[')) {
                    const propName = key.match(/props\[(.*?)\]/)[1];
                    props[propName] = value;
                }
            }
            data.props = props;

            const response = await this.apiRequest('POST', 'config/update_widget', data);
            
            if (response.success) {
                UIkit.notification('属性保存成功', {status: 'success'});
                
                // 双向绑定机制：实时更新左侧页面
                if (typeof config !== 'undefined' && config.item && data.widget_id) {
                    // 更新全局config对象中的控件数据 - 双向绑定的核心
                    config.item[data.widget_id] = {
                        ...config.item[data.widget_id],
                        name: data.name,
                        type: data.type,
                        width: parseInt(data.width),
                        listed: data.listed,
                        required: data.required,
                        readonly: data.readonly,
                        tips: data.tips,
                        ...props
                    };
                    
                    // 实时刷新左侧控件列表 - 双向绑定的UI更新
                    this.renderWidgetList(config.item);
                    
                    // 重新选择当前控件以保持高亮状态
                    this.selectWidget(data.widget_id);
                } else {
                    // 如果全局config不存在，则重新加载所有数据
                    this.loadInitialData();
                }
            } else {
                UIkit.notification(response.message || '保存失败', {status: 'danger'});
            }
        } catch (error) {
            console.error('保存属性失败:', error);
            UIkit.notification('保存失败', {status: 'danger'});
        }
    }

    /**
     * 保存控件排序
     * 仅更新本地config变量，不请求服务器
     */
    async saveWidgetOrder() {
        try {
            const widgetIds = Array.from(document.querySelectorAll('.uk-sortable-item')).map(
                item => item.dataset.widgetId
            );

            // 更新全局config对象的排序
            if (typeof config !== 'undefined') {
                config.widget_order = widgetIds;
                
                // 同时更新config.item的顺序以保持一致性
                const sortedWidgets = {};
                widgetIds.forEach(widgetId => {
                    if (config.item && config.item[widgetId]) {
                        sortedWidgets[widgetId] = config.item[widgetId];
                    }
                });
                config.item = sortedWidgets;
                console.log(config.item);
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
            // 生成唯一ID
            const newWidgetId = 'widget_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            
            // 创建默认控件数据
            const newWidget = {
                id: newWidgetId,
                name: '新控件',
                type: 'text',
                width: 1,
                listed: true,
                required: false,
                readonly: false,
                tips: '',
                props: {}
            };
            
            // 实时更新左侧页面
            if (typeof config !== 'undefined') {
                if (!config.item) {
                    config.item = {};
                }
                
                // 添加到全局config对象
                config.item[newWidgetId] = newWidget;
                
                // 实时刷新左侧控件列表
                this.renderWidgetList(config.item);
                
                // 自动选择新添加的控件
                this.selectWidget(newWidgetId);
                
                UIkit.notification('新控件已添加', {status: 'success'});
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
     * 仅删除本地config数据，不请求服务器
     */
    async deleteWidget() {
        if (!this.selectedWidgetId) return;

        try {
            UIkit.modal.confirm('确定要删除这个控件吗？此操作不可恢复。').then(async () => {
                // 实时更新左侧页面
                if (typeof config !== 'undefined' && config.item && this.selectedWidgetId) {
                    // 从全局config对象中删除控件数据
                    delete config.item[this.selectedWidgetId];
                    
                    // 实时刷新左侧控件列表
                    this.renderWidgetList(config.item);
                } else {
                    // 如果全局config不存在，则重新加载所有数据
                    this.loadInitialData();
                }
                
                UIkit.notification('控件已删除', {status: 'success'});
                this.selectedWidgetId = null;
                
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

        if (data && method !== 'GET') {
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
let widgetEditor = null;

// 初始化函数
document.addEventListener('DOMContentLoaded', function() {
    // 从URL获取configId
    const pathParts = window.location.pathname.split('/');
    const configId = pathParts[pathParts.length - 1];
    
    if (configId) {
        widgetEditor = new WidgetEditor({
            configId: configId,
            tenantId: 'default' // 可以从全局变量或URL获取
        });
    }
});

// 全局函数供模板调用
window.selectWidget = function(widgetId) {
    if (widgetEditor) {
        widgetEditor.selectWidget(widgetId);
    }
};

window.deleteWidget = function() {
    if (widgetEditor) {
        widgetEditor.deleteWidget();
    }
};

// 添加控件函数
window.addWidget = function() {
    if (widgetEditor) {
        widgetEditor.addWidget();
    }
}

// 更新配置函数 - 将所有配置数据提交到服务器
window.updateConfig = async function() {
    if (!widgetEditor) return;
    
    try {
        config.config_id = widgetEditor.configId;
        // 显示加载状态
        //UIkit.notification('正在更新配置...', {status: 'primary'});
        console.log(config);
        // 发送到服务器
        const response = await widgetEditor.apiRequest('POST', 'config/update', config);
        
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