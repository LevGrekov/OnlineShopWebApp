<?php

require_once "common/Page.php";
require_once "common/PhpHelper.php";
use common\Page;
use common\PhpHelper;

class autorizationPage extends Page
{
    private $Error;

    public function __construct(){
        parent::__construct();

        $this->Error = null;
        
        if(isset($_SESSION['user'])){
            header("Location: /account.php");
            exit();
        }
        if (
            isset($_POST['signupName']) &&
            isset($_POST['signupLastName']) &&
            isset($_POST['signupLogin']) &&
            isset($_POST['signupPassword']) &&
            isset($_POST['signupConfirmPassword'])
        ){
            $this->Error = $this->regUser();
        }
        else if(
            isset($_POST['loginlogin']) &&
            isset($_POST['loginPassword'])
        ){
            $bool = $this->auth();
            if($bool == 17) {
                header("Location: /account.php");
                exit();
            }
            else{
                $this->Error = $bool;
            }
        }
    }

    protected function showContent()
    {  
            $this->showRegistrationForms();
    }

    private function showRegistrationForms()
    {
        ?>
        <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
            <?php
                if(isset($this->Error))$this->RegistrationErrorAppend();
            ?>
            <div class="card">
                <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                    <a class="nav-link active"  href="#login" data-bs-toggle="tab">Авторизация</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link " href="#signup" data-bs-toggle="tab">Регистрация</a>
                    </li>
                </ul>
                </div>
                
                <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="login">
                    <form action="autorizationPage.php?auth=1" method="post">
                        <div class="mb-3">
                        <label for="loginlogin" class="form-label">Логин</label>
                        <input type="login" class="form-control" id="loginlogin" name="loginlogin" required>
                        </div>
                        <div class="mb-3">
                        <label for="loginPassword" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="loginPassword" name="loginPassword" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Войти</button>
                    </form>
                    </div>
                    <div class="tab-pane fade" id="signup">
                        
                    <form action="autorizationPage.php?reg=1" method="post">
                        <div class="mb-3">
                        <label for="signupName" class="form-label">Имя</label>
                        <input type="text" class="form-control" id="signupName" name="signupName" placeholder="Введите ваше имя*" required>
                        </div>
                        <div class="mb-3">
                        <label for="signupLastName" class="form-label">Фамилия</label>
                        <input type="text" class="form-control" id="signupLastName" name="signupLastName" placeholder="Введите вашу фамилию*" required>
                        </div>
                        <div class="mb-3">
                        <label for="signupLogin" class="form-label">Имя Пользователя</label>
                        <input type="text" class="form-control" id="signupLogin" name="signupLogin" placeholder="Придумайте логин*" required>
                        </div>
                        <div class="mb-3">
                        <label for="signupPassword" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="signupPassword" name="signupPassword" placeholder="Введите ваш пароль*" required>
                        </div>
                        <div class="mb-3">
                        <label for="signupConfirmPassword" class="form-label">Подтвердите пароль</label>
                        <input type="password" class="form-control" id="signupConfirmPassword" name="signupConfirmPassword" placeholder="Подтвердите ваш пароль*" required>
                        </div>
                        <div class="mb-3">
                        <label for="signupEmail" class="form-label">Электронная почта</label>
                        <input type="email" class="form-control" id="signupEmail" name="signupEmail" placeholder="Введите email">
                        </div>
                        <div class="mb-3">
                        <label for="signupPhone" class="form-label">Номер телефона</label>
                        <input type="tel" class="form-control" id="signupPhone" name="signupPhone" placeholder="Введите номер телефона" >
                        </div>
                        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
                    </form>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
        </div>
        <?php
    }

    private function regUser(): int
    {
        $error = 0;
 
            $login = htmlspecialchars($_POST['signupLogin']);
            if (mb_strlen($login) < 4 || mb_strlen($login) > 30) $error = 1;

            $password = htmlspecialchars($_POST['signupPassword']);
            if (mb_strlen($password) < 6 || mb_strlen($password) > 30) $error = 2;

            $password2 = htmlspecialchars($_POST['signupConfirmPassword']);
            if ($password !== $password2) $error = 3;

            $name = htmlspecialchars($_POST['signupName']);
            if (mb_strlen($name) < 2 || mb_strlen($password) > 100) $error = 4;

            $lastname = htmlspecialchars($_POST['signupLastName']);
            if (mb_strlen($name) < 2 || mb_strlen($password) > 100) $error = 5;

            $email = htmlspecialchars($_POST['signupEmail']);
            if(mb_strlen($email) === 0) $email = null;

            $phone = htmlspecialchars($_POST['signupPhone']);
            if(mb_strlen($phone) === 0) $phone = null; 
            // if (!$this->validatePhoneNumber($phone)) $error = 7;
        
        
        if ($error === 0){
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if (!$this->dbh->saveUser($login, $hash,$email,$phone,$name,$lastname)) $error = -2;
        }
        return $error;

    }

    private function RegistrationErrorAppend(){
        switch ($this->Error){
            case 1:{
                $e_msg = "Неверный логин!";
                break;
            }
            case 2:{
                $e_msg = "Неверный пароль!";
                break;
            }
            case 3:{
                $e_msg = "Пароли не совпадают!";
                break;
            }
            case 4:{
                $e_msg = "Неверное имя пользователя!";
                break;
            }
            case 5:{
                $e_msg = "Неверное Фамилия пользователя!";
                break;
            }
            case 6:{
                $e_msg = "Неверный почтовый адресс!";
                break;
            }
            case 7:
            case 8:{
                $e_msg = "Неверный Логин или Пароль!";
                break;
            }
            case -1:{
                $e_msg = "Заполните все поля формы!";
                break;
            }
            case -2:{
                $e_msg = "Не удалось зарегистрировать пользователя. Возможно такое имя уже занято!";
                break;
            }
            case 0:{
                ?>
                <div class="alert alert-success my-3">
                <strong>Поздравляем:</strong> Вы успешно зарегистрировались!
                </div>
                <?php
                return;
            }
            default:
                break;
        }
        ?>
        <div class="alert alert-danger my-3">
        <strong>Ошибка :</strong> <?php echo $e_msg?>
        </div>
        <?php
    }


    private function validateEmail($email) {
        // Используем встроенную функцию PHP для проверки формата email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
    
        return true;
    }
    
    private function auth(): int
    {
        $login = htmlspecialchars($_POST['loginlogin']);
        $password = htmlspecialchars($_POST['loginPassword']);

        $save_pwd = $this->dbh->getUserPassword($login) ?? "";

        $auth = password_verify($password, $save_pwd);

        if($auth){ 
            $_SESSION['user'] = $this->dbh->getUser($login,$save_pwd)[0];
            return 17;
        }
        else{
            unset($_SESSION['user']);
            return 8;
        }
        
    }
}

(new autorizationPage())->showPage();