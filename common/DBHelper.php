<?php

namespace common;

use Exception;
use mysqli;

class DbHelper
{
    private const dbName = "onlineshopdb";
    private static ?DbHelper $instance = null;
    private $conn;

    public static function getInstance($host = null, $port = null, $user = null, $pass = null): DbHelper
    {
        if (self::$instance === null) self::$instance = new DbHelper($host, $port, $user, $pass);
        return self::$instance;
    }

    private function __construct($host, $port, $user, $pass)
    {
        $this->conn = new mysqli();
        $this->conn->connect(hostname: $host,username: $user,password: $pass,database: self::dbName,port: $port);
    }

    public function getUserPassword(string $user): ?string
    {
        $sql = "SELECT password FROM user WHERE login = ?";
        $this->conn->begin_transaction();
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $this->conn->commit();
        return ($row === null) ? $row : $row['password'];
    }
    public function getUser(string $login, string $password): ?array {
        $sql = "SELECT * FROM user WHERE login = ? AND password = ?";
        $this->conn->begin_transaction();
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $login, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $user;
    }
    
    public function saveUser(string $login, string $password, ?string $email, ?string $phone, string $firstName, string $lastName): bool
    {
        $sql = "INSERT INTO `user` (login, password, email, phone, first_name, last_name) VALUES(?, ?, ?, ?, ?, ?)";
try {
    $this->conn->begin_transaction();
    $stmt = $this->conn->prepare($sql);

    // Создание временных переменных для параметров
    $loginParam = $login;
    $passwordParam = $password;
    $emailParam = $email !== null ? $email : NULL;
    $phoneParam = $phone !== null ? $phone : NULL;
    $firstNameParam = $firstName !== null ? $firstName : NULL;
    $lastNameParam = $lastName !== null ? $lastName : NULL;

    // Привязка временных переменных к параметрам
    $stmt->bind_param("ssssss", $loginParam, $passwordParam, $emailParam, $phoneParam, $firstNameParam, $lastNameParam);

    if (!$stmt->execute()) throw new Exception("Ошибка добавления пользователя");

        $this->conn->commit();
        return true;
    } catch (\Throwable $ex){
        $this->conn->rollback();
        return false;
    }
    }

    public function addBrand(string $name): void {
        $sql = "INSERT INTO brand (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
        $this->conn->commit();
    }
    

    public function addProduct(string $name, int $brand_id, int $category_id, ?string $img, float $price, ?string $description = null): void {

        if ($description !== null && !empty($description)) {
            $sql = "INSERT INTO product (name, img, brand_id, category_id, current_price, description) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssiiis", $name, $img, $brand_id, $category_id, $price, $description);
        } else {
            $sql = "INSERT INTO product (name, img, brand_id, category_id, current_price) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssiii", $name, $img, $brand_id, $category_id, $price);
        }

        $stmt->execute();
        $stmt->close();
        $this->conn->commit();
    }
    
    
    public function addCategory(string $name): void {
        $sql = "INSERT INTO category (name) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
        $this->conn->commit();
    }

    public function addReview(int $user_id, int $product_id, int $rate, string $text): void {
        $sql = "INSERT INTO review (user_id, product_id, rate, text) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiis", $user_id, $product_id, $rate, $text);
        $stmt->execute();
        $stmt->close();
        $this->conn->commit();
    }

    public function updateReview(int $review_id, int $rate, string $text): void {

        $sql = "UPDATE review SET rate = ?, text = ?, date = NOW()  WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isi", $rate, $text, $review_id);
        $stmt->execute();
        $stmt->close();
        $this->conn->commit();
    }

    private function _getDataFromDB(string $sql): array{
        $this->conn->begin_transaction();
        $result = $this->conn->query($sql);
        $data = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }

        $this->conn->commit();
        return $data;
    }

    public function getAllBrands(): array {
        $sql = "SELECT * FROM brand";
        $this->conn->begin_transaction();
        $result = $this->conn->query($sql);
        $brands = $result->fetch_all(MYSQLI_ASSOC);
        $this->conn->commit();
        return $brands;
    }
        
    public function getAllCategories(): array {
        $sql = "SELECT * FROM category";
        $this->conn->begin_transaction();
        $result = $this->conn->query($sql);
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        $this->conn->commit();
        return $categories;
    }

    public function getCategoriesWithProductCount(){
        $sql = "SELECT category.*, COUNT(product.id) AS product_count 
                FROM category 
                LEFT JOIN product ON product.category_id = category.id 
                GROUP BY category.id";

        return $this->_getDataFromDB($sql);
    }

    public function getBrandsWithProductCount(){
        $sql = "SELECT brand.*, COUNT(product.id) AS product_count 
                FROM brand 
                LEFT JOIN product ON product.brand_id = brand.id 
                GROUP BY brand.id";

        return $this->_getDataFromDB($sql);
    }

    public function getReviewFromUserOnChoosenProduct($product , $user){
        $sql = "SELECT * FROM review WHERE product_id = ? AND user_id = ?;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $product,$user);
        $stmt->execute();
        $result = $stmt->get_result();
        $review = $result->fetch_assoc();
        $stmt->close();
    
        return $review;
    }

    public function getAllReviewsOnTheProduct($product){
        $sql = "SELECT * FROM review WHERE product_id = ?;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $products;
    }
    

    public function getProductByID($id){
        $sql = "SELECT product.*, category.name AS category, brand.name AS brand, AVG(review.rate) AS rating
        FROM product
        LEFT JOIN review ON product.id = review.product_id
        LEFT JOIN category ON product.category_id = category.id
        LEFT JOIN brand ON product.brand_id = brand.id
        WHERE product.id = $id;";
        return $this->_getDataFromDB($sql);
    }
    
    public function getProductsRatingsGroups($ProudctId,$rate){
        $sql = "SELECT COUNT(*) AS count FROM review WHERE product_id = $ProudctId AND rate = $rate;";
        return $this->_getDataFromDB($sql) ;
    }

    public function getProductsRatings($ProductId) {
        $sql = "SELECT review.*, user.first_name AS firstname, user.last_name AS lastname 
                FROM review 
                INNER JOIN user ON review.user_id = user.id
                WHERE review.product_id = $ProductId;";
        return $this->_getDataFromDB($sql);
    }

    public function getMinAndMaxPrice(){
        $sql = "SELECT MIN(current_price) AS min_price, MAX(current_price) AS max_price FROM product;";
        return $this->_getDataFromDB($sql);
    }

    //
    //Тут уже продумано
    //

    public function addToCart(int $product_id, int $user_id, int $amount): void {
        $sql = "INSERT INTO product_in_cart (product_id, user_id, amount) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $product_id, $user_id, $amount);
        $stmt->execute();
        $stmt->close();
        $this->conn->commit();
    }
    
    public function removeFromCart(int $product_id, int $user_id): void {
        $sql = "DELETE FROM product_in_cart WHERE product_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $this->conn->commit();
    }
    
    public function addToWishlist(int $product_id, int $user_id): void {
        $sql = "INSERT INTO product_in_wishlist (product_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $this->conn->commit();
    }
    
    public function removeFromWishlist(int $user_id, int $product_id): void {
        $sql = "DELETE FROM product_in_wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $stmt->close();
        $this->conn->commit();
    }

    public function getCartProducts(int $user_id): array {
        $sql = "SELECT product.*, amount FROM product
                INNER JOIN product_in_cart ON product.id = product_in_cart.product_id
                WHERE product_in_cart.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $products;
    }

    public function getWishlistProductsWithRating(int $user_id): array {
        $sql = "SELECT product.*, AVG(review.rate) AS rating
                FROM product
                LEFT JOIN review ON product.id = review.product_id
                INNER JOIN product_in_wishlist ON product.id = product_in_wishlist.product_id
                WHERE product_in_wishlist.user_id = ?
                GROUP BY product.id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $products;
    }

    public function calculateSubtotal(array $products): float {
        $subtotal = 0;
        foreach ($products as $product) {
            $subtotal += $product['current_price'] * $product['amount'];
        }
        return $subtotal;
    }

    function isProductInCart($userId, $productId) {
        $sql = "SELECT COUNT(*) FROM product_in_cart WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    function isProductInWishList($userId, $productId) {
        $sql = "SELECT COUNT(*) FROM product_in_wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

 


    public function getSearchProducts($searchRequest, $category = 0) {
        $searchRequest = "%".$searchRequest."%";
        $sql = "SELECT product.*, category.name AS category, brand.name AS brand, AVG(review.rate) AS rating
                FROM product
                LEFT JOIN review ON product.id = review.product_id
                LEFT JOIN category ON product.category_id = category.id
                LEFT JOIN brand ON product.brand_id = brand.id
                WHERE ";
    
        if ($category != 0) {
            $sql .= "product.category_id = ? AND ";
        }
    
        // Добавляем условие для поискового запроса
        $sql .= "(product.name LIKE ? OR product.description LIKE ?) ";
    
        $sql .= " GROUP BY product.id;";
        $stmt = $this->conn->prepare($sql);
    
        if ($category != 0) {
            $stmt->bind_param("iss", $category, $searchRequest, $searchRequest);
        } else {
            $stmt->bind_param("ss", $searchRequest, $searchRequest);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $products;
    }        

    public function addOrder(int $user_id, string $status, string $phone, string $email, string $comment): int {
        $this->conn->begin_transaction(); // Начало транзакции
    
        try {
            // Вставка нового заказа
            $sql = "INSERT INTO orrder (user_id, status, phone, email, comment) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("issss", $user_id, $status, $phone, $email, $comment);
            $stmt->execute();
            $order_id = $stmt->insert_id; // Получение ID созданного заказа
            $stmt->close();
    
            // Перенос продуктов из корзины в заказ
            $sql = "INSERT INTO product_in_order (product_id, order_id, amount) SELECT product_id, ?, amount FROM product_in_cart WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $order_id, $user_id);
            $stmt->execute();
            $stmt->close();
    
            // Удаление продуктов из корзины
            $sql = "DELETE FROM product_in_cart WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
    
            $this->conn->commit(); // Фиксация транзакции

            return $order_id;
        } catch (Exception $e) {
            $this->conn->rollback(); // Ошибка, откат транзакции
            throw $e;
        }
    }

    
    function changeOrderStatus(int $order_id, string $new_status) {
        $this->conn->begin_transaction(); // Начало транзакции
    
        $sql = "UPDATE orrder SET status = ?, endDate = CURRENT_TIMESTAMP WHERE id = ?";
    
        if ($new_status == "Отменен" || $new_status == "Доставлен") {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $new_status, $order_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Обновление статуса заказа без установки endDate
            $stmt = $this->conn->prepare("UPDATE orrder SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $order_id);
            $stmt->execute();
            $stmt->close();
        }
    
        $this->conn->commit();
    }

    public function isOrderExists($order_id) {
        $sql = "SELECT COUNT(*) FROM orrder WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    public function getOrderById(int $order_id) {
        // Запрос для получения заказа по его ID
        $sql = "SELECT orrder.*, SUM(price_history.price * product_in_order.amount) AS total_price
                FROM orrder
                INNER JOIN product_in_order ON orrder.id = product_in_order.order_id
                INNER JOIN product ON product_in_order.product_id = product.id
                LEFT JOIN price_history ON product.id = price_history.product_id 
                WHERE orrder.id  = ? 
                AND orrder.order_date >= price_history.date_from
                AND (orrder.order_date <= price_history.date_to OR price_history.date_to IS NULL)
                GROUP BY orrder.id;";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
    
        return $order;
    }

    

    public function GetAllUsersOrders($user_id) {

        $sql = "SELECT orrder.*, SUM(price_history.price * product_in_order.amount) AS total_price
                FROM orrder
                INNER JOIN product_in_order ON orrder.id = product_in_order.order_id
                INNER JOIN product ON product_in_order.product_id = product.id
                LEFT JOIN price_history ON product.id = price_history.product_id 
                WHERE orrder.user_id  = ? 
                AND orrder.order_date >= price_history.date_from
                AND (orrder.order_date <= price_history.date_to OR price_history.date_to IS NULL)
                GROUP BY orrder.id
                ORDER BY orrder.order_date DESC;";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        return $orders;
    }

    public function GetAllOrdersWithStatus($status) {

        $sql = "SELECT orrder.*, user.first_name AS first_name, user.last_name AS last_name, user.login AS login, SUM(price_history.price * product_in_order.amount) AS total_price
        FROM orrder
        INNER JOIN product_in_order ON orrder.id = product_in_order.order_id
        INNER JOIN product ON product_in_order.product_id = product.id
        LEFT JOIN price_history ON product.id = price_history.product_id 
        INNER JOIN user ON orrder.user_id = user.id
        WHERE orrder.status = ?
        AND orrder.order_date >= price_history.date_from
        AND (orrder.order_date <= price_history.date_to OR price_history.date_to IS NULL)
        GROUP BY orrder.id
        ORDER BY orrder.order_date DESC;";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    
        return $orders;
    }

    public function GetProductsInSpecificOrder($order_id){
        $sql = "SELECT product.*, product_in_order.amount AS amount, (price_history.price * product_in_order.amount) AS priceForPos
        FROM orrder
        INNER JOIN product_in_order ON orrder.id = product_in_order.order_id
        INNER JOIN product ON product_in_order.product_id = product.id
        LEFT JOIN price_history ON product.id = price_history.product_id 
        WHERE orrder.id = ?
          AND orrder.order_date >= price_history.date_from
          AND (orrder.order_date <= price_history.date_to OR price_history.date_to IS NULL);";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $products;

    }

    public function calculateProfitForPeriod(string $start_date = null, string $end_date = null, $status)
{
    $sql = "SELECT SUM(product_in_order.amount * price_history.price) AS total_profit
            FROM product_in_order
            JOIN orrder ON orrder.id = product_in_order.order_id
            JOIN product ON product.id = product_in_order.product_id
            JOIN price_history ON price_history.product_id = product.id
            WHERE orrder.order_date >= ?
              AND orrder.order_date <= ?
              AND price_history.date_from <= orrder.order_date
              AND (price_history.date_to IS NULL OR price_history.date_to >= orrder.order_date)
              AND orrder.status = ?;";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sss", $start_date, $end_date, $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['total_profit'];

    
}

public function calculateProfit($status)
{
    $sql = "SELECT SUM(product_in_order.amount * price_history.price) AS total_profit
            FROM product_in_order
            JOIN orrder ON orrder.id = product_in_order.order_id
            JOIN product ON product.id = product_in_order.product_id
            JOIN price_history ON price_history.product_id = product.id
            WHERE orrder.status = ?;";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['total_profit'];
}

public function getProductsWithRatingFilterized(
    $orderType = null,
    array $categories = null,
    array $brands = null,
    $minPrice = null,
    $maxPrice = null
) {
    $sql = "SELECT product.*, category.name AS category, brand.name AS brand, AVG(review.rate) AS rating
        FROM product
        LEFT JOIN review ON product.id = review.product_id
        LEFT JOIN category ON product.category_id = category.id
        LEFT JOIN brand ON product.brand_id = brand.id ";

    $filters = [];
    if ($minPrice !== null) {
        $filters[] = "current_price >= $minPrice";
    }
    if ($maxPrice !== null) {
        $filters[] = "current_price <= $maxPrice";
    }
    if (!empty($categories)) {
        $categoryIds = implode(",", $categories);
        $filters[] = "product.category_id IN ($categoryIds)";
    }
    if (!empty($brands)) {
        $brandsIds = implode(",", $brands);
        $filters[] = "product.brand_id IN ($brandsIds)";
    }
    if (!empty($filters)) {
        $sql .= " WHERE " . implode(" AND ", $filters);
    }

    $sql .= " GROUP BY product.id ";

    switch ($orderType) {
        case "1":
            $sql .= " ORDER BY product.name DESC";
            break;
        case "2":
            $sql .= " ORDER BY product.current_price ASC";
            break;
        case "3":
            $sql .= " ORDER BY product.current_price DESC";
            break;
        case "4":
            $sql .= " ORDER BY rating DESC";
            break;
        default:
            $sql .= " ORDER BY product.name ASC";
            break;
    }
    $sql .= ";";

    return $this->_getDataFromDB($sql) ;
}

function getBrandsInCategories(array $categoryIds) {

    $categoryIdsString = implode(',', $categoryIds); // Преобразуем массив в строку

    $sql = "SELECT DISTINCT brand.id
            FROM brand
            JOIN product ON product.brand_id = brand.id
            JOIN category ON category.id = product.category_id
            WHERE category.id IN ($categoryIdsString)";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $brands = array();
    while ($row = $result->fetch_assoc()) {
        $brands[] = $row['id'];
    }
    
    $stmt->close();
    
    return $brands;
}

function updateUserProfile($userId, $name, $surname, $email, $phone) {
    // Формируем список полей и значений для обновления
    $fields = array();
    $params = array();

    if (!empty($name)) {
        $fields[] = "first_name = ?";
        $params[] = $name;
    }

    if (!empty($surname)) {
        $fields[] = "last_name = ?";
        $params[] = $surname;
    }

    if (!empty($email)) {
        $fields[] = "email = ?";
        $params[] = $email;
    }

    if (!empty($phone)) {
        $fields[] = "phone = ?";
        $params[] = $phone;
    }

    try{
        $params[] = $userId; // Добавляем $userId в конец массива $params

        $sql = "UPDATE user SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $paramTypes = str_repeat("s", count($params)-1) . "i";
    
        $stmt->bind_param($paramTypes, ...$params); // Распаковываем $params
        $stmt->execute();
        $stmt->close();
    }
    catch(Exception){
        echo "Не работает ничего";
    }

}

public function updateProductPrice(int $productId, float $newPrice): void {
    $sql = "UPDATE product SET current_price = ? WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("di", $newPrice, $productId);
    $stmt->execute();
    $stmt->close();
}

public function getCategoryWithHighestAverageRating()
{
    // $sql = "SELECT category_id, category.name AS name, AVG(rate) AS avg_rating
    //         FROM (
    //             SELECT p.category_id, r.rate
    //             FROM product AS p
    //             INNER JOIN review AS r ON p.id = r.product_id
    //             INNER JOIN category AS c ON p.category_id = c.id
    //         ) AS subquery
    //         GROUP BY category_id
    //         ORDER BY avg_rating DESC
    //         LIMIT 4";

    $sql = "SELECT c.name AS name, AVG(r.rate) AS avg_rating
        FROM product p
        INNER JOIN review r ON p.id = r.product_id
        INNER JOIN category c ON p.category_id = c.id
        GROUP BY p.category_id
        ORDER BY avg_rating DESC
        LIMIT 4";

    return $this->_getDataFromDB($sql);
}

}