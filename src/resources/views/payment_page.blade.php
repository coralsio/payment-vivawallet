<progress value="0" max="200" id="progressBar">

</progress>

<script>
    function paymentInitiation() {
        window.location.href = '{{ $checkoutUrl }}'
    }

    window.onload = function () {
        let timeLeft = 200;

        let redirectTimer = setInterval(function () {
            if (timeLeft <= 0) {
                clearInterval(redirectTimer);
            }

            document.getElementById("progressBar").value = 200 - timeLeft;

            timeLeft -= 1;
        }, 10);


        setTimeout(paymentInitiation, 2000);
    }
</script>
