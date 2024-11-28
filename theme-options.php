<?php

/**
 * Snippet Name: Theme Options for WordPress
 * Description: Дополнительная вкладка в меню "Внешний вид" с метаполями для дополнительных настроек
 * Version: 1.0
 * Author: Sharpeii
 */


// Подключение медиабиблиотеки WordPress в админке
function falaktech_enqueue_admin_scripts($hook_suffix): void
{
    // Подключаем только на нашей странице настроек
    if ($hook_suffix === 'appearance_page_falaktech-settings') {
        wp_enqueue_media(); // Подключаем медиабиблиотеку
        wp_enqueue_script('jquery');
    }
}
add_action('admin_enqueue_scripts', 'falaktech_enqueue_admin_scripts');

// Добавление страницы в меню админки
function falaktech_add_theme_page(): void
{
    add_theme_page(
        'Falaktech Settings',          // Название страницы
        'Falaktech',                    // Название в меню
        'manage_options',               // Уровень доступа
        'falaktech-settings',           // Слаг страницы
        'falaktech_render_settings_page' // Функция вывода страницы
    );
}
add_action('admin_menu', 'falaktech_add_theme_page');

// Функция рендера страницы настроек
function falaktech_render_settings_page(): void
{
    ?>
    <div class="wrap">
        <h1>Falaktech Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('falaktech_options_group'); // Группа настроек
            do_settings_sections('falaktech-settings'); // Секций настройки
            submit_button(); // Кнопка сохранения
            ?>
        </form>
    </div>
    <?php
}

// Функция для выбора изображения из медиабиблиотеки
function falaktech_media_upload_field($args): void
{
    $options = get_option('falaktech_options');
    $id = $args['label_for'];
    $value = $options[$id] ?? '';
    $image_preview = $value ? '<img src="' . esc_url($value) . '" alt="preview" style="max-width: 150px; max-height: 150px; display: block; margin-top: 10px;">' : '';

    echo '
    <div>
        <input type="hidden" id="' . esc_attr($id) . '" name="falaktech_options[' . esc_attr($id) . ']" value="' . esc_url($value) . '" />
        <button type="button" class="button falaktech-media-upload-button" data-target="#' . esc_attr($id) . '">Choose image</button>
        <button type="button" class="button falaktech-media-remove-button" data-target="#' . esc_attr($id) . '" style="margin-left: 10px;">Delete</button>
        ' . $image_preview . '
    </div>
    ';
    echo "
    <script>
    jQuery(document).ready(function($) {
        $('.falaktech-media-upload-button').on('click', function(e) {
            e.preventDefault();
            let button = $(this);
            let targetInput = $(button.data('target'));

            // Создаем новый экземпляр медиабиблиотеки
            let mediaUploader = wp.media({
                title: 'Choose image',
                button: { text: 'Use the image' },
                multiple: false
            });

            // Сброс старых обработчиков событий (если они есть)
//            mediaUploader.off('select');

            // Действие при выборе изображения
            mediaUploader.on('select', function() {
                let attachment = mediaUploader.state().get('selection').first().toJSON();
                targetInput.val(attachment.url); // Устанавливаем URL изображения в поле
                targetInput.siblings('img').remove(); // Удаляем старое превью
                targetInput.after('<img src=\"' + attachment.url + '\" alt=\"Preview\" style=\"max-width: 150px; max-height: 150px; display: block; margin-top: 10px; margin-bottom: 10px;\">');
            
                // Вручную закрываем окно медиабиблиотеки
                $('.media-modal-close').trigger('click');
            });

            // Открываем медиабиблиотеку
            mediaUploader.open();
        });

        // Кнопка удаления изображения
        $('.falaktech-media-remove-button').on('click', function(e) {
            e.preventDefault();
            let button = $(this);
            let targetInput = $(button.data('target'));
            targetInput.val(''); // Очищаем значение
            targetInput.siblings('img').remove(); // Убираем превью
        });
    });
</script>";
}
//Вывод на фронтенд
//$options = get_option('falaktech_options');
//
//// Логотип для шапки
//$header_logo = $options['falaktech_header_logo'] ?? '';
//if ($header_logo) {
//    echo '<img src="' . esc_url($header_logo) . '" alt="Логотип шапки">';
//}
//
//// Логотип для футера
//$footer_logo = $options['falaktech_footer_logo'] ?? '';
//if ($footer_logo) {
//    echo '<img src="' . esc_url($footer_logo) . '" alt="Логотип футера">';
//}


// Текстовое поле
function falaktech_text_field($args): void
{
    $options = get_option('falaktech_options');
    $id = $args['label_for'];
    $placeholder = $args['placeholder'] ?? '';
    $type = $args['type'] ?? 'text';
    $value = $options[$id] ?? '';
    echo "<input type='$type' id='$id' name='falaktech_options[$id]' value='$value' placeholder='$placeholder' style='width: 50%;' />";
}

//Поле TextArea
function falaktech_textarea_field($args) {
    $options = get_option('falaktech_options');
    $id = $args['label_for']; // Уникальный ID для связи с <label>
    $placeholder = $args['placeholder'] ?? ''; // Подсказка в поле (если есть)
    $value = $options[$id] ?? ''; // Значение, сохранённое в настройках

    echo "<textarea 
            id='" . esc_attr($id) . "' 
            name='falaktech_options[" . esc_attr($id) . "]' 
            placeholder='" . esc_attr($placeholder) . "' 
            rows='10' 
            style='width: 50%;'>" . esc_textarea($value) . "</textarea>";
}

//Вывод на фронтенд
//$options = get_option('falaktech_options');
//$address = $options['falaktech_address'] ?? '';
//
//if (!empty($address)) {
//    echo '<p>' . nl2br(esc_html($address)) . '</p>'; //nl2br() Преобразует переносы строк в HTML-теги <br>, чтобы текст в многострочном формате корректно отображался на странице.
//}

//Функция для очистки номера телефона
function falaktech_clean_phone_number($phone) {
    // Удаляем все символы, кроме цифр, плюса и дефисов
    return preg_replace('/[^+\d]/', '', $phone);
}
//Вывод на фронтенд
//$options = get_option('falaktech_options');
//$main_phone = $options['falaktech_main_phone'] ?? '';
//
//// Очищаем номер для ссылки
//$tel_link = falaktech_clean_phone_number($main_phone);
//
//// Выводим ссылку
//echo '<a href="tel:' . esc_attr($tel_link) . '">' . esc_html($main_phone) . '</a>';


// Регистрация настроек
function falaktech_register_settings(): void
{
    // Регистрация группы настроек
    register_setting('falaktech_options_group', 'falaktech_options');

    // Секция для логотипов
    add_settings_section(
        'falaktech_section_logo',
        'Logo',
        '__return_null', // Пустая функция, секция не требует описания
        'falaktech-settings'
    );

    // Поле для логотипа шапки
    add_settings_field(
        'falaktech_header_logo',
        'Header Logo',
        'falaktech_media_upload_field',
        'falaktech-settings',
        'falaktech_section_logo',
        ['label_for' => 'falaktech_header_logo']
    );

    // Поле для логотипа футера
    add_settings_field(
        'falaktech_footer_logo',
        'Footer Logo',
        'falaktech_media_upload_field',
        'falaktech-settings',
        'falaktech_section_logo',
        ['label_for' => 'falaktech_footer_logo']
    );

    // Секция для телефонов
    add_settings_section(
        'falaktech_section_phones',
        'Phone Numbers',
        '__return_null',
        'falaktech-settings'
    );

    // Основной телефон
    add_settings_field(
        'falaktech_main_phone',
        'Main Phone Number',
        'falaktech_text_field',
        'falaktech-settings',
        'falaktech_section_phones',
        ['label_for' => 'falaktech_main_phone', 'placeholder' => 'Main number', 'type' => 'tel']
    );

    // Дополнительные телефоны
    add_settings_field(
        'falaktech_phone_1',
        'Additional Phone Number 1',
        'falaktech_text_field',
        'falaktech-settings',
        'falaktech_section_phones',
        ['label_for' => 'falaktech_phone_1', 'placeholder' => 'Phone number 1', 'type' => 'tel']
    );

    add_settings_field(
        'falaktech_phone_2',
        'Additional Phone Number 2',
        'falaktech_text_field',
        'falaktech-settings',
        'falaktech_section_phones',
        ['label_for' => 'falaktech_phone_2', 'placeholder' => 'Phone number 2', 'type' => 'tel']
    );

    // Секция для адреса
    add_settings_section(
        'falaktech_section_address',
        'Address',
        '__return_null',
        'falaktech-settings'
    );

    // Поле Адрес
    add_settings_field(
        'falaktech_address',
        'Address',
        'falaktech_textarea_field',
        'falaktech-settings',
        'falaktech_section_address',
        ['label_for' => 'falaktech_address', 'placeholder' => 'Enter your address here']
    );

    // Секция для СоцСетей
    add_settings_section(
        'falaktech_section_social',
        'Social Links',
        '__return_null',
        'falaktech-settings'
    );

    // Соц Сеть 1
    add_settings_field(
        'falaktech_social-1',
        'Social 1',
        'falaktech_text_field',
        'falaktech-settings',
        'falaktech_section_social',
        ['label_for' => 'falaktech_social-1', 'placeholder' => 'Social Link 1', 'type' => 'url']
    );

    // Повторяем аналогично для других данных
}
add_action('admin_init', 'falaktech_register_settings');