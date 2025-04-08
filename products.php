<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <title>宠物产品列表</title>
  <style>
    /* 全局样式，选用优雅的 Georgia 字体，背景使用宠物主题的图片 */
    body {
      font-family: 'Georgia', serif;
      background: url('pet-background.jpg') no-repeat center center fixed;
      background-size: cover;
      margin: 0;
      padding: 0;
      color: #333;
    }
    /* 页面头部 */
    .header {
      background-color: rgba(255, 255, 255, 0.85);
      padding: 20px;
      text-align: center;
      border-bottom: 2px solid #eee;
    }
    .header h1 {
      margin: 0;
      font-size: 2.5em;
    }
    /* 主体容器 */
    .container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 20px;
      background-color: rgba(255,255,255,0.95);
      border-radius: 8px;
    }
    /* 产品列表使用 CSS Grid 布局 */
    .product-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }
    /* 单个产品卡片 */
    .product-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      background-color: #fff;
      transition: transform 0.3s;
    }
    .product-card:hover {
      transform: scale(1.03);
    }
    /* 产品图片区域 */
    .product-image img {
      width: 100%;
      height: auto;
    }
    /* 产品详情 */
    .product-details {
      padding: 15px;
    }
    .product-details h3 {
      margin: 0 0 10px;
      font-size: 1.4em;
      color: #555;
    }
    .product-details p {
      font-size: 1em;
      margin: 0 0 15px;
      line-height: 1.5;
    }
    /* 按钮样式 */
    .btn {
      display: inline-block;
      padding: 10px 20px;
      background-color: #5a8f7b;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      transition: background-color 0.3s;
    }
    .btn:hover {
      background-color: #487a67;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>高尚宠物商店</h1>
  </div>
  <div class="container">
    <div class="product-grid">
      <!-- 以下部分为示例代码，实际中请用循环遍历从数据库中获取的产品数据 -->
      <div class="product-card">
        <div class="product-image">
          <!-- 替换成数据库中产品图片的路径 -->
          <img src="path/to/product-image.jpg" alt="宠物玩具">
        </div>
        <div class="product-details">
          <!-- 替换为产品名称 -->
          <h3>宠物玩具</h3>
          <!-- 替换为产品描述 -->
          <p>精选优质材料制作，适合各种宠物使用，安全健康。</p>
          <!-- 替换为产品价格 -->
          <p>价格：¥120</p>
          <!-- “加入购物车”按钮，可配置为相应的后端链接或JS函数 -->
          <a href="shopping-cart.html?product_id=1" class="btn">加入购物车</a>
        </div>
      </div>
      <!-- 可根据数据库记录添加更多产品卡片 -->
    </div>
  </div>
</body>
</html>
