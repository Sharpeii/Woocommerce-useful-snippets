<?php
/**
 * Snippet Name: Additional Product Block for WooCommerce
 * Description: Блок для страницы товара для вывода выбранных администратором дополнительных товаров c использованием их артикулов.
 * Version: 1.0
 * Author: Sharpeii
 */
// Регистрация таксономии "бренд" для товаров
function register_product_brand_taxonomy(): void
{
    $labels = array(
        'name' => __('Бренды', 'woocommerce'),
        'singular_name' => __('Бренд', 'woocommerce'),
        'search_items' => __('Найти бренд', 'woocommerce'),
        'all_items' => __('Все бренды', 'woocommerce'),
        'parent_item' => __('Родительский бренд', 'woocommerce'),
        'parent_item_colon' => __('Родительский бренд:', 'woocommerce'),
        'edit_item' => __('Редактировать бренд', 'woocommerce'),
        'update_item' => __('Обновить бренд', 'woocommerce'),
        'add_new_item' => __('Добавить новый бренд', 'woocommerce'),
        'new_item_name' => __('Название нового бренда', 'woocommerce'),
        'menu_name' => __('Бренды', 'woocommerce'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'brand'),
    );

    register_taxonomy('product_brand', array('product'), $args);
}
add_action('init', 'register_product_brand_taxonomy');


//  Вывод дополнительных товаров на карточке основного товара

// Добавляем новую вкладку в админку WooCommerce
add_filter('woocommerce_product_data_tabs', 'add_custom_sku_tab');
function add_custom_sku_tab($tabs) {
    $tabs['additional_skus'] = array(
        'label' => __('Дополните образ', 'woocommerce'),
        'target' => 'additional_skus_data',
        'class' => array('show_if_simple', 'show_if_variable', 'show_if_external', 'show_if_grouped'),
        'priority' => 61, // Позиция вкладки
    );
    return $tabs;
}

// Добавляем поля для ввода артикулов
add_action('woocommerce_product_data_panels', 'add_custom_sku_fields');
function add_custom_sku_fields(): void
{
    global $post;
    ?>
    <div id="additional_skus_data" class="panel woocommerce_options_panel">
        <div class="options_group">
            <?php for ($i = 1; $i <= 4; $i++) : ?>
                <?php
                woocommerce_wp_text_input(array(
                    'id' => '_additional_sku_' . $i,
                    'label' => __('Артикул товара ' . $i, 'woocommerce'),
                    'description' => __('Введите артикул дополнительного товара.', 'woocommerce'),
                    'desc_tip' => true,
                    'value' => get_post_meta($post->ID, '_additional_sku_' . $i, true),
                ));
                ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php
}

// Сохранение значений метаполей
add_action('woocommerce_process_product_meta', 'save_custom_sku_fields');
function save_custom_sku_fields($post_id): void
{
    for ($i = 1; $i <= 4; $i++) {
        if (isset($_POST['_additional_sku_' . $i])) {
            update_post_meta($post_id, '_additional_sku_' . $i, sanitize_text_field($_POST['_additional_sku_' . $i]));
        }
    }
}

// Функция для вывода дополнительных товаров по артикулам
function display_additional_products($product_id): void
{
    $additional_skus = array();

    // Получаем артикулы из метаполей
    for ($i = 1; $i <= 4; $i++) {
        $sku = get_post_meta($product_id, '_additional_sku_' . $i, true);
        if (!empty($sku)) {
            $additional_skus[] = $sku;
        }
    }

    if (!empty($additional_skus)) {
        global $product;
        ?>

        <section class="additional-products">
            <div class="container-xl">
                <div class="row">
                    <div class="col-12 additional-products-title">
                        <h3>Дополните образ</h3>
                    </div>
                    <div class="col-md-6">
                        <div class="additional-products-img-wrapper">
                            <?php $product_image_id =$product->get_image_id();?>
                            <img class="swiper-img" src="<?php echo esc_url(wp_get_attachment_image_url ($product_image_id, 'full'))?>" alt="image" />
                        </div>
                    </div>
                    <div class="col-md-6 additional-products-right-wrapper">
                        <?php
                        // Поиск товаров по артикулам и вывод карточек
                        foreach ($additional_skus as $sku) {
                            $args = array(
                                'post_type' => 'product',
                                'meta_query' => array(
                                    array(
                                        'key' => '_sku', //Ключ, под которым WooCommerce сохраняет артикул
                                        'value' => $sku,
                                        'compare' => '='
                                    )
                                )
                            );
                            $query = new WP_Query($args);
                            if ($query->have_posts()) {
                                while ($query->have_posts()) {
                                    $query->the_post();
                                    global $product;
                                    // Получаем бренд дополнительного товара
                                    $product_brands = wp_get_post_terms(get_the_ID(), 'product_brand');
                                    $product_brand_name = (!is_wp_error($product_brands) && !empty($product_brands)) ? esc_html($product_brands[0]->name) :'' ;?>
                                    <!--                     Вывод карточки товара-->
                                    <div class="col-12 col-md-5 additional-products-card">
                                        <a href="<?php echo esc_url(get_permalink())?>">
                                            <div class="additional-products-card-img-wrapper">
                                                <?php echo woocommerce_get_product_thumbnail();?>
                                                <?php if (! empty($product_brand_name)){?>
                                                    <div class="additional-products-card-brand" style="background-image: url('<?php echo get_template_directory_uri() . '/assets/css/dndl/img/brend.svg';?>');">
                                                        <p><?php echo $product_brand_name;?></p>
                                                    </div>
                                                <?php }?>
                                            </div>
                                            <p>  <?php echo get_the_title();?></p>
                                            <p> <?php echo $product->get_price_html(); ?></p>

                                        </a>

                                    </div>
                                    <?php
                                }
                                wp_reset_postdata();
                            }
                        }
                        ?>

                    </div>
                </div>
            </div>
        </section>

        <?php
    }
}

// Вставка функции в нужное место шаблона страницы товара
//display_additional_products($product->get_id()); //Либо напрямую
//add_action('woocommerce_after_single_product_summary', 'display_additional_products', 15); //Либо через хук