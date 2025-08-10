# AI编程规则总结

## 数据库规则
1. 不要对Id使用auto_increment属性，使用utility.php中的randstr()方法生成唯一ID。
2. 采用SQLite数据库，每个租户的数据存储在独立的sqlite文件中，文件路径为：`data/{tenant_id}/db.sqlite`。

## 模块开发规则
1. 创建新模块时，参考tenant模块结构。
2. 数据结构定义在database.md文件中，任何数据结构变更都需要在该文件中更新。
3. PHP是解释执行语言，修改代码后无需重启服务器。
4. 忽略java目录下的所有代码，只关注php代码。

## 文件缓存规则
1. data目录下存储租户数据，每个租户的数据存放在以其id命名的目录中。
2. 配置数据保存两个文件版本：
   - JSON格式：`data/{tenant_id}/conf/{config_id}.json`
   - YAML格式：`data/{tenant_id}/conf/{config_id}.yaml`
3. 配置清单保存在`data/{tenant_id}/conf.json`文件中，包含Id和key的对应清单以及按type汇总的对应清单。
4. 实体更新时保存缓存文件：`data/{tenant_id}/entity/{entity_id}.json`，包含实体的所有数据。

## URI规则
URI格式遵循以下模式：
`{tenant_id}/{entity_type}/{entity_action}/{entity_id}/{submenu}/{submenu_action}/{submenu_entity_id}`

示例：
`default/1erztlh1/view/shngjYJZ/fundposition/add`
表示：default租户下/1erztlh1实体/查看/shngjYJZ数据/fundposition子菜单/add操作

## 编码规范
1. 类属性应在类的顶部声明。
2. 使用文档注释描述方法功能、参数和返回值。
3. 错误处理应包含日志记录。
4. 代码应有适当的缩进和格式。
5. 避免SQL注入，使用db.php中的escape()方法处理输入值。
6. 日志记录时，使用log()方法并确保能处理字符串、数组和对象类型的输入。