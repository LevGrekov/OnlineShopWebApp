<?php

require_once "../common/DbHelper.php";
require_once "../common/PhpHelper.php";
use common\DbHelper;




function filterProductsAJAX(){

    session_start();
    $dbh = DbHelper::getInstance("localhost", 3306, "root", "");

    $sortOption = $_GET['sort'];
    $categoryOptions = isset($_GET['categories']) && !empty($_GET['categories']) ? explode(',' ,$_GET['categories']) : null;
    $brandOptions = isset($_GET['brands']) && !empty($_GET['brands']) ? explode(',' ,$_GET['brands']) : null;
    $minPrice = $_GET['min_price'];
    $maxPrice = $_GET['max_price'];

    $filtratedProducts = $dbh->getProductsWithRatingFilterized($sortOption,$categoryOptions,$brandOptions,$minPrice,$maxPrice);
    //$filtratedProducts = $dbh->getProductsWithRatingFilterized2($categoryOptions);

    
    for($i = 0; $i <count($filtratedProducts); $i++) 
    {
        GenerateProductHolder($filtratedProducts[$i],$dbh);
    }
  }

  function newFunction(){
    session_start();
    $dbh = DbHelper::getInstance("localhost", 3306, "root", "");
    if (isset($_POST['productId'])) {
        $productId = $_POST['productId'];
        $userId = $_SESSION['user']['id'];

        $inCart = null;
        if ($dbh->isProductInCart($userId, $productId)) {
            // Продукт уже есть в корзине, удалить его
            $dbh->removeFromCart($productId,$userId);
            $inCart = false;
        } else {
            // Продукта нет в корзине, добавить его
            $dbh->addToCart($productId,$userId,1);
            $inCart = true;
        }

        $response = array("inCart" => $inCart);
        echo json_encode($response);
    }
  }

  
   function showCart(){
    session_start();
    $dbh = DbHelper::getInstance("localhost", 3306, "root", "");
    GetProductsInUsersCart($dbh);
}

function deleteProduct() {
    session_start();
    $dbh = DbHelper::getInstance("localhost", 3306, "root", "");

    if (isset($_POST['productId'])) {
      $productId = $_POST['productId'];
      $userId = $_SESSION['user']['id'];
      // Удаление продукта из базы данных
      $dbh->removeFromCart($productId,$userId);
      
      // Отправка успешного статуса удаления
      $response = array("success" => true);
      echo json_encode($response);
    } else {
      // Неверные параметры, вернуть ошибку
      $response = array("error" => "Invalid parameters");
      echo json_encode($response);
    }
  }

  function toggleWishListIcon(){
    session_start();
    $dbh = DbHelper::getInstance("localhost", 3306, "root", "");
    if (isset($_POST['productId'])) {
        $productId = $_POST['productId'];
        $userId = $_SESSION['user']['id'];

        $inWishList = null;
        if ($dbh->isProductInWishList($userId, $productId)) {
            // Продукт уже есть в корзине, удалить его
            $dbh->removeFromWishlist($userId,$productId);
            $inWishList = false;
        } else {
            // Продукта нет в корзине, добавить его
            $dbh->addToWishlist($productId,$userId);
            $inWishList = true;
        }

        $response = array("inWishlist" => $inWishList);
        echo json_encode($response);
    }
  }

    function RealoadWishListAJAX(){
        session_start();
        $dbh = DbHelper::getInstance("localhost", 3306, "root", "");
        $products = $dbh->getWishlistProductsWithRating($_SESSION['user']['id']);
        
        ob_start();
        for($i = 0; $i <count($products); $i++) 
        {
            GenerateProductHolderForWishList($products[$i],$dbh);
        }
        $content = ob_get_clean();
    
        echo $content;
    }
    function OutputInCome(){
        session_start();
        $dbh = DbHelper::getInstance("localhost", 3306, "root", "");
        $EndDate = $_POST['end_date'];
        $StartDate = $_POST['start_date'];
        $result = $dbh->calculateProfitForPeriod($StartDate, $EndDate, 'Доставлен');
        
        // Возвращаем результат в формате JSON
        echo json_encode($result);
    }

  switch($_REQUEST['state']){
    case 1:
        filterProductsAJAX();
        break;
    case 2:
        newFunction();
        break;
    case 3:
        showCart();
        break;
    case 4:
        deleteProduct();
        break;
    case 5:
        toggleWishListIcon();
        break;
    case 6:
        RealoadWishListAJAX();
        break;
    case 7:
        OutputInCome();
        break;


    default:
        echo "Ошибка AJAX запроса";
        break;
    }