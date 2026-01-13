<?php
session_start();

/* Proteksi admin */
if (!isset($_SESSION['status_admin']) || $_SESSION['status_admin'] != "loginadmin") {
    header("location: login.php");
    exit;
}

require '../function.php';
require '../vendor/autoload.php';

use Dompdf\Dompdf;

/* ===============================
   DATA ADMIN
   =============================== */
$admin = query("SELECT nama_admin FROM admin WHERE id_admin = $_SESSION[id_admin]")[0];
$nama_admin = $admin['nama_admin'];
$tanggal_cetak = date('d-m-Y H:i:s');

/* ===============================
   DATA USER (KTP VALID)
   =============================== */
$user = query("
    SELECT id_kamar, nama_user, foto_ktp
    FROM user
    WHERE foto_ktp IS NOT NULL
    AND status_ktp = 1
    ORDER BY id_kamar ASC
");

/* ===============================
   INIT DOMPDF
   =============================== */
$dompdf = new Dompdf([
    'isRemoteEnabled' => true
]);

/* ===============================
   HTML PDF
   =============================== */
$html = '
<!DOCTYPE html>
<html>
<head>
<style>
    body { font-family: Arial, sans-serif; font-size: 11px; }
    h2 { text-align: center; margin-bottom: 5px; }

    .info {
        margin-bottom: 10px;
        font-size: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: middle;
    }

    th {
        background: #f2f2f2;
        text-align: center;
    }

    .ktp { width: 180px; }

    .watermark {
        position: fixed;
        top: 45%;
        left: 15%;
        opacity: 0.12;
        font-size: 40px;
        transform: rotate(-30deg);
    }
</style>
</head>
<body>

<div class="watermark">KOST PUTRI NURMALA</div>

<h2>DATA KTP PENGHUNI</h2>

<div class="info">
    <strong>Dicetak oleh</strong> : ' . $nama_admin . '<br>
    <strong>Tanggal</strong> : ' . $tanggal_cetak . '
</div>

<table>
<thead>
<tr>
    <th width="15%">No Kamar</th>
    <th width="30%">Nama</th>
    <th width="55%">Foto KTP</th>
</tr>
</thead>
<tbody>
';

if (empty($user)) {
    $html .= '
        <tr>
            <td colspan="3" align="center">Data tidak tersedia</td>
        </tr>';
} else {
    foreach ($user as $u) {
        $path = "../user/ktp/" . $u['foto_ktp'];
        $img = file_exists($path)
            ? '<img src="' . $path . '" class="ktp">'
            : '-';

        $html .= '
        <tr>
            <td align="center">' . $u['id_kamar'] . '</td>
            <td>' . $u['nama_user'] . '</td>
            <td align="center">' . $img . '</td>
        </tr>';
    }
}

$html .= '
</tbody>
</table>

</body>
</html>
';

/* ===============================
   RENDER
   =============================== */
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* ===============================
   🔐 PASSWORD PDF
   =============================== */
$canvas = $dompdf->getCanvas();
$canvas->setEncryption(
    "ktp123",        // password buka PDF
    "adminKost!",    // owner password
    ['print']        // izin
);

/* ===============================
   OUTPUT
   =============================== */
$dompdf->stream(
    "data-ktp-penghuni.pdf",
    ["Attachment" => false]
);
