drop table if exists `love_page`;
create table if not exists `love_page` (
  id int not null auto_increment ,
  url char(255) comment '地址' ,
  page int comment '页数' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '抓取的页数 by cxl';

drop table if exists `love_detail`;
create table if not exists `love_detail` (
  id int not null auto_increment ,
  url char(255) comment '地址' ,
  page_id int comment 'love_page.id' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '抓取的详情页 by cxl';

drop table if exists `love_error_log`;
create table if not exists `love_error_log` (
  id int not null auto_increment ,
  url char(255) comment '地址' ,
  content longtext comment '解析出错的内容' ,
  remark text comment '备注' ,
  create_time datetime default current_timestamp ,
  primary key `id` (`id`)
) engine = innodb character set = utf8mb4 collate = utf8mb4_bin comment '错误日志 by cxl';