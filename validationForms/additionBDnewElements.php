<?php

require_once "../common/Page.php";
use common\Page;
use common\SupportPage;

class index extends SupportPage
{
    protected function processForm() : void
    {
        switch ($_REQUEST['form']) 
        {
            case 'brand':
                $this->addBrand();
                break;
            case 'category':
                $this->addCategory();
                break;
            case 'product':
                $this->addProduct();
                break;
            case 'comment':
                $this->addComment();
                break;
            case 'ProductToCart':
                $this->addProductToCart();
                break;
            case 'RemProductToCart':
                $this->RemoveFromCart();
                break;

            default:
                break;
        }
        $returnUrl = $_SERVER['HTTP_REFERER'];
        header("Location: " . $returnUrl);
        exit();
    }


    private function RemoveFromCart(){
        $id = $_POST['id'];
        $this->dbh->removeFromCart($id,$_SESSION['user']['id']);
    }

    private function addComment(){
        $rating = $_POST['rating'];
        $text = $_POST['comment'];
        $product_id = $_POST['product_id'];
        $user_id = $_POST['user_id'];
        $reviewID = $_POST['edit'];

        if(isset($_POST['edit']) && !empty($_POST['edit'])){
            $this->dbh->updateReview($reviewID, $rating, $text);
        }
        else{
            $this->dbh->addReview($user_id, $product_id, $rating, $text);
        }
    }
    private function addBrand()
    {
        $name = $_POST['name'];
        $this->dbh->addBrand($name);
    }

    private function addCategory()
    {
        $name = $_POST['name'];
        $this->dbh->addCategory($name);
    }
    private function addProduct()
    {
        $name = $_POST['name'];
        $brand_id = $_POST['brand_id'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        if(!empty($_POST['description'])){
            $description = $_POST['description'];
        }
        else{
            $description = null;
        }

        // Загрузка изображения продукта, если оно было выбрано
        if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == UPLOAD_ERR_OK) {
            // Изображение было загружено с компьютера
            $tmp_name = $_FILES['productImage']['tmp_name'];
            $name_parts = pathinfo($_FILES['productImage']['name']);
            $ext = $name_parts['extension'];
            $filename = uniqid() . '.' . $ext;
            $target_dir = "../productImages/";
            $target_path = $target_dir . $filename;
            move_uploaded_file($tmp_name, $target_path);
            $image_url = $target_path; // Путь к загруженному изображению
        } elseif (isset($_POST['imageUrl'])) {
            // Изображение передано по ссылке
            $image_url = $_POST['imageUrl'];
            $name_parts = pathinfo($image_url);
            $ext = $name_parts['extension'];
            $filename = uniqid() . '.' . $ext;
            $target_dir = "../productImages/";
            $target_path = $target_dir . $filename;
            file_put_contents($target_path, file_get_contents($image_url));
        } else {
            // Изображение не было загружено
            $filename = null;
        }

        $this->dbh->addProduct($name,$brand_id,$category_id,$filename,$price,$description);
    }

    private function addProductToCart(){
        $id = $_POST['id'];
        if(!empty($_POST['quantity'])){
            $amount = $_POST['quantity'];
        } 
        else $amount = 1;
        $this->dbh->addToCart($id,$_SESSION['user']['id'],$amount);
    }
    
}

(new index())->showPage();

/*
<?php
$categories = $dbHelper->getAllCategories();
for ($i=0;$i<count($categories);$i++):
?>
    <div class="input-checkbox">
        <input type="checkbox" id="category-<?php echo $i?>">
        <label for="category-<?php echo $i?>">
            <span></span>
            <?echo $categories[$i]['Name']?>
            <small>(120)</small>
        </label>
    </div>
<?php endfor;?>
*/