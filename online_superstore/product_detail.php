<?php
include 'templates/header.php';
include 'includes/db_connect.php'; // your DB connection file

// Get product ID from URL, ensure it's an integer to avoid SQL injection
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    echo "<div class='alert alert-danger'>Invalid product ID.</div>";
    include 'templates/footer.php';
    exit;
}

// Prepare and execute the query to get product info
$stmt = $mysqli->prepare("SELECT p.name, p.description, p.price, p.image, c.name AS category_name 
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.category_id
                          WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-warning'>Product not found.</div>";
    include 'templates/footer.php';
    exit;
}

$product = $result->fetch_assoc();

?>

<div class="container">
    <h2 class="mb-4"><?php echo htmlspecialchars($product['name']); ?></h2>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
    <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
    <img src="<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/600x400?text=No+Image'; ?>" 
         alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-fluid mb-4" style="max-width: 600px;">
    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
</div>

<?php if (isset($_SESSION['user'])): ?>
    <form method="post" action="cart_add.php" class="mt-4">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <button type="submit" class="btn btn-success">Add to Cart</button>
    </form>
<?php else: ?>
    <p class="text-muted mt-4">Login to add this product to cart.</p>
<?php endif; ?>


<?php include 'templates/footer.php'; ?>
