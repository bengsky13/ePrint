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
        <div class="text-center" id="qr">
            <img class="qr-code img-thumbnail img-responsive" />
            <br>
            <a id="link" href="" target="_blank">CLICK HERE</a>
        </div>
        <div class="text-center py-5" id="spinner" style="display: none">
            <div class="spinner-border" role="status">
                <span class="sr-only"></span>
            </div>
        </div>
        <div class="text-center py-5" id="pdf" style="display: none; width: 100vw; height: 60vh; overflow: scroll;">

        </div>
        <div class="container py-5" id="price" style="display: none;">
            <div class="row">
                <div class="col">
                    <label class="display-3">Total Page:<strong id="total"></strong></label>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label class="display-3">Colored Page:<strong id="colored"></strong></label>
                </div>
            </div>
            <div class="row">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">TYPE</th>
                            <th scope="col">PRICE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Black & White</td>
                            <td id="priceBnw"></td>
                        </tr>
                        <tr>
                            <td>Colored</td>
                            <td id="priceColored"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col">
                    <button id="print" data-type=1 class="btn btn-dark">BLACK&WHITE</button>
                </div>
                <div class="col">
                    <button id="print" data-type=2 class="btn btn-primary">COLORED</button>
                </div>
                <div class="col">
                    <button id="print" data-type=0 class="btn btn-danger">CANCEL</button>
                </div>
            </div>
        </div>
        <div class="text-center" id="paymentQr" style="display: none">
            <h1 id="totalPayment"></h1>
            <img id="qrPayment" class="qr-code img-thumbnail img-responsive" />
            <br>

            <div class="col">
                <h1 id="countdown">00:00</h1>
                <button id="print" data-type=0 class="btn btn-danger">CANCEL</button>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.js">
    </script>

    <script>
        function htmlEncode(value) {
            return $('<div/>').text(value)
                .html();
        }
        function makeid(length) {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }


        $(document).ready(function () {
            const baseUrl = "{{URL:: to('/')}}"
            let sessionId = makeid(10)
            let price
            let loaded = false
            let countdown = false
            let m = 1
            let s = 59
            let date = false
            const finalURL = 'https://chart.googleapis.com/chart?cht=qr&chl=' +
                htmlEncode(baseUrl + '/' + sessionId) +
                '&chs=500x500&chld=L|0'
            $('.qr-code').attr('src', finalURL);
            $("#link").attr("href", baseUrl + '/' + sessionId)

            $.get('/init').then((result) => {
            })

            $('[id="print"]').click(function () {
                const type = +this.dataset.type
                if (type === 0) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'No',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, Cancel!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload()
                        }
                    })
                }
                else {
                    Swal.fire({
                        title: type === 1 ? 'Print Black And White' : 'Print Colored',
                        text: type === 1 ? "Rp. " + price.bnw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : "Rp. " + price.colored.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","),
                        icon: 'info',
                        showCancelButton: true,
                        cancelButtonText: 'No',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Print Now'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.get('/' + sessionId + '/print/' + type).then((result) => {
                            })
                        }
                    })
                }
            })
            const delay = (delayInms) => {
                return new Promise(resolve => setTimeout(resolve, delayInms));
            }
            var startTimer = setInterval(function () {
                var now = new Date().getTime();
                var distance = date - now;
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                $("#countdown").html(`0${minutes}:${seconds < 10 ? "0" + seconds : seconds}`)
                if (distance < 0 && date != false) {

                    clearInterval(startTimer);
                }
            }, 1000);
            const sessionCheck = async () => {
                while (1) {
                    try {
                        await $.get('/' + sessionId + '/status').then((result) => {
                            if (result.status == 1) {
                                $("#qr").hide()
                                $("#spinner").show()
                            }
                            else if (result.status == 2 && loaded == false) {
                                $("#qr").hide()
                                $("#spinner").hide()
                                $("#pdf").show()
                                loaded = true
                                let preview = '<div class="row">'
                                for (x = 1; x <= result.page; x++) {
                                    preview += `<img src="uploads/${sessionId}/photo/Pic-${x}.png">`
                                }
                                preview += '</div>'
                                $("#pdf").html(preview)
                                $("#total").html(result.page)
                                $("#colored").html(result.coloredPage.length)
                                $("#priceBnw").html(result.price.bnw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))
                                $("#priceColored").html(result.price.colored.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))
                                $("#price").show()
                                price = {
                                    "bnw": result.price.bnw,
                                    "colored": result.price.colored
                                }
                            }
                            else if (result.status == 3) {
                                if (result.status_code == 201) {
                                }
                                else if (result.status_code == 200) {
                                }
                                else {
                                    location.reload()
                                }

                                if (date == false) {
                                    date = new Date(result.time).getTime();
                                }
                                $("#qr").hide()
                                $("#spinner").hide()
                                $("#price").hide()
                                $("#pdf").hide()
                                $("#paymentQr").show()
                                $("#totalPayment").html("Payment Rp. " + result.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))
                                $("#qrPayment").attr("src", result.qr)
                            }
                            else if (result.status == 4) {
                                $("#paymentQr").hide()
                                $("#spinner").show()
                                $("#qr").hide()
                                $("#price").hide()
                                $("#pdf").hide()
                                $("#paymentQr").hide()
                            }

                            else if (result.status == 5) {
                                location.reload();
                            }
                        });
                    } catch (error) {
                    }
                }
            }
            sessionCheck()
        });
    </script>
</body>

</html>