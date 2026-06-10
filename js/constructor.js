document.addEventListener("DOMContentLoaded", () => {
    const flowerInputs = document.querySelectorAll(".flower-quantity");
    const greensInputs = document.querySelectorAll(".greens-quantity");
    const decorSelect = document.getElementById("decor");
    const totalPriceElement = document.getElementById("totalPrice");

    function calculateTotal() {
        let total = 0;

        // 1. Считаем сумму за цветы (Лимит: 0 - 101)
        flowerInputs.forEach(input => {
            let count = parseInt(input.value || 0);
            const price = parseFloat(input.getAttribute("data-price")) || 0;

            if (count > 101) { count = 101; input.value = 101; }
            if (count < 0) { count = 0; input.value = 0; }

            total += count * price;
        });

        // 2. Считаем сумму за зелень (Лимит: 0 - 50 веточек)
        greensInputs.forEach(input => {
            let count = parseInt(input.value || 0);
            const price = parseFloat(input.getAttribute("data-price")) || 0;

            if (count > 50) { count = 50; input.value = 50; }
            if (count < 0) { count = 0; input.value = 0; }

            total += count * price;
        });

        // 3. Добавляем цену упаковки (безопасное получение)
        if (decorSelect) {
            const selectedOption = decorSelect.options[decorSelect.selectedIndex];
            const decorPrice = parseInt(selectedOption.getAttribute("data-price")) || 0;
            total += decorPrice;
        }

        // 4. Выводим итоговую сумму на страницу
        if (totalPriceElement) {
            totalPriceElement.textContent = total;
        }
    }

    // Слушаем изменения на всех элементах для динамического пересчета
    flowerInputs.forEach(input => input.addEventListener("input", calculateTotal));
    greensInputs.forEach(input => input.addEventListener("input", calculateTotal));
    
    if (decorSelect) {
        decorSelect.addEventListener("change", calculateTotal);
    }
// Функция для обновления скрытого поля цены в форме
function syncPriceToForm() {
    const total = document.getElementById("totalPrice").textContent;
    // Создадим скрытое поле, если его еще нет, или найдем существующее
    let hiddenPrice = document.getElementById("hiddenTotalPrice");
    if (!hiddenPrice) {
        hiddenPrice = document.createElement("input");
        hiddenPrice.type = "hidden";
        hiddenPrice.id = "hiddenTotalPrice";
        document.getElementById("orderForm").appendChild(hiddenPrice);
    }
    hiddenPrice.value = total;
}
    // Запускаем первичный расчет при загрузке страницы
    calculateTotal();
});