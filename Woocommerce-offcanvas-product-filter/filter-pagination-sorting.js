jQuery(document).ready(function($) {

    let isFilterApplied = false; // Флаг для отслеживания состояния фильтра

    // Управляем состоянием загрузки, чтобы избежать двойных запросов
    let isLoading = false;

    // Задаем минимальные и максимальные цены по умолчанию из filterData
    const defaultMinPrice = parseFloat(filterData.min_price) || 0;
    const defaultMaxPrice = parseFloat(filterData.max_price) || 100;

// Функция для отправки запроса с учетом текущей страницы
    function applyFilter(paged = 1, orderby = $('#sort-by').val()) {


        if (isLoading) return; // Прерываем, если фильтр уже загружен
        isLoading = true;
        isFilterApplied = true; // Устанавливаем, что фильтр активен

        // Обработка клика по кнопке "Применить фильтр"

        const formData = $('#product-filter-form').serializeArray();
        formData.push({
                name: 'min_price',
                value: $('#price-slider-range').slider('values', 0)
            }, {
                name: 'max_price',
                value: $('#price-slider-range').slider('values', 1)
            },  { name: 'orderby', value: orderby } // Добавляем сортировку


        );

        console.log(formData); // Логируем перед отправкой для проверки данных

        $.ajax({
            url: filterData.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_products',
                form_data: formData,
                paged: paged,
                orderby: orderby // Передаем сортировку на сервер
            },
            success: function (response) {
                if (response.success) {
                    $('#filtered-products .product-wrapper').html(response.data.html);
                    // Обновляем пагинацию фильтра
                    $('.pagination').html(response.data.pagination);

                    // Преобразуем значения в числа перед обновлением слайдера
                    const minPrice = parseFloat(response.min_price) || defaultMinPrice;
                    const maxPrice = parseFloat(response.max_price) || defaultMaxPrice;

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

                } else {
                    console.log('Ошибка в ответе:', response);
                    alert('Произошла ошибка при загрузке фильтра.');
                }
            },
            error: function (xhr, status, error) {
                console.log('Ошибка AJAX: ', error);
                console.log(xhr.responseText); // Логируем HTML-ответ
            },
            complete: function() {
                isLoading = false; // Устанавливаем флаг загрузки в false после завершения
            }
        });

    }

    // Инициализация слайдера для диапазона цен с реальными минимальным и максимальным значениями
    $('#price-slider-range').slider({
        range: true,
        min: defaultMinPrice, // Устанавливаем реальное минимальное значение
        max: defaultMaxPrice, // Устанавливаем реальное максимальное значение
        values: [defaultMinPrice, defaultMaxPrice],
        slide: function(event, ui) {
            $('#price-range').val(ui.values[0] + ' - ' + ui.values[1]);
            $('#min-price-label').text(ui.values[0]);
            $('#max-price-label').text(ui.values[1]);
        }
    });

    // Установка диапазона цен по умолчанию
    $('#price-range').val($('#price-slider-range').slider('values', 0) + ' - ' + $('#price-slider-range').slider('values', 1));
    $('#min-price-label').text(defaultMinPrice);
    $('#max-price-label').text(defaultMaxPrice);

    // Обработка кнопки "Применить фильтр"
    $('#apply-filter').on('click', function() {
        isFilterApplied = true;
        applyFilter();
    });



    // Обработка кликов по пагинации
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();

        const isMainPagination = $(this).closest('#main-pagination').length > 0;
        const paged = $(this).attr('href').split('paged=')[1]; // Извлекаем номер страницы

        if (isFilterApplied) {
            // Если фильтр активирован, то используем AJAX пагинацию
            applyFilter(paged);
        } else if (isMainPagination) {
            // Если фильтр не активен, выполняем обычную пагинацию каталога
            window.location.href = $(this).attr('href');
        }
    });

    // Обработка кнопки "Сбросить фильтр"
    $('#reset-filter').on('click', function() {
        $('#product-filter-form')[0].reset();
        $('#price-slider-range').slider('values', [defaultMinPrice, defaultMaxPrice]);
        $('#price-range').val(defaultMinPrice + ' - ' + defaultMaxPrice);
        $('#min-price-label').text(defaultMinPrice);
        $('#max-price-label').text(defaultMaxPrice);

        //$('#filtered-products .product-wrapper').empty(); // Очистка контейнера с карточками товаров
        isFilterApplied = false; // Сбрасываем флаг фильтрации
        location.reload(); // Перезагрузка страницы для сброса фильтров
    });
    // Обработка изменений в поле сортировки
    $('#sort-by').on('change', function() {
        const orderby = $(this).val();
        if (isFilterApplied) {
            // Если фильтр активен, сортировка через AJAX
            applyFilter(1, orderby);
        } else {
            // Если фильтр не активен, перезагружаем страницу с параметром сортировки
            const url = new URL(window.location.href);
            url.searchParams.set('orderby', orderby);
            window.location.href = url.toString();
        }
    });
});