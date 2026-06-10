document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("orderForm");
    const username = document.getElementById("username");
    const userphone = document.getElementById("userphone");
    const address = document.getElementById("address");
    
    // Элементы для сброса
    const totalPriceElement = document.getElementById("totalPrice");

    // ИНТЕЛЛЕКТУАЛЬНАЯ МАСКА ТЕЛЕФОНА
    userphone.addEventListener("input", (e) => {
        let input = e.target.value.replace(/\D/g, "");
        let formatted = "";

        if (input.length > 0) {
            formatted = "0" + input.substring(1, 10);
        }
        
        if (input.length > 4) formatted = formatted.substring(0, 4) + " " + formatted.substring(4);
        if (input.length > 6) formatted = formatted.substring(0, 7) + "-" + formatted.substring(7);
        if (input.length > 8) formatted = formatted.substring(0, 10) + "-" + formatted.substring(10);

        e.target.value = formatted;
    });

    // ВАЛИДАЦИЯ ПРИ ОТПРАВКЕ
    form.addEventListener("submit", (e) => {
        e.preventDefault(); 
        
        let isValid = true;

        // Сброс ошибок
        document.getElementById("nameError").textContent = "";
        document.getElementById("phoneError").textContent = "";
        document.getElementById("addressError").textContent = "";

        // 1. Имя
        const nameValue = username.value.trim();
        if (nameValue.length < 2 || nameValue.length > 30) {
            document.getElementById("nameError").textContent = "Введите имя от 2 до 30 символов.";
            isValid = false;
        }

        // 2. Телефон
        const phoneDigits = userphone.value.replace(/\D/g, ""); 
        if (phoneDigits.length !== 10) {
            document.getElementById("phoneError").textContent = "Ошибка: номер должен быть 0XXX XX-XX-XX.";
            isValid = false;
        }

        // 3. Адрес
        const addressValue = address.value.trim();
        if (addressValue.length < 10) {
            document.getElementById("addressError").textContent = "Пожалуйста, укажите подробный адрес (мин. 10 символов).";
            isValid = false;
        }

        // 4. Проверка цены (если 0 — значит пусто)
        const finalPrice = parseInt(totalPriceElement.textContent) || 0;
        if (finalPrice <= 0) {
            alert("Ошибка: Ваш букет пуст! Добавьте хотя бы один цветок.");
            isValid = false;
        }

        // ЕСЛИ ВСЁ УСПЕШНО
        if (isValid) {
            alert(`🎉 Заказ успешно принят!\n\nКлиент: ${nameValue}\nТелефон: ${userphone.value}\nАдрес: ${addressValue}\nИтого к оплате: ${finalPrice} сом.`);
            
            // Сброс формы
            form.reset();
            
            // Сброс конструктора
            document.querySelectorAll(".flower-quantity, .greens-quantity").forEach(input => input.value = 0);
            document.getElementById("decor").value = "none";
            
            // Важно: пересчитываем цену в 0 и скрываем ненужные элементы
            if (typeof calculateTotal === 'function') {
                calculateTotal();
            } else {
                totalPriceElement.textContent = "0";
            }
        }
    });
});