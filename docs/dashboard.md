config中有一个类型是 layout ， 它的value是一个数组， 数组中的每个元素都是一个对象， 对象中包含了控件的配置信息。

layout 是自定义排版，他会被用在 dashboard 中。如果没有特殊定义，当 Menu 菜单中的类别是 dashboard 时， 会读取 {entity}_view_dashboard 为 Key 的数据。并用 value 中的内容渲染当前页面。

### layout 配置

layout 配置是一个数组，数组中的每个元素都是一个对象，对象中包含了控件的配置信息。

每个对象的配置信息如下：

- `type`：控件的类型，例如 `text`、`chart`、`table` 等。
- `title`：控件的标题，用于显示在页面上。
- `width`：控件的宽度，单位为网格单位。页面分为 12 列，每个控件的宽度可以是 1-12 之间的任意值。
- `height`：控件的高度，单位为像素。
- `options`：控件的配置选项，根据不同的类型可能会有不同的配置项。

### 不同类型描述

 - text ： 文本控件，用于显示静态文本。
 - chart ： 图表控件，用于显示数据图表。
 - table ： 表格控件，用于显示数据表格。


### 示例

下面是一个简单的 layout 配置示例：

```json
{
  "layout": [
    {
      "type": "text",
      "title": "欢迎来到我的仪表盘",
      "width": 12,
      "height": 4,
      "options": {
        "text": "这是一个自定义的仪表盘"
      }
    },
    {
      "type": "chart",
      "title": "销售趋势",
      "width": 6,
      "height": 4,
      "options": {
        "type": "line",
        "data": {
          "labels": ["周一", "周二", "周三", "周四", "周五", "周六", "周日"],
          "datasets": [{
            "label": "销售额",
            "data": [120, 190, 300, 500, 200, 300, 400]
          }]
        }
      }
    }
  ]
}
```