<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Paradise - 宠物商城</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary-green: #5C8D89;
        --accent-orange: #F4A261;
        --light-beige: #E9E2D0;
        --deep-blue: #3D5A6C;
    }

    /* 基础样式 */
    body {
        font-family: 'Nunito Sans', sans-serif;
        margin: 0;
        background: var(--light-beige);
    }

    /* 产品列表页样式 */
    .product-list {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        padding: 2rem;
    }

    .product-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-image {
        height: 250px;
        background-size: cover;
        position: relative;
    }

    .paw-button {
        background: var(--accent-orange);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        position: relative;
    }

    .paw-button::after {
        content: '';
        background: url('data:image/svg+xml,<svg ...>') no-repeat; /* 爪印SVG */
        position: absolute;
        width: 24px;
        height: 24px;
        top: 8px;
        left: 8px;
    }

    /* 购物车样式 */
    .cart-container {
        max-width: 1200px;
        margin: 2rem auto;
        background: white;
        border-radius: 12px;
        padding: 2rem;
    }

    .cart-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid #eee;
    }

    .bowl-counter {
        display: flex;
        align-items: center;
    }

    .bowl-btn {
        width: 32px;
        height: 32px;
        border: none;
        background: var(--light-beige);
        border-radius: 8px;
        cursor: pointer;
    }

    /* 响应式设计 */
    @media (max-width: 768px) {
        .product-list {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    </style>
</head>
<body>
    <!-- 产品列表页 -->
    <section class="product-list-page">
        <div class="product-list">
            <!-- 商品卡片示例 -->
            <div class="product-card">
                <div class="product-image" style="background-image: url('dog-food.jpg')"></div>
                <div class="product-info">
                    <h3>天然无谷狗粮</h3>
                    <p class="price">¥<span>189</span></p>
                    <button class="paw-button add-to-cart"></button>
                </div>
            </div>
            <!-- 更多商品... -->
        </div>
    </section>

    <!-- 购物车页面 -->
    <section class="cart-page">
        <div class="cart-container">
            <div class="cart-items">
                <div class="cart-item">
                    <img src="dog-food-thumb.jpg" alt="商品图" class="product-thumb">
                    <div class="item-info">
                        <h4>天然无谷狗粮</h4>
                        <p>¥<span class="item-price">189</span></p>
                    </div>
                    <div class="bowl-counter">
                        <button class="bowl-btn minus">-</button>
                        <input type="number" value="1" class="quantity">
                        <button class="bowl-btn plus">+</button>
                    </div>
                    <button class="delete-btn">🗑️</button>
                </div>
            </div>
            
            <div class="checkout-summary">
                <div class="total-amount">
                    <span>总计：</span>
                    ¥<span id="total">0</span>
                </div>
                <button class="checkout-btn">立即结账</button>
            </div>
        </div>
    </section>

    <script>
    // 购物车功能
    let cart = JSON.parse(localStorage.getItem('cart')) || [];

    // 添加商品到购物车
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', () => {
            const productCard = btn.closest('.product-card');
            const product = {
                name: productCard.querySelector('h3').textContent,
                price: parseFloat(productCard.querySelector('.price span').textContent),
                quantity: 1
            };
            
            const existingItem = cart.find(item => item.name === product.name);
            if(existingItem) {
                existingItem.quantity++;
            } else {
                cart.push(product);
            }
            
            updateCart();
        });
    });

    // 更新购物车
    function updateCart() {
        localStorage.setItem('cart', JSON.stringify(cart));
        // 此处添加DOM更新逻辑
        calculateTotal();
    }

    // 计算总价
    function calculateTotal() {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        document.getElementById('total').textContent = total.toFixed(2);
    }

    // 初始化
    window.addEventListener('DOMContentLoaded', () => {
        calculateTotal();
    });
    </script>
</body>
</html>