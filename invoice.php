<?php
$order_id = $_GET['order_id'] ?? 'Unknown Order';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - Order #<?= $order_id ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
        .invoice-box {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            border: 1px solid #eee;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
        .invoice-box h2 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <h2>Invoice - Order #<?= $order_id ?></h2>

        <!-- You can replace the below with actual order details from your database -->
        <p><strong>Customer:</strong> John Doe</p>
        <p><strong>Date:</strong> <?= date("Y-m-d") ?></p>

        <hr>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Premium Dog Food</td>
                    <td>2</td>
                    <td>$29.99</td>
                    <td>$59.98</td>
                </tr>
                <!-- Repeat rows dynamically if needed -->
            </tbody>
        </table>

        <div class="text-end mt-3">
            <h5>Subtotal: $59.98</h5>
            <h5>Shipping: $5.00</h5>
            <h4>Total: $64.98</h4>
        </div>

        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print</button>
            <a href="orders.php" class="btn btn-secondary">Back</a>
        </div>
    </div>
</body>
</html>
