<?php
/**
 * WP-CLI eval-file script: migrate old `faq` wysiwyg into `faq_items` repeater.
 * Run: wp eval-file migrate-faq.php --page-id=2140 [--dry-run]
 *
 * Parses the old field's HTML where each item is:
 *   <p><strong>Question</strong><br>Answer text</p>
 *   <p><strong>Question</strong></p><ul>...</ul>
 */

$page_id = (int) ( WP_CLI\Utils\get_flag_value( $assoc_args, 'page-id', 0 ) );
$dry_run = WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false );

if ( ! $page_id ) {
	WP_CLI::error( 'Pass --page-id=<id>' );
}

$raw = get_post_meta( $page_id, 'faq', true );
if ( ! $raw ) {
	WP_CLI::error( 'No value found for meta key `faq` on page ' . $page_id );
}

// Wrap in a root element, use HTML5 parsing mode via mbstring-safe wrapper.
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

	// Skip text/whitespace nodes.
	if ( $node->nodeType !== XML_ELEMENT_NODE ) {
		$i++;
		continue;
	}

	$tag = strtolower( $node->nodeName );

	if ( $tag !== 'p' ) {
		$i++;
		continue;
	}

	// First child of <p> should be <strong> containing the question.
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

	// Collect the answer HTML.
	// Case 1: answer is inline in the same <p> after a <br>.
	// Case 2: answer is the next sibling <ul> (or <ol>).
	$answer_html = '';

	// Check for inline answer (text/elements after the <br> within this <p>).
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
	$inline_buf = trim( $inline_buf );

	if ( $inline_buf !== '' ) {
		$answer_html = '<p>' . $inline_buf . '</p>';
	}

	// Check if next sibling is a list.
	$j = $i + 1;
	while ( $j < $count && $children[ $j ]->nodeType !== XML_ELEMENT_NODE ) {
		$j++;
	}
	if ( $j < $count ) {
		$next_tag = strtolower( $children[ $j ]->nodeName );
		if ( $next_tag === 'ul' || $next_tag === 'ol' ) {
			$answer_html .= $dom->saveHTML( $children[ $j ] );
			$i = $j; // skip the list node in the outer loop
		}
	}

	$items[] = [
		'question' => $question,
		'answer'   => trim( $answer_html ),
	];

	$i++;
}

if ( empty( $items ) ) {
	WP_CLI::error( 'Parsed 0 items -- check the HTML structure.' );
}

WP_CLI::log( sprintf( 'Parsed %d FAQ items:', count( $items ) ) );
foreach ( $items as $idx => $item ) {
	WP_CLI::log( sprintf( '  [%d] %s', $idx + 1, $item['question'] ) );
}

if ( $dry_run ) {
	WP_CLI::success( 'Dry run -- no changes written.' );
	return;
}

$result = update_field( 'faq_items', $items, $page_id );

if ( $result !== false ) {
	WP_CLI::success( sprintf( 'Wrote %d FAQ items to faq_items on page %d.', count( $items ), $page_id ) );
} else {
	WP_CLI::error( 'update_field returned false -- check ACF is active and the field key is correct.' );
}
