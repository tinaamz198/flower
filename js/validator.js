document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("orderForm");
    const username = document.getElementById("username");
    const userphone = document.getElementById("userphone");
    const address = document.getElementById("address");

    // ИНТЕЛЛЕКТУАЛЬНАЯ МАСКА ВВОДА НОМЕРА ТЕЛЕФОНА (Формат: 0707 12-34-56)
    userphone.addEventListener("input", (e) => {
        let input = e.target.value.replace(/\D/g, ""); // Удаляем все, кроме цифр
        let formatted = "";

        if (input.length > 0) {
            formatted = "0" + input.substring(1, 10); // Гарантируем, что номер начинается с 0
        }
        
        // Разделяем пробелами и дефисами по мере ввода для красивой маски
        if (input.length > 4) {
            formatted = formatted.substring(0, 4) + " " + formatted.substring(4);
        }
        if (input.length > 6) {
            formatted = formatted.substring(0, 7) + "-" + formatted.substring(7);
        }
        if (input.length > 8) {
            formatted = formatted.substring(0, 10) + "-" + formatted.substring(10);
        }

        e.target.value = formatted;
    });

    // ВАЛИДАЦИЯ ПРИ ОТПРАВКЕ ФОРМЫ
    form.addEventListener("submit", (e) => {
        e.preventDefault(); // Запрещаем отправку, если есть ошибки
        
        let isValid = true;

        // Сброс старых сообщений об ошибках
        document.getElementById("nameError").textContent = "";
        document.getElementById("phoneError").textContent = "";
        document.getElementById("addressError").textContent = "";

        // 1. Проверка лимита на имя (Мин: 2 символа, Макс: 30 символов)
        const nameValue = username.value.trim();
        if (nameValue.length < 2) {
            document.getElementById("nameError").textContent = "Ошибка: Имя слишком короткое (минимум 2 буквы)!";
            isValid = false;
        } else if (nameValue.length > 30) {
            document.getElementById("nameError").textContent = "Ошибка: Имя не должно превышать 30 символов!";
            isValid = false;
        }

        // 2. Проверка телефона по маске (Длина строки с пробелом и дефисами должна быть ровно 13 символов)
        // Пример корректной строки: "0707 12-34-56"
        const phoneDigits = userphone.value.replace(/\D/g, ""); // Только цифры для проверки длины
        if (phoneDigits.length !== 10) {
            document.getElementById("phoneError").textContent = "Ошибка: Номер должен состоять ровно из 10 цифр!";
            isValid = false;
        }

        // 3. Проверка лимита на адрес (Минимум 10 символов для точности доставки)
        const addressValue = address.value.trim();
        if (addressValue === "") {
            document.getElementById("addressError").textContent = "Ошибка: Адрес доставки не может быть пустым!";
            isValid = false;
        } else if (addressValue.length < 10) {
            document.getElementById("addressError").textContent = "Ошибка: Напишите более подробный адрес (мин. 10 символов)!";
            isValid = false;
        }

        // 4. Проверка, выбрал ли клиент хоть что-нибудь в конструкторе
        const finalPrice = parseFloat(document.getElementById("totalPrice").textContent || 0);
        if (finalPrice === 0 || finalPrice === parseFloat(document.getElementById("decor").value || 0)) {
            alert("Ошибка: Нельзя заказать пустую упаковку без цветов!");
            isValid = false;
        }

        // Если все проверки пройдены успешно
        if (isValid) {
            alert(`🎉 Заказ успешно принят!\n\nКлиент: ${nameValue}\nТелефон: ${userphone.value}\nАдрес: ${addressValue}\nСумма заказа: ${finalPrice} сом.`);
            
            // Очищаем форму и сбрасываем калькулятор
            form.reset();
            document.querySelectorAll(".flower-quantity, .greens-quantity").forEach(input => input.value = 0);
            document.getElementById("decor").value = "none";
            document.getElementById("decorPreview").classList.add("hidden");
            document.getElementById("totalPrice").textContent = "0";
        }
    });
});