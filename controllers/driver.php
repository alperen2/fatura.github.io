<?php

$id = $params[0] ?? false;
$id = row_count($db, "drivers", $id) > 0 ? $id : false;

$post = $_POST ?? false;
$template_data['message'] = show_message();

if ($act == "add" && $post) {

    $q = $db->prepare("INSERT INTO drivers SET name = :name, surname = :surname, passport_no = :passport_no");
    $insert = $q->execute($post);
    if ($insert) {
        $message = ["status" => "success", "text" => "Şöför eklendi"];
    } else {
        $message = ["status" => "success", "text" => "Şöför eklenemedi"];
    }
    create_message($message);
    redirect('driver');
} elseif ($act == "edit" && $id) {
    $q = $db->prepare("SELECT * FROM drivers WHERE id = :id");
    $q->bindParam("id", $id, PDO::PARAM_INT);
    $q->execute();

    $template_data["driver"] = $q->fetch(PDO::FETCH_ASSOC);
} elseif ($act == "update" && $id && $post) {
    $post['id'] = $id;
    $q = $db->prepare("UPDATE drivers SET name = :name, surname = :surname, passport_no = :passport_no WHERE id = :id");
    $update = $q->execute($post);
    if ($update) {
        $message = ["status" => "success", "text" => "Şöför bilgileri güncellendi"];
    } else {
        $message = ["status" => "success", "text" => "Şöför bilgileri güncellenemedi"];
    }

    create_message($message);
    redirect("driver/edit/{$id}");
} elseif ($act == 'delete' && $id) {
    $q = $db->prepare("DELETE FROM drivers WHERE id = :id");
    $q->bindParam("id", $id, PDO::PARAM_INT);
    $delete = $q->execute();
    if ($delete) {
        $message = ["status" => "success", "text" => "Şöför kaldırıldı"];
    } else {
        $message = ["status" => "success", "text" => "Şöför kaldırılamadı"];
    }
    create_message($message);
    // redirect("driver");
} elseif (!$act) { } else {
    $error = [
        'code' => '404',
        'header' => 'NOT FOUND',
        'message' => 'Aradığınız sayfa bulunamadı!',
    ];

    echo $twig->render('error.html', $error);
    die();
}


$q = $db->prepare("SELECT * FROM drivers");
$q->execute();

$template_data["drivers"] = $q->fetchAll(PDO::FETCH_ASSOC);

echo $twig->render("driver.html", $template_data);
