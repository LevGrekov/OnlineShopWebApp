<?php

require_once "common/Page.php";
require_once "common/PhpHelper.php";
use common\Page;
use common\PhpHelper;

class wishList extends Page
{ 
    public function __construct() {
        parent::__construct();
        if (!isset($_SESSION['user'])) 
        {
            header("Location: /autorizationPage.php");
            exit();
        }
    }

    protected function ShowContent(){
        $this->showWishList();
    }


    private function showWishList(){
        ?>
        <div class="container mt-5">
            <div class="card">
            <div class="card-header">
                <h5 class="card-title">Мой WishList</h5>
            </div>
            <div class ="row m-3 " id="wishlistBlock">
                <?php
                $products = $this->dbh->getWishlistProductsWithRating($_SESSION['user']['id']);
                for($i = 0; $i <count($products); $i++) 
                {
                    GenerateProductHolderForWishList($products[$i],$this->dbh);
                }
                ?>
            </div>
            </div>
        </div>

        <?php
    }
}
(new wishList())->showPage();