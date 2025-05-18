<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Pad Test</n>
    <style>
        canvas {
            border: 1px solid #000;
            background-color: transparent;
            width: 400px;
            height: 200px;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        button {
            margin: 5px;
            padding: 8px 12px;
            font-size: 16px;
        }
        #results img {
            margin-top: 20px;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <h1>Signature Pad Test</h1>
    <canvas id="sigCanvas"></canvas><br>
    <button id="clearBtn">Clear</button>
    <button id="saveBtn">Save</button>
    <div id="results"></div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        const canvas = document.getElementById('sigCanvas');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(0,0,0,0)'
        });

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();

        document.getElementById('clearBtn').addEventListener('click', function () {
            signaturePad.clear();
            document.getElementById('results').innerHTML = '';
        });

        document.getElementById('saveBtn').addEventListener('click', function () {
            if (signaturePad.isEmpty()) {
                alert('Please provide a signature first.');
            } else {
                const dataURL = signaturePad.toDataURL();
                const img = new Image();
                img.src = dataURL;
                const results = document.getElementById('results');
                results.innerHTML = '';
                results.appendChild(img);
            }
        });
    </script>
</body>
</html> 