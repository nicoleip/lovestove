<?php 

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use GuzzleHttp\Client;

class ListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function getList(Request $request)
    {
        $recipeId = $request->id;
        $client = new Client();
        $result = $client->request('POST', 'http://lovestove.com/api/get', [
            'json' => [    
                'recipeId' => $recipeId,
            ]
        ]);        

        $result = json_decode($result->getBody());
        $data = $this->renderList($result->ingredients);      

        return $data;
    }

    public function renderList($ingredients)
    {
        $ingrArr = $this->parseIngredients($ingredients);
        $markup= '';
        foreach($ingrArr as $ingr)
        {
            $markup .= '
            <li class="shopping__item">
                    <div class="shopping__count">
                        <input type="number" value="'.$ingr['count'].'" step="100">
                        <p>'.$ingr['unit'].'</p>
                    </div>
                    <p class="shopping__description">'.$ingr['ingredient'].'</p>
                    <button class="shopping__delete btn-tiny">
                        <svg>
                            <use href="img/icons.svg#icon-circle-with-cross"></use>
                        </svg>
                    </button>
                </li>
            ';            
        }
        return $markup;
    }

    public function mapIngredients($ingredient) {
        $unitsLong = ['tablespoons', 'tablespoon', 'ounces', 'ounce', 'teaspoons', 'teaspoon', 'cups', 'pounds'];
        $unitsShort = ['tbsp', 'tbsp', 'oz', 'oz', 'tsp', 'tsp', 'cup', 'pound' ];

        $ingredient = strtolower($ingredient);

        
        foreach($unitsLong as $key=>$value){
            $ingredient = str_replace($value, $unitsShort[$key], $ingredient);           
        }        

        $ingredient = preg_replace('/ *\([^)]*\) * /', ' ', $ingredient);        

        $arrIngredient = explode(' ', $ingredient);

        
        foreach($arrIngredient as $key=>$value){            
            
            // There is a unit
            if(count(array_intersect($arrIngredient, $unitsShort))) {
                $unitIndex = key(array_intersect($arrIngredient, $unitsShort));                          
                $arrCount = array_slice($arrIngredient, 0, $unitIndex);
                if(count($arrCount) === 1) {
                    //if the count is smth like '1-1/3', replace the - with + and then evaluate
                    if(strpos($arrIngredient[0], '-')){
                        $strr = str_replace('-', '+', $arrIngredient[0]);
                        $countt = eval("return ($strr);");
                        $ingredientObj['count'] = $countt;
                    } else {
                    $ingredientObj['count'] = $arrIngredient[0];
                    }
                } else {
                    $str = implode("+", array_slice($arrIngredient, 0, $unitIndex));
                    $count = eval("return ($str);");
                    $ingredientObj['count'] = $count;
                }
                $ingredientObj['unit'] = $arrIngredient[$unitIndex];
                $ingredientObj['ingredient'] = implode(" ",(array_slice($arrIngredient, $unitIndex+1)));
                return $ingredientObj;
                // There is no unit...
            } else {
                // ..but the first element is number
                if (is_numeric($arrIngredient[0])){
                    $ingredientObj['count'] = $arrIngredient[0];
                    $ingredientObj['unit'] = '';
                    $ingredientObj['ingredient'] = implode(" ",(array_slice($arrIngredient, 1)));
                    return $ingredientObj;
                   // ... and no number
                } else {
                    $ingredientObj['count'] = "1";
                    $ingredientObj['unit'] = '';
                    $ingredientObj['ingredient'] = $ingredient;
                    return $ingredientObj;
                }
            } 
        }      
                
    }        

    public function parseIngredients($ingredients)
    {
        $newIngredients = array_map([$this, 'mapIngredients'], $ingredients);

        return $newIngredients;
    }
}