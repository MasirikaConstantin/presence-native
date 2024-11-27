<?php
require_once 'config.php';

session_start();
require 'fpdf/fpdf.php';

ini_set('memory_limit', '256M');
$pdf = new FPDF("P", 'mm', "A4");

$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Titre
$pdf->Cell(0, 10, mb_convert_encoding('PRESENCES', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Ln(10);

// Entête du tableau
$pdf->Cell(15, 5, mb_convert_encoding('Num', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell(60, 5, mb_convert_encoding('Nom', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell(30, 5, mb_convert_encoding('Lieu', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell(30, 5, mb_convert_encoding('Date', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell(30, 5, mb_convert_encoding('Heure', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Cell(30, 5, mb_convert_encoding('Status', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
$pdf->Ln();

// Parcourir les données de $_SESSION['impression']
if (isset($_SESSION['impression']) && is_array($_SESSION['impression'])) {
    foreach ($_SESSION['impression'] as $presence) {
        $pdf->Cell(15, 5, $presence['id'], 1, 0, 'C');
        $pdf->Cell(60, 5, mb_convert_encoding($presence['nom'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Cell(30, 5, $presence['nom_lieu'], 1, 0, 'C');
        $pdf->Cell(30, 5, $presence['date'], 1, 0, 'C');
        $pdf->Cell(30, 5, $presence['heure'], 1, 0, 'C');
        $pdf->Cell(30, 5, mb_convert_encoding($presence['status'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
        $pdf->Ln();
    }
}

// Fait à Kinshasa, date du jour
$pdf->Ln(10);
$pdf->Cell(0, 10, mb_convert_encoding('Fait à Kinshasa, le ' . date('d-m-Y'), 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

// Envoi du PDF au navigateur
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="document.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
$pdf->Output('I', 'document.pdf');


/*
  $pdf->Ln();
  foreach ($a as $k){
    $pdf->Cell(15,5, utf8_decode($k['num']),1,0,'C');
    $pdf->Cell(50,5, utf8_decode($k['nom']),1,0);
    $pdf->Cell(30,5, utf8_decode('Pièces'),1,0,'C');
    $pdf->Cell(20,5, utf8_decode($k['quantite']),1,0,'C');
    $pdf->Cell(25,5, utf8_decode(number_format($k['prix_ttc'],3,' ,','  ')),1,0,'R');
    $pdf->Cell(40,5, utf8_decode(number_format(($k['pri']),3,' ,','  ')),1,0,'R');



    $pdf->Ln();

  }
  
  $pdf->Cell(100,12,utf8_decode("Total article :  ".'  '.count($a)));
  $pdf->Cell(17,12,utf8_decode("Total : "));

  $pdf->Cell(20,12,utf8_decode(number_format(($tot),3,' , ','  '))." FC");

  $pdf->Ln(8);
  

  $pdf->Cell(23,10,utf8_decode("Total article :  ".'  '));

  $pdf->Cell(77,10,utf8_decode($panier->count()));

  

  $pdf->Cell(35,12,utf8_decode("Remise  % :     ".$remise ."%  :"));

  $pdf->Cell(15,12,utf8_decode(number_format(($remise1),3,' , ','  '))." FC");
  $pdf->Ln(8);

  $pdf->Cell(100,12,'');

  $pdf->Cell(23,12,utf8_decode("Total à payer  : "));

  $pdf->Cell(20,12,utf8_decode(number_format(($totalapayer),3,' , ','  '))." FC");
  

  $pdf->Output();*/

  ?>
