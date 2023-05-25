<?php
namespace common;
require_once "DbHelper.php";
abstract class Page
{

    protected $dbh;

    public function __construct()
    {
        session_start();
        $this->dbh = DbHelper::getInstance("localhost", 3306, "root", "");
    }

    public function showPage(): void
    {
        print "<!DOCTYPE html>";
        print "<html lang='ru'>";
        $this->createHeading();
        $this->createBody();
        print "</html>";
    }

    private function createHeading(){
        ?>
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <link type="text/css" rel="stylesheet" href="css/bootstrap.css"/>
            <link type="text/css" rel="stylesheet" href="css/style.css"/>
            <link type="text/css" rel="stylesheet" href="css/fontawesome.css"/>
            <link type="text/css" rel="stylesheet" href="css/nouislider.css"/>
            <link rel="shortcut icon" href="images/logo2.png" type="image/x-icon">
            <title>БуАмЛё</title>
        </head>
        <?php
    }

    private function createBody()
    {
        print "<body>";
        print "<div class='main'>";
        $this->showHeader();
        $this->showContent();
        $this->showFooter();
        print "</div>";
        print "</body>";
        $this->loadScripts();
    }

    protected abstract function showContent();

    private function showHeader()
    {
        ?>
                <!--TopHeader-->
        <div class="top-header bg-white py-2">
        <div class="container d-flex justify-content-end align-items-center">
            <ul class="list-unstyled mb-0 d-flex align-items-center ml-auto">
            <li class="me-3">
                <i class="fas fa-phone me-1"></i>
                <a href="tel:+79994701200">+7 999 470 1200</a>
            </li>
            <li class="me-3">
                <i class="fas fa-envelope me-1"></i>
                <a href="mailto:levgrekov@mail.ru">levgrekov@mail.ru</a>
            </li>
            <li class="me-3">
                <i class="fas fa-map-marker-alt me-1"></i>
                <span>Казань, Кремлевская 35а</span>
            </li>
            </ul>
        </div>
        </div>
        <!--Header-->
        <nav class=" navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="/index.php">
            <div class="logo">
                <h1>БуАмЛё</h1>
            </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
            <form class="d-flex mx-auto my-2 my-lg-0" method="post" action="index.php?search=1">
                <div class="input-group">
                <select class="form-select" id="category-select" name="search-category"> 
                    <option value="0" selected>Все Категории</option>
                    <?php GetCategoriesOptionsList($this->dbh)?>
                </select>
                <input class="form-control" type="search" placeholder="Search" aria-label="Search" name="search-input">
                <button class="btn btn-primary" type="submit">Искать</button>
                </div>
            </form>
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user']) && $_SESSION['user']['isAdmin']==true):?>
                <li class="nav-item me-4">
                <a class="nav-link" href="adminPanel.php">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <span class="tooltip">Админ Панель</span>
                </a>
                </li>
                <?php endif;?>
                <li class="nav-item">
                <a class="nav-link" href="account.php">
                <i class="fa-solid fa-user"></i>
                    Аккаунт
                </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#cartModal">
                        <i class="fa-solid fa-cart-shopping"></i>
                        Корзина
                    </a>
                </li>
                <?php 

                if(isset($_SESSION['user'])){
                    $this->showCart();
                }
                
                ?>
                <li class="nav-item">
                <a class="nav-link" href="wishlist.php">
                    <i class="fa-solid fa-heart"></i>
                    Любимое
                </a>
                <!-- </li>
                <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fa-solid fa-bell"></i>
                    Notifications
                </a>
                </li> -->
            </ul>
            </div>
        </div>
        </nav>
        <?php
    }

    private function showCart(){
        //$product = [2,3];
        ?>
            <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cartModalLabel">Cart</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            
                            <?php 
                                GetProductsInUsersCart($this->dbh);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }

    private function showFooter()
    {
        ?>
        <footer class="bg-light py-5 text-center px-0">
        <div class="container">
            <div class="row">
            <div class="col-md-4">
                <h5>О нас</h5>
                <p>Наш интернет магазин предлагает широкий ассортимент товаров для вашего удобства.</p>
            </div>
            <div class="col-md-4">
                <h5>Топ категории</h5>
                <ul class="list-unstyled">
                <?php
                $topCategories = $this->dbh->getCategoryWithHighestAverageRating();
                for($i=0;$i<4;$i++):?>
                <li><a href="#Futures\_(^_^)_/"><?= $topCategories[$i]['name']?></a></li>
                <?php endfor;?>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Контакты</h5>
                <ul class="list-unstyled">
                <li><i class="fas fa-map-marker-alt me-2"></i>Казань, Кремлевская 35а</li>
                <li><i class="fas fa-phone me-2"></i><a href="tel:+79994701200">+7 999 470 1200</a></li>
                <li><i class="fas fa-envelope me-2"></i><a href="mailto:levgrekov@mail.ru">levgrekov@mail.ru</a></li>
                </ul>
            </div>
            </div>
            <hr>
            <div class="row">
            <div class="col-md-12">
                <p class="text-center">&copy; 2023 Lev Grekov. Все права не защищены.</p>
            </div>
            </div>
        </div>
        </footer>
        <?php
    }

    private function loadScripts(){
        ?>
        <script src="JS/script.js"></script>
        <script src="JS/bootstrap.js"></script>
        <script src="JS/nouislider.js"></script>
        <script src="https://kit.fontawesome.com/0f08ca129c.js" crossorigin="anonymous"></script>
        <script src="JS/slider.js"></script>
        <script src="JS/AJAXdinamicProductsUpdate.js"></script>
        <?php
    }

}

abstract class SupportPage extends Page
{
    protected $formErrors = array();

    public function __construct()
    {
        parent::__construct();
        $this->processForm();
    }

    protected abstract function processForm(): void;

    protected function showContent(): void
    {
    }


}
