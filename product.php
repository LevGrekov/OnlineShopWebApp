<?php

require_once "common/Page.php";
require_once "common/PhpHelper.php";
use common\Page;
use common\PhpHelper;

class product extends Page
{
    
    private $product;

    public function __construct() {
        parent::__construct();
        $this->product = $this->dbh->getProductByID($_GET['id'])[0];
        if(!isset($_GET['id'])){
            header("Location: /index.php");
            exit();
        }
        if(isset($_POST['ProductIDPriceChange'])){
            $newPrice = $_POST['newPrice'];
            $id = $_POST['ProductIDPriceChange'];
            $this->dbh->updateProductPrice($id,$newPrice);

            header("Location: /product.php?id=$id");
            exit();
        }
    }

    protected function ShowContent(){
        $this->showMainBlock();
        $this->ShowRatingBlock();
    }

    private function ShowRatingBlock(){
        ?>
        <div class="container my-5">
        <div class="row">
            <div class="col-md-3">
            <h2 class="text-center">Рейтинг</h2>
            <!-- Код для отображения рейтинга -->
            <div id="rating">
                <h4 class="rating-avg">
                    <span><?php echo number_format(round($this->product['rating'], 1), 1); ?></span>
                    <div class="d-inline-block ml-2">
                    <?php echo RatingBar($this->product['rating']); ?>
                    </div>
                    </h4>
                <ul class="list-unstyled rating">
                    <?php for($i = 5; $i>0;$i--):?>
                    <li class="mb-0  py-0">
                    <div class="d-flex align-items-center" style="height: 30px">
                        <div class="rating-stars me-3">
                        <?php echo RatingBar($i); ?>
                        </div>
                        <div class="progress flex-grow-1 me-3">
                        <div class="progress-bar" role="progressbar" style="width: <?=$i*20?>%;" aria-valuenow="<?=$i*20?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="sum my-0">
                        <?php 
                         echo $this->dbh->getProductsRatingsGroups($this->product['id'],$i)[0]['count'];
                         ?>
                         </span>
                    </div>
                    </li>
                    <?php endfor;?>
                </ul>
            </div>
            </div>
            <div class="col-md-6">
            <h2 class="text-center">Комментарии</h2>

<div class="container border-top border-bottom">
  <div class="comment-scroll">
    <!-- Код для отображения комментариев -->
    <?php

    $comments = $this->dbh->getProductsRatings($this->product['id']);
    if(count($comments) == 0) {
        ?>      
        <div class="card-body d-flex justify-content-center align-items-center" style="height: 100%;">
            <div class="card mb-3">
                <p class="card-text m-3"><?="Пока нет ни одного отзыва, Будь первым !"?></p>
            </div>
        </div>
        <?php
    }
    for ($i = 0; $i < count($comments); $i++): ?>
        <div class="card mb-3">
            <div class="row g-0">
                <div class="col-md-5">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo $comments[$i]['firstname'] . ' ' . $comments[$i]['lastname']; ?></h5>
                        <p class="card-text"><?php echo $comments[$i]['date']; ?></p>
                        <p class="card-text"><?php echo RatingBar(($comments[$i]['rate'])); ?></p>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card-body d-flex justify-content-center align-items-center" style="height: 100%;">
                        <p class="card-text"><?php echo $comments[$i]['text']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor; ?> 
  </div>
</div>
            </div>
            <div class="col-md-3">
            <h2 class="text-center">Оставить Отзыв</h2>
            <!-- Код для формы добавления комментария -->
            <?php $this->GenerateFormForReview();?>
            
        </div>
        </div>
        </div>
        <?php
    }
    private function showMainBlock()
    {
        ?>
        <div class="container rounded border shadow mt-5 mb-5">
            <div class = "row my-5">
                <div class = col-md-2>
                    <!--Futures-->
                </div>
                <div class = col-md-5>
                    <div id="imageContainerProduct" style="width: 100%; height: 25rem; overflow: hidden;">
                    <img src="productImages/<?= $this->product['img'] ?>" onerror="this.onerror=null;this.src='https:via.placeholder.com/250x200';" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover;"  alt="...">
                    </div>  
                </div>
                <div class ="col-md-5">
                <div class="product-details">
                    <div class= "container">
                    <h2 class="product-name"><?= $this->product['name'] ?></h2>
                        <div class="d-flex align-items-center">
                        <div class="product-rating me-3">
                            <?php echo RatingBar($this->product['rating']) ?>
                        </div>
                        <a href="#review-form" class="text-reset text-decoration-none">
                             <?= count(($this->dbh->getAllReviewsOnTheProduct($this->product['id']))) ?? "Нет "?> Отзывов | <span class="text-primary"> Добавить Отзыв </span>
                        </a>
                        </div>
                        <h3>
                            ₽<?= $this->product['current_price'] ?> 
                            <del class="text-decoration-line-through text-muted small me-2"> ₽<?= $this->product['current_price'] * 3 ?> </del>
                            <span class="text-success fs-6">В наличии</span>
                        </h3>
                        <p>
                        <?php 
                         if($this->product['description'] != null){
                            echo $this->product['description'];
                         }
                         else echo "У данного товара нет описания :("
                        ?>
                        </p>
                        <div class = "col-md-9">
                        <?php if(!isset($_SESSION['user'])):?>

                            <div class="input-group mb-3">
                                    <input type="number" class="form-control" min="1" placeholder="Кол-во" aria-label="Количество" aria-describedby="add-to-cart-button" name="quantity">
                                    <a href="autorizationPage.php" class="btn btn-primary">
                                        <i class="fas fa-shopping-cart me-2"></i> Добавить в корзину
                                    </a>
                                </div>
                        <?php else:?>    
                            <?php if($this->dbh->isProductInCart($_SESSION['user']['id'],$this->product['id'])==true):?>
                            <form action="validationForms/additionBDnewElements.php?form=RemProductToCart" method="POST">
                                <div class="input-group mb-3">
                                    <input type="hidden" name="id" value="<?= $this->product['id'] ?>">
                                    <button class="btn btn-secondary" type="submit" id="add-to-cart-button">
                                        <i class="fa-solid fa-cart-arrow-down"></i> Убрать из Корзины
                                    </button>
                                </div>
                            </form>
                            <?php else:?>
                            <form action="validationForms/additionBDnewElements.php?form=ProductToCart" method="POST">
                                <div class="input-group mb-3">
                                    <input type="hidden" name="id" value="<?= $this->product['id'] ?>">
                                    <input type="number" class="form-control" min="1" placeholder="Кол-во" aria-label="Количество" aria-describedby="add-to-cart-button" name="quantity">
                                    <button class="btn btn-primary" type="submit" id="add-to-cart-button">
                                        <i class="fas fa-shopping-cart me-2"></i> Добавить в корзину
                                    </button>
                                </div>
                            </form>
                            <?php endif;?>
                        <?php endif;?>
                        <div class = "mb-2">
                        <a class="HideBlue" href="">Добавить в избранное <i class="fas fa-heart"></i> </a>
                        <!--<a class="HideBlue" href="">Убрать из избранного <i class="fa-solid fa-heart-crack"></i> </a>-->
                        </div>
                        <div class="category-brand">
                            <p class="category small text-muted mb-2">Категория: <?=$this->product['category']?></p>
                            <p class="brand small text-muted mb-2">Бренд: <?=$this->product['brand']?></p>
                            <p class="date small text-muted mb-2">Товар появился в магазине:</p> 
                            <p class="date small text-muted mb-2"><?=$this->product['date_of_add']?></p>
                        </div>
                        <?php if(isset($_SESSION['user']) && $_SESSION['user']['isAdmin']==True):?>
                            <form action="product.php" method="post">
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control" min="1" placeholder="новая цена" aria-label="Новая Цена" aria-describedby="newPrice" name="newPrice">
                                    <button class="btn btn-secondary" type="submit" id="newPrice">
                                    <i class="fa-solid fa-hand-holding-dollar"></i> Изменить цену
                                    </button>
                                    <input type="hidden" name="ProductIDPriceChange" value="<?=$this->product['id']?>">
                                </div>
                            </form>
                        <?php endif;?>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <?php
    }  

    private function GenerateFormForReview(){

        if(isset($_SESSION['user'])){
            $yetPostedComment = $this->dbh->getReviewFromUserOnChoosenProduct($this->product['id'],$_SESSION['user']['id']);
        }
        else{
            $yetPostedComment = null;
        }

        
            ?>
            <form id="review-form" action="validationForms\additionBDnewElements.php?form=comment" method="post";>
            <input type="hidden" name="product_id" value="<?php echo $this->product['id']; ?>">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user']['id'] ?? null; ?>">
            <?php
            if($yetPostedComment != null){
                print("<p>Вы уже оставили отзыв на этот товар!</p>");
             }
            ?>
            <div class="form-group my-4">
                <textarea class="form-control" id="comment" name="comment"placeholder="<?php echo isset($_SESSION['user']) ? 'Комментарий' : 'Отзывы оставлять могут только авторизированные пользователи'; ?>" rows="3"></textarea>
            </div>

            <div class="d-flex align-items-center my-3" style="height: 30px">
            <span class="me-3">Ваш рейтинг:</span>
            <div class="rating-area">
                <input type="radio" id="star-5" name="rating" value="5" required>
                <label for="star-5" title="Оценка «5»"><i class="fas fa-star fa-lg"></i></label>
                <input type="radio" id="star-4" name="rating" value="4" required>
                <label for="star-4" title="Оценка «4»"><i class="fas fa-star fa-lg"></i></label>
                <input type="radio" id="star-3" name="rating" value="3" required >
                <label for="star-3" title="Оценка «3»"><i class="fas fa-star fa-lg"></i></label>
                <input type="radio" id="star-2" name="rating" value="2" required>
                <label for="star-2" title="Оценка «2»"><i class="fas fa-star fa-lg"></i></label>
                <input type="radio" id="star-1" name="rating" value="1" required>
                <label for="star-1" title="Оценка «1»"><i class="fas fa-star fa-lg"></i></label>
            </div>
            </div>
            
            <?php
             if(!isset($_SESSION['user'])){
                echo '<a href="account.php" class="btn btn-primary">Авторизироваться</a>';
             }
             else if($yetPostedComment != null){
                ?>               
                <input type="hidden" name="edit" value="<?= $yetPostedComment['id'] ?? null?>">
                <button type="submit" class="btn btn-primary">Изменить</button>
                <?php
             }
             else{
                echo '<button type="submit" class="btn btn-primary">Подтвердить</button>';
             }
            ?>
            </form>
            <?php
        }
    }

(new product())->showPage();