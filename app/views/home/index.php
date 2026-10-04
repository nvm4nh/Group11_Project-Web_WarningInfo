<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URLROOT ?>/public/assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <h1 class="text-primary"><?= $data['title'] ?></h1>
                <p class="lead text-secondary"><?= $data['description'] ?></p>
                <hr>
                <p>Team Lead hãy push source code này lên nhánh <strong>develop</strong> nhé!</p>
            </div>
        </div>
    </div>
</body>
</html>
