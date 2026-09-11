<?php
/**
 * Header.
 *
 * @package Beautia
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php if ( get_theme_mod( 'beautia_enable_preloader', true ) ) : ?>
	<div class="preloader" id="beautia-preloader">
		<div class="preloader-mark"><?php beautia_icon( 'sparkle', 40 ); ?></div>
		<span class="preloader-bar"><i></i></span>
	</div>
<?php endif; ?>

<?php if ( get_theme_mod( 'beautia_enable_grain', false ) ) : ?>
	<div class="grain" aria-hidden="true"></div>
<?php endif; ?>

<?php if ( get_theme_mod( 'beautia_enable_cursor', false ) ) : ?>
	<div class="cursor" id="beautia-cursor"><span></span></div>
<?php endif; ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'beautia' ); ?></a>

<div id="page" class="site">

	<?php beautia_collection_bar(); ?>

	<header id="masthead" class="site-header<?php echo get_theme_mod( 'beautia_sticky_header', true ) ? ' is-sticky' : ''; ?>">
		<div class="container header-in">
			<div class="header-brand"><?php beautia_branding(); ?></div>

			<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary', 'beautia' ); ?>">
				<?php
				if ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'nav-list',
							'depth'          => 3,
						)
					);
				} else {
					echo '<ul class="nav-list">';
					wp_list_pages( array( 'title_li' => '', 'depth' => 1, 'number' => 6 ) );
					echo '</ul>';
				}
				?>
			</nav>

			<div class="header-actions">
				<?php beautia_theme_toggle(); ?>
				<button class="icon-btn search-toggle" type="button" aria-controls="beautia-search" aria-expanded="false" aria-label="<?php esc_attr_e( 'Search', 'beautia' ); ?>"><?php beautia_icon( 'search', 20 ); ?></button>
				<?php if ( is_user_logged_in() ) : ?>
					<a class="header-account" href="<?php echo esc_url( beautia_get_page_url( 'account' ) ); ?>"><span class="header-account-label">پنل من</span><span class="header-account-icon" aria-hidden="true"><?php beautia_icon( 'user', 20 ); ?></span></a>
				<?php else : ?>
					<a class="header-account" href="<?php echo esc_url( beautia_get_page_url( 'login' ) ); ?>"><span class="header-account-label">ورود / عضویت</span><span class="header-account-icon" aria-hidden="true"><?php beautia_icon( 'user', 20 ); ?></span></a>
				<?php endif; ?>
				<?php beautia_book_button( get_theme_mod( 'beautia_header_cta', __( 'Book Now', 'beautia' ) ), 'btn btn-primary btn-sm hide-sm' ); ?>
				<button class="burger" id="beautia-burger" aria-label="<?php esc_attr_e( 'Menu', 'beautia' ); ?>" aria-expanded="false">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>

		<div class="header-search" id="beautia-search">
			<div class="container smart-search" role="search">
				<label class="screen-reader-text" for="beautia-live-search">جستجوی هوشمند خدمات و محتوا</label>
				<div class="smart-search-field">
					<span aria-hidden="true"><?php beautia_icon( 'search', 21 ); ?></span>
					<input id="beautia-live-search" class="search-field" type="search" autocomplete="off" placeholder="نام خدمت، مقاله یا نمونه‌کار را بنویسید…">
					<button class="search-close" type="button" aria-label="بستن جستجو"><?php beautia_icon( 'close', 20 ); ?></button>
				</div>
				<div class="search-suggestions" id="beautia-search-suggestions" aria-live="polite"><p>برای نمونه «کوتاهی»، «فیشیال» یا «مژه» را جستجو کنید.</p></div>
			</div>
		</div>
	</header>

	<div class="mobile-nav" id="beautia-mobile-nav" aria-hidden="true">
		<div class="mobile-nav-in">
			<div class="mobile-nav-head"><strong>فهرست بیوتیا</strong><button class="mobile-nav-close" type="button" aria-label="بستن منو"><?php beautia_icon( 'close', 20 ); ?></button></div>
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'mobile-list', 'depth' => 2 ) );
			}
			?>
			<div class="mobile-cta">
				<?php beautia_book_button( __( 'Book an appointment', 'beautia' ), 'btn btn-primary btn-block' ); ?>
				<a class="mobile-account" href="<?php echo esc_url( is_user_logged_in() ? beautia_get_page_url( 'account' ) : beautia_get_page_url( 'login' ) ); ?>"><?php beautia_icon( 'user', 20 ); ?><span><?php echo is_user_logged_in() ? 'پنل من' : 'ورود / عضویت'; ?></span></a>
			</div>
			<?php beautia_social_links(); ?>
		</div>
	</div>

	<main id="content" class="site-main">
