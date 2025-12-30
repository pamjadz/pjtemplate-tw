<?php

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) return;

// wp post delete $(wp post list --post_type='revision' --format=ids) --force
// wp wc hpos enable --with-sync
// wp wc hpos verify_data --re-migrate
// wp wc hpos cleanup

WP_CLI::add_command('arvand_dbclean', function($args, $assoc_args) {
	global $wpdb;
	$action = isset($args[0]) ? $args[0] : null;
	$dry_run = isset($assoc_args['dry']);
	$batch_size = isset($assoc_args['batch']) ? absint( $assoc_args['batch'] ) : 1000;
	
	switch($action) {
		case 'orphans_postmeta':
			if ($dry_run) {
				$count = $wpdb->get_var(
					"SELECT COUNT(pm.meta_id) 
					 FROM {$wpdb->postmeta} pm 
					 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
					 WHERE p.ID IS NULL"
				);
				WP_CLI::log("Found: {$count} orphaned postmeta records");
				WP_CLI::log("Dry run - no records deleted");
			} else {
				$deleted = $wpdb->query(
					"DELETE pm 
					 FROM {$wpdb->postmeta} pm 
					 LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
					 WHERE p.ID IS NULL"
				);
				WP_CLI::success("Deleted: {$deleted} orphaned postmeta records");
			}
			break;
			
		case 'orphans_usermeta':
			if ($dry_run) {
				$count = $wpdb->get_var(
					"SELECT COUNT(um.umeta_id) 
					 FROM {$wpdb->usermeta} um 
					 LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID 
					 WHERE u.ID IS NULL"
				);
				WP_CLI::log("Found: {$count} orphaned usermeta records");
				WP_CLI::log("Dry run - no records deleted");
			} else {
				$deleted = $wpdb->query(
					"DELETE um 
					 FROM {$wpdb->usermeta} um 
					 LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID 
					 WHERE u.ID IS NULL"
				);
				WP_CLI::success("Deleted: {$deleted} orphaned usermeta records");
			}
			break;
			
		case 'orphans_termmeta':
			$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->termmeta}'");
			if( ! $table_exists ) {
				WP_CLI::success("termmeta table does not exist - skipping");
				break;
			}
			if ( $dry_run ) {
				$count = $wpdb->get_var("SELECT COUNT(tm.meta_id) FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id WHERE t.term_id IS NULL");
				WP_CLI::log("Found: {$count} orphaned termmeta records");
				WP_CLI::log("Dry run - no records deleted");
			} else {
				$deleted = $wpdb->query(
					"DELETE tm 
						FROM {$wpdb->termmeta} tm 
						LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id 
						WHERE t.term_id IS NULL"
				);
				WP_CLI::success("Deleted: {$deleted} orphaned termmeta records");
			}
			break;
		case 'unregistered_post_types':
			$registered_post_types = get_post_types();
			$db_post_types = $wpdb->get_col(
				"SELECT DISTINCT post_type 
				 FROM {$wpdb->posts} 
				 WHERE post_type NOT IN ('revision', 'nav_menu_item', 'custom_css', 'customize_changeset')
				   AND post_type NOT IN ('" . implode("','", array_map('esc_sql', $registered_post_types)) . "')"
			);
			
			if (empty($db_post_types)) {
				WP_CLI::success("No unregistered post types found");
				break;
			}
			
			WP_CLI::log("Found unregistered post types: " . implode(', ', $db_post_types));
			
			if (!$dry_run) {
				$wpdb->query('START TRANSACTION');
				
				try {
					$meta_deleted = $wpdb->query(
						"DELETE pm 
						 FROM {$wpdb->postmeta} pm 
						 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID 
						 WHERE p.post_type IN ('" . implode("','", array_map('esc_sql', $db_post_types)) . "')"
					);

					$posts_deleted = $wpdb->query(
						"DELETE FROM {$wpdb->posts} 
						 WHERE post_type IN ('" . implode("','", array_map('esc_sql', $db_post_types)) . "')"
					);
					
					$wpdb->query('COMMIT');
					WP_CLI::success("Deleted: {$posts_deleted} posts, {$meta_deleted} meta records");
					
				} catch (Exception $e) {
					$wpdb->query('ROLLBACK');
					WP_CLI::error("Failed to delete post types: " . $e->getMessage());
				}
			}
			break;
		case 'unregistered_taxonomies':
			$registered_taxonomies = get_taxonomies();
			$excluded_taxonomies = ['category', 'post_tag', 'nav_menu', 'link_category', 'post_format'];
			$excluded_sql = "'" . implode("','", array_map('esc_sql', $excluded_taxonomies)) . "'";
			
			$db_taxonomies = $wpdb->get_col(
				"SELECT DISTINCT taxonomy 
				 FROM {$wpdb->term_taxonomy} 
				 WHERE taxonomy NOT IN ({$excluded_sql})
				   AND taxonomy NOT IN ('" . implode("','", array_map('esc_sql', $registered_taxonomies)) . "')"
			);
			
			if (empty($db_taxonomies)) {
				WP_CLI::success("No unregistered taxonomies found");
				break;
			}
			
			WP_CLI::log("Found unregistered taxonomies: " . implode(', ', $db_taxonomies));
			
			if (!$dry_run) {
				$wpdb->query('START TRANSACTION');
				
				try {
					$term_ids = $wpdb->get_col(
						"SELECT term_id 
						 FROM {$wpdb->term_taxonomy} 
						 WHERE taxonomy IN ('" . implode("','", array_map('esc_sql', $db_taxonomies)) . "')"
					);
					
					if (!empty($term_ids)) {
						$term_ids_clean = array_map('intval', $term_ids);
						$term_ids_sql = implode(',', $term_ids_clean);
						$meta_deleted = 0;
						if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->termmeta}'") === $wpdb->termmeta) {
							$meta_deleted = $wpdb->query(
								"DELETE FROM {$wpdb->termmeta} WHERE term_id IN ({$term_ids_sql})"
							);
						}

						$term_taxonomy_deleted = $wpdb->query(
							"DELETE FROM {$wpdb->term_taxonomy} 
							 WHERE taxonomy IN ('" . implode("','", array_map('esc_sql', $db_taxonomies)) . "')"
						);
						
						$terms_deleted = $wpdb->query(
							"DELETE t 
							 FROM {$wpdb->terms} t 
							 LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
							 WHERE tt.term_id IS NULL"
						);
						
						$wpdb->query('COMMIT');
						WP_CLI::success(
							"Deleted: {$term_taxonomy_deleted} term_taxonomy records, " .
							"{$terms_deleted} terms, {$meta_deleted} termmeta records"
						);
					} else {
						$wpdb->query('COMMIT');
						WP_CLI::success("No terms found for unregistered taxonomies");
					}
					
				} catch (Exception $e) {
					$wpdb->query('ROLLBACK');
					WP_CLI::error("Failed to delete taxonomies: " . $e->getMessage());
				}
			}
			break;
			
		default:
			WP_CLI::error("Available commands:");
			echo PHP_EOL;
			WP_CLI::line("  wp arvand_dbclean orphans_postmeta");
			WP_CLI::line("  wp arvand_dbclean orphans_usermeta");
			WP_CLI::line("  wp arvand_dbclean orphans_termmeta");
			WP_CLI::line("  wp arvand_dbclean unregistered_post_types");
			WP_CLI::line("  wp arvand_dbclean unregistered_taxonomies");
			WP_CLI::line("  wp arvand_dbclean all");
			WP_CLI::line(PHP_EOL . "Add --dry for test mode");
			WP_CLI::line("Add --batch=1000 to set batch size (default: 1000)");
	}
});