document.addEventListener("DOMContentLoaded", () => {
    // Находим основные элементы формы в DOM, чтобы работать с ними
    const form = document.getElementById("orderForm");
    const username = document.getElementById("username");
    const userphone = document.getElementById("userphone");
    const address = document.getElementById("address");
    
    // Элемент, который показывает текущую итоговую цену (из конструктора)
    const totalPriceElement = document.getElementById("totalPrice");

    // ИНТЕЛЛЕКТУАЛЬНАЯ МАСКА ТЕЛЕФОНА
    // При каждом вводе символа форматируем номер под наш стандарт: 0XXX XX-XX-XX
    userphone.addEventListener("input", (e) => {
        let input = e.target.value.replace(/\D/g, ""); // Оставляем только цифры, удаляя всё лишнее
        let formatted = "";

        // Всегда начинаем номер с 0
        if (input.length > 0) {
            formatted = "0" + input.substring(1, 10);
        }
        
        // Добавляем пробелы и дефисы на нужных позициях для красоты
        if (input.length > 4) formatted = formatted.substring(0, 4) + " " + formatted.substring(4);
        if (input.length > 6) formatted = formatted.substring(0, 7) + "-" + formatted.substring(7);
        if (input.length > 8) formatted = formatted.substring(0, 10) + "-" + formatted.substring(10);

        e.target.value = formatted; // Обновляем поле ввода уже отформатированным текстом
    });

    // ВАЛИДАЦИЯ ПРИ ОТПРАВКЕ
    // Проверяем данные перед тем, как "отправить" их (пока просто выводим алерт)
    form.addEventListener("submit", (e) => {
        e.preventDefault(); // Останавливаем стандартную отправку формы
        
        let isValid = true; // Флаг: считаем, что форма верна, пока не найдем ошибку

        // Сбрасываем текст старых ошибок перед новой проверкой
        document.getElementById("nameError").textContent = "";
        document.getElementById("phoneError").textContent = "";
        document.getElementById("addressError").textContent = "";

        // 1. Проверка Имени: не слишком ли короткое или длинное?
        const nameValue = username.value.trim();
        if (nameValue.length < 2 || nameValue.length > 30) {
            document.getElementById("nameError").textContent = "Введите имя от 2 до 30 символов.";
            isValid = false; // Ошибка найдена, отправку блокируем
        }

        // 2. Проверка Телефона: проверяем количество цифр
        const phoneDigits = userphone.value.replace(/\D/g, ""); 
        if (phoneDigits.length !== 10) {
            document.getElementById("phoneError").textContent = "Ошибка: номер должен быть 0XXX XX-XX-XX.";
            isValid = false;
        }

        // 3. Проверка Адреса: должен быть подробным
        const addressValue = address.value.trim();
        if (addressValue.length < 10) {
            document.getElementById("addressError").textContent = "Пожалуйста, укажите подробный адрес (мин. 10 символов).";
            isValid = false;
        }

        // 4. Проверка Цены: нельзя заказать пустой букет
        const finalPrice = parseInt(totalPriceElement.textContent) || 0;
        if (finalPrice <= 0) {
            alert("Ошибка: Ваш букет пуст! Добавьте хотя бы один цветок.");
            isValid = false;
        }

        // ЕСЛИ ВСЁ УСПЕШНО (isValid остался true)
        if (isValid) {
            alert(`🎉 Заказ успешно принят!\n\nКлиент: ${nameValue}\nТелефон: ${userphone.value}\nАдрес: ${addressValue}\nИтого к оплате: ${finalPrice} сом.`);
            
            // Очищаем саму форму ввода данных
            form.reset();
            
            // Очищаем конструктор: сбрасываем счетчики цветов и зелени в 0
            document.querySelectorAll(".flower-quantity, .greens-quantity").forEach(input => input.value = 0);
            document.getElementById("decor").value = "none";
            
            // Обновляем итоговую цену на странице, вызывая функцию пересчета из другого файла
            if (typeof calculateTotal === 'function') {
                calculateTotal();
            } else {
                totalPriceElement.textContent = "0"; // Если функции нет, просто обнуляем текстом
            }
        }
    });
});
