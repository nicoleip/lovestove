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
                    <img src="img/test-1.jpg" alt="Test">
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

    public function renderResults($recipes) {
        $markupFull = '';
        foreach ($recipes as $recipe)
        {
           $markupFull .=  $this->renderRecipe($recipe);
        }

        return $markupFull;
    }
}