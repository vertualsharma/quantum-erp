<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Print Result</title>
<style>
    html, body { margin: 0; padding: 0; height: 100%; }
    iframe { width: 100%; height: 100%; border: none; }
</style>
</head>
<body>
<iframe id="pdfFrame" src="data:application/pdf;base64,{{ $pdfBase64 }}"></iframe>
<script>
    var frame = document.getElementById('pdfFrame');
    frame.onload = function () {
        setTimeout(function () {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (e) {
                window.print();
            }
        }, 500);
    };
</script>
</body>
</html>