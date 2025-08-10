/**
 * 表格增强器 - 将传统表格转换为支持分页、搜索和排序的表格
 * @author B2Core
 * @version 1.0.0
 */

class TableEnhancer {
    constructor(tableId, options = {}) {
        this.table = dom.gid(tableId);
        if (!this.table) {
            throw new Error(`Table with id "${tableId}" not found`);
        }
        
        // 默认配置
        this.options = {
            pageSize: 10,
            pageSizes: [10, 20, 50, 100],
            showInfo: true,
            showPagination: true,
            searchable: true,
            sortable: true,
            searchPlaceholder: '搜索...',
            ...options
        };
        
        this.originalData = [];
        this.filteredData = [];
        this.currentPage = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        
        this.init();
    }
    
    init() {
        this.extractData();
        this.createControls();
        this.bindEvents();
        this.render();
    }
    
    extractData() {
        const tbody = dom.qs('tbody', this.table);
        const rows = tbody.querySelectorAll('tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const data = {
                element: row,
                cells: Array.from(cells).map(cell => cell.textContent.trim())
            };
            this.originalData.push(data);
        });
        
        this.filteredData = [...this.originalData];
    }
    
    createControls() {
        const container = this.table.parentElement;
        
        // 创建控制面板
        const controlPanel = document.createElement('div');
        controlPanel.className = 'table-enhancer-controls uk-margin';
        controlPanel.innerHTML = `
            <div class="uk-flex uk-flex-between uk-flex-middle">
                ${this.options.searchable ? `
                    <div class="uk-search uk-search-default">
                        <span uk-search-icon></span>
                        <input class="uk-search-input" type="search" placeholder="${this.options.searchPlaceholder}" id="${this.table.id}-search">
                    </div>
                ` : ''}
                
                ${this.options.showPagination ? `
                    <div class="uk-flex uk-flex-middle">
                        <select class="uk-select uk-form-small uk-margin-right" id="${this.table.id}-pageSize" style="width: auto;">
                            ${this.options.pageSizes.map(size => 
                                `<option value="${size}" ${size === this.options.pageSize ? 'selected' : ''}>${size}条/页</option>`
                            ).join('')}
                        </select>
                    </div>
                ` : ''}
            </div>
        `;
        
        container.insertBefore(controlPanel, this.table);
        
        // 创建信息面板
        if (this.options.showInfo) {
            const infoPanel = document.createElement('div');
            infoPanel.className = 'table-enhancer-info uk-margin uk-text-center';
            infoPanel.innerHTML = `
                <span id="${this.table.id}-info"></span>
            `;
            container.insertBefore(infoPanel, this.table.nextSibling);
        }
        
        // 创建分页
        if (this.options.showPagination) {
            const pagination = document.createElement('div');
            pagination.className = 'table-enhancer-pagination uk-margin uk-flex uk-flex-center';
            pagination.innerHTML = `
                <ul class="uk-pagination" id="${this.table.id}-pagination"></ul>
            `;
            container.insertBefore(pagination, this.table.nextSibling);
        }
    }
    
    bindEvents() {
        // 搜索事件
        if (this.options.searchable) {
            const searchInput = dom.gid(`${this.table.id}-search`);
            searchInput.addEventListener('input', (e) => {
                this.search(e.target.value);
            });
        }
        
        // 分页大小事件
        if (this.options.showPagination) {
            const pageSizeSelect = dom.gid(`${this.table.id}-pageSize`);
            pageSizeSelect.addEventListener('change', (e) => {
                this.options.pageSize = parseInt(e.target.value);
                this.currentPage = 1;
                this.render();
            });
        }
        
        // 排序事件
        if (this.options.sortable) {
            const headers = this.table.querySelectorAll('thead th');
            headers.forEach((header, index) => {
                header.style.cursor = 'pointer';
                dom.off(header, 'click'); // 先移除旧事件
                dom.on(header, 'click', () => {
                    this.sort(index);
                });
            });
        }
    }
    
    search(query) {
        const searchTerm = query.toLowerCase().trim();
        
        if (!searchTerm) {
            this.filteredData = [...this.originalData];
        } else {
            this.filteredData = this.originalData.filter(item => 
                item.cells.some(cell => cell.toLowerCase().includes(searchTerm))
            );
        }
        
        this.currentPage = 1;
        this.render();
    }
    
    sort(columnIndex) {
        if (this.sortColumn === columnIndex) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = columnIndex;
            this.sortDirection = 'asc';
        }
        
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
        
        this.render();
    }
    
    render() {
        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';
        
        if (this.filteredData.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = `<td colspan="${this.table.querySelectorAll('thead th').length}" class="uk-text-center">没有找到匹配的数据</td>`;
            tbody.appendChild(row);
            this.updateInfo();
            this.renderPagination();
            return;
        }
        
        const startIndex = (this.currentPage - 1) * this.options.pageSize;
        const endIndex = Math.min(startIndex + this.options.pageSize, this.filteredData.length);
        
        for (let i = startIndex; i < endIndex; i++) {
            tbody.appendChild(this.filteredData[i].element.cloneNode(true));
        }
        
        this.updateInfo();
        this.renderPagination();
    }
    
    updateInfo() {
        const infoElement = dom.gid(`${this.table.id}-info`);
        if (infoElement) {
            const start = (this.currentPage - 1) * this.options.pageSize + 1;
            const end = Math.min(this.currentPage * this.options.pageSize, this.filteredData.length);
            infoElement.textContent = `显示 ${start}-${end} 条，共 ${this.filteredData.length} 条记录`;
        }
    }
    
    renderPagination() {
        if (!this.options.showPagination) return;
        
        const pagination = dom.gid(`${this.table.id}-pagination`);
        if (!pagination) return;
        
        const totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
        
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }
        
        let html = '';
        
        // 上一页
        const prevDisabled = this.currentPage === 1 ? 'uk-disabled' : '';
        html += `<li class="${prevDisabled}"><a href="#" data-page="${this.currentPage - 1}"><span uk-pagination-previous></span></a></li>`;
        
        // 页码
        const startPage = Math.max(1, this.currentPage - 2);
        const endPage = Math.min(totalPages, this.currentPage + 2);
        
        if (startPage > 1) {
            html += `<li><a href="#" data-page="1">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="uk-disabled"><span>...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const active = i === this.currentPage ? 'uk-active' : '';
            html += `<li class="${active}"><a href="#" data-page="${i}">${i}</a></li>`;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="uk-disabled"><span>...</span></li>`;
            }
            html += `<li><a href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }
        
        // 下一页
        const nextDisabled = this.currentPage === totalPages ? 'uk-disabled' : '';
        html += `<li class="${nextDisabled}"><a href="#" data-page="${this.currentPage + 1}"><span uk-pagination-next></span></a></li>`;
        
        pagination.innerHTML = html;
        
        // 绑定分页事件
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
    refresh() {
        this.extractData();
        this.filteredData = [...this.originalData];
        this.currentPage = 1;
        this.render();
    }
    
    getData() {
        return this.filteredData;
    }
    
    goToPage(page) {
        const totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.render();
        }
    }
}

// 使用示例：
// const enhancer = new TableEnhancer('myTable', {
//     pageSize: 20,
//     searchable: true,
//     sortable: true
// });

// 全局注册
if (typeof window !== 'undefined') {
    window.TableEnhancer = TableEnhancer;
}