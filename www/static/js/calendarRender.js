/**
 * CalendarRender类 - 日历渲染器，提供按月视图显示数据功能
 * 自动检测日期字段，支持多日期字段选择
 */
class CalendarRender {
    /**
     * 构造函数
     * @param {string} containerId - 容器元素的ID
     * @param {Object} options - 配置选项
     * @param {string} baseUrl - 基础URL
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
        
        // 当前视图相关
        this.currentDate = new Date(); // 当前显示的日期
        this.currentView = 'month'; // 当前视图：'month', 'week', 'day'
        
        // 初始化
        this.init();
    }
    
    /**
     * 获取默认配置选项
     */
    _getDefaultOptions(options) {
        return {
            pageSize: options.pageSize || 10,
            dateFields: options.dateFields || {},
            selectedDateField: options.selectedDateField || '',
            fields: options.fields || {},
            ...options
        };
    }
    
    /**
     * 初始化日历渲染器
     */
    init() {
        this.extractData(); // 提取数据
        this.createControls(); // 创建控制面板
        this.bindEvents(); // 绑定事件处理
        this.render(); // 首次渲染
    }
    
    /**
     * 提取表格数据
     */
    extractData() {
        // 从DOM表格中提取数据（如果存在）
        if (this.container.querySelector('table')) {
            this._extractDataFromTable();
        } else if (this.options.data) {
            // 使用提供的数组数据
            this.originalData = this.options.data;
            this.filteredData = [...this.originalData];
        }
    }
    
    /**
     * 创建控制面板
     */
    createControls() {
        const controlsHtml = `
            <div class="calendar-controls uk-margin-bottom">
                <div class="uk-button-group">
                    <button class="uk-button uk-button-small uk-button-primary" data-action="prev">
                        <i class="icon ion-md-arrow-back"></i>
                    </button>
                    <button class="uk-button uk-button-small uk-button-default" data-action="today">今天</button>
                    <button class="uk-button uk-button-small uk-button-primary" data-action="next">
                        <i class="icon ion-md-arrow-forward"></i>
                    </button>
                </div>
                
                <h3 class="calendar-title uk-display-inline-block uk-margin-left"></h3>       
                <div class="uk-button-group uk-margin-left">
                    <button class="uk-button uk-button-small uk-button-default" data-view="month">月</button>
                    <button class="uk-button uk-button-small uk-button-default" data-view="week">周</button>
                    <button class="uk-button uk-button-small uk-button-default" data-view="day">日</button>
                </div>
                ${this._getDateFieldSelector()}
            </div>
        `;
        
        this.container.innerHTML = controlsHtml;// + this.container.innerHTML;
    }
    
    /**
     * 获取日期字段选择器HTML
     */
    _getDateFieldSelector() {
        if (Object.keys(this.options.dateFields).length <= 1) {
            return '';
        }
        
        let optionsHtml = '';
        for (const [fieldName, fieldConfig] of Object.entries(this.options.dateFields)) {
            const selected = fieldName === this.options.selectedDateField ? 'selected' : '';
            optionsHtml += `<option value="${fieldName}" ${selected}>${fieldConfig.label || fieldName}</option>`;
        }
        
        return `
            <select class="uk-select uk-form-width-medium uk-margin-left" id="date-field-selector">
                ${optionsHtml}
            </select>
        `;
    }
    
    /**
     * 绑定事件处理
     */
    bindEvents() {
        this._bindNavigationEvents();
        this._bindViewChangeEvents();
        this._bindDateFieldChangeEvent();
    }
    
    /**
     * 绑定导航事件
     */
    _bindNavigationEvents() {
        const actionElements = this.container.querySelectorAll('[data-action]');
        actionElements.forEach(element => {
            dom.on(element, 'click', (e) => {
                const action = element.getAttribute('data-action');
                
                switch (action) {
                    case 'prev':
                        this._navigate(-1);
                        break;
                    case 'next':
                        this._navigate(1);
                        break;
                    case 'today':
                        this.currentDate = new Date();
                        this.render();
                        break;
                }
            });
        });
    }
    
    /**
     * 绑定视图切换事件
     */
    _bindViewChangeEvents() {
        const viewElements = this.container.querySelectorAll('[data-view]');
        viewElements.forEach(element => {
            dom.on(element, 'click', (e) => {
                const view = element.getAttribute('data-view');
                this.currentView = view;
                this.render();
            });
        });
    }
    
    /**
     * 绑定日期字段变更事件
     */
    _bindDateFieldChangeEvent() {
        const selector = this.container.querySelector('#date-field-selector');
        if (selector) {
            dom.on(selector, 'change', (e) => {
                this.options.selectedDateField = e.target.value;
                this.render();
            });
        }
    }
    
    /**
     * 导航到不同日期
     */
    _navigate(direction) {
        switch (this.currentView) {
            case 'month':
                this.currentDate.setMonth(this.currentDate.getMonth() + direction);
                break;
            case 'week':
                this.currentDate.setDate(this.currentDate.getDate() + (direction * 7));
                break;
            case 'day':
                this.currentDate.setDate(this.currentDate.getDate() + direction);
                break;
        }
        this.render();
    }
    
    /**
     * 渲染日历
     */
    render() {
        this._updateTitle();
        
        const calendarContent = document.createElement('div');
        calendarContent.className = 'calendar-content';
        
        switch (this.currentView) {
            case 'month':
                calendarContent.innerHTML = this._renderMonthView();
                break;
            case 'week':
                calendarContent.innerHTML = this._renderWeekView();
                break;
            case 'day':
                calendarContent.innerHTML = this._renderDayView();
                break;
        }
        
        // 移除旧的日历内容
        const oldContent = this.container.querySelector('.calendar-content');
        if (oldContent) {
            oldContent.remove();
        }
        
        this.container.appendChild(calendarContent);
        
        // 绑定日历项事件
        this._bindCalendarItemEvents();
    }
    
    /**
     * 更新标题
     */
    _updateTitle() {
        const titleEl = this.container.querySelector('.calendar-title');
        if (titleEl) {
            const formatter = new Intl.DateTimeFormat('zh-CN', {
                year: 'numeric',
                month: 'long'
            });
            titleEl.textContent = formatter.format(this.currentDate);
        }
    }
    
    /**
     * 渲染月视图
     */
    _renderMonthView() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        // 获取月份的第一天和最后一天
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        
        // 获取月份的天数
        const daysInMonth = lastDay.getDate();
        
        // 获取第一天的星期几（0-6，0表示周日）
        const firstDayOfWeek = firstDay.getDay();
        
        let html = '<div class="calendar-month-view">';
        html += '<div class="calendar-weekdays">';
        
        // 渲染星期标题
        const weekdays = ['日', '一', '二', '三', '四', '五', '六'];
        weekdays.forEach(day => {
            html += `<div class="calendar-weekday">${day}</div>`;
        });
        html += '</div>';
        
        html += '<div class="calendar-days">';
        
        // 填充空白（上个月的天数）
        for (let i = 0; i < firstDayOfWeek; i++) {
            html += '<div class="calendar-day calendar-day-other"></div>';
        }
        
        // 渲染当前月的每一天
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const events = this._getEventsForDate(date);
            const hasEvents = events.length > 0;
            
            html += `<div class="calendar-day ${hasEvents ? 'calendar-day-has-events' : ''}" data-date="${date.toISOString().split('T')[0]}">`;
            html += `<div class="calendar-day-number">${day}</div>`;
            
            if (hasEvents) {
                html += '<div class="calendar-events">';
                events.forEach(event => {
                    html += `<div class="calendar-event" data-id="${event.id}" uk-tooltip="title: ${this._getEventTooltip(event)}; pos: top">`;
                    html += `${this._getEventTitle(event)}`;
                    html += '</div>';
                });
                html += '</div>';
            }
            
            html += '</div>';
        }
        
        // 填充空白（下个月的天数）
        const totalCells = 42; // 6行7列
        const remainingCells = totalCells - firstDayOfWeek - daysInMonth;
        for (let i = 0; i < remainingCells; i++) {
            html += '<div class="calendar-day calendar-day-other"></div>';
        }
        
        html += '</div></div>';
        return html;
    }
    
    /**
     * 渲染周视图
     */
    _renderWeekView() {
        return '<div class="calendar-week-view">周视图（待实现）</div>';
    }
    
    /**
     * 渲染日视图
     */
    _renderDayView() {
        return '<div class="calendar-day-view">日视图（待实现）</div>';
    }
    
    /**
     * 获取指定日期的事件
     */
    _getEventsForDate(date) {
        if (!this.options.selectedDateField) {
            return [];
        }
        
        const dateStr = date.toISOString().split('T')[0];
        
        return this.filteredData.filter(item => {
            const eventDate = this._parseDate(item[this.options.selectedDateField]);
            return eventDate && eventDate.toISOString().split('T')[0] === dateStr;
        });
    }
    
    /**
     * 解析日期字符串
     */
    _parseDate(dateStr) {
        if (!dateStr) return null;
        
        // 尝试不同的日期格式
        const date = new Date(dateStr);
        if (!isNaN(date.getTime())) {
            return date;
        }
        
        // 尝试解析时间戳
        const timestamp = parseInt(dateStr);
        if (!isNaN(timestamp)) {
            return new Date(timestamp);
        }
        
        return null;
    }
    
    /**
     * 获取事件标题
     */
    _getEventTitle(event) {
        // 尝试使用名称字段
        if (event.name) {
            return event.name;
        }
        
        // 尝试使用标题字段
        if (event.title) {
            return event.title;
        }
        
        // 使用第一个字段的值
        const firstField = Object.keys(this.options.fields)[0];
        if (firstField && event[firstField]) {
            return event[firstField].toString().substring(0, 20);
        }
        
        return '未命名事件';
    }
    
    /**
     * 获取事件提示信息
     */
    _getEventTooltip(event) {
        let tooltip = '';
        
        // 添加主要字段信息
        for (const [fieldName, fieldConfig] of Object.entries(this.options.fields)) {
            if (fieldConfig.label && event[fieldName]) {
                tooltip += `${fieldConfig.label}: ${event[fieldName]}\n`;
            }
        }
        
        return tooltip.trim() || '无详细信息';
    }
    
    /**
     * 绑定日历项事件
     */
    _bindCalendarItemEvents() {
        // 绑定事件点击
        const eventElements = this.container.querySelectorAll('.calendar-event');
        eventElements.forEach(element => {
            dom.on(element, 'click', (e) => {
                const eventId = element.getAttribute('data-id');
                this._handleEventClick(eventId);
            });
        });
        
        // 绑定日期点击
        const dayElements = this.container.querySelectorAll('.calendar-day:not(.calendar-day-other)');
        dayElements.forEach(element => {
            dom.on(element, 'click', (e) => {
                const dateStr = element.getAttribute('data-date');
                this._handleDayClick(dateStr);
            });
        });
    }
    
    /**
     * 处理事件点击
     */
    _handleEventClick(eventId) {
        // 跳转到查看页面
        if (this.baseUrl) {
            const viewPath = this.baseUrl.endsWith('/') ?
                `${this.baseUrl}view/about/` + eventId :
                `${this.baseUrl}/view/about/` + eventId;
            window.location.href = viewPath;
        }
    }
    
    /**
     * 处理日期点击
     */
    _handleDayClick(dateStr) {
        console.log('点击日期:', dateStr);
        // 可以在这里实现添加新事件的功能
    }
    
    /**
     * 刷新日历
     */
    refresh() {
        this.render();
    }
    
    /**
     * 设置日期字段
     */
    setDateField(fieldName) {
        if (this.options.dateFields[fieldName]) {
            this.options.selectedDateField = fieldName;
            this.render();
        }
    }
    
    /**
     * 获取当前数据
     */
    getData() {
        return this.filteredData;
    }
}

// 全局注册
if (typeof window !== 'undefined') {
    window.CalendarRender = CalendarRender;
}
