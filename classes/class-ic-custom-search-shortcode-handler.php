<?php
/**
 * 
 * File contains the main class for this plugin
 * 
 * 
 * @category ic-custom-search
 * @author: Patrick Jackson <patrick@ivycat.com>
 * @version: 1.0.0
 * @since 1.0.0
 * 
 */

/**
 * The primary class for the IvyCat Custom Search plugin
 * 
 * Handles shortcode for search forms and search results
 * 
 * @author: Patrick Jackson <patrick@ivycat.com>
 * @version: 1.0.0
 * @since 1.0.0
 */
class IC_Custom_Search_Shortcode_Handler {
    
    /**
     * Shortcode for a custom search form
     * 
     * [ic-search-form action_path="/" post_types="post, page" 
     * search_field_label="Search for: " search_button_label="Search"]
     * 
     * @access  public
     * @param   string  $atts Attributes from the shortcode
     * @return  string  HTML format form to be inserted into the page content
     * 
     * @author: Patrick Jackson <patrick@ivycat.com>
     * @version 1.0.0
     * @since 1.0.0
     */
    static public function ic_search_form( $atts ) { 

        $attributes = shortcode_atts( array(
            'action_path' => '/',
            'post_types' => '',
            'search_field_label' => 'Search for: ',
            'search_button_label'=>'Search',
        ), $atts );


        return self::get_search_form( $attributes );
    }


    /**
     * 
     * Displays search results.  Place the shortcode on a page that will 
     * receive search results.  The http result should include a post with parameter
     * "s" that contains the search phrase, and optionally "post_types" with a comma-
     * delimited list of post types to search.
     * 
     * [ic-search-results no-posts-message='' template-before='' 
     * template-result='' template-after='']
     * 
     * 
     * @access  public
     * @param   string  $atts Attributes from shortcode
     * @return  string  HTML format search results to be inserted into the page content, 
     *                  formatted for viewing by the template
     * 
     * @author: Patrick Jackson <patrick@ivycat.com>
     * @version 1.0.0
     * @since   1.0.0
     */
    static public function ic_search_results( $atts ){

        $attributes = shortcode_atts( array(
            'no-posts-message' => 'Sorry, your search produced no results',
            'template-before' => '',
            'template-result' => '',
            'template-after' => '',
        ), $atts );


        // get request args
        $ics = filter_input(INPUT_GET, 'ics', FILTER_SANITIZE_STRING);
        $post_types = filter_input(INPUT_GET, 'ics_post_type', FILTER_SANITIZE_STRING, 
                FILTER_REQUIRE_ARRAY);
        
        // find the templates
        $template_before = self::get_template( $attributes['template-before'], 
                "/template-ic-custom-search-results-before.php" );
        $template_result = self::get_template( $attributes['template-result'], 
                "/template-ic-custom-search-result.php" );
        $template_after = self::get_template( $attributes['template-after'], 
                "/template-ic-custom-search-results-after.php" );

        // generate the WP_Query object
        $the_query = self::get_query( $ics, $post_types );

        // if there were results, generate the output to insert into the page
        if ( $the_query->have_posts() ) {
            
            $output = self::get_query_results_output( $the_query, $template_before, 
                    $template_result, $template_after );
        
        // if there were no results, display the no-results message
        /** @TODO instead of a message, add another template */
        } else {

            $output = $attributes['no-posts-message'];
        }

        wp_reset_postdata();
        return $output;
    }
    
    /**
     * 
     * Uses the query inputs to retrieve the WP_Query object
     * 
     * @access private
     * @param string    $search_phrase  The words used to search the database for results
     * @param array     $post_types     An array of strings, each of which is the slug for a post type
     * @return \WP_Query    The WP_Query object used to get the search results
     * 
     * @author: Patrick Jackson <patrick@ivycat.com>
     * @version 1.0.0
     * @since   1.0.0
     */
    static private function get_query( $search_phrase, $post_types){
        $query_args = array(
            's' => $search_phrase,
            'post_type' => $post_types,
            );
        
        $the_query = new WP_Query( $query_args );
        
        // If Relevanssi plugin is installed, run the query through it
        if ( function_exists( 'relevanssi_do_query') ){
            relevanssi_do_query($the_query);
        }
        
        return $the_query;
    }
    
    /**
     * 
     * Uses the WP_Query object and templates to compile the HTML results output 
     * to be inserted into the page.
     * 
     * @access private
     * @param   \WP_Query   $the_query          The WP_Query object used to obtain 
     *                                          the search results 
     * @param   string      $template_before    The path to the template file applied 
     *                                          before listing the query results
     * @param   string      $template_result    The path to the template file used
     *                                          to display each result in the results 
     *                                          loop
     * @param   string      $template_after     The path to the template file applied 
     *                                          after listing the query results
     * @return  string  The HTML output inserted into the page to display the entire
     *                  results content (before, loop, and after)
     * 
     * @author: Patrick Jackson <patrick@ivycat.com>
     * @version 1.0.0
     * @since   1.0.0
     */
    static private function get_query_results_output( $the_query, $template_before, 
            $template_result, $template_after ){
        ob_start();
            
        include $template_before;

        while ( $the_query->have_posts() ){ 
            $the_query->the_post();
            include $template_result;
        }

        include $template_after;

        return ob_get_clean();
    }
    
    /**
     * 
     * Generate the search form using the shortcode attributes
     * 
     * @access private
     * @param   array $attributes   array of attributes added to the shortcode
     * @return  string  HTML search form to insert into the page content
     * 
     * @author: Patrick Jackson <patrick@ivycat.com>
     * @version 1.0.0
     * @since   1.0.0
     */
    static private function get_search_form( $attributes ){
        
        $url = get_site_url();
        $post_types = explode( ", ", $attributes['post_types'] );        
        
        // Generate the form HTML...
        $form = <<<"EOH"
<form role="search" method="get" id="ics-search-form" class="ic-search-form" action="$url{$attributes['action_path']}"> 
<label class="ic-search-phrase-label" for="ics">{$attributes['search_field_label']}</label>
<input type="text" value="{$_GET['ics']}" name="ics" id="ics" />
EOH;

        foreach( $post_types as $post_type ){
            $form .= "<input type=\"hidden\" value=\"$post_type\""
                    . "name=\"ics_post_type[]\" id=\"ics_post_type[]\"/>";
        }

        $form .= <<<"EOH"
<input type="submit" id="ics-search-submit" value="{$attributes['search_button_label']}" /> 
</form> 
EOH;
        return $form; 
    }
    
    /**
     * 
     * Retrieve the path to the search results template.
     * 
     * 1. if template attribute is populated, look for it in child theme directory
     * 2. otherwise, look for it in the parent theme directory
     * 2. otherwise, look for default template override in child theme directory
     * 3. otherwise, look for default template override in parent theme directory
     * 3. otherwise, use default template in plugin templates directory
     * 
     * @access private
     * @param   string  $template_attribute the template directory specified in the 
     *                          search results shortcode
     * @return  string  the template directory to be used
     * 
     * @author: Patrick Jackson <patrick@ivycat.com>
     * @version 1.0.0
     * @since   1.0.0
     */
    static private function get_template( $template_attribute, $default_file_name ){
                        
        // if there was a template specified, look for it in child and parent dirs
        if ( $template_attribute != '' ){
            
            // prepend forward slash if necessary
            if ( strpos( $template_attribute, '/') !== 0 ){
                $template_attribute = '/' . $template_attribute;
            }
            
            $template = get_stylesheet_directory() . $template_attribute;
            if ( file_exists($template) ){
                return $template;
            }
            
            $template = get_template_directory() . $template_attribute;
            if ( file_exists($template) ){
                return $template;
            }
            
        // otherwise, look for default template name in child and parent dirs   
        } else {
            
            $template = get_stylesheet_directory() . $default_file_name;
            if ( file_exists($template) ){
                return $template;
            }
            
            $template = get_template_directory() . $default_file_name;
            if ( file_exists($template) ){
                return $template;
            }
            
        }
                    
        // otherwise, return default template
        return dirname(__DIR__) . "/templates" . $default_file_name;
        
    }    
}
