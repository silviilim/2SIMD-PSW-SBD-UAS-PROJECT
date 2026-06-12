<?php
$conn = mysqli_connect('localhost', 'root', '', 'maru_bake_house');
$query = "SELECT * FROM reviews WHERE status = 'published' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Maru Bake House - Reviews</title>
    </head>
<body>
    <h1>Customer Reviews</h1>
    
    <a href="contact.php" class="btn-review">Leave a Review</a>

    <div class="review-container">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="review-card">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p>Rating: <?= $row['rating'] ?>/5</p>
                <p>"<?= htmlspecialchars($row['comment']) ?>"</p>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>