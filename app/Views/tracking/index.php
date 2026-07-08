<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">

    <h2>🚚 CARTENZ TRACKING</h2>

    <form method="post"
          action="<?= site_url('track/search') ?>">

        <div class="input-group">

            <input
                type="text"
                name="awb"
                class="form-control"
                placeholder="Enter AWB Number">

            <button class="btn btn-primary">
                TRACK
            </button>

        </div>

    </form>

</div>