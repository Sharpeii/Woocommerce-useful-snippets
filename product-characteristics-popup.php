<?php
/**
 * Snippet Name: Additional Product Сharacteristics for WooCommerce
 * Description: Создание и вывод характеристик товара в виде таблицы в OffCanvas
 * Version: 1.0
 * Author: Sharpeii
 */


// Добавляем вкладку для характеристик товара
add_filter('woocommerce_product_data_tabs', 'add_product_characteristics_tab');
function add_product_characteristics_tab($tabs) {
    $tabs['product_characteristics'] = array(
        'label' => __('Характеристики', 'woocommerce'),
        'target' => 'product_characteristics_data',
        'priority' => 61,
        'class' => array('show_if_simple', 'show_if_variable', 'show_if_external', 'show_if_grouped'),
    );
    return $tabs;
}

// Добавляем метаполя для характеристик товара
add_action('woocommerce_product_data_panels', 'add_product_characteristics_fields');
function add_product_characteristics_fields(): void
{
    global $post;
    ?>
    <div id="product_characteristics_data" class="panel woocommerce_options_panel">
        <div class="options_group">
            <div id="product-characteristics-wrapper">
                <?php
                $characteristics = get_post_meta($post->ID, '_product_characteristics', true);
                if (!empty($characteristics) && is_array($characteristics)) {
                    foreach ($characteristics as $index => $characteristic) {
                        ?>
                        <div class="product-characteristic-item">
                            <input type="text" name="product_characteristics[<?php echo $index; ?>][name]" placeholder="Название характеристики" value="<?php echo esc_attr($characteristic['name']); ?>" />
                            <input type="text" name="product_characteristics[<?php echo $index; ?>][value]" placeholder="Значение характеристики" value="<?php echo esc_attr($characteristic['value']); ?>" />
                            <button type="button" class="remove-characteristic button"><?php _e('Удалить', 'woocommerce'); ?></button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <button type="button" id="add-characteristic" class="button" style="margin-top: 10px;"><?php _e('Добавить характеристику', 'woocommerce'); ?></button>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#add-characteristic').on('click', function() {
                const index = $('#product-characteristics-wrapper .product-characteristic-item').length;
                $('#product-characteristics-wrapper').append(`
                    <div class="product-characteristic-item">
                        <input type="text" name="product_characteristics[${index}][name]" placeholder="Название характеристики" style="margin-top: 10px;"/>
                        <input type="text" name="product_characteristics[${index}][value]" placeholder="Значение характеристики" style="margin-top: 10px;"/>
                        <button type="button" class="remove-characteristic button" style="margin-top: 10px;">Удалить</button>
                    </div>
                `);
            });

            $(document).on('click', '.remove-characteristic', function() {
                $(this).parent().remove();
            });
        });
    </script>
    <?php
}

// Сохранение характеристик товара
add_action('woocommerce_process_product_meta', 'save_product_characteristics');
function save_product_characteristics($post_id): void
{
    if (isset($_POST['product_characteristics'])) {
        $characteristics = array_map(function($item) {
            return array(
                'name' => sanitize_text_field($item['name']),
                'value' => sanitize_text_field($item['value']),
            );
        }, $_POST['product_characteristics']);
        update_post_meta($post_id, '_product_characteristics', $characteristics);
    } else {
        delete_post_meta($post_id, '_product_characteristics');
    }
}

// Функция для вывода характеристик во всплывающем окне
function display_product_characteristics(): void
{
    global $product;
    $characteristics = get_post_meta($product->get_id(), '_product_characteristics', true);

    if (!empty($characteristics) && is_array($characteristics)) {
        ?>
        <button class="product__about-btn" data-bs-toggle="offcanvas" data-bs-target="#characteristicsOffcanvas" aria-controls="characteristicsOffcanvas">
            об изделии
            <svg width="7" height="13" viewBox="0 0 7 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6.82278 6.05826L1.33797 0.232472C1.09873 -0.0761478 0.717722 -0.0761478 0.478481 0.232472L0 0.740507L4.96203 6.05826C5.20127 6.31465 5.20127 6.67075 4.96203 6.97937L0 12.2449L0.478481 12.8052C0.717722 13.0615 1.09873 13.0615 1.33797 12.8052L6.82278 6.97937C7.05759 6.67075 7.05759 6.31465 6.82278 6.05826Z" fill="#464646"/>
            </svg>
        </button>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="characteristicsOffcanvas" aria-labelledby="characteristicsOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 id="characteristicsOffcanvasLabel">Характеристики</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <table class="table">
                    <tbody>
                    <?php foreach ($characteristics as $characteristic) : ?>
                        <tr>
                            <td class="characteristics-name"><?php echo esc_html($characteristic['name']); ?></td>
                            <td class="characteristics-value"><?php echo esc_html($characteristic['value']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <h5>Описание</h5>
                <p class="product-desc"><?php echo $product->get_description()?></p>
            </div>
        </div>
        <?php
    }
}

// Добавляем вывод в нужное место шаблона страницы товара
//add_action('woocommerce_single_product_summary', 'display_product_characteristics', 25);
//display_product_characteristics();