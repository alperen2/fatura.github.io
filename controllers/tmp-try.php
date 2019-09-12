<?php
$id = 39;

$currencies = [
    0 => 'Leva',
    1 => 'Euro',
];

$q = $db->prepare("SELECT * FROM config");
$q->execute();
$confgig = $q->fetch(PDO::FETCH_ASSOC);

$q = $db->prepare("SELECT
                        s.id,
                        s.definition,
                        s.currency,
                        s.piece,
                        s.salary,
                        s.tax,
                        s.company_id,
                        s.from_place,
                        s.to_place,
                        s.driver_id,
                        s.created_date,
                        c.name as company_name,
                        c.reg,
                        c.adresa,
                        c.cif,
                        c.judet,
                        c.tara,
                        d.name as driver_name,
                        d.surname,
                        d.passport_no
                    FROM
                        services as s
                    INNER JOIN
                        companies as c ON
                        s.company_id = c.id
                    INNER JOIN
                        drivers as d ON
                        s.driver_id = d.id
                    WHERE
                        s.id = :id
");

$q->bindParam('id', $id, PDO::PARAM_INT);
$q->execute();

$service = $q->fetch(PDO::FETCH_ASSOC);
$rowCount = $q->rowCount();

if ($rowCount > 0) {

    $currency = $service['currency'];
    $service['currency'] = $currencies[$currency];
    $service['exchange'] = get_exchange_rate();

    echo $twig->render('bill.html', $service);
    $content = ob_get_contents();
    ob_end_clean();
    $mpdf->WriteHTML($content);
    $mpdf->Output();
    exit;
} else {

    $error = [
        'code' => '404',
        'header' => 'NOT FOUND',
        'message' => 'Aradığınız fatura bulunamadı!',
    ];

    echo $twig->render('error.html', $error);
    die();
}
