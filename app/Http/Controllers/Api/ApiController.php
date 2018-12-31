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
                'key' => '12a9b3fc07e627879e3c701bb4f0e878',
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
                'key' => '12a9b3fc07e627879e3c701bb4f0e878',
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