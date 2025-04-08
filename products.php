<?php
session_start();
require 'db_connection.php'; // 创建这个文件包含数据库连接

// 获取产品ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 查询产品信息
$product = [];
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

if(empty($product)) {
    header("Location: 404.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | PawsomeHome</title>
    <link rel="stylesheet" href="css/userhomepage.css">
    <style>
        .product-main {
            padding: 4rem 0;
        }
        
        .product-gallery {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .main-image {
            height: 500px;
            object-fit: cover;
        }
        
        .thumbnails {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .thumbnail {
            height: 100px;
            object-fit: cover;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .thumbnail:hover {
            transform: scale(1.05);
        }
        
        .product-info {
            padding-left: 3rem;
        }
        
        .product-price {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin: 1.5rem 0;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .quantity-input {
            width: 100px;
            padding: 0.5rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
        }
        
        .product-specs {
            margin: 2rem 0;
            padding: 1.5rem;
            background: var(--background);
            border-radius: 15px;
        }
    </style>
</head>
<body>
<!-- 导航栏（与主页相同） -->
<?php include 'navbar.php'; ?>

<main class="product-main">
    <div class="container">
        <div class="row g-5">
            <!-- Product image -->
            <div class="col-md-6">
                <div class="product-gallery">
                    <img src="images/products/<?= htmlspecialchars($product['main_image']) ?>" 
                         class="main-image w-100" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="thumbnails">
                        <?php foreach(json_decode($product['gallery_images']) as $image): ?>
                        <img src="images/products/<?= htmlspecialchars($image) ?>" 
                             class="thumbnail" 
                             alt="Product image">
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- 产品信息 -->
            <div class="col-md-6">
                <div class="product-info">
                    <h1 class="display-4"><?= htmlspecialchars($product['name']) ?></h1>
                    <div class="product-price">$<?= number_format($product['price'], 2) ?></div>
                    
                    <div class="product-specs">
                        <h3>规格参数</h3>
                        <ul class="specs-list">
                            <li>适用宠物：<?= htmlspecialchars($product['pet_type']) ?></li>
                            <li>产品重量：<?= $product['weight'] ?> kg</li>
                            <li>产品尺寸：<?= $product['dimensions'] ?></li>
                            <li>主要成分：<?= htmlspecialchars($product['ingredients']) ?></li>
                        </ul>
                    </div>
                    
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                        
                        <div class="quantity-selector">
                            <label for="quantity">数量：</label>
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   class="quantity-input"
                                   min="1" 
                                   value="1"
                                   required>
                        </div>
                        
                        <button type="submit" class="btn btn-accent btn-lg w-100">
                            <i class="bi bi-cart"></i> 加入购物车
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- 产品详情 -->
        <section class="product-details mt-5">
            <h2>产品详情</h2>
            <div class="content">
                <?= $product['description'] ?>
            </div>
        </section>
        
        <!-- 用户评价 -->
        <section class="product-reviews mt-5">
            <h3>用户评价 (4.8/5)</h3>
            <div class="review-card">
                <div class="review-header">
                    <div class="user-info">
                        <i class="bi bi-person-circle"></i>
                        <span>HappyPetOwner123</span>
                    </div>
                    <div class="rating">
                        ★★★★★
                    </div>
                </div>
                <p class="review-text">这个产品真的超出预期，我家狗狗非常喜欢！</p>
            </div>
        </section>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
// 图片切换功能
document.querySelectorAll('.thumbnail').forEach(thumb => {
    thumb.addEventListener('click', () => {
        const mainImage = document.querySelector('.main-image');
        mainImage.src = thumb.src;
        mainImage.alt = thumb.alt;
    });
});
</script>
</body>
</html>