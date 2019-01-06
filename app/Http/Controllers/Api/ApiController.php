<?php 

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    public function searchRecipes(Request $request)
    {
       
        $client = new Client();
        $result = $client->request('GET', 'https://www.food2fork.com/api/search', [
            'query' => [    
                'key' => '66a3fb9f4a0dc4d36b53c35b5fd212f4',
                'q' => $request['query'],
            ]
        ]);
        $result = json_decode($result->getBody());
        $recipes = $result->recipes;
        
        return $recipes;
    }

    public function getRecipe(Request $request)
    {
        $client = new Client();
        $result = $client->request('GET', 'https://www.food2fork.com/api/get', [
            'query' => [    
                'key' => '66a3fb9f4a0dc4d36b53c35b5fd212f4',
                'rId' => $request['recipeId'],
            ]
        ]);
        

        // $result = $result->getBody();
        // return $result;
        $result = json_decode($result->getBody());
        $recipe = $result->recipe; 
        
        return json_encode($recipe);
    }
}