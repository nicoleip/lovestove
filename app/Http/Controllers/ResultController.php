<?php 

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use GuzzleHttp\Client;

class ResultController extends Controller
{
    
    public function getResults(Request $request)
    {
        $query = $request->search;
        $client = new Client();
        $result = $client->request('POST', 'http://lovestove.com/api/test', [
            'json' => [    
                'query' => $query,
            ]
        ]);

        $result = json_decode($result->getBody());

        $data = $this->renderResults($result);

        return $data;
        
    }  
    
    
    public function renderRecipe($recipe) {
        $markup = '
        <li>
            <a class="results__link results__link--active" href="#'.$recipe->recipe_id.'">
                <figure class="results__fig">
                    <img src="'.$recipe->image_url.'" alt="'.$recipe->title.'">
                </figure>
                <div class="results__data">
                    <h4 class="results__name">'.$recipe->title.'</h4>
                    <p class="results__author">'.$recipe->publisher.'</p>
                </div>
            </a>
        </li>
        ';

        return $markup;
    }

    public function createPaginationButton($page, $type)
    {
        $pageNo = $type === 'prev' ? $page - 1 : $page + 1 ;
        $arrow = $type === 'prev' ? 'left' : 'right' ;
        $btnMarkup = '
        <button class="btn-inline results__btn--'.$type.'" data-goto='.$pageNo.'>
            <svg class="search__icon">
                <use href="img/icons.svg#icon-triangle-'.$arrow.'"></use>
            </svg>
            <span>Page'. $pageNo.'</span>
        </button>
        ';

        return $btnMarkup;
    }

    public function renderPaginationButtons($page, $numResulsts, $resPerPage) {
       $pages = ceil($numResulsts / $resPerPage);
       $button = '';

       if($page == 1 && $pages > 1){
            $button = $this->createPaginationButton($page, 'next');
       }else if($page < $pages){
            $button1 = $this->createPaginationButton($page, 'prev');
            $button2 = $this->createPaginationButton($page, 'next');
            $button = $button1.$button2;
       }else if($page == $pages && $pages > 1){
            $button = $this->createPaginationButton($page, 'prev');
       } 

       return $button;
    }

    public function renderResults($recipes, $page = 3, $resPerPage = 10) {
        $markupFull = '';
        $buttons = '';

        $start = ($page - 1) * $resPerPage;
        //$end = $page * $resPerPage;

        $recipesCut = array_slice($recipes, $start, $resPerPage);
        foreach ($recipesCut as $recipe)
        {
           $markupFull .=  $this->renderRecipe($recipe);
        }

        $buttons = $this->renderPaginationButtons($page, count($recipes), $resPerPage);

        return array(
            'markup' => $markupFull,
            'buttons' => $buttons
        );
    }
}