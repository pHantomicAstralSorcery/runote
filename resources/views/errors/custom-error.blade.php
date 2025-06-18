<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ошибка</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="text-center p-4">
        <h1 class="display-4 text-danger">Произошла ошибка!</h1>
        <p class="lead">{{ $message ?? 'Что-то пошло не так.' }}</p>
        <a href="/" class="btn btn-primary mt-3">Вернуться на главную</a>
    </div>
</body>
</html>