// Получаем элементы, на которые будем навешивать события

const sortSelect = document.querySelector('#sortSelect');
const categoryCheckboxes = document.querySelectorAll('.CategoryCheckbox');
const brandCheckboxes = document.querySelectorAll('.brandsCheckbox');
const priceRangeSliderStart = document.querySelector('#input-0');
const priceRangeSliderEnd = document.querySelector('#input-1');

// Функция, которая будет вызываться при изменении параметров фильтрации
function updateProducts() {
  // Получаем выбранные опции
  const sortOption = sortSelect.value;
  const categoryOptions = Array.from(categoryCheckboxes).filter(checkbox => checkbox.checked).map(checkbox => checkbox.id.split('-')[1]);
  const brandOptions = Array.from(brandCheckboxes).filter(checkbox => checkbox.checked).map(checkbox => checkbox.id.split('-')[1]);
  const minPrice = parseInt(priceRangeSliderStart.value);
  const maxPrice = parseInt(priceRangeSliderEnd.value);

  // Отправляем AJAX запрос на сервер с выбранными опциями
  const xhr = new XMLHttpRequest();
  console.log(`brandOptions: ${brandOptions} ;categoryOptions: ${categoryOptions}`);
  
  xhr.open('GET', `../validationForms/AJAXvalidator.php?sort=${sortOption}&categories=${categoryOptions}&brands=${brandOptions}&min_price=${minPrice}&max_price=${maxPrice}&state=1`, true);
  xhr.onload = function() {
    if (xhr.status === 200) {
      // Обновляем товарную сетку с полученными данными
      const productGrid = document.querySelector('#product-grid');
      productGrid.innerHTML = xhr.responseText;
    }
  };
  xhr.send();
}



// Навешиваем обработчики событий на элементы
sortSelect.addEventListener('change', updateProducts);
categoryCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateProducts));
brandCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateProducts));
priceRangeSliderStart.addEventListener('input', updateProducts);
priceRangeSliderEnd.addEventListener('input', updateProducts);



function toggleCartStatus(productId) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "validationForms/AJAXvalidator.php?state=2", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      // Получение данных от сервера
      var response = JSON.parse(xhr.responseText);

      // Изменение иконки и функционала кнопки
      
      var cartIcon = document.getElementById("cartIcon" + productId);
      if (response.inCart) {
        cartIcon.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i>'; // HTML-код иконки "fa-shopping-cart"
      } else {
        cartIcon.innerHTML = '<i class="fa-solid fa-cart-plus " aria-hidden="true"></i>'; // HTML-код иконки "fa-cart-xmark"
      }
      
      showCart();
      
      console.log("Функционал кнопки изменен");
    }
  };
  xhr.send("productId=" + productId);
  event.preventDefault();
  
}

function toggleWishlistStatus(productId) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "validationForms/AJAXvalidator.php?state=5", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      // Получение данных от сервера
      var response = JSON.parse(xhr.responseText);

      // Изменение иконки и функционала кнопки
      
      var wishlistIcon = document.getElementById("wishlistIcon" + productId);
      if (response.inWishlist) {
        wishlistIcon.innerHTML = '<i class="fa-solid fa-heart-circle-minus" aria-hidden="true"></i>'; // HTML-код иконки "fa-heart"
      } else {
        wishlistIcon.innerHTML = '<i class="fa-regular fa-heart" aria-hidden="true"></i>'; // HTML-код иконки "fa-heart"
      }
      reloadWishlistBlock();
      console.log("Функционал кнопки изменен");
    }
  };
  xhr.send("productId=" + productId);
  event.preventDefault();
}

function showCart() {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "validationForms/AJAXvalidator.php?state=3", true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      var cartContent = xhr.responseText;
      // Обновление содержимого модального окна с помощью полученных данных
      var modalBody = document.querySelector(".modal-body");
      modalBody.innerHTML = cartContent;
      console.log("Корзина отображена");
    }
  };
  xhr.send();
}

function deleteProduct(productId) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "validationForms/AJAXvalidator.php?state=4", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      // Обработка успешного удаления продукта
      console.log("Продукт удален из базы данных");
      
      // Вызов функции для обновления модального окна корзины
      showCart();
      updateProducts();
    }
  };
  xhr.send("productId=" + productId);
  event.preventDefault();
  
}

function reloadWishlistBlock() {
  var xhr = new XMLHttpRequest();
  xhr.open("GET", "validationForms/AJAXvalidator.php?state=6", true);
  xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
          // Обновление содержимого блока
          document.getElementById("wishlistBlock").innerHTML = xhr.responseText;
         
      }
  };
  xhr.send();
}

function calculateProfit() {
  var startDate = document.getElementById("start_date").value;
  var endDate = document.getElementById("end_date").value;

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "validationForms/AJAXvalidator.php?state=7", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
        var result = JSON.parse(xhr.responseText);
        document.getElementById("InComeResult").innerHTML = result;
    }
  };
  xhr.send("start_date=" + startDate + "&end_date=" + endDate);
}
