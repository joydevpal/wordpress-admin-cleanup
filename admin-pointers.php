<?php

/**
 * Add wordpress pointer for admin guide
 */
add_action( 'admin_enqueue_scripts', 'jpwp_create_admin_pointers' );

function jpwp_create_admin_pointers() {
    $pointers = array(
        array(
            'id'       => 'pointer1',
            'screen'   => 'page',
            'target'   => '#cmb2-metabox-_ananyoo_page_metabox',
            'title'    => 'Page Options Metabox',
            'content'  => 'Custom metabox for page option by Anblik',
            'position' => array(
                'edge'  => 'top', // top, bottom, left, right
                'align' => 'left' // top, bottom, left, right, middle
            )
        ),
        array(
            'id'       => 'pointer2',
            'screen'   => 'page',
            'target'   => '#wp-content-media-buttons',
            'title'    => 'Show plugin help',
            'content'  => 'Enable to see all the help texts or disable to view it tight.',
            'position' => array(
                'edge'  => 'right',
                'align' => 'right'
            )
        ),
    );
    new JPWP_Admin_Pointer( $pointers );
}

// Create class for admin pointers
class JPWP_Admin_Pointer
{
    public $screen_id;
    public $valid;
    public $pointers;

    // Register variables and start up plugin
    public function __construct( $pointers = array( ) )
    {
        if( get_bloginfo( 'version' ) < '3.3' )
            return;

        $screen = get_current_screen();
        $this->screen_id = $screen->id;
        $this->register_pointers( $pointers );
        add_action( 'admin_enqueue_scripts', array( $this, 'add_pointers' ), 1000 );
        add_action( 'admin_print_footer_scripts', array( $this, 'add_scripts' ) );
    }


    // Register the available pointers for the current screen
    public function register_pointers( $pointers )
    {
        $screen_pointers = null;
        foreach( $pointers as $ptr )
        {
            if( $ptr['screen'] == $this->screen_id )
            {
                $options = array(
                    'content'  => sprintf(
                        '<h3> %s </h3> <p> %s </p>', 
                        __( $ptr['title'], 'plugindomain' ), 
                        __( $ptr['content'], 'plugindomain' )
                    ),
                    'position' => $ptr['position']
                );
                $screen_pointers[$ptr['id']] = array(
                    'screen'  => $ptr['screen'],
                    'target'  => $ptr['target'],
                    'options' => $options
                );
            }
        }
        $this->pointers = $screen_pointers;
    }


    // Add pointers to the current screen if they were not dismissed
    public function add_pointers()
    {
        if( !$this->pointers || !is_array( $this->pointers ) )
            return;

        // Get dismissed pointers
        $get_dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
        $dismissed = explode( ',', (string) $get_dismissed );

        // Check pointers and remove dismissed ones.
        $valid_pointers = array( );
        foreach( $this->pointers as $pointer_id => $pointer )
        {
            if(
                in_array( $pointer_id, $dismissed ) 
                || empty( $pointer ) 
                || empty( $pointer_id ) 
                || empty( $pointer['target'] ) 
                || empty( $pointer['options'] )
            )
                continue;

            $pointer['pointer_id'] = $pointer_id;
            $valid_pointers['pointers'][] = $pointer;
        }

        if( empty( $valid_pointers ) )
            return;

        $this->valid = $valid_pointers;
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
    }


    // Print JavaScript if pointers are available
    public function add_scripts()
    {
        if( empty( $this->valid ) )
            return;

        $pointers = json_encode( $this->valid );

        echo <<<HTML
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready( function($) {
		var WPHelpPointer = {$pointers};

		$.each(WPHelpPointer.pointers, function(i) {
			wp_help_pointer_open(i);
		});

		function wp_help_pointer_open(i) 
		{
			pointer = WPHelpPointer.pointers[i];
			$( pointer.target ).pointer( 
			{
				content: pointer.options.content,
				position: 
				{
					edge: pointer.options.position.edge,
					align: pointer.options.position.align
				},
				close: function() 
				{
					$.post( ajaxurl, 
					{
						pointer: pointer.pointer_id,
						action: 'dismiss-wp-pointer'
					});
				}
			}).pointer('open');
		}
	});
//]]>
</script>
HTML;
    }
    
}