# Facebook广告投流课程单页站（PHP）- V4

本版本新增后台管理：
- 产品管理（新增/编辑/上下架）
- 订单列表（查看是否已支付）
- 后台配置（支付宝、USDT、Facebook像素/CAPI）

## 启动

```bash
cp lib/config.example.php lib/config.php
php -S 0.0.0.0:8000
```

前台：`http://localhost:8000`
后台：`http://localhost:8000/admin/login.php`

默认后台账号在 `lib/config.php` 中配置：
- username: `admin`
- password: `ChangeThisPassword123!`（请上线前修改）

## 页面说明

- `index.php`：课程销售单页，选择产品 + 邮箱 + 支付方式
- `checkout.php`：创建订单
- `mock_payment.php`：Demo支付页（演示）
- `webhook.php`：支付回调，更新订单、发邮件、回传CAPI
- `success.php`：支付成功页（触发前端Purchase像素）
- `admin/products.php`：产品管理
- `admin/orders.php`：订单列表
- `admin/settings.php`：USDT/支付宝/Facebook配置

## 打包下载

```bash
./scripts/package.sh
```

输出包路径：`dist/facebook-course-site-v4.zip`

## 生产建议

- 关闭 demo_mode 并对接真实 USDT / 支付宝网关
- `webhook.php` 增加签名校验
- 邮件改 SMTP 服务发送
