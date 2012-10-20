Paginator
=========

Awesomely customizable PHP 5+ pagination class

Example 1
include APPPATH . 'libraries' . DIRECTORY_SEPARATOR . 'Paginator.php';
$args = array(
  'pg' => $pr,
  'num_of_pages' => 27,
  'pg_query_var' => 'ps',
  'display_all' => false,
  'display_limit' => 5,
  'pad_left_for_even_nums' => false,
  'use_li_element' => true,
  'html_classes' => 'main-paginator clearfix',
  'around_the_world' => false,

);
$p = new Paginator($args);
var_dump($p);
echo $p->getHtml();
$args['pg_query_var'] = 'px';
$args['display_limit'] = '4';
$args['first_last_buttons'] = false;
$args['next_page_text'] = '&raquo';
$args['prev_page_text'] = '&laquo';
$p = new Paginator($args);
var_dump(Paginator::$instances);
echo $p->getHtml();
