# B2Core Java版本

这是B2Core框架的Java版本实现，参考了PHP版本的结构和功能。

## 项目结构

```
src/
├── main/
│   ├── java/
│   │   └── com/b2core/
│   │       ├── controller/
│   │       ├── library/
│   │       ├── model/
│   │       └── B2Core.java
│   └── resources/
│       ├── config/
│       └── views/
└── test/
    └── java/
        └── com/b2core/
```

## 功能特性

- MVC架构模式
- 路由处理
- 数据库操作（支持SQLite和MySQL）
- YAML配置解析
- 视图渲染

## 构建和运行

使用Maven构建项目：

```bash
mvn clean package
```

运行应用：

```bash
java -jar target/b2core-java-1.0.0.jar
```

## 配置

应用配置文件位于 `src/main/resources/config/` 目录下：

- `application.properties`: 应用配置
- `database.properties`: 数据库配置
- `routes.properties`: 路由配置

## 许可证

MIT License