<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= esc($title ?? 'CARTENZ ERP') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>

        body{

            margin:0;

            padding:0;

            background:#F1F5F9;

            font-family:Segoe UI,Tahoma,sans-serif;

        }

        .wrapper{

            display:flex;

            min-height:100vh;

        }

        .sidebar{

            width:260px;

            background:#1E293B;

            color:white;

            position:fixed;

            top:0;

            bottom:0;

            left:0;

            overflow-y:auto;

            transition:.3s;

            z-index:1000;

        }

        .sidebar a{

display:block;

padding:12px 22px;

color:#E2E8F0;

text-decoration:none;

font-size:15px;

transition:.2s;

}

.sidebar a:hover{

background:#334155;

padding-left:30px;

color:white;

}


.active-menu{

background:#2563EB !important;

color:white !important;

border-left:5px solid #38BDF8;

font-weight:bold;

}

.active-menu i{

color:white;

}


.menu-title{

padding:15px 20px 8px;

font-size:11px;

font-weight:bold;

color:#94A3B8;

letter-spacing:1px;

}

.sidebar i{

margin-right:10px;

}



        .main{

            margin-left:260px;

            width:calc(100% - 260px);

            display:flex;

            flex-direction:column;

            min-height:100vh;

        }

        .topbar{

            background:white;

            height:70px;

            display:flex;

            justify-content:space-between;

            align-items:center;

            padding:0 25px;

            box-shadow:0 2px 8px rgba(0,0,0,.08);

            position:sticky;

            top:0;

            z-index:999;

        }

        .content{

            flex:1;

            padding:25px;

        }

        .footer{

            background:white;

            padding:15px;

            text-align:center;

            font-size:13px;

            color:#888;

            border-top:1px solid #ddd;

        }

        .card{

            border:none;

            border-radius:12px;

            box-shadow:0 2px 8px rgba(0,0,0,.08);

        }

        .card:hover{

            transform:translateY(-2px);

            transition:.25s;

        }

        @media(max-width:991px){

            .sidebar{

                left:-260px;

            }

            .sidebar.show{

                left:0;

            }

            .main{

                width:100%;

                margin-left:0;

            }

        }

    </style>

</head>

<body>

<div class="wrapper">

    <?= $this->include('admin/layouts/sidebar') ?>

    <div class="main">

        <?= $this->include('admin/layouts/header') ?>

        <div class="content">

            <?= $this->renderSection('content') ?>

        </div>

        <?= $this->include('admin/layouts/footer') ?>

    </div>

</div>
<script src="<?= base_url('assets/js/jquery.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function toggleSidebar(){

    document.querySelector('.sidebar').classList.toggle('show');

}

</script>

<?= $this->renderSection('scripts') ?>

</body>
</html>