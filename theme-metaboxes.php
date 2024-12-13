<?php
// Добавляем метабокс на страницу с шаблоном home.php
function falaktech_add_slider_metabox_conditionally($post): void
{
    $template = get_page_template_slug($post->ID); // Получаем текущий шаблон страницы
    if ($template === 'home.php') {
        add_meta_box(
            'falaktech_slider',            // ID метабокса
            'Слайдер главной страницы',    // Заголовок метабокса
            'falaktech_render_slider_metabox', // Функция рендера полей метабокса
            'page',                        // Тип записи (только страницы)
            'normal',                      // Позиция метабокса
            'default'                      // Приоритет метабокса
        );
    }
}
add_action('add_meta_boxes', 'falaktech_add_slider_metabox_conditionally');


//Рендер полей метабокса
function falaktech_render_slider_metabox($post): void
{
    // Проверяем текущий шаблон
    $template = get_page_template_slug($post->ID);

    // Получаем сохраненные значения из метаполей
    $slider_data = get_post_meta($post->ID, '_falaktech_slider', true);

    // Если данных нет, создаем пустой массив
    if (empty($slider_data)) {
        $slider_data = [];
    }

    // Генерация полей для каждого слайда
    // Nonce для проверки безопасности
    wp_nonce_field('falaktech_slider_nonce_action', 'falaktech_slider_nonce');

    // Оборачиваем весь блок метаполей в контейнер с ID
    //echo '<div id="falaktech_slider" class="' . ($template === 'home.php' ? '' : 'hidden') . '">';

    // Контейнер для метаполей
    echo '<div id="falaktech-slides-container" style="display: grid; gap: 20px;">';

    foreach ($slider_data as $index => $slide) {
        echo '<div class="falaktech-slide" style="border: 1px solid #ddd; padding: 10px; position: relative;">';
        echo '<h4>Слайд ' . ($index + 1) . '</h4>';

        // Поле для заголовка
        echo '<p><label>Заголовок:</label><br>';
        echo '<input type="text" name="falaktech_slider[' . $index . '][title]" value="' . esc_attr($slide['title'] ?? '') . '" style="width: 100%;"></p>';

        // Поле для подзаголовка
        echo '<p><label>Подзаголовок:</label><br>';
        echo '<input type="text" name="falaktech_slider[' . $index . '][subtitle]" value="' . esc_attr($slide['subtitle'] ?? '') . '" style="width: 100%;"></p>';

        // Поле для изображения
        echo '<p><label>Изображение:</label><br>';
        echo '<input type="text" name="falaktech_slider[' . $index . '][image]" value="' . esc_url($slide['image'] ?? '') . '" style="width: 80%;" class="falaktech-slider-image">';
        echo '<button type="button" class="button falaktech-slider-upload">Выбрать изображение</button></p>';

        // Кнопка удаления слайда
        echo '<button type="button" class="button-link-delete falaktech-remove-slide" style="color: red; position: absolute; top: 10px; right: 10px;">Удалить слайд</button>';
        echo '</div>';
    }

    echo '</div>';

    // Кнопка добавления слайда
    echo '<button type="button" class="button button-primary" id="falaktech-add-slide" style="margin-top: 10px;">Добавить слайд</button>';

    //echo '</div>';

    // Скрипт для добавления/удаления слайдов
    echo "
    <script>
        jQuery(document).ready(function($) {
            let slideIndex = " . count($slider_data) . "; // Текущий индекс для новых слайдов

            // Функция для добавления нового слайда
            $('#falaktech-add-slide').on('click', function() {
                let newSlide = `
                <div class=\"falaktech-slide\" style=\"border: 1px solid #ddd; padding: 10px; position: relative;\">
                    <h4>New Slide</h4>
                    <p><label>Title:</label><br>
                    <input type=\"text\" name=\"falaktech_slider[\${slideIndex}][title]\" style=\"width: 50%;\"></p>
                    <p><label>Подзаголовок:</label><br>
                    <input type=\"text\" name=\"falaktech_slider[\${slideIndex}][subtitle]\" style=\"width: 50%;\"></p>
                    <p><label>Изображение:</label><br>
                    <input type=\"text\" name=\"falaktech_slider[\${slideIndex}][image]\" style=\"width: 50%;\" class=\"falaktech-slider-image\">
                    <button type=\"button\" class=\"button falaktech-slider-upload\">Выбрать изображение</button></p>
                    <button type=\"button\" class=\"button-link-delete falaktech-remove-slide\" style=\"color: red; position: absolute; top: 10px; right: 10px;\">Удалить слайд</button>
                </div>`;
                $('#falaktech-slides-container').append(newSlide);
                slideIndex++;
            });

            // Функция для удаления слайда
            $(document).on('click', '.falaktech-remove-slide', function() {
                $(this).closest('.falaktech-slide').remove();
            });

            // Медиабиблиотека для выбора изображений
            $(document).on('click', '.falaktech-slider-upload', function(e) {
                e.preventDefault();
                let button = $(this);
                let targetInput = button.siblings('.falaktech-slider-image');

                let mediaUploader = wp.media({
                    title: 'Выберите изображение',
                    button: { text: 'Использовать это изображение' },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    let attachment = mediaUploader.state().get('selection').first().toJSON();
                    targetInput.val(attachment.url);
                });

                mediaUploader.open();
            });
        });
    </script>
    ";
}

//Обработчик для сохранения данных метаполей
function falaktech_save_slider_metabox($post_id): void
{
    // Проверяем nonce
    if (!isset($_POST['falaktech_slider_nonce']) || !wp_verify_nonce($_POST['falaktech_slider_nonce'], 'falaktech_slider_nonce_action')) {
        return;
    }

    // Проверяем возможность редактирования
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Сохраняем данные, если они есть
    if (isset($_POST['falaktech_slider']) && is_array($_POST['falaktech_slider'])) {
        $slider_data = array_map(function ($slide) {
            return [
                'title' => sanitize_text_field($slide['title'] ?? ''),
                'subtitle' => sanitize_text_field($slide['subtitle'] ?? ''),
                'image' => esc_url_raw($slide['image'] ?? '')
            ];
        }, $_POST['falaktech_slider']);

        update_post_meta($post_id, '_falaktech_slider', $slider_data);
    } else {
        delete_post_meta($post_id, '_falaktech_slider');
    }
}
add_action('save_post', 'falaktech_save_slider_metabox');