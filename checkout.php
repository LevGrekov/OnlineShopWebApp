<?php


require_once "common/Page.php";
require_once "common/PhpHelper.php";
use common\Page;
use common\PhpHelper;

class checkout extends Page
{
    public function __construct(){
        parent::__construct();
        if(count($this->dbh->getCartProducts($_SESSION['user']['id'])) <= 0 ){
            header("Location: /account.php");
            exit();
        }
    }

    protected function showContent()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->ShowCheckOutBlock();
    }

    private function ShowCheckOutBlock(){
        $products = $this->dbh->getCartProducts($_SESSION['user']['id']);
        ?>
        <div class="container my-5">
        <h1 class="text-center my-4">Оформление заказа</h1>
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body">
                    <form method="post" action="FakePaymentPage.php">
                        <!-- <div class="mb-3">
                            <label for="orderName" class="form-label">Имя</label>
                            <input type="text" class="form-control" id="orderName" name="orderName" placeholder="Введите ваше имя*" value="<?php $_SESSION['user']['first_name']?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="orderSurname" class="form-label">Фамилия</label>
                            <input type="text" class="form-control" id="orderSurname" name="orderSurname" placeholder="Введите вашу фамилию*" value="<?php $_SESSION['user']['last_name']?>" required>
                        </div> -->
                            <h4>Здравствуйте, <?php echo $_SESSION['user']['first_name']?> <?php echo $_SESSION['user']['last_name']?>, Оставте ваши данные для связи</h4>
                        <div class="form-group">
                            <label for="email">Адрес эл. почты*</label>
                            <input type="email" class="form-control"name='email' id="email" value="<?php echo $_SESSION['user']['email'] ?? ""?> " required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Телефон*</label>
                            <input type="tel" class="form-control" name='phone' id="phone" value="<?php echo $_SESSION['user']['phone'] ?? ""?> " required>
                        </div>
                        <!-- <div class="form-group">
                            <label for="country">Страна*</label>
                            <input type="text" class="form-control" id="country" required>
                        </div>
                        <div class="form-group">
                            <label for="city">Город/населённый пункт*</label>
                            <input type="text" class="form-control" id="city" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Адрес для доставки*</label>
                            <input type="text" class="form-control" id="address" required>
                        </div>
                        <div class="form-group">
                            <label for="zip">Индекс*</label>
                            <input type="text" class="form-control" id="zip" required>
                        </div> -->
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user']['id']; ?>">
                        <div class="form-group">
                            <label for="comment">Комментарий к заказу</label>
                            <textarea class="form-control" name='comment' id="comment"></textarea>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']?>">
                        <button type="submit" class="btn btn-primary mt-3">Оплатить Заказ</button>
                        </form>
                        
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="container">
                    <h2 class="text-center">Ваш Заказ</h2>
                    <div class="row">
                        <div class="col-9">
                        <p>Продукт</p>
                        <?php for($i=0;$i<count($products);$i++):?>
                        <p><?php echo $products[$i]['amount'] ?>x <?php echo $products[$i]['name']?></p>
                        <?php endfor;?>
                        <p>Shipping</p>
                        <p><strong>ВСЕГО:</strong></p>
                        </div>
                        <div class="col-3">
                        <p>Цена</p>
                        <?php for($i=0;$i<count($products);$i++):?>
                        <p><?php echo $products[$i]['current_price'] ?>₽</p>
                        <?php endfor;?>
                        <p>FREE</p>
                        <h4 class="fs-lg"><strong class="text-primary "><?php echo $this->dbh->calculateSubtotal($products)?>₽</strong></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }

}

(new checkout())->ShowPage();