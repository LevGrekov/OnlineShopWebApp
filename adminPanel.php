<?php

require_once "common/Page.php";
require_once "common/PhpHelper.php";
use common\Page;
use common\DbHelper;

class adminPanel extends Page
{

    public function __construct(){
        parent::__construct();
        if(isset($_REQUEST['ConfirmedOrder'])){
            $id = $_POST['ConfirmedOrder'];
            $this->ConformDelivery($id);
        }
    }
    protected function showContent()
    {
        if (isset($_SESSION['user'])) 
        {
            if($_SESSION['user']['isAdmin'] == True)
            {
                $this->showAdminPanel();
            }
        }
        else{
            header("Location: /autorizationPage.php");
            exit();
        }
    }

    private function showAdminPanel()
    {
        
        ?>
        
        <div class="container rounded border shadow mt-5 mb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center border-bottom" style="margin-bottom: 0;">
      <h2 class="my-3">Админ Панель</h2>
    </div>
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="sidebar-sticky pt-3">
              <div class="col-md-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                  <a class="nav-link active" id="v-pills-AddProduct-tab" data-bs-toggle="pill" href="#v-pills-AddProduct" role="tab" aria-controls="v-pills-AddProduct" aria-selected="true">Добавление В БД</a>
                  <a class="nav-link" id="v-pills-countIncome-tab" data-bs-toggle="pill" href="#v-pills-countIncome" role="tab" aria-controls="v-pills-countIncome" aria-selected="false">Подсчет прибыли</a>
                  <a class="nav-link" id="v-pills-OrdersProcessing-tab" data-bs-toggle="pill" href="#v-pills-OrdersProcessing" role="tab" aria-controls="v-pills-OrdersProcessing" aria-selected="false">Обработка Заказов</a>
                </div>
              </div>
            </div>
        </nav>

        <!-- Main content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="row">
              <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade" id="v-pills-countIncome" role="tabpanel" aria-labelledby="v-pills-countIncome-tab">
                <?php $this->ShowIncomeCounter();?>
                </div>
                <?php $this->ShowProductAdditionalInstr()?>
                <div class="tab-pane fade" id="v-pills-OrdersProcessing" role="tabpanel" aria-labelledby="v-pills-OrdersProcessing-tab">
                  <h3 class="my-3">Обработка Заказов</h3>
                  <?$this->ShowOrdersLists();?>
                </div>
              </div>
            </div>
        </main>
    </div>
</div>

        <?php
    }

    private function ShowIncomeCounter(){
        ?>
        <div class="container">
                    <h3 class="my-3">Расчет прибыли</h3>
                    <div class="row">
                    <div class="col-md-4">
                    <div class="form-group">
                            <label for="start_date">Начальная дата:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="end_date">Конечная дата:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class = "col-md-4">
                        <button type="button" class="btn btn-primary my-3" onclick="calculateProfit()">Рассчитать прибыль за период</button>
                        </div>
                        <div class = "col-md-4">
                        <div class="d-flex justify-content-between bg-light my-3 p-2" style="max-width: 400px;">
                            <h4>Результат: <span id="InComeResult"></span></h4>
                        </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex justify-content-between bg-light my-2 p-2 border border-radius">
                            <h5>За всё время было заработано: <span class="text-primary"><?= $this->dbh->calculateProfit('Доставлен')?>₽ </span></h4>
                        </div>
                    </div>
                    <div class="row">
                    <div class="d-flex justify-content-between bg-light my-2 p-2 border border-radius">
                            <h5>Отмененных заказов: <strong><span><?=count($this->dbh->GetAllOrdersWithStatus('Отменен'))?></span></strong> на сумму: <span class="text-primary"><?= $this->dbh->calculateProfit('Отменен')?>₽ </span></h4>
                        </div>
                    </div>
                    <div class="row">
                    <div class="d-flex justify-content-between bg-light my-2 p-2 border border-radius">
                            <h5>Ожидается Прибыли на сумму: <span class="text-primary"><?= $this->dbh->calculateProfit('В Доставке')?>₽ </span> с <strong><span><?=count($this->dbh->GetAllOrdersWithStatus('В Доставке'))?></span></strong> заказов В Доставке</h4>
                        </div>
                    </div>
                
                </div>
        <?php
    }
    private function ShowProductAdditionalInstr(){
        ?>
                        <div class="tab-pane fade show active" id="v-pills-AddProduct" role="tabpanel" aria-labelledby="v-pills-AddProduct-tab">
                  <h3 class="my-3">Добавление Записей В БД</h3>
                  <div class="container border shadow my-4" style="border-radius: 20px;">
                    <form action="validationForms/additionBDnewElements.php?form=product" method="post" enctype="multipart/form-data" class="my-3">
                      <h4>Добавить продукт</h4>
                      <div class="mb-3">
                        <label for="productName" class="form-label">Название продукта</label>
                        <input type="text" name="name" class="form-control" id="productName" placeholder="Введите название продукта" required>
                      </div>
                      <div class="mb-3">
                        <label for="productBrand" class="form-label">Бренд</label>
                        <select name="brand_id" class="form-select" id="productBrand" required>
                          <?php
                          $brands = $this->dbh->getAllBrands();
                          for ($i=0;$i<count($brands);$i++):
                            ?>
                              <option value="<?echo $brands[$i]['id']?>"><?echo $brands[$i]['name']?></option>
                          <?php endfor;?>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="productCategory" class="form-label">Категория</label>
                        <select name="category_id" class="form-select" id="productCategory" required>
                        <?php GetCategoriesOptionsList($this->dbh)?>
                        </select>
                      </div>
                      <div class="mb-3">
                      <label class="form-label">Выберите способ загрузки изображения:</label>
                      <div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="imageSource" id="imageSourceUrl" value="url" checked>
                              <label class="form-check-label" for="imageSourceUrl">
                                  Указать ссылку на изображение
                              </label>
                          </div>
                          <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="imageSource" id="imageSourceFile" value="file" >
                              <label class="form-check-label" for="imageSourceFile">
                                  Загрузить с компьютера
                              </label>
                          </div>
                      </div>
                      </div>
                        <div class="mb-3" id="imageSourceUrlInput">
                            <label for="imageUrl" class="form-label">Ссылка на изображение</label>
                            <input type="url" name="imageUrl" class="form-control" id="imageUrl">
                        </div>
                        <div class="mb-3" id="imageSourceFileInput" style="display: none;">
                            <label for="productImage" class="form-label">Изображение</label>
                            <input type="file" name="productImage" class="form-control" id="productImage">
                        </div>


                        <div class="mb-3">
                            <label for="productPrice" class="form-label">Цена продукта в рублях</label>
                            <input type="number" name="price" class="form-control" id="productPrice" placeholder="Введите стоимость продукта" step="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label for="productDescription" class="form-label">Описание продукта</label>
                            <textarea name="description" class="form-control" id="productDescription" placeholder="Введите описание продукта" ></textarea>
                        </div>
                        
                      <button type="submit" class="btn btn-primary">Добавить продукт</button>

                    </form>
                  </div>
                  <div class="container border shadow my-4" style="border-radius: 20px;">
                      <h4 class="my-3">Новые Бренды и Категории Товаров</h4>
                      <!-- Форма для добавления Бренда -->
                      <form action="validationForms/additionBDnewElements.php?form=brand" method="post" class="my-1">
                      <div class="input-group mb-3">
                          <input type="text" name="name" class="form-control" placeholder="Введите название брэнда" id="brandName" required>
                          <button type="submit" class="btn btn-outline-primary">Добавить</button>
                      </div>
                      <input type="hidden" name="return_url" value="<?php echo $_SERVER['HTTP_REFERER']; ?>">
                      </form>

                      <!-- Форма для добавления категории -->
                      <form action="validationForms/additionBDnewElements.php?form=category" method="post" class="my-1">
                      <div class="input-group mb-3">
                          <input type="text" name="name" placeholder="Введите название категории" class="form-control" id="categoryName" required>
                          <button type="submit" class="btn btn-outline-primary">Добавить</button>
                      </div>
                      <input type="hidden" name="return_url" value="<?php echo $_SERVER['HTTP_REFERER']; ?>">
                      </form>
                  </div>
                </div>
        <?php

    }

    private function ShowOrdersLists(){
        ?>
        <div class="card">
                    <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                        <a class="nav-link active" href="#InDelivety" data-bs-toggle="tab">В Доставке</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="#Canceled" data-bs-toggle="tab">Отменены</a>
                        </li>
                        <li class="nav-item">
                        <a class="nav-link" href="#Delivered" data-bs-toggle="tab">Доставлены</a>
                        </li>
                    </ul>
                    </div>
                    <div class="card-body">
                    <div class="tab-content">
                    <div class="tab-pane fade show active" id="InDelivety">
                    <?php $this->OrdersInDelivery(); ?>
                    </div>
                    <div class="tab-pane fade" id="Canceled">
                    <?php $this->OrdersCanceled(); ?>
                    </div>
                    <div class="tab-pane fade" id="Delivered">
                    <?php $this->OrdersDelivered(); ?>
                    </div>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
                <?php
    }
    private function OrdersInDelivery(){
        ?>

    <div class="container my-4 mx-auto ">
    <div class="col-md-12">
        <h3>Выдача Заказов</h3>
        <hr>
        <div class="table-responsive">
            <div class="list-group">
                <div class="list-group-item list-group-item-dark">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">№</div>
                        <div class="col-md-2 text-center">Дата заказа</div>
                        <div class="col-md-3 text-center">Пользователь</div>
                        <div class="col-md-2 text-center">Статус</div>
                        <div class="col-md-1 text-center">Сумма</div>
                        <div class="col-md-2 text-center">Подтвердить</div>
                        <div class="col-md-1 text-center">Детали</div>
                    </div>
                </div>
                <?php 
                $orders = $this->dbh->GetAllOrdersWithStatus('В Доставке');
                // echo var_dump($orders);
                // echo "\n";
                // echo var_dump($_SESSION['nonPaidOrders']);
                for($i=0;$i<count($orders);$i++):?>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center"> #<?= $orders[$i]['id'] ?></div>
                            <div class="col-md-2 text-center"><?= $orders[$i]['order_date'] ?></div>
                            <div class="col-md-3 text-center"><?= $orders[$i]['login'].' ('.$orders[$i]['last_name'].' '.$orders[$i]['first_name'].')' ?></div>
                            <div class="col-md-2 text-center "><?= $orders[$i]['status'] ?></div>
                            <div class="col-md-1 text-center"><?= $orders[$i]['total_price'] ?></div>
                            <div class="col-md-2 text-center">
                                <form action="adminPanel.php" method="POST">
                                    <input type="hidden" name="ConfirmedOrder" value="<?= $orders[$i]['id'] ?>">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Вы уверены, что пользователь получил заказ?')">Confirm <i class="fa-solid fa-truck-ramp-box"></i></button>
                                </form>
                            </div>
                            <div class="col-md-1 text-center">
                                <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#orderDetails<?= $orders[$i]['id'] ?>" role="button" aria-expanded="false" aria-controls="orderDetails<?= $orders[$i]['id'] ?>">
                                <i class="fa-solid fa-circle-info"></i>
                                </a>
                            </div>
                            <div class="collapse" id="orderDetails<?= $orders[$i]['id'] ?>">
                                <div class="card card-body">
                                    <div class='row'>
                                    <h5 class="mb-4 col-md-9">Продукты в Заказе</h5>
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

        <?php
    }

    private function OrdersCanceled(){
        ?>
        <div class="container my-4 mx-auto ">
    <div class="col-md-12">
        <h3>Отмененные заказы</h3>
        <hr>
        <div class="table-responsive">
            <div class="list-group">
                <div class="list-group-item list-group-item-dark">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">№</div>
                        <div class="col-md-2 text-center">Дата заказа</div>
                        <div class="col-md-2 text-center">Дата Отмены</div>
                        <div class="col-md-3 text-center">Пользователь</div>
                        <div class="col-md-2 text-center">Статус</div>
                        <div class="col-md-1 text-center">Сумма</div>
                        <div class="col-md-1 text-center">Детали</div>
                    </div>
                </div>
                <?php 
                $orders = $this->dbh->GetAllOrdersWithStatus('Отменен');
                // echo var_dump($orders);
                // echo "\n";
                // echo var_dump($_SESSION['nonPaidOrders']);
                for($i=0;$i<count($orders);$i++):?>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center"> #<?= $orders[$i]['id'] ?></div>
                            <div class="col-md-2 text-center"><?= $orders[$i]['order_date'] ?></div>
                            <div class="col-md-2 text-center"><?= $orders[$i]['endDate'] ?></div>
                            <div class="col-md-3 text-center"><?= $orders[$i]['login'].' ('.$orders[$i]['last_name'].' '.$orders[$i]['first_name'].')' ?></div>
                            <div class="col-md-2 text-center "><?= $orders[$i]['status'] ?></div>
                            <div class="col-md-1 text-center"><?= $orders[$i]['total_price'] ?></div>
                            <div class="col-md-1 text-center">
                                <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#orderDetails<?= $orders[$i]['id'] ?>" role="button" aria-expanded="false" aria-controls="orderDetails<?= $orders[$i]['id'] ?>">
                                <i class="fa-solid fa-circle-info"></i>
                                </a>
                            </div>
                            <div class="collapse" id="orderDetails<?= $orders[$i]['id'] ?>">
                                <div class="card card-body">
                                    <div class='row'>
                                    <h5 class="mb-4 col-md-9">Продукты в Заказе</h5>
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

<?php
    }

    private function OrdersDelivered(){
        ?>
        <div class="container my-4 mx-auto ">
    <div class="col-md-12">
        <h3>Доставленные заказы</h3>
        <hr>
        <div class="table-responsive">
            <div class="list-group">
                <div class="list-group-item list-group-item-dark">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">№</div>
                        <div class="col-md-2 text-center">Дата заказа</div>
                        <div class="col-md-2 text-center">Дата Доставки</div>
                        <div class="col-md-3 text-center">Пользователь</div>
                        <div class="col-md-2 text-center">Статус</div>
                        <div class="col-md-1 text-center">Сумма</div>
                        <div class="col-md-1 text-center">Детали</div>
                    </div>
                </div>
                <?php 
                $orders = $this->dbh->GetAllOrdersWithStatus('Доставлен');
                // echo var_dump($orders);
                // echo "\n";
                // echo var_dump($_SESSION['nonPaidOrders']);
                for($i=0;$i<count($orders);$i++):?>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center"> #<?= $orders[$i]['id'] ?></div>
                            <div class="col-md-2 text-center"><?= $orders[$i]['order_date'] ?></div>
                            <div class="col-md-2 text-center"><?= $orders[$i]['endDate'] ?></div>
                            <div class="col-md-3 text-center"><?= $orders[$i]['login'].' ('.$orders[$i]['last_name'].' '.$orders[$i]['first_name'].')' ?></div>
                            <div class="col-md-2 text-center "><?= $orders[$i]['status'] ?></div>
                            <div class="col-md-1 text-center"><?= $orders[$i]['total_price'] ?></div>
                            <div class="col-md-1 text-center">
                                <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#orderDetails<?= $orders[$i]['id'] ?>" role="button" aria-expanded="false" aria-controls="orderDetails<?= $orders[$i]['id'] ?>">
                                <i class="fa-solid fa-circle-info"></i>
                                </a>
                            </div>
                            <div class="collapse" id="orderDetails<?= $orders[$i]['id'] ?>">
                                <div class="card card-body">
                                    <div class='row'>
                                    <h5 class="mb-4 col-md-9">Продукты в Заказе</h5>
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

<?php
    }
    
    private function ConformDelivery($OrderID){
        $order = $this->dbh->getOrderById($OrderID);
        $this->dbh->changeOrderStatus($OrderID,"Доставлен");
    }
}

(new adminPanel())->showPage();