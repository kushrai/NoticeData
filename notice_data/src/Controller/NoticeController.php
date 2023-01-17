<?php

namespace Drupal\notice_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Pager\PagerManagerInterface;


class NoticeController extends ControllerBase {

  protected $pagerManager;
  
  public function __construct(PagerManagerInterface $pagerManager) {
    $this->pagerManager = $pagerManager;
  }

  public static function create(ContainerInterface $container) {
    $instance = $container->get('pager.manager');
    return new static($instance);
  }

    public function getData() {
        $data = array();$build=array();
        $response = $data = $result = null;
        if (function_exists('notice_response')) {
		  $pageid = $this->pagerManager->findPage();
		  if(isset($pageid) && $pageid >=1){
			   $response = notice_response('https://www.thegazette.co.uk/all-notices/notice/data.json?page=1&results-page='.$pageid, 'GET');
		  }
		  else {
				$response = notice_response('https://www.thegazette.co.uk/all-notices/notice/data.json', 'GET');
		  }
        }

        if ($response) {

          $result = json_decode($response);
          $data = array();
        // Get data.
        $items = (array) $result->entry;
        $limit = 10;
		foreach($result->link as $pager_name){
			$ar= (array) $pager_name;
			if($ar['@rel'] =='last'){
				$resultPage= explode('results-page=',$ar['@href']);
				$total=$resultPage[1];
			}
		}
		

        // Initialize pager and get current page.
        $pager = $this->pagerManager->createPager($total, $limit,$element = 0);
        $currentPage = $pager->getCurrentPage();

        // Use currentPage to limit items for the page.
        $items = array_slice($items, $currentPage * $limit, $limit);
        
          
          $data['title'] = 'NOTICE DATA';
          $data['notice'] = $result->entry;
		
		  $data['pager'] = [
          '#type' => 'pager',
         ];

          // display the content in the middle section of the page
          $build = array(
            '#theme' => 'notice_list', 
            '#title' => 'Notice Data',
            '#data' => $data
          );

          
        }
        return $build;
      }

   
}