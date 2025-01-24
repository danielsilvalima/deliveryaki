<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificação</title>
</head>
<body>
    <h2>Olá, {{ $dados['nome'] }}</h2>
    <p>Você tem uma nova notificação:</p>
    <p><strong>Mensagem:</strong> {{ $dados['mensagem'] }}</p>
    <p>Atenciosamente,</p>
    <p>Equipe {{ env('MAIL_FROM_NAME') }}</p>
</body>
</html>
