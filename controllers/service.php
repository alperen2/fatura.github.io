<?php
$mpdf = new \Mpdf\Mpdf();


$id = $params[0] ?? false;
// $id = row_count($db, "drivers", $id) > 0 ? $id : false;

$post = isset($_POST) ?? false;

$currencies = [
    0 => 'Leva',
    1 => 'Euro',
];

$template_data['message'] = show_message();

$template_data['currencies'] = $currencies;

$q = $db->prepare("Select id,name from companies");
$q->execute();
$companies = $q->fetchAll(PDO::FETCH_ASSOC);

$template_data['companies'] = $companies;

$q = $db->prepare("Select id, CONCAT(name,' ', surname) as name_surname from drivers");
$q->execute();
$drivers = $q->fetchAll(PDO::FETCH_ASSOC);

$template_data['drivers'] = $drivers;


if ($act == 'add' && $post) {
    $data = $_POST;
    $data['created_date'] = date("Y-m-d");
    $q = $db->prepare("INSERT 
                        INTO 
                            services (   
                                definition,
                                currency, 
                                piece,
                                salary,
                                tax,
                                company_id,
                                from_place,
                                to_place,
                                driver_id,
                                license_plate,
                                created_date
                            ) 
                        VALUES (
                            :definition,
                            :currency,
                            :piece,
                            :salary,
                            :tax,
                            :company_id,
                            :from_place,
                            :to_place,
                            :driver_id,
                            :license_plate,
                            :created_date
                        )");

    $insert = $q->execute($data);

    if ($insert) {
        $message =  ['status' => 'Success', 'text' => 'Fatura eklendi'];
    } else {
        $message = ['status' => 'Error', 'text' => 'Fatura eklenemedi'];
    }

    create_message($message);
    redirect("service");
} elseif ($act == 'update' && $post && $id) {

    $data = $_POST;
    $data['id'] = $id;

    $q = $db->prepare("UPDATE
                            services
                        SET
                            definition = :definition,
                            currency = :currency,
                            piece = :piece,
                            salary = :salary,
                            tax = :tax,
                            company_id = :company_id,
                            from_place = :from_place,
                            to_place = :to_place,
                            driver_id = :driver_id,
                            license_plate = :license_plate
                        WHERE id = :id
                    ");
    $update = $q->execute($data);

    if ($update) {
        $message = ['status' => 'Success', 'text' => 'Hizmet bilgileri güncellendi'];
    } else {
        $message = ['status' => 'Error', 'text' => 'Hizmet bilgileri güncellenemedi'];
    }

    create_message($message);
    redirect("service/edit/{$id}");
} elseif ($act == 'edit' && $id) {

    $q = $db->prepare('SELECT * FROM services WHERE id = :id');
    $q->bindParam('id', $id, PDO::PARAM_INT);
    $q->execute();
    $service = $q->fetch(PDO::FETCH_ASSOC);
    $template_data['service'] = $service;
} elseif ($act == 'delete' && $id) {

    $q = $db->prepare("DELETE FROM services WHERE id = :id");
    $q->bindParam("id", $id, PDO::PARAM_INT);
    $result = $q->execute();
    if ($result) {
        $message = ['status' => 'Success', 'text' => 'Fatura kaldırıldı'];
    } else {
        $message = ['status' => 'Error', 'text' => 'Fatura kaldırılamadı'];
    }
    create_message($message);
    redirect("service/list");
} elseif ($act == 'print' && $id) {
    $q = $db->prepare("SELECT 
                            company_name as c_c_name,
                            reg as c_reg,
                            adresa as c_adresa,
                            cif as c_cif,
                            tel,
                            email,
                            owner_name,
                            owner_surname,
                            owner_id FROM config");
    $q->execute();
    $config = $q->fetch(PDO::FETCH_ASSOC);

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
                            s.license_plate,
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

        $data = array_merge($config, $service);

        echo $twig->render('bill.html', $data);
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
} elseif (!$act) { } else {

    $error = [
        'code' => '404',
        'header' => 'NOT FOUND',
        'message' => 'Aradığınız sayfa bulunamadı!',
    ];

    echo $twig->render('error.html', $error);
    die();
}


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
                        DATE_FORMAT(created_date,'%d.%m.%Y') as date,
                        c.name,
                        c.id as cid
                    FROM 
                        services as s
                    INNER JOIN companies as c ON 
                        s.company_id = c.id 
                    LIMIT
                        10
                ");

$q->execute();
$services = $q->fetchAll(PDO::FETCH_ASSOC);

foreach ($services as $key => $service) {
    $currency = $service['currency'];
    $services[$key]['currency'] = $currencies[$currency];
}

$rowCount = $q->rowCount();
if ($rowCount > 0) {
    $template_data['services'] = $services;
}

echo $twig->render('service.html', $template_data);
