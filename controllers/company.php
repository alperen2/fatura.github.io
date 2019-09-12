<?php

$template_data = [];

$id = $params[0] ?? false;
$id = row_count($db, "companies", $id) > 0 ? $id : false;

$post = isset($_POST) ?? false;

$template_data['message'] = show_message();

if ($act == 'add' && $post) {

    extract($_POST);

    $q = $db->prepare("INSERT INTO companies (name, reg, adresa, cif, judet, tara) VALUES (:name, :reg, :adresa, :cif, :judet, :tara)");

    $insert = $q->execute([
        'name' => $name,
        'reg' => $reg,
        'adresa' => $adresa,
        'cif' => $cif,
        'judet' => $judet,
        'tara' => $tara,
    ]);

    if ($insert) {
        $message = ['status' => 'Success', 'text' => 'Şirket eklendi'];
    } else {
        $message = ['status' => 'Error', 'text' => 'Şirket eklenemedi'];
    }
    create_message($message);
    redirect('company');

} elseif ($act == 'update' && $id && $post) {

    extract($_POST);
    $q = $db->prepare("UPDATE companies SET name = :name, reg = :reg, adresa = :adresa, cif = :cif, judet = :judet, tara = :tara WHERE id = :id");
    $update = $q->execute([
        'name' => $name,
        'reg' => $reg,
        'adresa' => $adresa,
        'cif' => $cif,
        'judet' => $judet,
        'tara' => $tara,
        'id' => $id,
    ]);

    if ($update) {
        $message = ['status' => 'Success', 'text' => 'Şirket bilgileri güncellendi'];
    } else {
        $message = ['status' => 'Error', 'text' => 'Şirket bilgileri güncellenemedi'];
    }

    create_message($message);
    redirect("company/edit/{$id}");

} elseif ($act == 'edit' && $id) {

    $q = $db->prepare("SELECT * FROM companies WHERE id = :id");
    $q->bindParam('id', $id, PDO::PARAM_INT);
    $q->execute();
    $template_data['company'] = $q->fetch(PDO::FETCH_ASSOC);
    
} elseif ($act == 'delete' && $id) {

    $q = $db->prepare('DELETE FROM companies WHERE id = :id');
    $q->bindParam('id', $id, PDO::PARAM_INT);
    $delete = $q->execute();
    if ($delete) {
        $message = ['status' => 'Success', 'text' => 'Şirket silindi'];
        $template_data['message'] = $message;
    } else {
        $message = ['status' => 'Error', 'text' => 'Şirket silinemedi'];
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

$q = $db->prepare('SELECT * FROM companies');
$q->execute();
$count = $q->rowCount();
if ($count > 0) {
    $template_data['companies'] = $q->fetchAll(PDO::FETCH_ASSOC);
}

echo $twig->render('company.html', $template_data);
