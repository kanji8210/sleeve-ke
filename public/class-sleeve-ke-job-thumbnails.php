<?php
/**
 * Helper class to encapsulate job thumbnail / company logo display logic.
 *
 * Keeps thumbnail rendering and URL resolution in one place so the main
 * job display class stays focused on layout and data.
 */
class Sleeve_KE_Job_Thumbnails {

    /**
     * Return the company logo URL for a job post.
     * Falls back to the post thumbnail if no `company_logo` meta is present.
     *
     * @param int $post_id
     * @return string
     */
    public function get_logo_url( $post_id ) {
        $post_id = intval( $post_id );
        if ( ! $post_id ) {
            return '';
        }

        $meta = get_post_meta( $post_id, 'company_logo', true );
        if ( ! empty( $meta ) ) {
            return esc_url_raw( $meta );
        }

        if ( has_post_thumbnail( $post_id ) ) {
            $url = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
            return $url ? esc_url_raw( $url ) : '';
        }

        return '';
    }

    /**
     * Build the company logo HTML block. If no logo is available or the
     * caller disables logos via $show flag, returns empty string.
     *
     * @param int $post_id
     * @param string $alt
     * @param bool|string $show True/false or 'true'/'false' (shortcode attributes)
     * @return string HTML
     */
    public function render_logo_html( $post_id, $alt = '', $show = true ) {
        // Normalize show value: accept 'true' string from shortcode attrs
        if ( is_string( $show ) ) {
            $show = ( strtolower( $show ) === 'true' );
        }
        if ( ! $show ) {
            return '';
        }

        $url = $this->get_logo_url( $post_id );
        if ( empty( $url ) ) {
            return '';
        }

        $alt = trim( $alt );
        $alt_attr = $alt !== '' ? esc_attr( $alt ) : '';

        return sprintf(
            '<div class="company-logo"><img src="%s" alt="%s" loading="lazy" /></div>',
            esc_url( $url ),
            $alt_attr
        );
    }
}
