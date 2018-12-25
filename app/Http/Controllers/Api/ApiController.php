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
                'key' => '3992789711285590629270c34985251f',
                'q' => $request['query'],
            ]
        ]);
        $result = json_decode($result->getBody());
        $recipes = $result->recipes;
        
        return $recipes;
    }
}