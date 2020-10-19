<?php
/**
 * Codeable Feedbacks Shortcode.
 *
 * @package Codeable-Feedbacks
 * @since 1.0
 */

if ( ! class_exists( 'CodeableSC_FeedbackForm' ) ) {
    /**
     * Shortcode class.
     *
     * @since 1.0
     */
    class CodeableSC_FeedbackForm {

        /**
         * Name string for ajax form submit action and nonce
         *
         * @access private
         * @since 1.0
         * @var string
         */
        private $submit_action_str = 'codeable_feedback_submit';

        /**
         * Constructor.
         *
         * @access public
         * @since 1.0
         */
        public function __construct() {
            // register shortcode
            add_shortcode( 'codeable_feedbackform', array( $this, 'render' ) );

            // handle ajax submit request
            add_action( 'wp_ajax_' . $this->submit_action_str, array( $this, 'submit' ) );
            add_action( 'wp_ajax_nopriv_' . $this->submit_action_str, array( $this, 'submit' ) );
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

            // if user logged in prepare pre-filled data
            $user_firstname = '';
            $user_lastname = '';
            $user_email = '';

            if ( is_user_logged_in() ) {
                $current_user = wp_get_current_user();

                $user_firstname = $current_user->user_firstname;
                $user_lastname = $current_user->user_lastname;
                $user_email = $current_user->user_email;
            }

            ob_start(); ?>
            
            <div id="feedback-form-wrapper" class="codeable-block">
                <div class="success-message">
                    <?php esc_html_e( 'Thank you for sending us your feedback', 'codeable' ) ?>
                </div>
                <div class="feedback-form-container">
                    <div class="feedback-form-header">
                        <h2 class="feedback-form-title"><?php esc_html_e( 'Submit your feedback', 'codeable' ) ?></h2>
                    </div><!-- end of .feedback-form-header -->

                    <div class="feedback-form-body">
                        <form class="feedback-form" action="" method="post" onsubmit="feedback_form_submit(event)">
                            <div class="input-block">
                                <label for="first-name"><?php esc_html_e( 'First Name', 'codeable' ) ?></label>
                                <input id="first-name" name="first_name" type="text" class="input-field" value="<?php echo esc_attr( $user_firstname ) ?>" required>
                            </div>

                            <div class="input-block">
                                <label for="last-name"><?php esc_html_e( 'Last Name', 'codeable' ) ?></label>
                                <input id="last-name" name="last_name" type="text" class="input-field" value="<?php echo esc_attr( $user_lastname ) ?>" required>
                            </div>

                            <div class="input-block">
                                <label for="email"><?php esc_html_e( 'Email', 'codeable' ) ?></label>
                                <input id="email" name="email" type="email" class="input-field" value="<?php echo esc_attr( $user_email ) ?>" required>
                            </div>

                            <div class="input-block">
                                <label for="subject"><?php esc_html_e( 'Subject', 'codeable' ) ?></label>
                                <input id="subject" name="subject" type="text" class="input-field" required>
                            </div>

                            <div class="input-block">
                                <label for="message"><?php esc_html_e( 'Message', 'codeable' ) ?></label>
                                <textarea id="message" name="message" class="input-field" required></textarea>
                            </div>

                            <div class="submit-block">
                                <input type="hidden" name="action" value="<?php echo esc_attr( $this->submit_action_str ) ?>">
                                <?php wp_nonce_field( $this->submit_action_str ) ?>
                                <button type="Submit"><?php esc_html_e( 'Submit', 'codeable' ) ?></button>
                            </div>
                            <div class="error-message">
                                <?php esc_html_e( 'Something is wrong!', 'codeable' ) ?>
                            </div>

                        </form>
                    </div><!-- end of .feedback-form-body -->

                </div><!-- end of .feedback-form-container -->

                <div class="codeable-loader">
                    <div class="codeable-spinner"></div>
                </div>

            </div><!-- end of #feedback-form-wrapper -->

            <?php

            $html = ob_get_clean();

            return $html;

        }

        /**
         * Save feedback ajax request form data to database
         *
         * @access public
         * @since 1.0
         * @return void Content is directly sent by wp_send_json_*.
         */
        public function submit() {
            // validation
            // nonce validation
            if ( ! check_ajax_referer( $this->submit_action_str, false, false ) ) {
                wp_send_json_error( array(
                    'message' => esc_html__( 'Nonce Error', 'codeable' )
                ) );
            }

            // required validation
            $required_fields = array( 'first_name', 'last_name', 'email', 'subject', 'message' );
            foreach ( $required_fields as $field ) {
                if ( ! isset( $_POST[ $field ] ) ) {
                    wp_send_json_error( array(
                        'message' => esc_html( sprintf( __( 'Field %s is required', 'codeable' ), $field ) )
                    ) );
                }

                $$field = $_POST[ $field ];
            }

            // email validation
            if ( ! is_email( $email ) ) {
                wp_send_json_error( array(
                    'message' => esc_html__( 'Email is not valid', 'codeable' )
                ) );
            }

            // sanitize input data
            $first_name = sanitize_text_field( $first_name );
            $last_name = sanitize_text_field( $last_name );
            $email = sanitize_email( $email );
            $subject = sanitize_text_field( $subject );
            $message = sanitize_textarea_field( $message );

            // save data
            $postarr = array(
                'post_type' => 'codeable_feedback',
                'post_title' => sprintf( 'Feedback from %s %s', $first_name, $last_name ),
                'post_status'   => 'publish',
                'meta_input' => array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message
                )
            );

            $post_id = wp_insert_post( $postarr );

            // handle error
            if ( is_wp_error( $post_id ) ) {
                wp_send_json_error( array(
                    'message' => $post_id->get_error_message()
                ) );
            }

            // return result
            wp_send_json_success( array(
                'message' => 'success'
            ) );
        }

    } // End class

    new CodeableSC_FeedbackForm();
} // End if()