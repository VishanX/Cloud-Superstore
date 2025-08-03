<?php
include 'templates/header.php';
include 'includes/db_connect.php';
?>

<h1 class="mb-5 text-center">Welcome to the Cloud Superstore!</h1>

<?php
$categories = [
    1 => 'Electronics',
    2 => 'Clothing',
    3 => 'Home & Kitchen'
];

foreach ($categories as $cat_id => $cat_name):
    $stmt = $mysqli->prepare("
        SELECT product_id, name, price, image 
        FROM products 
        WHERE category_id = ? 
        LIMIT 12
    ");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0):
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $chunks = array_chunk($products, 3); // 3 items per carousel slide
?>

<div class="mb-5">
    <h3 class="mb-3"><?php echo htmlspecialchars($cat_name); ?></h3>
    <div id="carousel-<?php echo $cat_id; ?>" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($chunks as $i => $group): ?>
                <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                    <div class="row g-4">
                        <?php foreach ($group as $product): ?>
                            <div class="col-md-4">
                                <div class="card h-100 shadow-sm">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                        class="card-img-top fixed-product-image" 
                                        alt="Product Image">

                                    <div class="card-body text-center d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="text-primary fw-bold">$<?php echo number_format((float)$product['price'], 2); ?></p>
                                        <a href="product_detail.php?id=<?php echo (int)$product['product_id']; ?>" class="btn btn-outline-primary w-100 mb-2 mt-auto">View Details</a>

                                        <?php if (isset($_SESSION['user'])): ?>
                                            <form method="post" action="cart_add.php">
                                                <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
                                                <button type="submit" class="btn btn-success w-100">Add to Cart</button>
                                            </form>
                                        <?php else: ?>
                                            <p class="text-muted">Login to add to cart</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Carousel Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?php echo $cat_id; ?>" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?php echo $cat_id; ?>" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>

<?php
    endif;
endforeach;

include 'templates/footer.php';
?>
