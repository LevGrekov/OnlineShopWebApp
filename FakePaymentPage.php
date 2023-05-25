<?php


require_once "common/Page.php";
require_once "common/PhpHelper.php";
use common\Page;
use common\PhpHelper;

class FakePayment extends Page
{
    private $orderID = null; 

    public function __construct(){
        
        parent::__construct();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['csrf_token']) && isset($_SESSION['csrf_token'])) {
                if($_POST['csrf_token'] == $_SESSION['csrf_token']){

                    $this->AddNewOrder();
                    unset($_SESSION['csrf_token']);
                    $_SESSION['nonPaidOrders'][$this->orderID] = 1;
                }
            }
            if (isset($_POST['orderID'])){
                $this->ProcessPayment(); 
            }
            if (isset($_POST['returnToPayment'])){
                $this->orderID = $_POST['returnToPayment'];
            }
        }
        
    }
    
    protected function showContent()
    {
        $this->ShowPaymentBlock();
    }


    private function AddNewOrder(){
        $email = htmlspecialchars($_POST["email"]);
        $phone = htmlspecialchars($_POST["phone"]);
        $comment = htmlspecialchars($_POST["comment"]);
        $userId = $_POST['user_id'];
        $this->orderID = $this->dbh->addOrder($userId,"Ожидает Оплаты",$phone,$email,$comment);
    }

    private function ProcessPayment(){
        $orderID = $_POST['orderID'];
        $this->dbh->changeOrderStatus($orderID,"В Доставке");
        unset($_SESSION['nonPaidOrders'][$this->orderID]);
        header("Location: /account.php");
        exit();    
    }

    private function ShowPaymentBlock(){
        ?>
      <div class="container col-md-7 my-4">
    <div class="alert alert-danger">
        <strong>Осторожно:</strong> не вводите сюда свои данные. Это демонстрационный пример. Ничего оплачивать не нужно. Нажатие на кнопку "Оплатить" считается за оплату заказа!
    </div>

    <div class="card p-4">
        <h1 class="mb-4">Оплата товара</h1>

        <form method="post" action="FakePaymentPage.php">
            <div class="form-group">
                <label for="cardNumber">Номер карты</label>
                <input type="text" class="form-control" id="cardNumber" placeholder="Введите номер карты">
            </div>

            <div class="form-group">
                <label for="expiryDate">Срок действия</label>
                <input type="text" class="form-control" id="expiryDate" placeholder="ММ/ГГ">
            </div>

            <div class="form-group">
                <label for="cvv">CVV-код</label>
                <input type="text" class="form-control" id="cvv" placeholder="Введите CVV-код">
            </div>

            <div class="form-group">
                <label for="cardHolder">Имя владельца карты</label>
                <input type="text" class="form-control" id="cardHolder" placeholder="Введите имя владельца карты">
            </div>

            <input type="hidden" name="orderID" value="<?php echo $this->orderID?>">
            <?php
            $theOrder = $this->dbh->getOrderById($this->orderID);
            ?>
            <button type="submit" class="btn btn-primary mt-3">Оплатить <?=$theOrder['total_price']?></button>
        </form>
    </div>
</div>

              <?php
            }
    }

(new FakePayment())->ShowPage();