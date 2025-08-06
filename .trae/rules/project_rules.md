1. 使用数据库时，不要对 Id 使用 auto_increment 属性。使用 utility.php 中的 randstr（） 方法生成。
2. 创建新模块时，参考 tenant 模块结构。
3. 数据结构在 database.md 文件中，任何数据结构的变更都需要在该文件中进行更新。
4. php 是解释执行的，不需要编译，修改万代码以后无需重启服务器。
5. 忽略 java 目录下所有代码，只关注 php 代码。
6. data 下是租户的数据，每个租户的数据都在自己的目录下，目录名是租户的 id。
7. 每一条配置数据会会保存两个文件版本，分别是 data/{tenant_id}/conf/{config_id}.json  和 data/{tenant_id}/conf/{config_id}.yaml, 文件内容是数据表的 value 字段的 json 字符串中的数据。
8. 同时会保存一个配置清单在 data/{tenant_id}/conf.json 文件中，这个文件包含两类数据，一类是 Id 和 key 的对应清单，另外一个是 按照 type 汇总后的 Id 和 key 的对应清单。
9. 采用 sqlite 数据库，租户的数据存在一个独立 sqlite 文件中，文件地址为 data/{tenant_id}/db.sqlite