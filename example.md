# 用户档案管理示例

本示例展示如何使用 B2Core 框架实现用户档案的完整 CRUD（创建、读取、更新、删除）操作。

## 1. 数据库设计

```sql
-- db/profile.sql
CREATE TABLE user_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    nickname VARCHAR(50),
    avatar VARCHAR(255),
    gender ENUM('male', 'female', 'other'),
    birthday DATE,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## 2. 模型层实现

```php
// app/m/profile.php
class ProfileModel extends Model {
    // 创建用户档案
    public function create($data) {
        $sql = "INSERT INTO user_profiles (user_id, nickname, avatar, gender, birthday, bio) 
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->query($sql, [
            $data['user_id'],
            $data['nickname'],
            $data['avatar'] ?? null,
            $data['gender'] ?? 'other',
            $data['birthday'] ?? null,
            $data['bio'] ?? ''
        ]);
    }

    // 获取用户档案
    public function getByUserId($userId) {
        $sql = "SELECT * FROM user_profiles WHERE user_id = ?";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->fetch();
    }

    // 更新用户档案
    public function update($userId, $data) {
        $sql = "UPDATE user_profiles 
                SET nickname = ?, avatar = ?, gender = ?, birthday = ?, bio = ? 
                WHERE user_id = ?";
        return $this->query($sql, [
            $data['nickname'],
            $data['avatar'],
            $data['gender'],
            $data['birthday'],
            $data['bio'],
            $userId
        ]);
    }

    // 删除用户档案
    public function delete($userId) {
        $sql = "DELETE FROM user_profiles WHERE user_id = ?";
        return $this->query($sql, [$userId]);
    }

    // 获取用户列表（带分页）
    public function getList($page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, u.username 
                FROM user_profiles p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        return $this->query($sql, [$perPage, $offset])->fetchAll();
    }
}
```

## 3. 控制器实现

```php
// app/c/profile.php
class ProfileController {
    private $profileModel;
    
    public function __construct() {
        $this->profileModel = new ProfileModel();
    }

    // 显示创建表单
    public function create() {
        // 检查用户登录状态
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        require 'app/v/profile/create.php';
    }

    // 处理创建请求
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'nickname' => $_POST['nickname'],
            'gender' => $_POST['gender'],
            'birthday' => $_POST['birthday'],
            'bio' => $_POST['bio']
        ];

        // 处理头像上传
        if (isset($_FILES['avatar'])) {
            $avatar = $this->handleFileUpload($_FILES['avatar']);
            if ($avatar) {
                $data['avatar'] = $avatar;
            }
        }

        try {
            $this->profileModel->create($data);
            header('Location: /profile/view');
        } catch (Exception $e) {
            // 处理错误
            require 'app/v/profile/create.php';
        }
    }

    // 显示编辑表单
    public function edit() {
        $profile = $this->profileModel->getByUserId($_SESSION['user_id']);
        require 'app/v/profile/edit.php';
    }

    // 处理更新请求
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        $data = [
            'nickname' => $_POST['nickname'],
            'gender' => $_POST['gender'],
            'birthday' => $_POST['birthday'],
            'bio' => $_POST['bio']
        ];

        try {
            $this->profileModel->update($_SESSION['user_id'], $data);
            header('Location: /profile/view');
        } catch (Exception $e) {
            // 处理错误
            $profile = $data;
            require 'app/v/profile/edit.php';
        }
    }

    // 处理删除请求
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            exit;
        }

        try {
            $this->profileModel->delete($_SESSION['user_id']);
            header('Location: /');
        } catch (Exception $e) {
            // 处理错误
            header('Location: /profile/view');
        }
    }
}
```

## 4. 视图实现

### 创建表单
```php
<!-- app/v/profile/create.php -->
<div>
    <h2>创建个人档案</h2>
    <form method="post" class="pure-form pure-form-stacked" enctype="multipart/form-data">
        <div>
            <label>昵称</label>
            <input type="text" name="nickname" value="<?=isset($data['nickname']) ? htmlspecialchars($data['nickname']) : ''?>" required />
        </div>

        <div>
            <label>性别</label>
            <select name="gender">
                <option value="male">男</option>
                <option value="female">女</option>
                <option value="other">其他</option>
            </select>
        </div>

        <div>
            <label>生日</label>
            <input type="date" name="birthday" value="<?=isset($data['birthday']) ? $data['birthday'] : ''?>" />
        </div>

        <div>
            <label>头像</label>
            <input type="file" name="avatar" accept="image/*" />
        </div>

        <div>
            <label>个人简介</label>
            <textarea name="bio"><?=isset($data['bio']) ? htmlspecialchars($data['bio']) : ''?></textarea>
        </div>

        <div>
            <button type="submit" class="pure-button pure-button-primary">保存</button>
            <a href="/profile/view" class="pure-button">返回</a>
        </div>
    </form>
</div>
```

### 编辑表单
```php
<!-- app/v/profile/edit.php -->
<div>
    <h2>编辑个人档案</h2>
    <form method="post" class="pure-form pure-form-stacked" enctype="multipart/form-data">
        <!-- 与创建表单类似，但需要填充现有数据 -->
        <div>
            <label>昵称</label>
            <input type="text" name="nickname" value="<?=htmlspecialchars($profile['nickname'])?>" required />
        </div>
        <!-- 其他字段类似 -->
        <div>
            <button type="submit" class="pure-button pure-button-primary">更新</button>
            <a href="/profile/view" class="pure-button">取消</a>
        </div>
    </form>
</div>
```

### 查看页面
```php
<!-- app/v/profile/view.php -->
<div>
    <h2>个人档案</h2>
    <?php if ($profile): ?>
        <div>
            <?php if ($profile['avatar']): ?>
                <img src="<?=htmlspecialchars($profile['avatar'])?>" alt="头像" />
            <?php endif; ?>
            <p>昵称：<?=htmlspecialchars($profile['nickname'])?></p>
            <p>性别：<?=htmlspecialchars($profile['gender'])?></p>
            <p>生日：<?=$profile['birthday']?></p>
            <p>简介：<?=htmlspecialchars($profile['bio'])?></p>
            
            <div>
                <a href="/profile/edit" class="pure-button">编辑</a>
                <form method="post" action="/profile/delete" style="display: inline;">
                    <button type="submit" class="pure-button" onclick="return confirm('确定要删除吗？')">删除</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <p>未创建个人档案</p>
        <a href="/profile/create" class="pure-button pure-button-primary">创建档案</a>
    <?php endif; ?>
</div>
```

## 5. 使用方法

1. 首先导入数据库表结构：
```bash
mysql -u username -p database_name < db/profile.sql
```

2. 访问相应的URL：
- 创建档案：`/profile/create`
- 查看档案：`/profile/view`
- 编辑档案：`/profile/edit`
- 删除档案：通过表单POST请求到 `/profile/delete`

## 6. 安全考虑

1. 所有用户输入都经过 HTML 转义处理
2. 文件上传限制类型和大小
3. 验证用户登录状态
4. 使用 PDO 预处理语句防止 SQL 注入
5. 表单提交使用 POST 方法
6. 删除操作需要确认 