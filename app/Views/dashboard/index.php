<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AtendeLab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">AtendeLab</span>
            <a href="?controller=auth&action=logout" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="card shado-sm">
            <div class="card-body">
                <h1 class="h4">Área restrita</h1>
                <p class="mb-1">
                    Bem vindo,
                    <strong><?= htmlspecialchars($usuario['nome'],
                            ENT_QUOTES,
                            'UTF-8')
                    ?></strong>
                </p>
                <p class="text-muted">
                    Perfil:
                    <strong><?= htmlspecialchars($usuario['perfil'],
                            ENT_QUOTES,
                            'UTF-8')
                    ?></strong>
                </p>
                <a href="?controller=usuarios&action=listar" class="btn btn-primary">Testar rota protegida de usuários</a>
            </div>
        </div>
    </div>
</body>
</html>
