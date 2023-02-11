<?php

class RSS_Importer_Gutenberg_Block_Callback
{
    public static function callback_rss_importer_block_type($attributes)
    {

        isset($attributes['className']) && $attributes['className'] ? $className = 'class="' . $attributes['className'] . '"' : $className = '';
        isset($attributes['selectedBeitragType']) && $attributes['selectedBeitragType'] ? $selectedBeitragType = $attributes['selectedBeitragType'] : $selectedBeitragType = '';
        isset($attributes['selectedContent']) && $attributes['selectedContent'] ? $selectedContent = $attributes['selectedContent'] : $selectedContent = 'content';
        isset($attributes['descriptionLimit']) && $attributes['descriptionLimit'] ? $descriptionLimit = $attributes['descriptionLimit'] : $descriptionLimit = 0;
        isset($attributes['maxOutput']) && $attributes['maxOutput'] ? $maxOutput = $attributes['maxOutput'] : $maxOutput = 0;
        isset($attributes['selectedOrder']) && $attributes['selectedOrder'] ? $selectedOrder = $attributes['selectedOrder'] : $selectedOrder = 1;
        isset($attributes['contentStripTags']) && $attributes['contentStripTags'] ? $contentStripTags = $attributes['contentStripTags'] : $contentStripTags = 1;

        $attr = [
            'className' => $className,
            'selectedBeitragType' => $selectedBeitragType,
            'selectedContent' => $selectedContent,
            'descriptionLimit' => $descriptionLimit,
            'maxOutput' => $maxOutput,
            'selectedOrder' => $selectedOrder,
            'contentStripTags' => $contentStripTags
        ];

        global $posts;
        $term = [];
        $feedImport = [];
        $metaArr = [];
        if($selectedBeitragType){

           $args =  sprintf('WHERE r.id=%d', $selectedBeitragType);
           $feed = apply_filters(WP_RSS_FEED_IMPORTER_BASENAME.'/get_rss_import', $args, false);
           if($feed->status) {
               switch ($selectedOrder) {
                   case 1:
                       $orderBy = 'date';
                       $order = 'DESC';
                       break;
                   case 2:
                       $orderBy = 'date';
                       $order = 'ASC';
                       break;
                   case 3:
                       $orderBy = 'menu_order';
                       $order = 'DESC';
                       break;
                   default:
                       $order = 'date';
                       $orderBy = 'DESC';
               }

               $maxOutput ? $numberposts = $maxOutput : $numberposts = -1;

               $feed = $feed->record;
               $feedImport = (array) $feed;
               $term = get_term($feed->post_taxonomy);
               $postArgs = [
                   'post_type' => $feed->post_type,
                   'numberposts' => $numberposts,
                   'orderby' => $orderBy,
                   'order' => $order,
                   'tax_query' => array(
                       array(
                           'taxonomy' => $term->taxonomy,
                           'field' => 'term_id',
                           'terms' => $term->term_id,
                       )
                   )
               ];
               $posts = get_posts($postArgs);
               if($posts) {
                   foreach($posts as $tmp) {
                       $meta = (array) apply_filters(WP_RSS_FEED_IMPORTER_BASENAME.'/get_rss_post_meta', $tmp->ID);
                       $metaArr[] = $meta;
                   }
               }
           }
        }

        return apply_filters('gutenberg_block_rss_importer_callback', $posts, $attr, $metaArr, $term, $feedImport);
    }

    public function gutenberg_block_rss_importer_filter() {
        ob_start();
        return ob_get_clean();
    }
}