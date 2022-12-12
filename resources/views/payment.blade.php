<!DOCTYPE html>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .qr-code {
            margin: 10px;
        }

        embed {
            width: 100vw;
            height: 100vh;
        }

        img {
            border: 1px solid black;
        }
    </style>

    <title>ePrint</title>
</head>

<body>
    <div class="container-fluid">
        <div class="text-center py-5" id="spinner">
            <div class="spinner-border" role="status">
                <span class="sr-only"></span>
            </div>
            @if($link !== null)
            <div>
                <h1> Waiting Payment</h1>
                <a href="{{ $link }}" target="_blank"><button class="btn btn-primary">Simulate Payment</button></a>
            </div>
            @else
            <script>
                setTimeout(() => location.reload(), 5000);
            </script>
            @endif
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.js">
    </script>
</body>

</html>