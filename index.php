<?php

require_once "common/Page.php";
require_once "common/PhpHelper.php";
use common\Page;
use common\PhpHelper;

class index extends Page
{
    private $products;

    protected function showContent()
    {
      ?>

<div class="container">
  <div class="row mt-4">
    <?php $this->showSideBar();?>
    <div class="col-md-9">
      <!---->
<div class="row mb-4">
  <div class="col-md-6">
    <div class="input-group">
      <span class="input-group-text">Сортировка по</span>
      <select class="form-select " id="sortSelect">
        <option selected>Названию (А-Я)</option>
        <option value="1">Названию (Я-А)</option>
        <option value="2">Цене (по возрастанию)</option>
        <option value="3">Цене (по убыванию)</option>
        <option value="4">Рэйтингу</option>
      </select>
    </div>
  </div>
  <!-- <div class="col-md-3">
    <div class="input-group">
      <span class="input-group-text">Показывать по</span>
      <select class="form-select">
        <option selected>12</option>
        <option value="1">24</option>
        <option value="2">36</option>
        <option value="3">Все</option>
      </select>
    </div>
  </div> -->
</div>
      <!---->
      <div class ="row " id="product-grid">
        <?php
        
        if(isset($_REQUEST['search'])){

          $category = $_POST['search-category'];
          $searchText = $_POST['search-input'];
          $this->products = $this->dbh->getSearchProducts($searchText,$category);
        }
        else{
          $this->products = $this->dbh->getProductsWithRatingFilterized();
        }
        
        for($i = 0; $i <count($this->products); $i++) 
        {
            GenerateProductHolder($this->products[$i],$this->dbh);
        }
        ?>
    </div>
  </div>
</div>
</div>
      <?php
      
    }
    private function showSideBar(){
      ?>

      <div class="col-md-3">
      <div class="category-filter">
        <h4>Категории</h4>
        <ul class="list-unstyled">
          <?php
          $categories = $this->dbh->getCategoriesWithProductCount();
          for ($i=0;$i<count($categories);$i++):
          ?>
            <li>
              <div class="form-check">
                <input class="form-check-input CategoryCheckbox" type="checkbox" id="category-<?= $categories[$i]['id']?>">
                <label class="form-check-label" for="category-<?=$categories[$i]['id']?>"><?=$categories[$i]['name']?> <small><?= $categories[$i]['product_count']?></small></label>
              </div>
            </li>
          <?php endfor;?>
        </ul>
      </div>
      

    <hr>


      <h4 class="card-title mb-3">Стоимость</h4>
      <div class="slider-styled my-3" id="slider-round"></div>
      <div class="d-flex justify-content-between">
        <?php $prices = $this->dbh->getMinAndMaxPrice()?>
        <input type="text" id="input-0" class="form-control w-50 mr-2" value="<?= $prices[0]['min_price']?>" />
        <input type="text" id="input-1" class="form-control w-50 ml-2" value="<?= $prices[0]['max_price']?>" />
      </div>


    <hr>

    
    <div class="brand-filter" id="brand-filter">
        <h4>Брэнды</h4>
        <ul class="list-unstyled ">
          <?php
          $brands = $this->dbh->getBrandsWithProductCount();
          for ($i=0;$i<count($brands);$i++):
          ?>
            <li>
              <div class="form-check">
                <input class="form-check-input brandsCheckbox" type="checkbox" id="brand-<?= $brands[$i]['id']?>">
                <label class="form-check-label" for="brand-<?=$brands[$i]['id']?>"><?=$brands[$i]['name']?> <small><?= $brands[$i]['product_count']?></small></label>
              </div>
            </li>
          <?php endfor;?>
        </ul>
    </div>

    
      <hr>
    </div>
      <?php
    }

    
    
}

(new index())->showPage();