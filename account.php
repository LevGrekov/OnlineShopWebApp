<?php

require_once "common/Page.php";
require_once "common/PhpHelper.php";
use common\Page;
use common\DbHelper;

class account extends Page
{
    public function __construct()
    {
        parent::__construct();
        if(!isset($_SESSION['user'])){
            header("Location: /autorizationPage.php");
            exit();
        }
        if(isset($_REQUEST['CanceledOrder_id'])){
            $id = $_POST['CanceledOrder_id'];
            $this->CancelOrder($id);
        }
        if (isset($_REQUEST['exit']))
        {
            unset($_SESSION['user']);
            header("Location: /autorizationPage.php");
            exit();
        }
        if (isset($_REQUEST['changeProf'])){
            $this->updateUserProfile();
            header("Location: /account.php");
            exit();
        }
        

    }


    protected function showContent()
    {
        if (isset($_SESSION['user'])) 
        {
            $this->showAccount();
        }
    }

    private function updateUserProfile(){
        $userId = $_SESSION['user']['id'];
        $name = htmlspecialchars($_POST['name']);
        $surname = htmlspecialchars($_POST['surname']);
        $email = htmlspecialchars($_POST['email']);
        $phone = htmlspecialchars($_POST['phone']);

        $this->dbh->updateUserProfile($userId,$name,$surname,$email,$phone);
        $_SESSION['user'] = $this->dbh->getUser($_SESSION['user']['login'],$_SESSION['user']['password'])[0];
    }

    private function showAccount()
    {
        ?>
        <div class="container my-4 mx-auto ">
            <div class="row">
            <div class="col-md-4">
                <div class="card">
                <div class="card-body">
                    <div class="text-center mb-3">
                    <img src="https://via.placeholder.com/150" alt="Avatar" class="avatar">
                    </div>
                    <h5 class="card-title text-center"><?=$_SESSION['user']['first_name']?> <?=$_SESSION['user']['last_name']?></h5>
                    <p class="card-text">Email: <?=$_SESSION['user']['email']?></p>
                    <p class="card-text">Телефон: <?=$_SESSION['user']['phone']?></p>
                    <!-- <p class="card-text">Адрес: Москва, ул. Пушкина, д. 10, кв. 5</p> -->
                    <a href="/account.php?exit=1" class="btn btn-primary">Выход</a>
                </div>
                </div>
            </div>
                <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                        <a class="nav-link active" href="#orders" data-bs-toggle="tab">Мои Заказы</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="#changeProf" data-bs-toggle="tab">Изменить Профиль</a>
                        </li>
                        <!-- <li class="nav-item">
                        <a class="nav-link" href="#ChangePassword" data-bs-toggle="tab">Изменить Пароль</a>
                        </li> -->
                    </ul>
                    </div>
                    <div class="card-body">
                    <div class="tab-content">
                    <div class="tab-pane fade show active" id="orders">
                            <div class="col-md-12">
                                <h3>Мои заказы</h3>
                                <hr>
                                <div class="table-responsive">
                                    <div class="list-group">
                                        <div class="list-group-item list-group-item-dark">
                                            <div class="row align-items-center">
                                                <div class="col-md-1 text-center">№</div>
                                                <div class="col-md-3 text-center">Дата заказа</div>
                                                <div class="col-md-2 text-center">Статус</div>
                                                <div class="col-md-1 text-center"></div>
                                                <div class="col-md-2 text-center">Сумма</div>
                                                <div class="col-md-3 text-center">Действия</div>
                                            </div>
                                        </div>
                                        <?php 
                                        $orders = $this->dbh->GetAllUsersOrders($_SESSION['user']['id']);
                                        // echo var_dump($orders);
                                        // echo "\n";
                                        // echo var_dump($_SESSION['nonPaidOrders']);
                                        for($i=0;$i<count($orders);$i++):?>
                                            <div class="list-group-item">
                                                <div class="row align-items-center">
                                                    <div class="col-md-1 text-center"> #<?= $orders[$i]['id'] ?></div>
                                                    <div class="col-md-3 text-center"><?= $orders[$i]['order_date'] ?></div>
                                                    <div class="col-md-2 text-center <?php if( $orders[$i]['status'] == "Отменен") echo "text-danger" ?>"><?= $orders[$i]['status'] ?></div>
                                                    <div class="col-md-1 text-center">
                                                        <?php if ($orders[$i]['status'] == "Ожидает Оплаты"):?>
                                                            <form action="FakePaymentPage.php" method= "post">
                                                                <?php if(isset($_SESSION['nonPaidOrders'][$orders[$i]['id']])):?>
                                                                    <input type="hidden" name="returnToPayment" value="<?= $orders[$i]['id']?>">
                                                                <?php endif;?>
                                                                <button  class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Оплатить">
                                                                    <i class="fa-solid fa-sack-dollar"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-2 text-center"><?= $orders[$i]['total_price'] ?></div>
                                                    <div class="col-md-3 text-center">
                                                        <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#orderDetails<?= $orders[$i]['id'] ?>" role="button" aria-expanded="false" aria-controls="orderDetails<?= $orders[$i]['id'] ?>">
                                                            Подробнее
                                                        </a>
                                                    </div>
                                                    <div class="collapse" id="orderDetails<?= $orders[$i]['id'] ?>">
                                                        <div class="card card-body">
                                                            <div class='row'>
                                                            <h5 class="mb-4 col-md-9">Продукты в Заказе</h5>

                                                            <?php if($orders[$i]['status'] == "В Доставке"):?>
                                                            <div class="col-md-3">
                                                                <form action="account.php" method="POST">
                                                                    <input type="hidden" name="CanceledOrder_id" value="<?= $orders[$i]['id'] ?>">
                                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите отменить заказ?')">Отменить заказ</button>
                                                                </form>
                                                            </div>
                                                            
                                                            <?php endif;?>
                                                            </div>
                                                            <?php
                                                            $products = $this->dbh->GetProductsInSpecificOrder($orders[$i]['id']);
                                                            foreach ($products as $product): ?>
                                                                <div class="product">
                                                                    <div class="row my-2 ">
                                                                        <div class = col-md-2>
                                                                            <div class="imgHolder m-3" style="width:80%; height: 80%; overflow: hidden;">
                                                                                <img src="productImages/<?php echo $product['img'] ?>" onerror="this.onerror=null;this.src='https:via.placeholder.com/250x200';" class="card-img-top" style="width: 100%; height: 100%; object-fit: cover;" alt="...">
                                                                            </div>  
                                                                        </div>
                                                                        <div class = col-md-5>
                                                                        <h6><?= $product['name'] ?></h6>
                                                                            <p>Количество: <?= $product['amount'] ?></p>
                                                                            <p>Общая Стоимость: <?= $product['priceForPos'] ?></p>
                                                                        </div>
                                                                        <div class="col-md-5 d-flex justify-content-center align-items-center">
                                                                            <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-outline-secondary btn-sm opacity-50">Перейти на страницу в магазине</a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endfor;?>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="tab-pane fade" id="changeProf">
                        <form action="account.php" method="post">
                            <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input type="text" name="name" class="form-control" id="name" placeholder="Введите имя">
                            </div>
                            <div class="mb-3">
                            <label for="name" class="form-label">Фамилия</label>
                            <input type="text" name="surname" class="form-control" id="surname" placeholder="Введите Фамилию">
                            </div>
                            <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" id="email" placeholder="Введите email">
                            </div>
                            <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" name="phone" class="form-control" id="phone" placeholder="Введите телефон">
                            </div>
                            <input type="hidden" name="changeProf" value="1">
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                        </form>
                        </div>

                        <!-- <div class="tab-pane fade" id="ChangePassword">
                            <h3>Изменить пароль</h3>
                            <hr>
                            <form>
                            <div class="mb-3">
                                <label for="current-password" class="form-label">Текущий пароль</label>
                                <input type="password" class="form-control" id="current-password" name="current-password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new-password" class="form-label">Новый пароль</label>
                                <input type="password" class="form-control" id="new-password" name="new-password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm-password" class="form-label">Подтверждение нового пароля</label>
                                <input type="password" class="form-control" id="confirm-password" name="confirm-password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Изменить пароль</button>
                            </form>
                        </div> -->
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
        <?php
    }

    private function CancelOrder($OrderID){
        $order = $this->dbh->getOrderById($OrderID);
        if($order['status'] != "Отменен" || $order['status'] != "Доставлен"){
            $this->dbh->changeOrderStatus($OrderID,"Отменен");
        }
    }
}

(new account())->showPage();