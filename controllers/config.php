<?php

$post = isset($_POST) ?? false;

$template_data['message'] = show_message();

if ($act == "update" && $post) {
    extract($_POST);
    $q = $db->prepare("UPDATE 
                            config
                        SET 
                            company_name = :company_name, 
                            reg = :reg, 
                            adresa = :adresa, 
                            cif = :cif, 
                            email = :email, 
                            tel = :tel, 
                            owner_name = :owner_name,
                            owner_surname = :owner_surname,
                            owner_id  = :owner_id 
                        WHERE id = :id");

    $update = $q->execute([
        "company_name" => $company_name,
        "reg" => $reg,
        "adresa" => $adresa,
        "cif" => $cif,
        "email" => $email,
        "tel" => $tel,
        "owner_name" => $owner_name,
        "owner_surname" => $owner_surname,
        "owner_id" => $owner_id,
        "id" => 1
    ]);

    if ($update) {
        $message = ['status' => 'success', 'text' => 'Ayarlar güncellendi'];
    } else {
        $message = ['status' => 'success', 'text' => 'Ayarlar güncellenemedi'];
    }

    create_message($message);
    redirect("config");
} elseif (!$act) {
    $q = $db->prepare("SELECT * FROM config");
    $q->execute();
    $template_data['config'] = $q->fetch(PDO::FETCH_ASSOC);
} else {
    $error = [
        'code' => '404',
        'header' => 'NOT FOUND',
        'message' => 'Aradığınız sayfa bulunamadı!',
    ];

    echo $twig->render('error.html', $error);
    exit;
}




echo $twig->render('config.html', $template_data);
