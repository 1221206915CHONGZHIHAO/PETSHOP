<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Food & Treats - 商品列表</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary-color: #2A5C8D;
        --accent-color: #FF6B35;
        --text-dark: #333;
        --text-light: #666;
    }

    /* 基础布局 */
    .container {
        display: flex;
        max-width: 1200px;
        margin: 2rem auto;
        gap: 2rem;
    }

    /* 侧边筛选栏 */
    .sidebar {
        width: 280px;
        padding: 1rem;
        background: #f8f8f8;
        border-radius: 8px;
    }

    .filter-section {
        margin-bottom: 2rem;
    }

    .filter-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-dark);
    }

    .category-list {
        list-style: none;
        padding: 0;
    }

    .category-item {
        margin-bottom: 0.5rem;
        cursor: pointer;
        color: var(--text-light);
        transition: color 0.2s;
    }

    .category-item:hover {
        color: var(--primary-color);
    }

    /* 主内容区 */
    .main-content {
        flex: 1;
    }

    .result-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .result-count {
        font-size: 0.9rem;
        color: var(--text-light);
    }

    /* 商品网格 */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
    }

    .product-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .product-image {
        height: 180px;
        background-color: #eee;
        border-radius: 6px;
        margin-bottom: 1rem;
    }

    .brand-name {
        color: var(--primary-color);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .product-title {
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .product-desc {
        color: var(--text-light);
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 1rem;
    }

    .product-price {
        color: var(--accent-color);
        font-weight: 600;
    }

    /* 响应式设计 */
    @media (max-width: 768px) {
        .container {
            flex-direction: column;
        }
        
        .sidebar {
            width: 100%;
        }

        .product-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <!-- 侧边筛选栏 -->
        <aside class="sidebar">
            <div class="filter-section">
                <h3 class="filter-title">Categories</h3>
                <ul class="category-list">
                    <li class="category-item">Dog > Dry Food (382)</li>
                    <li class="category-item">Dog > Freeze Dried & Air Dried</li>
                    <li class="category-item">Dog > Treats (254)</li>
                    <li class="category-item">Dog > Wet Food (162)</li>
                </ul>
            </div>

            <div class="filter-section">
                <h3 class="filter-title">Brands</h3>
                <input type="text" placeholder="Search Brand" class="search-input">
                <ul class="category-list">
                    <li class="category-item">Acana (10)</li>
                    <li class="category-item">Alps (16)</li>
                    <li class="category-item">Amanova (32)</li>
                    <li class="category-item">Araton (2)</li>
                    <li class="category-item">+ 51 more</li>
                </ul>
            </div>

            <div class="filter-section">
                <h3 class="filter-title">Price</h3>
                <div class="price-range">
                    <input type="number" placeholder="From" class="range-input">
                    <span>-</span>
                    <input type="number" placeholder="To" class="range-input">
                </div>
            </div>
        </aside>

        <!-- 主内容区 -->
        <main class="main-content">
            <div class="result-header">
                <div class="result-count">814 Items found for Food & Treats</div>
                <!-- 排序组件可在此添加 -->
            </div>

            <div class="product-grid">
                <!-- 商品卡片示例 -->
                <div class="product-card">
                    <div class="product-image"></div>
                    <div class="brand-name">PROBALANCE</div>
                    <h4 class="product-title">Probalance Pouch 130g</h4>
                    <p class="product-desc">With Veggies in Grovy Wet Dog Food</p>
                    <div class="product-price">RM 0.00</div>
                </div>

                <!-- 更多商品... -->
            </div>
        </main>
    </div>

    <script>
    // 筛选功能逻辑
    const categoryItems = document.querySelectorAll('.category-item');
    categoryItems.forEach(item => {
        item.addEventListener('click', () => {
            // 添加选中状态样式
            categoryItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            
            // 这里添加筛选逻辑
            console.log('Selected category:', item.textContent);
        });
    });

    // 价格范围验证
    const rangeInputs = document.querySelectorAll('.range-input');
    rangeInputs.forEach(input => {
        input.addEventListener('change', () => {
            if(input.value < 0) input.value = 0;
        });
    });
    </script>
</body>
</html>