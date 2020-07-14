<?php
/**
 * Plugin Name: Markdown To HTML Of XML-RPC
 * Description: 本插件主要配合 MarsEdit使用，仅对通过xmlrpc上传的 Markdown文章进行编译。原 Markdown文本保存到名为 raw_markdown的自定义栏目中，在通过xmlrpc获取文章时，会优先返回原 Markdown文本。
 * Plugin URI: https://hynote.cn/
 * Author: LaoGuo
 * Author URI: https://hynote.cn/
 * Version: 1.0
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( ! class_exists( 'XML_RPC_ParseDown' ) ):
    class XML_RPC_ParseDown {

        public static function on_load() {
            add_filter( 'xmlrpc_prepare_post', __CLASS__ . '::ReturnRaw', '99', 3 );
            add_filter( 'xmlrpc_wp_insert_post_data', __CLASS__ . '::ToHtml', '99', 2 );
        }

        /**
         * [ReturnRaw 返回原始Markdown文本]
         * @param [type] $_post  [description]
         * @param [type] $post   [description]
         * @param [type] $fields [description]
         */
        public function ReturnRaw($_post, $post, $fields)
        {
            $raw_markdown = get_post_meta( $_post['post_id'], 'raw_markdown', true );

            if ( $raw_markdown ) {
                $_post['post_content'] = $raw_markdown;
            }

            return $_post;
        }

        /**
         * [ToHtml 调用 Parsedown将Markdown转为HTML，并把原始文本存储到名为raw_markdown的自定义字段中]
         * @param [type] $post_data      [description]
         * @param [type] $content_struct [description]
         */
        public function ToHtml($post_data, $content_struct)
        {
            $Parsedown    = new ParsedownExtra();
            $post_content = wp_unslash( $post_data['post_content'] );

            update_post_meta( $post_data['ID'], 'raw_markdown', $post_content );

            $post_data['post_content'] = $Parsedown->text( $post_content );

            return $post_data;
        }
    }
endif;

add_action( 'plugins_loaded', 'XML_RPC_ParseDown::on_load' );
