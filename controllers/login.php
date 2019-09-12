<?php


$post = $_POST ?? false;

$template_data['message'] = show_message();
if ($post) {
    extract($post);
    $password = md5($password);
    $q = $db->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
    $q->bindParam("email", $email, PDO::PARAM_STR);
    $q->bindParam("password", $password, PDO::PARAM_STR);
    $q->execute();

    if ($q->rowCount() > 0) {
        $_SESSION['is_login'] = true;
        redirect("service");
    } else {
        $message = ["status" => "error", "text"=> "Kullanıcı adı veya parola yanlış"];
        create_message($message);
    }
}

if ($act == 'exit') {
    session_destroy();
    redirect('login');
}
echo $twig->render("login.html", $template_data);

