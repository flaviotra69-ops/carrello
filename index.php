<?php
// INIZIALIZZAZIONE VARIABILI
$quaderno = "0";
$penna = "0";
$zaino = "0";
$subtotale = 0;
$extra = [];
$codicesconto = "";
$sconto = 0;

$totaleextra = 0;
$totale = 0;
$subtotale_scontato = 0;
$carrellovuoto = true;
$errori = [];
$salvato = false;
// PREZZI PRODOTTI
$prezzo_quaderno = 2.50;
$prezzo_penna = 1.20;
$prezzo_zaino = 35.00;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ACQUISIZIONE QUADERNO
    if (isset($_POST["quaderno"])) {
        if (is_string($_POST["quaderno"])) {
            $quaderno = trim($_POST["quaderno"]);
        }
    }
    // ACQUISIZIONE PENNA
    if (isset($_POST["penna"])) {
        if (is_string($_POST["penna"])) {
            $penna = trim($_POST["penna"]);
        }
    }
    // ACQUISIZIONE ZAINO
    if (isset($_POST["zaino"])) {
        if (is_string($_POST["zaino"])) {
            $zaino = trim($_POST["zaino"]);
        }
    }
    // ACQUISIZIONE EXTRA
    if (isset($_POST["extra"])) {
        if (is_array($_POST["extra"])) {
            $extra = $_POST["extra"];
        }
    }
    
    // ACQUISIZIONE CODICE SCONTO
    if (isset($_POST["codicesconto"])) {
        if (is_string($_POST["codicesconto"])) {
            $codicesconto = trim($_POST["codicesconto"]);
        }
    }

    // CONTROLLO QUADERNO
    if (!is_numeric($quaderno) || $quaderno != intval($quaderno) || $quaderno < 0) {
        $errori["quaderno"] = "Quantità non valida";
    }
    // CONTROLLO PENNA
    if (!is_numeric($penna) || $penna != intval($penna) || $penna < 0) {
        $errori["penna"] = "Quantità non valida";
    }
    // CONTROLLO ZAINO
    if (!is_numeric($zaino) || $zaino != intval($zaino) || $zaino < 0) {
        $errori["zaino"] = "Quantità non valida";
    }

    // CALCOLO CARRELLO
    if (empty($errori)) {
      // CONTROLLO CARRELLO VUOTO
      if ($quaderno > 0 || $penna > 0 || $zaino > 0) {
        $carrellovuoto = false;
      }
      // CALCOLO SUBTOTALE
      $subtotale = ($quaderno * $prezzo_quaderno) + ($penna * $prezzo_penna) + ($zaino * $prezzo_zaino);

      // CONTROLLO CODICE SCONTO
      if ($codicesconto == "KTM50") {
        $sconto = 50;
      }
      elseif ($codicesconto == "KTSM80") {
        $sconto = 80;
      }
      elseif ($codicesconto != "") {
        $errori["codicesconto"] = "Codice sconto non valido";
      }
      // APPLICAZIONE SCONTO
      $subtotale_scontato = $subtotale - ($subtotale * $sconto / 100);

      // CONTROLLO EXTRA
      foreach ($extra as $elemento) {
        if ($elemento == "regalo") {
          $totaleextra += 3;
        }
        elseif ($elemento == "express") {
          $totaleextra += 7;
        }
        }
        // CALCOLO TOTALE
        $totale = $subtotale_scontato + $totaleextra;
        // SALVATAGGIO NEL FILE APPUNTI.TXT
        if (!$carrellovuoto) {
          // APERTURA FILE
          $file = fopen("appunti.txt", "a");
          if ($file != false) {
            // INIZIALIZZAZIONE RECORD
            $record = date("d/m/Y H:i:s") . ";";
            $record .= $quaderno . ";";
            $record .= $penna . ";";
            $record .= $zaino . ";";
            $record .= $codicesconto . ";";
            // SALVATAGGIO EXTRA
            if (in_array("regalo", $extra)) {
                $record .= "Regalo;";
            }
            else {
                $record .= "No regalo;";
            }
            if (in_array("express", $extra)) {
                $record .= "Express;";
            }
            else {
                $record .= "No express;";
            }
            // SALVATAGGIO IMPORTI
            $record .= number_format($subtotale, 2, ",", ".") . ";";
            $record .= number_format($sconto, 0) . "%;";
            $record .= number_format($totaleextra, 2, ",", ".") . ";";
            $record .= number_format($totale, 2, ",", ".");
            // SCRITTURA RECORD
            $salvato = fwrite($file, $record . "\n");
            // CHIUSURA FILE
            fclose($file);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riepilogo ordine</title>

    <style>
        body {
            font-family: sans-serif;
            max-width: 32rem;
            margin: 2rem auto;
            padding: 0 1rem;
            color: #1c1e22;
            background: #f7f6f2;
        }

        .riepilogo {
            background: white;
            border: 1px solid #d6d3cb;
            border-radius: 8px;
            padding: 1rem 1.2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 0.5rem;
            border-bottom: 1px solid #e6e3dc;
        }

        .error {
            color: red;
        }

        .totale {
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <h1>Riepilogo ordine</h1>
    <div class="riepilogo">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // CONTROLLO ERRORI QUANTITÀ
            if (!empty($errori["quaderno"]) ||
              !empty($errori["penna"]) ||
              !empty($errori["zaino"])) {
              echo "<p class='error'>Quantità non valide. Controlla i prodotti.</p>";
            }
            else {
              // CONTROLLO CARRELLO VUOTO
              if ($carrellovuoto) {
                echo "<p>Il carrello è vuoto</p>";
                }
                else {
                  // VISUALIZZAZIONE PRODOTTI
                  echo "<h2>Prodotti</h2>";
                  echo "<table>";
                  echo "<tr>";
                  echo "<th>Prodotto</th>";
                  echo "<th>Quantità</th>";
                  echo "<th>Prezzo</th>";
                  echo "<th>Subtotale</th>";
                  echo "</tr>";

                  // QUADERNO
                  if ($quaderno > 0) {
                    $risultato = $quaderno * $prezzo_quaderno;
                    echo "<tr>";
                    echo "<td>Quaderno</td>";
                    echo "<td>$quaderno</td>";
                    echo "<td>2,50 €</td>";
                    echo "<td>" . number_format($risultato, 2, ",", ".") . " €</td>";
                    echo "</tr>";
                  }

                    // PENNA
                    if ($penna > 0) {

                        $risultato = $penna * $prezzo_penna;

                        echo "<tr>";
                        echo "<td>Penna</td>";
                        echo "<td>$penna</td>";
                        echo "<td>1,20 €</td>";
                        echo "<td>" . number_format($risultato, 2, ",", ".") . " €</td>";
                        echo "</tr>";
                    }

                    // ZAINO
                    if ($zaino > 0) {

                        $risultato = $zaino * $prezzo_zaino;

                        echo "<tr>";
                        echo "<td>Zaino</td>";
                        echo "<td>$zaino</td>";
                        echo "<td>35,00 €</td>";
                        echo "<td>" . number_format($risultato, 2, ",", ".") . " €</td>";
                        echo "</tr>";
                    }

                    echo "</table>";

                    // SUBTOTALE
                    echo "<p>Subtotale prodotti: ";
                    echo number_format($subtotale, 2, ",", ".") . " €</p>";

                    // CODICE SCONTO
                    if ($codicesconto != "") {

                        if (isset($errori["codicesconto"])) {

                            echo "<p class='error'>";
                            echo $errori["codicesconto"];
                            echo "</p>";

                        }
                        else {

                            echo "<p>Sconto applicato: $sconto%</p>";

                            echo "<p>Subtotale scontato: ";
                            echo number_format($subtotale_scontato, 2, ",", ".") . " €</p>";

                        }
                    }

                    // VISUALIZZAZIONE EXTRA
                    echo "<h2>Extra</h2>";

                    if ($totaleextra == 0) {

                        echo "<p>Nessun extra selezionato</p>";

                    }
                    else {

                        foreach ($extra as $elemento) {

                            if ($elemento == "regalo") {
                                echo "<p>Confezione regalo: 3,00 €</p>";
                            }
                            elseif ($elemento == "express") {
                                echo "<p>Spedizione express: 7,00 €</p>";
                            }
                        }

                        echo "<p>Totale extra: ";
                        echo number_format($totaleextra, 2, ",", ".") . " €</p>";
                    }

                    // TOTALE FINALE
                    echo "<hr>";

                    echo "<p class='totale'>Totale ordine: ";
                    echo number_format($totale, 2, ",", ".") . " €</p>";

                    // CONFERMA SALVATAGGIO
                    if ($salvato !== false) {
                        echo "<p>Ordine salvato correttamente in appunti.txt</p>";
                    }
                    else {
                        echo "<p class='error'>Errore durante il salvataggio.</p>";
                    }

                }
            }
        }
        else {

            echo "<p>Nessun ordine ricevuto.</p>";

        }

        ?>
    </div>
    <p><a href="carrello.html">Torna al carrello</a></p>
</body>
</html>