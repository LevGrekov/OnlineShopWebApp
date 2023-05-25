function changeTab(evt, tabName) {
    var i, tabContent, tabLinks;
    tabContent = document.getElementsByClassName("tab-pane");
    for (i = 0; i < tabContent.length; i++) {
      tabContent[i].style.display = "none";
    }
    tabLinks = document.getElementsByClassName("nav-link");
    for (i = 0; i < tabLinks.length; i++) {
      tabLinks[i].className = tabLinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
  }

  //Скрипты для Админ Панели. Где radio buttons Для загрузки изображения товара
  const imageSourceFileInput = document.getElementById('imageSourceFileInput');
  const imageSourceUrlInput = document.getElementById('imageSourceUrlInput');
  const imageSourceFile = document.getElementById('imageSourceFile');
  const imageSourceUrl = document.getElementById('imageSourceUrl');

function showFileInput() {
      imageSourceFileInput.style.display = 'block';
      imageSourceUrlInput.style.display = 'none';
  }

function showUrlInput() {
      imageSourceFileInput.style.display = 'none';
      imageSourceUrlInput.style.display = 'block';
  }

  imageSourceFile.addEventListener('change', showFileInput);
  imageSourceUrl.addEventListener('change', showUrlInput);
  showUrlInput();


  //Для выбора рейтинга 

  window.addEventListener('DOMContentLoaded', (event) => {
    const ratingInputs = document.querySelectorAll('.rating input');

    ratingInputs.forEach((input) => {
      input.addEventListener('click', () => {
        const currentRating = Number(input.value);

        ratingInputs.forEach((input, index) => {
          const label = input.nextElementSibling;
          if (index < currentRating) {
            label.style.color = 'gold';
          } else {
            label.style.color = 'gray';
          }
        });
      });
    });
  });


  // Отображение блока по центру экрана и его исчезновение через пару секунд
function showMessage() {
  var message = document.getElementById('WelComemessage');
  message.style.display = 'block';

  setTimeout(function() {
    message.style.display = 'none';
  }, 2000);
}

// Вызов функции после загрузки страницы
window.onload = function() {
  showMessage();
};

// Для выпадающей Карзины
function toggleCart(event) {
  event.preventDefault(); // Предотвращаем переход по ссылке
  var cartWindow = document.getElementById('cartWindow');
  if (cartWindow.style.display === 'none') {
      cartWindow.style.display = 'block';
  } else {
      cartWindow.style.display = 'none';
  }
}

// Для закрытия всех колапсов


var collapses = document.querySelectorAll('.collapse');

    function hideCollapses() {
        for (var i = 0; i < collapses.length; i++) {
            collapses[i].classList.remove('show');
            collapses[i].style.height = null;
        }
    }

    function showCollapse(id) {
        hideCollapses();
        var collapse = document.getElementById(id);
        collapse.classList.add('show');
        collapse.style.height = collapse.scrollHeight + 'px';
    }




