<?php
session_start();
include 'templates/header.php';

$cart = $_SESSION['cart'] ?? [];
?>

<div class="container mt-4">
    <h2>Your Shopping Cart</h2>

    <?php if (empty($cart)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price (each)</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($cart as $product_id => $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td>
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Product Image" style="width: 80px; height: auto;"><br>
                        <?php echo htmlspecialchars($item['name']); ?>
                    </td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo (int)$item['quantity']; ?></td>
                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                    <td>
                        <form method="post" action="cart_remove.php" onsubmit="return confirm('Remove this item?');">
                            <input type="hidden" name="product_id" value="<?php echo (int)$product_id; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end fw-bold">Total</td>
                    <td colspan="2" class="fw-bold">$<?php echo number_format($total, 2); ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>
