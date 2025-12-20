<!DOCTYPE html>
<html>

<head>
    <style>
        /* Setup dasar PDF */
        body {
            font-family: sans-serif;
            margin: 2cm;
        }

        /* Area Konten Utama */
        .content {
            line-height: 1.6;
            color: #333;
        }

        /* CSS AJAIB BUAT POJOK KANAN BAWAH */
        .qr-stempel {
            position: fixed;
            bottom: 0px;
            /* Jarak dari bawah */
            right: 0px;
            /* Jarak dari kanan */
            width: 100px;
            /* Ukuran tampilan di PDF */
            height: 100px;

            /* Opsional: kasih border atau background biar kece */
            padding: 5px;
            background: #fff;
        }

        /* Opsional: Text kecil di bawah QR */
        .qr-caption {
            position: fixed;
            bottom: -15px;
            /* Sesuaikan biar pas di bawah gambar */
            right: 10px;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>SURAT PERNYATAAN</h1>
        <hr>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>
        <p><strong>Nama:</strong> {{ $nama }}</p>
        <p><strong>Tanggal:</strong> {{ $tanggal }}</p>

        <p>
            Dengan ini menyatakan bahwa dokumen ini adalah valid dan telah digenerate
            menggunakan sistem Laravel 12 yang super canggih.
        </p>

        <p>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor
            incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.
        </p>
    </div>

    <img src="{{ $qrCode }}" class="qr-stempel" alt="QR Code">
    <div class="qr-caption">Scan for validation</div>

</body>

</html>
