<?php
include 'templates/header.php';
include 'includes/db_connect.php'; // Your DB connection

// Define category IDs for your categories
// Make sure these IDs match your `categories` table
$categories = [
    1 => 'Electronics',
    2 => 'Clothing',
    3 => 'Home & Kitchen',
];

// Check if a category filter is set via GET parameter
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Prepare SQL query with optional category filter
if ($category_id && array_key_exists($category_id, $categories)) {
    $stmt = $mysqli->prepare("
        SELECT p.product_id, p.name, p.price, p.image, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.category_id = ?
        ORDER BY p.name
    ");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // No filter, show all products
    $query = "
        SELECT p.product_id, p.name, p.price, p.image, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        ORDER BY p.name
    ";
    $result = $mysqli->query($query);
}
?>

<h2 class="mb-4">Product List</h2>

<!-- Category Filter Buttons -->
<div class="mb-4">
    <a href="products.php" class="btn btn-outline-<?php echo $category_id === 0 ? 'primary' : 'secondary'; ?> me-2">All Categories</a>
    <?php foreach ($categories as $id => $name): ?>
        <a href="products.php?category_id=<?php echo $id; ?>" class="btn btn-outline-<?php echo $category_id === $id ? 'primary' : 'secondary'; ?> me-2">
            <?php echo htmlspecialchars($name); ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($result && $result->num_rows > 0): ?>
    <div class="row g-4">
        <?php while ($product = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <img
                        src="<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/500x300?text=No+Image'; ?>"
                        class="card-img-top fixed-product-image"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                    >

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($product['category_name']); ?></small></p>
                        <p class="card-text fw-semibold text-primary">$<?php echo number_format($product['price'], 2); ?></p>
                        <a href="product_detail.php?id=<?php echo (int)$product['product_id']; ?>" class="btn btn-outline-primary mt-auto">
                            View Details
                        </a>
                            <?php if (isset($_SESSION['user'])): ?>
                              <form method="post" action="cart_add.php" class="mt-2">
                                 <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
                                 <button type="submit" class="btn btn-success w-100">Add to Cart</button>
                              </form>
                            <?php else: ?>
    <p class="text-muted mt-2">Login to add to cart</p>
<?php endif; ?>


                        
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>No products found.</p>
<?php endif; ?>

<?php
if (isset($stmt)) {
    $stmt->close();
}
include 'templates/footer.php';
?>
