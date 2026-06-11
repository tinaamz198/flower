document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("orderForm");
    const username = document.getElementById("username");
    const userphone = document.getElementById("userphone");
    const address = document.getElementById("address");

    // МАСКА ДЛЯ ТЕЛЕФОНА под требования ТЗ (+996XXXXXXXXX)
    userphone.addEventListener("input", (e) => {
        let input = e.target.value.replace(/\D/g, "");
        if (!input.startsWith("996")) {
            input = "996" + input;
        }
        e.target.value = "+" + input.substring(0, 12);
    });

    form.addEventListener("submit", (e) => {
        e.preventDefault(); // Тормозим отправку для проверки
        
        let isValid = true;

        document.getElementById("nameError").textContent = "";
        document.getElementById("phoneError").textContent = "";
        document.getElementById("addressError").textContent = "";

        const nameValue = username.value.trim();
        if (nameValue.length < 2) {
            document.getElementById("nameError").textContent = "Имя должно быть от 2 символов!";
            isValid = false;
        }

        const phoneDigits = userphone.value.replace(/\D/g, "");
        if (phoneDigits.length !== 12) {
            document.getElementById("phoneError").textContent = "Номер телефона должен быть в формате +996XXXXXXXXX!";
            isValid = false;
        }

        const addressValue = address.value.trim();
        if (addressValue.length < 10) {
            document.getElementById("addressError").textContent = "Введите более подробный адрес (мин. 10 символов)!";
            isValid = false;
        }

        const finalPrice = parseFloat(document.getElementById("totalPrice").textContent || 0);
        if (finalPrice === 0) {
            alert("Ваш букет пуст! Соберите букет перед заказом.");
            isValid = false;
        }

        // ЕСЛИ ВСЁ VALIID — ПЕРЕДАЕМ ДАННЫЕ В PHP И ОТПРАВЛЯЕМ ФОРМУ
        if (isValid) {
            let bouquetText = "";
            
            document.querySelectorAll(".flower-quantity").forEach(input => {
                const count = parseInt(input.value || 0);
                if (count > 0) {
                    const label = input.parentElement.querySelector("label").textContent.split('(')[0].trim();
                    bouquetText += `• ${label}: ${count} шт.\n`;
                }
            });

            document.querySelectorAll(".greens-quantity").forEach(input => {
                const count = parseInt(input.value || 0);
                if (count > 0) {
                    const label = input.parentElement.querySelector("label").textContent.split('(')[0].trim();
                    bouquetText += `• Зелень (${label}): ${count} шт.\n`;
                }
            });

            const decorSelect = document.getElementById("decor");
            bouquetText += `• Упаковка: ${decorSelect.options[decorSelect.selectedIndex].text}`;

            // Закидываем всё в скрытые инпуты для отправки на бэкенд
            document.getElementById("phpBouquetDetails").value = bouquetText;
            document.getElementById("phpTotalPrice").value = finalPrice;

            // Отправляем форму на настоящий PHP-сервер!
            form.submit();
        }
    });
});