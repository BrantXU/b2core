/**
 * DOM 工具库
 * 提供常用的DOM操作工具函数
 */

const dom = (function() {
    'use strict';
    
    /**
     * 通过ID获取元素
     * @param {string} id - 元素ID
     * @returns {HTMLElement|null} 找到的元素或null
     */
    function gid(id) {
        return document.getElementById(id);
    }
    
    /**
     * 通过选择器获取元素
     * @param {string} selector - CSS选择器
     * @param {HTMLElement} [parent=document] - 父元素
     * @returns {HTMLElement|null} 找到的元素或null
     */
    function qs(selector, parent = document) {
        return parent.querySelector(selector);
    }
    
    /**
     * 通过选择器获取所有匹配的元素
     * @param {string} selector - CSS选择器
     * @param {HTMLElement} [parent=document] - 父元素
     * @returns {NodeList} 找到的元素列表
     */
    function qsa(selector, parent = document) {
        return parent.querySelectorAll(selector);
    }
    
    /**
     * 创建新元素
     * @param {string} tag - 标签名
     * @param {Object} [attributes] - 属性对象
     * @param {string} [html] - 内部HTML
     * @returns {HTMLElement} 创建的元素
     */
    function create(tag, attributes = {}, html = '') {
        const el = document.createElement(tag);
        
        // 设置属性
        Object.keys(attributes).forEach(key => {
            if (key === 'className') {
                el.className = attributes[key];
            } else if (key === 'htmlFor') {
                el.htmlFor = attributes[key];
            } else if (key.startsWith('on')) {
                el[key] = attributes[key];
            } else {
                el.setAttribute(key, attributes[key]);
            }
        });
        
        // 设置内部HTML
        if (html) {
            el.innerHTML = html;
        }
        
        return el;
    }
    
    /**
     * 添加事件监听器
     * @param {HTMLElement} el - 目标元素
     * @param {string} event - 事件类型
     * @param {Function} handler - 事件处理函数
     * @param {boolean} [useCapture=false] - 是否使用捕获
     */
    function on(el, event, handler, useCapture = false) {
        if (el && el.addEventListener) {
            el.addEventListener(event, handler, useCapture);
        }
    }
    
    /**
     * 移除事件监听器
     * @param {HTMLElement} el - 目标元素
     * @param {string} event - 事件类型
     * @param {Function} handler - 事件处理函数
     * @param {boolean} [useCapture=false] - 是否使用捕获
     */
    function off(el, event, handler, useCapture = false) {
        if (el && el.removeEventListener) {
            el.removeEventListener(event, handler, useCapture);
        }
    }
    
    /**
     * 显示元素
     * @param {HTMLElement} el - 目标元素
     */
    function show(el) {
        if (el) {
            el.style.display = '';
        }
    }
    
    /**
     * 隐藏元素
     * @param {HTMLElement} el - 目标元素
     */
    function hide(el) {
        if (el) {
            el.style.display = 'none';
        }
    }
    
    /**
     * 切换元素显示状态
     * @param {HTMLElement} el - 目标元素
     */
    function toggle(el) {
        if (el) {
            el.style.display = el.style.display === 'none' ? '' : 'none';
        }
    }
    
    /**
     * 添加CSS类
     * @param {HTMLElement} el - 目标元素
     * @param {string} className - CSS类名
     */
    function addClass(el, className) {
        if (el && className) {
            el.classList.add(className);
        }
    }
    
    /**
     * 移除CSS类
     * @param {HTMLElement} el - 目标元素
     * @param {string} className - CSS类名
     */
    function removeClass(el, className) {
        if (el && className) {
            el.classList.remove(className);
        }
    }
    
    /**
     * 切换CSS类
     * @param {HTMLElement} el - 目标元素
     * @param {string} className - CSS类名
     */
    function toggleClass(el, className) {
        if (el && className) {
            el.classList.toggle(className);
        }
    }
    
    /**
     * 检查是否包含CSS类
     * @param {HTMLElement} el - 目标元素
     * @param {string} className - CSS类名
     * @returns {boolean} 是否包含该类
     */
    function hasClass(el, className) {
        return el && className ? el.classList.contains(className) : false;
    }
    
    /**
     * 设置元素文本内容
     * @param {HTMLElement} el - 目标元素
     * @param {string} text - 文本内容
     */
    function text(el, text) {
        if (el) {
            el.textContent = text;
        }
    }
    
    /**
     * 获取元素文本内容
     * @param {HTMLElement} el - 目标元素
     * @returns {string} 文本内容
     */
    function getText(el) {
        return el ? el.textContent : '';
    }
    
    /**
     * 设置元素HTML内容
     * @param {HTMLElement} el - 目标元素
     * @param {string} html - HTML内容
     */
    function html(el, html) {
        if (el) {
            el.innerHTML = html;
        }
    }
    
    /**
     * 获取元素HTML内容
     * @param {HTMLElement} el - 目标元素
     * @returns {string} HTML内容
     */
    function getHtml(el) {
        return el ? el.innerHTML : '';
    }
    
    /**
     * 设置元素值（针对表单元素）
     * @param {HTMLElement} el - 目标元素
     * @param {string} value - 值
     */
    function val(el, value) {
        if (el) {
            el.value = value;
        }
    }
    
    /**
     * 获取元素值（针对表单元素）
     * @param {HTMLElement} el - 目标元素
     * @returns {string} 值
     */
    function getVal(el) {
        return el ? el.value : '';
    }
    
    /**
     * 设置元素属性
     * @param {HTMLElement} el - 目标元素
     * @param {string} name - 属性名
     * @param {string} value - 属性值
     */
    function attr(el, name, value) {
        if (el && name) {
            el.setAttribute(name, value);
        }
    }
    
    /**
     * 获取元素属性
     * @param {HTMLElement} el - 目标元素
     * @param {string} name - 属性名
     * @returns {string|null} 属性值
     */
    function getAttr(el, name) {
        return el && name ? el.getAttribute(name) : null;
    }
    
    /**
     * 移除元素属性
     * @param {HTMLElement} el - 目标元素
     * @param {string} name - 属性名
     */
    function removeAttr(el, name) {
        if (el && name) {
            el.removeAttribute(name);
        }
    }
    
    /**
     * 设置元素样式
     * @param {HTMLElement} el - 目标元素
     * @param {Object} styles - 样式对象
     */
    function css(el, styles) {
        if (el && styles) {
            Object.keys(styles).forEach(property => {
                el.style[property] = styles[property];
            });
        }
    }
    
    /**
     * 获取计算样式
     * @param {HTMLElement} el - 目标元素
     * @param {string} property - CSS属性名
     * @returns {string} 计算后的样式值
     */
    function getStyle(el, property) {
        return el && property ? window.getComputedStyle(el)[property] : '';
    }
    
    /**
     * 添加子元素
     * @param {HTMLElement} parent - 父元素
     * @param {HTMLElement} child - 子元素
     */
    function append(parent, child) {
        if (parent && child) {
            parent.appendChild(child);
        }
    }
    
    /**
     * 在指定元素前插入元素
     * @param {HTMLElement} parent - 父元素
     * @param {HTMLElement} newEl - 要插入的新元素
     * @param {HTMLElement} referenceEl - 参考元素
     */
    function insertBefore(parent, newEl, referenceEl) {
        if (parent && newEl && referenceEl) {
            parent.insertBefore(newEl, referenceEl);
        }
    }
    
    /**
     * 移除元素
     * @param {HTMLElement} el - 要移除的元素
     */
    function remove(el) {
        if (el && el.parentNode) {
            el.parentNode.removeChild(el);
        }
    }
    
    /**
     * 清空元素内容
     * @param {HTMLElement} el - 目标元素
     */
    function empty(el) {
        if (el) {
            el.innerHTML = '';
        }
    }
    
    /**
     * 克隆元素
     * @param {HTMLElement} el - 目标元素
     * @param {boolean} [deep=true] - 是否深度克隆
     * @returns {HTMLElement} 克隆的元素
     */
    function clone(el, deep = true) {
        return el ? el.cloneNode(deep) : null;
    }
    
    /**
     * 获取父元素
     * @param {HTMLElement} el - 目标元素
     * @returns {HTMLElement|null} 父元素
     */
    function parent(el) {
        return el ? el.parentNode : null;
    }
    
    /**
     * 获取子元素
     * @param {HTMLElement} el - 目标元素
     * @returns {HTMLCollection} 子元素集合
     */
    function children(el) {
        return el ? el.children : [];
    }
    
    /**
     * 获取下一个兄弟元素
     * @param {HTMLElement} el - 目标元素
     * @returns {HTMLElement|null} 下一个兄弟元素
     */
    function next(el) {
        return el ? el.nextElementSibling : null;
    }
    
    /**
     * 获取上一个兄弟元素
     * @param {HTMLElement} el - 目标元素
     * @returns {HTMLElement|null} 上一个兄弟元素
     */
    function prev(el) {
        return el ? el.previousElementSibling : null;
    }
    
    /**
     * 触发自定义事件
     * @param {HTMLElement} el - 目标元素
     * @param {string} eventName - 事件名称
     * @param {Object} [detail] - 事件详情
     */
    function trigger(el, eventName, detail = {}) {
        if (el) {
            const event = new CustomEvent(eventName, {
                bubbles: true,
                cancelable: true,
                detail: detail
            });
            el.dispatchEvent(event);
        }
    }
    
    /**
     * 防抖函数
     * @param {Function} func - 要防抖的函数
     * @param {number} wait - 等待时间(毫秒)
     * @returns {Function} 防抖后的函数
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * 节流函数
     * @param {Function} func - 要节流的函数
     * @param {number} limit - 时间限制(毫秒)
     * @returns {Function} 节流后的函数
     */
    function throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    // 公开API
    return {
        gid,
        qs,
        qsa,
        create,
        on,
        off,
        show,
        hide,
        toggle,
        addClass,
        removeClass,
        toggleClass,
        hasClass,
        text,
        getText,
        html,
        getHtml,
        val,
        getVal,
        attr,
        getAttr,
        removeAttr,
        css,
        getStyle,
        append,
        insertBefore,
        remove,
        empty,
        clone,
        parent,
        children,
        next,
        prev,
        trigger,
        debounce,
        throttle
    };
})();

// 全局注册
if (typeof window !== 'undefined') {
    window.dom = dom;
}