<?php

class RSS_Importer_Rest_Endpoint
{

    protected Wp_Rss_Feed_Importer $main;
    protected string $basename;

    public function __construct(string $basename, Wp_Rss_Feed_Importer $main)
    {
        $this->main = $main;
        $this->basename = $basename;
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_rss_importer_routes()
    {
        $version = '1';
        $namespace = 'rss-importer/v' . $version;
        $base = '/';

        @register_rest_route(
            $namespace,
            $base . '(?P<method>[\S]+)',

            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'rss_importer_endpoint_get_response'),
                'permission_callback' => array($this, 'permissions_check')
            )
        );
    }

    /**
     * Get one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function rss_importer_endpoint_get_response(WP_REST_Request $request)
    {

        $method = (string)$request->get_param('method');
        if (!$method) {
            return new WP_Error(404, ' Method failed');
        }

        return $this->get_method_item($method);

    }

    /**
     * GET Post Meta BY ID AND Field
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_method_item($method)
    {
        if (!$method) {
            return new WP_Error(404, ' Method failed');
        }
        $response = new stdClass();
        switch ($method) {
            case 'get-data':
                $importsArr = [];
                $args = 'WHERE r.active=1';
                $imports = apply_filters($this->basename.'/get_rss_import', $args);
                $def = [
                    '0' => [
                        'label' => __('select' .'...', 'wp-rss-feed-importer'),
                        'value' => 0
                    ]
                ];
                if(!$imports->status){
                    $response->imports = $def;
                } else {
                    foreach ($imports->record as $tmp){
                        $item = [
                            'label' => $tmp->bezeichnung,
                            'value' => $tmp->id
                        ];
                        $importsArr[] = $item;
                    }
                    if(!$importsArr){
                        $importsArr = $def;
                    }
                    $importsArr = array_merge_recursive($def, $importsArr);
                    $response->imports = $importsArr;
                }

                $selectContent = [
                    '0' => [
                        'label' => __('Content', 'wp-rss-feed-importer'),
                        'value' => 'content'
                    ],
                    '1' => [
                        'label' => __('Description', 'wp-rss-feed-importer'),
                        'value' => 'description'
                    ]
                ];

                $response->content = $selectContent;

                $sortOut = [
                    '0' => [
                        'label' => __('Date descending', 'wp-rss-feed-importer'),
                        'value' => 1
                    ],
                    '1' => [
                        'label' => __('Date ascending', 'wp-rss-feed-importer'),
                        'value' => 2
                    ],
                    '2' => [
                        'label' => __('Menu Order', 'wp-rss-feed-importer'),
                        'value' => 3
                    ],
                ];
                $response->order = $sortOut;
                break;
        }
        return new WP_REST_Response($response, 200);
    }

    /**
     * Get a collection of items.
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return void
     */
    public function get_items(WP_REST_Request $request)
    {


    }

    /**
     * Check if a given request has access.
     *
     * @return bool
     */
    public function permissions_check(): bool
    {
        return current_user_can('edit_posts');
    }
}