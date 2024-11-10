jQuery(document).ready(function($) {

    // Инициализация ползунка для диапазона цен с реальными минимальным и максимальным значениями
    $('#price-slider-range').slider({
        range: true,
        min: parseFloat(filterData.min_price), // Устанавливаем реальное минимальное значение
        max: parseFloat(filterData.max_price), // Устанавливаем реальное максимальное значение
        values: [parseFloat(filterData.min_price), parseFloat(filterData.max_price)],
        slide: function(event, ui) {
            $('#price-range').val(ui.values[0] + ' - ' + ui.values[1]);
            $('#min-price-label').text(ui.values[0]);
            $('#max-price-label').text(ui.values[1]);
        }
    });

    // Установка диапазона цен по умолчанию
    $('#price-range').val($('#price-slider-range').slider('values', 0) + ' - ' + $('#price-slider-range').slider('values', 1));
    $('#min-price-label').text(filterData.min_price);
    $('#max-price-label').text(filterData.max_price);

    // Обработка клика по кнопке "Применить фильтр"
    $('#apply-filter').on('click', function() {
        const formData = $('#product-filter-form').serializeArray();
        formData.push({
            name: 'min_price',
            value: $('#price-slider-range').slider('values', 0)
        }, {
            name: 'max_price',
            value: $('#price-slider-range').slider('values', 1)
        });

        console.log(formData); // Логируем перед отправкой для проверки данных

        $.ajax({
            url: filterData.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_products',
                form_data: formData
            },
            success: function(response) {
                $('#filtered-products .product-wrapper').html(response.html);

                // Преобразуем значения в числа перед обновлением слайдера
                const minPrice = parseFloat(response.min_price) || filterData.min_price;
                const maxPrice = parseFloat(response.max_price) || filterData.max_price;

                // Обновляем слайдер цены с новыми значениями
                $('#price-slider-range').slider('option', 'min', minPrice);
                $('#price-slider-range').slider('option', 'max', maxPrice);
                $('#price-range').val(minPrice + ' - ' + maxPrice);

                // Обновляем крайние метки
                $('#min-price-label').text(minPrice);
                $('#max-price-label').text(maxPrice);

                // Закрытие Offcanvas
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('filterOffcanvas'));
                if (offcanvas) offcanvas.hide();
            },
            error: function(xhr, status, error) {
                console.log('Ошибка AJAX: ', error);
            }
        });
    });

    // Обработка кнопки "Сбросить фильтр"
    $('#reset-filter').on('click', function() {
        $('#product-filter-form')[0].reset();
        $('#price-slider-range').slider('values', [parseFloat(filterData.min_price), parseFloat(filterData.max_price)]);
        $('#price-range').val(parseFloat(filterData.min_price) + ' - ' + parseFloat(filterData.max_price));
        $('#min-price-label').text(filterData.min_price);
        $('#max-price-label').text(filterData.max_price);

        // $('#filtered-products .product-wrapper').empty(); // Очистка контейнера с карточками товаров
        //location.reload(); // Перезагрузка страницы для сброса фильтров
    });
});