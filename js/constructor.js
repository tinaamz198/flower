document.addEventListener("DOMContentLoaded", () => {
    // Получаем все нужные элементы со страницы
    const flowerInputs = document.querySelectorAll(".flower-quantity"); // Все поля ввода количества цветов
    const greensInputs = document.querySelectorAll(".greens-quantity"); // Все поля ввода количества зелени
    const decorSelect = document.getElementById("decor");               // Выпадающий список упаковки
    const totalPriceElement = document.getElementById("totalPrice");     // Элемент, куда пишем итоговую сумму

    // Основная функция для подсчета общей цены заказа
    function calculateTotal() {
        let total = 0;

        // 1. Считаем сумму за цветы (каждый цветок имеет свою цену через data-price)
        flowerInputs.forEach(input => {
            let count = parseInt(input.value || 0); // Получаем число, если пусто — берем 0
            const price = parseFloat(input.getAttribute("data-price")) || 0;

            // Ограничения: не даем ввести больше 101 или меньше 0
            if (count > 101) { count = 101; input.value = 101; }
            if (count < 0) { count = 0; input.value = 0; }

            total += count * price; // Прибавляем стоимость этих цветов к общему итогу
        });

        // 2. Считаем сумму за зелень (аналогично цветам, но с лимитом до 50 штук)
        greensInputs.forEach(input => {
            let count = parseInt(input.value || 0);
            const price = parseFloat(input.getAttribute("data-price")) || 0;

            if (count > 50) { count = 50; input.value = 50; }
            if (count < 0) { count = 0; input.value = 0; }

            total += count * price;
        });

        // 3. Добавляем стоимость выбранной упаковки (если она выбрана)
        if (decorSelect) {
            const selectedOption = decorSelect.options[decorSelect.selectedIndex];
            const decorPrice = parseInt(selectedOption.getAttribute("data-price")) || 0;
            total += decorPrice;
        }

        // 4. Обновляем текст на экране и синхронизируем данные с формой
        if (totalPriceElement) {
            totalPriceElement.textContent = total;
            syncPriceToForm(); // Вызываем функцию синхронизации при каждом пересчете
        }
    }

    // Слушатели событий: пересчитываем цену каждый раз, когда пользователь что-то меняет
    flowerInputs.forEach(input => input.addEventListener("input", calculateTotal));
    greensInputs.forEach(input => input.addEventListener("input", calculateTotal));
    
    if (decorSelect) {
        decorSelect.addEventListener("change", calculateTotal);
    }

    // Функция, которая прячет текущую цену в скрытый инпут внутри формы
    // Это нужно, чтобы при нажатии на "Подтвердить" мы могли легко получить сумму
    function syncPriceToForm() {
        const total = document.getElementById("totalPrice").textContent;
        let hiddenPrice = document.getElementById("hiddenTotalPrice");
        
        // Если скрытого поля нет, создаем его динамически
        if (!hiddenPrice) {
            hiddenPrice = document.createElement("input");
            hiddenPrice.type = "hidden";
            hiddenPrice.id = "hiddenTotalPrice";
            // Вставляем его внутрь формы оформления заказа
            document.getElementById("orderForm").appendChild(hiddenPrice);
        }
        hiddenPrice.value = total; // Записываем текущую сумму в скрытое поле
    }

    // Первичный расчет при самой загрузке страницы, чтобы сумма не была пустой
    calculateTotal();
});
