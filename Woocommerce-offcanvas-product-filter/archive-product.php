

<!-- Offcanvas для фильтра товаров -->
<button style="z-index: 100;" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" aria-controls="filterOffcanvas">
    <?php _e('Фильтр', 'woocommerce'); ?>
</button>

<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 id="filterOffcanvasLabel"><?php _e('Фильтр товаров', 'woocommerce'); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="product-filter-form">
            <!-- Диапазон цен -->
            <h6><?php _e('Цена', 'woocommerce'); ?></h6>
            <input type="hidden" id="price-range" readonly>
            <div id="price-slider-range"></div>
            <div class="price-slider-container">
                <p>от <span id="min-price-label"></span></p>
                <p>до <span id="max-price-label"></span></p>
            </div>


            <!-- Фильтрация по категориям -->
            <div class="accordion" id="filterAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingCategories">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategories" aria-expanded="true" aria-controls="collapseCategories">
                            <?php _e('Категории', 'woocommerce'); ?>
                        </button>
                    </h2>
                    <div id="collapseCategories" class="accordion-collapse collapse show" aria-labelledby="headingCategories">
                        <div class="accordion-body">
                            <?php
                            $categories = get_terms(array(
                                'taxonomy' => 'product_cat',
                                'hide_empty' => true
                            ));
                            foreach ($categories as $category) {
                                echo '<label><input type="checkbox" name="categories[]" value="' . esc_attr($category->term_id) . '"> ' . esc_html($category->name) . '</label>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Фильтрация по меткам -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTags">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTags" aria-expanded="false" aria-controls="collapseTags">
                            <?php _e('Метки', 'woocommerce'); ?>
                        </button>
                    </h2>
                    <div id="collapseTags" class="accordion-collapse collapse" aria-labelledby="headingTags">
                        <div class="accordion-body">
                            <?php
                            $tags = get_terms('product_tag');
                            foreach ($tags as $tag) {
                                echo '<label><input type="checkbox" name="tags[]" value="' . esc_attr($tag->term_id) . '"> ' . esc_html($tag->name) . '</label>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Фильтрация по атрибутам -->
                <?php
                $attributes = wc_get_attribute_taxonomies();
                foreach ($attributes as $attribute) {
                    $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);
                    $terms = get_terms($taxonomy);
                    if (!empty($terms)) {
                        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo esc_attr($taxonomy); ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo esc_attr($taxonomy); ?>" aria-expanded="false" aria-controls="collapse<?php echo esc_attr($taxonomy); ?>">
                                    <?php echo esc_html($attribute->attribute_label); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo esc_attr($taxonomy); ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo esc_attr($taxonomy); ?>">
                                <div class="accordion-body">
                                    <?php foreach ($terms as $term) : ?>
                                        <label><input type="checkbox" name="attributes[<?php echo esc_attr($taxonomy); ?>][]" value="<?php echo esc_attr($term->term_id); ?>"> <?php echo esc_html($term->name); ?></label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php }
                }?>
            </div>

            <!-- Кнопки -->
            <div class="mt-3">
                <button type="button" id="apply-filter" class="btn btn-primary"><?php _e('Применить фильтр', 'woocommerce'); ?></button>
                <button type="button" id="reset-filter" class="btn btn-secondary"><?php _e('Сбросить фильтр', 'woocommerce'); ?></button>
            </div>
        </form>
    </div>
</div>
<?php if ( woocommerce_product_loop() ) { ?>
<!-- Добавляем селектор сортировки -->
<section>
    <div class="container-xxl">
        <div class="catalog-sort">
            <label for="sort-by"><?php _e('сортировать:', 'woocommerce'); ?></label>
            <select id="sort-by">
                <option value="menu_order" selected><?php _e('сортировать по:', 'woocommerce'); ?></option>
                <option value="date"><?php _e('по новизне', 'woocommerce'); ?></option>
                <option value="popularity"><?php _e('по популярности', 'woocommerce'); ?></option>
                <option value="price"><?php _e('цена: по возрастанию', 'woocommerce'); ?></option>
                <option value="price-desc"><?php _e('цена: по убыванию', 'woocommerce'); ?></option>
            </select>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Получаем параметр `orderby` из URL
                const urlParams = new URLSearchParams(window.location.search);
                const orderby = urlParams.get('orderby');

                // Если параметр `orderby` существует, устанавливаем его значение в селекте
                if (orderby) {
                    document.getElementById('sort-by').value = orderby;
                }
            });
        </script>
    </div>
</section>
<section id="filtered-products"  class="catalog-product">
    <div class=" container-xxl product-wrapper">

        <?php  woocommerce_product_loop_start();

        if ( wc_get_loop_prop( 'total' ) ) {
            while ( have_posts() ) {
                the_post();

                /**
                 * Hook: woocommerce_shop_loop.
                 */
                do_action( 'woocommerce_shop_loop' );

                wc_get_template_part( 'content', 'product' );
            }
        }

        woocommerce_product_loop_end();
        ?>
    </div>

    <?php
    $total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
    $current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
    $base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
    $format  = isset( $format ) ? $format : '';

    if ( $total <= 1 ) {
        return;
    }
    ?>
    <div id="main-pagination" class="pagination">
        <?php
        echo paginate_links(
            apply_filters(
                'woocommerce_pagination_args',
                array(
                    'base'      => $base,
                    'format'    => $format,
                    'add_args'  => false,
                    'current'   => max( 1, $current ),
                    'total'     => $total,
                    'prev_text' => '«',
                    'next_text' => '»',
                    'type'      => 'list',
                    'end_size'  => 3,
                    'mid_size'  => 3,
                )
            )
        );
        ?>
    </div>
</section>

    <?php
} else {
    wc_no_products_found();
}
?>