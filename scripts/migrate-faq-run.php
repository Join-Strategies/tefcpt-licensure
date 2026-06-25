<?php
/**
 * WP-CLI eval-file: migrate old `faq` wysiwyg to `faq_items` repeater.
 * Usage: wp eval-file /tmp/migrate-faq-run.php
 * Set $dry_run = true to preview without writing.
 */

$page_id = 2140;
$dry_run = false;

$raw = get_post_meta( $page_id, 'faq', true );
if ( ! $raw ) {
	WP_CLI::error( 'No value found for meta key `faq` on page ' . $page_id );
}

$dom = new DOMDocument();
libxml_use_internal_errors( true );
$dom->loadHTML( '<?xml encoding="utf-8"?><div id="faq-root">' . $raw . '</div>' );
libxml_clear_errors();

$root     = $dom->getElementById( 'faq-root' );
$children = iterator_to_array( $root->childNodes );
$items    = [];
$i        = 0;
$count    = count( $children );

while ( $i < $count ) {
	$node = $children[ $i ];

	if ( $node->nodeType !== XML_ELEMENT_NODE || strtolower( $node->nodeName ) !== 'p' ) {
		$i++;
		continue;
	}

	// First element child of <p> must be <strong> (the question).
	$first = null;
	foreach ( $node->childNodes as $child ) {
		if ( $child->nodeType === XML_ELEMENT_NODE ) {
			$first = $child;
			break;
		}
	}
	if ( ! $first || strtolower( $first->nodeName ) !== 'strong' ) {
		$i++;
		continue;
	}

	$question = trim( $first->textContent );

	// Collect inline answer (content after <br> within same <p>).
	$after_br   = false;
	$inline_buf = '';
	foreach ( $node->childNodes as $child ) {
		if ( ! $after_br ) {
			if ( $child->nodeType === XML_ELEMENT_NODE && strtolower( $child->nodeName ) === 'br' ) {
				$after_br = true;
			}
			continue;
		}
		$inline_buf .= $dom->saveHTML( $child );
	}

	$answer_html = trim( $inline_buf ) !== '' ? '<p>' . trim( $inline_buf ) . '</p>' : '';

	// Check if next sibling element is a list.
	$j = $i + 1;
	while ( $j < $count && $children[ $j ]->nodeType !== XML_ELEMENT_NODE ) {
		$j++;
	}
	if ( $j < $count && in_array( strtolower( $children[ $j ]->nodeName ), [ 'ul', 'ol' ] ) ) {
		$answer_html .= $dom->saveHTML( $children[ $j ] );
		$i = $j;
	}

	$items[] = [
		'question' => $question,
		'answer'   => trim( $answer_html ),
	];

	$i++;
}

if ( empty( $items ) ) {
	WP_CLI::error( 'Parsed 0 items — check HTML structure.' );
}

WP_CLI::log( sprintf( 'Parsed %d items:', count( $items ) ) );
foreach ( $items as $idx => $item ) {
	WP_CLI::log( sprintf( '  [%d] %s', $idx + 1, $item['question'] ) );
}

if ( $dry_run ) {
	WP_CLI::success( 'Dry run — no changes written.' );
	return;
}

$result = update_field( 'faq_items', $items, $page_id );

if ( $result !== false ) {
	WP_CLI::success( sprintf( 'Wrote %d FAQ items to faq_items on page %d.', count( $items ), $page_id ) );
} else {
	WP_CLI::error( 'update_field returned false — verify ACF is active and field key is registered.' );
}
