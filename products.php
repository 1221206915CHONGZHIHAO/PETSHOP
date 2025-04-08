<?php
session_start();
require 'db_connection.php';

// 处理筛选参数
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 12;

// 构建基础查询
$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = '';

// 添加分类筛选
if($category != 'all') {
    $query .= " AND Category = ?";
    $params[] = $category;
    $types .= 's';
}

// 添加排序
$sort_options = [
    'newest' => 'created_at DESC',
    'price_asc' => 'price ASC',
    'price_desc' => 'price DESC'
];
$order_by = $sort_options[$sort] ?? 'created_at DESC';

// 分页计算
$count_query = "SELECT COUNT(*) as total FROM products" . ($category != 'all' ? " WHERE Category = ?" : "");
$stmt = $conn->prepare($count_query);
if($category != 'all') $stmt->bind_param('s', $category);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// 获取产品数据
$query .= " ORDER BY $order_by LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = ($page - 1) * $per_page;
$types .= 'ii';

$stmt = $conn->prepare($query);
if($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品列表 | Pet Shop</title>
    <link rel="stylesheet" href="css/userhomepage.css">
    <style>
        /* 保持原有样式不变 */
        .product-listing { padding: 4rem 0; }
        .filter-sidebar { background: var(--background); padding: 2rem; border-radius: 15px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2rem; }
        .pagination { display: flex; justify-content: center; margin-top: 3rem; gap: 0.5rem; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<section class="product-listing">
    <div class="container">
        <h1 class="section-title">所有产品</h1>
        
        <div class="row g-4">
            <!-- 筛选侧边栏 -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <form id="filterForm">
                        <div class="filter-group">
                            <h4 class="filter-title">商品分类</h4>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" 
                                       name="category" value="all" 
                                       <?= $category == 'all' ? 'checked' : '' ?>>
                                <label class="form-check-label">全部</label>
                            </div>
                            <?php 
                            // 从数据库获取所有分类
                            $cat_query = "SELECT DISTINCT Category FROM products";
                            $categories = $conn->query($cat_query)->fetch_all(MYSQLI_ASSOC);
                            
                            foreach($categories as $cat): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="category" value="<?= $cat['Category'] ?>"
                                       <?= $category == $cat['Category'] ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $cat['Category'] ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="filter-group">
                            <h4 class="filter-title">排序方式</h4>
                            <select class="form-select" name="sort">
                                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>最新上架</option>
                                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>价格从低到高</option>
                                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>价格从高到低</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-accent w-100">应用筛选</button>
                    </form>
                </div>
            </div>
            
            <!-- 产品列表 -->
            <div class="col-lg-9">
                <div class="product-grid">
                    <?php foreach($products as $product): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= $product['product_id'] ?>">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($product['product_name']) ?>"
                                     loading="lazy">
                                <div class="product-overlay">
                                    <button class="btn btn-accent">查看详情</button>
                                </div>
                            </div>
                        </a>
                        <div class="product-info">
                            <h5><?= htmlspecialchars($product['product_name']) ?></h5>
                            <div class="product-price">￥<?= number_format($product['price'], 2) ?></div>
                            <div class="stock-info">库存: <?= $product['stock_quantity'] ?></div>
                            <form action="add_to_cart.php" method="POST" class="quick-add">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                <input type="number" name="quantity" value="1" min="1" 
                                       max="<?= $product['stock_quantity'] ?>" class="form-control mb-2">
                                <button type="submit" class="btn btn-sm btn-accent w-100">
                                    <i class="bi bi-cart-plus"></i> 加入购物车
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- 分页 -->
                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>" 
                       class="page-item">上一页</a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>" 
                       class="page-item">下一页</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
document.getElementById('filterForm').addEventListener('change', function() {
    this.submit();
});

// 库存验证
document.querySelectorAll('input[name="quantity"]').forEach(input => {
    input.addEventListener('change', function() {
        const max = parseInt(this.getAttribute('max'));
        if (this.value > max) this.value = max;
        if (this.value < 1) this.value = 1;
    });
});
</script>
</body>
</html>