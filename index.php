<?php
date_default_timezone_set('Asia/Bishkek');

$db_host = 'localhost';
$db_name = 'okii_flower_db';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Вытаскиваем только те б/у букеты, которые получили статус 'одобрен'
    $stmt = $pdo->prepare("SELECT * FROM flowers WHERE is_used = 1 AND status = 'одобрен' ORDER BY id DESC");
    $stmt->execute();
    $usedFlowers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Если база не подключена, сайт просто продолжит работать, но без б/у блока
    $usedFlowers = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Цветочный Рай — Главная</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Стили для блока Вторые руки, чтобы гармонично вписались в дизайн */
        .used-flowers-section { padding: 50px 20px; max-width: 1200px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .used-flowers-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; margin-top: 25px; }
        .used-card { background: #fafdfa; border: 1px solid #e2ece3; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s; }
        .used-card:hover { transform: translateY(-5px); }
        .used-img-container { width: 100%; height: 240px; background: #eaeea422; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .used-img { width: 100%; height: 100%; object-fit: cover; }
        .used-content { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
        .used-badge { background: #e2ece3; color: #47824b; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; align-self: flex-start; margin-bottom: 10px; }
        .used-title { margin: 5px 0; color: #222; font-size: 18px; }
        .used-desc { color: #666; font-size: 14px; margin: 10px 0; line-height: 1.4; flex-grow: 1; }
        .used-price-box { font-size: 20px; font-weight: bold; color: #47824b; margin-bottom: 15px; text-align: left; }
    </style>
</head>
<body>

    <header class="main-header">
        <div class="logo">🌸 Цветочный Окии</div>
        <nav class="nav-menu">
            <a href="index.php" class="active">Главная</a>
            <a href="indoor.html">Комнатные растения</a>
            <a href="garden.html">Садовые цветы</a>
            <a href="decor.html">Декоративные растения</a>
            <a href="profile.php" style="background: #47824b; color: white; padding: 5px 15px; border-radius: 20px; margin-left: 15px;">Профиль / Вход</a>
        </nav>
    </header>

    <main class="content">
        <section class="welcome-banner">
            <h1>Здравствуйте! Добро пожаловать в «Окии» </h1>
            <p>Мы создаем не просто букеты, а дарим живые эмоции. Выберите готовый вариант из нашей коллекции или соберите свой неповторимый шедевр!</p>
        </section>

        <h2>Наши готовые варианты букетов</h2>

        <div class="tag-filters">
            <button class="filter-btn active" data-filter="all">Все букеты</button>
            <button class="filter-btn" data-filter="розы">Розы</button>
            <button class="filter-btn" data-filter="тюльпаны">Тюльпаны</button>
            <button class="filter-btn" data-filter="премиум">Премиум</button>
            <button class="filter-btn" data-filter="бюджет">До 5000 сом</button>
            <button class="filter-btn" data-filter="нежные">Нежные цвета</button>
        </div>
        
        <div class="catalog-grid">
            
            <div class="flower-card" data-tags="тюльпаны нежные">
                <img src="images/bouquet/1.jpg" alt="Букет Нежность">
                <div class="card-content">
                    <div class="card-tags">
                        <span class="card-tag">#тюльпаны</span>
                        <span class="card-tag">#нежные</span>
                    </div>
                    <h3>Букет «Нежность»</h3>
                    <p class="info">Цена: <span class="price">6500</span> сом</p>
                    <p class="description">Состав: 15 розовых тюльпанов, оформление в нежный крафт с нежно-розовой атласной лентой.</p>
                    <button class="buy-btn" data-bouquet="Букет «Нежность»" data-price="6500">Оформить заказ</button>
                </div>
            </div>

            <div class="flower-card" data-tags="розы бюджет">
                <img src="images/bouquet/2.jpg" alt="Букет Яркий день">
                <div class="card-content">
                    <div class="card-tags">
                        <span class="card-tag">#розы</span>
                        <span class="card-tag">#бюджет</span>
                    </div>
                    <h3>Букет «Яркий День»</h3>
                    <p class="info">Цена: <span class="price">4200</span> сом</p>
                    <p class="description">Состав: Смесь ярких гербер, желтых кустовых роз и пушистых веточек фисташки в стильной корейской упаковке.</p>
                    <button class="buy-btn" data-bouquet="Букет «Яркий День»" data-price="4200">Оформить заказ</button>
                </div>
            </div>

            <div class="flower-card" data-tags="нежные премиум">
                <img src="images/bouquet/3.jpg" alt="Букет Белое Облако">
                <div class="card-content">
                    <div class="card-tags">
                        <span class="card-tag">#нежные</span>
                        <span class="card-tag">#премиум</span>
                    </div>
                    <h3>Букет «Белое Облако»</h3>
                    <p class="info">Цена: <span class="price">7500</span> сом</p>
                    <p class="description">Состав: Пышные белые гортензии, веточки серебристого эвкалипта Синерия, упакованные в матовую кальку.</p>
                    <button class="buy-btn" data-bouquet="Букет «Белое Облако»" data-price="7500">Оформить заказ</button>
                </div>
            </div>

            <div class="flower-card" data-tags="розы премиум">
                <img src="images/bouquet/4.jpg" alt="Букет Романтика">
                <div class="card-content">
                    <div class="card-tags">
                        <span class="card-tag">#розы</span>
                        <span class="card-tag">#премиум</span>
                    </div>
                    <h3>Букет «Романтика»</h3>
                    <p class="info">Цена: <span class="price">8700</span> сом</p>
                    <p class="description">Состав: 21 красная роза Эквадор, изящные ветви итальянского рускуса, перевязанные широкой атласной лентой.</p>
                    <button class="buy-btn" data-bouquet="Букет «Романтика»" data-price="8700">Оформить заказ</button>
                </div>
            </div>

            <div class="flower-card" data-tags="бюджет нежные тюльпаны">
                <img src="images/bouquet/5.jpg" alt="Букет Весеннее Утро">
                <div class="card-content">
                    <div class="card-tags">
                        <span class="card-tag">#бюджет</span>
                        <span class="card-tag">#нежные</span>
                        <span class="card-tag">#тюльпаны</span>
                    </div>
                    <h3>Букет «Весеннее Утро»</h3>
                    <p class="info">Цена: <span class="price">4500</span> сом</p>
                    <p class="description">Состав: Нежные альстромерии, белые кустовых ромашки и веточки воздушного аспарагуса в крафте.</p>
                    <button class="buy-btn" data-bouquet="Букет «Весеннее Утро»" data-price="4500">Оформить заказ</button>
                </div>
            </div>

            <div class="flower-card" data-tags="розы премиум">
                <img src="images/bouquet/6.jpg" alt="Букет Тайный Сад">
                <div class="card-content">
                    <div class="card-tags">
                        <span class="card-tag">#розы</span>
                        <span class="card-tag">#премиум</span>
                    </div>
                    <h3>Букет «Тайный Сад»</h3>
                    <p class="info">Цена: <span class="price">9600</span> сом</p>
                    <p class="description">Состав: Пионовидные розы, сиреневая лизиантус, бархатные резные листья цинерарии в премиальной шляпной коробке.</p>
                    <button class="buy-btn" data-bouquet="Букет «Тайный Сад»" data-price="9600">Оформить заказ</button>
                </div>
            </div>
        </div>

        <section class="used-flowers-section">
            <h2 style="color: #47824b; text-align: center; margin-bottom: 10px;">♻️ Витрина «Вторые руки»</h2>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">Подаренные букеты от обычных людей по сниженной цене</p>
            
            <?php if (empty($usedFlowers)): ?>
                <p style="text-align: center; color: #999; font-style: italic; padding: 20px;">На данный момент все б/у букеты распроданы. Вы можете выставить свой в личном кабинете!</p>
            <?php else: ?>
                <div class="used-flowers-grid">
                    <?php foreach ($usedFlowers as $flower): ?>
                        <div class="used-card">
                            <div class="used-img-container">
                                <?php if ($flower['image_blob']): ?>
                                    <img class="used-img" src="data:image/jpeg;base64,<?php echo base64_encode($flower['image_blob']); ?>" alt="Букет б/у">
                                <?php else: ?>
                                    <span style="color: #aaa;">Фото отсутствует</span>
                                <?php endif; ?>
                            </div>
                            <div class="used-content">
                                <span class="used-badge">Вторые руки</span>
                                <h3 class="used-title"><?php echo $flower['name']; ?></h3>
                                <p class="used-desc"><?php echo $flower['description']; ?></p>
                                <div class="used-price-box"><?php echo $flower['price']; ?> сом</div>
                                <button class="buy-btn" data-bouquet="Б/У: <?php echo $flower['name']; ?>" data-price="<?php echo $flower['price']; ?>" style="width: 100%; background: #47824b; color: white; border: none; padding: 10px; border-radius: 6px; cursor: pointer; font-weight: bold;">Купить букет</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <div class="action-box">
            <p>Хотите что-то особенное?</p>
            <a href="constructor.html" class="cta-btn">🌸 Собрать свой букет</a>
        </div>
    </main>

    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <h3>Оформление заказа</h3>
            <p id="modalBouquetInfo"></p>
            
           <form class="modal-form" id="fastOrderForm" action="process_order.php" method="POST" novalidate>
    <input type="hidden" id="selectedBouquetName" name="bouquet_details">
    <input type="hidden" id="selectedBouquetPrice" name="total_price">

    <label>Ваше имя:
        <input type="text" id="username" name="name" placeholder="Введите ваше имя" required>
        <span class="error-msg" id="nameError"></span>
    </label>
    
    <label>Телефон:
        <input type="tel" id="userphone" name="phone" placeholder="0707 12-34-56" required>
        <span class="error-msg" id="phoneError"></span>
    </label>
    
    <label>Адрес доставки:
        <input type="text" id="address" name="address" placeholder="Улица, дом, квартира" required>
        <span class="error-msg" id="addressError"></span>
    </label>
    
    <label>Способ оплаты:
        <select name="payment_method">
            <option value="cash">Наличными курьеру</option>
            <option value="o-dengi">О!Деньги / Элсом</option>
            <option value="card">Картой онлайн</option>
        </select>
    </label>
    <button type="submit" class="order-submit-btn">Подтвердить заказ</button>
</form>
        </div>
    </div>

   <script>
    document.addEventListener("DOMContentLoaded", () => {
        const modal = document.getElementById("orderModal");
        const closeModalBtn = document.getElementById("closeModal");
        const modalBouquetInfo = document.getElementById("modalBouquetInfo");
        
        const form = document.getElementById("fastOrderForm");
        const username = document.getElementById("username");
        const userphone = document.getElementById("userphone");
        const address = document.getElementById("address");
        const hiddenNameInput = document.getElementById("selectedBouquetName");
        const hiddenPriceInput = document.getElementById("selectedBouquetPrice");
        
        // Переназначаем слушатель кнопок динамически, чтобы он работал и для новых б/у карточек
        document.body.addEventListener("click", (e) => {
            if (e.target && e.target.classList.contains("buy-btn")) {
                const button = e.target;
                const bouquetName = button.getAttribute("data-bouquet");
                const bouquetPrice = button.getAttribute("data-price");

                modalBouquetInfo.textContent = `Вы выбрали: ${bouquetName} (${bouquetPrice} сом)`;
                
                hiddenNameInput.value = bouquetName.includes("Б/У:") ? bouquetName : `Готовый вариант: ${bouquetName}`;
                hiddenPriceInput.value = bouquetPrice;

                form.reset();
                document.getElementById("nameError").textContent = "";
                document.getElementById("phoneError").textContent = "";
                document.getElementById("addressError").textContent = "";

                modal.style.display = "flex";
            }
        });

        // МАСКА ДЛЯ ТЕЛЕФОНА
        userphone.addEventListener("input", (e) => {
            let input = e.target.value.replace(/\D/g, "");
            let formatted = "";

            if (input.length > 0) {
                if (input[0] !== "0") {
                    input = "0" + input;
                }
                formatted = input.substring(0, 10); 
            }
            
            if (input.length > 4) { formatted = formatted.substring(0, 4) + " " + formatted.substring(4); }
            if (input.length > 6) { formatted = formatted.substring(0, 7) + "-" + formatted.substring(7); }
            if (input.length > 8) { formatted = formatted.substring(0, 10) + "-" + formatted.substring(10); }

            e.target.value = formatted;
        });

        // ВАЛИДАЦИЯ И ОТПРАВКА НА СЕРВЕР
        form.addEventListener("submit", (e) => {
            e.preventDefault(); 
            
            let isValid = true;

            document.getElementById("nameError").textContent = "";
            document.getElementById("phoneError").textContent = "";
            document.getElementById("addressError").textContent = "";

            const nameValue = username.value.trim();
            if (nameValue.length < 2) {
                document.getElementById("nameError").textContent = "Ошибка: Имя слишком короткое (минимум 2 буквы)!";
                isValid = false;
            } else if (nameValue.length > 30) {
                document.getElementById("nameError").textContent = "Ошибка: Имя не должно превышать 30 символов!";
                isValid = false;
            }

            const phoneDigits = userphone.value.replace(/\D/g, ""); 
            if (phoneDigits.length !== 10) {
                document.getElementById("phoneError").textContent = "Ошибка: Номер должен состоять ровно из 10 цифр!";
                isValid = false;
            }

            const addressValue = address.value.trim();
            if (addressValue === "") {
                document.getElementById("addressError").textContent = "Ошибка: Адрес доставки не может быть пустым!";
                isValid = false;
            } else if (addressValue.length < 10) {
                document.getElementById("addressError").textContent = "Ошибка: Напишите подробнее (минимум 10 символов)!";
                isValid = false;
            }

            if (isValid) {
                modal.style.display = "none";
                form.submit(); 
            }
        });

        closeModalBtn.addEventListener("click", () => { modal.style.display = "none"; });
        window.addEventListener("click", (event) => { if (event.target === modal) { modal.style.display = "none"; } });

        // ЛОГИКА ФИЛЬТРАЦИИ ПО ТЕГАМ
        const filterButtons = document.querySelectorAll(".filter-btn");
        const flowerCards = document.querySelectorAll(".flower-card");

        filterButtons.forEach(button => {
            button.addEventListener("click", () => {
                filterButtons.forEach(btn => btn.classList.remove("active"));
                button.classList.add("active");

                const filterValue = button.getAttribute("data-filter");

                flowerCards.forEach(card => {
                    const cardTags = card.getAttribute("data-tags").split(" ");

                    if (filterValue === "all" || cardTags.includes(filterValue)) {
                        card.style.display = "flex";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        });
    });
</script>
</body>
</html>
