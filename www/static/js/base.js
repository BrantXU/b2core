/**
 * DOM操作简写库
 * @author B2Core
 * @version 1.0.0
 */

// 元素选择简写
const gid = id => document.getElementById(id);
const qs = selector => document.querySelector(selector);
const qsa = selector => document.querySelectorAll(selector);

// 事件监听简写
const on = (el, event, handler) => el.addEventListener(event, handler);
const off = (el, event, handler) => el.removeEventListener(event, handler);

// 类操作简写
const addClass = (el, className) => el.classList.add(className);
const removeClass = (el, className) => el.classList.remove(className);
const toggleClass = (el, className) => el.classList.toggle(className);

// 元素创建简写
const create = (tag, options = {}) => {
  const el = document.createElement(tag);
  Object.entries(options).forEach(([key, value]) => {
    el[key] = value;
  });
  return el;
};

// 批量属性设置
const setAttrs = (el, attrs) => {
  Object.entries(attrs).forEach(([key, value]) => {
    el.setAttribute(key, value);
  });
};

// AJAX简写
const fetchJSON = async (url, options) => {
  const response = await fetch(url, options);
  return response.json();
};

// 表单操作
const formDataToJSON = form => {
  return Object.fromEntries(new FormData(form));
};

// 全局导出
if (typeof window !== 'undefined') {
  window.dom = {
    gid,
    qs,
    qsa,
    on,
    off,
    addClass,
    removeClass,
    toggleClass,
    create,
    setAttrs,
    fetchJSON,
    formDataToJSON
  };
}

/*

// 使用方法示例：
const header = dom.gid('header');
dom.on(header, 'click', handler);
const newButton = dom.create('button', { textContent: '提交' });

// 包含以下简写方法：

- gid()       → getElementById
- qs()        → querySelector
- qsa()       → querySelectorAll
- on()        → addEventListener
- off()       → removeEventListener
- create()    → createElement增强版
- fetchJSON() → 封装fetch+json解析
- 类操作方法 addClass/removeClass/toggleClass
- 表单操作 formDataToJSON()

所有方法通过 dom 对象全局访问，保持命名空间整洁。
*/