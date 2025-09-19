/**
 * WidgetRenderer类 - 前端控件渲染工具
 * 用于根据配置和数据渲染不同类型的表单字段
 * 参考PHP的renderControl方法实现
 */

class WidgetRenderer {
    /**
     * 渲染单个控件
     * @param {mixed} value - 字段值
     * @param {Object} config - 控件配置
     * @param {string} label - 标签值
     * @param {string} compare - 比较值
     * @returns {string} 渲染后的HTML
     */
    static renderControl(value, config, label = null, compare = null) {
        const type = config.type || 'text';
        const field = config.id || '';
        const readonly = config.readonly ? 'readonly' : '';
        const required = config.required ? 'required' : '';
        const tips = config.tips ? `<small class="help-text">${this.escapeHtml(config.tips)}</small>` : '';
        const view = config.view || false;
        const props = config.props || {};
        
        let html = '';
        const readonlyClass = readonly ? ' uk-background-muted' : '';

        switch (type) {
            case 'datepicker':
                if (view) {
                    html += `<div class="uk-text-muted">${this.escapeHtml(value)}</div>`;
                } else {
                    html += `<input class="uk-input uk-width-1-1${readonlyClass}" type="date" name="data[${field}]" value="${this.escapeHtml(value)}" ${readonly} ${required}>`;
                }
                break;
            case 'datetimepicker':
            case 'datetimepicker2':
                if (view) {
                    html += `<div class="uk-text-muted">${this.escapeHtml(value)}</div>`;
                } else {
                    html += `<input class="uk-input uk-width-1-1${readonlyClass}" type="datetime-local" name="data[${field}]" value="${this.escapeHtml(value)}" ${readonly} ${required}>`;
                }
                break;

            case 'select_new':
            case 'select':
                html += this.renderSelect(value, config, label, view);
                break;

            case 'percent':
                if (view) {
                    html += `<div class="uk-text-muted">${this.escapeHtml(value)}%</div>`;
                } else {
                    html += `<div style="display: inline-flex; align-items: center; width: 100%;"><input class="uk-input${readonlyClass}" type="number" step="0.01" name="data[${field}]" value="${this.escapeHtml(value)}" ${readonly} ${required}><span style="margin-left: 5px;">%</span></div>`;
                }
                break;

            case 'yuan':
                if (view) {
                    html += `<div class="uk-text-muted">¥${this.escapeHtml(value)}</div>`;
                } else {
                    html += `<div style="display: inline-flex; align-items: center; width: 100%;"><input class="uk-input${readonlyClass}" type="number" step="0.01" name="data[${field}]" value="${this.escapeHtml(value)}" ${readonly} ${required} style="width: 90%;"><span style="margin-left: 5px;">¥</span></div>`;
                }
                break;

            case 'amount':
                if (view) {
                    html += `<div class="uk-text-muted">${this.escapeHtml(value)}</div>`;
                } else {
                    html += `<input type="number" class="uk-input${readonlyClass}" name="data[${field}]" value="${this.escapeHtml(value)}" ${readonly} ${required} style="width: 90%;">`;
                }
                break;

            case 'upload':
                if (view) {
                    html += `<div class="uk-text-muted">${this.escapeHtml(value)}</div>`;
                } else {
                    const disabledAttr = readonly ? ' disabled' : '';
                    html += `<input type="file" name="data[${field}]" style="width: 90%;"${disabledAttr}>`;
                }
                break;

            case 'muti':
                if (view) {
                    html += `<div class="uk-text-muted">${this.escapeHtml(value).replace(/\n/g, '<br>')}</div>`;
                } else {
                    html += `<textarea class="uk-textarea uk-width-1-1${readonlyClass}" name="data[${field}]" rows="5" ${readonly} ${required}>${this.escapeHtml(value)}</textarea>`;
                    html += tips;
                }
                break;

            case 'radio':
                if (view) {
                    html += `<div class="uk-text-muted">${this.escapeHtml(value)}</div>`;
                } else {
                    // 解析选项列表
                    const options = props.options ? props.options.split('\n') : [];
                    options.forEach(option => {
                        option = option.trim();
                        if (option) {
                            const checked = value == option ? 'checked' : '';
                            html += `<label class="uk-form-label"><input type="radio" class="uk-radio" name="data[${field}]" value="${this.escapeHtml(option)}" ${checked} ${readonly} ${required}> ${this.escapeHtml(option)}</label><br>`;
                        }
                    });
                    html += tips;
                }
                break;

            case 'checkbox':
                if (view) {
                    html += `<div class="uk-text-muted">${value ? '是' : '否'}</div>`;
                } else {
                    const checked = value ? 'checked' : '';
                    html += `<label class="uk-form-label"><input type="checkbox" class="uk-checkbox" name="data[${field}]" value="1" ${checked} ${readonly} ${required}> 是</label>`;
                    html += tips;
                }
                break;

            case 'puretext':
                html += `<div class="uk-text-muted">${props.tpl || ''}</div>`;
                break;

            case 'data':
                // 数据对象类型，需要异步加载数据
                html += this.renderDataObject(value, config, view);
                break;

            default:
                if (view) {
                    html += `<div class="uk-text-muted">${value}</div>`;
                } else {
                    html += `<input type="text" class="uk-input${readonlyClass}" name="data[${field}]" value="${this.escapeHtml(value)}" ${readonly} ${required}>`;
                    html += tips;
                }
        }

        if (compare) {
            html += ` <div class="uk-text-muted"><del>${this.escapeHtml(compare)}</del></div>`;
        }

        return html;
    }

    /**
     * 渲染选择框
     * @param {mixed} value - 当前值
     * @param {Object} config - 配置
     * @param {string} label - 标签
     * @param {boolean} view - 是否只读视图
     * @returns {string} HTML
     */
    static renderSelect(value, config, label, view) {
        if (view) {
            // 如果label为null或undefined，使用value作为显示内容
            const displayValue = (label !== null && label !== undefined) ? label : value;
            return `<div class="uk-text-muted">${displayValue}</div>`;
        }

        const field = config.id || '';
        const readonly = config.readonly ? 'readonly' : '';
        const required = config.required ? 'required' : '';
        const props = config.props || {};
        
        let html = `<select class="uk-select" name="data[${field}]" ${readonly} ${required}>`;
        
        if (props.options) {
            const options = props.options.split('\n');
            options.forEach(option => {
                option = option.trim();
                if (option) {
                    const selected = value == option ? 'selected' : '';
                    html += `<option value="${this.escapeHtml(option)}" ${selected}>${this.escapeHtml(option)}</option>`;
                }
            });
        }
        
        html += '</select>';
        return html;
    }

    /**
     * 渲染数据对象
     * @param {mixed} value - 当前值
     * @param {Object} config - 配置
     * @param {boolean} view - 是否只读视图
     * @returns {string} HTML
     */
    static renderDataObject(value, config, view) {
        const props = config.props || {};
        
        // 这里可以添加异步加载数据的逻辑
        // 暂时返回一个占位符
        return `<div class="uk-alert uk-alert-primary">
            <p>数据对象: ${props.data_source || '未指定数据源'}</p>
            <p>值: ${this.escapeHtml(value)}</p>
        </div>`;
    }

    /**
     * 渲染表单字段
     * @param {string} field - 字段名
     * @param {Object} config - 字段配置
     * @param {Object} entityData - 实体数据
     * @param {Object} val - 表单值
     * @param {Object} err - 错误信息
     * @param {string} view - 视图类型
     * @returns {string} HTML
     */
    static renderFormField(field, config, entityData = {}, val = {}, err = {}, view = 'form') {
        let html = '';
        
        // 计算宽度
        const width = ['section', 'tab', 'data'].includes(config.type) ? 3 : 
            Math.max(1, Math.min(3, parseInt(config.width) || 1));
        
        if (['section', 'tab'].includes(config.type)) {
            html += `<h3 class="uk-width-1-1 hd-title">${this.escapeHtml(config.name)}</h3>`;
        } else {
            const widthClass = width === 3 ? 'uk-width-1-1 ' : 
                (width === 2 ? 'uk-width-2-3@m ' : 'uk-width-1-3@m ');
            
            html += `<div class="${widthClass}uk-padding-small">`;
            
            // 添加必填标记
            const requiredMark = config.required ? '<span style="color: red;">*</span>' : '';
            
            // 添加标签
            if (config.type !== 'data') {
                html += `<label class="uk-form-label">${this.escapeHtml(config.name)}${requiredMark}</label>`;
            }
            
            html += '<div class="uk-form-controls">';
            
            // 获取字段值
            const fieldValue = entityData[field] || val.data?.[field] || '';
            const labelValue = entityData[`${field}_label`] || null;
            const compareValue = entityData[`${field}_compare`] || null;
            
            // 调用渲染控件
            const controlConfig = {
                type: config.type,
                id: field,
                readonly: !!config.readonly,
                required: !!config.required,
                tips: config.tips,
                view: view !== 'form',
                props: config.props || {}
            };
            
            // 如果是data对象类型，使用实体ID作为值
            if (config.type === 'data') {
                controlConfig.value = entityData.id || '';
            }
            
            html += this.renderControl(fieldValue, controlConfig, labelValue, compareValue);
            
            // 添加错误信息
            const errorMsg = err.data?.[field] || '';
            if (errorMsg) {
                html += `<span class="help-inline" style="color: red;">${this.escapeHtml(errorMsg)}</span>`;
            }
            
            html += '</div></div>';
        }
        
        return html;
    }

    /**
     * 渲染多个表单字段
     * @param {Object} config - 表单配置
     * @param {Object} data - 实体数据
     * @param {Object} val - 表单值
     * @param {Object} errors - 错误信息
     * @param {boolean|string} view - 视图类型
     * @returns {string} HTML
     */
    static renderFormFields(config, data = {}, val = {}, errors = {}, view = false) {
        let html = '';
        
        for (const [field, fieldConfig] of Object.entries(config)) {
            html += this.renderFormField(field, fieldConfig, data, val, errors, view);
        }
        
        return html;
    }

    /**
     * HTML转义
     * @param {string} text - 要转义的文本
     * @returns {string} 转义后的文本
     */
    static escapeHtml(text) {
        if (typeof text !== 'string') return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * 初始化表单事件
     * @param {string} containerId - 容器ID
     */
    static initFormEvents(containerId = '') {
        const container = containerId ? document.getElementById(containerId) : document;
        if (!container) return;
        
        // 这里可以添加表单事件初始化逻辑
        console.log('表单事件初始化完成');
    }
}

// 全局注册
if (typeof window !== 'undefined') {
    window.WidgetRenderer = WidgetRenderer;
}