<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>API Bank Sampah (ABS)</title>
    <style>
        body { margin: 0; padding: 0; }
    </style>
</head>
<body>

    <div id="app"></div>

    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>

    <script>
        Scalar.createApiReference('#app', {
            url: '/docs/api.json',
            agent: {
                disabled: true // Mengatur ini ke true akan menyembunyikan AI Agent
            }
        });
    </script>

</body>
</html>