<?php
/**
 * Server-render for the TEF-CPT Licensure Page block.
 *
 * Reads ACF fields attached to the current page and emits markup that
 * mirrors the Astro prototype. Keep the class names in sync with
 * public/styles.css so the prototype and production styling stay
 * interchangeable.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$post_id          = get_the_ID();
$hero_intro       = function_exists( 'get_field' ) ? get_field( 'hero_intro_copy', $post_id ) : '';
$eligibility_copy = function_exists( 'get_field' ) ? get_field( 'eligibility_copy', $post_id ) : '';
$how_it_works     = function_exists( 'get_field' ) ? get_field( 'how_it_works', $post_id ) : '';
$faq              = function_exists( 'get_field' ) ? get_field( 'faq', $post_id ) : '';
$contact_footer   = function_exists( 'get_field' ) ? get_field( 'contact_footer', $post_id ) : '';
$professions      = function_exists( 'get_field' ) ? get_field( 'professions', $post_id ) : array();

if ( ! is_array( $professions ) ) {
    $professions = array();
}

$regulator_class = function ( $regulator ) {
    return 'regulator-' . strtolower( (string) $regulator );
};

ob_start();
?>
<main class="page">
    <section class="hero">
        <h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
        <div class="hero-body"><?php echo wp_kses_post( $hero_intro ); ?></div>
    </section>

    <?php if ( $eligibility_copy ) : ?>
        <aside class="eligibility-teaser" role="note">
            <h2>Who can use this page</h2>
            <?php echo wp_kses_post( $eligibility_copy ); ?>
        </aside>
    <?php endif; ?>

    <?php if ( $how_it_works ) : ?>
        <section class="how-it-works">
            <h2>How it works</h2>
            <?php echo wp_kses_post( $how_it_works ); ?>
        </section>
    <?php endif; ?>

    <section class="profession-grid" aria-label="Professions">
        <h2 class="profession-grid-heading">Find your profession</h2>
        <div class="profession-grid-items">
            <?php foreach ( $professions as $i => $p ) :
                $slug             = isset( $p['slug'] ) ? sanitize_title( $p['slug'] ) : 'profession-' . $i;
                $regulator        = isset( $p['regulator'] ) ? $p['regulator'] : '';
                $form1_pdf        = isset( $p['form1_pdf'] ) ? $p['form1_pdf'] : '';
                $form1_revision   = isset( $p['form1_revision'] ) ? $p['form1_revision'] : '';
                $checklist_url    = isset( $p['checklist_url'] ) ? $p['checklist_url'] : '';
                $lic_url          = isset( $p['licensure_asana_url'] ) ? $p['licensure_asana_url'] : '';
                $lic_walkin       = ! empty( $p['licensure_walk_in_only'] );
                $lic_notes        = isset( $p['licensure_notes'] ) ? $p['licensure_notes'] : '';
                $exam_url         = isset( $p['exam_asana_url'] ) ? $p['exam_asana_url'] : '';
                $exam_walkin      = ! empty( $p['exam_walk_in_only'] );
                $exam_notes       = isset( $p['exam_notes'] ) ? $p['exam_notes'] : '';
                $card_body        = isset( $p['card_body'] ) ? $p['card_body'] : '';
                ?>
                <article class="profession-card" id="profession-<?php echo esc_attr( $slug ); ?>" data-regulator="<?php echo esc_attr( $regulator ); ?>">
                    <header class="profession-card-header">
                        <h3><?php echo esc_html( $p['name'] ?? '' ); ?></h3>
                        <span class="regulator-badge <?php echo esc_attr( $regulator_class( $regulator ) ); ?>"><?php echo esc_html( $regulator ); ?></span>
                    </header>
                    <div class="profession-card-body">
                        <?php if ( $card_body ) : ?>
                            <p><?php echo esc_html( $card_body ); ?></p>
                        <?php endif; ?>

                        <?php if ( $form1_pdf ) : ?>
                            <p class="form1-download">
                                <a href="<?php echo esc_url( $form1_pdf ); ?>" download>Download Form 1 (PDF)</a>
                                <?php if ( $form1_revision ) : ?>
                                    <small class="form1-revision"><?php echo esc_html( $form1_revision ); ?></small>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <?php if ( $checklist_url ) : ?>
                            <p class="checklist-link">
                                <a href="<?php echo esc_url( $checklist_url ); ?>" target="_blank" rel="noopener noreferrer">NYSED applicant checklist &nearr;</a>
                            </p>
                        <?php endif; ?>

                        <?php if ( $lic_url || $lic_walkin ) : ?>
                            <section class="fee-section">
                                <h4>Licensure / Application Fee</h4>
                                <?php if ( $lic_notes ) : ?>
                                    <p class="fee-notes"><?php echo esc_html( $lic_notes ); ?></p>
                                <?php endif; ?>
                                <?php if ( $lic_url ) :
                                    $modal_id = 'modal-' . $slug . '-licensure';
                                    $embed    = ( strpos( $lic_url, 'embed=' ) === false ) ? add_query_arg( 'embed', 'true', $lic_url ) : $lic_url;
                                    ?>
                                    <button type="button" class="asana-trigger" data-modal-target="<?php echo esc_attr( $modal_id ); ?>" data-asana-src="<?php echo esc_url( $embed ); ?>">Submit licensure fee request</button>
                                <?php elseif ( $lic_walkin ) : ?>
                                    <p class="walk-in-notice">Handled walk-in &mdash; contact career services.</p>
                                <?php endif; ?>
                            </section>
                        <?php endif; ?>

                        <?php if ( $exam_url || $exam_walkin ) : ?>
                            <section class="fee-section">
                                <h4>Exam Fee</h4>
                                <?php if ( $exam_notes ) : ?>
                                    <p class="fee-notes"><?php echo esc_html( $exam_notes ); ?></p>
                                <?php endif; ?>
                                <?php if ( $exam_url ) :
                                    $modal_id = 'modal-' . $slug . '-exam';
                                    $embed    = ( strpos( $exam_url, 'embed=' ) === false ) ? add_query_arg( 'embed', 'true', $exam_url ) : $exam_url;
                                    ?>
                                    <button type="button" class="asana-trigger" data-modal-target="<?php echo esc_attr( $modal_id ); ?>" data-asana-src="<?php echo esc_url( $embed ); ?>">Submit exam fee request</button>
                                <?php elseif ( $exam_walkin ) : ?>
                                    <p class="walk-in-notice">Handled walk-in &mdash; contact career services.</p>
                                <?php endif; ?>
                            </section>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ( $faq ) : ?>
        <section class="faq">
            <h2>Frequently asked questions</h2>
            <?php echo wp_kses_post( $faq ); ?>
        </section>
    <?php endif; ?>

    <?php if ( $contact_footer ) : ?>
        <footer class="contact-footer">
            <h2>Questions?</h2>
            <?php echo wp_kses_post( $contact_footer ); ?>
        </footer>
    <?php endif; ?>
</main>

<?php foreach ( $professions as $i => $p ) :
    $slug    = isset( $p['slug'] ) ? sanitize_title( $p['slug'] ) : 'profession-' . $i;
    $lic_url = isset( $p['licensure_asana_url'] ) ? $p['licensure_asana_url'] : '';
    $exam_url = isset( $p['exam_asana_url'] ) ? $p['exam_asana_url'] : '';
    $modals   = array();
    if ( $lic_url ) {
        $modals[] = array(
            'id'    => 'modal-' . $slug . '-licensure',
            'label' => 'Submit licensure fee request',
        );
    }
    if ( $exam_url ) {
        $modals[] = array(
            'id'    => 'modal-' . $slug . '-exam',
            'label' => 'Submit exam fee request',
        );
    }
    foreach ( $modals as $modal ) : ?>
        <dialog id="<?php echo esc_attr( $modal['id'] ); ?>" class="asana-modal" aria-label="<?php echo esc_attr( $modal['label'] ); ?>">
            <div class="asana-modal-header">
                <h3><?php echo esc_html( $modal['label'] ); ?></h3>
                <button type="button" class="asana-close" data-modal-close="<?php echo esc_attr( $modal['id'] ); ?>" aria-label="Close form">&times;</button>
            </div>
            <div class="asana-modal-body">
                <iframe title="<?php echo esc_attr( $modal['label'] ); ?>" data-asana-iframe="<?php echo esc_attr( $modal['id'] ); ?>" src="" width="100%" height="100%" frameborder="0" loading="lazy"></iframe>
            </div>
        </dialog>
    <?php endforeach;
endforeach; ?>
<?php
echo ob_get_clean();
