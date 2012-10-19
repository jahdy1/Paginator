<?php
/**
 * An awesomely customizable pagination script
 *
 * @package		Spotflare
 * @author		Benezer Jahdy Lancelot <jahdy@spotflare.com>
 * @link		http://www.jahdy.com
 * @since		Version 1.0
 */

// ------------------------------------------------------------------------

class Paginator
{
  /**
   * Current Page
   * @var int
   */
  public $pg = 0;

  /**
   * Number of records per page
   * @var int
   */
  public $rp = 0;

  /**
   * Next page number (If around_the_world is true), this value is never NULL
   * @var int
   */
  public $next_pg = 0;

  /**
   * Previous page number (If around_the_world is true), this value is never NULL
   * @var int
   */
  public $prev_pg = 0;

  /**
   * Total number of records to create pagination around (One of two ways to setup Paginator -- the other is but setting
   * the num_of_pages config directly)
   * @var int
   */
  public $total_records = 0;

  /**
   * Total number of pages to create pagination around (The other option is setting the total_records config)
   * @var int
   */
  public $num_of_pages = 0;

  /**
   * This is the url string variable that is used to set the $this->pg (current page) property.  (This class support the ability to have
   * multiple paginated lists on the same page.  Simply set a different pg_query_var for each instance of this class and
   * presto chango!)
   *
   * Paginator uses $_GET[pg_query_var] to paginate through pages.
   *
   * @example http://www.jahdy.com/portfolio/?pg=2 // only one instance of Paginator
   * @example http://www.jahdy.com/portfolio/?pg=2&another_list_pg=7 // another instance of Paginator on the same page
   * @var string
   */
  public $pg_query_var = 'pg';

  /**
   * Like pg_query_var, rp_query_var's config setting is queried in the $_GET array and sets the $this->rp property which
   * controls the number of records shown on each page.  Also like pg_query_var, you can have multiple instances of paginated
   * lists on the page showing different records per page

   * @example http://www.jahdy.com/portfolio/?pg=2&rp=10 // only one instance of Paginator
   * @example http://www.jahdy.com/portfolio/?pg=2&rp=10&another_list_pg=7&another_list_rp=15 // another instance of Paginator on the same page
   * @var string
   */
  public $rp_query_var = 'rp';

  /**
   * The text that shows up in your 'Next' link
   * @var string
   */
  public $next_page_text = 'Next &raquo;';

  /**
   * The text that shows up in your 'Previous' link
   * @var string
   */
  public $prev_page_text = '&laquo; Previous';

  /**
   * The text that shows up in your 'First' link
   * @var string
   */
  public $first_page_text = '&laquo;&laquo; First';

  /**
   * The text that shows up in your 'Last' link
   * @var string
   */
  public $last_page_text = 'Last &raquo;&raquo;';

  // Classes and IDs for elements
  public $html_classes = 'clearfix';
  public $html_item_classes = 'paginate_link';
  public $html_id = 'Paginator';

  /**
   * Whether or not to return anchor tags wrapped in li elements or just anchor tags
   * @var bool
   */
  public $use_li_element = false;

  /**
   * The base_url.  If not set, the query vars are appended to the current string
   * @var string
   */
  public $base_url = '';

  /**
   * All of the query string variables as key value pairs
   * @var array
   */
  public $query_vars = array();

  /**
   * Whether or not to display the 'Next' and 'Previous' buttons
   * @var bool
   */
  public $next_prev_buttons = true;

  /**
   * Whether or not to display buttons that jump to both the first and last page
   * @var bool
   */
  public $first_last_buttons = true;

  /**
   * Whether to display all of the links or display
   * @var bool
   */
  public $display_all = true;

  /**
   * Number of links to show at any given time
   * @var int
   */
  public $display_limit = 4;

  /**
   * If the display_limit is an even number, there will be more links on one side than the other.  This boolean property
   * determines which side to add the extra link
   * @var bool
   */
  public $pad_left_for_even_nums = false;

  /**
   * This array has all other display settings for use in the html output page
   * @var array
   */
  public $display_data = array();

  /**
   * Whether the back button returns NULL or the value of the last page if $pg == 1
   * @var bool
   */
  public $around_the_world = false;

  /**
   * The number of instances of Paginator
   * @var int
   */
  public static $instance_num = 0;

  /**
   * Data that refers to ever instance of Paginator on the page
   * @var array
   */
  public static $instances = array();

  /**
   * Pass in an array of arguments that correlate with any of the above instance variables
   * @param array $args
   */
  public function __construct(array $args = array())
	{
    self::$instance_num += 1; // Increment the instance number
		$this->init($args); // Initialize the object
		$this->analyzeData(); // Do number calculations to define key variables
    $this->createDisplayLimitArray(); // Prepare an array of data for html output
    self::$instances[] = $this; // Store this instance in a static variable
	}
	
	/**
	* Process arguments passed into constructor
	*/
	public function init(array $args = array())
	{
		// Initialize Arguments
		foreach($args as $key => $value)
		{
			if(isset($this->$key)) {
        $this->$key = $value;
      }
		}
		$this->pg();
		$this->rp();
	}
	
	/**
	* Grab parameter and set
	*/
	public function pg()
	{
		$this->pg = isset($_GET[$this->pg_query_var]) && is_numeric($_GET[$this->pg_query_var]) ?
			(int) $_GET[$this->pg_query_var] : 1;
	}
	
	/**
	* Grab parameter and set
	*/
	public function rp()
	{
		$this->rp = isset($_GET[$this->rp_query_var]) && is_numeric($_GET[$this->rp_query_var]) ?
			(int) $_GET[$this->rp_query_var] : 10;
	}

  /**
   * Populate display data with information about padding limits and when to start link nums
   */
  public function createDisplayLimitArray(){
    // Set defaults as the would be if limits didn't matter
    $this->display_data = array(
      'link_display_start' => 1,
      'link_display_end' => (int)$this->num_of_pages,
      'low_boundary' => 1, // This will always be 1.
      'high_boundary' => (int)$this->num_of_pages, // This will always be the last page number
      'limit_is_even' => $this->display_limit % 2 == 0 ? true : false, // Decide whether the amount of padding will be uneven
      'outside_low' => false, // Whether or not the unadjusted start is outside of the low boundary
      'outside_high' => false, // Whether or not the unadjusted end is outside of the high boundary
    );

    // Whether to display all
    if(!$this->display_all){

      // If the amount to display is less than the number of pages, perform block
      if($this->display_limit < $this->num_of_pages){

        $this->_getHighAndLowBounds();

      }
    } else {
      // Set display limit to the number of pages if display_all is true;
      $this->display_limit = $this->num_of_pages;
    }

  }

  /**
   * Sets the limits of the padding if there is a limit set
   */
  public function _getHighAndLowBounds()
  {
    $padding_halve = floor($this->display_limit / 2);
    // Set halves right down the middle ... ideal for a number in the middle of the set
    $this->display_data['padding_left'] = (int)$padding_halve;
    $this->display_data['padding_right'] = (int)$padding_halve;

    // If the limit is an even number, than the padding will be uneven.  This block decides on which side to have the smaller padding
    if($this->display_data['limit_is_even']){
      if($this->pad_left_for_even_nums) $this->display_data['padding_right'] -= 1; else $this->display_data['padding_left'] -= 1;
    }

    // Set default padding.
    $this->display_data['link_display_start'] = (int)$this->pg - $this->display_data['padding_left'];
    $this->display_data['link_display_end'] = (int)$this->pg + $this->display_data['padding_right'];

    // Test now that link_display_start and link_display_end (default padding) are not outside of the boundary
    if($this->display_data['link_display_start'] < $this->display_data['low_boundary']){
      $this->display_data['outside_low'] = true;
      $this->display_data['link_display_start'] = (int)$this->display_data['low_boundary'];
      $this->display_data['link_display_end'] = (int)$this->display_data['low_boundary'] + $this->display_limit - 1;
    } elseif($this->display_data['link_display_end'] > $this->display_data['high_boundary']) {
      $this->display_data['outside_high'] = true;
      $this->display_data['link_display_end'] = (int)$this->display_data['high_boundary'];
      $this->display_data['link_display_start'] = (int)$this->display_data['high_boundary'] - $this->display_limit + 1;
    }

    // Sets the page range.  This index is not used, but is useful for debugging
    $this->display_data['page_range'] = range((int)$this->display_data['link_display_start'], (int)$this->display_data['link_display_end']);

  }

  /**
	* Take the arguments and create data array of available pages
	*/
	public function analyzeData()
	{
    $this->setQueryVars();
    // This is in the incase user wants to set num_of_pages from the config and not by calculating total_records / rp
		if(!empty($this->total_records)) $this->num_of_pages = (int) ceil($this->total_records / $this->rp);
    $this->setNextPage();
    $this->setPrevPage();
    $this->getHtmlID(); // Sets the html ID that user set and makes sure it is unique
	}

  /**
   * Sets the array of query vars that need to be re-added on return
   */
  public function setQueryVars()
  {
    parse_str($_SERVER['QUERY_STRING'], $this->query_vars);
  }

  /**
   * Set the next_pg property
   */
  public function setNextPage()
	{
		if($this->pg < $this->num_of_pages){
      $this->next_pg = $this->pg + 1;
    } elseif($this->pg == $this->num_of_pages){
      if($this->around_the_world){
        $this->next_pg = 1;
      } else {
        $this->next_pg = NULL;
      }
    }
	}

  /**
   * Set the prev_pg property
   */
  public function setPrevPage()
	{
    if($this->pg > 1){
      $this->prev_pg = $this->pg - 1;
    } elseif($this->pg == 1){
      if($this->around_the_world){
        $this->prev_pg = $this->num_of_pages;
      } else {
        $this->prev_pg = NULL;
      }
    }
	}

  public static function isUniqueHtmlID($id){
    foreach(static::$instances as $instance){
      if($instance->html_id == $id) return false;
    }
    return true;
  }

  /**
   * Get container ID while making sure that the ID is unique on the page
   */
  public function getHtmlID(){
    if(empty($this->html_id)) $this->html_id = 'Paginator';
    if(!static::isUniqueHtmlID($this->html_id)) $this->html_id .= '-' . self::$instance_num;
  }

  /**
   * Returns a
   * @param $page_num
   * @return string
   */
  public function getHtmlLink($page_num)
  {
    //if(isset($this->query_vars[$this->pg_query_var])) unset($this->query_vars[$this->pg_query_var]);
    if(is_numeric($page_num)) $this->query_vars[$this->pg_query_var] = (int)$page_num;
    $url = $this->base_url;
    if(!empty($this->query_vars)) $url .= '?' . http_build_query($this->query_vars);
    return $url;
  }

  /**
   * Creates the html to be used on the site
   * @return string
   */
  public function getHtml()
  {
    if($this->use_li_element) $output = '<ul'; else $output = '<div';
    $output .= !empty($this->html_id) ? ' id="' . $this->html_id . '" ':'';
    $output .= !empty($this->html_classes) ? ' class="' . $this->html_classes . '" ':'';
    $output .= '>';

    // Set the first page link
    if($this->first_last_buttons && $this->pg > $this->display_data['low_boundary']){
        if($this->use_li_element){
          $output .= '<li ';
          $output .= !empty($this->html_item_classes) ? ' class="' . $this->html_item_classes . ' first-pg" ' : '';
          $output .= '>';
        }
        $output .= '<a href="'.$this->getHtmlLink($this->display_data['low_boundary']).'"';
        $output .= !empty($this->html_item_classes) ? ' class="' . $this->html_item_classes . ' first-pg" ' : '';
        $output .= '>'.$this->first_page_text.'</a>';
        if($this->use_li_element) $output .= '</li>';
    }

    // Set the previous link
    if($this->next_prev_buttons){
      if($this->prev_pg){
        if($this->use_li_element){
          $output .= '<li ';
          $output .= !empty($this->html_item_classes) ? ' class="' . $this->html_item_classes . ' prev-pg" ' : '';
          $output .= '>';
        }
        $output .= '<a href="'.$this->getHtmlLink($this->prev_pg).'"';
        $output .= !empty($this->html_item_classes) ? ' class="' . $this->html_item_classes . ' prev-pg" ' : '';
        $output .= '>'.$this->prev_page_text.'</a>';
        if($this->use_li_element) $output .= '</li>';
      }
    }

    // Set up all of the number links
    for($i = $this->display_data['link_display_start']; $i <= $this->display_data['link_display_end']; $i ++){
      if($this->use_li_element){
        $output .= '<li';
        $class = ($this->pg == $i) ?  $this->html_item_classes . ' active' : $this->html_item_classes;
        $output .= ' class="' . $class . ' num"';
        $output .= '>';
      }
      $output .= '<a href="'.$this->getHtmlLink($i).'"';
      $class = ($this->pg == $i) ?  $this->html_item_classes . ' active' : $this->html_item_classes;
      $output .= ' class="' . $class . ' num"';
      $output .= '>'.$i.'</a>';
      if($this->use_li_element) $output .= '</li>';
    }

    // Set the next link
    if($this->next_prev_buttons){
      if($this->next_pg){
        if($this->use_li_element){
          $output .= '<li ';
          $output .= !empty($this->html_item_classes) ? ' class="' . $this->html_item_classes . ' next-pg" ' : '';
          $output .= '>';
        }
        $output .= '<a href="'.$this->getHtmlLink($this->next_pg).'"';
        $output .= !empty($this->html_item_classes) ? ' class="' . $this->html_item_classes . ' next-pg" ' : '';
        $output .= '>'.$this->next_page_text.'</a>';
        if($this->use_li_element) $output .= '</li>';
      }
    }

    // Set the last page link
    if($this->first_last_buttons && $this->pg < $this->display_data['high_boundary']){
      if($this->use_li_element){
        $output .= '<li ';
        $output .= !empty($this->html_item_classes) ? ' class="' . $this->html_item_classes . ' last-pg" ' : '';
        $output .= '>';
      }
      $output .= '<a href="'.$this->getHtmlLink($this->display_data['high_boundary']).'"';
      $output .= !empty($this->html_item_classes) ? ' class="' . $this->html_item_classes . ' last-pg" ' : '';
      $output .= '>'.$this->last_page_text.'</a>';
      if($this->use_li_element) $output .= '</li>';
    }

    if($this->use_li_element) $output .= '</ul>'; else $output .= '</div>';
    return $output;
  }
}