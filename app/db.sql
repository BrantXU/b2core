CREATE DATABASE `b2core` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `b2core`;

DROP TABLE IF EXISTS `tb_user`;
CREATE TABLE IF NOT EXISTS `tb_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `tb_user` (`id`, `username`, `password`, `email`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin@b24.cn');

-- 创建配置表
DROP TABLE IF EXISTS `tb_config`;
CREATE TABLE IF NOT EXISTS `tb_config` (
  `id` VARCHAR(8) PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT NOT NULL,
  `description` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 插入默认配置
INSERT INTO `tb_config` (`id`, `key`, `value`, `description`) 
VALUES ('12345678', 'site_name', 'B2Core系统', '网站名称');

