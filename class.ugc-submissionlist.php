<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Ugc_submissionlist extends WP_List_Table {
	private $wpdb;
	private $filter;
	private $queryHandler;
	private $keyword = "";
	private $order = array('p.post_date','DESC');
	private $current_page = 1;

	function __construct($wpdb) {
        parent::__construct(
            array(
                'singular' => 'singular_form',
                'plural'   => 'plural_form',
                'ajax'     => false
            )
        );

        $this->wpdb = $wpdb;
    }

	function set_filter($options = array()) {
		$this->filter = $options;
	}

	function get_number($options = array()) {
		return $this->queryHandler->get_count($options,$this->keyword);
	}

	function get_current_page() {
		$current_page = $this->get_pagenum();

		if(!isset($current_page) or empty($current_page)) {
			$current_page = 1;
		}

		$this->current_page = $current_page;
	}

	function set_keyword($keyword) {
		$this->keyword = $keyword;
	}

	function set_sortparam($orderby = 'created_date',$order = 'DESC') {
		$this->order = array($orderby,$order);
	}



	function prepare_data($offset = 0,$limit = 25) {
		$status = "(p.post_type = 'ugc-post-video' OR p.post_type = 'ugc-post-youtube' OR p.post_type = 'ugc-post-photo')";
		$search = "";

		if($this->filter['state'] != 'all') {
			$status = "post_status = '".$this->filter['state']."'";			
		}

		if(!empty($this->keyword) and isset($this->keyword)) {
			$search = " AND (p.post_title LIKE '%".$this->keyword."%' OR p.post_content LIKE '%".$this->keyword."%' OR u.display_name LIKE '%".$this->keyword."%' OR u.ID LIKE '%".$this->keyword."%' OR u.user_email LIKE '%".$this->keyword."%' OR p.post_type LIKE '%".$this->keyword."%') ";
		}

		$lists = $this->wpdb->get_results("SELECT u.ID AS UID, u.user_email, u.display_name, p.*, s.* FROM wp_posts p, wp_users u, wp_ugc_post_stat s WHERE p.post_author = u.ID AND s.post_id = p.ID AND ".$status." AND (post_status = 'draft' OR post_status = 'publish') ".$search." ORDER BY ".$this->order[0]." ".$this->order[1]." LIMIT ".$offset.",".$limit);

		foreach ($lists as $key => $value) {
			$avatar = get_user_meta($value->UID,'ugc-user-avatar');
			$avatar = (isset($avatar[0])) ? $avatar[0] : $default_avatar;
			$metas = get_post_meta($value->ID);
			$meta_image = isset($metas['ugc_meta_image'][0]) ? $metas['ugc_meta_image'][0] : plugins_url( 'ugc/images/dummy-image-square.jpg');
			$url = site_url()."/wp-admin/user-edit.php?user_id=".$value->UID."&wp_http_referer=/wp-admin/admin.php?page=biucg-submission";
			$meta_url = ($value->content_type == 'youtube') ? 'https://www.youtube.com/watch?v='.$value->url : $value->guid;
			$approve_btn = ($value->post_status == 'draft') ? '<button class="approve show-approve" data-id="'.$value->ID.'">Approve</button>' : '';
			$pending_label = ($value->post_status == 'draft') ? "<button class='pending-label'>Pending</button>" : "";

			$post_type = 'MP4 Video';
			if($value->post_type == 'ugc-post-youtube') {
				$post_type = 'Youtube Video';
			} else if($value->post_type == 'ugc-post-photo') {
				$post_type = 'Photo';
			}

			$rows[] = array(
							'check'		=> '<input type="checkbox" class="checkitem" value="'.$value->ID.'" name="ids[]" style="margin-left:8px">',
							'name' 		=> '<img src="'.$avatar.'" width="40" class="avatarimage"><div class="username"><a href="'.$url.'" target="_blank">'.$value->display_name."</a><br>".$pending_label."</div>",
							'type' 		=> $post_type,
							'image' 	=> '<img src="'.$meta_image.'" width="100">',
							'meta_title'=> $value->post_title,
							'vote' 		=> '<a href="'.site_url().'/wp-admin/admin.php?page=ugc-submission&tab=like&id='.$value->ID.'">'.$value->like_count.'</a>',
							'comment' 	=> '<a href="'.site_url().'/wp-admin/edit-comments.php?p='.$value->ID.'">'.$value->comment_count.'</a>',
							'view' 		=> $value->view_count,
							'datetime' 	=> $value->post_date,
							'action'	=> '<div class="action-area-btn">
												<a href="'.site_url().'/wp-admin/admin.php?page=ugc-submission&tab=detail&id='.$value->ID.'"><button class="white show-detail-popup" data-id="'.$value->ID.'">View Detail</button></a>
												'.$approve_btn.'
												<button class="remove remove-item" data-id="'.$value->ID.'">Remove</button>
												
											</div>'
					);
		}

		return $rows;
	}	

	function get_columns(){
	  $columns = array(
	  	'check'		=> '<input type="checkbox" class="checkall">',
	    'name' 		=> 'Name',
	    'type'   	=> 'Type',
	    'image'		=> 'Preview',
	    'meta_title'=> 'Title',
	    'vote'  	=> '<span class="dashicons dashicons-thumbs-up"></span>',
	    'comment'  	=> '<span class="dashicons dashicons-format-chat"></span>',
	    'view'  	=> '<span class="dashicons dashicons-visibility"></span>',
	    'datetime'  => 'Timestamp',
	    'action'  	=> 'Action'
	  );
	  return $columns;
	}

	function prepare_items() {
	  $offset = 0;
	  $limit = 10;
  	  $current_page = $this->current_page;
  	  $total_items = count_submission($this->filter['state'],$this->keyword);


  	  if($current_page > 1) {
  	  	$offset = ($current_page - 1) * $limit;
  	  }

  	  $found_data = $this->prepare_data($offset,$limit);

	  $columns = $this->get_columns();
	  $hidden = array();
	  $sortable = array(
	  				'datetime'  => array( 'p.post_date', false ),
	  				'vote'  	=> array( 's.like_count', false ),
	  				'comment'  	=> array( 's.comment_count', false ),
	  				'view'  	=> array( 's.view_count', false ),
	  				'type'  	=> array( 'p.post_type', false )
	  			  );
	  $this->_column_headers = array($columns, $hidden, $sortable);
	  $this->set_pagination_args( array(
    	'total_items' => $total_items,              
    	'per_page'    => $limit                     
  	  ) );
 	  $this->items = $found_data;

 	  $this->process_bulk_action();
	}


	function column_cb($item) {
          
    }

    public function get_bulk_actions() {
	    return array(
	            'approve' => __( 'Approve', 'your-textdomain' ),
	            'remove'  => __( 'Remove', 'your-textdomain' ),
	    );
    }

    public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $action = $this->current_action();

        switch ( $action ) {

            case 'delete':
                wp_die( 'Delete something' );
                break;

            case 'save':
                wp_die( 'Save something' );
                break;

            default:
                // do nothing or something else
                return;
                break;
        }

        return;
    }


	function column_default( $item, $column_name ) {
	  switch( $column_name ) { 
	  	case 'check':
	    case 'name':
	    case 'type':
	    case 'meta_title':
	    case 'image':  
	    case 'email':
	    case 'vote':
	    case 'comment':
	    case 'view':	    	    
	    case 'action':
	    case 'datetime':
	      return $item[ $column_name ];
	    default:
	      return print_r( $item, true ) ; 
	  }
	}	
}