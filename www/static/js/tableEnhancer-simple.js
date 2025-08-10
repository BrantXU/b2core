/**
 * 简化的表格增强器 - 一行代码转换传统表格
 * @param {string} tableId - 表格ID
 * @param {object} options - 配置选项
 */
function enhanceTable(tableId, options = {}) {
    const defaults = {
        pageSize: 10,
        searchable: true,
        sortable: true,
        showInfo: true,
        showPagination: true
    };
    
    const config = { ...defaults, ...options };
    
    const table = document.getElementById(tableId);
    if (!table) {
        console.error(`Table ${tableId} not found`);
        return;
    }
    
    // 存储原始数据
    const tbody = table.querySelector('tbody');
    const originalRows = Array.from(tbody.querySelectorAll('tr'));
    let filteredRows = [...originalRows];
    let currentPage = 1;
    let sortColumn = -1;
    let sortDirection = 'asc';
    
    // 创建控制元素
    createControls();
    bindEvents();
    render();
    
    function createControls() {
        const container = table.parentElement;
        
        // 搜索框
        if (config.searchable) {
            const searchDiv = document.createElement('div');
            searchDiv.className = 'uk-margin';
            searchDiv.innerHTML = `
                <div class="uk-search uk-search-default">
                    <span uk-search-icon></span>
                    <input class="uk-search-input" type="search" placeholder="搜索..." id="${tableId}-search">
                </div>
            `;
            container.insertBefore(searchDiv, table);
        }
        
        // 信息面板
        if (config.showInfo) {
            const infoDiv = document.createElement('div');
            infoDiv.className = 'uk-text-center uk-margin-small';
            infoDiv.innerHTML = `<span id="${tableId}-info">共 ${originalRows.length} 条记录</span>`;
            container.insertBefore(infoDiv, table.nextSibling);
        }
        
        // 分页
        if (config.showPagination) {
            const paginationDiv = document.createElement('div');
            paginationDiv.className = 'uk-flex uk-flex-center uk-margin';
            paginationDiv.innerHTML = `<ul class="uk-pagination" id="${tableId}-pagination"></ul>`;
            container.insertBefore(paginationDiv, table.nextSibling);
        }
    }
    
    function bindEvents() {
        // 搜索
        if (config.searchable) {
            document.getElementById(`${tableId}-search`).addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                filteredRows = originalRows.filter(row => {
                    const text = Array.from(row.querySelectorAll('td'))
                        .map(td => td.textContent.trim().toLowerCase())
                        .join(' ');
                    return text.includes(query);
                });
                currentPage = 1;
                render();
            });
        }
        
        // 排序
        if (config.sortable) {
            const headers = table.querySelectorAll('thead th');
            headers.forEach((header, index) => {
                header.style.cursor = 'pointer';
                header.addEventListener('click', () => {
                    sortByColumn(index);
                });
            });
        }
    }
    
    function sortByColumn(columnIndex) {
        if (sortColumn === columnIndex) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = columnIndex;
            sortDirection = 'asc';
        }
        
        filteredRows.sort((a, b) => {
            const aText = a.querySelectorAll('td')[columnIndex].textContent.trim();
            const bText = b.querySelectorAll('td')[columnIndex].textContent.trim();
            
            // 尝试数字排序
            const aNum = parseFloat(aText);
            const bNum = parseFloat(bText);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return sortDirection === 'asc' ? aNum - bNum : bNum - aNum;
            }
            
            // 字符串排序
            return sortDirection === 'asc' 
                ? aText.localeCompare(bText) 
                : bText.localeCompare(aText);
        });
        
        render();
    }
    
    function render() {
        const start = (currentPage - 1) * config.pageSize;
        const end = Math.min(start + config.pageSize, filteredRows.length);
        
        // 清空并重新填充
        tbody.innerHTML = '';
        for (let i = start; i < end; i++) {
            tbody.appendChild(filteredRows[i]);
        }
        
        // 更新信息
        if (config.showInfo) {
            const info = document.getElementById(`${tableId}-info`);
            if (info) {
                const startRecord = start + 1;
                info.textContent = `显示 ${startRecord}-${end} 条，共 ${filteredRows.length} 条记录`;
            }
        }
        
        // 更新分页
        if (config.showPagination) {
            renderPagination();
        }
    }
    
    function renderPagination() {
        const totalPages = Math.ceil(filteredRows.length / config.pageSize);
        const pagination = document.getElementById(`${tableId}-pagination`);
        
        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }
        
        let html = '';
        
        // 上一页
        html += `<li ${currentPage === 1 ? 'class="uk-disabled"' : ''}>
            <a href="#" data-page="${currentPage - 1}">&lt;</a>
        </li>`;
        
        // 页码
        for (let i = 1; i <= totalPages; i++) {
            const active = i === currentPage ? 'uk-active' : '';
            html += `<li class="${active}"><a href="#" data-page="${i}">${i}</a></li>`;
        }
        
        // 下一页
        html += `<li ${currentPage === totalPages ? 'class="uk-disabled"' : ''}>
            <a href="#" data-page="${currentPage + 1}">&gt;</a>
        </li>`;
        
        pagination.innerHTML = html;
        
        // 绑定事件
        pagination.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(e.target.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages) {
                    currentPage = page;
                    render();
                }
            });
        });
    }
    
    // 返回API对象
    return {
        refresh: function() {
            filteredRows = [...originalRows];
            currentPage = 1;
            render();
        },
        goToPage: function(page) {
            const totalPages = Math.ceil(filteredRows.length / config.pageSize);
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                render();
            }
        },
        getData: function() {
            return filteredRows.map(row => {
                return Array.from(row.querySelectorAll('td')).map(td => td.textContent.trim());
            });
        }
    };
}

// 全局注册
if (typeof window !== 'undefined') {
    window.enhanceTable = enhanceTable;
}