<?php
/**
 * Codeable Feedbacks Shortcode.
 *
 * @package Codeable-Feedbacks
 * @since 1.0
 */

if ( ! class_exists( 'CodeableSC_FeedbackList' ) ) {
    /**
     * Shortcode class.
     *
     * @since 1.0
     */
    class CodeableSC_FeedbackList {

        /**
         * action and nonce name for ajax get list request
         *
         * @access private
         * @since 1.0
         * @var string
         */
        private $get_list_action_str = 'codeable_feedback_get_list';

        /**
         * action and nonce name for ajax get detail request
         *
         * @access private
         * @since 1.0
         * @var string
         */
        private $get_detail_action_str = 'codeable_feedback_get_detail';

        /**
         * Constructor.
         *
         * @access public
         * @since 1.0
         */
        public function __construct() {
            // register shortcode
            add_shortcode( 'codeable_feedbacklist', array( $this, 'render' ) );

            // handle ajax request for logged-in users
            add_action( 'wp_ajax_' . $this->get_list_action_str, array( $this, 'get_list' ) );
            add_action( 'wp_ajax_' . $this->get_detail_action_str, array( $this, 'get_detail' ) );
        }

        /**
         * Render the shortcode
         *
         * @access public
         * @since 1.0
         * @param  array  $args    Shortcode parameters.
         * @param  string $content Content between shortcode.
         * @return string          HTML output.
         */
        public function render( $args, $content = '' ) {

            ob_start(); ?>
            
            <div id="feedback-list-wrapper">

                <?php
                // disallow non-admin users to see the list
                if ( ! current_user_can( 'manage_options' ) ) { ?>

                    <div class="not-authorized-error">
                        <?php esc_html_e( 'You are not authorized to view the content of this page.', 'codeable' ); ?>
                    </div>

                <?php } else { ?>

                    <div class="feedback-list-container codeable-block" data-nonce="<?php echo esc_attr( wp_create_nonce( $this->get_list_action_str ) ) ?>">
                        <div class="feedback-list-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'First Name', 'codeable' ) ?></th>
                                        <th><?php esc_html_e( 'Last Name', 'codeable' ) ?></th>
                                        <th><?php esc_html_e( 'Email', 'codeable' ) ?></th>
                                        <th><?php esc_html_e( 'Subject', 'codeable' ) ?></th>
                                    </tr>
                                </thead>
                                <tbody class="empty-row">
                                    <tr>
                                        <td colspan="4"><?php esc_html_e( 'No feedbacks found.', 'codeable' ) ?></td>
                                    </tr>
                                </tbody>
                                <tbody class="feedback-list">
                                </tbody>
                            </table>

                            <div class="error-message"></div>
                            
                            <div class="codeable-loader">
                                <div class="codeable-spinner"></div>
                            </div>
                        </div><!-- end of .feedback-list-table -->

                        <nav class="codeable-pagination"></nav><!-- end of pagination -->

                    </div><!-- end of .feedback-list-container -->

                    <div class="feedback-detail-container codeable-block" data-nonce="<?php echo esc_attr( wp_create_nonce( $this->get_detail_action_str ) ) ?>">
                        <div class="feedback-detail">
                            <div class="feedback-detail-row">
                                <label><?php esc_html_e( 'ID', 'codeable' ) ?></label>
                                <div class="txt-id"></div>
                            </div>
                            <div class="feedback-detail-row">
                                <label><?php esc_html_e( 'First Name', 'codeable' ) ?></label>
                                <div class="txt-first-name"></div>
                            </div>
                            <div class="feedback-detail-row">
                                <label><?php esc_html_e( 'Last Name', 'codeable' ) ?></label>
                                <div class="txt-last-name"></div>
                            </div>
                            <div class="feedback-detail-row">
                                <label><?php esc_html_e( 'Email', 'codeable' ) ?></label>
                                <div class="txt-email"></div>
                            </div>
                            <div class="feedback-detail-row">
                                <label><?php esc_html_e( 'Subject', 'codeable' ) ?></label>
                                <div class="txt-subject"></div>
                            </div>
                            <div class="feedback-detail-row">
                                <label><?php esc_html_e( 'Message', 'codeable' ) ?></label>
                                <div class="txt-message"></div>
                            </div>
                        </div><!-- end of .feedback-detail -->

                        <div class="error-message"></div>

                        <div class="codeable-loader">
                            <div class="codeable-spinner"></div>
                        </div>

                    </div><!-- end of .feedback-detail-container -->

                <?php } // end of if ?>

            </div><!-- end of #feedback-list-wrapper -->


            <?php

            $html = ob_get_clean();

            return $html;

        }

        /**
         * Get feedback list from db and send json
         *
         * @access public
         * @since 1.0
         * @return void Content is directly sent by wp_send_json_*.
         */
        public function get_list() {
            // validation
            // user admin role validation
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array(
                    'message' => esc_html__( 'You don\'t have the permission to see the list', 'codeable' )
                ) );
            }

            // nonce validation
            if ( ! check_ajax_referer( $this->get_list_action_str, '_nonce', false ) ) {
                wp_send_json_error( array(
                    'message' => esc_html__( 'Nonce Error', 'codeable' )
                ) );
            }

            // sanitize numberic input data
            $page = ( ! isset( $_POST['page'] ) || ! is_numeric( $_POST['page'] ) ) ? 1 : $_POST['page'];
            $per_page = ( ! isset( $_POST['per_page'] ) || ! is_numeric( $_POST['per_page'] ) ) ? 10 : $_POST['per_page'];

            // fetch posts from database
            $feedback_posts = get_posts(
                array(
                    'post_type' => 'codeable_feedback',
                    'numberposts' => $per_page,
                    'paged'         => $page,
                    'post_status' => 'publish'
                )
            );

            // prepare feedback list
            $list = array();
            foreach ( $feedback_posts as $feedback_post ) {
                $item = $this->get_item_by_id( $feedback_post->ID );

                if ( $item ) {
                    $list[] = $item;
                }
            }

            // calculate total feedback
            $total_count = wp_count_posts( 'codeable_feedback' )->publish;

            // send result
            wp_send_json_success( array(
                'message' => esc_html__( 'success', 'codeable' ),
                'list' => $list,
                'total_count' => $total_count
            ) );
        }

        /**
         * Get feedback detail from db and send json
         *
         * @access public
         * @since 1.0
         * @return void Content is directly sent by wp_send_json_*.
         */
        public function get_detail() {
            // validation
            // user admin role validation
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array(
                    'message' => esc_html__( 'You don\'t have the permission to see the list', 'codeable' )
                ) );
            }

            // nonce validation
            if ( ! check_ajax_referer( $this->get_detail_action_str, '_nonce', false ) ) {
                wp_send_json_error( array(
                    'message' => esc_html__( 'Nonce Error', 'codeable' )
                ) );
            }

            // validation input data
            if ( ! isset( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {
                wp_send_json_error( array(
                    'message' => esc_html__( 'Wrong Feedback ID', 'codeable' )
                ) );
            }

            // $_POST['id'] is must a number, but double check
            $feedback_id = sanitize_text_field( $_POST['id'] );

            $feedback = get_post( $feedback_id, ARRAY_A );

            // check post_type and post_status
            if ( is_array( $feedback )
                && $feedback['post_type'] == 'codeable_feedback'
                && $feedback['post_status'] == 'publish' ) {

                $item = $this->get_item_by_id( $feedback_id );
                if ( $item ) {

                    // if found valid data send json and die
                    wp_send_json_success( array(
                        'message' => esc_html__( 'success', 'codeable' ),
                        'item' => $item
                    ) );

                }
            }

            // error handling
            wp_send_json_error( array(
                'message' => esc_html__( 'Wrong Feedback ID', 'codeable' )
            ) );
        }

        /**
         * Get all necessary fields from meta table
         *
         * @access private
         * @since 1.0
         * @return array().
         */
        private function get_item_by_id( $feedback_post_id ) {
            $meta = get_post_meta( $feedback_post_id, '' );

            // validation
            if ( ! is_array( $meta ) ) {
                return false;
            }

            // apply default data
            $meta = wp_parse_args( $meta, array(
                'first_name' => array(''),
                'last_name' => array(''),
                'email' => array(''),
                'subject' => array(''),
                'message' => array(''),
            ) );

            return array(
                'id' => $feedback_post_id,
                'first_name' => esc_attr( $meta['first_name'][0] ),
                'last_name' => esc_attr( $meta['last_name'][0] ),
                'email' => esc_attr( $meta['email'][0] ),
                'subject' => esc_attr( $meta['subject'][0] ),
                'message' => esc_attr( $meta['message'][0] ),
            );
        }

    } // End class

    new CodeableSC_FeedbackList();
} // End if()