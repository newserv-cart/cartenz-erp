<!DOCTYPE html>
<html>
<head>
    <title>Create Manifest</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <h2>📦 Create Manifest</h2>

    <form
        method="post"
        action="<?= base_url('manifests/store') ?>">

        <button
            class="btn btn-success">

            Create Manifest

        </button>

    </form>

</div>

</body>
</html>