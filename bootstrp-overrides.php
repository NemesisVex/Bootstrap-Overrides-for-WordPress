<?php
/*
  Plugin Name: Bootstrap Overrides
  Plugin URI: http://vigilantmedia.com/projects/
  Description: This plugin overrides a number of functions in wp-includes to accommodate Bootstrap classes. Of course, any changes to these functions in Core will not be reflected.
  Author: Greg Bueno
  Version: 0.01
  Author URI: http://vigilantmedia.com/
 */

if (! function_exists( 'bootstrap_comment_form' )) {
	/**
	 * Output a complete commenting form for use within a template.
	 *
	 * Most strings and form fields may be controlled through the $args array passed
	 * into the function, while you may also choose to use the comment_form_default_fields
	 * filter to modify the array of default fields if you'd just like to add a new
	 * one or remove a single field. All fields are also individually passed through
	 * a filter of the form comment_form_field_$name where $name is the key used
	 * in the array of fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array       $args {
	 *     Optional. Default arguments and form fields to override.
	 *
	 *     @type array $fields {
	 *         Default comment fields, filterable by default via the 'comment_form_default_fields' hook.
	 *
	 *         @type string $author Comment author field HTML.
	 *         @type string $email  Comment author email field HTML.
	 *         @type string $url    Comment author URL field HTML.
	 *     }
	 *     @type string $comment_field        The comment textarea field HTML.
	 *     @type string $must_log_in          HTML element for a 'must be logged in to comment' message.
	 *     @type string $logged_in_as         HTML element for a 'logged in as <user>' message.
	 *     @type string $comment_notes_before HTML element for a message displayed before the comment form.
	 *                                        Default 'Your email address will not be published.'.
	 *     @type string $comment_notes_after  HTML element for a message displayed after the comment form.
	 *                                        Default 'You may use these HTML tags and attributes ...'.
	 *     @type string $id_form              The comment form element id attribute. Default 'commentform'.
	 *     @type string $id_submit            The comment submit element id attribute. Default 'submit'.
	 *     @type string $name_submit          The comment submit element name attribute. Default 'submit'.
	 *     @type string $title_reply          The translatable 'reply' button label. Default 'Leave a Reply'.
	 *     @type string $title_reply_to       The translatable 'reply-to' button label. Default 'Leave a Reply to %s',
	 *                                        where %s is the author of the comment being replied to.
	 *     @type string $cancel_reply_link    The translatable 'cancel reply' button label. Default 'Cancel reply'.
	 *     @type string $label_submit         The translatable 'submit' button label. Default 'Post a comment'.
	 *     @type string $format               The comment form format. Default 'xhtml'. Accepts 'xhtml', 'html5'.
	 *     @type string $class_form           Class attribute for the form tag.
	 *     @type string $class_submit         Class attribute for the input submit tag.
	 * }
	 * @param int|WP_Post $post_id Post ID or WP_Post object to generate the form for. Default current post.
	 */
	function bootstrap_comment_form( $args = array(), $post_id = null ) {
		if ( null === $post_id )
			$post_id = get_the_ID();

		$commenter = wp_get_current_commenter();
		$user = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';

		$args = wp_parse_args( $args );
		if ( ! isset( $args['format'] ) )
			$args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';

		$req      = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );
		$html5    = 'html5' === $args['format'];
		$fields   =  array(
			'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
				'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
			'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
				'<input id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
			'url'    => '<p class="comment-form-url"><label for="url">' . __( 'Website' ) . '</label> ' .
				'<input id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>',
		);

		$required_text = sprintf( ' ' . __('Required fields are marked %s'), '<span class="required">*</span>' );

		/**
		 * Filter the default comment form fields.
		 *
		 * @since 3.0.0
		 *
		 * @param array $fields The default comment fields.
		 */
		$fields = apply_filters( 'comment_form_default_fields', $fields );
		$defaults = array(
			'fields'               => $fields,
			'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label> <textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
			/** This filter is documented in wp-includes/link-template.php */
			'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
			/** This filter is documented in wp-includes/link-template.php */
			'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ), get_edit_user_link(), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
			'comment_notes_before' => '<p class="comment-notes">' . __( 'Your email address will not be published.' ) . ( $req ? $required_text : '' ) . '</p>',
			'comment_notes_after'  => '<p class="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
			'id_form'              => 'commentform',
			'id_submit'            => 'submit',
			'class_form'           => '',
			'class_submit'         => '',
			'class_submit_container' => 'form-submit',
			'submit_container'     => 'p',
			'name_submit'          => 'submit',
			'title_reply'          => __( 'Leave a Reply' ),
			'title_reply_to'       => __( 'Leave a Reply to %s' ),
			'cancel_reply_link'    => __( 'Cancel reply' ),
			'label_submit'         => __( 'Post Comment' ),
			'format'               => 'xhtml',
		);

		/**
		 * Filter the comment form default arguments.
		 *
		 * Use 'comment_form_default_fields' to filter the comment fields.
		 *
		 * @since 3.0.0
		 *
		 * @param array $defaults The default comment form arguments.
		 */
		$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );

		?>
		<?php if ( comments_open( $post_id ) ) : ?>
			<?php
			/**
			 * Fires before the comment form.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_before' );
			?>
			<div id="respond" class="comment-respond">
				<h3 id="reply-title" class="comment-reply-title"><?php comment_form_title( $args['title_reply'], $args['title_reply_to'] ); ?> <small><?php cancel_comment_reply_link( $args['cancel_reply_link'] ); ?></small></h3>
				<?php if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) : ?>
					<?php echo $args['must_log_in']; ?>
					<?php
					/**
					 * Fires after the HTML-formatted 'must log in after' message in the comment form.
					 *
					 * @since 3.0.0
					 */
					do_action( 'comment_form_must_log_in_after' );
					?>
				<?php else : ?>
					<form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>" class="comment-form <?php echo esc_attr($args['class_form']); ?>"<?php echo $html5 ? ' novalidate' : ''; ?>>
						<?php
						/**
						 * Fires at the top of the comment form, inside the <form> tag.
						 *
						 * @since 3.0.0
						 */
						do_action( 'comment_form_top' );
						?>
						<?php if ( is_user_logged_in() ) : ?>
							<?php
							/**
							 * Filter the 'logged in' message for the comment form for display.
							 *
							 * @since 3.0.0
							 *
							 * @param string $args_logged_in The logged-in-as HTML-formatted message.
							 * @param array  $commenter      An array containing the comment author's
							 *                               username, email, and URL.
							 * @param string $user_identity  If the commenter is a registered user,
							 *                               the display name, blank otherwise.
							 */
							echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity );
							?>
							<?php
							/**
							 * Fires after the is_user_logged_in() check in the comment form.
							 *
							 * @since 3.0.0
							 *
							 * @param array  $commenter     An array containing the comment author's
							 *                              username, email, and URL.
							 * @param string $user_identity If the commenter is a registered user,
							 *                              the display name, blank otherwise.
							 */
							do_action( 'comment_form_logged_in_after', $commenter, $user_identity );
							?>
						<?php else : ?>
							<?php echo $args['comment_notes_before']; ?>
							<?php
							/**
							 * Fires before the comment fields in the comment form.
							 *
							 * @since 3.0.0
							 */
							do_action( 'comment_form_before_fields' );
							foreach ( (array) $args['fields'] as $name => $field ) {
								/**
								 * Filter a comment form field for display.
								 *
								 * The dynamic portion of the filter hook, $name, refers to the name
								 * of the comment form field. Such as 'author', 'email', or 'url'.
								 *
								 * @since 3.0.0
								 *
								 * @param string $field The HTML-formatted output of the comment form field.
								 */
								echo apply_filters( "comment_form_field_{$name}", $field ) . "\n";
							}
							/**
							 * Fires after the comment fields in the comment form.
							 *
							 * @since 3.0.0
							 */
							do_action( 'comment_form_after_fields' );
							?>
						<?php endif; ?>
						<?php
						/**
						 * Filter the content of the comment textarea field for display.
						 *
						 * @since 3.0.0
						 *
						 * @param string $args_comment_field The content of the comment textarea field.
						 */
						echo apply_filters( 'comment_form_field_comment', $args['comment_field'] );
						?>
						<?php echo $args['comment_notes_after']; ?>
						<p class="<?php echo esc_attr( $args['class_submit_container'] ); ?>">
							<input name="<?php echo esc_attr( $args['name_submit'] ); ?>" type="submit" class="<?php echo esc_attr( $args['class_submit'] ); ?>" id="<?php echo esc_attr( $args['id_submit'] ); ?>" value="<?php echo esc_attr( $args['label_submit'] ); ?>" />
							<?php comment_id_fields( $post_id ); ?>
						</p>
						<?php
						/**
						 * Fires at the bottom of the comment form, inside the closing </form> tag.
						 *
						 * @since 1.5.0
						 *
						 * @param int $post_id The post ID.
						 */
						do_action( 'comment_form', $post_id );
						?>
					</form>
				<?php endif; ?>
			</div><!-- #respond -->
			<?php
			/**
			 * Fires after the comment form.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_after' );
		else :
			/**
			 * Fires after the comment form if comments are closed.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_comments_closed' );
		endif;
	}
}

if (! function_exists( 'bootstrap_paginate_links' )) {
	/**
	 * Retrieve paginated link for archive post pages.
	 *
	 * Technically, the function can be used to create paginated link list for any
	 * area. The 'base' argument is used to reference the url, which will be used to
	 * create the paginated links. The 'format' argument is then used for replacing
	 * the page number. It is however, most likely and by default, to be used on the
	 * archive post pages.
	 *
	 * The 'type' argument controls format of the returned value. The default is
	 * 'plain', which is just a string with the links separated by a newline
	 * character. The other possible values are either 'array' or 'list'. The
	 * 'array' value will return an array of the paginated link list to offer full
	 * control of display. The 'list' value will place all of the paginated links in
	 * an unordered HTML list.
	 *
	 * The 'total' argument is the total amount of pages and is an integer. The
	 * 'current' argument is the current page number and is also an integer.
	 *
	 * An example of the 'base' argument is "http://example.com/all_posts.php%_%"
	 * and the '%_%' is required. The '%_%' will be replaced by the contents of in
	 * the 'format' argument. An example for the 'format' argument is "?page=%#%"
	 * and the '%#%' is also required. The '%#%' will be replaced with the page
	 * number.
	 *
	 * You can include the previous and next links in the list by setting the
	 * 'prev_next' argument to true, which it is by default. You can set the
	 * previous text, by using the 'prev_text' argument. You can set the next text
	 * by setting the 'next_text' argument.
	 *
	 * If the 'show_all' argument is set to true, then it will show all of the pages
	 * instead of a short list of the pages near the current page. By default, the
	 * 'show_all' is set to false and controlled by the 'end_size' and 'mid_size'
	 * arguments. The 'end_size' argument is how many numbers on either the start
	 * and the end list edges, by default is 1. The 'mid_size' argument is how many
	 * numbers to either side of current page, but not including current page.
	 *
	 * It is possible to add query vars to the link by using the 'add_args' argument
	 * and see {@link add_query_arg()} for more information.
	 *
	 * The 'before_page_number' and 'after_page_number' arguments allow users to
	 * augment the links themselves. Typically this might be to add context to the
	 * numbered links so that screen reader users understand what the links are for.
	 * The text strings are added before and after the page number - within the
	 * anchor tag.
	 *
	 * @since 2.1.0
	 *
	 * @param string|array $args Optional. Override defaults.
	 * @return array|string String of page links or array of page links.
	 */
	function bootstrap_paginate_links( $args = '' ) {
		global $wp_query, $wp_rewrite;

		$total        = ( isset( $wp_query->max_num_pages ) ) ? $wp_query->max_num_pages : 1;
		$current      = ( get_query_var( 'paged' ) ) ? intval( get_query_var( 'paged' ) ) : 1;
		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$query_args   = array();
		$url_parts    = explode( '?', $pagenum_link );

		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}

		$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
		$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

		$format  = $wp_rewrite->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $wp_rewrite->using_permalinks() ? user_trailingslashit( $wp_rewrite->pagination_base . '/%#%', 'paged' ) : '?paged=%#%';

		$defaults = array(
			'base' => $pagenum_link, // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
			'format' => $format, // ?page=%#% : %#% is replaced by the page number
			'total' => $total,
			'current' => $current,
			'show_all' => false,
			'prev_next' => true,
			'prev_text' => __('&laquo; Previous'),
			'next_text' => __('Next &raquo;'),
			'end_size' => 1,
			'mid_size' => 2,
			'type' => 'plain',
			'add_args' => false, // array of query args to add
			'add_fragment' => '',
			'before_page_number' => '',
			'after_page_number' => '',
			'list_class' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// Who knows what else people pass in $args
		$total = (int) $args['total'];
		if ( $total < 2 ) {
			return;
		}
		$current  = (int) $args['current'];
		$end_size = (int) $args['end_size']; // Out of bounds?  Make it the default.
		if ( $end_size < 1 ) {
			$end_size = 1;
		}
		$mid_size = (int) $args['mid_size'];
		if ( $mid_size < 0 ) {
			$mid_size = 2;
		}
		$add_args = is_array( $args['add_args'] ) ? $args['add_args'] : false;
		$r = '';
		$page_links = array();
		$dots = false;

		if ( $args['prev_next'] && $current && 1 < $current ) :
			$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current - 1, $link );
			if ( $add_args )
				$link = add_query_arg( $add_args, $link );
			$link .= $args['add_fragment'];

			/**
			 * Filter the paginated links for the given archive pages.
			 *
			 * @since 3.0.0
			 *
			 * @param string $link The paginated link URL.
			 */
			$page_links[] = '<a class="prev page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $args['prev_text'] . '</a>';
		endif;
		for ( $n = 1; $n <= $total; $n++ ) :
			if ( $n == $current ) :
				$page_links[] = "<span class='page-numbers current'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . "</span>";
				$dots = true;
			else :
				if ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
					$link = str_replace( '%_%', 1 == $n ? '' : $args['format'], $args['base'] );
					$link = str_replace( '%#%', $n, $link );
					if ( $add_args )
						$link = add_query_arg( $add_args, $link );
					$link .= $args['add_fragment'];

					/** This filter is documented in wp-includes/general-template.php */
					$page_links[] = "<a class='page-numbers' href='" . esc_url( apply_filters( 'paginate_links', $link ) ) . "'>" . $args['before_page_number'] . number_format_i18n( $n ) . $args['after_page_number'] . "</a>";
					$dots = true;
				elseif ( $dots && ! $args['show_all'] ) :
					$page_links[] = '<span class="page-numbers dots">' . __( '&hellip;' ) . '</span>';
					$dots = false;
				endif;
			endif;
		endfor;
		if ( $args['prev_next'] && $current && ( $current < $total || -1 == $total ) ) :
			$link = str_replace( '%_%', $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current + 1, $link );
			if ( $add_args )
				$link = add_query_arg( $add_args, $link );
			$link .= $args['add_fragment'];

			/** This filter is documented in wp-includes/general-template.php */
			$page_links[] = '<a class="next page-numbers" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '">' . $args['next_text'] . '</a>';
		endif;
		switch ( $args['type'] ) {
			case 'array' :
				return $page_links;

			case 'list' :
				$r .= "<ul class='page-numbers " . esc_attr( $args['list_class'] )  . "'>\n\t<li>";
				$r .= join("</li>\n\t<li>", $page_links);
				$r .= "</li>\n</ul>\n";
				break;

			default :
				$r = join("\n", $page_links);
				break;
		}
		return $r;
	}
}