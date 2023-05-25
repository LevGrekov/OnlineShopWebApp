<?php
require_once "DbHelper.php";
use common\DbHelper;

    function RatingBar($rating) {
        $html = '<div class="product-rating my-3">';
        $html .= '<div class=" starholder">';

        if($rating == null)
        {
            for($i =0; $i<5;$i++){
                $html .= '<i class="fa-regular fa-star text-secondary"></i>';
            }
        }
        else{
            $fullStars = floor($rating);
            $halfStars = round($rating - $fullStars, 1) >= 0.5 ? 1 : 0;
            $emptyStars = 5 - $fullStars - $halfStars;
            for ($i = 0; $i < $fullStars; $i++) {
            $html .= '<i class="fa-solid fa-star text-primary"></i>';
            }
            if ($halfStars > 0) {
            $html .= '<i class="fa-solid fa-star-half-stroke text-primary"></i>';
            }
            for ($i = 0; $i < $emptyStars; $i++) {
            $html .= '<i class="fa-regular fa-star text-primary"></i>';
            }
        }
        $html .= '</div>';
        $html .= '</div>';
    
        return $html;
    }

    function GenerateProductHolder($product,DBHelper $dbh){
        ?>
        <div class="col-md-3">
  <div class="card mb-4">
    <a href="product.php?id=<?= $product['id']; ?>" class="card-link">
    <div class = "mainInfo">
      <div class="imgHolder" style="width: 100%; height: 200px; overflow: hidden;">
        <img src="productImages/<?php echo $product['img'] ?>" onerror="this.onerror=null;this.src='https:via.placeholder.com/250x200';" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover;" alt="...">
      </div>  
      <div class="card-body text-center">
        <p class="text-muted my-1"><?=$product['category']?></p>
        <p class="text-muted"><?=$product['brand']?></p>
        <div class="card-title-holder" style="height: 2.8rem; overflow: hidden;">
          <h5 class="card-title" style="color:black; overflow-wrap: break-word;"><?=$product['name']?></h5>
        </div>
        <p class="card-text" style="color:black">Цена: <?=$product['current_price']?> руб.</p>
        <?php echo RatingBar($product['rating']);?>
      </div>
    </div>
    </a>
    <div class="d-flex justify-content-center my-2" id = "products-btns">

    <?php if(isset($_SESSION['user'])):?>
      <button id="cartIcon<?=$product['id']?>" class="btn btn-outline-primary rounded-circle p-2 lh-1 mx-2 mx-md-2" type="button" onclick="toggleCartStatus(<?= $product['id'] ?>)">
        <i class="<?= $dbh->isProductInCart($_SESSION['user']['id'], $product['id']) ? "fa-solid fa-check" : 'fa-solid fa-cart-plus' ?>"></i>
      </button>

      <button id ="wishlistIcon<?=$product['id']?>" class="btn btn-outline-primary rounded-circle p-2 lh-1 mx-2 mx-md-2" type="button" onclick="toggleWishlistStatus(<?=$product['id']?>)">
      <i class="<?= $dbh->isProductInWishList($_SESSION['user']['id'], $product['id']) ? "fa-solid fa-heart-circle-minus" : 'fa-regular fa-heart' ?>"></i>
      </button> 
    <?php else:?>
      <a href="autorizationPage.php" class="btn btn-outline-primary rounded-circle p-2 lh-1 mx-2 mx-md-2">
        <i class="fa-solid fa-cart-plus"></i>
      </a>

      <a href="autorizationPage.php" class="btn btn-outline-primary rounded-circle p-2 lh-1 mx-2 mx-md-2">
        <i class="fa-regular fa-heart"></i>
      </a>
    <?php endif?>
      <button class="btn btn-outline-primary rounded-circle p-2 lh-1 mx-2 mx-md-2" type="button">
        <i class="fas fa-search"></i>
        <span class="visually-hidden">Dismiss</span>
      </button>
    </div>
  </div>
</div>

        <?php
    }

    function GenerateProductHolderForWishList($product,DBHelper $dbh){
          ?>
          <div class="col-md-2">
            <div class="card mb-4">
              <a href="product.php?id=<?= $product['id']; ?>" class="card-link">
                <div class="imgHolder" style="width: 100%; height: 150px; overflow: hidden;">
                  <img src="productImages/<?php echo $product['img'] ?>" onerror="this.onerror=null;this.src='https:via.placeholder.com/250x200';" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover;" alt="...">
                </div>
              </a>  
                <div class="card-body text-center">
                  <div class="card-title-holder">
                    <b><h7 class="card-title"  style="color:black"><?=$product['name']?></h7></b>
                  </div>
                  <p class="card-text" style="color:black"><?=$product['current_price']?>₽</p>
                  <?php echo RatingBar($product['rating']);?>
                  <div class="d-flex justify-content-center my-0">
                  <div class="d-inline-flex">
                    <button id="cartIcon<?=$product['id']?>" class="btn btn-primary p-2 lh-1 mx-2 mx-md-2" type="button" onclick="toggleCartStatus(<?= $product['id'] ?>)">
                      <i class="<?= $dbh->isProductInCart($_SESSION['user']['id'], $product['id']) ? "fa-solid fa-check" : 'fa-solid fa-cart-plus' ?>"></i>
                    </button>
                    <button id ="wishlistIcon<?=$product['id']?>" class="btn btn-outline-secondary rounded-circle p-2 lh-1 mx-2 mx-md-2" type="button" onclick="toggleWishlistStatus(<?=$product['id']?>)">
                    <i class="<?= $dbh->isProductInWishList($_SESSION['user']['id'], $product['id']) ? "fa-solid fa-heart-circle-minus" : 'fa-regular fa-heart' ?>"></i>
                    </button> 
                  </div>
                </div>
                </div>
              
            </div>
          </div>
          <?php
    }

    function GetCategoriesOptionsList(DBHelper $dbh){
        $categories = $dbh->getAllCategories();
        for ($i=0;$i<count($categories);$i++):
            ?>
            <option value="<?echo $categories[$i]['id']?>" name="search-category"><?echo $categories[$i]['name']?></option>
        <?php endfor;?>
        <?php
    }

    function GetProductsInUsersCart(DBHelper $dbh){

      if(count($dbh->getCartProducts($_SESSION['user']['id']))>0):
      $products = $dbh->getCartProducts($_SESSION['user']['id']);
      for($i=0;$i<count($products);$i++):
      ?>
      <div class="product">
          <div class="imgHolder" style="width: 10rem; height: auto; overflow: hidden;">
              <img src="productImages/<?php echo $products[$i]['img'] ?>" onerror="this.onerror=null;this.src='https:via.placeholder.com/250x200';" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover; margin-right: 10px;" alt="...">
          </div>
          <div class="product-info d-flex flex-column justify-content-between m-3">
              <div class="row">
                  <div class="col-md-10">
                      <h4 class="product-title"><?php echo $products[$i]['name'] ?></h4>
                  </div>
                  <div class="col-md-2">
                  <button class="btn btn-danger mt-2 p-0" onclick="deleteProduct(<?=$products[$i]['id']?>)" style="border: none; background-color: transparent;">
                      <i class="fas fa-times text-danger"></i>
                  </button>
                  </div>
              </div>
              
              <div class="d-flex align-items-center">
                  <p class="product-price me-3"><?php echo $products[$i]['current_price'] ?>₽</p>
                  <p class="product-quantity"><?php echo $products[$i]['amount'] ?> шт.</p>
              </div>
              <a href="product.php?id=<?php echo $products[$i]['id']?>" class="text-decoration-none">
                  <i class=" btn btn-primary fas fa-external-link-alt me-2"></i>
                  Посмотреть в магазине
              </a>
          </div>
      </div>
      <?php endfor;?>
      <<div class="cart-summary bg-light p-3 rounded">
        <p class="mb-2"><?php echo count($products); ?> Продуктов в Корзине</p>
        <p class="fw-bold mb-3">ВСЕГО: <?php echo $dbh->calculateSubtotal($products)?>₽</p>
        <a class="btn btn-primary" href="checkout.php?userCart=<?=$_SESSION['user']['id']?>">Перейти к Оформлению</a>
      </div>
      <?php else:?>
      <div class="text-center">Ваша корзина пуста</div>
      <div class="text-center mt-3"><a href="../index.php" class="btn btn-primary">Перейти к покупкам</a></div>
      <?php endif;
    }