1. 使用数据库时，不要对 Id 使用 auto_increment 属性。使用 utility.php 中的 randstr（） 方法生成。
2. 创建新模块时，参考 tenant 模块结构。
3. 数据结构在 database.md 文件中，任何数据结构的变更都需要在该文件中进行更新。
4. php 是解释执行的，不需要编译，修改万代码以后无需重启服务器。
5. 忽略 java 目录下所有代码，只关注 php 代码。
