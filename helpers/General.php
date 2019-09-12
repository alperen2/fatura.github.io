<?php


function show_message()
{
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    } else {
        return false;
    }
}

function create_message($message)
{
    $_SESSION['message'] = $message;
}

function redirect($page)
{
    header("location:" . SITE . "/$page");
}

function get_exchange_rate()
{
    $content = file_get_contents("https://api.exchangeratesapi.io/latest?symbols=RON&based=EUR");
    $exchange = json_decode($content, true);

    return $exchange['rates']['RON'];

}


function row_count($db,$table, $id = false) {
    $query = "SELECT * FROM {$table} ";
    $query.= $id ? "WHERE id = :id" : "";
    $q = $db->prepare($query);
    if ($id) $q->bindParam("id", $id, PDO::PARAM_INT);
    $q->execute();
    $c = $q->rowCount();
    return $c ?? false;
}