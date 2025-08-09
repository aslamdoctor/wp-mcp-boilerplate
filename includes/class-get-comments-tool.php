<?php
/**
 * Get Comments Tool.
 *
 * @package WPMCPBoilerplate
 */

declare(strict_types=1);

namespace WPMCPBoilerplate;

// include the base tool.
require_once WP_MCP_BOILERPLATE_PATH . 'includes/class-base-tool.php';

// include the base tool.
use WPMCPBoilerplate\BaseTool;

/**
 * Get Comments Tool.
 */
class GetCommentsTool extends BaseTool { // phpcs:ignore

	/**
	 * Set the name of the tool.
	 *
	 * @return string The name of the tool.
	 */
	protected function set_name(): string {
		return 'wmb_get_comments_tool';
	}

	/**
	 * Set the description of the tool.
	 *
	 * @return string The description of the tool.
	 */
	protected function set_description(): string {
		return 'Retrieve all WordPress comments and list them with pagination support';
	}

	/**
	 * Set the type of the tool.
	 *
	 * @return string The type of the tool.
	 */
	protected function set_type(): string {
		return 'read';
	}

	/**
	 * Set the input schema of the tool.
	 *
	 * @return array The input schema of the tool.
	 */
	protected function set_input_schema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'per_page'     => array(
					'type'        => 'integer',
					'description' => 'Number of comments per page (default: 10, max: 100)',
					'default'     => 10,
					'minimum'     => 1,
					'maximum'     => 100,
				),
				'page'         => array(
					'type'        => 'integer',
					'description' => 'Page number for pagination (default: 1)',
					'default'     => 1,
					'minimum'     => 1,
				),
				'status'       => array(
					'type'        => 'string',
					'description' => 'Filter by comment status',
					'enum'        => array( 'approved', 'pending', 'spam', 'trash', 'all' ),
					'default'     => 'approved',
				),
				'post_id'      => array(
					'type'        => 'integer',
					'description' => 'Filter comments by specific post ID (optional)',
					'minimum'     => 1,
				),
				'author_email' => array(
					'type'        => 'string',
					'description' => 'Filter comments by author email (optional)',
				),
				'search'       => array(
					'type'        => 'string',
					'description' => 'Search term to filter comments by content (optional)',
				),
				'order'        => array(
					'type'        => 'string',
					'description' => 'Sort order',
					'enum'        => array( 'asc', 'desc' ),
					'default'     => 'desc',
				),
				'orderby'      => array(
					'type'        => 'string',
					'description' => 'Sort field',
					'enum'        => array( 'date', 'date_gmt', 'author', 'author_email', 'author_url', 'author_IP', 'post' ),
					'default'     => 'date',
				),
			),
		);
	}

	/**
	 * Set the annotations of the tool.
	 *
	 * @return array The annotations of the tool.
	 */
	protected function set_annotations(): array {
		return array(
			'title'         => 'Get WordPress Comments',
			'readOnlyHint'  => true,
			'openWorldHint' => false,
		);
	}

	/**
	 * Execute the tool.
	 *
	 * @param array $args The arguments of the tool.
	 * @return array The result of the tool.
	 */
	public function execute( array $args ): array {
		// Set default values.
		$per_page     = isset( $args['per_page'] ) ? max( 1, min( 100, intval( $args['per_page'] ) ) ) : 10;
		$page         = isset( $args['page'] ) ? max( 1, intval( $args['page'] ) ) : 1;
		$status       = isset( $args['status'] ) ? sanitize_text_field( $args['status'] ) : 'approved';
		$post_id      = isset( $args['post_id'] ) ? intval( $args['post_id'] ) : 0;
		$author_email = isset( $args['author_email'] ) ? sanitize_email( $args['author_email'] ) : '';
		$search       = isset( $args['search'] ) ? sanitize_text_field( $args['search'] ) : '';
		$order        = isset( $args['order'] ) ? sanitize_text_field( $args['order'] ) : 'desc';
		$orderby      = isset( $args['orderby'] ) ? sanitize_text_field( $args['orderby'] ) : 'date';

		// Build query arguments.
		$query_args = array(
			'number'  => $per_page,
			'offset'  => ( $page - 1 ) * $per_page,
			'order'   => $order,
			'orderby' => $orderby,
		);

		// Add status filter.
		if ( 'all' !== $status ) {
			$query_args['status'] = $status;
		}

		// Add post ID filter.
		if ( $post_id > 0 ) {
			$query_args['post_id'] = $post_id;
		}

		// Add author email filter.
		if ( ! empty( $author_email ) ) {
			$query_args['author_email'] = $author_email;
		}

		// Add search filter.
		if ( ! empty( $search ) ) {
			$query_args['search'] = $search;
		}

		// Get comments.
		$comments = get_comments( $query_args );

		// Get total count for pagination.
		$total_query_args         = $query_args;
		unset( $total_query_args['number'] );
		unset( $total_query_args['offset'] );
		$total_query_args['count'] = true;
		$total_comments           = get_comments( $total_query_args );

		// Format comments data.
		$formatted_comments = array();
		foreach ( $comments as $comment ) {
			$post_title = get_the_title( $comment->comment_post_ID );
			$formatted_comments[] = array(
				'id'           => intval( $comment->comment_ID ),
				'post_id'      => intval( $comment->comment_post_ID ),
				'post_title'   => $post_title ? $post_title : 'Unknown Post',
				'author_name'  => $comment->comment_author,
				'author_email' => $comment->comment_author_email,
				'author_url'   => $comment->comment_author_url,
				'author_ip'    => $comment->comment_author_IP,
				'content'      => wp_strip_all_tags( $comment->comment_content ),
				'status'       => $comment->comment_approved,
				'date'         => $comment->comment_date,
				'date_gmt'     => $comment->comment_date_gmt,
				'parent_id'    => intval( $comment->comment_parent ),
				'user_agent'   => $comment->comment_agent,
			);
		}

		// Calculate pagination info.
		$total_pages = ceil( $total_comments / $per_page );

		return array(
			'success'         => true,
			'comments'        => $formatted_comments,
			'pagination'      => array(
				'current_page'      => $page,
				'per_page'          => $per_page,
				'total_comments'    => intval( $total_comments ),
				'total_pages'       => $total_pages,
				'has_next_page'     => $page < $total_pages,
				'has_previous_page' => $page > 1,
			),
			'filters_applied' => array(
				'status'       => $status,
				'post_id'      => $post_id > 0 ? $post_id : null,
				'author_email' => ! empty( $author_email ) ? $author_email : null,
				'search'       => ! empty( $search ) ? $search : null,
				'order'        => $order,
				'orderby'      => $orderby,
			),
		);
	}

	/**
	 * Set the permission to access the tool.
	 *
	 * @return bool The permission of the tool.
	 */
	public function permission(): bool {
		// Allow administrators and editors to view comments.
		return current_user_can( 'moderate_comments' );
	}
}
