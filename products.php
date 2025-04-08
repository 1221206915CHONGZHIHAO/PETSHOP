<?php
session_start();
require 'db_connection.php';

// 处理筛选参数
$pet_type = isset($_GET['pet_type']) ? $_GET['pet_type'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 12;

// 构建基础查询
$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = '';

// 添加宠物类型筛选
if($pet_type != 'all' && in_array($pet_type, ['dog', 'cat', 'bird', 'fish'])) {
    $query .= " AND pet_type = ?";
    $params[] = $pet_type;
    $types .= 's';
}

// 添加排序
$sort_options = [
    'newest' => 'product_id DESC',
    'price_asc' => 'price ASC',
    'price_desc' => 'price DESC',
    'popular' => 'sales_count DESC'
];
$order_by = $sort_options[$sort] ?? 'product_id DESC';

// 分页计算
$count_query = str_replace('*', 'COUNT(*) as total', $query);
$stmt = $conn->prepare($count_query);
if($types) $stmt->bind_param($types, ...$params);
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
    <title>产品列表 | PawsomeHome</title>
    <link rel="stylesheet" href="css/userhomepage.css">
    <style>
        .product-listing {
            padding: 4rem 0;
        }
        
        .filter-sidebar {
            background: var(--background);
            padding: 2rem;
            border-radius: 15px;
            height: fit-content;
        }
        
        .filter-group {
            margin-bottom: 2rem;
        }
        
        .filter-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 3rem;
            gap: 0.5rem;
        }
        
        .page-item {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .page-item.active {
            background: var(--accent-color);
            color: white;
        }
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
                            <h4 class="filter-title">宠物类型</h4>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" 
                                       name="pet_type" value="all" 
                                       <?= $pet_type == 'all' ? 'checked' : '' ?>>
                                <label class="form-check-label">全部</label>
                            </div>
                            <?php foreach(['dog' => '狗狗', 'cat' => '猫咪', 'bird' => '鸟类', 'fish' => '鱼类'] as $value => $label): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio"
                                       name="pet_type" value="<?= $value ?>"
                                       <?= $pet_type == $value ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $label ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="filter-group">
                            <h4 class="filter-title">排序方式</h4>
                            <select class="form-select" name="sort">
                                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>最新上架</option>
                                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>价格从低到高</option>
                                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>价格从高到低</option>
                                <option value="popular" <?= $sort == 'popular' ? 'selected' : '' ?>>最受欢迎</option>
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
                                <img src="images/products/<?= $product['main_image'] ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="product-overlay">
                                    <button class="btn btn-accent">查看详情</button>
                                </div>
                            </div>
                        </a>
                        <div class="product-info">
                            <h5><?= htmlspecialchars($product['name']) ?></h5>
                            <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                            <form action="add_to_cart.php" method="POST" class="quick-add">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-accent">
                                    <i class="bi bi-cart-plus"></i> 快速购买
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- 分页 -->
                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                       class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
// 自动提交筛选表单
document.getElementById('filterForm').addEventListener('change', function() {
    this.submit();
});
</script>
</body>
</html>